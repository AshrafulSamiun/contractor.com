<template>
  <div>
    <PublicHeader />

    <section id="home" class="pm-hero d-flex align-items-center">
      <video
        ref="heroVideoRef"
        class="pm-hero-video"
        autoplay
        muted
        playsinline
        @ended="nextVideo"
        @error="handleVideoError"
      >
        <source :src="heroVideos[currentVideo]" type="video/mp4" />
      </video>
      <div class="container text-center">
        <div class="pm-hero-content">
          <h1 class="fw-semibold">Smart Management System for Contractors</h1>
          <p class="lead">
            Manage plumbing, electrical, and service jobs efficiently with one powerful platform.
          </p>
          <div class="d-flex flex-wrap justify-content-center gap-2">
            <RouterLink class="btn btn-light px-4" to="/register">Create Account</RouterLink>
            <RouterLink class="btn btn-outline-light px-4 border-2" to="/login">Requet a Demo</RouterLink>
          </div>
          <div class="pm-hero-dots mt-3">
            <span
              v-for="(item, index) in heroVideos"
              :key="`dot-${index}`"
              :class="{ active: index === currentVideo }"
              @click="setVideo(index)"
            ></span>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-trust-section">
      <div class="container">
        <div class="row g-3 align-items-center">
          <div class="col-lg-12">
            <h3 class="fw-semibold mb-2">Trusted by busy teams</h3>
            <p class="pm-muted">Contractor helps plumbing, electrical, and maintenance businesses manage jobs, clients, quotations, equipment, and payments in one smart system.
Perfect for plumbers, electricians, repair technicians, maintenance teams, construction service providers, and independent contractor</p>
          </div>
          
        </div>
        
      </div>
    </section>

    <section class="pm-section" id="about">
      <div class="container text-center">
        <h2 class="pm-section-title-center mt-4">What Contractor Does?</h2>
        <div class="row g-3 mt-3">
          <div class="col-md-4">
            <div class="pm-feature-card card p-3">
              <div class="pm-feature-icon mb-2" style="background:#2563eb;">
                <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 16C0 7.16344 7.16344 0 16 0H40C48.8366 0 56 7.16344 56 16V40C56 48.8366 48.8366 56 40 56H16C7.16344 56 0 48.8366 0 40V16Z" fill="url(#paint0_linear_114_14562)"/>
                    <path d="M41.068 25.3333C41.6769 28.3217 41.243 31.4285 39.8385 34.1357C38.434 36.8429 36.1439 38.9867 33.35 40.2097C30.5562 41.4328 27.4275 41.661 24.4857 40.8565C21.544 40.0519 18.9669 38.2632 17.1844 35.7885C15.4018 33.3139 14.5215 30.303 14.6902 27.2578C14.859 24.2127 16.0665 21.3174 18.1115 19.0549C20.1566 16.7923 22.9155 15.2992 25.9281 14.8246C28.9407 14.35 32.0251 14.9225 34.6667 16.4467" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M24 26.6663L28 30.6663L41.3333 17.333" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                    <defs>
                    <linearGradient id="paint0_linear_114_14562" x1="0" y1="28" x2="56" y2="28" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#2B7FFF"/>
                    <stop offset="1" stop-color="#155DFC"/>
                    </linearGradient>
                    </defs>
                </svg>

              </div>
              <div class="fw-semibold">Manage Job & Work Orders</div>
              <div class="pm-muted"></div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="pm-feature-card card p-3">
              <div class="pm-feature-icon mb-2" style="background:#16a34a;">
                <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 16C0 7.16344 7.16344 0 16 0H40C48.8366 0 56 7.16344 56 16V40C56 48.8366 48.8366 56 40 56H16C7.16344 56 0 48.8366 0 40V16Z" fill="url(#paint0_linear_114_14570)"/>
                <path d="M28 20V28L33.3333 30.6667" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M28 41.3333C35.3638 41.3333 41.3333 35.3638 41.3333 28C41.3333 20.6362 35.3638 14.6666 28 14.6666C20.6362 14.6666 14.6666 20.6362 14.6666 28C14.6666 35.3638 20.6362 41.3333 28 41.3333Z" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                <linearGradient id="paint0_linear_114_14570" x1="0" y1="28" x2="56" y2="28" gradientUnits="userSpaceOnUse">
                <stop stop-color="#00C950"/>
                <stop offset="1" stop-color="#00A63E"/>
                </linearGradient>
                </defs>
                </svg>

              </div>
              <div class="fw-semibold">Manage Job & Work Orders</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="pm-feature-card card p-3">
              <div class="pm-feature-icon mb-2" style="background:#7c3aed;">
                <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 16C0 7.16344 7.16344 0 16 0H40C48.8366 0 56 7.16344 56 16V40C56 48.8366 48.8366 56 40 56H16C7.16344 56 0 48.8366 0 40V16Z" fill="url(#paint0_linear_114_14577)"/>
                    <path d="M38.6666 29.3333C38.6666 35.9999 34 39.3333 28.4533 41.2666C28.1629 41.365 27.8474 41.3603 27.56 41.2533C22 39.3333 17.3333 35.9999 17.3333 29.3333V19.9999C17.3333 19.6463 17.4738 19.3072 17.7238 19.0571C17.9739 18.8071 18.313 18.6666 18.6666 18.6666C21.3333 18.6666 24.6666 17.0666 26.9866 15.0399C27.2691 14.7986 27.6285 14.666 28 14.666C28.3715 14.666 28.7308 14.7986 29.0133 15.0399C31.3466 17.0799 34.6666 18.6666 37.3333 18.6666C37.6869 18.6666 38.0261 18.8071 38.2761 19.0571C38.5262 19.3072 38.6666 19.6463 38.6666 19.9999V29.3333Z" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                    <defs>
                    <linearGradient id="paint0_linear_114_14577" x1="0" y1="28" x2="56" y2="28" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#AD46FF"/>
                    <stop offset="1" stop-color="#9810FA"/>
                    </linearGradient>
                    </defs>
                </svg>

              </div>
              <div class="fw-semibold">Create Professional Quotations</div>
              <div class="pm-muted"></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="pm-feature-card card p-3">
              <div class="pm-feature-icon mb-2" style="background:#f97316;">

                <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 16C0 7.16344 7.16344 0 16 0H40C48.8366 0 56 7.16344 56 16V40C56 48.8366 48.8366 56 40 56H16C7.16344 56 0 48.8366 0 40V16Z" fill="url(#paint0_linear_114_14583)"/>
                <path d="M32.636 29.1866L34.656 40.5546C34.6787 40.6885 34.6599 40.8261 34.6022 40.949C34.5445 41.0719 34.4507 41.1742 34.3333 41.2424C34.2159 41.3105 34.0804 41.3412 33.9451 41.3303C33.8098 41.3193 33.681 41.2674 33.576 41.1813L28.8027 37.5986C28.5723 37.4265 28.2923 37.3335 28.0047 37.3335C27.7171 37.3335 27.4371 37.4265 27.2067 37.5986L22.4254 41.18C22.3205 41.2659 22.1919 41.3178 22.0567 41.3287C21.9215 41.3396 21.7863 41.3091 21.6689 41.2411C21.5516 41.1732 21.4577 41.0711 21.3999 40.9484C21.3421 40.8258 21.3231 40.6884 21.3454 40.5546L23.364 29.1866" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M28 30.667C32.4183 30.667 36 27.0853 36 22.667C36 18.2487 32.4183 14.667 28 14.667C23.5817 14.667 20 18.2487 20 22.667C20 27.0853 23.5817 30.667 28 30.667Z" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                <linearGradient id="paint0_linear_114_14583" x1="0" y1="28" x2="56" y2="28" gradientUnits="userSpaceOnUse">
                <stop stop-color="#FF6900"/>
                <stop offset="1" stop-color="#F54900"/>
                </linearGradient>
                </defs>
                </svg>

              </div>
              <div class="fw-semibold">Track Equipment & Materials</div>
              <div class="pm-muted"></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="pm-feature-card card p-3">
              <div class="pm-feature-icon mb-2" style="background:#db2777;">
                <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 16C0 7.16344 7.16344 0 16 0H40C48.8366 0 56 7.16344 56 16V40C56 48.8366 48.8366 56 40 56H16C7.16344 56 0 48.8366 0 40V16Z" fill="url(#paint0_linear_114_14590)"/>
                    <path d="M33.3334 21.333H41.3334V29.333" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M41.3333 21.333L30 32.6663L23.3333 25.9997L14.6666 34.6663" stroke="white" stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round"/>
                    <defs>
                    <linearGradient id="paint0_linear_114_14590" x1="0" y1="28" x2="56" y2="28" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#F6339A"/>
                    <stop offset="1" stop-color="#E60076"/>
                    </linearGradient>
                    </defs>
                    </svg>

              </div>
              <div class="fw-semibold">Manage Client & Project Records</div>
              <div class="pm-muted"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-process pm-section" id="plans">
      <div class="container">
        <h2 class="pm-section-title-center">How It Works?</h2>
        <p class="text-center">Simple 5-step process from arrival to delivery</p>
        <div class="pm-process-list mt-4">
            <div class="pm-process-step flex items-start items-center gap-3">
                <span class="pm-step-circle">1</span>
                <div class="flex flex-col">
                    <span class="font-semibold">How Contractor Works</span>
                    <span class="text-gray-500 text-sm">
                        Simple 5-step process to manage your service jobs.
                    </span>
                </div>
            </div>
            <div class="pm-process-step flex items-start items-center gap-3">
                <span class="pm-step-circle">2</span>
                <div class="flex flex-col">
                    <span class="font-semibold">Create New Job Order</span>
                    <span class="text-gray-500 text-sm">
                       Add client details and create a new service job.
                    </span>
                </div>
            </div>
            <div class="pm-process-step flex items-start items-center gap-3">
                <span class="pm-step-circle">3</span>
                <div class="flex flex-col">
                    <span class="font-semibold">Prepare Quotation</span>
                    <span class="text-gray-500 text-sm">
                        Generate professional quotations with materials and labor cost.
                    </span>
                </div>
            </div>
            <div class="pm-process-step flex items-start items-center gap-3">
                <span class="pm-step-circle">4</span>
                <div class="flex flex-col">
                    <span class="font-semibold">Track Work Progress</span>
                    <span class="text-gray-500 text-sm">
                        Monitor job progress, expenses, and updates in real time.
                    </span>
                </div>
            </div>
            <div class="pm-process-step flex items-start items-center gap-3">
                <span class="pm-step-circle">5</span>
                <div class="flex flex-col">
                    <span class="font-semibold">Complete Job & Payment</span>
                    <span class="text-gray-500 text-sm">
                        Finalize the job and record payments for accurate financial tracking.
                    </span>
                </div>
            </div>
          
        </div>
      </div>
    </section>


    <section class="pm-section" id="contact">
      <div class="container">
        <div class="row g-3">
          <div class="col-lg-6">
            <div class="pm-contact-card card p-4">
              <h5>Talk To Us</h5>
              <h6 class="fw-semibold">How May We Help You!</h6>
              <div v-if="contactSuccess" class="pm-form-success">Thanks! We will reach out shortly.</div>
              <div v-if="contactError" class="pm-form-error">{{ contactError }}</div>
              <div class="row g-3">
                <div class="col-12"><input v-model="contact.name" class="form-control" placeholder="Your Name" /></div>
                <div class="col-12"><input v-model="contact.email" class="form-control" placeholder="Your Email" /></div>
                <div class="col-12"><input v-model="contact.phone" class="form-control" placeholder="Your Phone" /></div>
                <div class="col-12"><input v-model="contact.subject" class="form-control" placeholder="Subject" /></div>
                <div class="col-12"><textarea v-model="contact.message" class="form-control" rows="4" placeholder="Message"></textarea></div>
              </div>
              <button class="btn btn-primary mt-3" :disabled="contactLoading" @click="submitContact">
                {{ contactLoading ? 'Sending...' : 'Send Message' }}
              </button>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="pm-contact-card card p-4 h-100">
              <div class="mb-3">
                <img class="img-fluid rounded" :src="contact_us" alt="Parcel desk" />
              </div>
              <h5>Contact Us</h5>
              <p class="pm-muted">For further inquiries or assistance with setting up your marketplace, our team is ready to assist you.</p>
              <div class="mt-3">
                <div class="fw-semibold">Email</div>
                <div class="pm-muted">info@contractor.com</div>
              </div>
              <div class="mt-3">
                <div class="fw-semibold">Phone</div>
                <div class="pm-muted">+1 (555) 123-4567</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <PublicFooter />
  </div>
</template>
<style scoped>
    .pm-process-step .pm-step-desc{
    display: block;
    font-size: 14px;
    color: #6b7280;
    margin-top: 4px;
}

</style>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import PublicHeader from '../components/PublicHeader.vue'
import PublicFooter from '../components/PublicFooter.vue'
import client from '../api/client'
import heroVideo1 from '../assets/hero/hero-4.mp4'
import heroVideo2 from '../assets/hero/hero-2.mp4'
import contact_us from '../assets/contact-us.jpg'
import { onMounted, onBeforeUnmount } from 'vue'


const contact = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})
const contactLoading = ref(false)
const contactSuccess = ref(false)
const contactError = ref('')

const heroVideos = [heroVideo1]
const currentVideo = ref(0)
const heroVideoRef = ref(null)
let rotateTimer = null

const nextVideo = () => {
  currentVideo.value = (currentVideo.value + 1) % heroVideos.length
  const el = heroVideoRef.value
  if (el) {
    el.load()
    el.play().catch(() => {})
  }
}

const setVideo = (index) => {
  if (index === currentVideo.value) return
  currentVideo.value = index
  const el = heroVideoRef.value
  if (el) {
    el.load()
    el.play().catch(() => {})
  }
}

const handleVideoError = () => {
  if (rotateTimer) return
  rotateTimer = setInterval(nextVideo, 12000)
}

onMounted(() => {
  rotateTimer = setInterval(nextVideo, 14000)
})

onBeforeUnmount(() => {
  if (rotateTimer) clearInterval(rotateTimer)
  rotateTimer = null
})


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
