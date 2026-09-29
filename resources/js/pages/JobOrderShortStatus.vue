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
                        v-model="searchQuery"
                        class="form-control"
                        placeholder="Search..."
                    />
                    <button
                        v-if="searchQuery"
                        class="pm-clear-btn"
                        type="button"
                        aria-label="Clear search"
                        @click="searchQuery = ''"
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
                <div class="container pm-job-short-status-page">
                    <section class="pm-job-short-status-hero">
                        <h2>6-4 Job Orders Short Status</h2>
                        <p>
                            Quick overview and monitoring of all job orders
                        </p>
                    </section>

                    <section class="pm-job-short-status-metrics">
                        <article class="pm-job-short-status-metric is-blue">
                            <div class="pm-job-short-status-metric-icon">
                                &#128188;
                            </div>
                            <div>
                                <div class="pm-job-short-status-metric-label">
                                    Total Active Jobs
                                </div>
                                <div class="pm-job-short-status-metric-value">
                                    {{ activeJobsCount }}
                                </div>
                            </div>
                        </article>
                        <article class="pm-job-short-status-metric is-green">
                            <div class="pm-job-short-status-metric-icon">
                                &#10003;
                            </div>
                            <div>
                                <div class="pm-job-short-status-metric-label">
                                    Completed Jobs
                                </div>
                                <div class="pm-job-short-status-metric-value">
                                    {{ completedJobsCount }}
                                </div>
                            </div>
                        </article>
                        <article class="pm-job-short-status-metric is-red">
                            <div class="pm-job-short-status-metric-icon">
                                !
                            </div>
                            <div>
                                <div class="pm-job-short-status-metric-label">
                                    Overdue Jobs
                                </div>
                                <div class="pm-job-short-status-metric-value">
                                    {{ overdueJobsCount }}
                                </div>
                            </div>
                        </article>
                        <article class="pm-job-short-status-metric is-purple">
                            <div class="pm-job-short-status-metric-icon">
                                $
                            </div>
                            <div>
                                <div class="pm-job-short-status-metric-label">
                                    Total Budget Value
                                </div>
                                <div class="pm-job-short-status-metric-value">
                                    ${{ compactMoney(totalBudgetValue) }}
                                </div>
                            </div>
                        </article>
                    </section>

                    <section class="pm-job-short-status-card">
                        <div class="pm-job-short-status-card-head">
                            Search & Filter
                        </div>
                        <div class="pm-job-short-status-card-body">
                            <div class="pm-job-short-status-filter-grid">
                                <div>
                                    <label class="pm-field-label"
                                        >Customer Name</label
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
                                        >Job Order No</label
                                    >
                                    <input
                                        v-model.trim="jobOrderSearch"
                                        class="form-control"
                                        type="search"
                                        placeholder="Enter job order number..."
                                    />
                                </div>
                                <div class="pm-job-short-status-filter-actions">
                                    <button
                                        class="pm-job-short-status-search-btn"
                                        type="button"
                                    >
                                        Search
                                    </button>
                                    <button
                                        class="pm-job-short-status-reset-btn"
                                        type="button"
                                        @click="resetFilters"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-job-short-status-card">
                        <div class="pm-job-short-status-card-head">
                            Job Orders Short Status
                        </div>
                        <div class="pm-job-short-status-card-body">
                            <div class="table-responsive">
                                <table class="table pm-job-short-status-table">
                                    <thead>
                                        <tr>
                                            <th>Job Order No</th>
                                            <th>Customer Name</th>
                                            <th>Job Site</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Left Days - Hours</th>
                                            <th>Total Cost ($)</th>
                                            <th>Total Budget ($)</th>
                                            <th>Budget Balance ($)</th>
                                            <th>Progress</th>
                                            <th>View</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredItems.length">
                                        <tr
                                            v-for="item in filteredItems"
                                            :key="item.id"
                                        >
                                            <td
                                                class="pm-job-short-status-order-no"
                                            >
                                                {{ item.job_order_no }}
                                            </td>
                                            <td>
                                                {{
                                                    item.customer
                                                        ?.company_name ||
                                                    item.customer?.name ||
                                                    "--"
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    item.job_site?.address ||
                                                    item.job_site?.name ||
                                                    "--"
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatDate(
                                                        item.schedule_start_date,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatDate(
                                                        item.schedule_end_date,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    class="pm-job-short-status-badge"
                                                    :class="
                                                        statusBadgeClass(
                                                            item.status_label,
                                                            item,
                                                        )
                                                    "
                                                >
                                                    {{
                                                        derivedStatus(item)
                                                    }}
                                                </span>
                                            </td>
                                            <td
                                                :class="{
                                                    'is-overdue-text':
                                                        isOverdue(item),
                                                }"
                                            >
                                                {{
                                                    remainingDurationLabel(
                                                        item,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatMoney(item.sub_total)
                                                }}
                                            </td>
                                            <td>
                                                {{ formatMoney(item.total) }}
                                            </td>
                                            <td
                                                :class="
                                                    budgetBalanceClass(item)
                                                "
                                            >
                                                {{
                                                    formatMoney(
                                                        budgetBalance(item),
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <div
                                                    class="pm-job-short-status-progress"
                                                >
                                                    <span
                                                        >{{
                                                            item.progress || 0
                                                        }}%</span
                                                    >
                                                    <div
                                                        class="pm-job-short-status-progress-bar"
                                                    >
                                                        <span
                                                            :class="
                                                                progressBarClass(
                                                                    item.progress,
                                                                )
                                                            "
                                                            :style="{
                                                                width: `${item.progress || 0}%`,
                                                            }"
                                                        ></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <button
                                                    class="pm-job-short-status-view-btn"
                                                    type="button"
                                                    @click="openJobOrder(item)"
                                                >
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="12" class="text-center">
                                                No job orders found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pm-job-short-status-footer">
                                Showing {{ filteredItems.length }} of
                                {{ listItems.length }} job orders
                            </div>
                        </div>
                    </section>
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

const searchQuery = ref("");
const jobOrderSearch = ref("");
const selectedCustomer = ref("");
const userName = ref("John Doe");
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const listItems = ref([]);

const userInitials = computed(() => {
    const parts = String(userName.value || "U")
        .trim()
        .split(/\s+/);
    return (parts[0]?.[0] || "U").concat(parts[1]?.[0] || "").toUpperCase();
});

const customerOptions = computed(() =>
    Array.from(
        new Set(
            listItems.value
                .map(
                    (item) =>
                        item.customer?.company_name || item.customer?.name,
                )
                .filter(Boolean),
        ),
    ).sort(),
);

const filteredItems = computed(() => {
    const globalSearch = searchQuery.value.trim().toLowerCase();
    const orderSearch = jobOrderSearch.value.trim().toLowerCase();

    return listItems.value.filter((item) => {
        const customerName =
            item.customer?.company_name || item.customer?.name || "";
        const matchesCustomer =
            !selectedCustomer.value || customerName === selectedCustomer.value;

        const matchesJobOrder =
            !orderSearch ||
            String(item.job_order_no || "")
                .toLowerCase()
                .includes(orderSearch);

        const matchesGlobal =
            !globalSearch ||
            [
                item.job_order_no,
                item.job_description,
                customerName,
                item.job_site?.address,
                item.job_site?.name,
            ]
                .filter(Boolean)
                .some((value) =>
                    String(value).toLowerCase().includes(globalSearch),
                );

        return matchesCustomer && matchesJobOrder && matchesGlobal;
    });
});

const activeJobsCount = computed(
    () =>
        filteredItems.value.filter((item) =>
            ["In Progress", "Scheduled", "Pending", "On Hold"].includes(
                item.status_label,
            ),
        ).length,
);

const completedJobsCount = computed(
    () =>
        filteredItems.value.filter(
            (item) => item.status_label === "Completed",
        ).length,
);

const overdueJobsCount = computed(
    () => filteredItems.value.filter((item) => isOverdue(item)).length,
);

const totalBudgetValue = computed(() =>
    filteredItems.value.reduce((sum, item) => sum + toNumber(item.total), 0),
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
    await loadItems();
});

async function loadItems() {
    try {
        const { data } = await client.get("/job-orders", {
            params: { per_page: 100 },
        });
        listItems.value = data?.data?.data || [];
    } catch (error) {
        console.error("Failed to load job order short status", error);
        setFlash("Failed to load job order short status.", "warning", 3000);
    }
}

function openJobOrder(item) {
    router.push(`/job-orders/orders/${item.id}`);
}

function resetFilters() {
    selectedCustomer.value = "";
    jobOrderSearch.value = "";
    searchQuery.value = "";
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

function budgetBalance(item) {
    return toNumber(item.total) - toNumber(item.sub_total);
}

function budgetBalanceClass(item) {
    const balance = budgetBalance(item);
    if (balance > 0) return "is-positive-balance";
    if (balance < 0) return "is-negative-balance";
    return "is-zero-balance";
}

function isOverdue(item) {
    if (!item.schedule_end_date) return false;
    if (item.status_label === "Completed" || item.status_label === "Cancelled") {
        return false;
    }
    return new Date(item.schedule_end_date).getTime() < Date.now();
}

function derivedStatus(item) {
    if (isOverdue(item)) return "Overdue";
    if (item.payment_status_label === "Fully Paid") return "Paid";
    return item.status_label || "Pending";
}

function remainingDurationLabel(item) {
    if (!item.schedule_end_date) return "--";

    const end = new Date(item.schedule_end_date);
    const now = new Date();
    const diff = end.getTime() - now.getTime();

    if (diff <= 0) {
        const overdueHours = Math.floor(Math.abs(diff) / 3600000);
        const overdueDays = Math.floor(overdueHours / 24);
        const hours = overdueHours % 24;
        return overdueDays
            ? `Overdue ${overdueDays} Days`
            : `Overdue ${hours}h`;
    }

    const totalHours = Math.floor(diff / 3600000);
    const days = Math.floor(totalHours / 24);
    const hours = totalHours % 24;
    return `${days}d ${hours}h`;
}

function statusBadgeClass(status, item) {
    if (isOverdue(item)) return "is-overdue";
    if (item.payment_status_label === "Fully Paid") return "is-paid";
    if (status === "Completed") return "is-completed";
    if (status === "Pending") return "is-pending";
    if (status === "In Progress") return "is-progress";
    if (status === "On Hold") return "is-hold";
    return "is-progress";
}

function progressBarClass(progress) {
    const value = toNumber(progress);
    if (value >= 100) return "is-complete";
    if (value >= 60) return "is-blue";
    if (value >= 40) return "is-yellow";
    return "is-red";
}

function formatDate(value) {
    if (!value) return "--";
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
    if (numeric >= 1000) return `${(numeric / 1000).toFixed(1)}K`;
    return numeric.toFixed(0);
}
</script>

<style scoped>
.pm-job-short-status-page {
    padding-bottom: 48px;
}

.pm-job-short-status-hero,
.pm-job-short-status-card {
    overflow: hidden;
    margin-bottom: 22px;
    border: 1px solid #dce6f4;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.07);
}

.pm-job-short-status-hero {
    padding: 24px 22px;
    background: linear-gradient(90deg, #2563eb, #60a5fa);
    color: #fff;
}

.pm-job-short-status-hero h2 {
    margin: 0 0 8px;
    font-size: 2rem;
    font-weight: 800;
}

.pm-job-short-status-hero p {
    margin: 0;
    color: rgba(255, 255, 255, 0.88);
    font-size: 1rem;
}

.pm-job-short-status-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.pm-job-short-status-metric {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 18px;
    border-radius: 18px;
    color: #fff;
    box-shadow: 0 10px 22px rgba(15, 39, 71, 0.08);
}

.pm-job-short-status-metric.is-blue {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.pm-job-short-status-metric.is-green {
    background: linear-gradient(135deg, #22c55e, #0dbb3e);
}

.pm-job-short-status-metric.is-red {
    background: linear-gradient(135deg, #ff3347, #ff101f);
}

.pm-job-short-status-metric.is-purple {
    background: linear-gradient(135deg, #a855f7, #8b0cf0);
}

.pm-job-short-status-metric-icon {
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

.pm-job-short-status-metric-label {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.85);
}

.pm-job-short-status-metric-value {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.1;
}

.pm-job-short-status-card-head {
    padding: 14px 22px;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    color: #fff;
    font-size: 0.96rem;
    font-weight: 700;
}

.pm-job-short-status-card-body {
    padding: 20px 22px;
}

.pm-job-short-status-filter-grid {
    display: grid;
    grid-template-columns: 1.3fr 1.3fr auto;
    gap: 16px;
    align-items: end;
}

.pm-job-short-status-filter-actions {
    display: flex;
    gap: 10px;
    align-items: end;
}

.pm-job-short-status-search-btn,
.pm-job-short-status-reset-btn,
.pm-job-short-status-view-btn {
    border: 1px solid #d7dfef;
    border-radius: 10px;
    background: #fff;
    color: #213a5b;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 10px 16px;
}

.pm-job-short-status-search-btn {
    min-width: 140px;
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-job-short-status-table th {
    white-space: nowrap;
    background: linear-gradient(180deg, #2f70f0, #2563eb);
    color: #fff;
    font-size: 0.88rem;
    font-weight: 700;
    vertical-align: middle;
}

.pm-job-short-status-table td {
    vertical-align: middle;
    color: #1f334d;
}

.pm-job-short-status-order-no {
    color: #2563eb;
    font-weight: 700;
}

.pm-job-short-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 78px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #fff;
}

.pm-job-short-status-badge.is-progress {
    background: #3b82f6;
}

.pm-job-short-status-badge.is-completed {
    background: #22c55e;
}

.pm-job-short-status-badge.is-pending {
    background: #f97316;
}

.pm-job-short-status-badge.is-paid {
    background: #0f766e;
}

.pm-job-short-status-badge.is-overdue {
    background: #ff303d;
}

.pm-job-short-status-badge.is-hold {
    background: #64748b;
}

.pm-job-short-status-progress {
    display: grid;
    gap: 6px;
}

.pm-job-short-status-progress-bar {
    width: 88px;
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #dbe1ea;
}

.pm-job-short-status-progress-bar span {
    display: block;
    height: 100%;
    border-radius: inherit;
}

.pm-job-short-status-progress-bar span.is-complete {
    background: #22c55e;
}

.pm-job-short-status-progress-bar span.is-blue {
    background: #3b82f6;
}

.pm-job-short-status-progress-bar span.is-yellow {
    background: #f4b400;
}

.pm-job-short-status-progress-bar span.is-red {
    background: #ff4d4f;
}

.pm-job-short-status-footer {
    margin-top: 14px;
    color: #5f7089;
    font-size: 0.92rem;
}

.is-positive-balance {
    color: #16a34a;
    font-weight: 700;
}

.is-negative-balance,
.is-overdue-text {
    color: #dc2626;
    font-weight: 700;
}

.is-zero-balance {
    color: #0f172a;
    font-weight: 700;
}

@media (max-width: 1199.98px) {
    .pm-job-short-status-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .pm-job-short-status-filter-grid,
    .pm-job-short-status-metrics {
        grid-template-columns: 1fr;
    }

    .pm-job-short-status-filter-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
