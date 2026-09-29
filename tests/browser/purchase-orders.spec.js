import { test, expect } from '@playwright/test'
import { today } from '../../resources/js/utils/purchaseOrders.js'

// Browser fixtures avoid creating accounting records in the developer database.
async function mockApi(page) {
  const seller = { id: 1, seller_name: 'ABC Construction Ltd.', contact_person: 'John Smith', phone: '+12125550100', email: 'john.smith@example.test', address: '123 Construction Ave, Suite 200, New York, NY 10001, USA', website: 'https://example.test', tax_number: '12-3456789', vendor_category: 'Construction Supplier', is_active: true }
  const buyer = { id: 1, name: 'Michael Brown', email: 'michael@example.test', role: 'admin', project_id: 17, selected_plan: 'enterprise', account_access_ready: true, company_name: 'ABC Properties LLC' }
  const sample = { id: 1, po_no: 'PO-7789', seller_id: 1, seller_no: 'SLR-001', seller_name: seller.seller_name, seller_details: Object.fromEntries(['contact_person', 'phone', 'email', 'address', 'website', 'tax_number', 'vendor_category'].map(key => [key, seller[key]])), requester_details: { phone: '+12125550199', email: buyer.email, company_name: buyer.company_name, address: '456 Business Park Drive, New York, NY 10001, USA' }, requested_by: buyer.name, department: 'Maintenance', project: 'Main Warehouse', po_date: today(), expiry_at: null, expected_delivery_at: null, delivery_location: 'Main Warehouse', delivery_details: { contact_person: buyer.name, phone: '+12125550199', email: buyer.email }, currency_code: 'USD', status: 'Pending', approval_status: 'Draft', payment_status: 'Unpaid', payment_term: 'Net 30', payment_method: 'Direct Bank Transfer', items: [{ name: 'PVC Pipe 1/2 inch', code: 'PVC-001', uom: 'FT', quantity: 100, cost_rate: 1.2, sales_tax: 9.6 }, { name: 'Labor Installation', code: 'LAB-004', uom: 'HRS', quantity: 20, cost_rate: 45, sales_tax: 72 }], subtotal: 1020, sales_tax: 81.6, total: 1101.6, terms: 'Supply and installation as per the project specifications.', notes: 'Please deliver during working hours.', creator: buyer, created_at: new Date().toISOString(), updated_at: new Date().toISOString(), documents: [], attachments: [], activities: [{ id: 1, actor: buyer.name, action: 'Created', changes: '[]', created_at: new Date().toISOString() }] }
  const orders = [sample]
  await page.addInitScript(() => { localStorage.setItem('pm_token', 'browser-fixture'); localStorage.setItem('pm_locale', 'en') })
  await page.route('**/api/v1/**', async route => {
    const path = new URL(route.request().url()).pathname.replace('/api/v1', '')
    const method = route.request().method()
    let data = []
    if (path === '/me') data = buyer
    if (path === '/sellers') data = [seller, { ...seller, id: 2, seller_name: 'BuildWell Ltd.', contact_person: 'Emily Brown', email: 'emily@example.test', phone: '+14165550102' }]
    if (path === '/currencies') data = [{ currency_code: 'USD' }, { currency_code: 'CAD' }]
    if (path === '/invoice-terms') data = [{ id: 1, term_name: 'Net 30', status: 1 }, { id: 2, term_name: 'Net 60', status: 1 }]
    if (path === '/payment-methods') data = [{ id: 1, name: 'Direct Bank Transfer', status_active: true }, { id: 2, name: 'Company Cheque', status_active: true }]
    if (path === '/service-items') data = { data: [{ id: 1, item_name: 'Electrical Installation', item_no: 'SER-001', unit_of_measure: 'HRS', price: 100 }], last_page: 1 }
    if (path === '/purchase-orders' && method === 'GET') data = { data: orders, last_page: 1 }
    if (path === '/purchase-orders' && method === 'POST') {
      const payload = route.request().postDataJSON()
      const order = { ...payload, id: orders.length + 1, po_no: 'PO-7790', creator: buyer, created_at: new Date().toISOString(), updated_at: new Date().toISOString(), activities: [], attachments: [], documents: [] }
      order.subtotal = order.items.reduce((n, x) => n + x.quantity * x.cost_rate, 0); order.sales_tax = order.items.reduce((n, x) => n + x.sales_tax, 0); order.total = order.subtotal + order.sales_tax
      orders.push(order); data = order
    }
    const match = path.match(/^\/purchase-orders\/(\d+)(?:\/(\w+))?$/)
    if (match) {
      const order = orders.find(x => x.id === Number(match[1]))
      if (method === 'PUT') Object.assign(order, route.request().postDataJSON())
      if (match[2] === 'workflow') {
        const { action } = route.request().postDataJSON()
        if (action === 'submit') order.approval_status = 'Submitted'
        if (action === 'approve') { order.approval_status = 'Approved'; order.status = 'Accepted' }
        if (action === 'convert') order.documents.push({ id: 1, type: 'Purchase Invoice', number: 'PINV-000001', date: today(), due_date: today(), subtotal: order.subtotal, sales_tax: order.sales_tax, total: order.total, items: order.items })
        order.activities.push({ id: order.activities.length + 1, actor: buyer.name, action, created_at: new Date().toISOString(), changes: '[]' })
      }
      if (match[2] === 'documents') {
        const doc = route.request().postDataJSON(); order.documents.push({ ...doc, id: 2, total: Number(doc.subtotal) + Number(doc.sales_tax) }); order.payment_status = 'Paid'
      }
      data = order
    }
    await route.fulfill({ json: { success: true, data } })
  })
}

test('desktop list, seller autofill, save, workflow, reports and exports', async ({ page }, testInfo) => {
  await page.setViewportSize({ width: 1700, height: 1100 })
  const errors = []; page.on('pageerror', error => errors.push(error.message))
  await mockApi(page)
  await page.goto('/accounting/purchase-orders')
  await expect(page.getByRole('button', { name: 'New Purchase Order', exact: true })).toBeVisible()
  const desktopLayout = await page.evaluate(() => ({ body: document.documentElement.scrollWidth, viewport: window.innerWidth }))
  expect(desktopLayout.body).toBeLessThanOrEqual(desktopLayout.viewport + 1)
  const listScroller = await page.locator('.po-list > .po-table-scroll').evaluate(el => ({ scroll: el.scrollWidth, width: el.clientWidth, overflow: getComputedStyle(el).overflowX }))
  expect(listScroller.scroll).toBeGreaterThanOrEqual(listScroller.width)
  expect(listScroller.overflow).toBe('auto')
  await page.screenshot({ path: testInfo.outputPath('purchase-orders-list.png'), fullPage: true })
  await page.getByRole('button', { name: 'New Purchase Order', exact: true }).click()
  await page.getByLabel('Seller', { exact: true }).fill('BuildWell Ltd.')
  await page.getByLabel('Seller', { exact: true }).press('Tab')
  await expect(page.locator('.po-details-grid')).toContainText('Emily Brown')
  await expect(page.locator('.po-details-grid')).toContainText('emily@example.test')
  await page.getByLabel('Payment Method', { exact: true }).selectOption('Company Cheque')
  await page.getByRole('button', { name: 'Add item', exact: true }).click()
  await page.getByLabel('Item or service', { exact: true }).fill('Electrical Installation')
  await page.getByLabel('Item or service', { exact: true }).press('Tab')
  await expect(page.getByLabel('Item code', { exact: true })).toHaveValue('SER-001')
  await expect(page.getByLabel('Unit of measure', { exact: true })).toHaveValue('HRS')
  await expect(page.getByLabel('Cost rate', { exact: true })).toHaveValue('100')
  await page.getByLabel('Sales tax amount', { exact: true }).fill('8')
  await page.getByRole('button', { name: 'Save', exact: true }).click()
  await expect(page.locator('.po-alert.success')).toHaveText('Purchase order saved.')
  await page.getByRole('button', { name: 'Edit', exact: true }).click()
  await page.getByLabel('Seller', { exact: true }).fill('ABC Construction Ltd.')
  await page.getByLabel('Seller', { exact: true }).press('Tab')
  await expect(page.locator('.po-details-grid')).toContainText('John Smith')
  await page.getByRole('button', { name: 'Save', exact: true }).click()
  await expect(page.getByRole('button', { name: 'Send for Approval', exact: true })).toBeEnabled()
  await page.getByRole('button', { name: 'Send for Approval', exact: true }).click()
  await expect(page.locator('.po-document-head')).toContainText('Submitted')
  await page.getByRole('button', { name: 'Approve', exact: true }).click()
  await page.getByRole('button', { name: 'Convert to Invoice', exact: true }).click()
  await expect(page.getByRole('button', { name: 'PINV-000001', exact: true })).toBeVisible()
  await expect(page.getByRole('button', { name: 'Edit', exact: true })).toBeDisabled()
  await page.screenshot({ path: testInfo.outputPath('purchase-order-detail.png'), fullPage: true })
  await page.getByRole('button', { name: 'Record payment / adjustment', exact: true }).click()
  await page.getByLabel('Document No.', { exact: true }).fill('PAY-001')
  await page.getByRole('button', { name: 'Record Document', exact: true }).click()
  await expect(page.locator('.po-sidebar')).toContainText('Paid')
  await page.getByRole('button', { name: 'View Audit Log', exact: true }).click()
  await expect(page.getByRole('dialog')).toContainText('approve')
  await page.getByRole('button', { name: 'Close dialog', exact: true }).click()
  await page.getByRole('button', { name: /Account Statement/ }).click()
  await expect(page.getByRole('dialog')).toContainText('PAY-001')
  await page.getByRole('button', { name: 'Close dialog', exact: true }).click()
  const excelDownload = page.waitForEvent('download')
  await page.getByRole('button', { name: 'Export Excel', exact: true }).click()
  expect((await excelDownload).suggestedFilename()).toMatch(/\.xlsx$/)
  const pdfDownload = page.waitForEvent('download')
  await page.getByRole('button', { name: 'Save PDF', exact: true }).click()
  expect((await pdfDownload).suggestedFilename()).toMatch(/\.pdf$/)
  expect(errors).toEqual([])
})

test('mobile forms and tables stay within the viewport', async ({ page }, testInfo) => {
  await page.setViewportSize({ width: 390, height: 844 })
  await mockApi(page)
  await page.goto('/accounting/purchase-orders')
  await page.getByRole('button', { name: 'PO-7789', exact: true }).click()
  await expect(page.locator('.po-document-head')).toContainText('PO-7789')
  await page.screenshot({ path: testInfo.outputPath('purchase-order-mobile.png'), fullPage: true })
  const overflow = await page.locator('.po-page').evaluate(el => ({ scroll: el.scrollWidth, width: el.clientWidth }))
  expect(overflow.scroll).toBeLessThanOrEqual(overflow.width + 1)
  await page.getByRole('button', { name: 'Edit', exact: true }).click()
  await page.screenshot({ path: testInfo.outputPath('purchase-order-mobile-edit.png'), fullPage: true })
  const fields = await page.locator('.po-field input, .po-field select').evaluateAll(elements => elements.every(el => el.getBoundingClientRect().right <= window.innerWidth + 1))
  expect(fields).toBe(true)
})
