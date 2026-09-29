<template>
    <div class="pm-dashboard-layout">
        <AppSidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />
        <main class="pm-dashboard-main">
            <header class="pm-dashboard-topbar">
                <button
                    class="pm-icon-btn pm-menu-btn"
                    type="button"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    ☰
                </button>
                <div class="pm-topbar-search">
                    <input
                        v-model="filters.search"
                        class="form-control"
                        placeholder="Search..."
                        @keyup.enter="load"
                    />
                </div>
            </header>
            <section class="pm-dashboard-content">
                <div class="container pm-ops-page daily-page">
                    <div class="daily-title-row">
                        <h1>
                            Daily Report
                            <small
                                >-
                                {{
                                    mode === "list" ? "Report List" : ""
                                }}</small
                            >
                        </h1>
                        <button
                            v-if="mode !== 'list'"
                            class="btn btn-outline-secondary daily-list-button"
                            type="button"
                            @click="mode = 'list'"
                        >
                            List
                        </button>
                    </div>
                    <template v-if="mode === 'list'"
                        ><section class="pm-card filter-card">
                            <div class="filter-head">
                                <h2>▽&nbsp; Filter</h2>
                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    @click="exportCsv"
                                >
                                    ⇩ Export
                                </button>
                            </div>
                            <div class="filter-grid">
                                <label
                                    >Report Number<input
                                        v-model="filters.report_no"
                                        class="form-control"
                                        placeholder="Select or enter report no." /></label
                                ><label
                                    >Date From<input
                                        v-model="filters.date_from"
                                        class="form-control"
                                        type="date" /></label
                                ><label
                                    >Date To<input
                                        v-model="filters.date_to"
                                        class="form-control"
                                        type="date" /></label
                                ><label
                                    >Staff<input
                                        v-model="filters.staff"
                                        class="form-control"
                                        placeholder="All Staff" /></label
                                ><label
                                    >Job Order Number<input
                                        v-model="filters.job_order"
                                        class="form-control"
                                        placeholder="Select or enter job order no." /></label
                                ><label
                                    >Customer Name / Address<input
                                        v-model="filters.customer"
                                        class="form-control"
                                        placeholder="Select or enter customer name / address"
                                /></label>
                                <div class="filter-actions">
                                    <button
                                        class="btn btn-primary"
                                        type="button"
                                        @click="search"
                                    >
                                        ⌕&nbsp; Search</button
                                    ><button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                        @click="clear"
                                    >
                                        ↻&nbsp; Clear
                                    </button>
                                </div>
                            </div>
                        </section>
                        <section class="pm-card list-card">
                            <div class="list-head">
                                <h2>
                                    ▣&nbsp; Daily Report List ({{ meta.total }})
                                </h2>
                                <div>
                                    <label class="entries"
                                        >Show
                                        <select
                                            v-model.number="filters.per_page"
                                            class="form-select"
                                            @change="search"
                                        >
                                            <option :value="10">10</option>
                                            <option :value="25">25</option>
                                            <option :value="50">50</option>
                                        </select>
                                        entries</label
                                    ><button
                                        class="btn btn-primary new"
                                        type="button"
                                        @click="create"
                                    >
                                        ＋ New Report
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table daily-table">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>
                                                <button
                                                    @click="sort('report_no')"
                                                >
                                                    Report Number
                                                    {{ marker("report_no") }}
                                                </button>
                                            </th>
                                            <th>
                                                <button
                                                    @click="sort('report_date')"
                                                >
                                                    Date
                                                    {{ marker("report_date") }}
                                                </button>
                                            </th>
                                            <th>
                                                <button
                                                    @click="
                                                        sort('employee_name')
                                                    "
                                                >
                                                    Staff
                                                    {{
                                                        marker("employee_name")
                                                    }}
                                                </button>
                                            </th>
                                            <th>Job Order Number</th>
                                            <th>Customer Name / Address</th>
                                            <th>View Report</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(report, index) in reports"
                                            :key="report.id"
                                        >
                                            <td>
                                                {{ (meta.from || 1) + index }}
                                            </td>
                                            <td>{{ report.report_no }}</td>
                                            <td>
                                                {{
                                                    displayDate(
                                                        report.report_date,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{ report.employee_name || "—"
                                                }}<small
                                                    v-if="report.employee_code"
                                                    >({{
                                                        report.employee_code
                                                    }})</small
                                                >
                                            </td>
                                            <td>{{ jobOrders(report) }}</td>
                                            <td>{{ customers(report) }}</td>
                                            <td>
                                                <button
                                                    class="view-btn"
                                                    type="button"
                                                    @click="edit(report.id)"
                                                >
                                                    ◉&nbsp; View Report
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="!reports.length">
                                            <td colspan="7" class="empty">
                                                No daily reports found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="daily-pagination-bar">
                                <span class="pagination-summary"
                                    >Showing {{ meta.from || 0 }} to
                                    {{ meta.to || 0 }} of
                                    {{ meta.total }} entries</span
                                >
                                <div class="pagination">
                                    <button
                                        :disabled="meta.current_page <= 1"
                                        @click="go(1)"
                                    >
                                        First</button
                                    ><button
                                        :disabled="meta.current_page <= 1"
                                        @click="go(meta.current_page - 1)"
                                    >
                                        ‹</button
                                    ><button class="active">
                                        {{ meta.current_page }}</button
                                    ><button
                                        :disabled="
                                            meta.current_page >= meta.last_page
                                        "
                                        @click="go(meta.current_page + 1)"
                                    >
                                        ›</button
                                    ><button
                                        :disabled="
                                            meta.current_page >= meta.last_page
                                        "
                                        @click="go(meta.last_page)"
                                    >
                                        Last
                                    </button>
                                </div>
                            </div>
                        </section></template
                    >
                    <form v-else @submit.prevent="save">
                        <section class="pm-card">
                            <h2><b>1</b> Report Info.</h2>
                            <div class="form-grid report-info">
                                <label
                                    >Report Number<input
                                        :value="
                                            form.report_no ||
                                            'Generated when saved'
                                        "
                                        class="form-control"
                                        disabled /></label
                                ><label
                                    >Date *<input
                                        v-model="form.report_date"
                                        type="date"
                                        class="form-control"
                                        required /></label
                                ><label
                                    >Department<input
                                        v-model="form.department"
                                        class="form-control"
                                        placeholder="Maintenance"
                                /></label>
                            </div>
                        </section>
                        <section class="pm-card">
                            <h2><b>2</b> Staff Info.</h2>
                            <div class="form-grid">
                                <label
                                    >Staff Number<input
                                        v-model="form.employee_code"
                                        class="form-control" /></label
                                ><label
                                    >Staff Name *<input
                                        v-model="form.employee_name"
                                        class="form-control"
                                        required /></label
                                ><label
                                    >Phone<input
                                        v-model="form.employee_phone"
                                        class="form-control" /></label
                                ><label
                                    >Email<input
                                        v-model="form.employee_email"
                                        type="email"
                                        class="form-control"
                                /></label>
                            </div>
                        </section>
                        <section class="pm-card details-card">
                            <div class="detail-head">
                                <h2><b>3</b> Daily Report Details</h2>
                                <button
                                    class="btn btn-outline-primary"
                                    type="button"
                                    @click="addLine"
                                >
                                    ＋ Add New
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table details-table">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>From Date</th>
                                            <th>From Time</th>
                                            <th>To Date</th>
                                            <th>To Time</th>
                                            <th>Net Time</th>
                                            <th>Description / Job Activity</th>
                                            <th>Job Order Number</th>
                                            <th>Customer Name</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                line, index
                                            ) in form.details_json"
                                            :key="index"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <input
                                                    v-model="line.date"
                                                    type="date"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="line.time_from"
                                                    type="time"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="line.to_date"
                                                    type="date"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="line.time_to"
                                                    type="time"
                                                />
                                            </td>
                                            <td>{{ netTime(line) }}</td>
                                            <td>
                                                <input
                                                    v-model="line.description"
                                                    placeholder="Work performed"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="line.job_order"
                                                    placeholder="JO-2024-0156"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="line.customer"
                                                    placeholder="Customer name"
                                                />
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="delete-line"
                                                    @click="removeLine(index)"
                                                >
                                                    ♜
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td>
                                                <strong>{{ totalNet }}</strong>
                                            </td>
                                            <td colspan="4">TOTAL NET TIME</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </section>
                        <section class="pm-card">
                            <h2><b>4</b> Summary</h2>
                            <div class="summary-table">
                                <div>
                                    <span>Customer Name</span
                                    ><span>Job Order Number(s)</span
                                    ><span>Net Spent Time</span>
                                </div>
                                <div
                                    v-for="row in summaryRows"
                                    :key="row.customer"
                                >
                                    <span>{{ row.customer }}</span
                                    ><span>{{ row.orders }}</span
                                    ><span>{{ row.time }}</span>
                                </div>
                                <div class="total">
                                    <span>TOTAL</span><span></span
                                    ><span>{{ totalNet }}</span>
                                </div>
                            </div>
                        </section>
                        <div class="form-actions">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                @click="mode = 'list'"
                            >
                                Cancel</button
                            ><button
                                type="button"
                                class="btn btn-outline-secondary"
                                @click="reset"
                            >
                                Reset</button
                            ><button class="btn btn-primary" :disabled="saving">
                                {{ saving ? "Saving…" : "Save" }}</button
                            ><button
                                v-if="form.id"
                                type="button"
                                class="btn btn-outline-danger"
                                @click="remove"
                            >
                                Delete</button
                            ><button
                                type="button"
                                class="btn btn-primary"
                                :disabled="saving"
                                @click="saveAndOut"
                            >
                                Save & Out
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import AppSidebar from "../components/AppSidebar.vue";
import client from "../api/client";
const sidebarOpen = ref(false),
    mode = ref("list"),
    reports = ref([]),
    saving = ref(false),
    meta = reactive({
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0,
    }),
    today = new Date().toISOString().slice(0, 10);
const filters = reactive({
    report_no: "",
    date_from: "",
    date_to: "",
    staff: "",
    job_order: "",
    customer: "",
    search: "",
    sort_by: "report_date",
    sort_direction: "desc",
    per_page: 10,
    page: 1,
});
const blank = () => ({
        id: null,
        report_no: "",
        report_date: today,
        department: "",
        employee_code: "",
        employee_name: "",
        employee_phone: "",
        employee_email: "",
        status: "draft",
        details_json: [line()],
        metrics_json: {},
    }),
    line = () => ({
        date: today,
        to_date: today,
        time_from: "08:00",
        time_to: "17:00",
        description: "",
        job_order: "",
        customer: "",
    }),
    form = reactive(blank());
const load = async () => {
        const { data } = await client.get("/workforce/daily-reports", {
            params: filters,
        });
        reports.value = data.data || [];
        Object.assign(meta, data.meta || {});
    },
    search = () => {
        filters.page = 1;
        load();
    },
    clear = () => {
        Object.assign(filters, {
            report_no: "",
            date_from: "",
            date_to: "",
            staff: "",
            job_order: "",
            customer: "",
            search: "",
            sort_by: "report_date",
            sort_direction: "desc",
            per_page: 10,
            page: 1,
        });
        load();
    },
    go = (page) => {
        filters.page = page;
        load();
    },
    sort = (column) => {
        filters.sort_direction =
            filters.sort_by === column && filters.sort_direction === "asc"
                ? "desc"
                : "asc";
        filters.sort_by = column;
        search();
    },
    marker = (column) =>
        filters.sort_by === column
            ? filters.sort_direction === "asc"
                ? "↑"
                : "↓"
            : "";
const create = () => {
        Object.assign(form, blank());
        mode.value = "form";
    },
    edit = async (id) => {
        const { data } = await client.get(`/workforce/daily-reports/${id}`),
            item = data.data;
        Object.assign(form, item, {
            department: item.department || "",
            details_json: (item.details_json || []).map((row) => ({
                ...line(),
                ...row,
                to_date: row.to_date || row.date,
            })),
        });
        if (!form.details_json.length) form.details_json = [line()];
        mode.value = "form";
    },
    reset = () => Object.assign(form, blank()),
    addLine = () => form.details_json.push(line()),
    removeLine = (index) => form.details_json.splice(index, 1);
const minutes = (value) => {
        if (!value) return 0;
        const [h, m] = value.split(":").map(Number);
        return h * 60 + m;
    },
    duration = (row) =>
        Math.max(0, minutes(row.time_to) - minutes(row.time_from)),
    formatMinutes = (value) =>
        `${Math.floor(value / 60)}h ${String(value % 60).padStart(2, "0")}m`,
    netTime = (row) => formatMinutes(duration(row)),
    totalMinutes = computed(() =>
        form.details_json.reduce((sum, row) => sum + duration(row), 0),
    ),
    totalNet = computed(() => formatMinutes(totalMinutes.value)),
    summaryRows = computed(() => {
        const items = {};
        form.details_json.forEach((row) => {
            const key = row.customer || "Unassigned";
            items[key] ??= { customer: key, orders: [], minutes: 0 };
            if (row.job_order) items[key].orders.push(row.job_order);
            items[key].minutes += duration(row);
        });
        return Object.values(items).map((row) => ({
            ...row,
            orders: [...new Set(row.orders)].join(", "),
            time: formatMinutes(row.minutes),
        }));
    });
const payload = () => ({
    ...form,
    details_json: {
        department: form.department,
        entries: form.details_json.map((row) => ({
            ...row,
            day: new Date(`${row.date}T00:00:00`).toLocaleDateString(
                undefined,
                { weekday: "long" },
            ),
            net_hours: duration(row) / 60,
            job_site: row.description,
        })),
    },
    metrics_json: {
        regular_hours: totalMinutes.value / 60,
        total_hours: totalMinutes.value / 60,
        total_jobs: form.details_json.length,
    },
});
const save = async () => {
        saving.value = true;
        try {
            const data = payload();
            form.id
                ? await client.put(`/workforce/daily-reports/${form.id}`, data)
                : await client.post("/workforce/daily-reports", data);
            await load();
            mode.value = "list";
        } finally {
            saving.value = false;
        }
    },
    saveAndOut = save,
    remove = async () => {
        if (!confirm("Delete this daily report?")) return;
        await client.delete(`/workforce/daily-reports/${form.id}`);
        await load();
        mode.value = "list";
    };
const entries = (report) =>
        Array.isArray(report.details_json)
            ? report.details_json
            : report.details_json?.entries || [],
    jobOrders = (report) =>
        entries(report)
            .map((row) => row.job_order)
            .filter(Boolean)
            .join(", ") || "—",
    customers = (report) =>
        [
            ...new Set(
                entries(report)
                    .map((row) => row.customer)
                    .filter(Boolean),
            ),
        ].join(", ") || "—",
    displayDate = (value) =>
        value ? new Date(`${value}T00:00:00`).toLocaleDateString() : "—";
const exportCsv = () => {
    const rows = [
            ["Report Number", "Date", "Staff", "Job Order Number", "Customer"],
            ...reports.value.map((report) => [
                report.report_no,
                report.report_date,
                report.employee_name,
                jobOrders(report),
                customers(report),
            ]),
        ],
        blob = new Blob(
            [
                rows
                    .map((row) =>
                        row
                            .map(
                                (cell) =>
                                    `"${String(cell || "").replaceAll('"', '""')}"`,
                            )
                            .join(","),
                    )
                    .join("\n"),
            ],
            { type: "text/csv" },
        ),
        a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "daily-reports.csv";
    a.click();
    URL.revokeObjectURL(a.href);
};
onMounted(load);
</script>

<style scoped>
.daily-page {
    max-width: 1500px;
    padding-bottom: 2rem;
}
.daily-page h1 {
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 1rem;
}
.daily-page h1 small {
    font-size: 1.25rem;
}
.pm-card {
    border: 1px solid #dbe2ee;
    box-shadow: none;
    padding: 1rem 1.3rem;
    margin-bottom: 0.8rem;
}
.filter-head,
.list-head,
.detail-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.daily-page h2 {
    font-size: 1rem;
    font-weight: 800;
    color: #061e6b;
    margin: 0;
}
.filter-grid,
.form-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(150px, 1fr));
    gap: 0.85rem 1.45rem;
}
.filter-grid label,
.form-grid label {
    display: grid;
    gap: 0.35rem;
    font-size: 0.81rem;
    font-weight: 700;
}
.filter-grid label:nth-child(5) {
    grid-column: span 1;
}
.filter-grid label:nth-child(6) {
    grid-column: span 2;
}
.filter-actions {
    display: flex;
    align-items: end;
    gap: 0.65rem;
}
.entries {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
}
.entries select {
    width: 70px;
}
.new {
    margin-left: 0.8rem;
}
.daily-table,
.details-table {
    font-size: 0.82rem;
    margin: 0;
}
.daily-table th,
.details-table th {
    background: #061e59;
    color: white;
    text-align: center;
    white-space: nowrap;
    border-color: #486087;
}
.daily-table th button {
    border: 0;
    background: none;
    color: inherit;
    font-weight: 750;
}
.daily-table td,
.details-table td {
    vertical-align: middle;
}
.daily-table small {
    margin-left: 0.2rem;
    color: #667085;
}
.view-btn {
    border: 1px solid #bfd0e6;
    background: #fff;
    color: #062a80;
    border-radius: 4px;
    padding: 0.3rem 0.6rem;
    font-weight: 700;
}
.empty {
    text-align: center;
    padding: 2rem !important;
    color: #667085;
}
.daily-pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
    font-size: 0.82rem;
}
.pagination {
    display: flex;
    gap: 0.4rem;
}
.pagination button {
    border: 1px solid #d6deea;
    background: #fff;
    border-radius: 4px;
    padding: 0.35rem 0.65rem;
}
.pagination button.active {
    background: #061e59;
    color: white;
}
.pagination button:disabled {
    opacity: 0.45;
}
.daily-page h2 b {
    display: inline-grid;
    place-items: center;
    width: 27px;
    height: 27px;
    border-radius: 4px;
    background: #061e59;
    color: white;
}
.report-info {
    grid-template-columns: repeat(3, minmax(180px, 1fr));
}
.details-table input {
    width: 100%;
    min-width: 90px;
    border: 1px solid #d7dee9;
    border-radius: 3px;
    padding: 0.32rem;
    background: #fff;
    font-size: 0.77rem;
}
.details-table td:nth-child(7) input {
    min-width: 190px;
}
.delete-line {
    border: 0;
    background: none;
    color: #b42318;
    font-size: 1rem;
}
.details-table tfoot {
    font-weight: 800;
    color: #061e6b;
    background: #eff5ff;
}
.summary-table {
    border: 1px solid #dbe2ee;
}
.summary-table > div {
    display: grid;
    grid-template-columns: 1fr 1.6fr 0.8fr;
    border-bottom: 1px solid #dbe2ee;
}
.summary-table span {
    padding: 0.55rem 0.75rem;
    border-right: 1px solid #dbe2ee;
    font-size: 0.82rem;
}
.summary-table > div:first-child {
    font-weight: 800;
    text-align: center;
}
.summary-table .total {
    font-weight: 800;
    color: #061e6b;
    background: #eff5ff;
}
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.btn {
    font-weight: 700;
    min-height: 38px;
}
@media (max-width: 900px) {
    .filter-grid,
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .filter-grid label:nth-child(6) {
        grid-column: auto;
    }
}
@media (max-width: 600px) {
    .daily-page h1 {
        font-size: 1.55rem;
    }
    .filter-grid,
    .form-grid {
        grid-template-columns: 1fr;
    }
    .filter-head,
    .list-head,
    .detail-head,
    .daily-pagination-bar {
        align-items: flex-start;
        gap: 0.65rem;
        flex-direction: column;
    }
    .new {
        margin-left: 0;
    }
    .form-actions {
        flex-wrap: wrap;
    }
    .summary-table > div {
        grid-template-columns: 1fr;
    }
}
.list-card .pagination button.active {
    background: #fff;
    color: #061e59;
    border-color: #061e59;
}
.daily-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.daily-title-row h1 {
    margin-bottom: 1rem;
}
.daily-list-button {
    margin-bottom: 1rem;
}
</style>
