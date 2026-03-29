import { i18n } from './index'

const DEFAULT_LOCALE = 'en'
const TRANSLATION_API_BATCH_SIZE = 160
const TRANSLATION_API_PARALLEL_BATCHES = 3
const TRANSLATION_DEBOUNCE_MS = 90
const MAX_TRANSLATABLE_LENGTH = 220
const MAX_TEXT_UNITS_PER_PASS = 2600
const MAX_ATTR_UNITS_PER_PASS = 700
const LOCALE_CACHE_STORAGE_PREFIX = 'pm_ui_translate_cache_v1:'
const LOCALE_CACHE_MAX_ENTRIES = 3500

const SKIP_TAGS = new Set([
  'SCRIPT',
  'STYLE',
  'NOSCRIPT',
  'IFRAME',
  'CANVAS',
  'SVG',
  'PATH',
  'PRE',
  'CODE',
])

const ATTRIBUTE_NAMES = ['placeholder', 'title', 'aria-label', 'value']
const ATTRIBUTE_SELECTOR = [
  '[placeholder]',
  '[title]',
  '[aria-label]',
  'input[type="submit"][value]',
  'input[type="button"][value]',
  'button[value]',
].join(',')

const textOriginalByNode = new WeakMap()
const textLastTranslatedByNode = new WeakMap()
const trackedTextNodes = new Set()

const attrOriginalByElement = new WeakMap()
const attrLastTranslatedByElement = new WeakMap()
const trackedAttributeElements = new Set()

const localeCache = new Map()
const localeCachePersistenceTimer = new Map()

let initialized = false
let debounceTimer = null
let applyQueue = Promise.resolve()
let activeLocale = DEFAULT_LOCALE
let removeAfterEach = null
let passTimers = []
let isApplying = false

const getScopeRoot = () =>
  document.querySelector('[data-translate-scope="page"]') ||
  document.getElementById('app') ||
  document.body

const getApiBase = () => {
  const raw = String(import.meta.env.VITE_API_BASE_URL || '/api/v1').trim()
  if (!raw) return '/api/v1'
  return raw.endsWith('/') ? raw.slice(0, -1) : raw
}

const normalizeLocale = (value) => {
  const locale = String(value || '').trim().toLowerCase()
  return locale || DEFAULT_LOCALE
}

const containsLetters = (value) => /\p{L}/u.test(value)

const splitSpacing = (value) => {
  const text = String(value ?? '')
  const leading = (text.match(/^\s*/) || [''])[0]
  const trailing = (text.match(/\s*$/) || [''])[0]
  return {
    leading,
    trailing,
    core: text.slice(leading.length, text.length - trailing.length),
  }
}

const isLikelyIdentifier = (value) => /^[A-Z0-9][A-Z0-9._/-]{2,}$/.test(value)

const isTranslatableCoreText = (value) => {
  const text = String(value || '').trim()
  if (!text) return false
  if (text.length > MAX_TRANSLATABLE_LENGTH) return false
  if (!containsLetters(text)) return false
  if (/^https?:\/\//i.test(text)) return false
  if (/^[\w.+-]+@[\w.-]+\.[a-z]{2,}$/i.test(text)) return false
  if (isLikelyIdentifier(text) && !text.includes(' ')) return false
  return true
}

const isSkippableElement = (element) => {
  if (!element) return true
  if (element.closest('[data-no-auto-translate="true"]')) return true
  const notranslateRoot = element.closest('.notranslate')
  if (notranslateRoot && notranslateRoot !== document.body) return true
  if (element.closest('[contenteditable="true"]')) return true
  return SKIP_TAGS.has(element.tagName)
}

const getLocaleMap = (locale) => {
  const normalized = normalizeLocale(locale)
  if (!localeCache.has(normalized)) {
    const hydratedMap = new Map()

    try {
      const raw = localStorage.getItem(`${LOCALE_CACHE_STORAGE_PREFIX}${normalized}`)
      if (raw) {
        const parsed = JSON.parse(raw)
        if (parsed && typeof parsed === 'object') {
          Object.entries(parsed).forEach(([source, translated]) => {
            if (typeof source === 'string' && typeof translated === 'string') {
              hydratedMap.set(source, translated)
            }
          })
        }
      }
    } catch {
      // ignore storage parse errors
    }

    localeCache.set(normalized, hydratedMap)
  }
  return localeCache.get(normalized)
}

const scheduleLocaleCachePersist = (locale) => {
  const normalized = normalizeLocale(locale)
  const existingTimer = localeCachePersistenceTimer.get(normalized)
  if (existingTimer) {
    clearTimeout(existingTimer)
  }

  const timerId = setTimeout(() => {
    localeCachePersistenceTimer.delete(normalized)

    const localeMap = localeCache.get(normalized)
    if (!localeMap || !localeMap.size) return

    while (localeMap.size > LOCALE_CACHE_MAX_ENTRIES) {
      const oldestKey = localeMap.keys().next().value
      if (!oldestKey) break
      localeMap.delete(oldestKey)
    }

    try {
      localStorage.setItem(
        `${LOCALE_CACHE_STORAGE_PREFIX}${normalized}`,
        JSON.stringify(Object.fromEntries(localeMap.entries())),
      )
    } catch {
      // ignore storage quota errors
    }
  }, 180)

  localeCachePersistenceTimer.set(normalized, timerId)
}

const getElementMap = (storage, element) => {
  let map = storage.get(element)
  if (!map) {
    map = new Map()
    storage.set(element, map)
  }
  return map
}

const collectTextUnits = () => {
  const root = getScopeRoot()
  if (!root) return []

  const units = []
  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT)
  let current = walker.nextNode()

  while (current) {
    if (units.length >= MAX_TEXT_UNITS_PER_PASS) break

    const node = current
    const parent = node.parentElement
    if (parent && !isSkippableElement(parent)) {
      const currentValue = String(node.nodeValue || '')
      const currentSplit = splitSpacing(currentValue)
      if (isTranslatableCoreText(currentSplit.core)) {
        const previousOriginal = textOriginalByNode.get(node)
        const lastTranslated = textLastTranslatedByNode.get(node)

        let originalValue = previousOriginal
        if (!previousOriginal) {
          originalValue = currentValue
          textOriginalByNode.set(node, currentValue)
        } else if (lastTranslated && currentValue !== lastTranslated && currentValue !== previousOriginal) {
          originalValue = currentValue
          textOriginalByNode.set(node, currentValue)
        }

        const originalSplit = splitSpacing(originalValue ?? currentValue)
        if (isTranslatableCoreText(originalSplit.core)) {
          trackedTextNodes.add(node)
          units.push({
            type: 'text',
            node,
            source: originalSplit.core,
            leading: originalSplit.leading,
            trailing: originalSplit.trailing,
          })
        }
      }
    }

    current = walker.nextNode()
  }

  return units
}

const collectAttributeUnits = () => {
  const root = getScopeRoot()
  if (!root) return []

  const units = []
  const elements = root.querySelectorAll(ATTRIBUTE_SELECTOR)

  elements.forEach((element) => {
    if (units.length >= MAX_ATTR_UNITS_PER_PASS) return
    if (isSkippableElement(element)) return

    const originalMap = getElementMap(attrOriginalByElement, element)
    const translatedMap = getElementMap(attrLastTranslatedByElement, element)
    trackedAttributeElements.add(element)

    ATTRIBUTE_NAMES.forEach((attr) => {
      if (units.length >= MAX_ATTR_UNITS_PER_PASS) return
      if (!element.hasAttribute(attr)) return

      if (attr === 'value') {
        const tag = element.tagName
        const type = String(element.getAttribute('type') || '').toLowerCase()
        const allowed =
          (tag === 'INPUT' && (type === 'submit' || type === 'button')) ||
          tag === 'BUTTON'
        if (!allowed) return
      }

      const currentValue = String(element.getAttribute(attr) || '')
      const currentSplit = splitSpacing(currentValue)
      if (!isTranslatableCoreText(currentSplit.core)) return

      const previousOriginal = originalMap.get(attr)
      const lastTranslated = translatedMap.get(attr)

      let originalValue = previousOriginal
      if (!previousOriginal) {
        originalValue = currentValue
        originalMap.set(attr, currentValue)
      } else if (lastTranslated && currentValue !== lastTranslated && currentValue !== previousOriginal) {
        originalValue = currentValue
        originalMap.set(attr, currentValue)
      }

      const originalSplit = splitSpacing(originalValue ?? currentValue)
      if (!isTranslatableCoreText(originalSplit.core)) return

      units.push({
        type: 'attr',
        element,
        attr,
        source: originalSplit.core,
        leading: originalSplit.leading,
        trailing: originalSplit.trailing,
      })
    })
  })

  return units
}

const restoreOriginalContent = () => {
  trackedTextNodes.forEach((node) => {
    if (!node?.isConnected) {
      trackedTextNodes.delete(node)
      return
    }

    const original = textOriginalByNode.get(node)
    if (typeof original === 'string') {
      node.nodeValue = original
      textLastTranslatedByNode.delete(node)
    }
  })

  trackedAttributeElements.forEach((element) => {
    if (!element?.isConnected) {
      trackedAttributeElements.delete(element)
      return
    }

    const originalMap = attrOriginalByElement.get(element)
    const translatedMap = attrLastTranslatedByElement.get(element)
    if (!originalMap) return

    originalMap.forEach((value, attr) => {
      if (typeof value === 'string') {
        element.setAttribute(attr, value)
      }
      translatedMap?.delete(attr)
    })
  })
}

const requestTranslations = async (target, texts) => {
  if (!texts.length) return []

  const token = localStorage.getItem('pm_token')
  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  const response = await fetch(`${getApiBase()}/translations/ui`, {
    method: 'POST',
    headers,
    body: JSON.stringify({
      target,
      texts,
    }),
  })

  if (!response.ok) {
    throw new Error(`Translation request failed with status ${response.status}`)
  }

  const payload = await response.json()
  const translations = payload?.data?.translations
  if (!Array.isArray(translations)) {
    throw new Error('Invalid translation payload')
  }

  return translations.map((item, index) => {
    const value = String(item ?? '').trim()
    return value || texts[index]
  })
}

const buildTranslations = async (locale, sourceTexts) => {
  const localeMap = getLocaleMap(locale)
  const unresolved = []
  const translationMap = new Map()

  sourceTexts.forEach((text) => {
    if (localeMap.has(text)) {
      translationMap.set(text, localeMap.get(text))
      return
    }
    unresolved.push(text)
  })

  const chunks = []
  for (let index = 0; index < unresolved.length; index += TRANSLATION_API_BATCH_SIZE) {
    chunks.push(unresolved.slice(index, index + TRANSLATION_API_BATCH_SIZE))
  }

  for (let index = 0; index < chunks.length; index += TRANSLATION_API_PARALLEL_BATCHES) {
    const group = chunks.slice(index, index + TRANSLATION_API_PARALLEL_BATCHES)
    const groupResults = await Promise.all(
      group.map(async (chunk) => {
        try {
          const translatedChunk = await requestTranslations(locale, chunk)
          return { chunk, translatedChunk }
        } catch {
          return { chunk, translatedChunk: [] }
        }
      }),
    )

    groupResults.forEach(({ chunk, translatedChunk }) => {
      chunk.forEach((source, offset) => {
        const translated = translatedChunk[offset] || source
        localeMap.set(source, translated)
        translationMap.set(source, translated)
      })
    })
  }

  scheduleLocaleCachePersist(locale)

  return translationMap
}

const applyTranslations = async () => {
  if (!document?.body) return

  if (activeLocale === DEFAULT_LOCALE || activeLocale === 'bn') {
    restoreOriginalContent()
    return
  }

  const textUnits = collectTextUnits()
  const attrUnits = collectAttributeUnits()
  const units = [...textUnits, ...attrUnits]
  if (!units.length) return

  const uniqueSources = [...new Set(units.map((item) => item.source))]
  if (!uniqueSources.length) return

  const translatedMap = await buildTranslations(activeLocale, uniqueSources)

  units.forEach((unit) => {
    const translatedCore = translatedMap.get(unit.source) || unit.source
    const composed = `${unit.leading}${translatedCore}${unit.trailing}`

    if (unit.type === 'text') {
      unit.node.nodeValue = composed
      textLastTranslatedByNode.set(unit.node, composed)
      return
    }

    const translatedAttrMap = getElementMap(attrLastTranslatedByElement, unit.element)
    unit.element.setAttribute(unit.attr, composed)
    translatedAttrMap.set(unit.attr, composed)
  })
}

const queueApply = () => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  debounceTimer = setTimeout(() => {
    applyQueue = applyQueue
      .then(async () => {
        isApplying = true
        try {
          await applyTranslations()
        } finally {
          isApplying = false
        }
      })
      .catch(() => {
        isApplying = false
      })
  }, TRANSLATION_DEBOUNCE_MS)
}

const clearPassTimers = () => {
  passTimers.forEach((timerId) => clearTimeout(timerId))
  passTimers = []
}

const schedulePasses = (delays) => {
  clearPassTimers()
  delays.forEach((delay) => {
    const timerId = setTimeout(() => {
      if (!isApplying) queueApply()
    }, delay)
    passTimers.push(timerId)
  })
}

const handleLocaleChange = (event) => {
  activeLocale = normalizeLocale(event?.detail ?? i18n.global.locale.value)
  schedulePasses([0, 260, 800])
}

const handleContentUpdated = () => {
  if (activeLocale === DEFAULT_LOCALE || activeLocale === 'bn') return
  schedulePasses([180])
}

export const initPageTranslator = (router) => {
  if (initialized) return
  initialized = true
  activeLocale = normalizeLocale(i18n.global.locale.value)

  window.addEventListener('pm-locale-changed', handleLocaleChange)
  window.addEventListener('pm-content-updated', handleContentUpdated)

  if (router?.afterEach) {
    removeAfterEach = router.afterEach(() => {
      if (activeLocale === DEFAULT_LOCALE || activeLocale === 'bn') return
      schedulePasses([160, 520])
    })
  }

  if (activeLocale !== DEFAULT_LOCALE && activeLocale !== 'bn') {
    schedulePasses([180])
  }
}

export const disposePageTranslator = () => {
  if (!initialized) return
  initialized = false

  if (debounceTimer) {
    clearTimeout(debounceTimer)
    debounceTimer = null
  }

  localeCachePersistenceTimer.forEach((timerId) => clearTimeout(timerId))
  localeCachePersistenceTimer.clear()

  clearPassTimers()

  if (typeof removeAfterEach === 'function') {
    removeAfterEach()
    removeAfterEach = null
  }

  window.removeEventListener('pm-locale-changed', handleLocaleChange)
  window.removeEventListener('pm-content-updated', handleContentUpdated)
}
