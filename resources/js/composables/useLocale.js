import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { LOCALE_OPTIONS, setLocale } from '../i18n'

export const useLocale = () => {
  const { t, locale } = useI18n()

  const localeOptions = LOCALE_OPTIONS

  const currentLocale = computed(() => locale.value)

  const changeLocale = async (nextLocale) => {
    return setLocale(nextLocale)
  }

  return {
    t,
    locale,
    currentLocale,
    localeOptions,
    changeLocale,
  }
}
