<template>
  <div>
    <PublicHeader />

    <section class="pm-plan-hero">
      <div class="container">
        <h1 class="fw-semibold">Price & Plan</h1>
      </div>
    </section>

    <section class="pm-section">
      <div class="container text-center">
        <h2 class="fw-semibold">Find Your Perfect Plan</h2>
        <p class="pm-muted">Discover the ideal plan from here. here is our pricing also you can get support from our support team if you have any issues for the plan.</p>
        <div class="pm-plan-table-wrap mt-4">
          <h4 class="fw-semibold mb-1">Contractor Service Plans</h4>
          <div class="pm-muted mb-3">Choose the plan that best fits your organization's needs</div>
          <div class="table-responsive">
            <table class="table pm-plan-table text-start">
              <thead>
                <tr>
                  <th class="pm-plan-head">Plan Name</th>
                  <th class="pm-plan-col text-center">Basic Plan</th>
                  <th class="pm-plan-col text-center pm-plan-highlight">
                    <span class="pm-popular-badge">Most Popular</span>
                    <div>Standard Plan</div>
                  </th>
                  <th class="pm-plan-col text-center">Enterprise Plan</th>
                </tr>
                <tr>
                  <th class="pm-plan-head">Price (per user/month)</th>
                  <th class="text-center">$15 USD</th>
                  <th class="text-center pm-plan-highlight">$20 USD</th>
                  <th class="text-center">$25 USD</th>
                </tr>
                <tr>
                  <th></th>
                  <th class="text-center">
                    <button
                      class="btn btn-sm"
                      :class="selectedPlan === 'basic' ? 'btn-primary' : 'btn-outline-primary'"
                      type="button"
                      @click="selectPlan('basic')"
                    >
                      {{ selectedPlan === 'basic' ? 'Selected' : 'Select Plan' }}
                    </button>
                  </th>
                  <th class="text-center pm-plan-highlight">
                    <button
                      class="btn btn-sm"
                      :class="selectedPlan === 'standard' ? 'btn-primary' : 'btn-outline-primary'"
                      type="button"
                      @click="selectPlan('standard')"
                    >
                      {{ selectedPlan === 'standard' ? 'Selected' : 'Select Plan' }}
                    </button>
                  </th>
                  <th class="text-center">
                    <button
                      class="btn btn-sm"
                      :class="selectedPlan === 'enterprise' ? 'btn-primary' : 'btn-outline-primary'"
                      type="button"
                      @click="selectPlan('enterprise')"
                    >
                      {{ selectedPlan === 'enterprise' ? 'Selected' : 'Select Plan' }}
                    </button>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in planFeatureRows" :key="row.label">
                  <td>{{ row.label }}</td>
                  <td class="text-center">
                    <span :class="row.basic ? 'pm-check' : 'pm-x'">
                      <span v-if="row.basic">&#10003;</span>
                      <span v-else>&#10005;</span>
                    </span>
                  </td>
                  <td class="text-center pm-plan-highlight">
                    <span :class="row.standard ? 'pm-check' : 'pm-x'">
                      <span v-if="row.standard">&#10003;</span>
                      <span v-else>&#10005;</span>
                    </span>
                  </td>
                  <td class="text-center">
                    <span :class="row.enterprise ? 'pm-check' : 'pm-x'">
                      <span v-if="row.enterprise">&#10003;</span>
                      <span v-else>&#10005;</span>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pm-plan-notes mt-4 text-start">
            <div class="fw-semibold">Notes:</div>
            <div>Fees are exclusive of taxes or additional charges.</div>
            <div>Fees are per user/license.</div>
            <div>Fees are non-refundable.</div>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-terms py-5">
      <div class="container text-center">
        <h3 class="fw-semibold">Terms & Conditions</h3>
        <ul class="mt-3 d-inline-block text-start">
          <li class="d-flex align-items-start mb-2">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2 flex-shrink-0">
              <path d="M12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="currentColor"/>
            </svg>
            <span>Terms & Conditions provisions for subscription plans</span>
          </li>
          <li class="d-flex align-items-start mb-2">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2 flex-shrink-0">
              <path d="M12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="currentColor"/>
            </svg>
            <span>Payment terms</span>
          </li>
          <li class="d-flex align-items-start mb-2">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2 flex-shrink-0">
              <path d="M12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="currentColor"/>
            </svg>
            <span>Non-refundable fees</span>
          </li>
          <li class="d-flex align-items-start mb-2">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2 flex-shrink-0">
              <path d="M12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="currentColor"/>
            </svg>
            <span>Restrictions on use</span>
          </li>
          <li class="d-flex align-items-start mb-2">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2 flex-shrink-0">
              <path d="M12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="currentColor"/>
            </svg>
            <span>Returns and refunds</span>
          </li>
          <li class="d-flex align-items-start">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2 flex-shrink-0">
              <path d="M12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="currentColor"/>
            </svg>
            <span>Termination of subscription</span>
          </li>
        </ul>
      </div>
    </section>

    <section class="pm-section">
      <div class="container">
        <h5 class="fw-semibold">Payment & Subscription Information</h5>
        <p class="pm-muted">If you sell products or services online, you may wish to specify the methods of payment that you accept, including your use of third-party payment providers if any. Similarly, if you operate based on a subscription method, you should set out when payment will be taken and at what frequency.</p>
        <p class="pm-muted">Your customers should be able to refer to your terms to understand how they will be charged if they choose to cancel their subscription and when they will still be able to access your services.</p>
        <p class="pm-muted">If you offer a free trial period, you need to be clear as to what happens at the end of that timeframe. Will their credit card automatically be charged unless they cancel prior to the end date, and will it continue to be charged monthly unless they terminate their subscription?</p>
        <p class="pm-muted">Writing this down in plain language prevents bad surprises and ensures customers understand the terms they accept.</p>
      </div>
    </section>

    <section class="pm-section">
      <div class="container">
        <div class="pm-plan-cta">
          <div>
            <h4 class="fw-semibold mb-1">Need help choosing?</h4>
            <p class="pm-muted mb-0">Get a tailored recommendation and onboarding plan from our team.</p>
          </div>
          <div class="pm-plan-cta-actions">
            <RouterLink class="btn btn-primary px-4" :to="`/register?plan=${selectedPlan}`">Buy Now</RouterLink>
            <RouterLink class="btn btn-outline-primary px-4" to="/contact">Contact Sales</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <PublicFooter />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import PublicHeader from '../components/PublicHeader.vue'
import PublicFooter from '../components/PublicFooter.vue'
import { RouterLink } from 'vue-router'
import client from '../api/client'
import { authState, setUser } from '../store/auth'

const selectedPlan = ref('standard')

const planFeatureRows = [
  { label: 'Parcel Logging (record arrivals and departures)', basic: true, standard: true, enterprise: true },
  { label: 'Track Parcel Handovers (who received what)', basic: true, standard: true, enterprise: true },
  { label: 'Update Parcel Status (Picked Up, Delivered)', basic: true, standard: true, enterprise: true },
  { label: 'Manage Storage Locations (Back Office, Lockers)', basic: false, standard: true, enterprise: true },
  { label: 'Confirm Parcel Pickups', basic: false, standard: true, enterprise: true },
  { label: 'Track Pending or Held Parcels', basic: false, standard: true, enterprise: true },
  { label: 'Track External Lockers (third-party lockers)', basic: false, standard: false, enterprise: true },
  { label: 'Delivery by Staff or Locker Access Management', basic: false, standard: false, enterprise: true },
  { label: 'Record Parcel Final Outcomes (Returned, Lost, Damaged)', basic: false, standard: false, enterprise: true },
  { label: 'Generate Reports & Basic Analytics', basic: false, standard: false, enterprise: true },
  { label: 'Text Notifications for Parcel Status Updates', basic: false, standard: false, enterprise: true },
  { label: 'Multi-language Support', basic: false, standard: true, enterprise: true },
  { label: 'User Management (add users, roles, active/inactive)', basic: false, standard: false, enterprise: true },
  { label: 'Staff Administrative Tasks (Daily & Incident Reports)', basic: false, standard: false, enterprise: true },
  { label: 'Full Account & Security Management (Billing, MFA, Recovery)', basic: false, standard: false, enterprise: true },
]

const selectPlan = (planKey) => {
  selectedPlan.value = planKey
  if (!authState.token) return
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
