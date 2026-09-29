<template>
  <div class="pm-contact-page">
    <PublicHeader />

    <section class="pm-contact-hero">
      <div class="container">
        <div class="pm-contact-hero-copy">
          <div class="pm-contact-kicker">CONTACT US</div>
          <h1>We’re Here to Help</h1>
          <p>Have questions or need assistance? Get in touch with our team.</p>
        </div>
      </div>
    </section>

    <section class="pm-contact-main">
      <div class="container">
        <div class="pm-contact-grid">
          <div class="pm-contact-form-card">
            <div class="pm-contact-card-head">
              <div class="pm-contact-card-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path fill="currentColor" d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm3 6h10V8H7v2Zm0 4h7v-2H7v2Z"/>
                </svg>
              </div>
              <div>
                <h2>Send Us a Message</h2>
                <p>Fill out the form below and we’ll get back to you as soon as possible.</p>
              </div>
            </div>

            <div v-if="contactSuccess" class="pm-form-success">Thanks! We will reach out shortly.</div>
            <div v-if="contactError" class="pm-form-error">{{ contactError }}</div>

            <form class="pm-contact-form-grid" @submit.prevent="submitContact">
              <div class="pm-contact-field">
                <label>Full Name *</label>
                <input v-model="contact.name" class="form-control" placeholder="Enter your full name" />
              </div>

              <div class="pm-contact-field">
                <label>Email Address *</label>
                <input v-model="contact.email" class="form-control" placeholder="Enter your email address" />
              </div>

              <div class="pm-contact-field">
                <label>Company Name</label>
                <input v-model="contact.company" class="form-control" placeholder="Enter your company name" />
              </div>

              <div class="pm-contact-field">
                <label>Phone Number</label>
                <input v-model="contact.phone" class="form-control" placeholder="Enter your phone number" />
              </div>

              <div class="pm-contact-field pm-contact-field-full">
                <label>Subject *</label>
                <select v-model="contact.subject" class="form-select">
                  <option value="">Select a subject</option>
                  <option>Product Demo</option>
                  <option>Technical Support</option>
                  <option>Pricing Question</option>
                  <option>General Inquiry</option>
                </select>
              </div>

              <div class="pm-contact-field pm-contact-field-full">
                <label>Message *</label>
                <textarea v-model="contact.message" class="form-control" rows="5" placeholder="How can we help you?"></textarea>
              </div>

              <div class="pm-contact-field-full">
                <button class="btn pm-contact-submit" type="submit" :disabled="contactLoading">
                  {{ contactLoading ? 'Sending...' : 'Send Message' }}
                </button>
              </div>
            </form>

            <div class="pm-contact-response-note">
              <span class="pm-contact-response-check">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M20 6L9 17L4 12" />
                </svg>
              </span>
              <span>We typically reply within 1 business day.</span>
            </div>
          </div>

          <div class="pm-contact-info-card">
            <div class="pm-contact-card-head">
              <div class="pm-contact-card-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.1.37 2.28.56 3.48.56a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.3 21 3 13.7 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.2.19 2.38.56 3.48a1 1 0 0 1-.24 1.01l-2.2 2.3z"/>
                </svg>
              </div>
              <div>
                <h2>Get in Touch</h2>
              </div>
            </div>

            <div class="pm-contact-info-list">
              <div v-for="item in contactInfo" :key="item.title" class="pm-contact-info-item">
                <div class="pm-contact-info-icon" :style="{ color: item.color }" v-html="item.icon"></div>
                <div>
                  <h3>{{ item.title }}</h3>
                  <p v-for="line in item.lines" :key="line">{{ line }}</p>
                </div>
              </div>
            </div>

            <div class="pm-contact-map">
              <iframe
                src="https://www.google.com/maps?q=123%20Main%20St,%20Austin,%20TX%2078701&z=14&output=embed"
                width="100%"
                height="100%"
                style="border:0"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
            </div>
          </div>
        </div>

        <div class="pm-contact-cta-strip">
          <div class="pm-contact-cta-left">
            <div class="pm-contact-cta-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M12 3a9 9 0 0 0-9 9v3a3 3 0 0 0 3 3h1v-7H6v-1a6 6 0 0 1 12 0v1h-1v7h1a3 3 0 0 0 3-3v-3a9 9 0 0 0-9-9Zm-1 15h2v2h-2v-2Z"/>
              </svg>
            </div>
            <div>
              <h3>Need help getting started?</h3>
              <p>Our team is happy to help you find the right plan and get set up.</p>
            </div>
          </div>

          <a class="btn pm-contact-demo-btn" href="mailto:support@contractorpro.com">Schedule a Demo</a>
        </div>
      </div>
    </section>

    <PublicFooter />
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
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

const iconSet = {
  phone: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.1.37 2.28.56 3.48.56a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.3 21 3 13.7 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.2.19 2.38.56 3.48a1 1 0 0 1-.24 1.01l-2.2 2.3z"/></svg>',
  mail: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 2 8 5 8-5"/></svg>',
  pin: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a7 7 0 0 1 7 7c0 4.2-4.7 9.95-6.3 11.77a1 1 0 0 1-1.4 0C9.7 18.95 5 13.2 5 9a7 7 0 0 1 7-7Zm0 4.5A2.5 2.5 0 1 0 12 11a2.5 2.5 0 0 0 0-4.5Z"/></svg>',
  clock: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm1 5h-2v6l5 3 .9-1.45-3.9-2.3V7Z"/></svg>',
}

const contactInfo = [
  {
    title: 'Phone',
    lines: ['+1 (888) 555-1234', 'Mon - Fri, 8:00 AM - 6:00 PM CT'],
    color: '#2563eb',
    icon: iconSet.phone,
  },
  {
    title: 'Email',
    lines: ['support@contractorpro.com', 'We’ll respond within 1 business day'],
    color: '#2563eb',
    icon: iconSet.mail,
  },
  {
    title: 'Address',
    lines: ['123 Main St, Suite 400', 'Austin, TX 78701, USA'],
    color: '#2563eb',
    icon: iconSet.pin,
  },
  {
    title: 'Business Hours',
    lines: ['Monday - Friday', '8:00 AM - 6:00 PM CT'],
    color: '#2563eb',
    icon: iconSet.clock,
  },
]

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
</script>

<style scoped>
.pm-contact-page {
  background: #ffffff;
}

.pm-contact-hero {
  padding: 18px 0 8px;
  background:
    radial-gradient(760px 420px at 8% 12%, rgba(37, 99, 235, 0.08), transparent 60%),
    radial-gradient(620px 340px at 92% 16%, rgba(59, 130, 246, 0.08), transparent 60%),
    #ffffff;
}

.pm-contact-hero-copy {
  text-align: center;
}

.pm-contact-kicker {
  display: inline-flex;
  align-items: center;
  padding: 8px 14px;
  border-radius: 999px;
  background: #edf3ff;
  color: #2563eb;
  font-size: 0.78rem;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 18px;
}

.pm-contact-hero-copy h1 {
  margin: 0 0 12px;
  color: #12234a;
  font-size: clamp(2rem, 4.1vw, 3.35rem);
  line-height: 1.04;
  letter-spacing: -0.04em;
}

.pm-contact-hero-copy p {
  margin: 0;
  color: #64748b;
  font-size: 0.95rem;
}

.pm-contact-main {
  padding: 10px 0 30px;
}

.pm-contact-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 16px;
}

.pm-contact-form-card,
.pm-contact-info-card {
  background: #ffffff;
  border: 1px solid #e7eef9;
  border-radius: 20px;
  padding: 22px;
  box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
}

.pm-contact-card-head {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  margin-bottom: 18px;
}

.pm-contact-card-icon,
.pm-contact-info-icon,
.pm-contact-cta-icon {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: linear-gradient(180deg, #eef4ff 0%, #e5edff 100%);
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
}

.pm-contact-card-icon svg,
.pm-contact-info-icon :deep(svg),
.pm-contact-cta-icon svg {
  width: 22px;
  height: 22px;
  display: block;
  fill: currentColor;
}

.pm-contact-card-head h2 {
  margin: 0 0 4px;
  color: #12234a;
  font-size: 1.5rem;
}

.pm-contact-card-head p {
  margin: 0;
  color: #64748b;
  font-size: 0.86rem;
}

.pm-contact-form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.pm-contact-field label {
  display: block;
  margin-bottom: 8px;
  color: #1f2d4d;
  font-weight: 700;
  font-size: 0.84rem;
}

.pm-contact-field-full {
  grid-column: 1 / -1;
}

.pm-contact-field :deep(.form-control),
.pm-contact-field :deep(.form-select) {
  min-height: 52px;
  border-radius: 12px;
}

.pm-contact-field textarea.form-control {
  min-height: 120px;
}

.pm-contact-submit {
  width: 100%;
  min-height: 54px;
  border-radius: 12px;
  background: linear-gradient(180deg, #143d8d 0%, #102f6f 100%);
  border: 0;
  color: #ffffff;
  font-weight: 800;
  font-size: 1rem;
}

.pm-contact-response-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 14px;
  color: #64748b;
  font-weight: 600;
  font-size: 0.84rem;
}

.pm-contact-response-check {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #edf3ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pm-contact-response-check svg {
  width: 11px;
  height: 11px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2.6;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.pm-contact-info-list {
  display: grid;
}

.pm-contact-info-item {
  display: grid;
  grid-template-columns: 54px 1fr;
  gap: 16px;
  padding: 14px 0;
}

.pm-contact-info-item + .pm-contact-info-item {
  border-top: 1px solid #edf1f7;
}

.pm-contact-info-item h3 {
  margin: 0 0 4px;
  color: #12234a;
  font-size: 0.92rem;
}

.pm-contact-info-item p {
  margin: 0;
  color: #64748b;
  font-size: 0.84rem;
  line-height: 1.6;
}

.pm-contact-map {
  margin-top: 16px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e7eef9;
  height: 190px;
}

.pm-contact-cta-strip {
  margin-top: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  border: 1px solid #e7eef9;
  border-radius: 20px;
  background: linear-gradient(180deg, #f9fbff 0%, #f4f8ff 100%);
  padding: 18px 22px;
}

.pm-contact-cta-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.pm-contact-cta-left h3 {
  margin: 0 0 4px;
  color: #12234a;
  font-size: 1.35rem;
}

.pm-contact-cta-left p {
  margin: 0;
  color: #64748b;
  font-size: 0.86rem;
}

.pm-contact-demo-btn {
  min-width: 208px;
  min-height: 54px;
  border-radius: 12px;
  background: linear-gradient(180deg, #2563eb 0%, #155dfc 100%);
  border: 0;
  color: #ffffff;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 991.98px) {
  .pm-contact-grid {
    grid-template-columns: 1fr;
  }

  .pm-contact-cta-strip {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 767.98px) {
  .pm-contact-card-head h2,
  .pm-contact-cta-left h3 {
    font-size: 1.55rem;
  }

  .pm-contact-form-grid {
    grid-template-columns: 1fr;
  }

  .pm-contact-demo-btn {
    width: 100%;
  }
}
</style>
