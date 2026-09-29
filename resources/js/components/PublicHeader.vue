<template>
  <div>
    <div class="pm-public-topbar">
      <div class="container pm-public-topbar-inner">
        <div class="pm-public-topbar-left">
          <span class="pm-public-topbar-item">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.1.37 2.28.56 3.48.56a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.3 21 3 13.7 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.2.19 2.38.56 3.48a1 1 0 0 1-.24 1.01l-2.2 2.3z"/>
            </svg>
            <span>+1 (888) 555-1234</span>
          </span>
          <span class="pm-public-topbar-item">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2zm0 2 8 5 8-5"/>
            </svg>
            <span>support@contractorpro.com</span>
          </span>
        </div>

        <div class="pm-public-topbar-right">
          <RouterLink class="pm-public-topbar-link" to="/login">Login</RouterLink>
          <RouterLink class="pm-public-topbar-link" to="/register">Register</RouterLink>
        </div>
      </div>
    </div>

    <nav class="pm-public-navbar">
      <div class="container pm-public-navbar-inner">
        <RouterLink class="pm-public-brand" to="/">
          <span class="pm-public-brand-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M3 10.5 12 4l9 6.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10.5z"/>
            </svg>
          </span>
          <span class="pm-public-brand-text">Contractor<span>Pro</span></span>
        </RouterLink>

        <button
          class="pm-public-menu-toggle"
          type="button"
          aria-controls="pmPublicNav"
          :aria-expanded="isMenuOpen"
          aria-label="Toggle navigation"
          @click="isMenuOpen = !isMenuOpen"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>

        <div :class="['pm-public-nav-panel', { show: isMenuOpen }]" id="pmPublicNav">
          <ul class="pm-public-nav-list">
            <li><RouterLink class="pm-public-nav-link" to="/" @click="closeMenu">{{ t('publicHeader.home') }}</RouterLink></li>
            <li><RouterLink class="pm-public-nav-link" to="/about" @click="closeMenu">{{ t('publicHeader.about') }}</RouterLink></li>
            <li><RouterLink class="pm-public-nav-link" to="/plans" @click="closeMenu">Features</RouterLink></li>
            <li><RouterLink class="pm-public-nav-link" to="/about" @click="closeMenu">Industries</RouterLink></li>
            <li><RouterLink class="pm-public-nav-link" to="/plans" @click="closeMenu">Pricing Plans</RouterLink></li>
            <li><RouterLink class="pm-public-nav-link" to="/contact" @click="closeMenu">{{ t('publicHeader.contact') }}</RouterLink></li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const isMenuOpen = ref(false)

const closeMenu = () => {
  isMenuOpen.value = false
}
</script>

<style scoped>
.pm-public-topbar {
  background: linear-gradient(90deg, #0f43bd 0%, #1448c9 100%);
  color: #ffffff;
}

.pm-public-topbar-inner {
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
}

.pm-public-topbar-left,
.pm-public-topbar-right {
  display: flex;
  align-items: center;
  gap: 24px;
}

.pm-public-topbar-item,
.pm-public-topbar-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #ffffff;
  font-size: 0.92rem;
  font-weight: 600;
}

.pm-public-topbar-item svg {
  width: 15px;
  height: 15px;
  fill: currentColor;
  stroke: currentColor;
  stroke-width: 1.2;
}

.pm-public-navbar {
  background: #ffffff;
  border-bottom: 1px solid #e7eef8;
}

.pm-public-navbar-inner {
  min-height: 82px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
}

.pm-public-brand {
  display: inline-flex;
  align-items: center;
  gap: 12px;
}

.pm-public-brand-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(180deg, #2563eb 0%, #155dfc 100%);
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 12px 26px rgba(37, 99, 235, 0.24);
}

.pm-public-brand-icon svg {
  width: 22px;
  height: 22px;
  fill: currentColor;
}

.pm-public-brand-text {
  color: #12234a;
  font-size: 1.3rem;
  font-weight: 800;
}

.pm-public-brand-text span {
  color: #2563eb;
}

.pm-public-nav-panel {
  display: flex;
}

.pm-public-nav-list {
  list-style: none;
  display: flex;
  align-items: center;
  gap: 28px;
  margin: 0;
  padding: 0;
}

.pm-public-nav-link {
  position: relative;
  color: #1d2c4a;
  font-size: 1rem;
  font-weight: 700;
  padding: 10px 0;
}

.pm-public-nav-link.router-link-exact-active,
.pm-public-nav-link:hover {
  color: #2563eb;
}

.pm-public-nav-link.router-link-exact-active::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: -18px;
  height: 3px;
  border-radius: 999px 999px 0 0;
  background: #2563eb;
}

.pm-public-menu-toggle {
  display: none;
  width: 42px;
  height: 42px;
  border: 1px solid #dbe5f1;
  border-radius: 12px;
  background: #ffffff;
  padding: 9px;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
}

.pm-public-menu-toggle span {
  display: block;
  width: 100%;
  height: 2px;
  border-radius: 999px;
  background: #12234a;
}

@media (max-width: 991.98px) {
  .pm-public-topbar-inner,
  .pm-public-topbar-left,
  .pm-public-topbar-right {
    gap: 12px;
  }

  .pm-public-topbar-inner {
    flex-direction: column;
    align-items: flex-start;
    padding-top: 8px;
    padding-bottom: 8px;
  }

  .pm-public-navbar-inner {
    min-height: 74px;
  }

  .pm-public-menu-toggle {
    display: inline-flex;
  }

  .pm-public-nav-panel {
    display: none;
    position: absolute;
    left: 18px;
    right: 18px;
    top: calc(100% + 8px);
    background: #ffffff;
    border: 1px solid #e7eef8;
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    padding: 16px;
  }

  .pm-public-nav-panel.show {
    display: block;
  }

  .pm-public-navbar {
    position: relative;
  }

  .pm-public-nav-list {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .pm-public-nav-link.router-link-exact-active::after {
    display: none;
  }
}
</style>
