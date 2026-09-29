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
                        v-model="toolbarSearch"
                        class="form-control"
                        placeholder="Search..."
                    />
                    <button
                        v-if="toolbarSearch"
                        class="pm-clear-btn"
                        type="button"
                        aria-label="Clear search"
                        @click="toolbarSearch = ''"
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

            <div class="container pm-ops-page pm-daily-report-page">
                <header class="pm-page-heading">
                    <h1>{{ isDetailMode ? "Contractor Dashboard" : "Daily Reports" }}</h1>
                </header>

                <section class="pm-hero-card">
                    <div class="pm-hero-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M8 3h6l5 5v13H5V3h3Zm5 1.5V9h4.5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M8.5 13h7M8.5 16.5h7"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>
                    <div>
                        <h2>{{ heroTitle }}</h2>
                        <p>{{ heroSubtitle }}</p>
                    </div>
                </section>

                <section class="pm-toolbar">
                    <template v-if="!isDetailMode">
                        <button
                            class="pm-primary-btn"
                            type="button"
                            @click="startCreate"
                        >
                            <span>+</span>
                            New
                        </button>
                        <button
                            class="pm-secondary-btn"
                            type="button"
                            @click="goToList"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 7h12M8 12h12M8 17h12M4 7h.01M4 12h.01M4 17h.01" />
                            </svg>
                            List
                        </button>
                    </template>
                    <template v-else>
                        <button
                            class="pm-secondary-btn"
                            type="button"
                            @click="goToList"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 7h12M8 12h12M8 17h12M4 7h.01M4 12h.01M4 17h.01" />
                            </svg>
                            List
                        </button>
                        <button
                            class="pm-secondary-btn"
                            type="button"
                            @click="printPage"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7 8V4h10v4M6 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-1M7 14h10v6H7z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Print
                        </button>
                        <button
                            class="pm-secondary-btn"
                            type="button"
                            @click="emailReport"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 6h16v12H4zM4 8l8 5 8-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Email
                        </button>
                        <button
                            class="pm-primary-blue-btn"
                            type="button"
                            @click="openEditMode"
                        >
                            Edit Report
                        </button>
                    </template>
                </section>

                <template v-if="!isDetailMode">
                    <form class="pm-daily-form-layout" @submit.prevent="saveReport">
                        <section class="pm-section-card">
                            <div class="pm-section-title">Report Basic Information</div>
                            <div class="pm-form-grid">
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Report No</label>
                                    <input
                                        class="form-control"
                                        :value="form.report_no || generatedReportNo"
                                        readonly
                                    />
                                    <small class="pm-field-hint">Auto-generated on save</small>
                                </div>
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Date</label>
                                    <input
                                        v-model="form.report_date"
                                        type="date"
                                        class="form-control"
                                        required
                                    />
                                </div>
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Day</label>
                                    <input
                                        class="form-control"
                                        :value="reportDayLabel"
                                        readonly
                                    />
                                </div>
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Status</label>
                                    <select v-model="form.status" class="form-control">
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="approved">Approved</option>
                                        <option value="final">Final</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="pm-section-card">
                            <div class="pm-section-title with-icon">
                                <span class="pm-section-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 8a7 7 0 0 1 14 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                Employee Information
                            </div>
                            <div class="pm-form-grid">
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Employee</label>
                                    <select
                                        v-model="form.employee_code"
                                        class="form-control"
                                        @change="applyEmployeePreset"
                                    >
                                        <option value="">Select employee...</option>
                                        <option
                                            v-for="employee in employeePresets"
                                            :key="employee.code"
                                            :value="employee.code"
                                        >
                                            {{ employee.code }} - {{ employee.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Name</label>
                                    <input
                                        v-model="form.employee_name"
                                        class="form-control"
                                        type="text"
                                    />
                                </div>
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Phone</label>
                                    <input
                                        v-model="form.employee_phone"
                                        class="form-control"
                                        type="text"
                                    />
                                </div>
                                <div class="pm-form-field">
                                    <label class="pm-field-label">Email</label>
                                    <input
                                        v-model="form.employee_email"
                                        class="form-control"
                                        type="email"
                                    />
                                </div>
                            </div>
                        </section>

                        <section class="pm-section-card">
                            <div class="pm-section-head">
                                <div class="pm-section-title with-icon">
                                    <span class="pm-section-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M6 4h12v16H6zM9 2v4M15 2v4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    Work Details
                                </div>
                                <button
                                    class="pm-add-row-btn"
                                    type="button"
                                    @click="addDetailRow"
                                >
                                    + Add Row
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table pm-detail-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Time From</th>
                                            <th>Time To</th>
                                            <th>Net Time (hrs)</th>
                                            <th>Over Time (hrs)</th>
                                            <th>Customer</th>
                                            <th>Job Site / Address</th>
                                            <th>Job Order</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(row, index) in form.details"
                                            :key="row.key"
                                        >
                                            <td>
                                                <input
                                                    v-model="row.date"
                                                    type="date"
                                                    class="form-control"
                                                    @change="syncRowDay(row)"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="row.day"
                                                    class="form-control"
                                                    readonly
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="row.time_from"
                                                    type="time"
                                                    class="form-control"
                                                    @change="recalculateRow(row)"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="row.time_to"
                                                    type="time"
                                                    class="form-control"
                                                    @change="recalculateRow(row)"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="row.net_hours"
                                                    type="number"
                                                    min="0"
                                                    step="0.25"
                                                    class="form-control"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="row.overtime_hours"
                                                    type="number"
                                                    min="0"
                                                    step="0.25"
                                                    class="form-control"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="row.customer"
                                                    class="form-control"
                                                    type="text"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="row.job_site"
                                                    class="form-control"
                                                    type="text"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="row.job_order"
                                                    class="form-control"
                                                    type="text"
                                                />
                                            </td>
                                            <td>
                                                <button
                                                    class="pm-row-remove"
                                                    type="button"
                                                    :disabled="form.details.length === 1"
                                                    @click="removeDetailRow(index)"
                                                >
                                                    x
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <div class="pm-lower-grid">
                            <section class="pm-section-card">
                                <div class="pm-section-title with-icon">
                                    <span class="pm-section-icon is-orange">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 3v9l6 3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8" />
                                        </svg>
                                    </span>
                                    Overtime / Stat Holiday
                                </div>
                                <div class="pm-overtime-label">Total Overtime Hours</div>
                                <div class="pm-overtime-chip">
                                    {{ summaryTotals.overtime_hours.toFixed(1) }} hours
                                </div>
                                <div class="pm-radio-group">
                                    <div class="pm-field-label">Worked on Stat Holiday?</div>
                                    <div class="pm-radio-block">
                                        <label class="pm-radio-option">
                                            <input
                                                v-model="form.worked_on_stat_holiday"
                                                :value="true"
                                                type="radio"
                                            />
                                            Yes
                                        </label>
                                        <label class="pm-radio-option">
                                            <input
                                                v-model="form.worked_on_stat_holiday"
                                                :value="false"
                                                type="radio"
                                            />
                                            No
                                        </label>
                                    </div>
                                </div>
                            </section>

                            <section class="pm-section-card">
                                <div class="pm-section-title with-icon">
                                    <span class="pm-section-icon is-green">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M7 12.5 10 15.5 17 8.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8" />
                                        </svg>
                                    </span>
                                    Summary
                                </div>
                                <div class="pm-summary-stack">
                                    <div class="pm-summary-item is-blue">
                                        <span>Total Regular Hours</span>
                                        <strong>{{ summaryTotals.regular_hours.toFixed(1) }} hrs</strong>
                                    </div>
                                    <div class="pm-summary-item is-orange">
                                        <span>Total Overtime Hours</span>
                                        <strong>{{ summaryTotals.overtime_hours.toFixed(1) }} hrs</strong>
                                    </div>
                                    <div class="pm-summary-item is-green">
                                        <span>Total Hours Worked</span>
                                        <strong>{{ summaryTotals.total_hours.toFixed(1) }} hrs</strong>
                                    </div>
                                    <div class="pm-summary-item is-purple">
                                        <span>Total Jobs Completed</span>
                                        <strong>{{ summaryTotals.total_jobs }}</strong>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <section class="pm-section-card">
                            <div class="pm-section-title">Notes</div>
                            <textarea
                                v-model="form.report_notes"
                                class="form-control pm-notes-box"
                                rows="4"
                                placeholder="Add any additional notes, issues, or comments about the day's work..."
                            ></textarea>
                        </section>

                        <section class="pm-form-actions">
                            <button
                                class="pm-secondary-btn"
                                type="button"
                                @click="goToList"
                            >
                                Cancel
                            </button>
                            <button class="pm-primary-blue-btn" type="submit">
                                {{ saving ? "Saving..." : form.id ? "Update Report" : "Save Report" }}
                            </button>
                        </section>
                    </form>
                </template>

                <template v-else>
                    <div class="pm-detail-top-grid">
                        <section class="pm-detail-highlight">
                            <div class="pm-detail-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M8 3h6l5 5v13H5V3h3Zm5 1.5V9h4.5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="M8.5 13h7M8.5 16.5h7"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>
                            <h3>{{ form.report_no || generatedReportNo }}</h3>
                            <p>{{ formatDisplayDate(form.report_date) }}</p>
                            <span>{{ form.employee_name || "Unknown Employee" }}</span>
                            <div
                                class="pm-status-pill"
                                :class="statusClass(form.status)"
                            >
                                {{ formatStatus(form.status) }}
                            </div>
                        </section>

                        <section class="pm-section-card">
                            <div class="pm-detail-summary-title">Hours Summary</div>
                            <div class="pm-summary-grid">
                                <div class="pm-summary-item is-blue">
                                    <span>Regular Hours</span>
                                    <strong>{{ summaryTotals.regular_hours.toFixed(1) }} hrs</strong>
                                </div>
                                <div class="pm-summary-item is-orange">
                                    <span>Overtime Hours</span>
                                    <strong>{{ summaryTotals.overtime_hours.toFixed(1) }} hrs</strong>
                                </div>
                                <div class="pm-summary-item is-green">
                                    <span>Total Hours</span>
                                    <strong>{{ summaryTotals.total_hours.toFixed(1) }} hrs</strong>
                                </div>
                                <div class="pm-summary-item is-purple">
                                    <span>Total Jobs</span>
                                    <strong>{{ summaryTotals.total_jobs }}</strong>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section class="pm-section-card">
                        <div class="pm-section-title with-icon">
                            <span class="pm-section-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M8 3h6l5 5v13H5V3h3Zm5 1.5V9h4.5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                            Report Information
                        </div>
                        <div class="pm-info-grid">
                            <div class="pm-info-row">
                                <span>Report No</span>
                                <strong>{{ form.report_no || generatedReportNo }}</strong>
                            </div>
                            <div class="pm-info-row">
                                <span>Date</span>
                                <strong>{{ formatDisplayDate(form.report_date) }}</strong>
                            </div>
                            <div class="pm-info-row">
                                <span>Status</span>
                                <strong>{{ formatStatus(form.status) }}</strong>
                            </div>
                            <div class="pm-info-row">
                                <span>Stat Holiday</span>
                                <strong>{{ form.worked_on_stat_holiday ? "Yes" : "No" }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-section-card">
                        <div class="pm-section-title with-icon">
                            <span class="pm-section-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 8a7 7 0 0 1 14 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            Employee Information
                        </div>
                        <div class="pm-two-column-info">
                            <div class="pm-info-row">
                                <span>Employee ID</span>
                                <strong>{{ form.employee_code || "--" }}</strong>
                            </div>
                            <div class="pm-info-row">
                                <span>Phone</span>
                                <strong>{{ form.employee_phone || "--" }}</strong>
                            </div>
                            <div class="pm-info-row">
                                <span>Employee Name</span>
                                <strong>{{ form.employee_name || "--" }}</strong>
                            </div>
                            <div class="pm-info-row">
                                <span>Email</span>
                                <strong>{{ form.employee_email || "--" }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-section-card">
                        <div class="pm-section-title">Notes</div>
                        <p class="pm-detail-notes">
                            {{ form.report_notes || "No notes added." }}
                        </p>
                    </section>
                </template>
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
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
import dailyReportsService from "../api/dailyReports";
import { clearToken, logout as apiLogout } from "../api/auth";
import { authState } from "../store/auth";

const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const userName = ref("John Doe");
const toolbarSearch = ref("");
const saving = ref(false);
const loading = ref(false);

const employeePresets = [
    { code: "EMP-001", name: "John Smith", phone: "(416) 555-0101", email: "john@company.com" },
    { code: "EMP-002", name: "Sarah Johnson", phone: "(416) 555-0112", email: "sarah@company.com" },
    { code: "EMP-003", name: "Mike Peters", phone: "(905) 555-0144", email: "mike@company.com" },
    { code: "EMP-004", name: "Emily Carter", phone: "(416) 555-0188", email: "emily@company.com" },
];

const form = reactive(createEmptyForm());

const userInitials = computed(() => {
    const value = userName.value || "User";
    return value
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || "")
        .join("");
});

const isDetailMode = computed(() => route.query.view === "detail");

const heroTitle = computed(() =>
    isDetailMode.value ? "Daily Report Details" : "Daily Work Report - Entry",
);

const heroSubtitle = computed(() =>
    isDetailMode.value
        ? "View daily work report information"
        : "Track daily work activities and hours",
);

const generatedReportNo = computed(() => {
    const year = new Date().getFullYear();
    return `DR-${year}-NEW`;
});

const reportDayLabel = computed(() => {
    if (!form.report_date) return "";
    return new Date(`${form.report_date}T00:00:00`).toLocaleDateString(
        undefined,
        { weekday: "long" },
    );
});

const summaryTotals = computed(() => {
    const regular = form.details.reduce(
        (sum, row) => sum + Number(row.net_hours || 0),
        0,
    );
    const overtime = form.details.reduce(
        (sum, row) => sum + Number(row.overtime_hours || 0),
        0,
    );

    return {
        regular_hours: regular,
        overtime_hours: overtime,
        total_hours: regular + overtime,
        total_jobs: form.details.filter(
            (row) => row.customer || row.job_site || row.job_order,
        ).length,
    };
});

function createDetailRow(seed = {}, defaultDate = "") {
    return {
        key: `${Date.now()}-${Math.random()}`,
        date: seed.date || defaultDate || "",
        day: seed.day || "",
        time_from: seed.time_from || "",
        time_to: seed.time_to || "",
        net_hours: Number(seed.net_hours || 0),
        overtime_hours: Number(seed.overtime_hours || 0),
        customer: seed.customer || "",
        job_site: seed.job_site || "",
        job_order: seed.job_order || "",
    };
}

function createEmptyForm() {
    return {
        id: null,
        report_no: "",
        report_date: "",
        shift: "",
        shift_name: "",
        shift_time: "",
        report_location: "",
        status: "draft",
        summary: "",
        report_notes: "",
        employee_name: "",
        employee_code: "",
        employee_phone: "",
        employee_email: "",
        licence_no: "",
        expire_date: "",
        is_valid: true,
        worked_on_stat_holiday: false,
        details: [createDetailRow()],
    };
}

function resetForm() {
    Object.assign(form, createEmptyForm());
}

function applyEmployeePreset() {
    const selected = employeePresets.find(
        (employee) => employee.code === form.employee_code,
    );
    if (!selected) return;
    form.employee_name = selected.name;
    form.employee_phone = selected.phone;
    form.employee_email = selected.email;
}

function syncRowDay(row) {
    row.day = row.date
        ? new Date(`${row.date}T00:00:00`).toLocaleDateString(undefined, {
              weekday: "long",
          })
        : "";
}

function recalculateRow(row) {
    syncRowDay(row);
    if (!row.time_from || !row.time_to) return;

    const [fromH, fromM] = row.time_from.split(":").map(Number);
    const [toH, toM] = row.time_to.split(":").map(Number);
    let minutes = toH * 60 + toM - (fromH * 60 + fromM);
    if (minutes < 0) minutes += 24 * 60;
    const hours = Math.max(0, minutes / 60);
    row.net_hours = Number(hours.toFixed(2));
    row.overtime_hours = Number(Math.max(0, hours - 8).toFixed(2));
}

function addDetailRow() {
    form.details.push(createDetailRow({ date: form.report_date }, form.report_date));
}

function removeDetailRow(index) {
    if (form.details.length === 1) return;
    form.details.splice(index, 1);
}

function normalizeReport(report) {
    return {
        id: report?.id || null,
        report_no: report?.report_no || "",
        report_date: report?.report_date || "",
        shift: report?.shift || "",
        shift_name: report?.shift_name || "",
        shift_time: report?.shift_time || "",
        report_location: report?.report_location || "",
        status: report?.status || "draft",
        summary: report?.summary || "",
        report_notes: report?.report_notes || "",
        employee_name: report?.employee_name || "",
        employee_code: report?.employee_code || "",
        employee_phone: report?.employee_phone || "",
        employee_email: report?.employee_email || "",
        licence_no: report?.licence_no || "",
        expire_date: report?.expire_date || "",
        is_valid: report?.is_valid ?? true,
        worked_on_stat_holiday: report?.worked_on_stat_holiday ?? false,
        details: Array.isArray(report?.details_json) && report.details_json.length
            ? report.details_json.map((row) => {
                  const detail = createDetailRow(row, report?.report_date || "");
                  syncRowDay(detail);
                  return detail;
              })
            : [createDetailRow({ date: report?.report_date || "" }, report?.report_date || "")],
    };
}

async function loadReport(id) {
    loading.value = true;
    try {
        const { data } = await dailyReportsService.getItem(id);
        Object.assign(form, normalizeReport(data?.data || {}));
    } finally {
        loading.value = false;
    }
}

function buildPayload() {
    const details = form.details.map((row) => ({
        date: row.date || null,
        day: row.day || null,
        time_from: row.time_from || null,
        time_to: row.time_to || null,
        net_hours: Number(row.net_hours || 0),
        overtime_hours: Number(row.overtime_hours || 0),
        customer: row.customer || null,
        job_site: row.job_site || null,
        job_order: row.job_order || null,
    }));

    return {
        report_date: form.report_date,
        shift: reportDayLabel.value.toLowerCase(),
        shift_name: reportDayLabel.value,
        shift_time: form.details[0]?.time_from || null,
        report_location: form.details[0]?.job_site || null,
        status: form.status,
        summary: form.summary || `Daily work report for ${form.employee_name || "employee"}`,
        report_notes: form.report_notes || null,
        employee_name: form.employee_name || null,
        employee_code: form.employee_code || null,
        employee_phone: form.employee_phone || null,
        employee_email: form.employee_email || null,
        licence_no: form.licence_no || null,
        expire_date: form.expire_date || null,
        is_valid: form.is_valid,
        worked_on_stat_holiday: !!form.worked_on_stat_holiday,
        details_json: details,
        metrics_json: {
            regular_hours: Number(summaryTotals.value.regular_hours.toFixed(2)),
            overtime_hours: Number(summaryTotals.value.overtime_hours.toFixed(2)),
            total_hours: Number(summaryTotals.value.total_hours.toFixed(2)),
            total_jobs: summaryTotals.value.total_jobs,
        },
    };
}

async function saveReport() {
    saving.value = true;
    try {
        const payload = buildPayload();
        const response = form.id
            ? await dailyReportsService.updateItem(form.id, payload)
            : await dailyReportsService.createItem(payload);
        const saved = response?.data?.data;
        await router.push({
            name: "workforce-daily-report",
            query: { report: String(saved?.id || form.id), view: "detail" },
        });
    } finally {
        saving.value = false;
    }
}

function startCreate() {
    resetForm();
    router.push({ name: "workforce-daily-report", query: { mode: "create" } });
}

function openEditMode() {
    router.push({
        name: "workforce-daily-report",
        query: { report: String(form.id) },
    });
}

function goToList() {
    router.push("/workforce/daily-reports");
}

function printPage() {
    window.print();
}

function emailReport() {
    if (!form.employee_email) return;
    window.location.href = `mailto:${form.employee_email}?subject=${encodeURIComponent(form.report_no || "Daily Report")}`;
}

function formatDisplayDate(value) {
    if (!value) return "--";
    const date = new Date(`${value}T00:00:00`);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString(undefined, {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
}

function formatStatus(status) {
    const value = String(status || "draft");
    return value.charAt(0).toUpperCase() + value.slice(1);
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

async function syncFromRoute() {
    const reportId = Number(route.query.report || 0);
    if (reportId) {
        await loadReport(reportId);
        return;
    }
    resetForm();
}

watch(
    () => route.query,
    () => {
        syncFromRoute();
    },
    { deep: true },
);

watch(
    () => form.report_date,
    (value) => {
        form.details.forEach((row, index) => {
            if (!row.date && index === 0) {
                row.date = value;
                syncRowDay(row);
            }
        });
    },
);

onMounted(async () => {
    if (authState.user?.name) {
        userName.value = authState.user.name;
    }
    document.addEventListener("click", handleClickOutside);
    await syncFromRoute();
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.pm-daily-report-page {
    padding-bottom: 2rem;
}

.pm-page-heading {
    margin: 1rem 0 1.25rem;
}

.pm-page-heading h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: #1f2937;
}

.pm-hero-card {
    margin-bottom: 1rem;
    padding: 1.85rem 1.6rem;
    border-radius: 18px;
    background: linear-gradient(90deg, #2563eb 0%, #54a2f6 100%);
    color: #fff;
    box-shadow: 0 18px 40px rgba(37, 99, 235, 0.18);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pm-hero-icon,
.pm-detail-icon,
.pm-section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.pm-hero-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.16);
    flex: 0 0 auto;
}

.pm-hero-icon svg,
.pm-detail-icon svg,
.pm-section-icon svg {
    width: 22px;
    height: 22px;
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

.pm-toolbar {
    margin-bottom: 1.25rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.pm-primary-btn,
.pm-secondary-btn,
.pm-primary-blue-btn {
    border-radius: 12px;
    padding: 0.78rem 1rem;
    border: 1px solid #d7deea;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    background: #fff;
    color: #111827;
}

.pm-primary-btn {
    background: #10b341;
    border-color: #10b341;
    color: #fff;
}

.pm-primary-blue-btn {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-secondary-btn svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.9;
    stroke-linecap: round;
}

.pm-daily-form-layout {
    display: grid;
    gap: 1.25rem;
}

.pm-section-card {
    background: #fff;
    border: 1px solid #dde4ef;
    border-radius: 18px;
    padding: 1.25rem;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.05);
}

.pm-section-title {
    margin-bottom: 1rem;
    font-size: 1.3rem;
    font-weight: 800;
    color: #1f2937;
}

.pm-section-head {
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.pm-section-title.with-icon {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.pm-section-icon {
    width: 24px;
    height: 24px;
    border-radius: 999px;
    color: #2563eb;
    background: #e7f0ff;
}

.pm-section-icon.is-orange {
    background: #fff0e1;
    color: #f97316;
}

.pm-section-icon.is-green {
    background: #e8faef;
    color: #16a34a;
}

.pm-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.pm-form-field {
    display: grid;
    gap: 0.45rem;
}

.pm-field-label {
    font-size: 0.95rem;
    font-weight: 700;
    color: #334155;
}

.pm-field-hint {
    color: #94a3b8;
    font-size: 0.85rem;
}

.pm-add-row-btn {
    border: 0;
    border-radius: 12px;
    padding: 0.72rem 1rem;
    background: #2563eb;
    color: #fff;
    font-weight: 700;
}

.pm-detail-table {
    margin: 0;
}

.pm-detail-table thead th {
    background: #fff;
    border-bottom: 1px solid #e8edf5;
    white-space: nowrap;
    padding: 0.95rem 0.75rem;
    color: #1f2937;
}

.pm-detail-table td {
    padding: 0.85rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #eef2f7;
}

.pm-row-remove {
    border: 0;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #fee2e2;
    color: #dc2626;
    font-weight: 800;
}

.pm-lower-grid,
.pm-detail-top-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

.pm-overtime-label {
    margin-bottom: 0.7rem;
    color: #334155;
    font-weight: 700;
}

.pm-overtime-chip {
    display: inline-flex;
    margin: 0 0 1.25rem;
    padding: 0.7rem 1rem;
    border-radius: 12px;
    background: #ff6b0f;
    color: #fff;
    font-weight: 800;
}

.pm-radio-group {
    display: grid;
    gap: 0.75rem;
}

.pm-radio-block {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.pm-radio-option {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 600;
}

.pm-summary-stack,
.pm-summary-grid {
    display: grid;
    gap: 0.85rem;
}

.pm-summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.pm-summary-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 14px;
}

.pm-summary-item span {
    color: #475569;
    font-weight: 600;
}

.pm-summary-item strong {
    font-size: 1.1rem;
}

.pm-summary-item.is-blue {
    background: #eff6ff;
    color: #2563eb;
}

.pm-summary-item.is-orange {
    background: #fff7ed;
    color: #ea580c;
}

.pm-summary-item.is-green {
    background: #ecfdf5;
    color: #16a34a;
}

.pm-summary-item.is-purple {
    background: #f5f3ff;
    color: #9333ea;
}

.pm-notes-box {
    min-height: 110px;
}

.pm-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.pm-detail-highlight {
    background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe;
    border-radius: 18px;
    padding: 1.6rem;
    display: grid;
    justify-items: center;
    text-align: center;
    gap: 0.45rem;
}

.pm-detail-icon {
    width: 88px;
    height: 88px;
    border-radius: 999px;
    background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
    color: #fff;
}

.pm-detail-highlight h3 {
    margin: 0.5rem 0 0;
    font-size: 2rem;
    color: #1e3a8a;
}

.pm-detail-highlight p,
.pm-detail-highlight span {
    margin: 0;
    color: #2563eb;
}

.pm-detail-summary-title {
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    color: #2563eb;
    font-size: 1.5rem;
    font-weight: 800;
}

.pm-info-grid,
.pm-two-column-info {
    display: grid;
    gap: 0;
}

.pm-two-column-info {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 1.5rem;
}

.pm-info-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.pm-info-row span {
    color: #475569;
    font-weight: 700;
}

.pm-info-row strong {
    color: #111827;
}

.pm-detail-notes {
    margin: 0;
    color: #334155;
    white-space: pre-wrap;
}

.pm-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.8rem;
    border-radius: 10px;
    font-size: 0.84rem;
    font-weight: 700;
}

.pm-status-pill.is-draft {
    background: #e5e7eb;
    color: #374151;
}

.pm-status-pill.is-submitted {
    background: #dbeafe;
    color: #2563eb;
}

.pm-status-pill.is-approved {
    background: #dcfce7;
    color: #16a34a;
}

.pm-status-pill.is-rejected {
    background: #fee2e2;
    color: #dc2626;
}

@media (max-width: 1200px) {
    .pm-detail-table {
        min-width: 1150px;
    }
}

@media (max-width: 991px) {
    .pm-form-grid,
    .pm-lower-grid,
    .pm-detail-top-grid,
    .pm-summary-grid,
    .pm-two-column-info {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767px) {
    .pm-hero-card {
        align-items: flex-start;
    }

    .pm-hero-card h2,
    .pm-page-heading h1 {
        font-size: 1.65rem;
    }

    .pm-section-head,
    .pm-form-actions,
    .pm-toolbar,
    .pm-info-row {
        display: grid;
        grid-template-columns: 1fr;
    }
}
</style>
