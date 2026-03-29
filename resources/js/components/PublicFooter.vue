<template>
  <div>
    <footer class="pm-footer-main py-5">
      <div class="container">
        <div class="row g-3">
          <div class="col-lg-4">
            <div class="fw-semibold mb-2">
                <img :src="logoWhite" alt="Contractor logo" style="height:50px" />
                <span>Contractor.com</span>
            </div>
            <div class="pm-muted">Contractor is a smart management platform designed for plumbers, electricians, and service contractors. Easily manage jobs, quotations, equipment, clients, and payments in one powerful system.</div>
            <div class="mt-3 pm-muted">123 Main Street, Suite 200</div>
            <div class="pm-muted">support@contractor.com</div>
            <div class="pm-muted">+1 (555) 123-4567</div>
          </div>
          <div class="col-lg-2">
            <div class="fw-semibold mb-2">Quick Links</div>
            <div><RouterLink to="/about">About Us</RouterLink></div>
            <div><RouterLink to="/plans">Pricing & Services</RouterLink></div>
            <div><RouterLink to="/contact">Contact</RouterLink></div>
            <div><a :href="termsUrl" target="_blank" rel="noopener">Terms &amp; Conditions</a></div>
          </div>
          <div class="col-lg-3">
            <div class="fw-semibold mb-2">Our Services</div>
            <div>Delivery Record</div>
            <div>Instant Notification</div>
            <div>International Shipping</div>
            <div>Bulk Shipping Record</div>
          </div>
          <div class="col-lg-3">
            <div class="fw-semibold mb-2">Stay Updated</div>
            <div class="pm-muted text-sm">Subscribe to our newsletter for exclusive offers and shipping updates.</div>
            <div class="pm-footer-input d-flex align-items-center mb-2">
              <input class="form-control" placeholder="Email address" />
              <button class="btn btn-light">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 10C0 4.47715 4.47715 0 10 0H22C27.5228 0 32 4.47715 32 10V22C32 27.5228 27.5228 32 22 32H10C4.47715 32 0 27.5228 0 22V10Z" fill="url(#paint0_linear_114_14777)"/>
                    <path d="M17.6907 22.4577C17.716 22.5208 17.7601 22.5747 17.8169 22.6121C17.8737 22.6494 17.9406 22.6685 18.0086 22.6668C18.0766 22.665 18.1424 22.6426 18.1973 22.6023C18.2521 22.5621 18.2933 22.506 18.3154 22.4417L22.6487 9.77503C22.67 9.71596 22.6741 9.65203 22.6604 9.59073C22.6468 9.52943 22.6159 9.47329 22.5715 9.42888C22.5271 9.38447 22.471 9.35363 22.4097 9.33996C22.3484 9.32629 22.2844 9.33036 22.2254 9.3517L9.5587 13.685C9.49436 13.7071 9.43832 13.7483 9.39808 13.8031C9.35785 13.858 9.33535 13.9238 9.33361 13.9918C9.33186 14.0598 9.35096 14.1267 9.38834 14.1835C9.42571 14.2403 9.47958 14.2844 9.5427 14.3097L14.8294 16.4297C14.9965 16.4966 15.1483 16.5967 15.2757 16.7238C15.4032 16.851 15.5035 17.0027 15.5707 17.1697L17.6907 22.4577Z" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22.5692 9.43164L15.2759 16.7243" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
                    <defs>
                    <linearGradient id="paint0_linear_114_14777" x1="0" y1="16" x2="32" y2="16" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#2B7FFF"/>
                    <stop offset="1" stop-color="#4F39F6"/>
                    </linearGradient>
                    </defs>
                </svg>

              </button>
            </div>
            <div class="pm-footer-card mt-3">
                <div class="fw-semibold flex item-start gap-2">
                    <span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 5V10L13.3333 11.6667" stroke="#51A2FF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.0001 18.3337C14.6025 18.3337 18.3334 14.6027 18.3334 10.0003C18.3334 5.39795 14.6025 1.66699 10.0001 1.66699C5.39771 1.66699 1.66675 5.39795 1.66675 10.0003C1.66675 14.6027 5.39771 18.3337 10.0001 18.3337Z" stroke="#51A2FF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    Opening Hours
                </div>
                <div class="pm-muted flex justify-between gap-2 font-semibold" >
                    <div>Mon - Fri:  </div>
                    <div> 08:00 AM - 06:00 PM</div>
                </div>
                <div class="pm-muted flex justify-between gap-2-muted font-semibold">
                    <div>Saturday:</div>
                    <div>10:00 AM - 04:00 PM</div> 
                </div>
              <div class="pm-muted flex justify-between gap-2-muted font-semibold">
                <div>Sunday:</div><div>Closed</div>
            </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <div class="pm-footer-bottom py-2">
      <div class="container d-flex flex-column flex-lg-row justify-content-between">
        <div>Copyright 2026 ParcelPro. All rights reserved.</div>
        <div>
          <span>Privacy Policy</span>
          <span> | </span>
          <a :href="termsUrl" target="_blank" rel="noopener">Terms of Service</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import logoWhite from '../assets/logo-white.png'

const termsUrl = `${import.meta.env.BASE_URL}terms`
</script>
