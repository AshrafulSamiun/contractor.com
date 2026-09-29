# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: purchase-orders.spec.js >> desktop list, seller autofill, save, workflow, reports and exports
- Location: tests\browser\purchase-orders.spec.js:48:1

# Error details

```
Error: expect(locator).toHaveValue(expected) failed

Locator:  getByLabel('Item code', { exact: true })
Expected: "SER-001"
Received: ""
Timeout:  5000ms

Call log:
  - Expect "toHaveValue" getByLabel('Item code', { exact: true }) with timeout 5000ms
  - waiting for getByLabel('Item code', { exact: true })
    14 × locator resolved to <input aria-label="Item code"/>
       - unexpected value ""

```

```yaml
- textbox "Item code"
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test'
  2   | import { today } from '../../resources/js/utils/purchaseOrders.js'
  3   | 
  4   | // Browser fixtures avoid creating accounting records in the developer database.
  5   | async function mockApi(page) {
  6   |   const seller = { id: 1, seller_name: 'ABC Construction Ltd.', contact_person: 'John Smith', phone: '+12125550100', email: 'john.smith@example.test', address: '123 Construction Ave, Suite 200, New York, NY 10001, USA', website: 'https://example.test', tax_number: '12-3456789', vendor_category: 'Construction Supplier', is_active: true }
  7   |   const buyer = { id: 1, name: 'Michael Brown', email: 'michael@example.test', role: 'admin', project_id: 17, selected_plan: 'enterprise', account_access_ready: true, company_name: 'ABC Properties LLC' }
  8   |   const sample = { id: 1, po_no: 'PO-7789', seller_id: 1, seller_no: 'SLR-001', seller_name: seller.seller_name, seller_details: Object.fromEntries(['contact_person', 'phone', 'email', 'address', 'website', 'tax_number', 'vendor_category'].map(key => [key, seller[key]])), requester_details: { phone: '+12125550199', email: buyer.email, company_name: buyer.company_name, address: '456 Business Park Drive, New York, NY 10001, USA' }, requested_by: buyer.name, department: 'Maintenance', project: 'Main Warehouse', po_date: today(), expiry_at: null, expected_delivery_at: null, delivery_location: 'Main Warehouse', delivery_details: { contact_person: buyer.name, phone: '+12125550199', email: buyer.email }, currency_code: 'USD', status: 'Pending', approval_status: 'Draft', payment_status: 'Unpaid', payment_term: 'Net 30', payment_method: 'Direct Bank Transfer', items: [{ name: 'PVC Pipe 1/2 inch', code: 'PVC-001', uom: 'FT', quantity: 100, cost_rate: 1.2, sales_tax: 9.6 }, { name: 'Labor Installation', code: 'LAB-004', uom: 'HRS', quantity: 20, cost_rate: 45, sales_tax: 72 }], subtotal: 1020, sales_tax: 81.6, total: 1101.6, terms: 'Supply and installation as per the project specifications.', notes: 'Please deliver during working hours.', creator: buyer, created_at: new Date().toISOString(), updated_at: new Date().toISOString(), documents: [], attachments: [], activities: [{ id: 1, actor: buyer.name, action: 'Created', changes: '[]', created_at: new Date().toISOString() }] }
  9   |   const orders = [sample]
  10  |   await page.addInitScript(() => { localStorage.setItem('pm_token', 'browser-fixture'); localStorage.setItem('pm_locale', 'en') })
  11  |   await page.route('**/api/v1/**', async route => {
  12  |     const path = new URL(route.request().url()).pathname.replace('/api/v1', '')
  13  |     const method = route.request().method()
  14  |     let data = []
  15  |     if (path === '/me') data = buyer
  16  |     if (path === '/sellers') data = [seller, { ...seller, id: 2, seller_name: 'BuildWell Ltd.', contact_person: 'Emily Brown', email: 'emily@example.test', phone: '+14165550102' }]
  17  |     if (path === '/currencies') data = [{ currency_code: 'USD' }, { currency_code: 'CAD' }]
  18  |     if (path === '/invoice-terms') data = [{ id: 1, term_name: 'Net 30', status: 1 }, { id: 2, term_name: 'Net 60', status: 1 }]
  19  |     if (path === '/payment-methods') data = [{ id: 1, name: 'Direct Bank Transfer', status_active: true }, { id: 2, name: 'Company Cheque', status_active: true }]
  20  |     if (path === '/service-items') data = { data: [{ id: 1, item_name: 'Electrical Installation', item_no: 'SER-001', unit_of_measure: 'HRS', price: 100 }], last_page: 1 }
  21  |     if (path === '/purchase-orders' && method === 'GET') data = { data: orders, last_page: 1 }
  22  |     if (path === '/purchase-orders' && method === 'POST') {
  23  |       const payload = route.request().postDataJSON()
  24  |       const order = { ...payload, id: orders.length + 1, po_no: 'PO-7790', creator: buyer, created_at: new Date().toISOString(), updated_at: new Date().toISOString(), activities: [], attachments: [], documents: [] }
  25  |       order.subtotal = order.items.reduce((n, x) => n + x.quantity * x.cost_rate, 0); order.sales_tax = order.items.reduce((n, x) => n + x.sales_tax, 0); order.total = order.subtotal + order.sales_tax
  26  |       orders.push(order); data = order
  27  |     }
  28  |     const match = path.match(/^\/purchase-orders\/(\d+)(?:\/(\w+))?$/)
  29  |     if (match) {
  30  |       const order = orders.find(x => x.id === Number(match[1]))
  31  |       if (method === 'PUT') Object.assign(order, route.request().postDataJSON())
  32  |       if (match[2] === 'workflow') {
  33  |         const { action } = route.request().postDataJSON()
  34  |         if (action === 'submit') order.approval_status = 'Submitted'
  35  |         if (action === 'approve') { order.approval_status = 'Approved'; order.status = 'Accepted' }
  36  |         if (action === 'convert') order.documents.push({ id: 1, type: 'Purchase Invoice', number: 'PINV-000001', date: today(), due_date: today(), subtotal: order.subtotal, sales_tax: order.sales_tax, total: order.total, items: order.items })
  37  |         order.activities.push({ id: order.activities.length + 1, actor: buyer.name, action, created_at: new Date().toISOString(), changes: '[]' })
  38  |       }
  39  |       if (match[2] === 'documents') {
  40  |         const doc = route.request().postDataJSON(); order.documents.push({ ...doc, id: 2, total: Number(doc.subtotal) + Number(doc.sales_tax) }); order.payment_status = 'Paid'
  41  |       }
  42  |       data = order
  43  |     }
  44  |     await route.fulfill({ json: { success: true, data } })
  45  |   })
  46  | }
  47  | 
  48  | test('desktop list, seller autofill, save, workflow, reports and exports', async ({ page }, testInfo) => {
  49  |   await page.setViewportSize({ width: 1700, height: 1100 })
  50  |   const errors = []; page.on('pageerror', error => errors.push(error.message))
  51  |   await mockApi(page)
  52  |   await page.goto('/accounting/purchase-orders')
  53  |   await expect(page.getByRole('button', { name: 'New Purchase Order', exact: true })).toBeVisible()
  54  |   const desktopLayout = await page.evaluate(() => ({ body: document.documentElement.scrollWidth, viewport: window.innerWidth }))
  55  |   expect(desktopLayout.body).toBeLessThanOrEqual(desktopLayout.viewport + 1)
  56  |   const listScroller = await page.locator('.po-list > .po-table-scroll').evaluate(el => ({ scroll: el.scrollWidth, width: el.clientWidth, overflow: getComputedStyle(el).overflowX }))
  57  |   expect(listScroller.scroll).toBeGreaterThanOrEqual(listScroller.width)
  58  |   expect(listScroller.overflow).toBe('auto')
  59  |   await page.screenshot({ path: testInfo.outputPath('purchase-orders-list.png'), fullPage: true })
  60  |   await page.getByRole('button', { name: 'New Purchase Order', exact: true }).click()
  61  |   await page.getByLabel('Seller', { exact: true }).fill('BuildWell Ltd.')
  62  |   await page.getByLabel('Seller', { exact: true }).press('Tab')
  63  |   await expect(page.locator('.po-details-grid')).toContainText('Emily Brown')
  64  |   await expect(page.locator('.po-details-grid')).toContainText('emily@example.test')
  65  |   await page.getByLabel('Payment Method', { exact: true }).selectOption('Company Cheque')
  66  |   await page.getByRole('button', { name: 'Add item', exact: true }).click()
  67  |   await page.getByLabel('Item or service', { exact: true }).fill('Electrical Installation')
  68  |   await page.getByLabel('Item or service', { exact: true }).press('Tab')
> 69  |   await expect(page.getByLabel('Item code', { exact: true })).toHaveValue('SER-001')
      |                                                               ^ Error: expect(locator).toHaveValue(expected) failed
  70  |   await expect(page.getByLabel('Unit of measure', { exact: true })).toHaveValue('HRS')
  71  |   await expect(page.getByLabel('Cost rate', { exact: true })).toHaveValue('100')
  72  |   await page.getByLabel('Sales tax amount', { exact: true }).fill('8')
  73  |   await page.getByRole('button', { name: 'Save', exact: true }).click()
  74  |   await expect(page.locator('.po-alert.success')).toHaveText('Purchase order saved.')
  75  |   await page.getByRole('button', { name: 'Edit', exact: true }).click()
  76  |   await page.getByLabel('Seller', { exact: true }).fill('ABC Construction Ltd.')
  77  |   await page.getByLabel('Seller', { exact: true }).press('Tab')
  78  |   await expect(page.locator('.po-details-grid')).toContainText('John Smith')
  79  |   await page.getByRole('button', { name: 'Save', exact: true }).click()
  80  |   await expect(page.getByRole('button', { name: 'Send for Approval', exact: true })).toBeEnabled()
  81  |   await page.getByRole('button', { name: 'Send for Approval', exact: true }).click()
  82  |   await expect(page.locator('.po-document-head')).toContainText('Submitted')
  83  |   await page.getByRole('button', { name: 'Approve', exact: true }).click()
  84  |   await page.getByRole('button', { name: 'Convert to Invoice', exact: true }).click()
  85  |   await expect(page.getByRole('button', { name: 'PINV-000001', exact: true })).toBeVisible()
  86  |   await expect(page.getByRole('button', { name: 'Edit', exact: true })).toBeDisabled()
  87  |   await page.screenshot({ path: testInfo.outputPath('purchase-order-detail.png'), fullPage: true })
  88  |   await page.getByRole('button', { name: 'Record payment / adjustment', exact: true }).click()
  89  |   await page.getByLabel('Document No.', { exact: true }).fill('PAY-001')
  90  |   await page.getByRole('button', { name: 'Record Document', exact: true }).click()
  91  |   await expect(page.locator('.po-sidebar')).toContainText('Paid')
  92  |   await page.getByRole('button', { name: 'View Audit Log', exact: true }).click()
  93  |   await expect(page.getByRole('dialog')).toContainText('approve')
  94  |   await page.getByRole('button', { name: 'Close dialog', exact: true }).click()
  95  |   await page.getByRole('button', { name: /Account Statement/ }).click()
  96  |   await expect(page.getByRole('dialog')).toContainText('PAY-001')
  97  |   await page.getByRole('button', { name: 'Close dialog', exact: true }).click()
  98  |   const excelDownload = page.waitForEvent('download')
  99  |   await page.getByRole('button', { name: 'Export Excel', exact: true }).click()
  100 |   expect((await excelDownload).suggestedFilename()).toMatch(/\.xlsx$/)
  101 |   const pdfDownload = page.waitForEvent('download')
  102 |   await page.getByRole('button', { name: 'Save PDF', exact: true }).click()
  103 |   expect((await pdfDownload).suggestedFilename()).toMatch(/\.pdf$/)
  104 |   expect(errors).toEqual([])
  105 | })
  106 | 
  107 | test('mobile forms and tables stay within the viewport', async ({ page }, testInfo) => {
  108 |   await page.setViewportSize({ width: 390, height: 844 })
  109 |   await mockApi(page)
  110 |   await page.goto('/accounting/purchase-orders')
  111 |   await page.getByRole('button', { name: 'PO-7789', exact: true }).click()
  112 |   await expect(page.locator('.po-document-head')).toContainText('PO-7789')
  113 |   await page.screenshot({ path: testInfo.outputPath('purchase-order-mobile.png'), fullPage: true })
  114 |   const overflow = await page.locator('.po-page').evaluate(el => ({ scroll: el.scrollWidth, width: el.clientWidth }))
  115 |   expect(overflow.scroll).toBeLessThanOrEqual(overflow.width + 1)
  116 |   await page.getByRole('button', { name: 'Edit', exact: true }).click()
  117 |   await page.screenshot({ path: testInfo.outputPath('purchase-order-mobile-edit.png'), fullPage: true })
  118 |   const fields = await page.locator('.po-field input, .po-field select').evaluateAll(elements => elements.every(el => el.getBoundingClientRect().right <= window.innerWidth + 1))
  119 |   expect(fields).toBe(true)
  120 | })
  121 | 
```