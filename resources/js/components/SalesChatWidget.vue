<template>
  <div class="sales-chat-widget" data-sales-chat="true" :data-chat-endpoint="chatEndpoint">
    <button class="sales-chat-toggle" type="button" aria-expanded="false" aria-controls="sales-chat-panel">
      <span class="sales-chat-toggle-icon">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M4 4h16v12H7l-3 3V4z" fill="currentColor"/>
        </svg>
      </span>
      <span class="sales-chat-toggle-text">Chat with our team</span>
    </button>

    <div class="sales-chat-panel" id="sales-chat-panel" aria-hidden="true" role="dialog" aria-label="Chat with our team">
      <div class="sales-chat-header">
        <div class="sales-chat-title">
          <strong>Chat with our team</strong>
          <span>Parcel Advisor</span>
        </div>
        <div class="sales-chat-actions">
          <button class="sales-chat-minimize" type="button" aria-label="Minimize chat">
            &minus;
          </button>
          <button class="sales-chat-close" type="button" aria-label="Close chat">
            &times;
          </button>
        </div>
      </div>

      <div class="sales-chat-body">
        <div class="sales-chat-intro">
          <p>Welcome to DeskDrop. Submit your info and a specialist will join the chat.</p>
        </div>

        <form class="sales-chat-form" novalidate>
          <div class="sales-chat-field is-hidden" aria-hidden="true">
            <label for="sales-chat-id">Chat No</label>
            <input id="sales-chat-id" name="chat_no" type="text" readonly>
          </div>
          <div class="sales-chat-field is-hidden" aria-hidden="true">
            <label for="sales-chat-datetime">Date &amp; time</label>
            <input id="sales-chat-datetime" name="chat_datetime" type="text" readonly>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-first-name">First name*</label>
            <input id="sales-chat-first-name" name="first_name" type="text" autocomplete="given-name" placeholder="First name">
            <span class="sales-chat-error" data-error-for="first_name"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-last-name">Last name*</label>
            <input id="sales-chat-last-name" name="last_name" type="text" autocomplete="family-name" placeholder="Last name">
            <span class="sales-chat-error" data-error-for="last_name"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-company">Company name*</label>
            <input id="sales-chat-company" name="company_name" type="text" autocomplete="organization" placeholder="Company name">
            <span class="sales-chat-error" data-error-for="company_name"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-email">Business email*</label>
            <input id="sales-chat-email" name="work_email" type="email" autocomplete="email" placeholder="name@company.com">
            <span class="sales-chat-error" data-error-for="work_email"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-phone">Business phone*</label>
            <input id="sales-chat-phone" name="business_phone" type="tel" autocomplete="tel" placeholder="+1 555 000 0000">
            <span class="sales-chat-error" data-error-for="business_phone"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-country">Country/Region*</label>
            <select id="sales-chat-country" name="country_id" data-country-select="true">
              <option value="">Select a country</option>
            </select>
            <span class="sales-chat-error" data-error-for="country_id"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-city">City*</label>
            <input id="sales-chat-city" name="city" type="text" autocomplete="address-level2" placeholder="City">
            <span class="sales-chat-error" data-error-for="city"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-call">Best time to call*</label>
            <input id="sales-chat-call" name="call_time" type="datetime-local">
            <span class="sales-chat-error" data-error-for="call_time"></span>
          </div>
          <div class="sales-chat-field">
            <label for="sales-chat-inquiry">What would you like to know?*</label>
            <textarea id="sales-chat-inquiry" name="inquiry" rows="3" placeholder="Tell us what you want to learn or solve."></textarea>
            <span class="sales-chat-error" data-error-for="inquiry"></span>
          </div>
          <label class="sales-chat-consent">
            <input type="checkbox" name="marketing_opt_in">
            <span>Receive product updates and event invitations. Unsubscribe anytime.</span>
          </label>
          <div class="sales-chat-cta">
            <button class="sales-chat-btn sales-chat-btn-secondary" type="button">Cancel</button>
            <button class="sales-chat-btn" type="submit">Submit</button>
          </div>
        </form>

        <div class="sales-chat-thread sales-chat-hidden" role="log" aria-live="polite">
          <div class="sales-chat-message assistant">
            <div class="sales-chat-bubble">Your information has been verified. How can I help you today?</div>
          </div>
        </div>

        <form class="sales-chat-input sales-chat-hidden">
          <input type="text" name="message" placeholder="Type your message">
          <button class="sales-chat-send" type="submit">Send</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

const chatEndpoint = '/api/v1/sales-chat/reply'
const chatAssetVersion = '20260228.1'

const ensureAsset = (tag, attributes) => {
  const selector = Object.entries(attributes)
    .map(([key, value]) => `[${key}="${value}"]`)
    .join('');
  if (document.querySelector(`${tag}${selector}`)) return;
  const el = document.createElement(tag);
  Object.entries(attributes).forEach(([key, value]) => {
    el.setAttribute(key, value);
  });
  document.head.appendChild(el);
};

onMounted(() => {
  ensureAsset('link', { rel: 'stylesheet', href: `/chat-widget/sales-chat.css?v=${chatAssetVersion}` });
  ensureAsset('script', { src: `/chat-widget/sales-chat.js?v=${chatAssetVersion}`, defer: 'true' });
});
</script>
