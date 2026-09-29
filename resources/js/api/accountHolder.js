import client from './client';

export const accountHolderService = {
    getItems(params = {}) {
        return client.get('/account-holders', { params });
    },

    getItem(id) {
        return client.get(`/account-holders/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/account-holders/${id}/edit`);
    },

    createItem(data) {
        return client.post('/account-holders', data);
    },

    updateItem(id, data) {
        return client.put(`/account-holders/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/account-holders/${id}`);
    },
};

export default accountHolderService;