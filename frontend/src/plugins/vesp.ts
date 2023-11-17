import type {Composer} from 'vue-i18n'
import {Socket} from 'socket.io-client'
import {storeToRefs} from 'pinia'
import {useVespStore} from '~/stores/vesp'

export default defineNuxtPlugin(async (nuxtApp) => {
  const $i18n = nuxtApp.$i18n as Composer
  const $socket = nuxtApp.$socket as Socket
  const currency = (nuxtApp.$config.public.CURRENCY || 'RUB') as string
  const store = useVespStore()
  await store.load()
  const refStore = storeToRefs(store)

  nuxtApp.provide('scope', hasScope)
  nuxtApp.provide('image', getImageLink)
  nuxtApp.provide('file', getFileLink)
  nuxtApp.provide('sidebar', refStore.sidebar)
  nuxtApp.provide('login', refStore.login)
  nuxtApp.provide('price', (val: number) => {
    if (!val) {
      return ''
    }
    const locale = $i18n.locales.value.find((i: any) => i.code === $i18n.locale.value)
    if (locale && typeof locale !== 'string') {
      const formatter = new Intl.NumberFormat(locale.iso || 'ru-RU', {
        currency,
        style: 'currency',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      })
      return formatter.format(val)
    }
    return val
  })
  nuxtApp.provide(
    'settings',
    computed(() => {
      const settings: Record<string, any> = {}
      Object.keys(store.settings).forEach((key: string) => {
        let value: string | Record<string, any> = store.settings[key]
        if (value && typeof value === 'object' && value[$i18n.locale.value]) {
          value = value[$i18n.locale.value]
        }
        settings[key] = value
      })
      return settings
    }),
  )
  nuxtApp.provide('isMobile', ref(store.isMobile))
  nuxtApp.provide('contentPreview', contentPreview)
  nuxtApp.provide('contentClick', contentClick)

  // Listen for settings update
  if ($socket) {
    $socket.on('setting', ({key, value}: {key: string; value: string | string[]}) => {
      store.settings[key] = value
    })
  }
})
