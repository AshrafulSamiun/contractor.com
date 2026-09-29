import client from "./client";

export const dailyReportsService = {
    getItems(params = {}) {
        return client.get("/workforce/daily-reports", { params });
    },

    getItem(id) {
        return client.get(`/workforce/daily-reports/${id}`);
    },

    createItem(data) {
        return client.post("/workforce/daily-reports", data);
    },

    updateItem(id, data) {
        return client.put(`/workforce/daily-reports/${id}`, data);
    },
};

export default dailyReportsService;
