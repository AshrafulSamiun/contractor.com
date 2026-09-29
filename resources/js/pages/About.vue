<template>
  <div class="pm-about-page">
    <PublicHeader />

    <section class="pm-about-hero">
      <div class="container">
        <div class="pm-about-hero-grid">
          <div class="pm-about-copy">
            <div class="pm-about-kicker">ABOUT OUR PRODUCT</div>
            <h1>All-in-One Contractor Management Software Built to Simplify Every Job</h1>
            <p>
              ContractorPro is an all-in-one platform that helps contractors streamline daily operations, manage jobs,
              track teams, control costs, and get paid faster. From the office to the job site, everything you need is
              in one powerful system.
            </p>

            <div class="pm-about-benefits">
              <div v-for="item in topBenefits" :key="item.title" class="pm-about-benefit-card">
                <div class="pm-about-benefit-icon" :style="{ color: item.color }" v-html="item.icon"></div>
                <h3>{{ item.title }}</h3>
                <p>{{ item.text }}</p>
              </div>
            </div>
          </div>

          <div class="pm-about-hero-visual">
            <img class="pm-about-hero-image" :src="dashboardPreview" alt="ContractorPro dashboard" />
          </div>
        </div>
      </div>
    </section>

    <section class="pm-about-features">
      <div class="container">
        <div class="pm-about-section-head">
          <h2>Everything You Need to Manage Jobs Efficiently</h2>
          <p>Powerful features designed to help contractors run projects smoothly from start to finish.</p>
        </div>

        <div class="pm-about-feature-grid">
          <div v-for="feature in productFeatures" :key="feature.title" class="pm-about-feature-card">
            <div class="pm-about-feature-icon" :style="{ color: feature.color }" v-html="feature.icon"></div>
            <div>
              <h3>{{ feature.title }}</h3>
              <p>{{ feature.text }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-about-bottom">
      <div class="container">
        <div class="pm-about-bottom-panel">
          <div class="pm-about-bottom-main">
            <img class="pm-about-bottom-image" :src="constructionImage" alt="Construction site" />

            <div class="pm-about-bottom-copy">
              <h2>Built for Every Type of Construction Business</h2>
              <p>
                Whether you're a small contractor or a large construction company, ContractorPro adapts to your workflow
                and helps you stay on time, on budget, and in control.
              </p>

              <div class="pm-about-business-list">
                <div v-for="item in businessTypes" :key="item" class="pm-about-business-item">
                  <span class="pm-about-business-check">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M20 6L9 17L4 12" />
                    </svg>
                  </span>
                  <span>{{ item }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="pm-about-bottom-side">
            <div v-for="item in trustPoints" :key="item.title" class="pm-about-trust-card">
              <div class="pm-about-trust-icon" :style="{ color: item.color }" v-html="item.icon"></div>
              <div>
                <h3>{{ item.title }}</h3>
                <p>{{ item.text }}</p>
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
import PublicHeader from '../components/PublicHeader.vue'
import PublicFooter from '../components/PublicFooter.vue'
import dashboardPreview from '../assets/about-us/Summary.png'
import constructionImage from '../assets/about-us/pic1.jpg'

const icons = {
  grid: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>',
  bolt: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13 2 5 13h5l-1 9 8-11h-5l1-9Z"/></svg>',
  chart: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 20h16v2H2V4h2v16Zm4-2H5v-6h3v6Zm5 0h-3V8h3v10Zm5 0h-3V5h3v13Z"/></svg>',
  shield: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2 5 5v6c0 5 3.4 9.7 7 11 3.6-1.3 7-6 7-11V5l-7-3Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 12.2A8.95 8.95 0 0 1 7 11.1V6.3l5-2.1 5 2.1v4.8a8.95 8.95 0 0 1-5 8.1Z"/></svg>',
  clipboard: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M9 2h6v3h4a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4V2Zm2 2v1h2V4h-2Zm-4 5v2h10V9H7Zm0 4v2h10v-2H7Zm0 4v2h6v-2H7Z"/></svg>',
  calendar: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2V2Zm12 8H5v10h14V10ZM5 8h14V6H5v2Z"/></svg>',
  team: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-8 1a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm8 2c-2.67 0-8 1.34-8 4v2h14v-2c0-2.66-5.33-4-8-4ZM8 14c-.29 0-.62.02-.97.05C4.8 14.31 2 15.3 2 17v3h4v-2c0-1.48.8-2.6 1.97-3.45A12.9 12.9 0 0 0 8 14Z"/></svg>',
  payment: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm1 3h-2v1.1a3.5 3.5 0 0 0 .5 6.96h1a1.5 1.5 0 1 1 0 3H9v2h2v1h2v-1.1a3.5 3.5 0 0 0-.5-6.96h-1a1.5 1.5 0 1 1 0-3H15V5h-2V4Z"/></svg>',
  reports: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 20h16v2H2V4h2v16Zm4-2H5v-6h3v6Zm5 0h-3V8h3v10Zm5 0h-3V5h3v13Z"/></svg>',
  car: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 11 6.5 6.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11a3 3 0 0 1 2 2.82V18h-2v2h-2v-2H7v2H5v-2H3v-4.18A3 3 0 0 1 5 11Zm2.1-1h9.8l-.9-2.7a.5.5 0 0 0-.47-.3H8.47a.5.5 0 0 0-.47.34L7.1 10ZM7 15.5A1.5 1.5 0 1 0 7 12.5a1.5 1.5 0 0 0 0 3Zm10 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>',
  building: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 21V5a2 2 0 0 1 2-2h7v18H4Zm11 0V9h3a2 2 0 0 1 2 2v10h-5ZM7 6v2h2V6H7Zm0 4v2h2v-2H7Zm0 4v2h2v-2H7Zm4-8v2h2V6h-2Zm0 4v2h2v-2h-2Zm0 4v2h2v-2h-2Z"/></svg>',
  more: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6 12a2 2 0 1 1 0 .01V12Zm6 0a2 2 0 1 1 0 .01V12Zm6 0a2 2 0 1 1 0 .01V12Z"/></svg>',
  lock: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17 9h-1V7a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v9h14v-9a2 2 0 0 0-2-2Zm-7-2a2 2 0 1 1 4 0v2h-4V7Z"/></svg>',
  cloud: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 18H6a4 4 0 1 1 .6-7.96A6 6 0 0 1 18 11a3.5 3.5 0 0 1 1 7Z"/></svg>',
  scale: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 20h16v2H2V4h2v16Zm3-3 3-4 2 2 5-7 2 1-6 8-2-2-2 3H7Zm10-8h3v7h-3V9Z"/></svg>',
}

const topBenefits = [
  {
    icon: icons.grid,
    title: 'Complete Solution',
    text: 'All the tools you need to run your contracting business in one place.',
    color: '#2563eb',
  },
  {
    icon: icons.bolt,
    title: 'Save Time',
    text: 'Automate tasks, reduce manual work, and focus on what matters.',
    color: '#2563eb',
  },
  {
    icon: icons.chart,
    title: 'Work Smarter',
    text: 'Real-time data and insights to make better decisions and increase profits.',
    color: '#2563eb',
  },
  {
    icon: icons.shield,
    title: 'Built for Contractors',
    text: 'Designed specifically for contractors, by industry experts who understand your challenges.',
    color: '#2563eb',
  },
]

const productFeatures = [
  { icon: icons.clipboard, title: 'Job Management', text: 'Create, assign, and track jobs from start to finish.', color: '#2563eb' },
  { icon: icons.calendar, title: 'Scheduling & Dispatch', text: 'Schedule jobs and dispatch teams with ease.', color: '#22c55e' },
  { icon: icons.team, title: 'Team Management', text: 'Manage employees, roles, permissions, and timesheets.', color: '#f59e0b' },
  { icon: icons.payment, title: 'Invoicing & Payments', text: 'Send invoices, track payments, and get paid faster.', color: '#9333ea' },
  { icon: icons.reports, title: 'Reports & Analytics', text: 'Get real-time insights and make better decisions.', color: '#2563eb' },
  { icon: icons.car, title: 'Vehicle Management', text: 'Track vehicles, maintenance, and fuel usage.', color: '#2563eb' },
  { icon: icons.building, title: 'Job Sites', text: 'Manage all your job sites and keep everything organized.', color: '#65a30d' },
  { icon: icons.more, title: 'More', text: 'And many more tools to grow your business.', color: '#64748b' },
]

const businessTypes = [
  'General Contractors',
  'Commercial Construction',
  'Subcontractors',
  'Residential Construction',
  'Specialty Contractors',
  'Service & Maintenance',
]

const trustPoints = [
  {
    icon: icons.lock,
    title: 'Secure & Reliable',
    text: 'Your data is safe with enterprise-grade security and regular backups.',
    color: '#2563eb',
  },
  {
    icon: icons.cloud,
    title: 'Cloud Based',
    text: 'Access your data anytime, anywhere from any device.',
    color: '#2563eb',
  },
  {
    icon: icons.scale,
    title: 'Scalable',
    text: 'Grow your business with a platform that scales with your needs.',
    color: '#2563eb',
  },
]
</script>

<style scoped>
.pm-about-page {
  background: #ffffff;
}

.pm-about-hero {
  padding: 14px 0 10px;
  background:
    radial-gradient(760px 420px at 8% 12%, rgba(37, 99, 235, 0.08), transparent 60%),
    radial-gradient(620px 340px at 92% 16%, rgba(59, 130, 246, 0.08), transparent 60%),
    #ffffff;
}

.pm-about-hero-grid {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  gap: 26px;
  align-items: center;
}

.pm-about-kicker {
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

.pm-about-copy h1 {
  max-width: 620px;
  margin: 0 0 18px;
  color: #12234a;
  font-size: clamp(1.25rem, 2.2vw, 1.9rem);
  line-height: 1.12;
  letter-spacing: -0.04em;
}

.pm-about-copy > p {
  max-width: 620px;
  margin: 0 0 24px;
  color: #566884;
  font-size: 0.82rem;
  line-height: 1.72;
}

.pm-about-benefits {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.pm-about-benefit-card,
.pm-about-feature-card {
  background: #ffffff;
  border: 1px solid #e7eef9;
  border-radius: 18px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.pm-about-benefit-card {
  padding: 18px 14px;
}

.pm-about-benefit-icon,
.pm-about-feature-icon,
.pm-about-trust-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: #f8fbff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pm-about-benefit-icon :deep(svg),
.pm-about-feature-icon :deep(svg),
.pm-about-trust-icon :deep(svg) {
  width: 24px;
  height: 24px;
  display: block;
}

.pm-about-benefit-card h3,
.pm-about-feature-card h3,
.pm-about-trust-card h3 {
  margin: 14px 0 8px;
  color: #12234a;
  font-size: 0.82rem;
}

.pm-about-benefit-card p,
.pm-about-feature-card p,
.pm-about-trust-card p,
.pm-about-bottom-copy p {
  margin: 0;
  color: #64748b;
  font-size: 0.76rem;
  line-height: 1.7;
}

.pm-about-hero-visual {
  display: flex;
  align-items: flex-end;
}

.pm-about-hero-image {
  width: min(100%, 920px);
  max-height: 520px;
  display: block;
  margin-left: auto;
  object-fit: contain;
  border-radius: 18px;
  border: 1px solid #e7eef9;
  background: #ffffff;
  filter: drop-shadow(0 28px 52px rgba(15, 23, 42, 0.16));
}

.pm-about-features {
  padding: 10px 0 18px;
}

.pm-about-section-head {
  text-align: center;
  margin-bottom: 16px;
}

.pm-about-section-head h2 {
  margin: 0 0 8px;
  color: #12234a;
  font-size: 2.2rem;
}

.pm-about-section-head p {
  margin: 0;
  color: #64748b;
  font-size: 1rem;
}

.pm-about-feature-grid {
  display: grid;
  grid-template-columns: repeat(8, minmax(0, 1fr));
  gap: 12px;
}

.pm-about-feature-card {
  padding: 16px 14px;
  min-height: 122px;
}

.pm-about-bottom {
  padding: 6px 0 30px;
}

.pm-about-bottom-panel {
  border: 1px solid #e7eef9;
  border-radius: 20px;
  background: linear-gradient(180deg, #f9fbff 0%, #f3f7ff 100%);
  overflow: hidden;
}

.pm-about-bottom-main {
  display: grid;
  grid-template-columns: 320px minmax(0, 1fr);
  gap: 28px;
  padding: 18px;
  align-items: center;
}

.pm-about-bottom-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 16px;
}

.pm-about-bottom-copy h2 {
  margin: 0 0 10px;
  color: #12234a;
  font-size: 2rem;
}

.pm-about-business-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px 20px;
  margin-top: 18px;
}

.pm-about-business-item {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #223154;
  font-weight: 600;
}

.pm-about-business-check {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #2563eb;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
}

.pm-about-business-check svg {
  width: 11px;
  height: 11px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.pm-about-bottom-side {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  border-top: 1px solid #e7eef9;
}

.pm-about-trust-card {
  padding: 22px 20px;
  background: rgba(255, 255, 255, 0.65);
}

.pm-about-trust-card + .pm-about-trust-card {
  border-left: 1px solid #e7eef9;
}

@media (max-width: 1399.98px) {
  .pm-about-feature-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 1199.98px) {
  .pm-about-hero-grid,
  .pm-about-bottom-main {
    grid-template-columns: 1fr;
  }

  .pm-about-copy {
    text-align: center;
  }

  .pm-about-copy h1,
  .pm-about-copy > p {
    margin-left: auto;
    margin-right: auto;
  }

  .pm-about-benefits {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-about-hero-visual {
    justify-content: center;
  }

  .pm-about-bottom-image {
    height: 260px;
  }
}

@media (max-width: 991.98px) {
  .pm-about-feature-grid,
  .pm-about-bottom-side,
  .pm-about-business-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-about-trust-card + .pm-about-trust-card {
    border-left: 0;
  }
}

@media (max-width: 767.98px) {
  .pm-about-copy h1 {
    font-size: 2.3rem;
  }

  .pm-about-benefits,
  .pm-about-feature-grid,
  .pm-about-bottom-side,
  .pm-about-business-list {
    grid-template-columns: 1fr;
  }

  .pm-about-section-head h2,
  .pm-about-bottom-copy h2 {
    font-size: 1.75rem;
  }
}
</style>
