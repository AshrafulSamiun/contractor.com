<template>
  <div>
    <div class="pm-topbar-mini py-2">
      <div class="container d-flex justify-content-between">
        <div>info@contractor.com</div>
        <div class="pm-social pm-social-right">
          <LanguageSwitcher compact :show-label="false" />
          <span>+1 (555) 123-4567</span>
          <a href="https://facebook.com" target="_blank" rel="noopener">fb</a>
          <a href="https://twitter.com" target="_blank" rel="noopener">tw</a>
          <a href="https://linkedin.com" target="_blank" rel="noopener">in</a>
        </div>
      </div>
    </div>

    <nav class="navbar pm-navbar">
      <div class="container pm-navbar-inner">
        <RouterLink class="navbar-brand text-white fw-semibold d-flex align-items-center gap-2" to="/">
          <img :src="logoWhite" alt="Contractor logo" style="height:50px" />
          <span>Contractor.com</span>
        </RouterLink>
        <button
          class="navbar-toggler"
          type="button"
          aria-controls="pmNav"
          :aria-expanded="isMenuOpen"
          aria-label="Toggle navigation"
          @click="isMenuOpen = !isMenuOpen"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div :class="['pm-mobile-menu', { show: isMenuOpen }]" id="pmNav">
          <ul class="navbar-nav pm-nav-center align-items-lg-center">
            <li class="nav-item"><RouterLink class="nav-link" to="/" @click="closeMenu">{{ t('publicHeader.home') }}</RouterLink></li>
            <li class="nav-item"><RouterLink class="nav-link" to="/about" @click="closeMenu">{{ t('publicHeader.about') }}</RouterLink></li>
            <li class="nav-item"><RouterLink class="nav-link" to="/plans" @click="closeMenu">{{ t('publicHeader.plans') }}</RouterLink></li>
            <li class="nav-item"><RouterLink class="nav-link" to="/contact" @click="closeMenu">{{ t('publicHeader.contact') }}</RouterLink></li>
          </ul>
          <ul class="navbar-nav pm-nav-auth align-items-lg-center">
            <li v-if="isAuthed" class="nav-item">
              <RouterLink class="nav-link" to="/dashboard" @click="closeMenu">{{ t('publicHeader.dashboard') }}</RouterLink>
            </li>
            <li v-if="!isAuthed" class="nav-item">
              <RouterLink class="nav-link" to="/login" @click="closeMenu">{{ t('publicHeader.login') }}</RouterLink>
            </li>
            <li v-if="!isAuthed" class="nav-item ms-lg-2">
              <RouterLink class="nav-link pm-nav-cta" to="/register" @click="closeMenu">{{ t('publicHeader.createAccount') }}</RouterLink>
            </li>
            <li v-if="isAuthed" class="nav-item ms-lg-2 d-flex align-items-center gap-2">
              <img v-if="avatarUrl" :src="avatarUrl" alt="User avatar" class="pm-avatar-img" />
              <span v-else class="pm-avatar">{{ initials }}</span>
              <span class="text-white small">{{ displayName }}</span>
              <button class="btn btn-outline-light btn-sm" type="button" @click="showAvatarModal = true">{{ t('publicHeader.edit') }}</button>
              <button class="btn btn-light" type="button" @click="onLogout">{{ t('common.logout') }}</button>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <AvatarModal v-if="showAvatarModal" @close="showAvatarModal = false" />
  </div>
</template>

<script setup>
import logoWhite from '../assets/logo-white.png'
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { authState } from '../store/auth'
import { logout, clearToken } from '../api/auth'
import { setFlash } from '../store/flash'
import { FLASH } from '../config/messages'
import AvatarModal from './AvatarModal.vue'
import LanguageSwitcher from './LanguageSwitcher.vue'

const router = useRouter()
const { t } = useI18n()
const isAuthed = computed(() => !!authState.token)
const displayName = computed(() => authState.user?.name || authState.user?.email || 'User')
const avatarUrl = computed(() => authState.user?.image_url || authState.user?.avatar_url || '')
const showAvatarModal = ref(false)
const isMenuOpen = ref(false)
const initials = computed(() => {
  const name = displayName.value || ''
  const parts = name.trim().split(/\s+/).slice(0, 2)
  if (!parts.length) return 'U'
  return parts.map((p) => p[0].toUpperCase()).join('')
})

const closeMenu = () => {
  isMenuOpen.value = false
}

const onLogout = async () => {
  try {
    await logout()
  } catch {
    // ignore logout errors
  } finally {
    clearToken()
    setFlash(FLASH.LOGOUT_SUCCESS, 'info', 2000)
    router.push('/login')
  }
}
</script>
