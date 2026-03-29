import { createI18n } from 'vue-i18n'
import { DEFAULT_LANGUAGE_CODE, LANGUAGE_CODES, LANGUAGE_OPTIONS, normalizeLanguageCode } from '../config/languages'

export const DEFAULT_LOCALE = DEFAULT_LANGUAGE_CODE
export const LOCALE_STORAGE_KEY = 'pm_locale'
export const LOCALE_OPTIONS = LANGUAGE_OPTIONS

const localeCodeSet = new Set(LANGUAGE_CODES)
const localeLoaders = {
  en: () => import('./locales/en.json'),
  bn: () => import('./locales/bn.json'),
}

const loadedLocales = new Set()

const normalizeLocale = (value) => {
  const input = String(value || '').trim().toLowerCase()
  if (!input) return DEFAULT_LOCALE
  if (localeCodeSet.has(input)) return input
  return normalizeLanguageCode(input)
}

const getBrowserLocale = () => {
  const browserLocale = String(navigator?.language || '').trim().toLowerCase()
  if (!browserLocale) return DEFAULT_LOCALE

  if (localeCodeSet.has(browserLocale)) return browserLocale

  const baseCode = browserLocale.split('-')[0]
  if (localeCodeSet.has(baseCode)) return baseCode

  return DEFAULT_LOCALE
}

const getInitialLocale = () => {
  const stored = localStorage.getItem(LOCALE_STORAGE_KEY)
  if (stored) return normalizeLocale(stored)
  return getBrowserLocale()
}

export const i18n = createI18n({
  legacy: false,
  locale: DEFAULT_LOCALE,
  fallbackLocale: DEFAULT_LOCALE,
  messages: {},
  globalInjection: true,
})

const setHtmlLang = (locale) => {
  document.documentElement.setAttribute('lang', locale)
}

export const loadLocaleMessages = async (locale) => {
  const normalized = normalizeLocale(locale)
  if (loadedLocales.has(normalized)) return normalized

  if (!loadedLocales.has(DEFAULT_LOCALE)) {
    const baseModule = await localeLoaders[DEFAULT_LOCALE]()
    const baseMessages = baseModule.default || baseModule
    i18n.global.setLocaleMessage(DEFAULT_LOCALE, baseMessages)
    loadedLocales.add(DEFAULT_LOCALE)
  }

  const loader = localeLoaders[normalized]
  if (loader) {
    const module = await loader()
    const messages = module.default || module
    i18n.global.setLocaleMessage(normalized, messages)
  } else {
    const fallbackMessages = i18n.global.getLocaleMessage(DEFAULT_LOCALE)
    i18n.global.setLocaleMessage(normalized, fallbackMessages)
  }

  loadedLocales.add(normalized)
  return normalized
}

export const setLocale = async (locale, options = {}) => {
  const normalized = normalizeLocale(locale)
  await loadLocaleMessages(normalized)

  i18n.global.locale.value = normalized
  setHtmlLang(normalized)

  const shouldPersist = options.persist !== false
  if (shouldPersist) {
    localStorage.setItem(LOCALE_STORAGE_KEY, normalized)
  }

  window.dispatchEvent(new CustomEvent('pm-locale-changed', { detail: normalized }))
  return normalized
}

export const initI18n = async () => {
  const initial = getInitialLocale()
  await setLocale(initial, { persist: false })
  return initial
}
