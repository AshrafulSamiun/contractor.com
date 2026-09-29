import client from './client';

export const jobSiteService = {
    getItems(params = {}) {
        return client.get('/job-sites', { params });
    },

    getItem(id) {
        return client.get(`/job-sites/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/job-sites/${id}/edit`);
    },

    createItem(data) {
        return client.post('/job-sites', data);
    },

    updateItem(id, data) {
        return client.put(`/job-sites/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/job-sites/${id}`);
    },

    toggleStatus(id) {
        return client.post(`/job-sites/${id}/toggle-status`);
    },
};

export default jobSiteService;