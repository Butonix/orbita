// https://nuxt.com/docs/api/configuration/nuxt-config

import type {NuxtConfig} from '@nuxt/schema'

const enabledLocales = (process.env.LOCALES || 'ru,en,de').split(',')
const locales = [
  {code: 'ru', name: 'Русский', file: 'lexicons/ru.js', iso: 'ru-RU'},
  {code: 'en', name: 'English', file: 'lexicons/en.js', iso: 'en-GB'},
  {code: 'de', name: 'Deutsch', file: 'lexicons/de.js', iso: 'de-DE'},
].filter((i) => enabledLocales.includes(i.code))

const config: NuxtConfig = {
  telemetry: false,
  srcDir: 'src/',
  css: ['~/assets/scss/index.scss'],
  devtools: {enabled: false},
  vite: {
    server: {
      hmr: {port: 3001},
    },
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: '@use "@/assets/scss/_variables.scss" as *;',
          quietDeps: true,
        },
      },
    },
  },
  nitro: {
    experimental: {websocket: true},
    storage: {cache: {driver: 'redis', host: 'redis'}},
    devStorage: {cache: {driver: 'redis', host: 'redis'}},
  },
  routeRules: {
    '/admin/**': {ssr: false},
    '/user/**': {ssr: false},
    '/search': {ssr: false},
  },
  runtimeConfig: {
    CACHE_PAGES_TIME: process.env.CACHE_PAGES_TIME,
    SOCKET_SECRET: process.env.SOCKET_SECRET,
    YANDEX_METRIKA_ID: process.env.YANDEX_METRIKA_ID,
    locales,
    public: {
      TZ: process.env.TZ || 'Europe/Moscow',
      SITE_URL: process.env.SITE_URL || 'http://127.0.0.1:8080/',
      API_URL: process.env.API_URL || '/api/',
      JWT_EXPIRE: process.env.JWT_EXPIRE || '2592000',
    },
  },
  app: {
    pageTransition: {name: 'page', mode: 'out-in'},
    layoutTransition: {name: 'page', mode: 'out-in'},
    head: {
      title: process.env.SITE_NAME,
      viewport: 'width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=0',
      meta: [
        {name: 'msapplication-config', content: '/favicons/browserconfig.xml'},
        {name: 'theme-color', content: '#fff'},
      ],
      link: [
        {rel: 'apple-touch-icon', sizes: '180x180', href: '/favicons/apple-touch-icon.png'},
        {rel: 'icon', sizes: '32x32', href: '/favicons/favicon-32x32.png', type: 'image/png'},
        {rel: 'icon', sizes: '16x16', href: '/favicons/favicon-16x16.png', type: 'image/png'},
        {rel: 'manifest', href: '/favicons/site.webmanifest'},
        {rel: 'mask-icon', href: '/favicons/safari-pinned-tab.svg'},
        {rel: 'shortcut icon', href: '/favicons/favicon.ico'},
        {rel: 'alternate', type: 'application/rss+xml', title: 'RSS', href: '/rss.xml'},
        {rel: 'alternate', type: 'application/atom+xml', title: 'Atom', href: '/atom.xml'},
      ],
    },
  },
  modules: ['@vesp/frontend'],
  vesp: {
    icons: {
      solid: [
        'user',
        'power-off',
        'globe',
        'filter',
        'pause',
        'play',
        'upload',
        'question',
        'image',
        'video',
        'file',
        'music',
        'code',
        'calendar',
        'cloud-arrow-down',
        'comment',
        'comments',
        'bars',
        'right-to-bracket',
        'hashtag',
        'reply',
        'trash',
        'undo',
        'paper-plane',
        'wallet',
        'hourglass-half',
        'lock',
        'lock-open',
        'heading',
        'list',
        'face-smile',
        'tags',
        'external-link',
        'magnifying-glass',
        'arrow-up-wide-short',
        'arrow-down-short-wide',
        'download',
        'sun',
        'moon',
      ],
      regular: ['face-smile'],
    },
  },
  i18n: {
    defaultLocale: locales[0].code,
    detectBrowserLanguage: {
      fallbackLocale: locales[0].code,
    },
    locales,
  },
  compatibilityDate: '2024-09-04',
}

if (process.env.NODE_ENV === 'development') {
  config.modules?.push('@nuxtjs/eslint-module', '@nuxtjs/stylelint-module')
  // @ts-ignore
  config.eslint = {
    lintOnStart: false,
  }
  // @ts-ignore
  config.stylelint = {
    lintOnStart: false,
  }
}

if (process.env.YANDEX_METRIKA_ID && Number(process.env.YANDEX_METRIKA_ID) > 0) {
  let options = {}
  if (process.env.YANDEX_METRIKA_OPTIONS) {
    try {
      options = JSON.parse(process.env.YANDEX_METRIKA_OPTIONS)
    } catch (e) {}
  }
  config.modules?.push(['yandex-metrika-module-nuxt3', {...options, id: process.env.YANDEX_METRIKA_ID}])
}

export default defineNuxtConfig(config)
