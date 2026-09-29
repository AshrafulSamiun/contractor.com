import client from "./client";

export const accidentService = {
    getItems(params = {}) {
        return client.get("/accident-reports", { params });
    },

    getItem(id) {
        return client.get(`/accident-reports/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/accident-reports/${id}/edit`);
    },

    createItem(data) {
        return client.post("/accident-reports", data);
    },

    updateItem(id, data) {
        return client.put(`/accident-reports/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/accident-reports/${id}`);
    },
};

export default accidentService;
