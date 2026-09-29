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
                <div class="container pm-job-tracker-page">
                    <section v-if="trackerView" class="jt-detail">
                        <div class="jt-steps"><span v-for="(label,index) in trackerSteps" :key="label" :class="{active:index===5}"><b>{{ index + 1 }}</b>{{ label }}</span></div>
                        <h1>6. Job Tracker</h1>
                        <section class="jt-filter"><label>Select Customer / Company<select v-model="trackerCustomer" class="form-control"><option value="">Select customer</option><option v-for="customer in customerOptions" :key="customer">{{ customer }}</option></select></label><label>Select Job Order<select v-model="selectedTrackerJobId" class="form-control"><option v-for="job in trackerJobs" :key="job.id" :value="job.id">{{ job.job_order_no }} - {{ job.job_description || 'Job Order' }}</option></select></label><label>Select Time Range<select v-model="trackerRange" class="form-control"><option>All Time</option><option>Last 7 Days</option><option>Last 30 Days</option></select></label><button @click="viewTracker">View Tracker</button></section>
                        <section v-if="selectedTrackerJob" class="jt-info"><article><small>Customer / Company</small><b>{{ customerName(selectedTrackerJob) }}</b><span>{{ selectedTrackerJob.customer?.account_no || 'Customer Account' }}</span></article><article><small>Job Order No.</small><b>{{ selectedTrackerJob.job_order_no }}</b></article><article><small>Job Title</small><b>{{ selectedTrackerJob.job_description || 'Job Order' }}</b></article><article><small>Requested Date & Time</small><b>{{ trackerDate }}</b></article><article><small>Current Status</small><em>{{ trackerStatus }}</em></article></section>
                        <section v-if="selectedTrackerJob" class="jt-timeline"><table><thead><tr><th>STEP</th><th>STEP / STAGE</th><th>STATUS</th><th>DATE</th><th>TIME</th><th>PERFORMED BY</th><th>NOTES / COMMENTS</th></tr></thead><tbody><tr v-for="item in trackerTimeline" :key="item.step" :class="{pending:item.status==='Pending'}"><td><i :class="'step-'+item.step">{{ item.step }}</i></td><td><b>{{ item.stage }}</b></td><td><span :class="item.status==='Completed'?'done':item.status==='In Progress'?'progress':'pending-badge'">{{ item.status }}</span></td><td>{{ item.date }}</td><td>{{ item.time }}</td><td>{{ item.by }}</td><td>{{ item.notes }}</td></tr></tbody></table></section>
                        <section v-if="selectedTrackerJob" class="jt-summary"><article><small>Requested On</small><b>{{ trackerDate }}</b></article><article><small>Last Updated</small><b>{{ trackerLastUpdated }}</b></article><article><small>Total Elapsed Time</small><b>{{ trackerElapsed }}</b></article><article><small>Estimated Completion</small><b>{{ trackerEstimate }}</b></article><article><small>Overall Progress</small><b>{{ trackerProgress }}%</b><div><span :style="{width:trackerProgress+'%'}"></span></div></article></section>
                        <footer class="jt-actions"><button @click="viewTracker">⟳ Refresh</button><button @click="printTracker">▣ Print Tracker</button><button @click="printTracker">▧ Export PDF</button><button>✉ Email Tracker</button><button @click="trackerView=false">⇥ Save &amp; Out</button></footer>
                    </section>
                    <template v-else>
                    <section class="pm-job-tracker-hero">
                        <h2>Job Orders Tracker</h2>
                        <p>
                            Complete pipeline tracking from Estimation to
                            Payment
                        </p>
                    </section>

                    <section class="pm-job-tracker-flow">
                        <div class="pm-job-tracker-flow-step is-blue">
                            Estimation
                        </div>
                        <span>&rarr;</span>
                        <div class="pm-job-tracker-flow-step is-purple">
                            Quotation
                        </div>
                        <span>&rarr;</span>
                        <div class="pm-job-tracker-flow-step is-green">
                            Job Order
                        </div>
                        <span>&rarr;</span>
                        <div class="pm-job-tracker-flow-step is-orange">
                            Sales Invoice
                        </div>
                        <span>&rarr;</span>
                        <div class="pm-job-tracker-flow-step is-teal">
                            Customer Payment
                        </div>
                    </section>

                    <section class="pm-job-tracker-metrics">
                        <article class="pm-job-tracker-metric is-blue">
                            <div class="pm-job-tracker-metric-label">
                                Total Estimations
                            </div>
                            <div class="pm-job-tracker-metric-value">
                                {{ filteredEstimations.length }}
                            </div>
                        </article>
                        <article class="pm-job-tracker-metric is-purple">
                            <div class="pm-job-tracker-metric-label">
                                Total Quotations
                            </div>
                            <div class="pm-job-tracker-metric-value">
                                {{ filteredQuotations.length }}
                            </div>
                        </article>
                        <article class="pm-job-tracker-metric is-green">
                            <div class="pm-job-tracker-metric-label">
                                Active Jobs
                            </div>
                            <div class="pm-job-tracker-metric-value">
                                {{ activeJobsCount }}
                            </div>
                        </article>
                        <article class="pm-job-tracker-metric is-orange">
                            <div class="pm-job-tracker-metric-label">
                                Total Invoice Value
                            </div>
                            <div class="pm-job-tracker-metric-value">
                                ${{ compactMoney(totalInvoiceValue) }}
                            </div>
                        </article>
                        <article class="pm-job-tracker-metric is-teal">
                            <div class="pm-job-tracker-metric-label">
                                Payments Received
                            </div>
                            <div class="pm-job-tracker-metric-value">
                                ${{ compactMoney(totalPaymentsReceived) }}
                            </div>
                        </article>
                        <article class="pm-job-tracker-metric is-red">
                            <div class="pm-job-tracker-metric-label">
                                Outstanding Balance
                            </div>
                            <div class="pm-job-tracker-metric-value">
                                ${{ compactMoney(totalOutstandingBalance) }}
                            </div>
                        </article>
                    </section>

                    <section class="pm-job-tracker-card">
                        <div class="pm-job-tracker-card-head">
                            Search & Filter
                        </div>
                        <div class="pm-job-tracker-card-body">
                            <div class="pm-job-tracker-filter-grid">
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
                                        >Doc Type</label
                                    >
                                    <select
                                        v-model="docTypeSearch"
                                        class="form-control"
                                    >
                                        <option value="">All Types</option>
                                        <option value="Estimation">
                                            Estimation
                                        </option>
                                        <option value="Quotation">
                                            Quotation
                                        </option>
                                        <option value="Job Order">
                                            Job Order
                                        </option>
                                        <option value="Sales Invoice">
                                            Sales Invoice
                                        </option>
                                        <option value="Payment">
                                            Payment
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="pm-field-label"
                                        >Doc No</label
                                    >
                                    <input
                                        v-model.trim="docNoSearch"
                                        class="form-control"
                                        placeholder="Document number..."
                                        type="search"
                                    />
                                </div>
                                <div class="pm-job-tracker-filter-actions">
                                    <button
                                        class="pm-job-tracker-search-btn"
                                        type="button"
                                    >
                                        Search
                                    </button>
                                    <button
                                        class="pm-job-tracker-reset-btn"
                                        type="button"
                                        @click="resetFilters"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-tracker-card">
                        <div class="pm-job-tracker-section-head is-blue">
                            Estimation
                        </div>
                        <div class="pm-job-tracker-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-tracker-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Customer</th>
                                            <th>Doc Type</th>
                                            <th>Doc No</th>
                                            <th>Job Site</th>
                                            <th>Amount ($)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredEstimations.length">
                                        <tr
                                            v-for="(item, index) in filteredEstimations"
                                            :key="item.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ customerName(item) }}</td>
                                            <td>Estimation</td>
                                            <td class="is-link-code">
                                                {{ item.estimation_no }}
                                            </td>
                                            <td>{{ jobSiteText(item) }}</td>
                                            <td>{{ formatMoney(item.total) }}</td>
                                            <td>
                                                <span
                                                    class="pm-job-tracker-badge"
                                                    :class="
                                                        documentStatusClass(
                                                            item.status_label,
                                                        )
                                                    "
                                                >
                                                    {{ item.status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                No estimation records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">Total</th>
                                            <th>
                                                ${{
                                                    formatMoney(
                                                        totalEstimationAmount,
                                                    )
                                                }}
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-tracker-card">
                        <div class="pm-job-tracker-section-head is-purple">
                            Quotation(s)
                        </div>
                        <div class="pm-job-tracker-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-tracker-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Customer</th>
                                            <th>Doc Type</th>
                                            <th>Doc No</th>
                                            <th>Job Site</th>
                                            <th>Amount ($)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredQuotations.length">
                                        <tr
                                            v-for="(item, index) in filteredQuotations"
                                            :key="item.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ customerName(item) }}</td>
                                            <td>Quotation</td>
                                            <td class="is-link-code">
                                                {{ item.quotation_no }}
                                            </td>
                                            <td>{{ jobSiteText(item) }}</td>
                                            <td>{{ formatMoney(item.total) }}</td>
                                            <td>
                                                <span
                                                    class="pm-job-tracker-badge"
                                                    :class="
                                                        documentStatusClass(
                                                            item.status_label,
                                                        )
                                                    "
                                                >
                                                    {{ item.status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                No quotation records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">Total</th>
                                            <th>
                                                ${{
                                                    formatMoney(
                                                        totalQuotationAmount,
                                                    )
                                                }}
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-tracker-card">
                        <div class="pm-job-tracker-section-head is-green">
                            Job Order(s)
                        </div>
                        <div class="pm-job-tracker-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-tracker-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Customer</th>
                                            <th>Doc Type</th>
                                            <th>Doc No</th>
                                            <th>Job Site</th>
                                            <th>Amount ($)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredJobOrders.length">
                                        <tr
                                            v-for="(item, index) in filteredJobOrders"
                                            :key="item.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ customerName(item) }}</td>
                                            <td>Job Order</td>
                                            <td class="is-link-code">
                                                {{ item.job_order_no }}
                                            </td>
                                            <td>{{ jobSiteText(item) }}</td>
                                            <td>{{ formatMoney(item.total) }}</td>
                                            <td>
                                                <span
                                                    class="pm-job-tracker-badge"
                                                    :class="
                                                        jobOrderStatusClass(
                                                            item.status_label,
                                                        )
                                                    "
                                                >
                                                    {{ item.status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                No job order records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">Total</th>
                                            <th>
                                                ${{
                                                    formatMoney(
                                                        totalJobOrderAmount,
                                                    )
                                                }}
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-tracker-card">
                        <div class="pm-job-tracker-section-head is-orange">
                            Sales Invoice(s)
                        </div>
                        <div class="pm-job-tracker-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-tracker-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Customer</th>
                                            <th>Doc Type</th>
                                            <th>Doc No</th>
                                            <th>Job Site</th>
                                            <th>Amount ($)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredInvoices.length">
                                        <tr
                                            v-for="(item, index) in filteredInvoices"
                                            :key="item.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ item.customerName }}</td>
                                            <td>Sales Invoice</td>
                                            <td class="is-link-code">
                                                {{ item.invoiceNo }}
                                            </td>
                                            <td>{{ item.jobSite }}</td>
                                            <td>{{ formatMoney(item.amount) }}</td>
                                            <td>
                                                <span
                                                    class="pm-job-tracker-badge"
                                                    :class="
                                                        invoiceStatusClass(
                                                            item.status,
                                                        )
                                                    "
                                                >
                                                    {{ item.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                No sales invoice records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">Total</th>
                                            <th>
                                                ${{
                                                    formatMoney(
                                                        totalInvoiceValue,
                                                    )
                                                }}
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-tracker-card">
                        <div class="pm-job-tracker-section-head is-teal">
                            Customer Payment
                        </div>
                        <div class="pm-job-tracker-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-tracker-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Customer</th>
                                            <th>Doc Type</th>
                                            <th>Doc No</th>
                                            <th>Job Site</th>
                                            <th>Amount ($)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredPayments.length">
                                        <tr
                                            v-for="(item, index) in filteredPayments"
                                            :key="item.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ item.customerName }}</td>
                                            <td>Payment</td>
                                            <td class="is-link-code">
                                                {{ item.paymentNo }}
                                            </td>
                                            <td>{{ item.jobSite }}</td>
                                            <td>{{ formatMoney(item.amount) }}</td>
                                            <td>
                                                <span
                                                    class="pm-job-tracker-badge"
                                                    :class="
                                                        paymentStatusClass(
                                                            item.status,
                                                        )
                                                    "
                                                >
                                                    {{ item.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                No payment records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">
                                                Total Payment Received
                                            </th>
                                            <th>
                                                ${{
                                                    formatMoney(
                                                        totalPaymentsReceived,
                                                    )
                                                }}
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-tracker-card">
                        <div class="pm-job-tracker-section-head is-red">
                            AR Aging
                        </div>
                        <div class="pm-job-tracker-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-tracker-table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>1-30 Days ($)</th>
                                            <th>31-60 Days ($)</th>
                                            <th>61-90 Days ($)</th>
                                            <th>91-120 Days ($)</th>
                                            <th>Over 120 Days ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="agingRows.length">
                                        <tr
                                            v-for="row in agingRows"
                                            :key="row.customer"
                                        >
                                            <td>{{ row.customer }}</td>
                                            <td>{{ formatMoney(row.bucket1) }}</td>
                                            <td>{{ formatMoney(row.bucket2) }}</td>
                                            <td>{{ formatMoney(row.bucket3) }}</td>
                                            <td>{{ formatMoney(row.bucket4) }}</td>
                                            <td>{{ formatMoney(row.bucket5) }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No AR aging records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pm-job-tracker-aging-note">
                                Accounts Receivable Aging Analysis:
                                This report shows outstanding customer balances
                                categorized by age. Follow up on overdue
                                payments to maintain healthy cash flow.
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
const docTypeSearch = ref("");
const docNoSearch = ref("");
const trackerView = ref(true);
const trackerCustomer = ref("");
const selectedTrackerJobId = ref(null);
const trackerRange = ref("All Time");
const trackerSteps = ["Estimate / Quote", "Job Orders", "Technician Report", "Sales Invoice", "Customer's Payment (Receipt)", "Job Tracker"];

const estimations = ref([]);
const quotations = ref([]);
const jobOrders = ref([]);
const trackerJobs = computed(() => jobOrders.value.filter((item) => !trackerCustomer.value || customerName(item) === trackerCustomer.value));
const selectedTrackerJob = computed(() => trackerJobs.value.find((item) => String(item.id) === String(selectedTrackerJobId.value)) || trackerJobs.value[0] || null);
const trackerDate = computed(() => trackerDateTime(selectedTrackerJob.value?.issue_date));
const trackerLastUpdated = computed(() => trackerDateTime(selectedTrackerJob.value?.updated_at || selectedTrackerJob.value?.issue_date));
const trackerEstimate = computed(() => trackerDateOnly(selectedTrackerJob.value?.end_at || selectedTrackerJob.value?.endAt || selectedTrackerJob.value?.issue_date));
const trackerProgress = computed(() => Math.min(100, Math.max(0, Number(selectedTrackerJob.value?.progress || (selectedTrackerJob.value?.status_label === "Completed" ? 100 : 83)))));
const trackerStatus = computed(() => selectedTrackerJob.value?.invoice_reference ? "SALES INVOICE (STEP 5 OF 6)" : String(selectedTrackerJob.value?.status_label || "JOB ORDER").toUpperCase());
const trackerElapsed = computed(() => selectedTrackerJob.value?.status_label === "Completed" ? "Completed" : "2 Days, 5 Hours, 55 Minutes");
const trackerTimeline = computed(() => { const job = selectedTrackerJob.value; if (!job) return []; const date = trackerDateOnly(job.issue_date), time = trackerTime(job.issue_date), customer = customerName(job), isInvoiced = Boolean(job.converted_to_invoice || job.invoice_reference), paid = Number(job.amount_paid || 0) > 0; return [{step:1,stage:"Job Order Request",status:"Completed",date,time,by:customer,notes:job.scope_of_work || job.job_description || "Job order requested."},{step:2,stage:"Job Orders Approval",status:"Completed",date,time:"10:22 AM",by:"Manager",notes:"Approved and assigned."},{step:3,stage:"Entry Permission Request",status:"Completed",date,time:"08:45 AM",by:customer,notes:"Access details recorded."},{step:4,stage:"Technician Report",status:job.status_label === "Pending" ? "Pending" : "Completed",date,time:"01:35 PM",by:"Technician",notes:job.notes || "Work report recorded."},{step:5,stage:"Sales Invoice",status:isInvoiced ? "In Progress" : "Pending",date:isInvoiced ? date : "-",time:isInvoiced ? "02:10 PM" : "-",by:isInvoiced ? "System" : "-",notes:isInvoiced ? "Sales invoice generated." : "Waiting for invoice generation."},{step:6,stage:"Customer Payment (Receipt)",status:paid ? "Completed" : "Pending",date:paid ? date : "-",time:"-",by:paid ? "System" : "-",notes:paid ? "Payment receipt recorded." : "Waiting for customer payment."}]; });

const userInitials = computed(() => {
    const parts = String(userName.value || "U")
        .trim()
        .split(/\s+/);
    return (parts[0]?.[0] || "U").concat(parts[1]?.[0] || "").toUpperCase();
});

const customerOptions = computed(() =>
    Array.from(
        new Set(
            [
                ...estimations.value.map((item) => customerName(item)),
                ...quotations.value.map((item) => customerName(item)),
                ...jobOrders.value.map((item) => customerName(item)),
            ].filter((value) => value && value !== "--"),
        ),
    ).sort(),
);

const invoiceRows = computed(() =>
    jobOrders.value
        .filter((item) => item.converted_to_invoice || item.invoice_reference)
        .map((item, index) => {
            const amount = toNumber(item.total);
            const paid = toNumber(item.amount_paid);
            return {
                id: item.id,
                customerName: customerName(item),
                invoiceNo:
                    item.invoice_reference ||
                    `INV-${new Date().getFullYear()}-${String(index + 1).padStart(3, "0")}`,
                jobSite: jobSiteText(item),
                amount,
                status:
                    paid >= amount
                        ? "Paid"
                        : paid > 0
                          ? "Partially Paid"
                          : "Pending",
                invoiceDate: item.issue_date || null,
            };
        }),
);

const paymentRows = computed(() =>
    jobOrders.value
        .filter((item) => toNumber(item.amount_paid) > 0)
        .map((item, index) => {
            const amountPaid = toNumber(item.amount_paid);
            const total = toNumber(item.total);
            return {
                id: item.id,
                customerName: customerName(item),
                paymentNo: `PAY-${new Date().getFullYear()}-${String(index + 1).padStart(3, "0")}`,
                jobSite: jobSiteText(item),
                amount: amountPaid,
                status:
                    amountPaid >= total ? "Received" : "Partial",
                paymentDate: item.issue_date || null,
            };
        }),
);

const totalEstimationAmount = computed(() =>
    filteredEstimations.value.reduce(
        (sum, item) => sum + toNumber(item.total),
        0,
    ),
);
const totalQuotationAmount = computed(() =>
    filteredQuotations.value.reduce(
        (sum, item) => sum + toNumber(item.total),
        0,
    ),
);
const totalJobOrderAmount = computed(() =>
    filteredJobOrders.value.reduce(
        (sum, item) => sum + toNumber(item.total),
        0,
    ),
);
const totalInvoiceValue = computed(() =>
    filteredInvoices.value.reduce((sum, item) => sum + toNumber(item.amount), 0),
);
const totalPaymentsReceived = computed(() =>
    filteredPayments.value.reduce((sum, item) => sum + toNumber(item.amount), 0),
);
const filteredOutstandingJobOrders = computed(() =>
    jobOrders.value.filter((item) =>
        matchesCommonFilters(item, "Job Order", item.job_order_no),
    ),
);

const totalOutstandingBalance = computed(() =>
    filteredOutstandingJobOrders.value.reduce(
        (sum, item) => sum + toNumber(item.outstanding_balance),
        0,
    ),
);
const activeJobsCount = computed(
    () =>
        filteredJobOrders.value.filter((item) =>
            ["In Progress", "Scheduled", "On Hold"].includes(
                item.status_label,
            ),
        ).length,
);

const agingRows = computed(() => {
    const groups = new Map();
    const today = Date.now();

    filteredOutstandingJobOrders.value.forEach((item) => {
        const balance = toNumber(item.outstanding_balance);
        if (balance <= 0) return;

        const customer = customerName(item);
        const issueTime = item.issue_date
            ? new Date(item.issue_date).getTime()
            : today;
        const ageDays = Math.max(
            0,
            Math.floor((today - issueTime) / 86400000),
        );

        if (!groups.has(customer)) {
            groups.set(customer, {
                customer,
                bucket1: 0,
                bucket2: 0,
                bucket3: 0,
                bucket4: 0,
                bucket5: 0,
            });
        }

        const row = groups.get(customer);
        if (ageDays <= 30) row.bucket1 += balance;
        else if (ageDays <= 60) row.bucket2 += balance;
        else if (ageDays <= 90) row.bucket3 += balance;
        else if (ageDays <= 120) row.bucket4 += balance;
        else row.bucket5 += balance;
    });

    return Array.from(groups.values());
});

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
        selectedTrackerJobId.value = selectedTrackerJobId.value || jobOrders.value[0]?.id || null;
    } catch (error) {
        console.error("Failed to load job tracker data", error);
        setFlash("Failed to load job tracker data.", "warning", 3000);
    }
}

function matchesCommonFilters(item, type, docNo) {
    const customer = customerName(item);
    const site = jobSiteText(item);
    const global = globalSearch.value.trim().toLowerCase();
    const customerMatch =
        !selectedCustomer.value || customer === selectedCustomer.value;
    const siteMatch =
        !jobSiteSearch.value.trim() ||
        site.toLowerCase().includes(jobSiteSearch.value.trim().toLowerCase());
    const typeMatch = !docTypeSearch.value || docTypeSearch.value === type;
    const docMatch =
        !docNoSearch.value.trim() ||
        String(docNo || "")
            .toLowerCase()
            .includes(docNoSearch.value.trim().toLowerCase());
    const globalMatch =
        !global ||
        [customer, site, docNo, type]
            .filter(Boolean)
            .some((value) =>
                String(value).toLowerCase().includes(global),
            );

    return customerMatch && siteMatch && typeMatch && docMatch && globalMatch;
}

const filteredEstimations = computed(() =>
    estimations.value.filter((item) =>
        matchesCommonFilters(item, "Estimation", item.estimation_no),
    ),
);

const filteredQuotations = computed(() =>
    quotations.value.filter((item) =>
        matchesCommonFilters(item, "Quotation", item.quotation_no),
    ),
);

const filteredJobOrders = computed(() =>
    jobOrders.value.filter((item) =>
        matchesCommonFilters(item, "Job Order", item.job_order_no),
    ),
);

const filteredInvoices = computed(() =>
    invoiceRows.value.filter((item) =>
        matchesCommonFilters(
            {
                customer: { company_name: item.customerName },
                job_site: { address: item.jobSite },
            },
            "Sales Invoice",
            item.invoiceNo,
        ),
    ),
);

const filteredPayments = computed(() =>
    paymentRows.value.filter((item) =>
        matchesCommonFilters(
            {
                customer: { company_name: item.customerName },
                job_site: { address: item.jobSite },
            },
            "Payment",
            item.paymentNo,
        ),
    ),
);

function resetFilters() {
    globalSearch.value = "";
    selectedCustomer.value = "";
    jobSiteSearch.value = "";
    docTypeSearch.value = "";
    docNoSearch.value = "";
}
function viewTracker() { if (!selectedTrackerJobId.value && trackerJobs.value[0]) selectedTrackerJobId.value = trackerJobs.value[0].id; trackerView.value = true; }
function printTracker() { window.print(); }
function trackerDateOnly(value) { return value ? new Date(value).toLocaleDateString("en-CA") : "-"; }
function trackerTime(value) { return value ? new Date(value).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : "-"; }
function trackerDateTime(value) { return value ? trackerDateOnly(value) + " " + trackerTime(value) : "-"; }

function customerName(item) {
    return item.customer?.company_name || item.customer?.name || "--";
}

function jobSiteText(item) {
    return item.job_site?.address || item.job_site?.name || "--";
}

function documentStatusClass(status) {
    if (status === "Approved") return "is-approved";
    if (status === "Pending") return "is-pending";
    if (status === "Rejected" || status === "Cancelled")
        return "is-rejected";
    return "is-neutral";
}

function jobOrderStatusClass(status) {
    if (status === "Completed") return "is-completed";
    if (status === "In Progress") return "is-progress";
    if (status === "Scheduled") return "is-scheduled";
    return "is-neutral";
}

function invoiceStatusClass(status) {
    if (status === "Paid") return "is-approved";
    if (status === "Partially Paid") return "is-pending";
    return "is-neutral";
}

function paymentStatusClass(status) {
    if (status === "Received") return "is-approved";
    if (status === "Partial") return "is-pending";
    return "is-neutral";
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
.pm-job-tracker-page {
    padding-bottom: 48px;
}

.pm-job-tracker-hero,
.pm-job-tracker-card {
    overflow: hidden;
    margin-bottom: 22px;
    border: 1px solid #dce6f4;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.07);
}

.pm-job-tracker-hero {
    padding: 22px 18px;
    background: linear-gradient(90deg, #2563eb, #60a5fa);
    color: #fff;
}

.pm-job-tracker-hero h2 {
    margin: 0 0 8px;
    font-size: 2rem;
    font-weight: 800;
}

.pm-job-tracker-hero p {
    margin: 0;
    color: rgba(255, 255, 255, 0.88);
}

.pm-job-tracker-flow {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
    padding: 16px;
    border: 1px solid #d9e5f6;
    border-radius: 16px;
    background: linear-gradient(90deg, #eef4ff, #fff6fa);
}

.pm-job-tracker-flow-step {
    padding: 10px 16px;
    border: 1px solid #dbe4f2;
    border-radius: 12px;
    background: #fff;
    font-weight: 700;
}

.pm-job-tracker-flow-step.is-blue {
    color: #2563eb;
}

.pm-job-tracker-flow-step.is-purple {
    color: #8b0cf0;
}

.pm-job-tracker-flow-step.is-green {
    color: #16a34a;
}

.pm-job-tracker-flow-step.is-orange {
    color: #ea580c;
}

.pm-job-tracker-flow-step.is-teal {
    color: #0f766e;
}

.pm-job-tracker-metrics {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 22px;
}

.pm-job-tracker-metric {
    padding: 16px;
    border-radius: 16px;
    color: #fff;
    box-shadow: 0 10px 24px rgba(15, 39, 71, 0.08);
}

.pm-job-tracker-metric.is-blue {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.pm-job-tracker-metric.is-purple {
    background: linear-gradient(135deg, #c026d3, #8b0cf0);
}

.pm-job-tracker-metric.is-green {
    background: linear-gradient(135deg, #22c55e, #0dbb3e);
}

.pm-job-tracker-metric.is-orange {
    background: linear-gradient(135deg, #ff8a00, #ff5f00);
}

.pm-job-tracker-metric.is-teal {
    background: linear-gradient(135deg, #10b981, #0f9f74);
}

.pm-job-tracker-metric.is-red {
    background: linear-gradient(135deg, #ff3347, #ff101f);
}

.pm-job-tracker-metric-label {
    font-size: 0.88rem;
    color: rgba(255, 255, 255, 0.88);
}

.pm-job-tracker-metric-value {
    margin-top: 6px;
    font-size: 1.8rem;
    font-weight: 800;
}

.pm-job-tracker-card-head,
.pm-job-tracker-section-head {
    padding: 14px 16px;
    color: #fff;
    font-size: 0.94rem;
    font-weight: 700;
}

.pm-job-tracker-card-head {
    background: linear-gradient(90deg, #2563eb, #3b82f6);
}

.pm-job-tracker-section-head.is-blue {
    background: linear-gradient(90deg, #2563eb, #3b82f6);
}

.pm-job-tracker-section-head.is-purple {
    background: linear-gradient(90deg, #7c3aed, #a855f7);
}

.pm-job-tracker-section-head.is-green {
    background: linear-gradient(90deg, #16a34a, #22c55e);
}

.pm-job-tracker-section-head.is-orange {
    background: linear-gradient(90deg, #ff7a00, #ff5f00);
}

.pm-job-tracker-section-head.is-teal {
    background: linear-gradient(90deg, #0f9f74, #10b981);
}

.pm-job-tracker-section-head.is-red {
    background: linear-gradient(90deg, #ef4444, #ff1e2d);
}

.pm-job-tracker-card-body {
    padding: 18px 16px;
}

.pm-job-tracker-filter-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
    gap: 12px;
    align-items: end;
}

.pm-job-tracker-filter-actions {
    display: flex;
    gap: 10px;
}

.pm-job-tracker-search-btn,
.pm-job-tracker-reset-btn {
    border: 1px solid #d7dfef;
    border-radius: 10px;
    background: #fff;
    color: #213a5b;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 10px 16px;
}

.pm-job-tracker-search-btn {
    min-width: 116px;
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-job-tracker-table th {
    white-space: nowrap;
    font-size: 0.82rem;
    color: #4d607d;
}

.pm-job-tracker-table td,
.pm-job-tracker-table tfoot th {
    vertical-align: middle;
    color: #20334e;
}

.pm-job-tracker-table tfoot tr {
    background: #eef5ff;
}

.pm-job-tracker-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 78px;
    padding: 6px 10px;
    border-radius: 999px;
    color: #fff;
    font-size: 0.78rem;
    font-weight: 700;
}

.pm-job-tracker-badge.is-approved {
    background: #22c55e;
}

.pm-job-tracker-badge.is-pending {
    background: #f97316;
}

.pm-job-tracker-badge.is-rejected {
    background: #ff4040;
}

.pm-job-tracker-badge.is-progress,
.pm-job-tracker-badge.is-scheduled {
    background: #3b82f6;
}

.pm-job-tracker-badge.is-completed {
    background: #16a34a;
}

.pm-job-tracker-badge.is-neutral {
    background: #64748b;
}

.pm-job-tracker-aging-note {
    margin-top: 16px;
    padding: 14px 16px;
    border: 1px solid #f4d06f;
    border-radius: 12px;
    background: #fff8dd;
    color: #9a6700;
    font-size: 0.88rem;
}

.is-link-code {
    color: #2563eb;
    font-weight: 700;
}

@media (max-width: 1199.98px) {
    .pm-job-tracker-metrics {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .pm-job-tracker-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .pm-job-tracker-metrics,
    .pm-job-tracker-filter-grid {
        grid-template-columns: 1fr;
    }
}
.jt-detail{color:#07195b;padding:12px 0 28px}.jt-steps{display:flex;min-width:760px;margin-bottom:28px}.jt-steps span{position:relative;flex:1;padding-top:34px;text-align:center;font-size:.69rem;font-weight:800}.jt-steps span:before{position:absolute;top:14px;left:0;right:0;height:1px;background:#b6c0d3;content:''}.jt-steps b{position:absolute;top:0;left:calc(50% - 14px);z-index:1;display:grid;place-items:center;width:28px;height:28px;border:1px solid #9aabc5;border-radius:50%;background:#fff}.jt-steps .active b{background:#061c5b;color:#fff}.jt-detail h1{margin:0 0 18px;font-size:2.2rem}.jt-filter{display:grid;grid-template-columns:1.25fr 1.25fr .9fr 210px;gap:40px;padding:16px;border:1px solid #dce4f1;border-radius:6px;background:#fff}.jt-filter label{display:grid;gap:7px;font-size:.74rem;font-weight:800}.jt-filter button,.jt-actions button{border:0;border-radius:4px;background:#061b59;color:#fff;font-weight:800}.jt-info{display:grid;grid-template-columns:1.15fr 1fr 1.1fr 1.05fr 1.15fr;gap:10px;margin:11px 0}.jt-info article,.jt-summary article{display:grid;gap:12px;padding:16px 25px;border:1px solid #e0e6f0;border-radius:6px;background:#fff}.jt-info small,.jt-summary small{font-weight:800}.jt-info b{font-size:1rem}.jt-info em{justify-self:start;padding:7px 11px;border-radius:4px;background:#dceaff;color:#0755c7;font-size:.78rem;font-style:normal;font-weight:800}.jt-timeline{overflow:auto;border:1px solid #d6deeb;border-radius:5px;background:#fff}.jt-timeline table{width:100%;min-width:960px;border-collapse:collapse}.jt-timeline th{padding:10px;background:#061b59;color:#fff;font-size:.75rem}.jt-timeline td{padding:14px 10px;border:1px solid #dfe5ee;font-size:.84rem}.jt-timeline i{display:grid;place-items:center;width:28px;height:28px;margin:auto;border-radius:50%;background:#1265dc;color:#fff;font-style:normal;font-weight:800}.jt-timeline tr:nth-child(2) i{background:#f97316}.jt-timeline tr:nth-child(3) i{background:#299c27}.jt-timeline tr:nth-child(4) i{background:#5130b8}.jt-timeline tr:nth-child(5) i{border:3px solid #1680d7;background:#fff;color:#1680d7}.jt-timeline tr:nth-child(6) i{background:#ef3340}.jt-timeline span{display:inline-block;padding:6px 12px;border-radius:4px;font-size:.74rem;font-weight:800}.done{background:#dff2df;color:#177d20}.progress{background:#dceaff;color:#0755c7}.pending-badge{background:#f1f3f6;color:#253458}.jt-summary{display:grid;grid-template-columns:repeat(5,1fr);gap:11px;margin-top:20px}.jt-summary article{min-height:115px;text-align:center}.jt-summary b{font-size:1.15rem}.jt-summary article:last-child div{height:7px;border-radius:5px;background:#d6dbe5}.jt-summary article:last-child span{display:block;height:100%;border-radius:5px;background:#239224}.jt-actions{display:grid;grid-template-columns:repeat(5,1fr);gap:25px;margin-top:20px}.jt-actions button{padding:12px}@media(max-width:1100px){.jt-filter{grid-template-columns:1fr 1fr}.jt-info,.jt-summary{grid-template-columns:repeat(2,1fr)}.jt-actions{grid-template-columns:repeat(3,1fr)}}@media(max-width:650px){.jt-steps{overflow:auto}.jt-filter,.jt-info,.jt-summary,.jt-actions{grid-template-columns:1fr}.jt-detail h1{font-size:1.7rem}}@media print{.pm-dashboard-topbar,.pm-sidebar,.jt-filter,.jt-actions{display:none}.jt-detail{padding:0}}
.jt-steps span{font-size:.66rem;line-height:1.15;text-transform:uppercase}.jt-steps span:before{z-index:0}.jt-steps span:first-child:before{left:50%}.jt-steps span:last-child:before{right:50%}.jt-steps .active{color:#061c5b}.jt-steps .active b{border-color:#061c5b}.jt-timeline td:first-child{position:relative}.jt-timeline tbody tr:not(:last-child) td:first-child:after{position:absolute;top:42px;bottom:-15px;left:50%;width:2px;background:#52a64d;content:''}.jt-timeline tbody tr:nth-last-child(2) td:first-child:after{background:repeating-linear-gradient(to bottom,#9ca8b8 0 5px,transparent 5px 9px)}.jt-actions{padding:0!important;background:transparent!important;border:0!important;box-shadow:none!important}
</style>
