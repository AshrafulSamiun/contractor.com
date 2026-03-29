<template>
  <div>
    <PublicHeader />

    <section class="pm-contact-hero-alt">
      <div class="pm-hero-overlay"></div>
      <div class="pm-hero-glow"></div>
      <div class="container">
        <div class="pm-hero-inner">
          <div class="pm-hero-left">
            <div class="pm-eyebrow">Contact DeskDrop</div>
            <h1>World-class support for parcel operations</h1>
            <p class="pm-hero-subtitle">
              Whether you need onboarding, a technical fix, or account guidance, our team responds fast and stays with you
              end-to-end.
            </p>
            <div class="pm-hero-cta">
              <a class="btn pm-btn-solid px-4" href="tel:+15551234567">Call Sales</a>
              <a class="btn pm-btn-ghost px-4" href="mailto:hello@deskdrop.com">Email Support</a>
            </div>
            <div class="pm-hero-kpis">
              <div>
                <div class="pm-kpi-value">15 min</div>
                <div class="pm-muted">Priority response</div>
              </div>
              <div>
                <div class="pm-kpi-value">24/7</div>
                <div class="pm-muted">Critical coverage</div>
              </div>
              <div>
                <div class="pm-kpi-value">3 hubs</div>
                <div class="pm-muted">NA / EU / APAC</div>
              </div>
            </div>
          </div>
          <div class="pm-hero-right">
            <div class="pm-hero-panel">
              <div class="pm-hero-panel-title">Primary Office</div>
              <div class="pm-hero-panel-address">26985 Brighton Lane, Lake Forest, CA 92630</div>
              <div class="pm-hero-panel-row">
                <span>Sales</span>
                <strong>+1 (888) 888-888</strong>
              </div>
              <div class="pm-hero-panel-row">
                <span>Support</span>
                <strong>+1 (888) 777-777</strong>
              </div>
              <div class="pm-hero-panel-row">
                <span>Email</span>
                <strong>hello@deskdrop.com</strong>
              </div>
              <div class="pm-hero-panel-row">
                <span>Hours</span>
                <strong>Mon-Fri 08:00-18:00</strong>
              </div>
            </div>
            <div class="pm-hero-panel pm-hero-panel-alt">
              <div class="pm-hero-panel-title">Escalation Desk</div>
              <div class="pm-hero-panel-address">priority@deskdrop.com</div>
              <div class="pm-hero-panel-row">
                <span>SLA</span>
                <strong>60 minutes</strong>
              </div>
              <div class="pm-hero-panel-row">
                <span>Availability</span>
                <strong>Global</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-section">
      <div class="container">
        <div class="row g-4 align-items-start">
          <div class="col-lg-4">
            <div class="pm-map-card">
              <iframe
                src="https://www.google.com/maps?q=49.2827,-123.1207&z=12&output=embed"
                width="100%"
                height="300"
                style="border:0; border-radius:12px"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="pm-contact-panel">
              <div class="pm-panel-tabs mb-3">
                <button
                  v-for="region in regions"
                  :key="region.key"
                  class="btn btn-sm btn-light me-2"
                  :class="{ active: activeRegion === region.key }"
                  @click="setRegion(region.key)"
                >
                  {{ region.label }}
                </button>
              </div>

              <div class="pm-contact-panel-grid">
                <div
                  v-for="card in currentRegion.contacts"
                  :key="card.title"
                  class="pm-contact-card"
                >
                  <h6>{{ card.title }}</h6>
                  <div class="d-flex justify-content-between">
                    <div>
                      <div class="pm-muted text-white font-semibold text-sm">Phone</div>
                      <div v-for="p in card.phones" :key="p" class="pm-contact-line text-white text-[10px]">{{ p }}</div>
                    </div>
                    <div>
                      <div class="pm-muted  text-white font-semibold text-[12px]">Email</div>
                      <div v-for="e in card.emails" :key="e" class="pm-contact-line text-white text-[10px]">{{ e }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="pm-contact-us mt-4">
                <h6>CONTACT US</h6>
                <div class="d-flex flex-wrap gap-4 mt-2">
                  <div class="d-flex gap-2 align-items-start">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8 2 5 5 5 9c0 7 7 13 7 13s7-6 7-13c0-4-3-7-7-7z" fill="currentColor"/></svg>
                    <div>
                      <div class="pm-muted text-white" >26985 Brighton Lane, Lake Forest, CA 92630</div>
                    </div>
                  </div>
                  <div class="d-flex gap-2 align-items-start">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.62 10.79a15.091 15.091 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.21c1.2.48 2.5.74 3.85.74a1 1 0 011 1V20a1 1 0 01-1 1C9.39 21 3 14.61 3 6a1 1 0 011-1h2.5a1 1 0 011 1c0 1.35.26 2.65.74 3.85a1 1 0 01-.21 1.11l-2.41 2.83z" fill="currentColor"/></svg>
                    <div>
                      <div class="pm-muted text-white">Sales: +1 (888) 888-888</div>
                    </div>
                  </div>
                  <div class="d-flex gap-2 align-items-start">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/></svg>
                    <div>
                      <div class="pm-muted text-white" >info@contractor.com</div>
                    </div>
                  </div>
                </div>
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
import { reactive, ref, computed } from 'vue'
import PublicHeader from '../components/PublicHeader.vue'
import PublicFooter from '../components/PublicFooter.vue'
import client from '../api/client'

const contact = reactive({
  name: '',
  company: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const contactLoading = ref(false)
const contactSuccess = ref(false)
const contactError = ref('')

const submitContact = async () => {
  contactError.value = ''
  contactSuccess.value = false
  if (!contact.name || !contact.email || !contact.message) {
    contactError.value = 'Please fill in name, email, and message.'
    return
  }
  contactLoading.value = true
  try {
    const { data } = await client.post('/contact', contact)
    if (data?.success) {
      contactSuccess.value = true
      contact.name = ''
      contact.company = ''
      contact.email = ''
      contact.phone = ''
      contact.subject = ''
      contact.message = ''
    } else {
      contactError.value = data?.message || 'Failed to send message.'
    }
  } catch {
    contactError.value = 'Failed to send message.'
  } finally {
    contactLoading.value = false
  }
}

// Region data for contact panel — content-driven from array/object
const regions = ref([
  {
    key: 'north-america',
    label: 'North America',
    mapUrl: 'https://www.google.com/maps?q=49.2827,-123.1207&z=12&output=embed',
    contacts: [
      { title: 'SALES', phones: ['+1 212 555 5011', '+1 310 555 0173'], emails: ['sales.na1@contractor.com', 'sales.na2@contractor.com'] },
      { title: 'TECHNICAL SUPPORT', phones: ['+1 212 555 0211', '+1 310 555 0222'], emails: ['support.na1@contractor.com', 'support.na2@contractor.com'] },
      { title: 'BILLING', phones: ['+1 212 555 0311', '+1 310 555 0322'], emails: ['billing.na1@contractor.com', 'billing.na2@contractor.com'] },
      { title: 'CUSTOMER SERVICE', phones: ['+1 212 555 0411', '+1 310 555 0422'], emails: ['service.na1@contractor.com', 'service.na2@contractor.com'] },
    ],
  },
  {
    key: 'europe',
    label: 'Europe',
    mapUrl: 'https://www.google.com/maps?q=51.5074,-0.1278&z=5&output=embed',
    contacts: [
      { title: 'SALES', phones: ['+1 212 555 5011', '+1 310 555 0173'], emails: ['sales.na1@contractor.com', 'sales.na2@contractor.com'] },
      { title: 'TECHNICAL SUPPORT', phones: ['+1 212 555 0211', '+1 310 555 0222'], emails: ['support.na1@contractor.com', 'support.na2@contractor.com'] },
      { title: 'BILLING', phones: ['+1 212 555 0311', '+1 310 555 0322'], emails: ['billing.na1@contractor.com', 'billing.na2@contractor.com'] },
      { title: 'CUSTOMER SERVICE', phones: ['+1 212 555 0411', '+1 310 555 0422'], emails: ['service.na1@contractor.com', 'service.na2@contractor.com'] },
    ],
  },
  {
    key: 'australia',
    label: 'Australia',
    mapUrl: 'https://www.google.com/maps?q=-33.8688,151.2093&z=5&output=embed',
    contacts: [
      { title: 'SALES', phones: ['+1 212 555 5011', '+1 310 555 0173'], emails: ['sales.na1@contractor.com', 'sales.na2@contractor.com'] },
      { title: 'TECHNICAL SUPPORT', phones: ['+1 212 555 0211', '+1 310 555 0222'], emails: ['support.na1@contractor.com', 'support.na2@contractor.com'] },
      { title: 'BILLING', phones: ['+1 212 555 0311', '+1 310 555 0322'], emails: ['billing.na1@contractor.com', 'billing.na2@contractor.com'] },
      { title: 'CUSTOMER SERVICE', phones: ['+1 212 555 0411', '+1 310 555 0422'], emails: ['service.na1@contractor.com', 'service.na2@contractor.com'] },
    ],
  },
  {
    key: 'asia',
    label: 'Asia',
    mapUrl: 'https://www.google.com/maps?q=35.6895,139.6917&z=5&output=embed',
    contacts: [
      { title: 'SALES', phones: ['+1 212 555 5011', '+1 310 555 0173'], emails: ['sales.na1@contractor.com', 'sales.na2@contractor.com'] },
      { title: 'TECHNICAL SUPPORT', phones: ['+1 212 555 0211', '+1 310 555 0222'], emails: ['support.na1@contractor.com', 'support.na2@contractor.com'] },
      { title: 'BILLING', phones: ['+1 212 555 0311', '+1 310 555 0322'], emails: ['billing.na1@contractor.com', 'billing.na2@contractor.com'] },
      { title: 'CUSTOMER SERVICE', phones: ['+1 212 555 0411', '+1 310 555 0422'], emails: ['service.na1@contractor.com', 'service.na2@contractor.com'] },
    ],
  },
  {
    key: 'south-america',
    label: 'South America',
    mapUrl: 'https://www.google.com/maps?q=-23.5505,-46.6333&z=5&output=embed',
    contacts: [
      { title: 'SALES', phones: ['+1 212 555 5011', '+1 310 555 0173'], emails: ['sales.na1@contractor.com', 'sales.na2@contractor.com'] },
      { title: 'TECHNICAL SUPPORT', phones: ['+1 212 555 0211', '+1 310 555 0222'], emails: ['support.na1@contractor.com', 'support.na2@contractor.com'] },
      { title: 'BILLING', phones: ['+1 212 555 0311', '+1 310 555 0322'], emails: ['billing.na1@contractor.com', 'billing.na2@contractor.com'] },
      { title: 'CUSTOMER SERVICE', phones: ['+1 212 555 0411', '+1 310 555 0422'], emails: ['service.na1@contractor.com', 'service.na2@contractor.com'] },
    ],
  },
  { key: 'africa', label: 'Africa', mapUrl: 'https://www.google.com/maps?q=0,20&z=3&output=embed', contacts: [] },
])

const activeRegion = ref('north-america')
const currentRegion = computed(() => regions.value.find((r) => r.key === activeRegion.value) || regions.value[0])

const setRegion = (key) => {
  activeRegion.value = key
}
</script>
