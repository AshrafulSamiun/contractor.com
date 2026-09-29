import { defineConfig } from '@playwright/test'

export default defineConfig({
  testDir: './tests/browser',
  testMatch: 'purchase-orders.spec.js',
  timeout: 60000,
  workers: 1,
  use: {
    baseURL: process.env.PO_TEST_URL || 'http://localhost:8000',
    browserName: 'chromium',
    channel: 'msedge',
    headless: true,
    screenshot: 'only-on-failure',
  },
})
