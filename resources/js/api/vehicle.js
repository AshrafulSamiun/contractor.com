import client from "./client";

export const vehicleService = {
    getItems(params = {}) {
        return client.get("/vehicles", { params });
    },

    getItem(id) {
        return client.get(`/vehicles/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/vehicles/${id}/edit`);
    },

    createItem(data) {
        return client.post("/vehicles", data);
    },

    updateItem(id, data) {
        return client.put(`/vehicles/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/vehicles/${id}`);
    },
};

export default vehicleService;
