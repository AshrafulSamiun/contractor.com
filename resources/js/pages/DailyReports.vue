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
                        placeholder="Search daily reports..."
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
                    <button
                        class="pm-icon-btn"
                        type="button"
                        aria-label="Notifications"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z"
                            />
                        </svg>
                    </button>
                    <button class="pm-icon-btn" type="button" aria-label="Mail">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M4 6h16v12H4zM4 8l8 5 8-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
                <div
                    ref="userMenuRef"
                    class="pm-topbar-user"
                    @click="toggleUserMenu"
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
                        <button
                            class="pm-user-item"
                            type="button"
                            @click.stop="goProfile"
                        >
                            Profile
                        </button>
                        <button
                            class="pm-user-item"
                            type="button"
                            @click.stop="goAccount"
                        >
                            Account
                        </button>
                        <button
                            class="pm-user-item danger"
                            type="button"
                            @click.stop="logoutUser"
                        >
                            Log out
                        </button>
                    </div>
                </div>
            </div>

            <div class="container pm-ops-page pm-daily-reports-page">
                <header class="pm-page-heading">
                    <h1>Daily Reports</h1>
                </header>

                <section class="pm-hero-card">
                    <div>
                        <h2>Daily Work Reports - List</h2>
                        <p>View and manage all daily work reports</p>
                    </div>
                </section>

                <section class="pm-toolbar-row">
                    <div class="pm-filter-group">
                        <input
                            v-model.trim="searchQuery"
                            type="search"
                            class="form-control"
                            placeholder="Search report no, employee, date..."
                        />
                        <select v-model="statusFilter" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="final">Final</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="pm-page-actions">
                        <button
                            class="pm-secondary-btn"
                            type="button"
                            @click="refreshItems"
                        >
                            Refresh
                        </button>
                        <button
                            class="pm-primary-btn"
                            type="button"
                            @click="openManager()"
                        >
                            <span>+</span>
                            New
                        </button>
                    </div>
                </section>

                <section class="pm-list-card">
                    <div class="table-responsive">
                        <table class="table pm-daily-table align-middle">
                            <thead>
                                <tr>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('report_no')"
                                        >
                                            Report No
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('report_no')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('report_date')"
                                        >
                                            Date
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('report_date')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('employee_name')"
                                        >
                                            Employee
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('employee_name')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('regular_hours')"
                                        >
                                            Regular Hours
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('regular_hours')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('overtime_hours')"
                                        >
                                            Overtime Hours
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('overtime_hours')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('total_hours')"
                                        >
                                            Total Hours
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('total_hours')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('total_jobs')"
                                        >
                                            Total Jobs
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('total_jobs')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-btn"
                                            type="button"
                                            @click="toggleSort('status')"
                                        >
                                            Status
                                            <span
                                                class="pm-sort-icon"
                                                :class="sortIconClass('status')"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in filteredItems" :key="item.id">
                                    <td class="pm-table-id">
                                        {{ item.report_no || "--" }}
                                    </td>
                                    <td>{{ formatDate(item.report_date) }}</td>
                                    <td>{{ item.employee_name || "--" }}</td>
                                    <td>
                                        <span class="pm-hour-chip is-blue">
                                            {{ formatHours(item.regular_hours) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pm-hour-chip is-orange">
                                            {{ formatHours(item.overtime_hours) }}
                                        </span>
                                    </td>
                                    <td class="pm-total-hours">
                                        {{ formatHours(item.total_hours, true) }}
                                    </td>
                                    <td>{{ item.total_jobs ?? 0 }}</td>
                                    <td>
                                        <span
                                            class="pm-status-pill"
                                            :class="statusClass(item.status)"
                                        >
                                            {{ formatStatus(item.status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="pm-table-actions">
                                            <button
                                                class="pm-action-btn"
                                                type="button"
                                                @click="openManager(item)"
                                            >
                                                View
                                            </button>
                                            <button
                                                class="pm-icon-action"
                                                type="button"
                                                aria-label="Print report"
                                                @click="printPage"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path
                                                        d="M7 8V4h10v4M6 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-1M7 14h10v6H7z"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.7"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!loading && !filteredItems.length">
                                    <td colspan="9" class="text-center py-5">
                                        No daily reports matched your search.
                                    </td>
                                </tr>
                                <tr v-if="loading">
                                    <td colspan="9" class="text-center py-5">
                                        Loading daily reports...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
        <div
            v-if="sidebarOpen"
            class="pm-sidebar-overlay"
            @click="toggleSidebar"
        ></div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
import dailyReportsService from "../api/dailyReports";
import { clearToken, logout as apiLogout } from "../api/auth";
import { authState } from "../store/auth";

const router = useRouter();

const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const userName = ref("John Doe");
const loading = ref(false);
const searchQuery = ref("");
const statusFilter = ref("");
const items = ref([]);
const sortKey = ref("report_date");
const sortDirection = ref("desc");

const userInitials = computed(() => {
    const value = userName.value || "User";
    return value
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || "")
        .join("");
});

const filteredItems = computed(() => {
    const search = searchQuery.value.trim().toLowerCase();
    const direction = sortDirection.value === "asc" ? 1 : -1;

    return [...items.value]
        .filter((item) => {
            const matchesStatus =
                !statusFilter.value || item.status === statusFilter.value;

            if (!matchesStatus) return false;
            if (!search) return true;

            return [
                item.report_no,
                item.report_date,
                item.employee_name,
                item.report_location,
                item.status,
                item.regular_hours,
                item.overtime_hours,
                item.total_hours,
                item.total_jobs,
            ]
                .filter((value) => value !== null && value !== undefined)
                .some((value) =>
                    String(value).toLowerCase().includes(search),
                );
        })
        .sort((left, right) => {
            const leftValue = normalizeSortValue(left[sortKey.value]);
            const rightValue = normalizeSortValue(right[sortKey.value]);

            if (leftValue < rightValue) return -1 * direction;
            if (leftValue > rightValue) return 1 * direction;
            return 0;
        });
});

function normalizeSortValue(value) {
    if (value === null || value === undefined || value === "") return "";
    if (typeof value === "number") return value;
    const numeric = Number(value);
    if (!Number.isNaN(numeric) && String(value).trim() !== "") return numeric;
    return String(value).toLowerCase();
}

function toggleSort(key) {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortKey.value = key;
    sortDirection.value = key === "report_date" ? "desc" : "asc";
}

function sortIconClass(key) {
    return {
        active: sortKey.value === key,
        desc: sortKey.value === key && sortDirection.value === "desc",
    };
}

function statusClass(status) {
    const value = String(status || "").toLowerCase();
    return {
        "is-draft": value === "draft",
        "is-submitted": value === "submitted",
        "is-approved": value === "approved" || value === "final",
        "is-rejected": value === "rejected",
    };
}

function formatStatus(status) {
    const value = String(status || "draft");
    return value.charAt(0).toUpperCase() + value.slice(1);
}

function formatDate(value) {
    if (!value) return "--";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString(undefined, {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
}

function formatHours(value, emphasize = false) {
    if (value === null || value === undefined || value === "") {
        return emphasize ? "--" : "0 hrs";
    }

    return `${Number(value).toFixed(1)} hrs`;
}

async function refreshItems() {
    loading.value = true;
    try {
        const { data } = await dailyReportsService.getItems();
        items.value = Array.isArray(data?.data) ? data.data : [];
    } finally {
        loading.value = false;
    }
}

function openManager(item = null) {
    router.push({
        name: "workforce-daily-report",
        query: item?.id
            ? { report: String(item.id), view: "detail" }
            : { mode: "create" },
    });
}

function printPage() {
    window.print();
}

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}

function closeSidebar() {
    sidebarOpen.value = false;
}

function toggleSidebarHidden() {
    sidebarHidden.value = !sidebarHidden.value;
}

function toggleUserMenu() {
    userMenuOpen.value = !userMenuOpen.value;
}

function handleClickOutside(event) {
    if (!userMenuRef.value?.contains(event.target)) {
        userMenuOpen.value = false;
    }
}

function goProfile() {
    userMenuOpen.value = false;
    router.push("/account/profile");
}

function goAccount() {
    userMenuOpen.value = false;
    router.push("/account/status");
}

async function logoutUser() {
    userMenuOpen.value = false;
    try {
        await apiLogout();
    } catch {
        // ignore logout errors
    }
    clearToken();
    await router.push("/login");
}

onMounted(async () => {
    if (authState.user?.name) {
        userName.value = authState.user.name;
    }

    document.addEventListener("click", handleClickOutside);
    await refreshItems();
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.pm-daily-reports-page {
    padding-bottom: 2rem;
}

.pm-page-heading {
    margin: 1rem 0 1.35rem;
}

.pm-page-heading h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: #1f2937;
}

.pm-hero-card {
    margin-bottom: 1rem;
    padding: 1.8rem 1.55rem;
    border-radius: 18px;
    background: linear-gradient(90deg, #2563eb 0%, #54a2f6 100%);
    color: #fff;
    box-shadow: 0 20px 40px rgba(37, 99, 235, 0.18);
}

.pm-hero-card h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
}

.pm-hero-card p {
    margin: 0.55rem 0 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.02rem;
}

.pm-toolbar-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.1rem;
}

.pm-filter-group,
.pm-page-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.pm-filter-group {
    flex: 1;
    flex-wrap: nowrap;
}

.pm-filter-group .form-control:first-child {
    max-width: 340px;
}

.pm-filter-group .form-control:last-child {
    max-width: 180px;
}

.pm-primary-btn,
.pm-secondary-btn {
    border-radius: 12px;
    padding: 0.78rem 1rem;
    border: 1px solid #d7deea;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #fff;
    color: #111827;
}

.pm-primary-btn {
    background: #10b341;
    border-color: #10b341;
    color: #fff;
}

.pm-list-card {
    background: #fff;
    border: 1px solid #dce3ef;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.04);
}

.pm-daily-table {
    margin: 0;
}

.pm-daily-table thead th {
    padding: 1.15rem 1rem;
    background: #fff;
    border-bottom: 1px solid #e5eaf3;
    white-space: nowrap;
    font-size: 0.98rem;
    color: #1f2937;
}

.pm-daily-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #eef2f7;
    color: #273449;
    vertical-align: middle;
}

.pm-daily-table tbody tr:last-child td {
    border-bottom: 0;
}

.pm-sort-btn {
    border: 0;
    padding: 0;
    background: transparent;
    color: inherit;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.pm-sort-icon {
    opacity: 0.3;
    transform: rotate(0deg);
    transition: transform 0.18s ease, opacity 0.18s ease;
}

.pm-sort-icon.active {
    opacity: 1;
    color: #2563eb;
}

.pm-sort-icon.desc {
    transform: rotate(180deg);
}

.pm-table-id,
.pm-total-hours {
    font-weight: 700;
    color: #111827;
}

.pm-hour-chip,
.pm-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 70px;
    padding: 0.3rem 0.68rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
}

.pm-hour-chip.is-blue {
    color: #2563eb;
    background: #e8f0ff;
}

.pm-hour-chip.is-orange {
    color: #ea580c;
    background: #fff0e0;
}

.pm-status-pill.is-draft {
    color: #475569;
    background: #e5e7eb;
}

.pm-status-pill.is-submitted {
    color: #2563eb;
    background: #dbeafe;
}

.pm-status-pill.is-approved {
    color: #16a34a;
    background: #dcfce7;
}

.pm-status-pill.is-rejected {
    color: #dc2626;
    background: #fee2e2;
}

.pm-table-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
}

.pm-action-btn,
.pm-icon-action {
    border: 1px solid #d8deea;
    background: #fff;
    color: #111827;
    border-radius: 12px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.pm-action-btn {
    padding: 0 0.95rem;
}

.pm-icon-action {
    width: 40px;
}

.pm-icon-action svg {
    width: 18px;
    height: 18px;
}

@media (max-width: 1200px) {
    .pm-daily-table {
        min-width: 1180px;
    }
}

@media (max-width: 991px) {
    .pm-toolbar-row {
        flex-direction: column;
        align-items: stretch;
    }

    .pm-page-actions {
        flex-wrap: wrap;
    }
}

@media (max-width: 767px) {
    .pm-page-heading h1,
    .pm-hero-card h2 {
        font-size: 1.65rem;
    }

    .pm-filter-group {
        flex-direction: column;
        align-items: stretch;
        flex-wrap: wrap;
    }

    .pm-filter-group .form-control:first-child,
    .pm-filter-group .form-control:last-child {
        max-width: none;
    }
}
</style>
