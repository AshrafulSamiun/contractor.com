export const roundMoney = value => Math.round((Number(value || 0) + Number.EPSILON) * 100) / 100
export const lineSubtotal = item => roundMoney(Number(item.quantity || 0) * Number(item.cost_rate || 0))
export const lineTotal = item => roundMoney(lineSubtotal(item) + roundMoney(item.sales_tax))
export const invoiceFor = order => (order.documents || []).find(doc => doc.type === 'Purchase Invoice')
export function balanceFor(order, field = 'total') {
  return roundMoney((order.documents || []).reduce((total, doc) => total + (doc.type === 'Purchase Invoice' ? 1 : -1) * Number(doc[field] || 0), 0))
}
export function paymentStatus(order) {
  const invoice = invoiceFor(order)
  if (!invoice) return order.payment_status || 'Unpaid'
  if (balanceFor(order) <= 0) return 'Paid'
  if (invoice.due_date && invoice.due_date < today()) return 'Overdue'
  return balanceFor(order) < Number(invoice.total) ? 'Partially Paid' : 'Unpaid'
}
export function orderStatus(order) {
  if (!['Accepted', 'Cancelled'].includes(order.status) && order.expiry_at && new Date(order.expiry_at) < new Date()) return 'Expired'
  return order.status
}
export function today() {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}
export function localDateTime(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}T${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
}
export const money = value => Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
export const dateLabel = value => value ? new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString('en-GB') : '-'
export const dateTimeLabel = value => value ? new Date(value).toLocaleString('en-GB', { dateStyle: 'short', timeStyle: 'short' }) : '-'
