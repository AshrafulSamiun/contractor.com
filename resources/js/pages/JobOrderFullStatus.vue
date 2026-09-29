<template>
    <div
        class="pm-dashboard-layout"
        :class="{ 'pm-sidebar-hidden': sidebarHidden }"
    >
        <AppSidebar :isOpen="sidebarOpen" @close="closeSidebar" />
        <div class="pm-dashboard-main">
            <div class="pm-dashboard-topbar">
                <button
                    class="pm-icon-btn pm-menu-btn"
                    type="button"
                    aria-label="Open menu"
                    @click="toggleSidebar"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
                <button
                    class="pm-icon-btn pm-hide-btn"
                    type="button"
                    aria-label="Toggle sidebar"
                    @click="toggleSidebarHidden"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 6 3 12l6 6M21 12H4" />
                    </svg>
                </button>
                <div class="pm-topbar-search">
                    <input
                        v-model="globalSearch"
                        class="form-control"
                        placeholder="Search..."
                    />
                    <button
                        v-if="globalSearch"
                        class="pm-clear-btn"
                        type="button"
                        aria-label="Clear search"
                        @click="globalSearch = ''"
                    >
                        &times;
                    </button>
                </div>
                <div class="pm-topbar-actions">
                    <button class="pm-icon-btn" type="button">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z"
                            />
                        </svg>
                        <span class="pm-topbar-badge"></span>
                    </button>
                </div>
                <div
                    class="pm-topbar-user"
                    @click="toggleUserMenu"
                    ref="userMenuRef"
                >
                    <div class="pm-topbar-avatar">{{ userInitials }}</div>
                    <span class="pm-topbar-name">{{ userName }}</span>
                    <span class="pm-topbar-pill">Admin</span>
                    <button
                        class="pm-icon-btn pm-chevron-btn"
                        type="button"
                        aria-label="User menu"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m7 10 5 5 5-5H7Z" />
                        </svg>
                    </button>
                    <div v-if="userMenuOpen" class="pm-user-menu">
                        <button class="pm-user-item" type="button">
                            Profile
                        </button>
                        <button class="pm-user-item" type="button">
                            Account
                        </button>
                        <button
                            class="pm-user-item danger"
                            type="button"
                            @click="logout"
                        >
                            Log out
                        </button>
                    </div>
                </div>
            </div>

            <section class="pm-dashboard-content">
                <div class="container pm-job-full-status-page">
                    <section v-if="reportView" class="jor-report">
                        <h1>7. Job Orders List (Report)</h1>
                        <section class="jor-filter"><label>Select Customer / Company<select v-model="selectedCustomer" class="form-control"><option value="">All Customers</option><option v-for="customer in customerOptions" :key="customer" :value="customer">{{ customer }}</option></select></label><label>Status<select v-model="reportStatus" class="form-control"><option value="">All Status</option><option v-for="status in reportStatuses" :key="status">{{ status }}</option></select></label><label>Date From<input v-model="reportDateFrom" type="date" class="form-control"/></label><label>Date To<input v-model="reportDateTo" type="date" class="form-control"/></label><button @click="applyReport">Search</button><button class="clear" @click="clearReport">Clear</button></section>
                        <section class="jor-metrics"><article><span>Total Job Orders</span><b>{{ reportRows.length }}</b></article><article><span>Total Estimation Amount</span><b>{{ currency(reportEstimateTotal) }}</b></article><article><span>Total Invoiced Amount</span><b>{{ currency(reportInvoiceTotal) }}</b></article><article><span>Total Receipts Amount</span><b>{{ currency(reportReceiptTotal) }}</b></article><article><span>Total Balance</span><b>{{ currency(reportBalanceTotal) }}</b></article></section>
                        <section class="jor-table-wrap"><div class="table-responsive"><table class="jor-table"><thead><tr><th rowspan="2">No.</th><th rowspan="2">Job Order No.</th><th rowspan="2">Customer / Company</th><th colspan="2">Job Order</th><th colspan="3">Estimation / Quote</th><th colspan="3">Sales Invoice</th><th colspan="3">Receipt</th><th rowspan="2">Balance<br/>(This Job Order)</th></tr><tr><th>Date</th><th>Time</th><th>Est. / Quote No.</th><th>Est. / Quote Date</th><th>Amount</th><th>Inv. No.</th><th>Inv. Date</th><th>Amount</th><th>Receipt No.</th><th>Receipt Date</th><th>Amount</th></tr></thead><tbody><tr v-for="(row,index) in reportRows" :key="row.id"><td>{{ index+1 }}</td><td class="code">{{ row.jobNo }}</td><td>{{ row.customer }}</td><td>{{ reportDate(row.date) }}</td><td>{{ reportTime(row.date) }}</td><td class="code">{{ row.estimateNo }}</td><td>{{ reportDate(row.estimateDate) }}</td><td>{{ currency(row.estimateAmount) }}</td><td class="code">{{ row.invoiceNo }}</td><td>{{ reportDate(row.invoiceDate) }}</td><td>{{ currency(row.invoiceAmount) }}</td><td class="code">{{ row.receiptNo }}</td><td>{{ reportDate(row.receiptDate) }}</td><td>{{ currency(row.receiptAmount) }}</td><td>{{ currency(row.balance) }}</td></tr><tr v-if="!reportRows.length"><td colspan="15" class="empty">No job orders match the selected report filters.</td></tr></tbody></table></div><div class="jor-table-footer"><b>Showing 1 to {{ reportRows.length }} of {{ reportRows.length }} entries</b><div><button class="active">1</button><button>2</button><button>3</button><button>&gt;</button></div></div></section>
                        <footer class="jor-actions"><button>▧ Export to Excel</button><button @click="printReport">▧ Export to PDF</button><button @click="printReport">▣ Print</button><button @click="applyReport">⟳ Refresh</button></footer>
                    </section>
                    <template v-else>
                    <section class="pm-job-full-status-hero">
                        <h2>Job Orders Full Status</h2>
                        <p>
                            Complete pipeline tracking from Estimation to
                            Payment
                        </p>
                    </section>

                    <section class="pm-job-full-status-metrics">
                        <article class="pm-job-full-status-metric is-blue">
                            <div class="pm-job-full-status-metric-icon">
                                &#128196;
                            </div>
                            <div>
                                <div class="pm-job-full-status-metric-label">
                                    Total Estimations
                                </div>
                                <div class="pm-job-full-status-metric-value">
                                    {{ estimations.length }}
                                </div>
                            </div>
                        </article>
                        <article class="pm-job-full-status-metric is-purple">
                            <div class="pm-job-full-status-metric-icon">
                                &#128196;
                            </div>
                            <div>
                                <div class="pm-job-full-status-metric-label">
                                    Total Quotations
                                </div>
                                <div class="pm-job-full-status-metric-value">
                                    {{ quotations.length }}
                                </div>
                            </div>
                        </article>
                        <article class="pm-job-full-status-metric is-green">
                            <div class="pm-job-full-status-metric-icon">
                                &#10003;
                            </div>
                            <div>
                                <div class="pm-job-full-status-metric-label">
                                    Active Jobs
                                </div>
                                <div class="pm-job-full-status-metric-value">
                                    {{ activeJobsCount }}
                                </div>
                            </div>
                        </article>
                        <article class="pm-job-full-status-metric is-orange">
                            <div class="pm-job-full-status-metric-icon">
                                &#9716;
                            </div>
                            <div>
                                <div class="pm-job-full-status-metric-label">
                                    Pending Invoices
                                </div>
                                <div class="pm-job-full-status-metric-value">
                                    {{ pendingInvoicesCount }}
                                </div>
                            </div>
                        </article>
                        <article class="pm-job-full-status-metric is-teal">
                            <div class="pm-job-full-status-metric-icon">
                                $
                            </div>
                            <div>
                                <div class="pm-job-full-status-metric-label">
                                    Total Revenue
                                </div>
                                <div class="pm-job-full-status-metric-value">
                                    ${{ compactMoney(totalRevenue) }}
                                </div>
                            </div>
                        </article>
                    </section>

                    <section class="pm-job-full-status-card">
                        <div class="pm-job-full-status-card-head">
                            Search & Filter
                        </div>
                        <div class="pm-job-full-status-card-body">
                            <div class="pm-job-full-status-filter-grid">
                                <div>
                                    <label class="pm-field-label"
                                        >Customer</label
                                    >
                                    <select
                                        v-model="selectedCustomer"
                                        class="form-control"
                                    >
                                        <option value="">All Customers</option>
                                        <option
                                            v-for="customer in customerOptions"
                                            :key="customer"
                                            :value="customer"
                                        >
                                            {{ customer }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="pm-field-label"
                                        >Job Site</label
                                    >
                                    <input
                                        v-model.trim="jobSiteSearch"
                                        class="form-control"
                                        placeholder="Search job site..."
                                        type="search"
                                    />
                                </div>
                                <div>
                                    <label class="pm-field-label"
                                        >Estimation</label
                                    >
                                    <input
                                        v-model.trim="estimationSearch"
                                        class="form-control"
                                        placeholder="EST-2024-XXX"
                                        type="search"
                                    />
                                </div>
                                <div>
                                    <label class="pm-field-label"
                                        >Quotation</label
                                    >
                                    <input
                                        v-model.trim="quotationSearch"
                                        class="form-control"
                                        placeholder="QT-2024-XXX"
                                        type="search"
                                    />
                                </div>
                                <div>
                                    <label class="pm-field-label"
                                        >Job Order</label
                                    >
                                    <input
                                        v-model.trim="jobOrderSearch"
                                        class="form-control"
                                        placeholder="JO-2026-XXX"
                                        type="search"
                                    />
                                </div>
                                <div class="pm-job-full-status-filter-actions">
                                    <button
                                        class="pm-job-full-status-search-btn"
                                        type="button"
                                    >
                                        Search
                                    </button>
                                    <button
                                        class="pm-job-full-status-reset-btn"
                                        type="button"
                                        @click="resetFilters"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-full-status-card">
                        <div class="pm-job-full-status-card-head">
                            Job Orders Full Status
                        </div>
                        <div class="pm-job-full-status-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-full-status-table">
                                    <thead>
                                        <tr>
                                            <th>Cust No</th>
                                            <th>Cust Name</th>
                                            <th>Ph. No</th>
                                            <th>Trans Code</th>
                                            <th>Doc Type</th>
                                            <th>Doc Date</th>
                                            <th>Amt ($)</th>
                                            <th>Status</th>
                                            <th>Converted</th>
                                            <th>Job Order No</th>
                                            <th>Job Site</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Job Amt ($)</th>
                                            <th>Job Status</th>
                                            <th>Progress</th>
                                            <th>Sales Invoice No</th>
                                            <th>Invoice Date</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredPipelineRows.length">
                                        <tr
                                            v-for="row in filteredPipelineRows"
                                            :key="row.key"
                                        >
                                            <td>{{ row.customerNo }}</td>
                                            <td>{{ row.customerName }}</td>
                                            <td>{{ row.phone }}</td>
                                            <td class="is-link-code">
                                                {{ row.transCode }}
                                            </td>
                                            <td
                                                :class="
                                                    row.docType ===
                                                    'Estimation'
                                                        ? 'is-estimation-doc'
                                                        : 'is-quotation-doc'
                                                "
                                            >
                                                {{ row.docType }}
                                            </td>
                                            <td>{{ formatDate(row.docDate) }}</td>
                                            <td>{{ formatMoney(row.docAmount) }}</td>
                                            <td>
                                                <span
                                                    class="pm-job-full-status-badge"
                                                    :class="
                                                        documentStatusClass(
                                                            row.status,
                                                        )
                                                    "
                                                >
                                                    {{ row.status }}
                                                </span>
                                            </td>
                                            <td
                                                :class="
                                                    row.converted
                                                        ? 'is-converted'
                                                        : 'is-not-converted'
                                                "
                                            >
                                                {{ row.converted ? "Y" : "N" }}
                                            </td>
                                            <td class="is-link-code">
                                                {{ row.jobOrderNo }}
                                            </td>
                                            <td>{{ row.jobSite }}</td>
                                            <td>{{ formatDate(row.startDate) }}</td>
                                            <td>{{ formatDate(row.endDate) }}</td>
                                            <td>{{ formatMoney(row.jobAmount) }}</td>
                                            <td>
                                                <span
                                                    class="pm-job-full-status-badge"
                                                    :class="
                                                        jobStatusClass(
                                                            row.jobStatus,
                                                        )
                                                    "
                                                >
                                                    {{ row.jobStatus }}
                                                </span>
                                            </td>
                                            <td>
                                                <div
                                                    class="pm-job-full-status-progress"
                                                >
                                                    <span
                                                        >{{ row.progress }}%</span
                                                    >
                                                    <div
                                                        class="pm-job-full-status-progress-bar"
                                                    >
                                                        <span
                                                            :class="
                                                                progressBarClass(
                                                                    row.progress,
                                                                )
                                                            "
                                                            :style="{
                                                                width: `${row.progress}%`,
                                                            }"
                                                        ></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ row.invoiceNo }}</td>
                                            <td>{{ formatDate(row.invoiceDate) }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="18" class="text-center">
                                                No pipeline records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-full-status-summary-grid">
                        <div class="pm-job-full-status-summary-card">
                            <div
                                class="pm-job-full-status-summary-head is-green"
                            >
                                Summary Table
                            </div>
                            <div class="pm-job-full-status-summary-body">
                                <div class="pm-job-full-status-summary-row">
                                    <span>Total Estimations</span>
                                    <strong>{{ estimations.length }}</strong>
                                    <strong>${{ formatMoney(totalEstimationAmount) }}</strong>
                                </div>
                                <div class="pm-job-full-status-summary-row">
                                    <span>Total Quotations</span>
                                    <strong>{{ quotations.length }}</strong>
                                    <strong>${{ formatMoney(totalQuotationAmount) }}</strong>
                                </div>
                                <div
                                    class="pm-job-full-status-summary-row is-highlight-green"
                                >
                                    <span>Total Job Orders</span>
                                    <strong>{{ jobOrders.length }}</strong>
                                    <strong>${{ formatMoney(totalJobOrderAmount) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="pm-job-full-status-summary-card">
                            <div
                                class="pm-job-full-status-summary-head is-purple"
                            >
                                Job Status Summary
                            </div>
                            <div class="pm-job-full-status-summary-body">
                                <div class="pm-job-full-status-summary-row">
                                    <span>Scheduled</span>
                                    <strong>{{ scheduledCount }}</strong>
                                    <strong>${{ formatMoney(scheduledAmount) }}</strong>
                                </div>
                                <div class="pm-job-full-status-summary-row">
                                    <span>In Progress</span>
                                    <strong>{{ inProgressCount }}</strong>
                                    <strong>${{ formatMoney(inProgressAmount) }}</strong>
                                </div>
                                <div
                                    class="pm-job-full-status-summary-row is-highlight-green"
                                >
                                    <span>Completed</span>
                                    <strong>{{ completedCount }}</strong>
                                    <strong>${{ formatMoney(completedAmount) }}</strong>
                                </div>
                            </div>
                        </div>
                    </section>
                    </template>
                </div>
            </section>
        </div>
        <div
            v-if="sidebarOpen"
            class="pm-sidebar-overlay"
            @click="toggleSidebar"
        ></div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
import client from "../api/client";
import { logout as logoutRequest, clearToken } from "../api/auth";
import { authState } from "../store/auth";
import {
    sidebarOpen,
    sidebarHidden,
    toggleSidebar,
    closeSidebar,
    toggleSidebarHidden,
} from "../store/sidebar";
import { setFlash } from "../store/flash";

const router = useRouter();

const userName = ref("John Doe");
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const globalSearch = ref("");
const selectedCustomer = ref("");
const jobSiteSearch = ref("");
const estimationSearch = ref("");
const quotationSearch = ref("");
const jobOrderSearch = ref("");
const reportView = ref(true);
const reportStatus = ref("");
const reportDateFrom = ref("");
const reportDateTo = ref("");

const estimations = ref([]);
const quotations = ref([]);
const jobOrders = ref([]);
const reportStatuses = computed(() => Array.from(new Set(jobOrders.value.map((item) => item.status_label).filter(Boolean))));
const reportRows = computed(() => jobOrders.value.filter((job) => {
    const customer = job.customer?.company_name || job.customer?.name || "--";
    const afterFrom = !reportDateFrom.value || !job.issue_date || String(job.issue_date).slice(0, 10) >= reportDateFrom.value;
    const beforeTo = !reportDateTo.value || !job.issue_date || String(job.issue_date).slice(0, 10) <= reportDateTo.value;
    return (!selectedCustomer.value || customer === selectedCustomer.value) && (!reportStatus.value || job.status_label === reportStatus.value) && afterFrom && beforeTo;
}).map((job, index) => {
    const estimate = estimations.value.find((item) => item.id === job.estimation_id) || job.estimation || null;
    const total = toNumber(job.total);
    const paid = toNumber(job.amount_paid);
    const hasInvoice = Boolean(job.converted_to_invoice || job.invoice_reference);
    return { id: job.id || index, jobNo: job.job_order_no || "-", customer: job.customer?.company_name || job.customer?.name || "--", date: job.issue_date, estimateNo: estimate?.estimation_no || job.quotation?.quotation_no || "-", estimateDate: estimate?.issue_date || job.issue_date, estimateAmount: toNumber(estimate?.total || total), invoiceNo: hasInvoice ? (job.invoice_reference || "INV-" + String(index + 1).padStart(4, "0")) : "-", invoiceDate: hasInvoice ? job.issue_date : null, invoiceAmount: hasInvoice ? total : 0, receiptNo: paid > 0 ? "RCPT-" + String(index + 1).padStart(4, "0") : "-", receiptDate: paid > 0 ? job.issue_date : null, receiptAmount: paid, balance: Math.max(0, total - paid) };
}));
const reportEstimateTotal = computed(() => reportRows.value.reduce((sum, row) => sum + row.estimateAmount, 0));
const reportInvoiceTotal = computed(() => reportRows.value.reduce((sum, row) => sum + row.invoiceAmount, 0));
const reportReceiptTotal = computed(() => reportRows.value.reduce((sum, row) => sum + row.receiptAmount, 0));
const reportBalanceTotal = computed(() => reportRows.value.reduce((sum, row) => sum + row.balance, 0));

const userInitials = computed(() => {
    const parts = String(userName.value || "U")
        .trim()
        .split(/\s+/);
    return (parts[0]?.[0] || "U").concat(parts[1]?.[0] || "").toUpperCase();
});

const customerOptions = computed(() =>
    Array.from(
        new Set(
            pipelineRows.value
                .map((row) => row.customerName)
                .filter(Boolean)
                .filter((value) => value !== "--"),
        ),
    ).sort(),
);

const pipelineRows = computed(() => {
    const quotationByEstimationId = new Map(
        quotations.value
            .filter((item) => item.estimation_id)
            .map((item) => [item.estimation_id, item]),
    );

    const jobOrderByQuotationId = new Map(
        jobOrders.value
            .filter((item) => item.quotation_id)
            .map((item) => [item.quotation_id, item]),
    );
    const jobOrderByEstimationId = new Map(
        jobOrders.value
            .filter((item) => item.estimation_id)
            .map((item) => [item.estimation_id, item]),
    );

    const rows = [];

    estimations.value.forEach((estimation, index) => {
        const quotation = quotationByEstimationId.get(estimation.id) || null;
        const jobOrder =
            (quotation && jobOrderByQuotationId.get(quotation.id)) ||
            jobOrderByEstimationId.get(estimation.id) ||
            null;

        rows.push(
            buildPipelineRow({
                source: estimation,
                quotation,
                jobOrder,
                type: "Estimation",
                rowIndex: index + 1,
            }),
        );
    });

    quotations.value
        .filter((quotation) => !quotation.estimation_id)
        .forEach((quotation, index) => {
            const jobOrder = jobOrderByQuotationId.get(quotation.id) || null;
            rows.push(
                buildPipelineRow({
                    source: quotation,
                    quotation,
                    jobOrder,
                    type: "Quotation",
                    rowIndex: rows.length + index + 1,
                }),
            );
        });

    return rows;
});

const filteredPipelineRows = computed(() => {
    const global = globalSearch.value.trim().toLowerCase();
    const site = jobSiteSearch.value.trim().toLowerCase();
    const est = estimationSearch.value.trim().toLowerCase();
    const quo = quotationSearch.value.trim().toLowerCase();
    const job = jobOrderSearch.value.trim().toLowerCase();

    return pipelineRows.value.filter((row) => {
        const matchesCustomer =
            !selectedCustomer.value ||
            row.customerName === selectedCustomer.value;
        const matchesSite =
            !site || row.jobSite.toLowerCase().includes(site);
        const matchesEst =
            !est || row.estimationNo.toLowerCase().includes(est);
        const matchesQuo =
            !quo || row.quotationNo.toLowerCase().includes(quo);
        const matchesJob =
            !job || row.jobOrderNo.toLowerCase().includes(job);
        const matchesGlobal =
            !global ||
            [
                row.customerName,
                row.jobSite,
                row.estimationNo,
                row.quotationNo,
                row.jobOrderNo,
                row.transCode,
            ]
                .filter(Boolean)
                .some((value) =>
                    String(value).toLowerCase().includes(global),
                );

        return (
            matchesCustomer &&
            matchesSite &&
            matchesEst &&
            matchesQuo &&
            matchesJob &&
            matchesGlobal
        );
    });
});

const activeJobsCount = computed(
    () =>
        jobOrders.value.filter((item) =>
            ["In Progress", "Scheduled", "On Hold"].includes(
                item.status_label,
            ),
        ).length,
);
const pendingInvoicesCount = computed(
    () =>
        jobOrders.value.filter(
            (item) => item.converted_to_invoice && !item.invoice_reference,
        ).length,
);
const totalRevenue = computed(() =>
    jobOrders.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);
const totalEstimationAmount = computed(() =>
    estimations.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);
const totalQuotationAmount = computed(() =>
    quotations.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);
const totalJobOrderAmount = computed(() =>
    jobOrders.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);
const scheduledJobs = computed(() =>
    jobOrders.value.filter((item) => item.status_label === "Scheduled"),
);
const inProgressJobs = computed(() =>
    jobOrders.value.filter((item) => item.status_label === "In Progress"),
);
const completedJobs = computed(() =>
    jobOrders.value.filter((item) => item.status_label === "Completed"),
);
const scheduledCount = computed(() => scheduledJobs.value.length);
const inProgressCount = computed(() => inProgressJobs.value.length);
const completedCount = computed(() => completedJobs.value.length);
const scheduledAmount = computed(() =>
    scheduledJobs.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);
const inProgressAmount = computed(() =>
    inProgressJobs.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);
const completedAmount = computed(() =>
    completedJobs.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);

watch(
    () => authState.user?.name,
    (value) => {
        if (value) userName.value = value;
    },
    { immediate: true },
);

watch(sidebarOpen, (value) => {
    document.body.classList.toggle("pm-no-scroll", value);
});

onMounted(async () => {
    await loadData();
});

async function loadData() {
    try {
        const [estimationRes, quotationRes, jobOrderRes] = await Promise.all([
            client.get("/estimations", { params: { per_page: 100 } }),
            client.get("/quotations", { params: { per_page: 100 } }),
            client.get("/job-orders", { params: { per_page: 100 } }),
        ]);

        estimations.value = estimationRes.data?.data?.data || [];
        quotations.value = quotationRes.data?.data?.data || [];
        jobOrders.value = jobOrderRes.data?.data?.data || [];
    } catch (error) {
        console.error("Failed to load full status data", error);
        setFlash("Failed to load full status data.", "warning", 3000);
    }
}

function buildPipelineRow({ source, quotation, jobOrder, type, rowIndex }) {
    const customer =
        source.customer || quotation?.customer || jobOrder?.customer || null;
    const jobSite =
        jobOrder?.job_site || quotation?.job_site || source.job_site || null;
    const invoiceNo = jobOrder?.invoice_reference || "-";
    const invoiceDate =
        jobOrder?.invoice_reference && jobOrder?.issue_date
            ? jobOrder.issue_date
            : null;
    const converted =
        !!jobOrder || !!source.convert_to_job_order || !!quotation?.convert_to_job_order;

    return {
        key: `${type}-${source.id}`,
        customerNo: customer?.id ? `AH-${String(customer.id).padStart(4, "0")}` : "--",
        customerName: customer?.company_name || customer?.name || "--",
        phone: customer?.contact || "--",
        transCode:
            type === "Estimation"
                ? source.estimation_no || "--"
                : quotation?.quotation_no || source.quotation_no || "--",
        docType: type,
        docDate: source.issue_date || quotation?.issue_date || null,
        docAmount: toNumber(source.total || quotation?.total),
        status: source.status_label || quotation?.status_label || "--",
        converted,
        estimationNo:
            type === "Estimation" ? source.estimation_no || "" : quotation?.estimation?.estimation_no || "",
        quotationNo:
            quotation?.quotation_no || (type === "Quotation" ? source.quotation_no || "" : ""),
        jobOrderNo: jobOrder?.job_order_no || "-",
        jobSite: jobSite?.address || jobSite?.name || "-",
        startDate: jobOrder?.schedule_start_date || null,
        endDate: jobOrder?.schedule_end_date || null,
        jobAmount: toNumber(jobOrder?.total),
        jobStatus: jobOrder?.status_label || "Not Created",
        progress: toNumber(jobOrder?.progress),
        invoiceNo,
        invoiceDate,
        rowIndex,
    };
}

function resetFilters() {
    globalSearch.value = "";
    selectedCustomer.value = "";
    jobSiteSearch.value = "";
    estimationSearch.value = "";
    quotationSearch.value = "";
    jobOrderSearch.value = "";
}
function applyReport() { reportView.value = true; }
function clearReport() { selectedCustomer.value = ""; reportStatus.value = ""; reportDateFrom.value = ""; reportDateTo.value = ""; }
function reportDate(value) { return value ? new Date(value).toLocaleDateString("en-CA") : "-"; }
function reportTime(value) { return value ? new Date(value).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : "-"; }
function currency(value) { return toNumber(value).toLocaleString("en-CA", { style: "currency", currency: "CAD", minimumFractionDigits: 2 }); }
function printReport() { window.print(); }

function documentStatusClass(status) {
    if (status === "Approved") return "is-approved";
    if (status === "Pending") return "is-pending";
    if (status === "Rejected" || status === "Cancelled")
        return "is-rejected";
    return "is-neutral";
}

function jobStatusClass(status) {
    if (status === "Completed") return "is-completed";
    if (status === "In Progress") return "is-progress";
    if (status === "Not Created") return "is-not-created";
    if (status === "Scheduled") return "is-scheduled";
    return "is-neutral";
}

function progressBarClass(progress) {
    const value = toNumber(progress);
    if (value >= 100) return "is-complete";
    if (value >= 60) return "is-blue";
    if (value >= 35) return "is-yellow";
    return "is-red";
}

function toggleUserMenu() {
    userMenuOpen.value = !userMenuOpen.value;
}

async function logout() {
    try {
        await logoutRequest();
    } catch {
        // Ignore request errors and clear local state.
    } finally {
        clearToken();
        router.push("/login");
    }
}

function formatDate(value) {
    if (!value || value === "-") return "-";
    return new Date(value).toLocaleDateString();
}

function toNumber(value) {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
}

function formatMoney(value) {
    return toNumber(value).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
}

function compactMoney(value) {
    const numeric = toNumber(value);
    if (numeric >= 1000000) return `${(numeric / 1000000).toFixed(1)}M`;
    if (numeric >= 1000) return `${(numeric / 1000).toFixed(0)}K`;
    return numeric.toFixed(0);
}
</script>

<style scoped>
.pm-job-full-status-page {
    padding-bottom: 48px;
}

.pm-job-full-status-hero,
.pm-job-full-status-card,
.pm-job-full-status-summary-card {
    overflow: hidden;
    margin-bottom: 22px;
    border: 1px solid #dce6f4;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.07);
}

.pm-job-full-status-hero {
    padding: 28px 22px;
    text-align: center;
}

.pm-job-full-status-hero h2 {
    margin: 0 0 8px;
    color: #1c2f49;
    font-size: 2rem;
    font-weight: 800;
}

.pm-job-full-status-hero p {
    margin: 0;
    color: #60728c;
    font-size: 1rem;
}

.pm-job-full-status-metrics {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.pm-job-full-status-metric {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 18px;
    border-radius: 18px;
    color: #fff;
    box-shadow: 0 10px 24px rgba(15, 39, 71, 0.08);
}

.pm-job-full-status-metric.is-blue {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.pm-job-full-status-metric.is-purple {
    background: linear-gradient(135deg, #c026d3, #8b0cf0);
}

.pm-job-full-status-metric.is-green {
    background: linear-gradient(135deg, #22c55e, #0dbb3e);
}

.pm-job-full-status-metric.is-orange {
    background: linear-gradient(135deg, #ff8a00, #ff5f00);
}

.pm-job-full-status-metric.is-teal {
    background: linear-gradient(135deg, #16a085, #0f9f74);
}

.pm-job-full-status-metric-icon {
    display: inline-flex;
    width: 46px;
    height: 46px;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.45rem;
    font-weight: 700;
}

.pm-job-full-status-metric-label {
    color: rgba(255, 255, 255, 0.84);
    font-size: 0.94rem;
}

.pm-job-full-status-metric-value {
    font-size: 1.95rem;
    font-weight: 800;
    line-height: 1.1;
}

.pm-job-full-status-card-head,
.pm-job-full-status-summary-head {
    padding: 14px 22px;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
}

.pm-job-full-status-card-head {
    background: linear-gradient(90deg, #2563eb, #3b82f6);
}

.pm-job-full-status-summary-head.is-green {
    background: linear-gradient(90deg, #16a34a, #22c55e);
}

.pm-job-full-status-summary-head.is-purple {
    background: linear-gradient(90deg, #7c3aed, #a855f7);
}

.pm-job-full-status-card-body,
.pm-job-full-status-summary-body {
    padding: 20px 22px;
}

.pm-job-full-status-filter-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
    gap: 14px;
    align-items: end;
}

.pm-job-full-status-filter-actions {
    display: flex;
    gap: 10px;
}

.pm-job-full-status-search-btn,
.pm-job-full-status-reset-btn {
    border: 1px solid #d7dfef;
    border-radius: 10px;
    background: #fff;
    color: #213a5b;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 10px 16px;
}

.pm-job-full-status-search-btn {
    min-width: 128px;
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-job-full-status-table th {
    white-space: nowrap;
    background: linear-gradient(180deg, #2f70f0, #2563eb);
    color: #fff;
    font-size: 0.82rem;
    font-weight: 700;
    vertical-align: middle;
}

.pm-job-full-status-table td {
    vertical-align: middle;
    color: #20334e;
}

.pm-job-full-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 78px;
    padding: 6px 10px;
    border-radius: 999px;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
}

.pm-job-full-status-badge.is-approved,
.pm-job-full-status-badge.is-completed {
    background: #22c55e;
}

.pm-job-full-status-badge.is-pending {
    background: #f97316;
}

.pm-job-full-status-badge.is-rejected {
    background: #ff4040;
}

.pm-job-full-status-badge.is-progress,
.pm-job-full-status-badge.is-scheduled {
    background: #3b82f6;
}

.pm-job-full-status-badge.is-not-created,
.pm-job-full-status-badge.is-neutral {
    background: #64748b;
}

.pm-job-full-status-progress {
    display: grid;
    gap: 6px;
}

.pm-job-full-status-progress-bar {
    width: 86px;
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #dde5ef;
}

.pm-job-full-status-progress-bar span {
    display: block;
    height: 100%;
    border-radius: inherit;
}

.pm-job-full-status-progress-bar span.is-complete {
    background: #22c55e;
}

.pm-job-full-status-progress-bar span.is-blue {
    background: #3b82f6;
}

.pm-job-full-status-progress-bar span.is-yellow {
    background: #f4b400;
}

.pm-job-full-status-progress-bar span.is-red {
    background: #ff4d4f;
}

.pm-job-full-status-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.pm-job-full-status-summary-row {
    display: grid;
    grid-template-columns: 1.4fr 0.6fr 0.9fr;
    gap: 16px;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #e4ecf5;
    color: #1f334d;
}

.pm-job-full-status-summary-row:first-child {
    padding-top: 0;
}

.pm-job-full-status-summary-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.pm-job-full-status-summary-row.is-highlight-green {
    padding: 14px 12px;
    border-radius: 12px;
    background: #eaf9ee;
    color: #16a34a;
    font-weight: 700;
}

.is-link-code {
    color: #2563eb;
    font-weight: 700;
}

.is-estimation-doc {
    color: #ef4444;
    font-weight: 700;
}

.is-quotation-doc {
    color: #334155;
    font-weight: 700;
}

.is-converted {
    color: #16a34a;
    font-weight: 700;
}

.is-not-converted {
    color: #94a3b8;
    font-weight: 700;
}

@media (max-width: 1199.98px) {
    .pm-job-full-status-metrics {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .pm-job-full-status-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .pm-job-full-status-metrics,
    .pm-job-full-status-filter-grid,
    .pm-job-full-status-summary-grid {
        grid-template-columns: 1fr;
    }
}
.jor-report{padding:8px 0 30px;color:#06145c}.jor-report h1{margin:0 0 14px;font-size:2rem}.jor-filter{display:grid;grid-template-columns:1.25fr 1.1fr 1fr 1fr 135px 115px;gap:36px;align-items:end;padding:16px 22px;border:1px solid #d9e2ef;border-radius:6px;background:#fff}.jor-filter label{display:grid;gap:6px;font-size:.72rem;font-weight:800}.jor-filter button,.jor-actions button{min-height:40px;border:0;border-radius:3px;background:#061b59;color:#fff;font-weight:800}.jor-filter .clear{border:1px solid #cbd6e6;background:#fff;color:#111}.jor-metrics{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin:10px 0}.jor-metrics article{display:grid;gap:14px;padding:14px;border:1px solid #dce4f0;border-radius:5px;background:#fff;text-align:center}.jor-metrics span{font-size:.82rem;font-weight:700}.jor-metrics b{font-size:1.35rem}.jor-table-wrap{border:1px solid #d4ddea;background:#fff}.jor-table{width:100%;min-width:1330px;border-collapse:collapse}.jor-table th{padding:8px 6px;border:1px solid #aebbd2;background:#061b59;color:#fff;font-size:.7rem;text-align:center}.jor-table td{padding:9px 7px;border:1px solid #dce3ed;font-size:.74rem;text-align:center}.jor-table .code{font-weight:800;color:#06145c}.jor-table .empty{padding:26px}.jor-table-footer{display:flex;justify-content:space-between;align-items:center;padding:8px;font-size:.78rem}.jor-table-footer button{min-width:30px;margin-left:5px;padding:4px 8px;border:1px solid #cdd7e6;border-radius:3px;background:#fff}.jor-table-footer button.active{background:#061b59;color:#fff}.jor-actions{display:grid;grid-template-columns:205px 200px 170px 260px;justify-content:space-between;gap:15px;margin-top:15px}.jor-actions button{padding:10px}.jor-actions button:last-child{grid-column:4}@media(max-width:1100px){.jor-filter{grid-template-columns:repeat(3,1fr);gap:16px}.jor-metrics{grid-template-columns:repeat(3,1fr)}.jor-actions{grid-template-columns:repeat(2,1fr)}.jor-actions button:last-child{grid-column:auto}}@media(max-width:650px){.jor-filter,.jor-metrics,.jor-actions{grid-template-columns:1fr}.jor-report h1{font-size:1.6rem}}@media print{.pm-dashboard-topbar,.pm-sidebar,.jor-filter,.jor-actions{display:none}.jor-report{padding:0}}
</style>
