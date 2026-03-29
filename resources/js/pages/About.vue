<template>
  <div>
    <PublicHeader />

    <section class="pm-hero-about">
      <div class="container">
        <h1 class="fw-semibold">About Us</h1>
      </div>
    </section>

    <section class="pm-section">
      <div class="container">
        <div class="row g-4 align-items-center">
          <div class="col-lg-6">
            <img
              class="img-fluid rounded"
              :src="picture1"
              alt="Parcel team"
            />
          </div>
          <div class="col-lg-6">
            <h3>About Our Platform </h3>
            <p class="pm-muted">
              Contractor is a powerful job and service management platform designed for contractors such as plumbers, electricians, technicians, and maintenance professionals. Our system helps service providers organize their work, manage client information, prepare quotations, and track equipment and expenses efficiently.
            </p>
            <p class="pm-muted">
              With Contractor, businesses can easily manage their daily operations from one centralized platform. From job creation to payment tracking, every step of the service process becomes more organized, transparent, and easier to control.
            </p>
            <p class="pm-muted">
              This platform is built to simplify contractor operations, reduce paperwork, and help service professionals focus more on delivering quality work instead of managing complicated records.
            </p>
          
            <h6>Why Choose Contractor</h6>
            <ul class="pm-features-list list-unstyled mt-3">
              <li class="d-flex mb-3">
                <div class="me-3">
                  <span class="pm-badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px">✓</span>
                </div>
                <div>
                  <strong>All-in-one job management</strong>
                  <p class="pm-muted mb-0">Manage all service jobs and work orders in one centralized place.</p>
                </div>
              </li>
              <li class="d-flex mb-3">
                <div class="me-3">
                  <span class="pm-badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px">✓</span>
                </div>
                <div>
                  <strong>Quick professional quotations</strong>
                  <p class="pm-muted mb-0">Create polished, client-ready quotes in minutes.</p>
                </div>
              </li>
              <li class="d-flex mb-3">
                <div class="me-3">
                  <span class="pm-badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px">✓</span>
                </div>
                <div>
                  <strong>Track tools & materials</strong>
                  <p class="pm-muted mb-0">Monitor equipment, tools, and materials used on each project.</p>
                </div>
              </li>
              <li class="d-flex mb-3">
                <div class="me-3">
                  <span class="pm-badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px">✓</span>
                </div>
                <div>
                  <strong>Complete client & project records</strong>
                  <p class="pm-muted mb-0">Keep full histories for clients, jobs, and project notes.</p>
                </div>
              </li>
              <li class="d-flex mb-3">
                <div class="me-3">
                  <span class="pm-badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px">✓</span>
                </div>
                <div>
                  <strong>Finance & payments</strong>
                  <p class="pm-muted mb-0">Monitor expenses, payments, and financial activity with ease.</p>
                </div>
              </li>
              <li class="d-flex">
                <div class="me-3">
                  <span class="pm-badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px">✓</span>
                </div>
                <div>
                  <strong>Built for contractors</strong>
                  <p class="pm-muted mb-0">Our vision is to become a trusted management platform for contractors worldwide, helping plumbers, electricians, and service professionals run smarter and more organized businesses.</p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-mission">
      <div class="container">
        <div class="pm-mission-card">
          <h4>Our Mission</h4>
          <p class="pm-muted">
           Our mission is to simplify contractor business management by providing an easy-to-use digital platform that helps service professionals organize jobs, manage resources, and grow their businesses efficiently.
          </p>
          <h4 class="mt-3">Our Vision</h4>
          <p class="pm-muted">To become the most trusted parcel management solution for smart buildings worldwide.</p>
        </div>
      </div>
    </section>

    <section class="pm-section" id="contact">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-7">
            <div class="pm-contact-form-card">
              <div class="pm-form-header">
                <h4 class="fw-semibold">Talk to our team</h4>
                <p class="pm-muted">Tell us what you need and our team will respond quickly.</p>
                <div class="pm-form-meta">
                  <span>15 min response</span>
                  <span>Dedicated support</span>
                  <span>Secure handling</span>
                </div>
              </div>
              <div v-if="contactSuccess" class="pm-form-success">Thanks! We will reach out shortly.</div>
              <div v-if="contactError" class="pm-form-error">{{ contactError }}</div>
              <form class="row g-3" @submit.prevent="submitContact">
                <div class="col-md-6">
                  <div class="pm-float-field">
                    <input v-model="contact.name" class="form-control" placeholder=" " />
                    <label>Full name</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="pm-float-field">
                    <input v-model="contact.company" class="form-control" placeholder=" " />
                    <label>Company</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="pm-float-field">
                    <input v-model="contact.email" class="form-control" placeholder=" " />
                    <label>Email</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="pm-float-field">
                    <input v-model="contact.phone" class="form-control" placeholder=" " />
                    <label>Phone</label>
                  </div>
                </div>
                <div class="col-12">
                  <div class="pm-float-field">
                    <input v-model="contact.subject" class="form-control" placeholder=" " />
                    <label>Subject</label>
                  </div>
                </div>
                <div class="col-12">
                  <div class="pm-float-field">
                    <textarea v-model="contact.message" class="form-control" rows="5" placeholder=" "></textarea>
                    <label>Message</label>
                  </div>
                </div>
                <div class="col-12 d-flex gap-2">
                  <button class="btn pm-btn-solid px-4" type="submit" :disabled="contactLoading">
                    {{ contactLoading ? 'Sending...' : 'Send Message' }}
                  </button>
                  <button class="btn pm-btn-ghost px-4" type="reset">Clear</button>
                </div>
              </form>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="pm-contact-side-card">
              <div class="pm-side-title">Contact desk</div>
              <p class="pm-muted">For onboarding, integrations, or account help, our team is ready to assist.</p>
              <div class="pm-side-block">
                <div class="pm-side-label">Email</div>
                <div class="pm-side-value">info@contractor.com</div>
              </div>
              <div class="pm-side-block">
                <div class="pm-side-label">Phone</div>
                <div class="pm-side-value">+1 (555) 123-4567</div>
              </div>
            </div>
            <div class="pm-contact-side-card pm-contact-side-image">
              <img
                class="img-fluid rounded-4"
                :src="contact_us"
                alt="Support desk"
              />
            </div>
          </div>
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
import picture1 from '../assets/about-us/pic1.jpg'
import contact_us from '../assets/contact-us.jpg'


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
</script>
