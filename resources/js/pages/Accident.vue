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
                        placeholder="Search accident reports..."
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

            <div class="container pm-ops-page pm-accident-page">
                <header class="pm-page-heading pm-page-heading--with-actions">
                    <h1>{{ pageTitle }}</h1>
                    <div class="pm-accident-toolbar">
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
                            class="pm-accident-primary-btn"
                            type="button"
                            @click="startCreate"
                        >
                            <span>+</span>
                            New Accident Report
                        </button>
                    </div>
                </header>

                <section class="pm-accident-hero">
                    <div class="pm-accident-hero-content">
                        <div class="pm-accident-hero-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 3 2 20h20L12 3Zm0 6v5m0 4h.01"
                                />
                            </svg>
                        </div>
                        <div>
                            <h2>{{ heroTitle }}</h2>
                            <p>{{ heroSubtitle }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="viewMode === 'list'"
                    class="pm-accident-card pm-accident-list-filter"
                >
                    <div class="pm-accident-search-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                            />
                        </svg>
                        <input
                            v-model.trim="listSearchQuery"
                            type="search"
                            class="form-control"
                            placeholder="Search by report, vehicle, driver, location..."
                        />
                    </div>
                    <select
                        v-model="filterAccidentType"
                        class="form-control pm-filter-select"
                    >
                        <option value="">All Accident Types</option>
                        <option
                            v-for="item in accidentTypeOptions"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>
                    </select>
                    <select
                        v-model="filterStatus"
                        class="form-control pm-filter-select"
                    >
                        <option value="">All Status</option>
                        <option
                            v-for="item in statusOptions"
                            :key="item.value"
                            :value="String(item.value)"
                        >
                            {{ item.label }}
                        </option>
                    </select>
                </section>

                <section
                    v-if="viewMode === 'list'"
                    class="pm-accident-card pm-accident-list-card"
                >
                    <div class="pm-accident-list-head">
                        <div class="pm-accident-list-title">
                            <span class="pm-accident-list-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M12 3 2 20h20L12 3Zm0 6v5m0 4h.01"
                                    />
                                </svg>
                            </span>
                            Accident Reports ({{ filteredItems.length }})
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table pm-accident-table align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>
                                        <button
                                            class="pm-sort-button"
                                            type="button"
                                            @click="toggleSort('report_code')"
                                        >
                                            Report No
                                            <span class="pm-sort-indicator">{{
                                                sortIndicator("report_code")
                                            }}</span>
                                        </button>
                                    </th>
                                    <th>Report Date</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Accident Type</th>
                                    <th>Location</th>
                                    <th>Total Cost</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, index) in paginatedItems"
                                    :key="item.id"
                                >
                                    <td>{{ rowNumber(index) }}</td>
                                    <td>
                                        <button
                                            class="pm-accident-link-btn"
                                            type="button"
                                            @click="openDetailView(item)"
                                        >
                                            {{ item.report_code || "--" }}
                                        </button>
                                    </td>
                                    <td>{{ formatDisplayDate(item.report_date) }}</td>
                                    <td>
                                        <strong class="d-block text-primary">
                                            {{ item.vehicle_number || "--" }}
                                        </strong>
                                        <small>{{
                                            item.vehicle_make_model || "--"
                                        }}</small>
                                    </td>
                                    <td>{{ item.driver_name || "--" }}</td>
                                    <td>
                                        <span class="pm-accident-type-badge">
                                            {{ item.accident_type || "--" }}
                                        </span>
                                    </td>
                                    <td>{{ item.location || "--" }}</td>
                                    <td class="pm-cost-text">
                                        {{ formatMoney(item.total_cost) }}
                                    </td>
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
                                                class="pm-icon-action"
                                                type="button"
                                                title="View"
                                                @click="openDetailView(item)"
                                            >
                                                <svg viewBox="0 0 24 24">
                                                    <path
                                                        d="M1.5 12s3.5-7 10.5-7 10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"
                                                    />
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="3"
                                                    />
                                                </svg>
                                            </button>
                                            <button
                                                class="pm-icon-action edit"
                                                type="button"
                                                title="Edit"
                                                @click="startEdit(item)"
                                            >
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M12 20h9" />
                                                    <path
                                                        d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"
                                                    />
                                                </svg>
                                            </button>
                                            <button
                                                class="pm-icon-action delete"
                                                type="button"
                                                title="Delete"
                                                @click="deleteItem(item)"
                                            >
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M3 6h18" />
                                                    <path
                                                        d="M8 6V4h8v2M6 6l1 14h10l1-14"
                                                    />
                                                    <path
                                                        d="M10 11v6M14 11v6"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!paginatedItems.length">
                                    <td colspan="10" class="text-center py-4">
                                        No accident reports found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pm-accident-stats-grid">
                        <div class="pm-stat-card is-open">
                            <span>Open Reports</span>
                            <strong>{{ openCount }}</strong>
                        </div>
                        <div class="pm-stat-card is-closed">
                            <span>Closed Reports</span>
                            <strong>{{ closedCount }}</strong>
                        </div>
                        <div class="pm-stat-card is-pending">
                            <span>Pending Reports</span>
                            <strong>{{ pendingCount }}</strong>
                        </div>
                        <div class="pm-stat-card is-total">
                            <span>Total Cost</span>
                            <strong>{{ formatMoney(totalCostAmount) }}</strong>
                        </div>
                    </div>

                    <div class="pm-accident-list-footer">
                        <span>
                            Showing {{ filteredItems.length }} of
                            {{ items.length }} accident reports
                        </span>
                        <div class="pm-pagination">
                            <button
                                type="button"
                                :disabled="currentPage === 1"
                                @click="currentPage -= 1"
                            >
                                Previous
                            </button>
                            <span>{{ currentPage }}</span>
                            <button
                                type="button"
                                :disabled="currentPage >= totalPages"
                                @click="currentPage += 1"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </section>

                <div
                    v-else-if="viewMode === 'detail' && detailItem"
                    class="pm-accident-detail-layout"
                >
                    <section class="pm-accident-card pm-accident-profile-card">
                        <div class="pm-accident-profile-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 3 2 20h20L12 3Zm0 6v5m0 4h.01"
                                />
                            </svg>
                        </div>
                        <h3>{{ detailItem.report_code || "--" }}</h3>
                        <p>{{ detailItem.accident_type || "--" }}</p>
                        <small>{{ detailItem.location || "--" }}</small>
                        <span
                            class="pm-status-pill mt-3"
                            :class="statusClass(detailItem.status)"
                        >
                            {{ detailItem.status_label }}
                        </span>
                        <div class="pm-accident-total">
                            <span>Total Cost</span>
                            <strong>{{
                                formatMoney(detailItem.total_cost)
                            }}</strong>
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-detail-card">
                        <div class="pm-section-header">Report Overview</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Report No</span>
                                <strong>{{
                                    detailItem.report_code || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Report Date</span>
                                <strong>{{
                                    formatLongDate(detailItem.report_date)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Created By</span>
                                <strong>{{
                                    detailItem.created_by_name || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Incident Report No</span>
                                <strong>{{
                                    detailItem.incident_report_no || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Incident Report Name</span>
                                <strong>{{
                                    detailItem.incident_report_name || "--"
                                }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-full-row">
                        <div class="pm-section-header">Accident Details</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Accident ID</span>
                                <strong>{{
                                    detailItem.accident_reference || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Incident Date</span>
                                <strong>{{
                                    formatLongDate(detailItem.incident_date)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Time</span>
                                <strong>{{
                                    detailItem.incident_time || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Location</span>
                                <strong>{{
                                    detailItem.location || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Accident Type</span>
                                <strong>{{
                                    detailItem.accident_type || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row pm-detail-row--notes">
                                <span>Description</span>
                                <strong>{{
                                    detailItem.description || "--"
                                }}</strong>
                            </div>
                        </div>
                    </section>

                    <div class="pm-accident-detail-bottom">
                        <section class="pm-accident-card">
                            <div class="pm-section-header">
                                Vehicle Information
                            </div>
                            <div class="pm-detail-grid">
                                <div class="pm-detail-row">
                                    <span>Vehicle Number</span>
                                    <strong>{{
                                        detailItem.vehicle_number || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Plate Number</span>
                                    <strong>{{
                                        detailItem.plate_number || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Make & Model</span>
                                    <strong>{{
                                        detailItem.vehicle_make_model || "--"
                                    }}</strong>
                                </div>
                            </div>
                        </section>

                        <section class="pm-accident-card">
                            <div class="pm-section-header">
                                Driver Information
                            </div>
                            <div class="pm-detail-grid">
                                <div class="pm-detail-row">
                                    <span>Driver Name</span>
                                    <strong>{{
                                        detailItem.driver_name || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Driver Phone</span>
                                    <strong>{{
                                        detailItem.driver_phone || "--"
                                    }}</strong>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section class="pm-accident-card pm-accident-full-row">
                        <div class="pm-section-header">Responsibility</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>At Fault Name</span>
                                <strong>{{
                                    detailItem.at_fault_name || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>At Fault Position</span>
                                <strong>{{
                                    detailItem.at_fault_position || "--"
                                }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-full-row">
                        <div class="pm-section-header">
                            Repair & Billing Information
                        </div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Repair Shop</span>
                                <strong>{{
                                    detailItem.repair_shop || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Invoice No</span>
                                <strong>{{
                                    detailItem.invoice_no || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Invoice Date</span>
                                <strong>{{
                                    formatLongDate(detailItem.invoice_date)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Subtotal Repair Cost</span>
                                <strong>{{
                                    formatMoney(
                                        detailItem.subtotal_repair_cost,
                                    )
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Sales Tax ({{ detailItem.sales_tax_rate || 0 }}%)</span>
                                <strong>{{
                                    formatMoney(
                                        detailItem.sales_tax_amount,
                                    )
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Total Cost</span>
                                <strong>{{
                                    formatMoney(detailItem.total_cost)
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Payment Method</span>
                                <strong>{{
                                    detailItem.payment_method || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Paid</span>
                                <strong>{{
                                    detailItem.is_paid ? "Yes" : "No"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Paid By</span>
                                <strong>{{
                                    detailItem.paid_by || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Amount Paid by Company</span>
                                <strong>{{
                                    formatMoney(
                                        detailItem.amount_paid_by_company,
                                    )
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Amount Paid by Insurance</span>
                                <strong>{{
                                    formatMoney(
                                        detailItem.amount_paid_by_insurance,
                                    )
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Status</span>
                                <strong>{{
                                    detailItem.status_label || "--"
                                }}</strong>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="detailItem.notes"
                        class="pm-accident-card pm-accident-notes-card pm-accident-full-row"
                    >
                        <div class="pm-section-header">Notes & Actions</div>
                        <p>{{ detailItem.notes }}</p>
                    </section>

                    <section
                        class="pm-accident-card pm-accident-action-bar pm-accident-full-row"
                    >
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            @click="openListView"
                        >
                            Back to List
                        </button>
                        <button
                            class="btn btn-danger"
                            type="button"
                            @click="startEdit(detailItem)"
                        >
                            Edit Report
                        </button>
                    </section>
                </div>

                <div v-else class="pm-accident-form-layout">
                    <section class="pm-accident-card pm-accident-form-card">
                        <div class="pm-section-header">Report Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">Report No</label>
                                <input
                                    class="form-control"
                                    :value="form.report_code || generatedReportCode"
                                    readonly
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Report Date</label>
                                <input
                                    v-model="form.report_date"
                                    type="date"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Created By</label>
                                <input
                                    v-model="form.created_by_name"
                                    class="form-control"
                                    placeholder="Select user"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Incident Report No
                                </label>
                                <input
                                    v-model="form.incident_report_no"
                                    class="form-control"
                                    placeholder="INC-001"
                                />
                            </div>
                            <div class="col-md-12">
                                <label class="pm-field-label">
                                    Incident Report Name
                                </label>
                                <input
                                    v-model="form.incident_report_name"
                                    class="form-control"
                                    placeholder="Minor Collision at Gate"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-form-card">
                        <div class="pm-section-header">Accident Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">Accident ID</label>
                                <input
                                    v-model="form.accident_reference"
                                    class="form-control"
                                    placeholder="ACCID-4088"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Incident Date
                                    <span class="pm-required-star">*</span>
                                </label>
                                <input
                                    v-model="form.incident_date"
                                    type="date"
                                    class="form-control"
                                />
                                <div
                                    v-if="errors.incident_date"
                                    class="pm-form-error"
                                >
                                    {{ errors.incident_date }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Time</label>
                                <input
                                    v-model="form.incident_time"
                                    type="time"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Location</label>
                                <input
                                    v-model="form.location"
                                    class="form-control"
                                    placeholder="Enter location"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Accident Type
                                    <span class="pm-required-star">*</span>
                                </label>
                                <select
                                    v-model="form.accident_type"
                                    class="form-control"
                                >
                                    <option value="">
                                        Select accident type
                                    </option>
                                    <option
                                        v-for="item in accidentTypeOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                                <div
                                    v-if="errors.accident_type"
                                    class="pm-form-error"
                                >
                                    {{ errors.accident_type }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="pm-field-label">
                                    Description / Damage
                                </label>
                                <textarea
                                    v-model="form.description"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Describe the accident and damages..."
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-form-card">
                        <div class="pm-section-header">Vehicle Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Vehicle Number
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
                                    Plate Number
                                </label>
                                <input
                                    class="form-control"
                                    :value="selectedVehicle?.plate_number || ''"
                                    placeholder="Auto-filled"
                                    readonly
                                />
                            </div>
                            <div class="col-md-12">
                                <label class="pm-field-label">
                                    Make & Model
                                </label>
                                <input
                                    class="form-control"
                                    :value="
                                        selectedVehicle
                                            ? `${selectedVehicle.make_brand} ${selectedVehicle.model}`
                                            : ''
                                    "
                                    placeholder="Auto-filled"
                                    readonly
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Driver Name</label>
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
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Driver Phone</label>
                                <input
                                    class="form-control"
                                    :value="selectedDriver?.contact_number || ''"
                                    placeholder="Auto-filled"
                                    readonly
                                />
                            </div>
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-form-card">
                        <div class="pm-section-header">Responsibility</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    At Fault Name
                                </label>
                                <input
                                    v-model="form.at_fault_name"
                                    class="form-control"
                                    placeholder="Enter name"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    At Fault Position
                                </label>
                                <select
                                    v-model="form.at_fault_position"
                                    class="form-control"
                                >
                                    <option value="">Select position</option>
                                    <option
                                        v-for="item in faultPositionOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-form-card">
                        <div class="pm-section-header">Repair & Billing</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">Repair Shop</label>
                                <input
                                    v-model="form.repair_shop"
                                    class="form-control"
                                    placeholder="Enter repair shop name"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Invoice No</label>
                                <input
                                    v-model="form.invoice_no"
                                    class="form-control"
                                    placeholder="INV-001"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Invoice Date</label>
                                <input
                                    v-model="form.invoice_date"
                                    type="date"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Subtotal Repair Cost ($)
                                </label>
                                <input
                                    v-model="form.subtotal_repair_cost"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="2000"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Sales Tax (%)
                                </label>
                                <input
                                    v-model="form.sales_tax_rate"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="13"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Total Paid ($)
                                </label>
                                <input
                                    class="form-control"
                                    :value="totalCostDisplay"
                                    placeholder="Auto-calculated"
                                    readonly
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Payment Method
                                </label>
                                <select
                                    v-model="form.payment_method"
                                    class="form-control"
                                >
                                    <option value="">Select method</option>
                                    <option
                                        v-for="item in paymentMethodOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Paid</label>
                                <select
                                    v-model="form.is_paid"
                                    class="form-control"
                                >
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Paid By</label>
                                <input
                                    v-model="form.paid_by"
                                    class="form-control"
                                    placeholder="Company/Insurance"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Amount Paid by Company ($)
                                </label>
                                <input
                                    v-model="form.amount_paid_by_company"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="1500"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Amount Paid by Insurance ($)
                                </label>
                                <input
                                    v-model="form.amount_paid_by_insurance"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="500"
                                />
                            </div>
                            <div class="col-md-6">
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
                        </div>
                    </section>

                    <section class="pm-accident-card pm-accident-form-card">
                        <div class="pm-section-header">Notes & Actions</div>
                        <textarea
                            v-model="form.notes"
                            class="form-control"
                            rows="4"
                            placeholder="Add any additional notes about this accident..."
                        ></textarea>
                    </section>

                    <section class="pm-accident-card pm-accident-form-actions">
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
                            {{ saving ? "Saving..." : "Save Report" }}
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
                            @click="exportPdf"
                        >
                            Save PDF
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
import accidentService from "../api/accident";
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
const filterAccidentType = ref("");
const filterStatus = ref("");
const currentPage = ref(1);
const pageSize = 8;
const errors = reactive({});

const statusOptions = [
    { value: 1, label: "Open" },
    { value: 2, label: "Closed" },
    { value: 3, label: "Pending" },
];
const accidentTypeOptions = [
    "Collision",
    "Breakdown",
    "Minor Accident",
    "Other",
];
const faultPositionOptions = [
    "Driver",
    "Third Party",
    "Company",
    "Unknown",
];
const paymentMethodOptions = [
    "Cash",
    "Bank Transfer",
    "Card",
    "Insurance",
];
const vehicleOptions = ref([]);
const driverOptions = ref([]);
const sortKey = ref("report_code");
const sortDirection = ref("asc");

const form = reactive({
    id: "",
    report_code: "",
    report_date: "",
    created_by_name: "",
    incident_report_no: "",
    incident_report_name: "",
    accident_reference: "",
    incident_date: "",
    incident_time: "",
    location: "",
    accident_type: "",
    description: "",
    vehicle_id: "",
    driver_id: "",
    at_fault_name: "",
    at_fault_position: "",
    repair_shop: "",
    invoice_no: "",
    invoice_date: "",
    subtotal_repair_cost: "",
    sales_tax_rate: "13",
    payment_method: "",
    is_paid: "0",
    paid_by: "",
    amount_paid_by_company: "",
    amount_paid_by_insurance: "",
    status: "1",
    notes: "",
});

const pageTitle = computed(() => {
    if (viewMode.value === "detail") return "Accident Report Details";
    if (viewMode.value === "form") {
        return activeId.value ? "Edit Accident Report" : "Accident Report Entry";
    }
    return "Accident Reports List";
});

const heroTitle = computed(() => {
    if (viewMode.value === "detail") return "Accident Report Details";
    if (viewMode.value === "form") {
        return activeId.value
            ? "Update Accident Report"
            : "Create New Accident Report";
    }
    return "Accidents & Damages Management";
});

const heroSubtitle = computed(() => {
    if (viewMode.value === "detail") {
        return "View detailed accident report information";
    }
    if (viewMode.value === "form") {
        return activeId.value
            ? "Review and update accident report information"
            : "Record vehicle accident or damage information";
    }
    return "Manage vehicle accident and damage reports";
});

const userInitials = computed(() => {
    const value = userName.value || "User";
    return value
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || "")
        .join("");
});

const generatedReportCode = computed(() => {
    const year = new Date().getFullYear();
    return `ACC-${year}-${String(items.value.length + 1).padStart(3, "0")}`;
});

const selectedVehicle = computed(() =>
    vehicleOptions.value.find((item) => String(item.id) === form.vehicle_id),
);

const selectedDriver = computed(() =>
    driverOptions.value.find((item) => String(item.id) === form.driver_id),
);

const salesTaxAmount = computed(() => {
    const subtotal = Number(form.subtotal_repair_cost || 0);
    const rate = Number(form.sales_tax_rate || 0);
    return subtotal > 0 ? (subtotal * rate) / 100 : 0;
});

const totalCostAmountForm = computed(
    () => Number(form.subtotal_repair_cost || 0) + salesTaxAmount.value,
);

const totalCostDisplay = computed(() =>
    totalCostAmountForm.value ? totalCostAmountForm.value.toFixed(2) : "",
);

const filteredItems = computed(() => {
    const search = listSearchQuery.value.trim().toLowerCase();

    return items.value.filter((item) => {
        const matchesType =
            !filterAccidentType.value ||
            item.accident_type === filterAccidentType.value;
        const matchesStatus =
            !filterStatus.value || String(item.status) === filterStatus.value;

        if (!matchesType || !matchesStatus) return false;
        if (!search) return true;

        return [
            item.report_code,
            item.vehicle_number,
            item.vehicle_make_model,
            item.driver_name,
            item.location,
            item.accident_type,
            item.status_label,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(search));
    });
});

const sortedItems = computed(() => {
    const direction = sortDirection.value === "asc" ? 1 : -1;
    const key = sortKey.value;

    return [...filteredItems.value].sort((left, right) => {
        const leftValue = normalizeSortValue(left[key], key);
        const rightValue = normalizeSortValue(right[key], key);

        if (leftValue < rightValue) return -1 * direction;
        if (leftValue > rightValue) return 1 * direction;
        return 0;
    });
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(sortedItems.value.length / pageSize)),
);

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return sortedItems.value.slice(start, start + pageSize);
});

const openCount = computed(
    () => filteredItems.value.filter((item) => Number(item.status) === 1).length,
);
const closedCount = computed(
    () => filteredItems.value.filter((item) => Number(item.status) === 2).length,
);
const pendingCount = computed(
    () => filteredItems.value.filter((item) => Number(item.status) === 3).length,
);
const totalCostAmount = computed(() =>
    filteredItems.value.reduce(
        (sum, item) => sum + Number(item.total_cost || 0),
        0,
    ),
);

watch([listSearchQuery, filterAccidentType, filterStatus], () => {
    currentPage.value = 1;
});

watch(totalPages, (value) => {
    if (currentPage.value > value) currentPage.value = value;
});

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
}

function normalizeSortValue(value, key) {
    if (value === null || value === undefined || value === "") {
        return ["report_date", "total_cost"].includes(key) ? 0 : "";
    }

    if (key === "report_date") {
        const timestamp = new Date(value).getTime();
        return Number.isNaN(timestamp) ? 0 : timestamp;
    }

    if (key === "total_cost") return Number(value) || 0;

    return String(value).toLowerCase();
}

function toggleSort(key) {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortKey.value = key;
    sortDirection.value = "asc";
}

function sortIndicator(key) {
    if (sortKey.value !== key) return "<>";
    return sortDirection.value === "asc" ? "^" : "v";
}

function resetForm() {
    clearErrors();
    activeId.value = null;
    detailItem.value = null;
    Object.assign(form, {
        id: "",
        report_code: "",
        report_date: new Date().toISOString().slice(0, 10),
        created_by_name: userName.value,
        incident_report_no: "",
        incident_report_name: "",
        accident_reference: "",
        incident_date: "",
        incident_time: "",
        location: "",
        accident_type: "",
        description: "",
        vehicle_id: "",
        driver_id: "",
        at_fault_name: "",
        at_fault_position: "",
        repair_shop: "",
        invoice_no: "",
        invoice_date: "",
        subtotal_repair_cost: "",
        sales_tax_rate: "13",
        payment_method: "",
        is_paid: "0",
        paid_by: "",
        amount_paid_by_company: "",
        amount_paid_by_insurance: "",
        status: "1",
        notes: "",
    });
}

function applyItemToForm(item) {
    Object.assign(form, {
        id: item.id || "",
        report_code: item.report_code || "",
        report_date: item.report_date || "",
        created_by_name: item.created_by_name || "",
        incident_report_no: item.incident_report_no || "",
        incident_report_name: item.incident_report_name || "",
        accident_reference: item.accident_reference || "",
        incident_date: item.incident_date || "",
        incident_time: item.incident_time || "",
        location: item.location || "",
        accident_type: item.accident_type || "",
        description: item.description || "",
        vehicle_id: item.vehicle_id ? String(item.vehicle_id) : "",
        driver_id: item.driver_id ? String(item.driver_id) : "",
        at_fault_name: item.at_fault_name || "",
        at_fault_position: item.at_fault_position || "",
        repair_shop: item.repair_shop || "",
        invoice_no: item.invoice_no || "",
        invoice_date: item.invoice_date || "",
        subtotal_repair_cost:
            item.subtotal_repair_cost !== null &&
            item.subtotal_repair_cost !== undefined
                ? String(item.subtotal_repair_cost)
                : "",
        sales_tax_rate:
            item.sales_tax_rate !== null && item.sales_tax_rate !== undefined
                ? String(item.sales_tax_rate)
                : "13",
        payment_method: item.payment_method || "",
        is_paid: item.is_paid ? "1" : "0",
        paid_by: item.paid_by || "",
        amount_paid_by_company:
            item.amount_paid_by_company !== null &&
            item.amount_paid_by_company !== undefined
                ? String(item.amount_paid_by_company)
                : "",
        amount_paid_by_insurance:
            item.amount_paid_by_insurance !== null &&
            item.amount_paid_by_insurance !== undefined
                ? String(item.amount_paid_by_insurance)
                : "",
        status: item.status ? String(item.status) : "1",
        notes: item.notes || "",
    });
}

function buildPayload() {
    return {
        report_date: form.report_date || null,
        created_by_name: form.created_by_name || null,
        incident_report_no: form.incident_report_no || null,
        incident_report_name: form.incident_report_name || null,
        accident_reference: form.accident_reference || null,
        incident_date: form.incident_date,
        incident_time: form.incident_time || null,
        location: form.location || null,
        accident_type: form.accident_type,
        description: form.description || null,
        vehicle_id: Number(form.vehicle_id),
        driver_id: form.driver_id ? Number(form.driver_id) : null,
        at_fault_name: form.at_fault_name || null,
        at_fault_position: form.at_fault_position || null,
        repair_shop: form.repair_shop || null,
        invoice_no: form.invoice_no || null,
        invoice_date: form.invoice_date || null,
        subtotal_repair_cost:
            form.subtotal_repair_cost !== ""
                ? Number(form.subtotal_repair_cost)
                : null,
        sales_tax_rate:
            form.sales_tax_rate !== "" ? Number(form.sales_tax_rate) : null,
        payment_method: form.payment_method || null,
        is_paid: form.is_paid === "1",
        paid_by: form.paid_by || null,
        amount_paid_by_company:
            form.amount_paid_by_company !== ""
                ? Number(form.amount_paid_by_company)
                : null,
        amount_paid_by_insurance:
            form.amount_paid_by_insurance !== ""
                ? Number(form.amount_paid_by_insurance)
                : null,
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
    const { data } = await accidentService.getItems({ per_page: 200 });
    items.value = Array.isArray(data?.data?.data) ? data.data.data : [];
}

async function fetchVehicles() {
    const { data } = await vehicleService.getItems({ per_page: 200 });
    const records = Array.isArray(data?.data?.data) ? data.data.data : [];
    vehicleOptions.value = records.map((item) => ({
        id: item.id,
        vehicle_number: item.vehicle_number,
        make_brand: item.make_brand,
        model: item.model,
        plate_number: item.plate_number,
    }));
}

async function fetchDrivers() {
    const { data } = await driverService.getItems({ per_page: 200 });
    const records = Array.isArray(data?.data?.data) ? data.data.data : [];
    driverOptions.value = records.map((item) => ({
        id: item.id,
        driver_name: item.driver_name,
        contact_number: item.contact_number,
    }));
}

async function loadDetail(id) {
    const { data } = await accidentService.getItem(id);
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
    const { data } = await accidentService.getItemForEdit(id);
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
            response = await accidentService.updateItem(activeId.value, payload);
        } else {
            response = await accidentService.createItem(payload);
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
    if (!window.confirm(`Delete report ${item.report_code || ""}?`)) return;

    await accidentService.deleteItem(item.id);

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
        report_code: form.report_code || detailItem.value?.report_code,
    });
}

function printCurrent() {
    window.print();
}

function exportPdf() {
    window.print();
}

function formatDisplayDate(value) {
    if (!value) return "--";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString(undefined, {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function formatLongDate(value) {
    if (!value) return "--";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString(undefined, {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}

function formatMoney(value) {
    if (value === null || value === undefined || value === "") return "--";
    return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 2,
    }).format(Number(value) || 0);
}

function statusClass(status) {
    return {
        "is-open": Number(status) === 1,
        "is-closed": Number(status) === 2,
        "is-pending": Number(status) === 3,
    };
}

function rowNumber(index) {
    return (currentPage.value - 1) * pageSize + index + 1;
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
.pm-accident-page {
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

.pm-accident-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.pm-view-toggle,
.pm-accident-primary-btn {
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
.pm-accident-primary-btn {
    background: #ef1c24;
    border-color: #ef1c24;
    color: #fff;
}

.pm-accident-hero {
    margin-bottom: 1rem;
    padding: 1.7rem 1.8rem;
    border-radius: 18px;
    background: linear-gradient(120deg, #ef1c24 0%, #ff6b71 100%);
    color: #fff;
    box-shadow: 0 20px 45px rgba(239, 28, 36, 0.2);
}

.pm-accident-hero-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pm-accident-hero-icon {
    width: 58px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.14);
}

.pm-accident-hero-icon svg,
.pm-accident-list-icon svg,
.pm-accident-profile-icon svg {
    width: 28px;
    height: 28px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pm-accident-hero h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
}

.pm-accident-hero p {
    margin: 0.35rem 0 0;
    color: rgba(255, 255, 255, 0.88);
}

.pm-accident-card {
    background: #fff;
    border: 1px solid #dce3ef;
    border-radius: 18px;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.05);
}

.pm-accident-list-filter {
    margin-bottom: 1.25rem;
    padding: 1rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 220px 180px;
    gap: 1rem;
}

.pm-accident-search-wrap {
    position: relative;
}

.pm-accident-search-wrap svg {
    position: absolute;
    top: 50%;
    left: 12px;
    width: 18px;
    height: 18px;
    fill: none;
    stroke: #94a3b8;
    stroke-width: 2;
    transform: translateY(-50%);
}

.pm-accident-search-wrap input {
    padding-left: 2.4rem;
}

.pm-filter-select {
    min-height: 46px;
}

.pm-accident-list-card {
    overflow: hidden;
}

.pm-accident-list-head {
    padding: 1.2rem 1.3rem;
    background: linear-gradient(90deg, #fee2e2 0%, #fff1f2 100%);
    border-bottom: 1px solid #fecaca;
}

.pm-accident-list-title {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.pm-accident-list-icon {
    color: #ef1c24;
}

.pm-accident-table {
    margin: 0;
}

.pm-accident-table thead th {
    padding: 1rem 0.8rem;
    color: #111827;
    background: #f8fafc;
    border-bottom-width: 1px;
    white-space: nowrap;
}

.pm-sort-button {
    border: 0;
    padding: 0;
    background: transparent;
    color: inherit;
    font: inherit;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.pm-sort-indicator {
    min-width: 1rem;
    color: #ef1c24;
    font-size: 0.85rem;
    line-height: 1;
}

.pm-accident-table td {
    padding: 0.9rem 0.8rem;
    vertical-align: middle;
}

.pm-accident-link-btn {
    border: 0;
    padding: 0;
    background: transparent;
    color: #ef1c24;
    font-weight: 700;
}

.pm-accident-type-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.28rem 0.72rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #374151;
    background: #f8fafc;
    border: 1px solid #dce3ef;
}

.pm-cost-text {
    font-weight: 700;
    color: #ef1c24;
}

.pm-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.28rem 0.72rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
}

.pm-status-pill.is-open {
    background: #f97316;
}

.pm-status-pill.is-closed {
    background: #22c55e;
}

.pm-status-pill.is-pending {
    background: #ef4444;
}

.pm-table-actions {
    display: inline-flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.pm-icon-action {
    width: 34px;
    height: 34px;
    border: 1px solid #dce3ef;
    border-radius: 10px;
    background: #fff;
    color: #2563eb;
}

.pm-icon-action.edit {
    color: #16a34a;
}

.pm-icon-action.delete {
    color: #ef4444;
}

.pm-icon-action svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pm-accident-stats-grid {
    padding: 1.2rem;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    border-top: 1px solid #eef2f7;
}

.pm-stat-card {
    border-radius: 16px;
    padding: 1rem 1.2rem;
    border: 1px solid;
    text-align: center;
}

.pm-stat-card span {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.pm-stat-card strong {
    font-size: 2rem;
    line-height: 1.1;
}

.pm-stat-card.is-open {
    background: #fff7ed;
    border-color: #fdba74;
    color: #ea580c;
}

.pm-stat-card.is-closed {
    background: #ecfdf5;
    border-color: #86efac;
    color: #16a34a;
}

.pm-stat-card.is-pending {
    background: #fff1f2;
    border-color: #fda4af;
    color: #ef4444;
}

.pm-stat-card.is-total {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #2563eb;
}

.pm-accident-list-footer {
    padding: 1rem 1.2rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.pm-pagination {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
}

.pm-pagination button,
.pm-pagination span {
    border: 1px solid #d7dbe7;
    border-radius: 10px;
    padding: 0.5rem 0.9rem;
    background: #fff;
}

.pm-pagination span {
    background: #ef1c24;
    color: #fff;
    border-color: #ef1c24;
}

.pm-accident-detail-layout,
.pm-accident-form-layout {
    display: grid;
    gap: 1.4rem;
}

.pm-accident-detail-layout {
    grid-template-columns: 320px minmax(0, 1fr);
}

.pm-accident-full-row {
    grid-column: 1 / -1;
}

.pm-accident-profile-card {
    min-height: 320px;
    background: linear-gradient(180deg, #fff1f2 0%, #fff7f7 100%);
    border-color: #fecaca;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 1.5rem;
}

.pm-accident-profile-icon {
    width: 84px;
    height: 84px;
    border-radius: 999px;
    background: #ef1c24;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.pm-accident-profile-icon svg {
    width: 42px;
    height: 42px;
}

.pm-accident-profile-card h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: #991b1b;
}

.pm-accident-profile-card p,
.pm-accident-profile-card small {
    margin: 0.2rem 0 0;
    color: #ef1c24;
}

.pm-accident-total {
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid #fecaca;
    width: 100%;
}

.pm-accident-total span {
    display: block;
    color: #b91c1c;
}

.pm-accident-total strong {
    font-size: 2rem;
    color: #ef1c24;
}

.pm-accident-detail-card,
.pm-accident-form-card,
.pm-accident-action-bar,
.pm-accident-notes-card {
    padding: 1.3rem 1.4rem;
}

.pm-section-header {
    margin-bottom: 1rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #e5e7eb;
    font-size: 1.4rem;
    font-weight: 700;
    color: #ef1c24;
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

.pm-detail-row--notes {
    grid-column: 1 / -1;
}

.pm-detail-row span {
    color: #475569;
    font-weight: 600;
}

.pm-detail-row strong {
    color: #1f2937;
    text-align: right;
}

.pm-accident-detail-bottom {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.4rem;
}

.pm-accident-action-bar,
.pm-accident-form-actions {
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
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

.pm-accident-notes-card p {
    margin: 0;
    color: #334155;
    white-space: pre-wrap;
}

@media (max-width: 1200px) {
    .pm-accident-table {
        min-width: 1120px;
    }
}

@media (max-width: 991px) {
    .pm-page-heading--with-actions,
    .pm-accident-list-filter,
    .pm-accident-detail-layout,
    .pm-accident-detail-bottom,
    .pm-detail-grid,
    .pm-accident-stats-grid {
        grid-template-columns: 1fr;
    }

    .pm-page-heading--with-actions {
        display: grid;
    }

    .pm-accident-toolbar {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}

@media (max-width: 767px) {
    .pm-page-heading h1 {
        font-size: 1.7rem;
    }

    .pm-accident-list-footer,
    .pm-detail-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .pm-detail-row strong {
        text-align: left;
    }
}
</style>
