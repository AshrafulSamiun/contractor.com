<template>
  <footer class="pm-public-footer">
    <div class="container">
      <div class="pm-public-footer-grid">
        <div class="pm-public-footer-brand">
          <div class="pm-public-footer-logo">
            <span class="pm-public-footer-logo-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 10.5 12 4l9 6.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10.5z"/>
              </svg>
            </span>
            <span class="pm-public-footer-logo-text">ContractorPro</span>
          </div>
          <p>
            All-in-one contractor management software to run your business smarter and more efficiently.
          </p>
          <div class="pm-public-footer-social">
            <a href="https://facebook.com" target="_blank" rel="noopener">f</a>
            <a href="https://twitter.com" target="_blank" rel="noopener">t</a>
            <a href="https://linkedin.com" target="_blank" rel="noopener">in</a>
            <a href="https://youtube.com" target="_blank" rel="noopener">o</a>
          </div>
        </div>

        <div>
          <h4>COMPANY</h4>
          <RouterLink to="/about">About Us</RouterLink>
          <a href="#">Careers</a>
          <a href="#">Blog</a>
          <a href="#">News</a>
          <RouterLink to="/contact">Contact Us</RouterLink>
        </div>

        <div>
          <h4>PRODUCT</h4>
          <a href="#">Features</a>
          <a href="#">Industries</a>
          <RouterLink to="/plans">Pricing Plans</RouterLink>
          <a href="#">Updates</a>
          <a href="#">Integrations</a>
        </div>

        <div>
          <h4>SUPPORT</h4>
          <a href="#">Help Center</a>
          <a href="#">Documentation</a>
          <a href="#">Video Tutorials</a>
          <a href="#">System Status</a>
          <RouterLink to="/contact">Contact Support</RouterLink>
        </div>

        <div>
          <h4>LEGAL</h4>
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Refund Policy</a>
          <a href="#">Cookie Policy</a>
        </div>

        <div class="pm-public-footer-contact">
          <h4>CONTACT US</h4>
          <div class="pm-public-footer-contact-item">
            <span>+1 (888) 555-1234</span>
          </div>
          <div class="pm-public-footer-contact-item">
            <span>support@contractorpro.com</span>
          </div>
          <div class="pm-public-footer-contact-item">
            <span>123 Main St, Suite 400<br />Austin, TX 78701, USA</span>
          </div>
        </div>
      </div>

      <div class="pm-public-footer-bottom">
        <span>&copy; 2024 ContractorPro. All rights reserved.</span>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.pm-public-footer {
  background: linear-gradient(180deg, #10244b 0%, #091a36 100%);
  color: #dbe7ff;
  padding: 28px 0 14px;
}

.pm-public-footer-grid {
  display: grid;
  grid-template-columns: 1.25fr repeat(4, minmax(0, 0.75fr)) 1fr;
  gap: 28px;
}

.pm-public-footer-grid h4 {
  margin: 0 0 14px;
  color: #ffffff;
  font-size: 1rem;
  font-weight: 800;
}

.pm-public-footer-grid a,
.pm-public-footer-grid :deep(a) {
  display: block;
  color: #dbe7ff;
  margin-bottom: 8px;
}

.pm-public-footer-brand p {
  max-width: 260px;
  margin: 10px 0 18px;
  line-height: 1.7;
}

.pm-public-footer-logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.pm-public-footer-logo-icon {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(180deg, #2563eb 0%, #155dfc 100%);
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pm-public-footer-logo-icon svg {
  width: 17px;
  height: 17px;
  fill: currentColor;
}

.pm-public-footer-logo-text {
  color: #ffffff;
  font-size: 1.55rem;
  font-weight: 800;
}

.pm-public-footer-social {
  display: flex;
  gap: 10px;
}

.pm-public-footer-social a {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.22);
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin: 0;
  font-weight: 700;
}

.pm-public-footer-contact-item {
  margin-bottom: 12px;
  line-height: 1.7;
}

.pm-public-footer-bottom {
  margin-top: 26px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  text-align: center;
  color: #dbe7ff;
}

@media (max-width: 1199.98px) {
  .pm-public-footer-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 767.98px) {
  .pm-public-footer-grid {
    grid-template-columns: 1fr;
  }
}
</style>
