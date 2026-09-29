<template>
  <div class="pm-pricing-page">
    <PublicHeader />

    <section class="pm-pricing-hero">
      <div class="container">
        <div class="pm-pricing-head">
          <div class="pm-pricing-kicker">PRICING PLANS</div>
          <h1>Simple, Transparent Pricing</h1>
          <p>Choose the plan that fits your business needs.</p>
          <div class="pm-pricing-subnote">
            All plans include core features to help you manage jobs, teams, customers, and finances.
          </div>

          <div class="pm-pricing-toggle-wrap">
            <div class="pm-pricing-toggle">
              <button
                type="button"
                class="pm-pricing-toggle-btn"
                :class="{ active: billingCycle === 'monthly' }"
                @click="billingCycle = 'monthly'"
              >
                Monthly
              </button>
              <button
                type="button"
                class="pm-pricing-toggle-btn"
                :class="{ active: billingCycle === 'yearly' }"
                @click="billingCycle = 'yearly'"
              >
                Yearly (Save 5%)
              </button>
            </div>
            <div class="pm-pricing-savings-note">5% savings and we bill monthly</div>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-pricing-cards-section">
      <div class="container">
        <div class="pm-pricing-cards-grid">
          <div
            v-for="plan in plans"
            :key="plan.key"
            class="pm-pricing-card"
            :class="{ featured: plan.featured, selected: selectedPlan === plan.key }"
          >
            <div v-if="plan.featured" class="pm-pricing-popular-bar">MOST POPULAR</div>

            <div class="pm-pricing-card-inner">
              <div class="pm-pricing-card-head">
                <div class="pm-pricing-card-icon" :style="{ color: plan.iconColor }" v-html="plan.icon"></div>
                <div>
                  <h2>{{ plan.name }}</h2>
                  <p>{{ plan.description }}</p>
                </div>
              </div>

              <div class="pm-pricing-price-row">
                <template v-if="plan.price !== null">
                  <span class="pm-pricing-price">${{ displayPrice(plan.price) }}</span>
                  <span class="pm-pricing-price-unit">/month per license</span>
                </template>
                <template v-else>
                  <span class="pm-pricing-price">Custom</span>
                  <span class="pm-pricing-price-unit">/month per license</span>
                </template>
              </div>

              <div class="pm-pricing-billed-note">
                {{ billingCycle === 'monthly' ? 'Billed monthly' : 'Billed monthly at yearly savings rate' }}
              </div>

              <div class="pm-pricing-divider"></div>

              <ul class="pm-pricing-feature-list">
                <li v-for="item in plan.features" :key="item" class="pm-pricing-feature-item">
                  <span class="pm-pricing-check">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M20 6L9 17L4 12" />
                    </svg>
                  </span>
                  <span>{{ item }}</span>
                </li>
              </ul>

              <button
                class="btn pm-pricing-action-btn"
                :class="{ featured: plan.featured && selectedPlan !== plan.key, selected: selectedPlan === plan.key }"
                type="button"
                @click="selectPlan(plan.key)"
              >
                {{
                  selectedPlan === plan.key
                    ? 'Selected'
                    : plan.price === null
                      ? 'Contact Sales'
                      : 'Get Started'
                }}
              </button>
            </div>
          </div>
        </div>

        <div class="pm-pricing-note-bar">
          <div>Prices do not include tax. Tax will be added at checkout as applicable.</div>
          <div>This is the price per license. Add the number of licenses you need during checkout.</div>
        </div>

        <div class="pm-pricing-includes">
          <h3>All Plans Include</h3>
          <div class="pm-pricing-includes-grid">
            <div v-for="item in includes" :key="item.title" class="pm-pricing-include-item">
              <div class="pm-pricing-include-icon" v-html="item.icon"></div>
              <div>
                <div class="pm-pricing-include-title">{{ item.title }}</div>
                <div class="pm-pricing-include-text">{{ item.text }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <PublicFooter />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import PublicHeader from '../components/PublicHeader.vue'
import PublicFooter from '../components/PublicFooter.vue'
import client from '../api/client'
import { authState, setUser } from '../store/auth'

const selectedPlan = ref('business')
const billingCycle = ref('monthly')

const icons = {
  starter: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2 9 9l-7 3 7 3 3 7 3-7 7-3-7-3-3-7Zm0 5.2 1.34 3.12L16.46 12l-3.12 1.68L12 16.8l-1.34-3.12L7.54 12l3.12-1.68L12 7.2Z"/></svg>',
  professional: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10 4V3a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1h5a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h5Zm2-1v1h0V3Zm9 8v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9h18Z"/></svg>',
  business: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-8 1a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm8 2c-2.67 0-8 1.34-8 4v2h14v-2c0-2.66-5.33-4-8-4ZM8 14c-.29 0-.62.02-.97.05C4.8 14.31 2 15.3 2 17v3h4v-2c0-1.48.8-2.6 1.97-3.45A12.9 12.9 0 0 0 8 14Z"/></svg>',
  enterprise: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 21V5a2 2 0 0 1 2-2h7v18H3Zm10 0V9h6a2 2 0 0 1 2 2v10h-8ZM6 6v2h2V6H6Zm0 4v2h2v-2H6Zm0 4v2h2v-2H6Zm4-8v2h2V6h-2Zm0 4v2h2v-2h-2Z"/></svg>',
  cloud: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 18H6a4 4 0 1 1 .6-7.96A6 6 0 0 1 18 11a3.5 3.5 0 0 1 1 7Z"/></svg>',
  mobile: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm5 18a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM7 5v11h10V5H7Z"/></svg>',
  shield: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2 5 5v6c0 5 3.4 9.7 7 11 3.6-1.3 7-6 7-11V5l-7-3Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 12.2A8.95 8.95 0 0 1 7 11.1V6.3l5-2.1 5 2.1v4.8a8.95 8.95 0 0 1-5 8.1Z"/></svg>',
  refresh: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.65 6.35A7.95 7.95 0 0 0 12 4V1L7 6l5 5V7a5 5 0 1 1-4.9 6h-2.02A7 7 0 1 0 17.65 6.35Z"/></svg>',
  speed: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 4a10 10 0 1 0 10 10A10 10 0 0 0 12 4Zm0 2a8 8 0 0 1 7.75 6H18.2a6.5 6.5 0 0 0-12.4 0H4.25A8 8 0 0 1 12 6Zm-1 8 5-5 1.41 1.41-5 5A2 2 0 1 1 11 14Z"/></svg>',
}

const plans = [
  {
    key: 'starter',
    name: 'Starter',
    description: 'Perfect for small contractors getting started.',
    price: 29,
    icon: icons.starter,
    iconColor: '#111827',
    features: ['Up to 3 Users', 'Unlimited Jobs', 'Job Scheduling', 'Invoicing & Payments', 'Basic Reports', 'Email Support'],
    featured: false,
  },
  {
    key: 'professional',
    name: 'Professional',
    description: 'Great for growing businesses that need more power.',
    price: 59,
    icon: icons.professional,
    iconColor: '#111827',
    features: ['Up to 10 Users', 'Unlimited Jobs', 'Job Scheduling', 'Invoicing & Payments', 'Advanced Reports', 'Customer Management', 'Email & Chat Support'],
    featured: false,
  },
  {
    key: 'business',
    name: 'Business',
    description: 'Ideal for established contractors managing multiple projects.',
    price: 99,
    icon: icons.business,
    iconColor: '#111827',
    features: ['Up to 25 Users', 'Unlimited Jobs', 'Job Scheduling', 'Invoicing & Payments', 'Advanced Reports', 'Customer Management', 'Timesheets', 'Priority Support', 'Custom Roles & Permissions'],
    featured: true,
  },
  {
    key: 'enterprise',
    name: 'Enterprise',
    description: 'Built for large teams with complex needs.',
    price: null,
    icon: icons.enterprise,
    iconColor: '#111827',
    features: ['Unlimited Users', 'Unlimited Jobs', 'Job Scheduling', 'Invoicing & Payments', 'Advanced Reports', 'Customer Management', 'Timesheets', 'Priority Support', 'Custom Roles & Permissions', 'API Access', 'Dedicated Account Manager'],
    featured: false,
  },
]

const includes = [
  { title: 'Secure Cloud Storage', text: '', icon: icons.cloud },
  { title: 'Mobile Friendly Access', text: '', icon: icons.mobile },
  { title: 'Data Backup & Recovery', text: '', icon: icons.shield },
  { title: 'Regular Feature Updates', text: '', icon: icons.refresh },
  { title: '99.9% Uptime Guarantee', text: '', icon: icons.speed },
]

const yearlyMultiplier = computed(() => 0.95)

const displayPrice = (price) => {
  if (billingCycle.value === 'yearly') {
    return Math.round(price * yearlyMultiplier.value)
  }
  return price
}

const selectPlan = (planKey) => {
  if (selectedPlan.value === planKey) {
    selectedPlan.value = ''
    return
  }
  selectedPlan.value = planKey
  if (!authState.token || planKey === 'enterprise') return
  client
    .post('/plans/select', { plan: planKey })
    .then((res) => {
      if (res?.data?.data?.selected_plan && authState.user) {
        setUser({ ...authState.user, selected_plan: res.data.data.selected_plan })
      }
    })
    .catch(() => {
      // ignore API errors for public users
    })
}

onMounted(() => {
  if (authState.user?.selected_plan) {
    selectedPlan.value = authState.user.selected_plan
  }
})
</script>

<style scoped>
.pm-pricing-page {
  background: #ffffff;
}

.pm-pricing-hero {
  padding: 12px 0 8px;
  background:
    radial-gradient(760px 420px at 8% 12%, rgba(37, 99, 235, 0.08), transparent 60%),
    radial-gradient(620px 340px at 92% 16%, rgba(59, 130, 246, 0.08), transparent 60%),
    #ffffff;
}

.pm-pricing-head {
  text-align: center;
}

.pm-pricing-kicker {
  display: inline-flex;
  align-items: center;
  font-size: 0.82rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 10px;
}

.pm-pricing-head h1 {
  margin: 0 0 10px;
  color: #111827;
  font-size: clamp(2.4rem, 4vw, 4rem);
  line-height: 1.08;
  letter-spacing: -0.04em;
}

.pm-pricing-head p,
.pm-pricing-subnote {
  color: #374151;
}

.pm-pricing-head p {
  margin: 0 0 8px;
  font-size: 1.15rem;
}

.pm-pricing-subnote {
  margin: 0;
  font-size: 0.98rem;
}

.pm-pricing-toggle-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
  margin-top: 18px;
  flex-wrap: wrap;
}

.pm-pricing-toggle {
  display: inline-flex;
  align-items: center;
  border: 1px solid #d9e1ec;
  border-radius: 999px;
  padding: 3px;
  background: #ffffff;
}

.pm-pricing-toggle-btn {
  min-width: 142px;
  border: 0;
  background: transparent;
  border-radius: 999px;
  padding: 10px 18px;
  color: #111827;
  font-weight: 700;
}

.pm-pricing-toggle-btn.active {
  background: #f3f4f6;
}

.pm-pricing-savings-note {
  color: #111827;
  font-weight: 600;
}

.pm-pricing-cards-section {
  padding: 6px 0 28px;
}

.pm-pricing-cards-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.pm-pricing-card {
  background: #ffffff;
  border: 1px solid #dce5f0;
  border-radius: 20px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.04);
  overflow: hidden;
}

.pm-pricing-card.selected {
  border-color: #111827;
}

.pm-pricing-popular-bar {
  background: #111827;
  color: #ffffff;
  text-align: center;
  font-size: 0.86rem;
  font-weight: 800;
  padding: 8px 12px;
}

.pm-pricing-card-inner {
  padding: 18px 18px 16px;
}

.pm-pricing-card-head {
  display: grid;
  grid-template-columns: 50px 1fr;
  gap: 12px;
  align-items: start;
}

.pm-pricing-card-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: #f8fafc;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pm-pricing-card-icon :deep(svg),
.pm-pricing-include-icon :deep(svg) {
  width: 24px;
  height: 24px;
  display: block;
}

.pm-pricing-card-head h2 {
  margin: 0 0 4px;
  color: #111827;
  font-size: 1.75rem;
}

.pm-pricing-card-head p,
.pm-pricing-billed-note,
.pm-pricing-feature-item,
.pm-pricing-note-bar,
.pm-pricing-include-text {
  color: #374151;
}

.pm-pricing-card-head p {
  margin: 0;
  font-size: 0.96rem;
  line-height: 1.55;
}

.pm-pricing-price-row {
  margin-top: 18px;
  display: flex;
  align-items: flex-end;
  gap: 6px;
  flex-wrap: wrap;
}

.pm-pricing-price {
  color: #111827;
  font-size: 2.6rem;
  line-height: 1;
  font-weight: 800;
}

.pm-pricing-price-unit {
  color: #111827;
  font-weight: 600;
  margin-bottom: 4px;
}

.pm-pricing-billed-note {
  margin-top: 6px;
  font-size: 0.92rem;
  font-weight: 600;
}

.pm-pricing-divider {
  height: 1px;
  background: #e5edf7;
  margin: 14px 0 12px;
}

.pm-pricing-feature-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 10px;
  min-height: 260px;
}

.pm-pricing-feature-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-weight: 600;
}

.pm-pricing-check {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  color: #111827;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  margin-top: 2px;
}

.pm-pricing-check svg {
  width: 14px;
  height: 14px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2.6;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.pm-pricing-action-btn {
  width: 100%;
  min-height: 48px;
  border-radius: 12px;
  border: 1px solid #111827;
  background: #ffffff;
  color: #111827;
  font-weight: 800;
  margin-top: 18px;
}

.pm-pricing-action-btn.featured {
  background: #111827;
  color: #ffffff;
}

.pm-pricing-action-btn.selected {
  background: #2563eb;
  border-color: #2563eb;
  color: #ffffff;
}

.pm-pricing-note-bar {
  margin-top: 14px;
  border: 1px solid #dce5f0;
  border-radius: 16px;
  background: #ffffff;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0;
  overflow: hidden;
}

.pm-pricing-note-bar > div {
  padding: 14px 18px;
  text-align: center;
  font-weight: 600;
}

.pm-pricing-note-bar > div + div {
  border-left: 1px solid #dce5f0;
}

.pm-pricing-includes {
  margin-top: 18px;
}

.pm-pricing-includes h3 {
  margin: 0 0 14px;
  text-align: center;
  color: #111827;
  font-size: 1.6rem;
}

.pm-pricing-includes-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  border-top: 1px solid #e5edf7;
}

.pm-pricing-include-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
}

.pm-pricing-include-item + .pm-pricing-include-item {
  border-left: 1px solid #e5edf7;
}

.pm-pricing-include-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: #f8fafc;
  color: #111827;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
}

.pm-pricing-include-title {
  color: #111827;
  font-weight: 800;
}

@media (max-width: 1399.98px) {
  .pm-pricing-cards-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-pricing-includes-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 991.98px) {
  .pm-pricing-note-bar,
  .pm-pricing-includes-grid {
    grid-template-columns: 1fr;
  }

  .pm-pricing-note-bar > div + div,
  .pm-pricing-include-item + .pm-pricing-include-item {
    border-left: 0;
    border-top: 1px solid #e5edf7;
  }
}

@media (max-width: 767.98px) {
  .pm-pricing-cards-grid {
    grid-template-columns: 1fr;
  }

  .pm-pricing-card-head h2 {
    font-size: 1.45rem;
  }

  .pm-pricing-price {
    font-size: 2.1rem;
  }
}
</style>
