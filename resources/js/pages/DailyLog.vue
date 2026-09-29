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
                        placeholder="Search daily logs..."
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
                        <span class="pm-topbar-badge"></span>
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

            <div class="container pm-ops-page pm-daily-vehicle-page">
                <header class="pm-page-heading pm-page-heading--with-actions">
                    <h1>{{ pageTitle }}</h1>
                    <div class="pm-daily-page-toolbar">
                        <button
                            class="pm-view-toggle"
                            type="button"
                            :class="{ active: viewMode === 'list' }"
                            @click="openListView"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"
                                />
                            </svg>
                            List
                        </button>
                        <button
                            class="pm-daily-primary-btn"
                            type="button"
                            @click="startCreate"
                        >
                            <span>+</span>
                            New
                        </button>
                    </div>
                </header>

                <section class="pm-daily-vehicle-hero">
                    <div>
                        <h2>{{ heroTitle }}</h2>
                        <p>{{ heroSubtitle }}</p>
                    </div>
                </section>

                <section class="pm-daily-vehicle-card pm-daily-toolbar-card">
                    <div>
                        <h3>Daily Activity Log</h3>
                        <p>Today's Date: {{ formattedSelectedDate }}</p>
                    </div>
                    <div class="pm-daily-toolbar-actions">
                        <input
                            v-model="selectedDate"
                            type="date"
                            class="form-control"
                        />
                    </div>
                </section>

                <section class="pm-daily-stats-grid">
                    <div class="pm-daily-stat-card is-blue">
                        <span>Vehicles Active Today</span>
                        <strong>{{ activeVehicleCount }}</strong>
                        <small>Out of {{ vehicleOptions.length }} total</small>
                    </div>
                    <div class="pm-daily-stat-card is-green">
                        <span>Total Miles Today</span>
                        <strong>{{ totalMilesDisplay }}</strong>
                        <small>Across all vehicles</small>
                    </div>
                    <div class="pm-daily-stat-card is-orange">
                        <span>Fuel Used Today</span>
                        <strong>{{ totalFuelDisplay }}</strong>
                        <small>Tracked fuel entries</small>
                    </div>
                    <div class="pm-daily-stat-card is-purple">
                        <span>Avg. Trip Duration</span>
                        <strong>{{ averageDurationDisplay }}</strong>
                        <small>Per completed trip</small>
                    </div>
                </section>

                <section
                    v-if="viewMode === 'list'"
                    class="pm-daily-vehicle-card pm-daily-table-card"
                >
                    <div class="pm-daily-table-head">
                        <h3>Today's Activity Log</h3>
                        <div class="pm-daily-table-filters">
                            <input
                                v-model.trim="listSearchQuery"
                                type="search"
                                class="form-control"
                                placeholder="Search vehicle, driver, location..."
                            />
                            <select v-model="filterStatus" class="form-control">
                                <option value="">All Status</option>
                                <option
                                    v-for="item in statusOptions"
                                    :key="item.value"
                                    :value="String(item.value)"
                                >
                                    {{ item.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table
                            class="table pm-daily-vehicle-table align-middle"
                        >
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Start Odo</th>
                                    <th>End Odo</th>
                                    <th>Miles</th>
                                    <th>Fuel Added</th>
                                    <th>Purpose/Location</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in filteredItems"
                                    :key="item.id"
                                >
                                    <td>{{ item.vehicle_number || "--" }}</td>
                                    <td>{{ item.driver_name || "--" }}</td>
                                    <td>{{ formatTime(item.start_time) }}</td>
                                    <td>{{ formatEndTime(item) }}</td>
                                    <td>
                                        {{ formatNumber(item.start_odometer) }}
                                    </td>
                                    <td>
                                        {{ formatNumber(item.end_odometer) }}
                                    </td>
                                    <td class="pm-mile-value">
                                        {{ item.miles_driven ?? "-" }}
                                    </td>
                                    <td>{{ formatFuel(item.fuel_added) }}</td>
                                    <td>{{ item.purpose_location || "--" }}</td>
                                    <td>
                                        <span
                                            class="pm-status-pill"
                                            :class="statusClass(item.status)"
                                        >
                                            {{ item.status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="pm-table-actions">
                                            <button
                                                class="pm-row-link"
                                                type="button"
                                                @click="openDetailView(item)"
                                            >
                                                View
                                            </button>
                                            <button
                                                class="pm-row-link"
                                                type="button"
                                                @click="startEdit(item)"
                                            >
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!filteredItems.length">
                                    <td colspan="11" class="text-center py-4">
                                        No daily logs found for this date.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div
                    v-else-if="viewMode === 'detail' && detailItem"
                    class="pm-daily-detail-layout"
                >
                    <section class="pm-daily-vehicle-card pm-daily-detail-card">
                        <div class="pm-section-header">Log Overview</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Log ID</span>
                                <strong>{{
                                    detailItem.log_code || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Date</span>
                                <strong>{{
                                    formatLongDate(detailItem.log_date)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Vehicle</span>
                                <strong>{{
                                    detailItem.vehicle_make_model ||
                                    detailItem.vehicle_number ||
                                    "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Driver</span>
                                <strong>{{
                                    detailItem.driver_name || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Start Time</span>
                                <strong>{{
                                    formatTime(detailItem.start_time)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>End Time</span>
                                <strong>{{ formatEndTime(detailItem) }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Start Odometer</span>
                                <strong>{{
                                    formatNumber(detailItem.start_odometer)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>End Odometer</span>
                                <strong>{{
                                    formatNumber(detailItem.end_odometer)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Miles Driven</span>
                                <strong>{{
                                    detailItem.miles_driven ?? "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Fuel Added</span>
                                <strong>{{
                                    formatFuel(detailItem.fuel_added)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Status</span>
                                <strong>{{
                                    detailItem.status_label || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Purpose/Location</span>
                                <strong>{{
                                    detailItem.purpose_location || "--"
                                }}</strong>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="detailItem.notes"
                        class="pm-daily-vehicle-card pm-daily-detail-card"
                    >
                        <div class="pm-section-header">Notes</div>
                        <p class="pm-detail-notes">{{ detailItem.notes }}</p>
                    </section>

                    <section class="pm-daily-vehicle-card pm-daily-action-bar">
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            @click="openListView"
                        >
                            Back to List
                        </button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            @click="startEdit(detailItem)"
                        >
                            Edit Log
                        </button>
                    </section>
                </div>

                <div v-else class="pm-daily-form-layout">
                    <section class="pm-daily-vehicle-card pm-daily-form-card">
                        <div class="pm-section-header">Log Entry</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">Log ID</label>
                                <input
                                    class="form-control"
                                    :value="form.log_code || generatedLogCode"
                                    readonly
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Log Date
                                    <span class="pm-required-star">*</span>
                                </label>
                                <input
                                    v-model="form.log_date"
                                    type="date"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Vehicle
                                    <span class="pm-required-star">*</span>
                                </label>
                                <select
                                    v-model="form.vehicle_id"
                                    class="form-control"
                                >
                                    <option value="">Select vehicle</option>
                                    <option
                                        v-for="item in vehicleOptions"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.vehicle_number }}
                                    </option>
                                </select>
                                <div
                                    v-if="errors.vehicle_id"
                                    class="pm-form-error"
                                >
                                    {{ errors.vehicle_id }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Driver
                                    <span class="pm-required-star">*</span>
                                </label>
                                <select
                                    v-model="form.driver_id"
                                    class="form-control"
                                >
                                    <option value="">Select driver</option>
                                    <option
                                        v-for="item in driverOptions"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.driver_name }}
                                    </option>
                                </select>
                                <div
                                    v-if="errors.driver_id"
                                    class="pm-form-error"
                                >
                                    {{ errors.driver_id }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Start Time
                                    <span class="pm-required-star">*</span>
                                </label>
                                <input
                                    v-model="form.start_time"
                                    type="time"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">End Time</label>
                                <input
                                    v-model="form.end_time"
                                    type="time"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Start Odometer
                                    <span class="pm-required-star">*</span>
                                </label>
                                <input
                                    v-model="form.start_odometer"
                                    type="number"
                                    min="0"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label"
                                    >End Odometer</label
                                >
                                <input
                                    v-model="form.end_odometer"
                                    type="number"
                                    min="0"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label"
                                    >Miles Driven</label
                                >
                                <input
                                    class="form-control"
                                    :value="calculatedMiles"
                                    placeholder="Auto-calculated"
                                    readonly
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Fuel Added</label>
                                <input
                                    v-model="form.fuel_added"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-8">
                                <label class="pm-field-label"
                                    >Purpose/Location</label
                                >
                                <input
                                    v-model="form.purpose_location"
                                    class="form-control"
                                    placeholder="Job Site - Downtown Project"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label">Status</label>
                                <select
                                    v-model="form.status"
                                    class="form-control"
                                >
                                    <option
                                        v-for="item in statusOptions"
                                        :key="item.value"
                                        :value="String(item.value)"
                                    >
                                        {{ item.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="pm-field-label">Notes</label>
                                <textarea
                                    v-model="form.notes"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Add any additional notes about the daily log..."
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <section
                        class="pm-daily-vehicle-card pm-daily-form-actions"
                    >
                        <button
                            class="btn btn-success"
                            type="button"
                            @click="startCreate"
                        >
                            + New
                        </button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            :disabled="saving"
                            @click="saveItem"
                        >
                            {{ saving ? "Saving..." : "Save Log" }}
                        </button>
                        <button
                            class="btn btn-danger"
                            type="button"
                            :disabled="!activeId"
                            @click="deleteCurrent"
                        >
                            Delete
                        </button>
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            @click="printCurrent"
                        >
                            Print
                        </button>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
import dailyLogService from "../api/dailyLog";
import driverService from "../api/driver";
import vehicleService from "../api/vehicle";
import { clearToken, logout as apiLogout } from "../api/auth";
import { authState } from "../store/auth";

const router = useRouter();

const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const searchQuery = ref("");
const userName = ref("John Doe");

const viewMode = ref("list");
const saving = ref(false);
const items = ref([]);
const detailItem = ref(null);
const activeId = ref(null);
const listSearchQuery = ref("");
const filterStatus = ref("");
const selectedDate = ref(new Date().toISOString().slice(0, 10));
const errors = reactive({});

const statusOptions = [
    { value: 1, label: "Active" },
    { value: 2, label: "Completed" },
    { value: 3, label: "Cancelled" },
];

const vehicleOptions = ref([]);
const driverOptions = ref([]);

const form = reactive({
    id: "",
    log_code: "",
    log_date: "",
    vehicle_id: "",
    driver_id: "",
    start_time: "",
    end_time: "",
    start_odometer: "",
    end_odometer: "",
    fuel_added: "",
    purpose_location: "",
    status: "1",
    notes: "",
});

const pageTitle = computed(() => {
    if (viewMode.value === "detail") return "Daily Log Details";
    if (viewMode.value === "form") {
        return activeId.value ? "Edit Daily Log" : "Daily Log Entry";
    }
    return "Daily Log";
});

const heroTitle = computed(() => {
    if (viewMode.value === "detail") return "Daily Log Details";
    if (viewMode.value === "form") {
        return activeId.value ? "Update Daily Log" : "Daily Log";
    }
    return "Daily Log";
});

const heroSubtitle = computed(() =>
    viewMode.value === "form"
        ? "Track daily vehicle usage, mileage, and activities"
        : "Track daily vehicle usage, mileage, and activities",
);

const userInitials = computed(() => {
    const value = userName.value || "User";
    return value
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || "")
        .join("");
});

const formattedSelectedDate = computed(() =>
    formatLongDate(selectedDate.value),
);

const generatedLogCode = computed(() => {
    const year = new Date().getFullYear();
    return `DL-${year}-${String(items.value.length + 1).padStart(3, "0")}`;
});

const calculatedMiles = computed(() => {
    if (form.start_odometer === "" || form.end_odometer === "") return "";
    const miles = Number(form.end_odometer) - Number(form.start_odometer);
    return miles >= 0 ? String(miles) : "";
});

const filteredItems = computed(() => {
    const search = listSearchQuery.value.trim().toLowerCase();

    return items.value.filter((item) => {
        const matchesDate = item.log_date === selectedDate.value;
        const matchesStatus =
            !filterStatus.value || String(item.status) === filterStatus.value;

        if (!matchesDate || !matchesStatus) return false;
        if (!search) return true;

        return [
            item.vehicle_number,
            item.driver_name,
            item.purpose_location,
            item.status_label,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(search));
    });
});

const activeVehicleCount = computed(
    () =>
        new Set(
            filteredItems.value
                .filter(
                    (item) =>
                        Number(item.status) === 1 || Number(item.status) === 2,
                )
                .map((item) => item.vehicle_id),
        ).size,
);

const totalMiles = computed(() =>
    filteredItems.value.reduce(
        (sum, item) => sum + Number(item.miles_driven || 0),
        0,
    ),
);

const totalFuel = computed(() =>
    filteredItems.value.reduce(
        (sum, item) => sum + Number(item.fuel_added || 0),
        0,
    ),
);

const averageDurationHours = computed(() => {
    const completed = filteredItems.value.filter(
        (item) => item.start_time && item.end_time,
    );
    if (!completed.length) return 0;

    const totalMinutes = completed.reduce((sum, item) => {
        const [startH, startM] = item.start_time.split(":").map(Number);
        const [endH, endM] = item.end_time.split(":").map(Number);
        return sum + Math.max(0, endH * 60 + endM - (startH * 60 + startM));
    }, 0);

    return totalMinutes / 60 / completed.length;
});

const totalMilesDisplay = computed(() => formatNumber(totalMiles.value));
const totalFuelDisplay = computed(
    () =>
        `${new Intl.NumberFormat().format(Number(totalFuel.value.toFixed(0)))} gal`,
);
const averageDurationDisplay = computed(
    () => `${averageDurationHours.value.toFixed(1)} hrs`,
);

watch(selectedDate, () => {
    if (viewMode.value === "form" && !activeId.value) {
        form.log_date = selectedDate.value;
    }
});

function clearErrors() {
    Object.keys(errors).forEach((key) => delete errors[key]);
}

function statusClass(status) {
    return {
        "is-active": Number(status) === 1,
        "is-completed": Number(status) === 2,
        "is-cancelled": Number(status) === 3,
    };
}

function resetForm() {
    clearErrors();
    activeId.value = null;
    detailItem.value = null;
    Object.assign(form, {
        id: "",
        log_code: "",
        log_date: selectedDate.value,
        vehicle_id: "",
        driver_id: "",
        start_time: "",
        end_time: "",
        start_odometer: "",
        end_odometer: "",
        fuel_added: "",
        purpose_location: "",
        status: "1",
        notes: "",
    });
}

function applyItemToForm(item) {
    Object.assign(form, {
        id: item.id || "",
        log_code: item.log_code || "",
        log_date: item.log_date || selectedDate.value,
        vehicle_id: item.vehicle_id ? String(item.vehicle_id) : "",
        driver_id: item.driver_id ? String(item.driver_id) : "",
        start_time: item.start_time || "",
        end_time: item.end_time || "",
        start_odometer:
            item.start_odometer !== null ? String(item.start_odometer) : "",
        end_odometer:
            item.end_odometer !== null ? String(item.end_odometer) : "",
        fuel_added:
            item.fuel_added !== null && item.fuel_added !== undefined
                ? String(item.fuel_added)
                : "",
        purpose_location: item.purpose_location || "",
        status: item.status ? String(item.status) : "1",
        notes: item.notes || "",
    });
}

function buildPayload() {
    return {
        log_date: form.log_date,
        vehicle_id: Number(form.vehicle_id),
        driver_id: Number(form.driver_id),
        start_time: form.start_time,
        end_time: form.end_time || null,
        start_odometer: Number(form.start_odometer),
        end_odometer:
            form.end_odometer !== "" ? Number(form.end_odometer) : null,
        fuel_added: form.fuel_added !== "" ? Number(form.fuel_added) : null,
        purpose_location: form.purpose_location || null,
        status: Number(form.status || 1),
        notes: form.notes || null,
    };
}

function assignValidationErrors(error) {
    clearErrors();
    const serverErrors = error?.response?.data?.errors || {};
    Object.entries(serverErrors).forEach(([key, value]) => {
        errors[key] = Array.isArray(value) ? value[0] : value;
    });
}

async function fetchItems() {
    const { data } = await dailyLogService.getItems({ per_page: 200 });
    items.value = Array.isArray(data?.data?.data) ? data.data.data : [];
}

async function fetchVehicles() {
    const { data } = await vehicleService.getItems({ per_page: 200 });
    const records = Array.isArray(data?.data?.data) ? data.data.data : [];
    vehicleOptions.value = records.map((item) => ({
        id: item.id,
        vehicle_number: item.vehicle_number,
    }));
}

async function fetchDrivers() {
    const { data } = await driverService.getItems({ per_page: 200 });
    const records = Array.isArray(data?.data?.data) ? data.data.data : [];
    driverOptions.value = records.map((item) => ({
        id: item.id,
        driver_name: item.driver_name,
    }));
}

async function loadDetail(id) {
    const { data } = await dailyLogService.getItem(id);
    detailItem.value = data?.data || null;
    return detailItem.value;
}

function openListView() {
    viewMode.value = "list";
    detailItem.value = null;
}

function openFormView() {
    viewMode.value = "form";
    if (!activeId.value) resetForm();
}

function startCreate() {
    resetForm();
    openFormView();
}

async function openDetailView(item) {
    const id = item?.id || activeId.value;
    if (!id) return;
    const detail = await loadDetail(id);
    if (!detail) return;
    activeId.value = detail.id;
    viewMode.value = "detail";
}

async function startEdit(item) {
    const id = item?.id || activeId.value;
    if (!id) return;
    const { data } = await dailyLogService.getItemForEdit(id);
    const editableItem = data?.data || item;
    activeId.value = editableItem.id;
    applyItemToForm(editableItem);
    viewMode.value = "form";
}

async function saveItem() {
    saving.value = true;
    clearErrors();

    try {
        const payload = buildPayload();
        let response;

        if (activeId.value) {
            response = await dailyLogService.updateItem(
                activeId.value,
                payload,
            );
        } else {
            response = await dailyLogService.createItem(payload);
        }

        const saved = response?.data?.data;
        await fetchItems();
        activeId.value = saved?.id || activeId.value;

        if (saved?.id) {
            await openDetailView(saved);
        } else {
            openListView();
        }
    } catch (error) {
        assignValidationErrors(error);
    } finally {
        saving.value = false;
    }
}

async function deleteItem(item) {
    if (!item?.id) return;
    if (!window.confirm(`Delete daily log ${item.log_code || ""}?`)) return;
    await dailyLogService.deleteItem(item.id);

    if (activeId.value === item.id) {
        resetForm();
        openListView();
    }

    await fetchItems();
}

async function deleteCurrent() {
    if (!activeId.value) return;
    await deleteItem({
        id: activeId.value,
        log_code: form.log_code || detailItem.value?.log_code,
    });
}

function printCurrent() {
    window.print();
}

function formatNumber(value) {
    if (value === null || value === undefined || value === "") return "--";
    return new Intl.NumberFormat().format(Number(value));
}

function formatFuel(value) {
    if (value === null || value === undefined || value === "") return "-";
    return `${Number(value)} gal`;
}

function formatTime(value) {
    if (!value) return "--";
    const [hours, minutes] = value.split(":").map(Number);
    const suffix = hours >= 12 ? "PM" : "AM";
    const adjustedHours = hours % 12 || 12;
    return `${String(adjustedHours).padStart(2, "0")}:${String(minutes).padStart(2, "0")} ${suffix}`;
}

function formatEndTime(item) {
    if (!item.end_time) return Number(item.status) === 1 ? "In Progress" : "-";
    return formatTime(item.end_time);
}

function formatLongDate(value) {
    if (!value) return "--";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString(undefined, {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
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
    resetForm();
    await Promise.all([fetchItems(), fetchVehicles(), fetchDrivers()]);
    openListView();
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.pm-daily-vehicle-page {
    padding-bottom: 2rem;
}

.pm-page-heading {
    margin: 1rem 0 1.25rem;
}

.pm-page-heading--with-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.pm-page-heading h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
}

.pm-daily-page-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.pm-view-toggle,
.pm-daily-primary-btn {
    border: 1px solid #d7dbe7;
    border-radius: 12px;
    padding: 0.78rem 1rem;
    background: #fff;
    color: #1f2937;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
}

.pm-view-toggle svg {
    width: 18px;
    height: 18px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
}

.pm-view-toggle.active,
.pm-daily-primary-btn {
    background: #18a8a0;
    border-color: #18a8a0;
    color: #fff;
}

.pm-daily-vehicle-hero {
    margin-bottom: 1rem;
    padding: 1.7rem 1.8rem;
    border-radius: 18px;
    background: linear-gradient(120deg, #109d90 0%, #1ec7bb 100%);
    color: #fff;
    box-shadow: 0 20px 45px rgba(16, 157, 144, 0.2);
}

.pm-daily-vehicle-hero h2 {
    margin: 0;
    font-size: 1.9rem;
    font-weight: 700;
}

.pm-daily-vehicle-hero p {
    margin: 0.45rem 0 0;
    color: rgba(255, 255, 255, 0.9);
}

.pm-daily-vehicle-card {
    background: #fff;
    border: 1px solid #dce3ef;
    border-radius: 18px;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.05);
}

.pm-daily-toolbar-card,
.pm-daily-form-actions,
.pm-daily-action-bar {
    padding: 1.3rem 1.4rem;
}

.pm-daily-toolbar-card {
    margin-bottom: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.pm-daily-toolbar-card h3,
.pm-daily-table-head h3 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 700;
    color: #1f2937;
}

.pm-daily-toolbar-card p {
    margin: 0.3rem 0 0;
    color: #64748b;
}

.pm-daily-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.pm-daily-stats-grid {
    margin-bottom: 1.25rem;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.pm-daily-stat-card {
    border-radius: 18px;
    padding: 1.2rem;
    color: #fff;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
}

.pm-daily-stat-card span,
.pm-daily-stat-card small {
    display: block;
}

.pm-daily-stat-card strong {
    display: block;
    margin: 0.45rem 0;
    font-size: 2rem;
    line-height: 1;
}

.pm-daily-stat-card small {
    opacity: 0.85;
}

.pm-daily-stat-card.is-blue {
    background: linear-gradient(120deg, #2563eb 0%, #3b82f6 100%);
}

.pm-daily-stat-card.is-green {
    background: linear-gradient(120deg, #0dbb3f 0%, #16a34a 100%);
}

.pm-daily-stat-card.is-orange {
    background: linear-gradient(120deg, #ff6a00 0%, #f97316 100%);
}

.pm-daily-stat-card.is-purple {
    background: linear-gradient(120deg, #a855f7 0%, #7e22ce 100%);
}

.pm-daily-table-card,
.pm-daily-form-card,
.pm-daily-detail-card {
    overflow: hidden;
}

.pm-daily-table-head {
    padding: 1.2rem 1.4rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.pm-daily-table-filters {
    display: flex;
    gap: 0.8rem;
    min-width: min(100%, 520px);
}

.pm-daily-vehicle-table {
    margin: 0;
}

.pm-daily-vehicle-table thead th {
    padding: 1rem 0.8rem;
    background: #f8fafc;
    color: #111827;
    white-space: nowrap;
}

.pm-daily-vehicle-table td {
    padding: 1rem 0.8rem;
    vertical-align: middle;
}

.pm-mile-value {
    font-weight: 700;
    color: #1f2937;
}

.pm-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.28rem 0.72rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
}

.pm-status-pill.is-active {
    background: #dbeafe;
    color: #2563eb;
}

.pm-status-pill.is-completed {
    background: #dcfce7;
    color: #16a34a;
}

.pm-status-pill.is-cancelled {
    background: #fee2e2;
    color: #ef4444;
}

.pm-table-actions {
    display: inline-flex;
    gap: 0.85rem;
}

.pm-row-link {
    border: 0;
    padding: 0;
    background: transparent;
    color: #0f9a93;
    font-weight: 700;
}

.pm-daily-detail-layout,
.pm-daily-form-layout {
    display: grid;
    gap: 1.25rem;
}

.pm-section-header {
    margin-bottom: 1rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #e5e7eb;
    font-size: 1.3rem;
    font-weight: 700;
    color: #109d90;
}

.pm-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1.25rem;
}

.pm-detail-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.95rem 0;
    border-bottom: 1px solid #edf2f7;
}

.pm-detail-row span {
    color: #475569;
    font-weight: 600;
}

.pm-detail-row strong {
    color: #1f2937;
    text-align: right;
}

.pm-detail-notes {
    margin: 0;
    color: #334155;
    white-space: pre-wrap;
}

.pm-field-label {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 0.45rem;
    font-size: 0.94rem;
    font-weight: 700;
    color: #334155;
}

.pm-required-star {
    color: #ef4444;
}

.pm-form-error {
    margin-top: 0.35rem;
    color: #dc2626;
    font-size: 0.85rem;
}

.pm-daily-form-actions,
.pm-daily-action-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
}

@media (max-width: 1200px) {
    .pm-daily-vehicle-table {
        min-width: 1180px;
    }
}

@media (max-width: 991px) {
    .pm-page-heading--with-actions,
    .pm-daily-toolbar-card,
    .pm-daily-table-head,
    .pm-daily-page-toolbar,
    .pm-daily-toolbar-actions,
    .pm-daily-table-filters,
    .pm-daily-stats-grid,
    .pm-detail-grid {
        display: grid;
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767px) {
    .pm-page-heading h1 {
        font-size: 1.7rem;
    }

    .pm-detail-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .pm-detail-row strong {
        text-align: left;
    }
}
</style>
