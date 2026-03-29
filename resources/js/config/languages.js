export const LANGUAGE_OPTIONS = Object.freeze([
  { code: 'en', label: 'English' },
  { code: 'bn', label: 'Bangla' },
  { code: 'hi', label: 'Hindi' },
  { code: 'ur', label: 'Urdu' },
  { code: 'ar', label: 'Arabic' },
  { code: 'zh', label: 'Chinese (Simplified)' },
  { code: 'zh-tw', label: 'Chinese (Traditional)' },
  { code: 'ja', label: 'Japanese' },
  { code: 'ko', label: 'Korean' },
  { code: 'fr', label: 'French' },
  { code: 'es', label: 'Spanish' },
  { code: 'de', label: 'German' },
  { code: 'it', label: 'Italian' },
  { code: 'pt', label: 'Portuguese' },
  { code: 'pt-br', label: 'Portuguese (Brazil)' },
  { code: 'ru', label: 'Russian' },
  { code: 'tr', label: 'Turkish' },
  { code: 'nl', label: 'Dutch' },
  { code: 'pl', label: 'Polish' },
  { code: 'sv', label: 'Swedish' },
  { code: 'no', label: 'Norwegian' },
  { code: 'da', label: 'Danish' },
  { code: 'fi', label: 'Finnish' },
  { code: 'cs', label: 'Czech' },
  { code: 'sk', label: 'Slovak' },
  { code: 'hu', label: 'Hungarian' },
  { code: 'ro', label: 'Romanian' },
  { code: 'bg', label: 'Bulgarian' },
  { code: 'el', label: 'Greek' },
  { code: 'he', label: 'Hebrew' },
  { code: 'id', label: 'Indonesian' },
  { code: 'ms', label: 'Malay' },
  { code: 'th', label: 'Thai' },
  { code: 'vi', label: 'Vietnamese' },
  { code: 'fa', label: 'Persian' },
  { code: 'uk', label: 'Ukrainian' },
  { code: 'ta', label: 'Tamil' },
  { code: 'te', label: 'Telugu' },
  { code: 'ml', label: 'Malayalam' },
  { code: 'mr', label: 'Marathi' },
  { code: 'gu', label: 'Gujarati' },
  { code: 'pa', label: 'Punjabi' },
  { code: 'ne', label: 'Nepali' },
  { code: 'si', label: 'Sinhala' },
  { code: 'sw', label: 'Swahili' },
  { code: 'am', label: 'Amharic' },
  { code: 'af', label: 'Afrikaans' },
  { code: 'ca', label: 'Catalan' },
  { code: 'hr', label: 'Croatian' },
  { code: 'et', label: 'Estonian' },
  { code: 'lv', label: 'Latvian' },
  { code: 'lt', label: 'Lithuanian' },
  { code: 'sl', label: 'Slovenian' },
  { code: 'sr', label: 'Serbian' },
  { code: 'mk', label: 'Macedonian' },
  { code: 'is', label: 'Icelandic' },
  { code: 'ga', label: 'Irish' },
  { code: 'cy', label: 'Welsh' },
  { code: 'sq', label: 'Albanian' },
  { code: 'hy', label: 'Armenian' },
  { code: 'ka', label: 'Georgian' },
  { code: 'kk', label: 'Kazakh' },
  { code: 'uz', label: 'Uzbek' },
  { code: 'az', label: 'Azerbaijani' },
  { code: 'mn', label: 'Mongolian' },
])

export const DEFAULT_LANGUAGE_CODE = 'en'

export const LANGUAGE_CODES = Object.freeze(LANGUAGE_OPTIONS.map((item) => item.code))

const languageCodeSet = new Set(LANGUAGE_CODES)

export const normalizeLanguageCode = (value) => {
  const code = String(value || '').trim().toLowerCase()
  if (!code) return DEFAULT_LANGUAGE_CODE
  return languageCodeSet.has(code) ? code : DEFAULT_LANGUAGE_CODE
}

export const findLanguageOption = (value) => {
  const code = String(value || '').trim().toLowerCase()
  return LANGUAGE_OPTIONS.find((item) => item.code === code) || null
}
