import client from "./client";

export const dailyLogService = {
    getItems(params = {}) {
        return client.get("/daily-logs", { params });
    },

    getItem(id) {
        return client.get(`/daily-logs/${id}`);
    },

    getItemForEdit(id) {
        return client.get(`/daily-logs/${id}/edit`);
    },

    createItem(data) {
        return client.post("/daily-logs", data);
    },

    updateItem(id, data) {
        return client.put(`/daily-logs/${id}`, data);
    },

    deleteItem(id) {
        return client.delete(`/daily-logs/${id}`);
    },
};

export default dailyLogService;
