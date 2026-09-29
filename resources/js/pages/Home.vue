<template>
  <div class="pm-home-page">
    <PublicHeader />

    <section class="pm-home-hero">
      <div class="container">
        <div class="pm-home-hero-grid">
          <div class="pm-home-copy">
            <div class="pm-home-kicker">ALL-IN-ONE CONTRACTOR MANAGEMENT SOFTWARE</div>
            <h1>Run Your Contractor Business Smarter</h1>
            <p class="pm-home-subtitle">
              Manage jobs, schedules, employees, invoices, payments and customers in one powerful platform.
            </p>

            <div class="pm-home-benefits">
              <div class="pm-home-benefit">
                <span class="pm-home-check">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20 6L9 17L4 12" />
                  </svg>
                </span>
                <span>Save Time</span>
              </div>
              <div class="pm-home-benefit">
                <span class="pm-home-check">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20 6L9 17L4 12" />
                  </svg>
                </span>
                <span>Stay Organized</span>
              </div>
              <div class="pm-home-benefit">
                <span class="pm-home-check">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20 6L9 17L4 12" />
                  </svg>
                </span>
                <span>Improve Productivity</span>
              </div>
              <div class="pm-home-benefit">
                <span class="pm-home-check">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20 6L9 17L4 12" />
                  </svg>
                </span>
                <span>Grow Your Business</span>
              </div>
            </div>
          </div>

          <div class="pm-home-visual">
            <img class="pm-home-hero-art" :src="heroVisual" alt="ContractorPro hero visual" />
          </div>
        </div>
      </div>
    </section>

    <section class="pm-home-stats">
      <div class="container">
        <div class="pm-home-stats-grid">
          <div class="pm-home-stat-card">
            <div class="pm-home-stat-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M16 11C17.66 11 19 9.66 19 8S17.66 5 16 5 13 6.34 13 8s1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.96 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
              </svg>
            </div>
            <div>
              <div class="pm-home-stat-value">5,000+</div>
              <div class="pm-home-stat-label">Contractors</div>
            </div>
          </div>

          <div class="pm-home-stat-card">
            <div class="pm-home-stat-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M10 4H4v6h6V4zm10 0h-6v6h6V4zM10 14H4v6h6v-6zm10 0h-6v6h6v-6z"/>
              </svg>
            </div>
            <div>
              <div class="pm-home-stat-value">50,000+</div>
              <div class="pm-home-stat-label">Jobs Managed</div>
            </div>
          </div>

          <div class="pm-home-stat-card">
            <div class="pm-home-stat-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm8 1.5V8h4.5"/>
                <path d="M8 13h8M8 17h8M8 9h3"/>
              </svg>
            </div>
            <div>
              <div class="pm-home-stat-value">120,000+</div>
              <div class="pm-home-stat-label">Invoices Sent</div>
            </div>
          </div>

          <div class="pm-home-stat-card">
            <div class="pm-home-stat-icon">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M19 18H6a4 4 0 1 1 .6-7.96A6 6 0 0 1 18 11a3.5 3.5 0 0 1 1 7z"/>
              </svg>
            </div>
            <div>
              <div class="pm-home-stat-value">99.9%</div>
              <div class="pm-home-stat-label">Uptime</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="pm-home-sections">
      <div class="container">
        <div class="pm-home-sections-grid">
          <div class="pm-home-section-block">
            <h2>Built for Every Type of Contractor</h2>
            <div class="pm-home-industry-grid">
              <div v-for="industry in industries" :key="industry.name" class="pm-home-industry-card">
                <div class="pm-home-industry-icon" :style="{ color: industry.color }" v-html="industry.icon"></div>
                <div class="pm-home-industry-name">{{ industry.name }}</div>
              </div>
            </div>
            <RouterLink class="pm-home-link" to="/about">View all industries</RouterLink>
          </div>

          <div class="pm-home-section-block">
            <h2>Powerful Features to Grow Your Business</h2>
            <div class="pm-home-feature-grid">
              <div v-for="feature in features" :key="feature.title" class="pm-home-feature-card">
                <div class="pm-home-feature-icon" :style="{ color: feature.color }" v-html="feature.icon"></div>
                <div>
                  <h3>{{ feature.title }}</h3>
                  <p>{{ feature.text }}</p>
                </div>
              </div>
            </div>
            <RouterLink class="pm-home-link" to="/plans">View all features</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <PublicFooter />
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import PublicHeader from '../components/PublicHeader.vue'
import PublicFooter from '../components/PublicFooter.vue'
const heroVisual = '/hero1.png'

const industryIcons = {
  plumbing: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 3v4.1a5 5 0 0 1-2 9.8V21h-2v-4.1a5 5 0 0 1-2-9.8V3h2v3h2V3h2Zm-4 5a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm6-5v3h2V3h2v5h-4V6h-2V3h2ZM4 3v3h2V3h2v5H4V6H2V3h2Z"/></svg>',
  hvac: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M11 2h2v5l2.6-2.6 1.4 1.4L14.4 8.4H19v2h-5l2.6 2.6-1.4 1.4L13 11.8V17h4.2l2.2-2.2 1.4 1.4-1.2 1.2H22v2h-2.4l1.2 1.2-1.4 1.4-2.2-2.2H13v2h-2v-5l-2.6 2.6-1.4-1.4 2.6-2.6H5v-2h5l-2.6-2.6 1.4-1.4L11 14.2V9H6.8L4.6 11.2 3.2 9.8 4.4 8.6H2v-2h2.4L3.2 5.4 4.6 4 6.8 6.2H11V2Z"/></svg>',
  electrical: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13 2 5 13h5l-1 9 8-11h-5l1-9Z"/></svg>',
  painting: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 3h5a2 2 0 0 1 2 2v4a3 3 0 0 1-3 3h-1v7a2 2 0 0 1-2 2h-1v-9h-2V5a2 2 0 0 1 2-2Zm-8 9h5v9H8a2 2 0 0 1-2-2v-7Z"/></svg>',
  roofing: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 12 12 4l9 8-1.3 1.5L12 6.7 4.3 13.5 3 12Zm4 2.5L12 10l5 4.5-1.3 1.5-3.7-3.2-3.7 3.2L7 14.5Z"/></svg>',
  landscaping: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 3c-6.1.2-10.4 2.2-13 6-1.6 2.4-2.3 5.2-2 8.5 2.8.3 5.6-.4 8-2 3.9-2.5 5.9-6.9 7-12.5Zm-7.1 10.5c-1.6 1-3.4 1.5-5.4 1.5.1-2 .7-3.8 1.8-5.4 1.6-2.3 4.2-3.9 7.8-4.8-1 3.7-2.6 6.2-4.2 8.7Z"/></svg>',
  applianceRepair: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m7.1 7.1 2.8 2.8-1.4 1.4-2.8-2.8A5 5 0 0 0 3 13a5 5 0 0 0 8.5 3.5l6.1-6.1-2.1-2.1 1.4-1.4 2.1 2.1 1.9-1.9a2 2 0 1 1 2.8 2.8l-1.9 1.9 2.1 2.1-1.4 1.4-2.1-2.1-6.1 6.1A7 7 0 1 1 7.1 7.1Z"/></svg>',
  handyman: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 7a2 2 0 0 1 2-2h3V3h6v2h3a2 2 0 0 1 2 2v3H4V7Zm0 5h16v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7Zm6 2v2h4v-2h-4Z"/></svg>',
  cleaning: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10 3h4v2h1a2 2 0 0 1 2 2v2h-2V7h-6v2H7V7a2 2 0 0 1 2-2h1V3Zm-2 7h8l1.5 10.5A1.5 1.5 0 0 1 16 22H8a1.5 1.5 0 0 1-1.5-1.5L8 10Zm9.5 1.5 2.5 2.5-1.4 1.4-2.5-2.5 1.4-1.4ZM6 10h12v2H6v-2Z"/></svg>',
  more: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6 12a2 2 0 1 1 0 .01V12Zm6 0a2 2 0 1 1 0 .01V12Zm6 0a2 2 0 1 1 0 .01V12Z"/></svg>',
}

const featureIcons = {
  jobManagement: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M9 2h6v3h4a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4V2Zm2 2v1h2V4h-2Zm-4 5v2h10V9H7Zm0 4v2h10v-2H7Zm0 4v2h6v-2H7Z"/></svg>',
  scheduling: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2V2Zm12 8H5v10h14V10ZM5 8h14V6H5v2Z"/></svg>',
  dispatch: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a7 7 0 0 1 7 7c0 4.2-4.7 9.95-6.3 11.77a1 1 0 0 1-1.4 0C9.7 18.95 5 13.2 5 9a7 7 0 0 1 7-7Zm0 4.5A2.5 2.5 0 1 0 12 11a2.5 2.5 0 0 0 0-4.5Z"/></svg>',
  invoicing: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm1 3h-2v1.1a3.5 3.5 0 0 0 .5 6.96h1a1.5 1.5 0 1 1 0 3H9v2h2v1h2v-1.1a3.5 3.5 0 0 0-.5-6.96h-1a1.5 1.5 0 1 1 0-3H15V5h-2V4Z"/></svg>',
  customer: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-8 1a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm8 2c-2.67 0-8 1.34-8 4v2h14v-2c0-2.66-5.33-4-8-4ZM8 14c-.29 0-.62.02-.97.05C4.8 14.31 2 15.3 2 17v3h4v-2c0-1.48.8-2.6 1.97-3.45A12.9 12.9 0 0 0 8 14Z"/></svg>',
  reports: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 20h16v2H2V4h2v16Zm4-2H5v-6h3v6Zm5 0h-3V8h3v10Zm5 0h-3V5h3v13Z"/></svg>',
  employee: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11C17.66 11 19 9.66 19 8S17.66 5 16 5 13 6.34 13 8s1.34 3 3 3Zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.96 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z"/></svg>',
  timesheets: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm1 5h-2v6l5 3 .9-1.45-3.9-2.3V7Z"/></svg>',
  vehicle: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 11 6.5 6.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11a3 3 0 0 1 2 2.82V18h-2v2h-2v-2H7v2H5v-2H3v-4.18A3 3 0 0 1 5 11Zm2.1-1h9.8l-.9-2.7a.5.5 0 0 0-.47-.3H8.47a.5.5 0 0 0-.47.34L7.1 10ZM7 15.5A1.5 1.5 0 1 0 7 12.5a1.5 1.5 0 0 0 0 3Zm10 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>',
}

const industries = [
  { icon: industryIcons.plumbing, name: 'Plumbing', color: '#3b82f6' },
  { icon: industryIcons.hvac, name: 'HVAC', color: '#2563eb' },
  { icon: industryIcons.electrical, name: 'Electrical', color: '#f59e0b' },
  { icon: industryIcons.painting, name: 'Painting', color: '#22c55e' },
  { icon: industryIcons.roofing, name: 'Roofing', color: '#ef4444' },
  { icon: industryIcons.landscaping, name: 'Landscaping', color: '#65a30d' },
  { icon: industryIcons.applianceRepair, name: 'Appliance Repair', color: '#9333ea' },
  { icon: industryIcons.handyman, name: 'Handyman', color: '#f97316' },
  { icon: industryIcons.cleaning, name: 'Cleaning Services', color: '#3b82f6' },
  { icon: industryIcons.more, name: 'More', color: '#64748b' },
]

const features = [
  { icon: featureIcons.jobManagement, title: 'Job Management', text: 'Create, assign and track jobs from start to finish.', color: '#2563eb' },
  { icon: featureIcons.scheduling, title: 'Scheduling & Calendar', text: 'Schedule jobs, appointments and manage your time.', color: '#22c55e' },
  { icon: featureIcons.dispatch, title: 'Dispatch & Tracking', text: 'Dispatch technicians and track in real-time.', color: '#9333ea' },
  { icon: featureIcons.invoicing, title: 'Invoicing & Payments', text: 'Create invoices and get paid faster online.', color: '#65a30d' },
  { icon: featureIcons.customer, title: 'Customer Management', text: 'Keep all your customers and job history in one place.', color: '#f59e0b' },
  { icon: featureIcons.reports, title: 'Reports & Analytics', text: 'Get real-time insights and make better decisions.', color: '#2563eb' },
  { icon: featureIcons.employee, title: 'Employee Management', text: 'Manage employees, roles and performance.', color: '#2563eb' },
  { icon: featureIcons.timesheets, title: 'Timesheets', text: 'Track work hours and manage timesheets.', color: '#16a34a' },
  { icon: featureIcons.vehicle, title: 'Vehicle Management', text: 'Manage vehicles, drivers, maintenance and fuel.', color: '#2563eb' },
]
</script>

<style scoped>
.pm-home-page {
  background: #ffffff;
}

.pm-home-hero {
  padding: 22px 0 8px;
  background:
    radial-gradient(760px 420px at 8% 12%, rgba(37, 99, 235, 0.08), transparent 60%),
    radial-gradient(620px 340px at 92% 16%, rgba(59, 130, 246, 0.08), transparent 60%),
    #ffffff;
}

.pm-home-hero-grid {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  gap: 28px;
  align-items: center;
}

.pm-home-copy {
  padding-top: 10px;
}

.pm-home-kicker {
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

.pm-home-copy h1 {
  max-width: 520px;
  margin: 0 0 16px;
  color: #12234a;
  font-size: clamp(1.75rem, 3.6vw, 3.05rem);
  line-height: 1.08;
  letter-spacing: -0.04em;
}

.pm-home-subtitle {
  max-width: 520px;
  margin: 0 0 22px;
  color: #566884;
  font-size: 1.06rem;
  line-height: 1.65;
}

.pm-home-benefits {
  display: flex;
  flex-wrap: wrap;
  gap: 14px 22px;
}

.pm-home-benefit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #223154;
  font-weight: 600;
}

.pm-home-check {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 1.5px solid #2563eb;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pm-home-check svg {
  width: 12px;
  height: 12px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2.4;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.pm-home-visual {
  display: flex;
  align-items: flex-end;
}

.pm-home-hero-art {
  width: min(100%, 980px);
  display: block;
  margin-left: auto;
  filter: drop-shadow(0 28px 52px rgba(15, 23, 42, 0.16));
}

.pm-home-stats {
  padding: 10px 0 8px;
}

.pm-home-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  border-top: 1px solid #edf1f7;
  border-bottom: 1px solid #edf1f7;
}

.pm-home-stat-card {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 20px 14px;
}

.pm-home-stat-card + .pm-home-stat-card {
  border-left: 1px solid #edf1f7;
}

.pm-home-stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(180deg, #eef4ff 0%, #e4edff 100%);
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
}

.pm-home-stat-icon svg {
  width: 24px;
  height: 24px;
  fill: currentColor;
  stroke: currentColor;
  stroke-width: 1.2;
}

.pm-home-stat-value {
  color: #12234a;
  font-size: 1.95rem;
  line-height: 1;
  font-weight: 800;
}

.pm-home-stat-label {
  color: #64748b;
  font-weight: 600;
  margin-top: 4px;
}

.pm-home-sections {
  padding: 8px 0 30px;
}

.pm-home-sections-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 22px;
  align-items: start;
}

.pm-home-section-block h2 {
  margin: 0 0 16px;
  text-align: center;
  color: #12234a;
  font-size: 1.95rem;
}

.pm-home-industry-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 12px;
}

.pm-home-industry-card,
.pm-home-feature-card {
  background: #ffffff;
  border: 1px solid #e7eef9;
  border-radius: 18px;
}

.pm-home-industry-card {
  text-align: center;
  padding: 18px 10px 16px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.pm-home-industry-icon {
  width: 42px;
  height: 42px;
  margin: 0 auto 10px;
  border-radius: 14px;
  background: #f8fbff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.28rem;
  font-weight: 800;
}

.pm-home-industry-icon :deep(svg),
.pm-home-feature-icon :deep(svg) {
  width: 24px;
  height: 24px;
  display: block;
}

.pm-home-industry-name {
  color: #1f2d4d;
  font-weight: 700;
  font-size: 0.92rem;
}

.pm-home-feature-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.pm-home-feature-card {
  display: flex;
  gap: 12px;
  padding: 16px 14px;
  min-height: 112px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.pm-home-feature-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: #f8fbff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 800;
  flex: 0 0 auto;
}

.pm-home-feature-card h3 {
  margin: 0 0 5px;
  color: #12234a;
  font-size: 0.96rem;
}

.pm-home-feature-card p {
  margin: 0;
  color: #64748b;
  font-size: 0.84rem;
  line-height: 1.55;
}

.pm-home-link {
  display: inline-flex;
  align-items: center;
  margin-top: 18px;
  color: #2563eb;
  font-weight: 700;
}

.pm-home-link::after {
  content: "->";
  margin-left: 8px;
}

@media (max-width: 1199.98px) {
  .pm-home-hero-grid,
  .pm-home-sections-grid {
    grid-template-columns: 1fr;
  }

  .pm-home-copy {
    text-align: center;
  }

  .pm-home-copy h1,
  .pm-home-subtitle {
    margin-left: auto;
    margin-right: auto;
  }

  .pm-home-benefits {
    justify-content: center;
  }
}

@media (max-width: 991.98px) {
  .pm-home-visual {
    justify-content: center;
  }

  .pm-home-stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-home-stat-card:nth-child(3) {
    border-left: 0;
  }

  .pm-home-feature-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pm-home-industry-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 767.98px) {
  .pm-home-copy h1 {
    font-size: 2.5rem;
  }

  .pm-home-stats-grid,
  .pm-home-feature-grid {
    grid-template-columns: 1fr;
  }

  .pm-home-stat-card + .pm-home-stat-card {
    border-left: 0;
    border-top: 1px solid #edf1f7;
  }

  .pm-home-industry-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
