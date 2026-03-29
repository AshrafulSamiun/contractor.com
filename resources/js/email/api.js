import client from '../api/client'

export const fetchMessages = (folder, params = {}) => {
  return client.get('/email/messages', { params: { folder, ...params } })
}

export const getMessage = (id) => client.get(`/email/messages/${id}`)
export const createMessage = (payload) => client.post('/email/messages', payload)
export const updateMessage = (id, payload) => client.put(`/email/messages/${id}`, payload)
export const sendMessage = (id, payload) => client.post(`/email/messages/${id}/send`, payload)
export const trashMessage = (id) => client.post(`/email/messages/${id}/trash`)
export const restoreMessage = (id) => client.post(`/email/messages/${id}/restore`)
export const deleteMessage = (id) => client.delete(`/email/messages/${id}`)
export const fetchThread = (threadId) => client.get(`/email/threads/${threadId}`)
export const syncInbox = () => client.post('/email/messages/sync')
export const markThread = (threadId, status) =>
  client.post(`/email/threads/${threadId}/mark`, { status })
export const uploadAttachments = (messageId, files) => {
  const formData = new FormData()
  files.forEach((file) => formData.append('files[]', file))
  return client.post(`/email/messages/${messageId}/attachments`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}
export const deleteAttachment = (messageId, attachmentId) =>
  client.delete(`/email/messages/${messageId}/attachments/${attachmentId}`)
export const downloadAttachment = (messageId, attachmentId) =>
  client.get(`/email/messages/${messageId}/attachments/${attachmentId}`, { responseType: 'blob' })

export const fetchTemplates = () => client.get('/email/templates')
export const createTemplate = (payload) => client.post('/email/templates', payload)
export const updateTemplate = (id, payload) => client.put(`/email/templates/${id}`, payload)
export const deleteTemplate = (id) => client.delete(`/email/templates/${id}`)

export const fetchEmailSettings = () => client.get('/email/settings')
export const updateEmailSettings = (payload, scope = 'user') =>
  client.put(`/email/settings${scope === 'global' ? '?scope=global' : ''}`, payload)
