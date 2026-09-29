import client from "./client";

export const driverService = {
    getItems(params = {}) {
        return client.get("/drivers", { params });
    },

    getItem(id) {
        return client.get(`/drivers/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/drivers/${id}/edit`);
    },

    createItem(data) {
        return client.post("/drivers", data);
    },

    updateItem(id, data) {
        return client.put(`/drivers/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/drivers/${id}`);
    },
};

export default driverService;
