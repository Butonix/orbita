<template>
  <div>
    <NuxtLoadingIndicator color="var(--bs-primary)" />

    <div id="layout" :class="mainClasses">
      <AppNavbar class="border-bottom" :sidebar="!isAdmin" />

      <div v-if="isColumns" class="main-background">
        <BImg :src="background" height="240" />
      </div>

      <BContainer class="pt-4 flex-grow-1">
        <div v-if="isColumns">
          <BRow class="main-columns">
            <BCol md="8" :class="{offset: $isMobile}">
              <slot>
                <NuxtPage />
              </slot>
            </BCol>
            <BCol v-if="!$isMobile" md="4" class="offset">
              <div v-if="!hideWidgets.includes('author')" class="column">
                <WidgetsAuthor />
              </div>
              <WidgetsSearch v-if="!hideWidgets.includes('search')" class="column" />
              <WidgetsOnline v-if="!hideWidgets.includes('online')" class="column" />
              <WidgetsLevels v-if="!hideWidgets.includes('levels')" class="column" />
              <WidgetsCategories v-if="!hideWidgets.includes('categories')" class="column" />
              <WidgetsTags v-if="!hideWidgets.includes('tags')" class="column" />
              <WidgetsScrollTop />
            </BCol>
          </BRow>
        </div>
        <div v-else>
          <slot>
            <NuxtPage />
          </slot>
        </div>
      </BContainer>

      <AppSidebar
        v-if="$isMobile"
        :show-pages="!hideWidgets.includes('pages')"
        :show-author="!hideWidgets.includes('author')"
        :show-search="!hideWidgets.includes('search')"
        :show-online="!hideWidgets.includes('online')"
        :show-levels="!hideWidgets.includes('levels')"
        :show-categories="!hideWidgets.includes('categories')"
        :show-tags="!hideWidgets.includes('tags')"
      />
      <AppFooter class="border-top" />
      <AppPayment />
    </div>
  </div>
</template>

<script setup lang="ts">
const {$settings, $variables, $image, $isMobile} = useNuxtApp()
const router = useRouter()
const route = useRoute()
const isColumns = computed(() => {
  const route = router.currentRoute.value?.name as string
  return route && (route === 'index' || route.startsWith('topics'))
})
const isAdmin = computed(() => {
  const route = router.currentRoute.value?.name as string
  return route && route.startsWith('admin')
})
const background = computed(() => {
  const bg = $settings.value.background as VespFile
  return bg ? $image(bg, {h: 480, fit: 'crop-center'}) : ''
})
const hideWidgets = computed(() => {
  const data = $variables.value?.HIDE_WIDGETS?.split(',').map((i) => i.trim().toLowerCase())
  if ($variables.value?.COMMENTS_SHOW_ONLINE === '0') {
    data.push('online')
  }
  return data
})

const mainClasses = computed(() => {
  const arr = ['d-flex', 'flex-column', 'min-vh-100']
  if (isColumns.value) {
    arr.push('columns')
  }
  if (route.name === 'index') {
    arr.push('main-page')
  }
  return arr
})

function handleResize() {
  const width = import.meta.client ? window.innerWidth : 768
  $isMobile.value = width < 768
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

useHead(() => ({
  link: [{rel: 'canonical', href: $variables.value.SITE_URL + route.path.slice(1)}],
}))

const description = stripTags(String($settings.value.description))
useSeoMeta({
  title: $settings.value.title as string,
  ogTitle: $settings.value.title as string,
  description,
  ogDescription: description,
  ogImage: $settings.value.poster ? $image($settings.value.poster as VespFile) : undefined,
  twitterCard: 'summary_large_image',
})
</script>
