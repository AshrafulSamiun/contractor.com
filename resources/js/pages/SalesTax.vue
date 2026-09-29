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
                    <button class="pm-icon-btn" type="button" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z"
                            />
                        </svg>
                        <span class="pm-topbar-badge"></span>
                    </button>
                    <button class="pm-icon-btn" type="button" aria-label="Settings">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66Z"
                            />
                        </svg>
                    </button>
                </div>
                <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
                    <div class="pm-topbar-avatar">JA</div>
                    <span class="pm-topbar-name">{{ userName }}</span>
                    <span class="pm-topbar-pill">Admin</span>
                    <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m7 10 5 5 5-5H7Z" />
                        </svg>
                    </button>
                    <div v-if="userMenuOpen" class="pm-user-menu">
                        <button class="pm-user-item" type="button" @click="goProfile">
                            Profile
                        </button>
                        <button class="pm-user-item" type="button" @click="goAccount">
                            Account
                        </button>
                        <button class="pm-user-item danger" type="button" @click="logout">
                            Log out
                        </button>
                    </div>
                </div>
            </div>

            <div class="container pm-ops-page pm-payment-method-page">
                <section class="pm-payment-method-hero">
                    <div>
                        <div class="pm-payment-method-kicker">Profiles Module</div>
                        <h2 class="pm-payment-method-title">Sales Tax</h2>
                        <div class="pm-page-subtitle pm-payment-method-subtitle">
                            Dashboard &gt; Profiles &gt; Sales Tax &gt;
                            {{
                                viewMode === "list"
                                    ? "List"
                                    : viewMode === "detail"
                                      ? "Details"
                                      : activeId
                                        ? "Editor"
                                        : "Create"
                            }}
                        </div>
                    </div>
                    <div
                        v-if="viewMode === 'detail'"
                        class="pm-payment-method-hero-actions"
                    >
                        <button
                            class="pm-hero-action-button pm-hero-action-button--primary"
                            type="button"
                            @click="startEdit(detailItem)"
                        >
                            <span class="pm-hero-action-icon" aria-hidden="true"
                                >&crarr;</span
                            >
                            <span>Edit</span>
                        </button>
                    </div>
                    <div
                        v-else
                        class="pm-payment-method-hero-actions"
                    >
                        <span class="pm-payment-method-chip">{{
                            viewMode === "list"
                                ? "List View"
                                : viewMode === "detail"
                                  ? "Detail View"
                                  : activeId
                                    ? "Editing"
                                    : "New Entry"
                        }}</span>
                        <div class="pm-page-actions pm-page-actions--hero">
                            <button
                                class="pm-hero-action-button"
                                :class="
                                    viewMode === 'form'
                                        ? 'pm-hero-action-button--primary'
                                        : 'pm-hero-action-button--secondary'
                                "
                                type="button"
                                @click="openFormView"
                            >
                                <span class="pm-hero-action-icon" aria-hidden="true"
                                    >+</span
                                >
                                <span>New</span>
                            </button>
                            <button
                                class="pm-hero-action-button"
                                :class="
                                    viewMode === 'list'
                                        ? 'pm-hero-action-button--primary'
                                        : 'pm-hero-action-button--secondary'
                                "
                                type="button"
                                @click="openListView"
                            >
                                <span class="pm-hero-action-icon pm-hero-action-icon--list" aria-hidden="true">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                                <span>List</span>
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="viewMode === 'list'"
                    class="pm-card pm-ops-card p-4 pm-payment-method-list-shell"
                >
                    <div class="pm-payment-method-list-toolbar">
                        <div>
                            <h5 class="pm-form-title">Sales Tax List</h5>
                            <div class="pm-payment-method-list-meta">
                                {{ filteredItems.length }} records found
                            </div>
                        </div>
                        <div class="pm-payment-method-list-search">
                            <input
                                v-model.trim="listSearchQuery"
                                class="form-control"
                                type="search"
                                placeholder="Search all sales tax fields"
                            />
                        </div>
                    </div>

                    <div class="table-responsive pm-payment-method-table-wrap">
                        <table class="table pm-payment-method-table align-middle">
                            <thead>
                                <tr>
                                    <th
                                        v-for="column in listColumns"
                                        :key="column.key"
                                        scope="col"
                                    >
                                        <button
                                            class="pm-payment-method-sort"
                                            type="button"
                                            @click="toggleSort(column.key)"
                                        >
                                            <span>{{ column.label }}</span>
                                            <span
                                                class="pm-payment-method-sort-icon"
                                                :class="{
                                                    'is-active':
                                                        listSortKey === column.key,
                                                    'is-desc':
                                                        listSortKey === column.key &&
                                                        listSortDirection ===
                                                            'desc',
                                                }"
                                                aria-hidden="true"
                                            >
                                                ^
                                            </span>
                                        </button>
                                    </th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody v-if="paginatedItems.length">
                                <tr
                                    v-for="item in paginatedItems"
                                    :key="item.id"
                                >
                                    <td>
                                        <span class="pm-payment-method-table-code">
                                            {{ item.system_no || "--" }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="pm-payment-method-table-primary">
                                            {{ item.tax_name || "--" }}
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="pm-payment-method-type-badge"
                                            :class="getTaxTypeBadgeClass(item.tax_type)"
                                        >
                                            {{ formatTaxType(item.tax_type) }}
                                        </span>
                                    </td>
                                    <td>{{ formatTaxRate(item.tax_type, item.tax_rate) }}</td>
                                    <td>
                                        <span
                                            class="pm-payment-method-type-badge"
                                            :class="getApplicationReasonBadgeClass(item.application_reason)"
                                        >
                                            {{ formatApplicationReason(item.application_reason) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="pm-payment-method-status"
                                            :class="
                                                item.status_active
                                                    ? 'is-active'
                                                    : 'is-inactive'
                                            "
                                        >
                                            {{
                                                item.status_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate(item.created_at) }}</td>
                                    <td>
                                        <div class="pm-payment-method-table-actions">
                                            <button
                                                class="btn btn-outline-primary btn-sm"
                                                type="button"
                                                @click="startView(item)"
                                            >
                                                View
                                            </button>
                                            <button
                                                class="btn btn-primary btn-sm"
                                                type="button"
                                                @click="startEdit(item)"
                                            >
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td
                                        class="pm-payment-method-table-empty"
                                        :colspan="listColumns.length + 1"
                                    >
                                        No sales taxes match your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pm-payment-method-list-footer">
                        <div class="pm-payment-method-pagination-summary">
                            Showing {{ paginationStart }}-{{ paginationEnd }} of
                            {{ filteredItems.length }}
                        </div>
                        <div
                            v-if="totalPages > 1"
                            class="pm-payment-method-pagination"
                        >
                            <button
                                class="pm-payment-method-page-button"
                                type="button"
                                :disabled="listPage === 1"
                                @click="changePage(listPage - 1)"
                            >
                                Prev
                            </button>
                            <button
                                v-for="page in visiblePages"
                                :key="page"
                                class="pm-payment-method-page-button"
                                :class="{ 'is-active': page === listPage }"
                                type="button"
                                @click="changePage(page)"
                            >
                                {{ page }}
                            </button>
                            <button
                                class="pm-payment-method-page-button"
                                type="button"
                                :disabled="listPage === totalPages"
                                @click="changePage(listPage + 1)"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-else-if="viewMode === 'detail' && detailItem"
                    class="pm-payment-method-detail-shell"
                >
                    <section class="pm-card pm-ops-card p-4 pm-payment-method-detail-code-card">
                        <div>
                            <div class="pm-payment-method-detail-code-label">
                                Sales Tax No
                            </div>
                            <div class="pm-payment-method-detail-code-value">
                                {{ detailItem.system_no || "--" }}
                            </div>
                        </div>
                        <div class="pm-payment-method-detail-badges">
                            <span
                                class="pm-payment-method-status"
                                :class="
                                    detailItem.status_active
                                        ? 'is-active'
                                        : 'is-inactive'
                                "
                            >
                                {{
                                    detailItem.status_active
                                        ? "Active"
                                        : "Inactive"
                                }}
                            </span>
                        </div>
                    </section>

                    <section class="pm-payment-method-detail-section">
                        <div class="pm-payment-method-detail-section-head">
                            Basic Information
                        </div>
                        <div class="pm-card pm-ops-card p-4 pm-payment-method-detail-card">
                            <div class="pm-payment-method-detail-grid">
                                <div class="pm-payment-method-detail-row">
                                    <span class="pm-payment-method-detail-key"
                                        >Tax Name:</span
                                    >
                                    <span class="pm-payment-method-detail-value"
                                        >{{ detailItem.tax_name || "--" }}</span
                                    >
                                </div>
                                <div class="pm-payment-method-detail-row">
                                    <span class="pm-payment-method-detail-key"
                                        >Tax Type:</span
                                    >
                                    <span class="pm-payment-method-detail-value"
                                        >{{ formatTaxType(detailItem.tax_type) }}</span
                                    >
                                </div>
                                <div class="pm-payment-method-detail-row">
                                    <span class="pm-payment-method-detail-key"
                                        >Tax Rate:</span
                                    >
                                    <span class="pm-payment-method-detail-value"
                                        >{{ formatTaxRate(detailItem.tax_type, detailItem.tax_rate) }}</span
                                    >
                                </div>
                                <div class="pm-payment-method-detail-row">
                                    <span class="pm-payment-method-detail-key"
                                        >Application Reason:</span
                                    >
                                    <span class="pm-payment-method-detail-value"
                                        >{{ formatApplicationReason(detailItem.application_reason) }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-payment-method-detail-section">
                        <div
                            class="pm-payment-method-detail-section-head pm-payment-method-detail-section-head--muted"
                        >
                            System Information
                        </div>
                        <div class="pm-card pm-ops-card p-4 pm-payment-method-detail-card">
                            <div class="pm-payment-method-detail-grid">
                                <div class="pm-payment-method-detail-row">
                                    <span class="pm-payment-method-detail-key"
                                        >Created Date:</span
                                    >
                                    <span class="pm-payment-method-detail-value"
                                        >{{ formatDate(detailItem.created_at) }}</span
                                    >
                                </div>
                                <div class="pm-payment-method-detail-row">
                                    <span class="pm-payment-method-detail-key"
                                        >Last Modified:</span
                                    >
                                    <span class="pm-payment-method-detail-value"
                                        >{{ formatDate(detailItem.updated_at) }}</span
                                    >
                                </div>
                                <div class="pm-payment-method-detail-row">
                                    <span class="pm-payment-method-detail-key"
                                        >Record ID:</span
                                    >
                                    <span class="pm-payment-method-detail-value"
                                        >{{ detailItem.id || "--" }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section v-if="detailItem.notes" class="pm-payment-method-detail-section">
                        <div class="pm-payment-method-detail-section-head">
                            Notes
                        </div>
                        <div class="pm-card pm-ops-card p-4 pm-payment-method-detail-card">
                            <p>{{ detailItem.notes }}</p>
                        </div>
                    </section>
                </section>

                <div v-else class="pm-payment-method-form-shell">
                    <section class="pm-card pm-ops-card p-4 pm-payment-method-section">
                        <div class="pm-payment-method-section-head">
                            <h5 class="pm-form-title">
                                Tax Type
                                <span class="pm-required-star">*</span>
                            </h5>
                            <span class="pm-payment-method-section-tag"
                                >Classification</span
                            >
                        </div>
                        <div class="pm-checkbox-grid pm-checkbox-grid--4">
                            <label
                                class="pm-checkbox-item"
                                v-for="type in taxTypeOptions"
                                :key="type.value"
                            >
                                <input
                                    type="radio"
                                    name="tax_type"
                                    :value="type.value"
                                    v-model="form.tax_type"
                                />
                                <span class="pm-checkbox-label">{{
                                    type.label
                                }}</span>
                            </label>
                        </div>
                        <div v-if="errors.tax_type" class="pm-form-error">
                            {{ errors.tax_type }}
                        </div>
                    </section>

                    <section class="pm-card pm-ops-card p-4 pm-payment-method-section">
                        <div class="pm-payment-method-section-head">
                            <h5 class="pm-form-title">
                                Application Reason
                                <span class="pm-required-star">*</span>
                            </h5>
                            <span class="pm-payment-method-section-tag"
                                >Jurisdiction</span
                            >
                        </div>
                        <div class="pm-checkbox-grid pm-checkbox-grid--4">
                            <label
                                class="pm-checkbox-item"
                                v-for="reason in applicationReasonOptions"
                                :key="reason.value"
                            >
                                <input
                                    type="radio"
                                    name="application_reason"
                                    :value="reason.value"
                                    v-model="form.application_reason"
                                />
                                <span class="pm-checkbox-label">{{
                                    reason.label
                                }}</span>
                            </label>
                        </div>
                        <div v-if="errors.application_reason" class="pm-form-error">
                            {{ errors.application_reason }}
                        </div>
                    </section>

                    <section class="pm-card pm-ops-card p-4 pm-payment-method-section">
                        <div class="pm-payment-method-section-head">
                            <h5 class="pm-form-title">Basic Information</h5>
                            <span class="pm-payment-method-section-tag"
                                >Core</span
                        >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="pm-field-label">System No</label>
                                <input
                                    class="form-control"
                                    v-model="form.system_no"
                                    placeholder="Auto-generated"
                                    disabled
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Tax Name <span class="pm-required-star">*</span></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.tax_name"
                                    placeholder="Enter tax name"
                                />
                                <div v-if="errors.tax_name" class="pm-form-error">
                                    {{ errors.tax_name }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Tax Rate <span class="pm-required-star">*</span></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.tax_rate"
                                    :placeholder="form.tax_type === 1 ? 'e.g., 5.00 for 5%' : 'e.g., 100.00'"
                                    type="number"
                                    step="0.01"
                                />
                                <div v-if="errors.tax_rate" class="pm-form-error">
                                    {{ errors.tax_rate }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-card pm-ops-card p-4 pm-payment-method-section">
                        <div class="pm-payment-method-section-head">
                            <h5 class="pm-form-title">Status</h5>
                            <span class="pm-payment-method-section-tag"
                                >Settings</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="pm-status-checkboxes">
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="checkbox"
                                            :checked="form.status_active"
                                            @change="form.status_active = $event.target.checked"
                                        />
                                        <span class="pm-checkbox-label">Active</span>
                                    </label>
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="checkbox"
                                            :checked="!form.status_active"
                                            @change="form.status_active = !$event.target.checked"
                                        />
                                        <span class="pm-checkbox-label">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-card pm-ops-card p-4 pm-payment-method-section">
                        <div class="pm-payment-method-section-head">
                            <h5 class="pm-form-title">Notes</h5>
                            <span class="pm-payment-method-section-tag"
                                >Additional</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <textarea
                                    class="form-control"
                                    v-model="form.notes"
                                    placeholder="Enter any additional notes"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <div class="pm-form-actions">
                        <button
                            class="btn btn-secondary"
                            type="button"
                            @click="cancelForm"
                        >
                            Cancel
                        </button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            @click="saveForm"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import client from "../api/client";
import AppSidebar from "../components/AppSidebar.vue";

const router = useRouter();

const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const userName = ref("John Admin");
const searchQuery = ref("");

const viewMode = ref("list");
const activeId = ref(null);
const detailItem = ref(null);
const listSearchQuery = ref("");
const listPage = ref(1);
const listSortKey = ref("created_at");
const listSortDirection = ref("desc");
const items = ref([]);
const errors = ref({});

const form = ref({
    system_prefix: "ST",
    system_no: "",
    tax_name: "",
    tax_type: null,
    tax_rate: "",
    application_reason: null,
    status_active: true,
    notes: "",
});

const taxTypeOptions = [
    { value: 1, label: "Percentage" },
    { value: 2, label: "Fixed Amount" },
];

const applicationReasonOptions = [
    { value: 1, label: "Canada GST" },
    { value: 2, label: "Canada HST" },
    { value: 3, label: "Canada PST/QST" },
    { value: 4, label: "USA Sales Tax" },
    { value: 5, label: "UK VAT" },
    { value: 6, label: "Australia GST" },
    { value: 7, label: "TBD" },
    { value: 8, label: "TBD" },
    { value: 9, label: "TBD" },
    { value: 10, label: "Other" },
];

const listColumns = [
    { key: "system_no", label: "Tax No" },
    { key: "tax_name", label: "Tax Name" },
    { key: "tax_type", label: "Tax Type" },
    { key: "tax_rate", label: "Tax Rate" },
    { key: "application_reason", label: "Application Reason" },
    { key: "status_active", label: "Status" },
    { key: "created_at", label: "Created" },
];

const filteredItems = computed(() => {
    let result = items.value.filter((item) => !item.is_deleted);

    if (listSearchQuery.value) {
        const query = listSearchQuery.value.toLowerCase();
        result = result.filter(
            (item) =>
                (item.tax_name && item.tax_name.toLowerCase().includes(query)) ||
                (item.system_no &&
                    item.system_no.toLowerCase().includes(query))
        );
    }

    result.sort((a, b) => {
        const aVal = a[listSortKey.value];
        const bVal = b[listSortKey.value];
        if (aVal < bVal) return listSortDirection.value === "asc" ? -1 : 1;
        if (aVal > bVal) return listSortDirection.value === "asc" ? 1 : -1;
        return 0;
    });

    return result;
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredItems.value.length / 10))
);

const paginatedItems = computed(() => {
    const start = (listPage.value - 1) * 10;
    return filteredItems.value.slice(start, start + 10);
});

const paginationStart = computed(() =>
    filteredItems.value.length > 0
        ? (listPage.value - 1) * 10 + 1
        : 0
);

const paginationEnd = computed(() =>
    Math.min(listPage.value * 10, filteredItems.value.length)
);

const visiblePages = computed(() => {
    const pages = [];
    const total = totalPages.value;
    const current = listPage.value;
    for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
        pages.push(i);
    }
    return pages;
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const toggleSidebarHidden = () => {
    sidebarHidden.value = !sidebarHidden.value;
};

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value;
};

const goProfile = () => {
    router.push("/account/profile");
};

const goAccount = () => {
    router.push("/account/status");
};

const logout = () => {
    router.push("/login");
};

const formatDate = (date) => {
    if (!date) return "--";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatTaxType = (value) => {
    const option = taxTypeOptions.find((opt) => opt.value === value);
    return option ? option.label : "--";
};

const formatTaxRate = (type, rate) => {
    if (rate === null || rate === "" || rate === undefined) return "--";
    if (type === 1) {
        return `${parseFloat(rate).toFixed(2)}%`;
    }
    return `$${parseFloat(rate).toFixed(2)}`;
};

const formatApplicationReason = (value) => {
    const option = applicationReasonOptions.find((opt) => opt.value === value);
    return option ? option.label : "--";
};

const getTaxTypeBadgeClass = (value) => {
    const classes = {
        1: "pm-type-percentage",
        2: "pm-type-fixed",
    };
    return classes[value] || "";
};

const getApplicationReasonBadgeClass = (value) => {
    const classes = {
        1: "pm-type-canada",
        2: "pm-type-canada",
        3: "pm-type-canada",
        4: "pm-type-usa",
        5: "pm-type-uk",
        6: "pm-type-au",
        10: "pm-type-other",
    };
    return classes[value] || "pm-type-default";
};

const openFormView = () => {
    viewMode.value = "form";
    activeId.value = null;
    resetForm();
    generateSystemNo();
};

const openListView = () => {
    viewMode.value = "list";
    activeId.value = null;
    detailItem.value = null;
};

const startView = async (item) => {
    viewMode.value = "detail";
    detailItem.value = item;
    activeId.value = item.id;
};

const startEdit = async (item) => {
    viewMode.value = "form";
    activeId.value = item.id;
    form.value = { ...item };
};

const cancelForm = () => {
    if (activeId.value) {
        startView(items.value.find((i) => i.id === activeId.value));
    } else {
        openListView();
    }
};

const saveForm = async () => {
    errors.value = {};

    if (!form.value.tax_name) {
        errors.value.tax_name = "Tax name is required";
    }
    if (!form.value.tax_type) {
        errors.value.tax_type = "Tax type is required";
    }
    if (!form.value.tax_rate && form.value.tax_rate !== 0) {
        errors.value.tax_rate = "Tax rate is required";
    }
    if (!form.value.application_reason) {
        errors.value.application_reason = "Application reason is required";
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    try {
        const payload = { ...form.value };

        if (activeId.value) {
            await client.put(`/sales-taxes/${activeId.value}`, payload);
        } else {
            await client.post("/sales-taxes", payload);
        }

        await fetchItems();
        openListView();
    } catch (error) {
        console.error("Error saving sales tax:", error);
    }
};

const resetForm = () => {
    form.value = {
        system_prefix: "ST",
        system_no: "",
        tax_name: "",
        tax_type: null,
        tax_rate: "",
        application_reason: null,
        status_active: true,
        notes: "",
    };
    errors.value = {};
};

const generateSystemNo = () => {
    const year = new Date().getFullYear();
    const count = items.value.filter(
        (item) => item.system_no && item.system_no.includes(year)
    ).length;
    form.value.system_no = `ST-${year}-${String(count + 1).padStart(3, "0")}`;
};

const fetchItems = async () => {
    try {
        const { data } = await client.get("/sales-taxes");
        items.value = data.data || data;
    } catch (error) {
        console.error("Error fetching sales taxes:", error);
    }
};

const toggleSort = (key) => {
    if (listSortKey.value === key) {
        listSortDirection.value = listSortDirection.value === "asc" ? "desc" : "asc";
    } else {
        listSortKey.value = key;
        listSortDirection.value = "asc";
    }
};

const changePage = (page) => {
    listPage.value = page;
};

onMounted(() => {
    fetchItems();
});
</script>

<style scoped>
.pm-payment-method-page {
    max-width: 1400px;
}

.pm-payment-method-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background: var(--pm-card-bg, #fff);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.pm-payment-method-kicker {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.pm-payment-method-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.pm-payment-method-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.pm-payment-method-chip {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #f3f4f6;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.pm-payment-method-hero-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
}

.pm-page-actions--hero {
    display: flex;
    gap: 0.5rem;
}

.pm-hero-action-button {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
}

.pm-hero-action-button--primary {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}

.pm-hero-action-button--secondary {
    background: #fff;
    border-color: #d1d5db;
    color: #374151;
}

.pm-hero-action-icon {
    font-size: 1rem;
    line-height: 1;
}

.pm-hero-action-icon--list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.pm-hero-action-icon--list span {
    display: block;
    width: 14px;
    height: 2px;
    background: currentColor;
    border-radius: 1px;
}

.pm-payment-method-list-shell {
    margin-bottom: 1.5rem;
}

.pm-payment-method-list-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.pm-form-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.pm-payment-method-list-meta {
    font-size: 0.875rem;
    color: #6b7280;
}

.pm-payment-method-list-search {
    width: 300px;
}

.pm-payment-method-table-wrap {
    overflow-x: auto;
}

.pm-payment-method-table {
    width: 100%;
    margin-bottom: 0;
}

.pm-payment-method-table th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.pm-payment-method-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
}

.pm-payment-method-table-code {
    font-family: monospace;
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
}

.pm-payment-method-table-primary {
    font-weight: 500;
    color: #111827;
}

.pm-payment-method-type-badge {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.pm-type-percentage {
    background: #dbeafe;
    color: #1e40af;
}

.pm-type-fixed {
    background: #e0e7ff;
    color: #3730a3;
}

.pm-type-canada {
    background: #fef3c7;
    color: #92400e;
}

.pm-type-usa {
    background: #dbeafe;
    color: #1e40af;
}

.pm-type-uk {
    background: #e0e7ff;
    color: #3730a3;
}

.pm-type-au {
    background: #d1fae5;
    color: #065f46;
}

.pm-type-default {
    background: #f3f4f6;
    color: #374151;
}

.pm-type-other {
    background: #fce7f3;
    color: #9d174d;
}

.pm-payment-method-status {
    display: inline-flex;
    align-items: center;
    font-size: 0.875rem;
    font-weight: 500;
}

.pm-payment-method-status.is-active {
    color: #059669;
}

.pm-payment-method-status.is-inactive {
    color: #dc2626;
}

.pm-payment-method-table-actions {
    display: flex;
    gap: 0.5rem;
}

.pm-payment-method-table-empty {
    text-align: center;
    color: #6b7280;
    padding: 2rem;
}

.pm-payment-method-list-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.pm-payment-method-pagination-summary {
    font-size: 0.875rem;
    color: #6b7280;
}

.pm-payment-method-pagination {
    display: flex;
    gap: 0.25rem;
}

.pm-payment-method-page-button {
    padding: 0.375rem 0.75rem;
    border: 1px solid #d1d5db;
    background: #fff;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
}

.pm-payment-method-page-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pm-payment-method-page-button.is-active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}

.pm-payment-method-sort {
    background: none;
    border: none;
    padding: 0;
    font: inherit;
    color: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pm-payment-method-sort-icon {
    opacity: 0.5;
}

.pm-payment-method-sort-icon.is-active {
    opacity: 1;
}

.pm-payment-method-sort-icon.is-desc {
    transform: rotate(180deg);
}

.pm-payment-method-detail-shell {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.pm-payment-method-detail-code-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pm-payment-method-detail-code-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.pm-payment-method-detail-code-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    font-family: monospace;
}

.pm-payment-method-detail-badges {
    display: flex;
    gap: 0.5rem;
}

.pm-payment-method-detail-section-head {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 0.75rem;
}

.pm-payment-method-detail-section-head--muted {
    opacity: 0.7;
}

.pm-payment-method-detail-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.pm-payment-method-detail-row {
    display: flex;
    gap: 1rem;
}

.pm-payment-method-detail-key {
    font-weight: 500;
    color: #374151;
    min-width: 150px;
}

.pm-payment-method-detail-value {
    color: #111827;
}

.pm-payment-method-form-shell {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.pm-payment-method-section {
    margin-bottom: 0;
}

.pm-payment-method-section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.pm-payment-method-section-head .pm-form-title {
    margin: 0;
}

.pm-payment-method-section-tag {
    font-size: 0.75rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
}

.pm-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 0.75rem;
}

.pm-checkbox-grid--4 {
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
}

.pm-status-checkboxes {
    display: flex;
    gap: 1.5rem;
}

.pm-checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.pm-checkbox-item input {
    width: 16px;
    height: 16px;
}

.pm-checkbox-label {
    font-size: 0.875rem;
    color: #374151;
}

.pm-field-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.375rem;
}

.pm-required-star {
    color: #dc2626;
    margin-left: 0.25rem;
}

.pm-form-error {
    font-size: 0.75rem;
    color: #dc2626;
    margin-top: 0.25rem;
}

.pm-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .pm-payment-method-hero {
        flex-direction: column;
        gap: 1rem;
    }

    .pm-payment-method-hero-actions {
        align-items: flex-start;
        width: 100%;
    }

    .pm-page-actions--hero {
        width: 100%;
    }

    .pm-hero-action-button {
        flex: 1;
        justify-content: center;
    }

    .pm-payment-method-list-toolbar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .pm-payment-method-list-search {
        width: 100%;
    }
}
</style>