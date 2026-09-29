import client from './client';

export const itemService = {
    getItems(params = {}) {
        return client.get('/inventory-items', { params });
    },

    getItem(id) {
        return client.get(`/inventory-items/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/inventory-items/${id}/edit`);
    },

    createItem(data) {
        return client.post('/inventory-items', data);
    },

    updateItem(id, data) {
        return client.put(`/inventory-items/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/inventory-items/${id}`);
    },

    toggleStatus(id) {
        return client.post(`/inventory-items/${id}/toggle-status`);
    },
};

export default itemService;