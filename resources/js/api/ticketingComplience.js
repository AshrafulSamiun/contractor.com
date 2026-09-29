import client from "./client";

export const ticketingComplienceService = {
    getItems(params = {}) {
        return client.get("/violation-tickets", { params });
    },

    getItem(id) {
        return client.get(`/violation-tickets/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/violation-tickets/${id}/edit`);
    },

    createItem(data) {
        return client.post("/violation-tickets", data);
    },

    updateItem(id, data) {
        return client.put(`/violation-tickets/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/violation-tickets/${id}`);
    },
};

export default ticketingComplienceService;
