import client from './client';

export const serviceItemService = {
    getItems(params = {}) {
        return client.get('/service-items', { params });
    },

    getItem(id) {
        return client.get(`/service-items/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/service-items/${id}/edit`);
    },

    createItem(data) {
        return client.post('/service-items', data);
    },

    updateItem(id, data) {
        return client.put(`/service-items/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/service-items/${id}`);
    },

    toggleStatus(id) {
        return client.post(`/service-items/${id}/toggle-status`);
    },
};

export default serviceItemService;