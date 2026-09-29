import client from "./client";

export const insuranceService = {
    getItems(params = {}) {
        return client.get("/insurance-policies", { params });
    },

    getItem(id) {
        return client.get(`/insurance-policies/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/insurance-policies/${id}/edit`);
    },

    createItem(data) {
        return client.post("/insurance-policies", data);
    },

    updateItem(id, data) {
        return client.put(`/insurance-policies/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/insurance-policies/${id}`);
    },
};

export default insuranceService;
