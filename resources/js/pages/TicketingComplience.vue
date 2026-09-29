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
                        placeholder="Search tickets..."
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

            <div class="container pm-ops-page pm-ticket-page">
                <header class="pm-page-heading pm-page-heading--with-actions">
                    <h1>{{ pageTitle }}</h1>
                    <div class="pm-ticket-toolbar">
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
                            class="pm-ticket-primary-btn"
                            type="button"
                            @click="startCreate"
                        >
                            <span>+</span>
                            New Violation Ticket
                        </button>
                    </div>
                </header>

                <section class="pm-ticket-hero">
                    <div class="pm-ticket-hero-content">
                        <div class="pm-ticket-hero-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v5m0 3h.01" />
                            </svg>
                        </div>
                        <div>
                            <h2>{{ heroTitle }}</h2>
                            <p>{{ heroSubtitle }}</p>
                        </div>
                    </div>
                </section>

                <template v-if="viewMode === 'list'">
                    <section class="pm-ticket-card pm-ticket-filter-panel">
                        <div class="pm-ticket-filter-title">Filters</div>
                        <div class="pm-ticket-filter-grid">
                            <div>
                                <label class="pm-field-label">Driver</label>
                                <select
                                    v-model="filterDriverId"
                                    class="form-control"
                                >
                                    <option value="">All Drivers</option>
                                    <option
                                        v-for="item in driverOptions"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.driver_name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="pm-field-label">Vehicle</label>
                                <select
                                    v-model="filterVehicleId"
                                    class="form-control"
                                >
                                    <option value="">All Vehicles</option>
                                    <option
                                        v-for="item in vehicleOptions"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.vehicle_number }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="pm-field-label">Date Range</label>
                                <div class="pm-ticket-date-range">
                                    <input
                                        v-model="filterDateFrom"
                                        type="date"
                                        class="form-control"
                                    />
                                    <input
                                        v-model="filterDateTo"
                                        type="date"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="pm-field-label">Ticket Type</label>
                                <select
                                    v-model="filterViolationType"
                                    class="form-control"
                                >
                                    <option value="">All Types</option>
                                    <option
                                        v-for="item in violationTypeOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-search-panel">
                        <div class="pm-ticket-search-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                                />
                            </svg>
                            <input
                                v-model.trim="listSearchQuery"
                                type="search"
                                class="form-control"
                                placeholder="Search by ticket, driver, vehicle, violation..."
                            />
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-list-card">
                        <div class="pm-ticket-list-head">
                            <div class="pm-ticket-list-title">
                                <span class="pm-ticket-list-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 8v5m0 3h.01" />
                                    </svg>
                                </span>
                                Traffic Tickets List ({{ filteredItems.length }})
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table pm-ticket-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Ticket No</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Driver</th>
                                        <th>Vehicle</th>
                                        <th>Violation Type</th>
                                        <th>Issued By</th>
                                        <th>Fine</th>
                                        <th>Points</th>
                                        <th>Business/Personal</th>
                                        <th>Paid</th>
                                        <th>Payment Method</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in paginatedItems"
                                        :key="item.id"
                                    >
                                        <td>
                                            <button
                                                class="pm-ticket-link-btn"
                                                type="button"
                                                @click="openDetailView(item)"
                                            >
                                                {{ item.ticket_code || "--" }}
                                            </button>
                                        </td>
                                        <td>{{ formatShortDate(item.issue_date) }}</td>
                                        <td>{{ formatShortDate(item.due_date) }}</td>
                                        <td>{{ item.driver_name || "--" }}</td>
                                        <td>
                                            <strong class="d-block text-primary">
                                                {{ item.vehicle_number || "--" }}
                                            </strong>
                                            <small>{{
                                                item.vehicle_make_model || "--"
                                            }}</small>
                                        </td>
                                        <td>
                                            <span class="pm-ticket-type-badge">
                                                {{ item.violation_type || "--" }}
                                            </span>
                                        </td>
                                        <td>{{ item.issued_by || "--" }}</td>
                                        <td class="pm-ticket-amount">
                                            {{ formatMoney(item.fine_amount) }}
                                        </td>
                                        <td>
                                            <span class="pm-points-pill">
                                                {{ item.points ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="pm-scope-pill"
                                                :class="scopeClass(item.ticket_scope)"
                                            >
                                                {{ item.ticket_scope || "--" }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="pm-paid-pill"
                                                :class="item.is_paid ? 'is-paid' : 'is-unpaid'"
                                            >
                                                {{ item.is_paid ? "Yes" : "No" }}
                                            </span>
                                        </td>
                                        <td>{{ item.payment_method || "--" }}</td>
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
                                        <td colspan="13" class="text-center py-4">
                                            No violation tickets found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-summary-table-card">
                        <div class="pm-ticket-summary-head">
                            Traffic Tickets / Violations Summary
                        </div>
                        <div class="table-responsive">
                            <table class="table pm-ticket-summary-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Vehicle</th>
                                        <th>Ticket Type</th>
                                        <th>Ticket Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in filteredItems"
                                        :key="`summary-${item.id}`"
                                    >
                                        <td>{{ item.driver_name || "--" }}</td>
                                        <td>{{ item.vehicle_make_model || item.vehicle_number || "--" }}</td>
                                        <td>{{ item.violation_type || "--" }}</td>
                                        <td class="pm-ticket-amount">
                                            {{ formatMoney(item.fine_amount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="pm-ticket-stats-grid">
                        <div class="pm-stat-card is-purple">
                            <span>Total Tickets</span>
                            <strong>{{ filteredItems.length }}</strong>
                        </div>
                        <div class="pm-stat-card is-blue">
                            <span>Total Fine Amount</span>
                            <strong>{{ formatMoney(totalFineAmount) }}</strong>
                        </div>
                        <div class="pm-stat-card is-green">
                            <span>Paid Tickets</span>
                            <strong>{{ paidCount }}</strong>
                        </div>
                        <div class="pm-stat-card is-red">
                            <span>Unpaid Tickets</span>
                            <strong>{{ unpaidCount }}</strong>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-export-bar">
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            @click="exportExcel"
                        >
                            Export to Excel
                        </button>
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            @click="exportPdf"
                        >
                            Export to PDF
                        </button>
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            @click="printCurrent"
                        >
                            Print Report
                        </button>
                    </section>

                    <section class="pm-ticket-card pm-ticket-list-footer">
                        <span>
                            Showing {{ filteredItems.length }} of
                            {{ items.length }} violation tickets
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
                    </section>
                </template>

                <div
                    v-else-if="viewMode === 'detail' && detailItem"
                    class="pm-ticket-detail-layout"
                >
                    <section class="pm-ticket-card pm-ticket-profile-card">
                        <div class="pm-ticket-profile-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v5m0 3h.01" />
                            </svg>
                        </div>
                        <h3>{{ detailItem.ticket_code || "--" }}</h3>
                        <p>{{ detailItem.violation_type || "--" }}</p>
                        <small>{{ detailItem.driver_name || "--" }}</small>
                        <span
                            class="pm-paid-pill mt-3"
                            :class="detailItem.is_paid ? 'is-paid' : 'is-unpaid'"
                        >
                            {{ detailItem.is_paid ? "Paid" : "Unpaid" }}
                        </span>
                        <div class="pm-ticket-profile-meta">
                            <span>Fine Amount</span>
                            <strong>{{ formatMoney(detailItem.fine_amount) }}</strong>
                            <span>Points</span>
                            <strong>{{ detailItem.points ?? 0 }}</strong>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-detail-card">
                        <div class="pm-section-header">Ticket Overview</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Ticket No</span>
                                <strong>{{ detailItem.ticket_code || "--" }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Issue Date</span>
                                <strong>{{ formatLongDate(detailItem.issue_date) }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Due Date</span>
                                <strong>{{ formatLongDate(detailItem.due_date) }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Issued By</span>
                                <strong>{{ detailItem.issued_by || "--" }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Business / Personal</span>
                                <strong>{{ detailItem.ticket_scope || "--" }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-full-row">
                        <div class="pm-section-header">Violation Details</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Driver Name</span>
                                <strong>{{ detailItem.driver_name || "--" }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Vehicle Number</span>
                                <strong>{{ detailItem.vehicle_make_model || detailItem.vehicle_number || "--" }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Violation Type</span>
                                <strong>{{ detailItem.violation_type || "--" }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Fine Amount</span>
                                <strong>{{ formatMoney(detailItem.fine_amount) }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Points</span>
                                <strong>{{ detailItem.points ?? 0 }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Status</span>
                                <strong>{{ detailItem.status_label || "--" }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-full-row">
                        <div class="pm-section-header">Payment Information</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Paid</span>
                                <strong>{{ detailItem.is_paid ? "Yes" : "No" }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Payment Method</span>
                                <strong>{{ detailItem.payment_method || "--" }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Paid Date</span>
                                <strong>{{ formatLongDate(detailItem.paid_date) }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-full-row">
                        <div class="pm-section-header">Notes</div>
                        <p class="pm-ticket-notes">
                            {{ detailItem.notes || "--" }}
                        </p>
                    </section>

                    <section class="pm-ticket-card pm-ticket-action-bar pm-ticket-full-row">
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
                            Edit Ticket
                        </button>
                    </section>
                </div>

                <div v-else class="pm-ticket-form-layout">
                    <section class="pm-ticket-card pm-ticket-form-card">
                        <div class="pm-section-header">
                            Violation Ticket Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">Ticket No</label>
                                <input
                                    class="form-control"
                                    :value="form.ticket_code || generatedTicketCode"
                                    readonly
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Issue Date
                                    <span class="pm-required-star">*</span>
                                </label>
                                <input
                                    v-model="form.issue_date"
                                    type="date"
                                    class="form-control"
                                />
                                <div
                                    v-if="errors.issue_date"
                                    class="pm-form-error"
                                >
                                    {{ errors.issue_date }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Due Date</label>
                                <input
                                    v-model="form.due_date"
                                    type="date"
                                    class="form-control"
                                />
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
                                    Violation Type
                                    <span class="pm-required-star">*</span>
                                </label>
                                <select
                                    v-model="form.violation_type"
                                    class="form-control"
                                >
                                    <option value="">
                                        Select violation type
                                    </option>
                                    <option
                                        v-for="item in violationTypeOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                                <div
                                    v-if="errors.violation_type"
                                    class="pm-form-error"
                                >
                                    {{ errors.violation_type }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="pm-field-label">Issued By</label>
                                <input
                                    v-model="form.issued_by"
                                    class="form-control"
                                    placeholder="Traffic Police / Authority"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Fine Amount ($)</label>
                                <input
                                    v-model="form.fine_amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="150"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Points</label>
                                <input
                                    v-model="form.points"
                                    type="number"
                                    min="0"
                                    class="form-control"
                                    placeholder="2"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Business / Personal
                                </label>
                                <select
                                    v-model="form.ticket_scope"
                                    class="form-control"
                                >
                                    <option
                                        v-for="item in ticketScopeOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
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

                    <section class="pm-ticket-card pm-ticket-form-card">
                        <div class="pm-section-header">Payment Information</div>
                        <div class="row g-3">
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
                                <label class="pm-field-label">Payment Method</label>
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
                                <label class="pm-field-label">Paid Date</label>
                                <input
                                    v-model="form.paid_date"
                                    type="date"
                                    class="form-control"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="pm-ticket-card pm-ticket-form-card">
                        <div class="pm-section-header">Notes</div>
                        <textarea
                            v-model="form.notes"
                            class="form-control"
                            rows="4"
                            placeholder="Add any additional notes about this violation ticket..."
                        ></textarea>
                    </section>

                    <section class="pm-ticket-card pm-ticket-form-actions">
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
                            {{ saving ? "Saving..." : "Save Ticket" }}
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
import ticketingComplienceService from "../api/ticketingComplience";
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
const filterDriverId = ref("");
const filterVehicleId = ref("");
const filterDateFrom = ref("");
const filterDateTo = ref("");
const filterViolationType = ref("");
const currentPage = ref(1);
const pageSize = 10;
const errors = reactive({});

const statusOptions = [
    { value: 1, label: "Pending" },
    { value: 2, label: "Paid" },
    { value: 3, label: "Overdue" },
    { value: 4, label: "Disputed" },
];
const violationTypeOptions = [
    "Speeding",
    "Parking Violation",
    "Illegal Parking",
    "Running Red Light",
    "Failure to Signal",
    "Illegal U-turn",
    "Expired Registration",
];
const ticketScopeOptions = ["Business", "Personal"];
const paymentMethodOptions = [
    "Cash",
    "Credit Card",
    "Online",
    "Bank Transfer",
];
const vehicleOptions = ref([]);
const driverOptions = ref([]);

const form = reactive({
    id: "",
    ticket_code: "",
    issue_date: "",
    due_date: "",
    vehicle_id: "",
    driver_id: "",
    violation_type: "",
    issued_by: "",
    fine_amount: "",
    points: "0",
    ticket_scope: "Business",
    status: "1",
    is_paid: "0",
    payment_method: "",
    paid_date: "",
    notes: "",
});

const pageTitle = computed(() => {
    if (viewMode.value === "detail") return "Violation Ticket Details";
    if (viewMode.value === "form") {
        return activeId.value ? "Edit Violation Ticket" : "Violation Ticket Entry";
    }
    return "Violation Tickets List";
});

const heroTitle = computed(() => {
    if (viewMode.value === "detail") return "Violation Ticket Details";
    if (viewMode.value === "form") {
        return activeId.value
            ? "Update Violation Ticket"
            : "Create New Violation Ticket";
    }
    return "Traffic Tickets / Violations Log";
});

const heroSubtitle = computed(() => {
    if (viewMode.value === "detail") {
        return "View detailed violation ticket information";
    }
    if (viewMode.value === "form") {
        return activeId.value
            ? "Review and update violation ticket information"
            : "Record traffic violation or ticket information";
    }
    return "Comprehensive traffic violation tracking and compliance management";
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

const generatedTicketCode = computed(
    () => `TX${String(900101 + items.value.length).padStart(6, "0")}`,
);

const filteredItems = computed(() => {
    const search = listSearchQuery.value.trim().toLowerCase();

    return items.value.filter((item) => {
        const matchesDriver =
            !filterDriverId.value || String(item.driver_id) === filterDriverId.value;
        const matchesVehicle =
            !filterVehicleId.value || String(item.vehicle_id) === filterVehicleId.value;
        const matchesType =
            !filterViolationType.value || item.violation_type === filterViolationType.value;
        const matchesFrom = !filterDateFrom.value || item.issue_date >= filterDateFrom.value;
        const matchesTo = !filterDateTo.value || item.issue_date <= filterDateTo.value;

        if (!matchesDriver || !matchesVehicle || !matchesType || !matchesFrom || !matchesTo) {
            return false;
        }

        if (!search) return true;

        return [
            item.ticket_code,
            item.driver_name,
            item.vehicle_number,
            item.vehicle_make_model,
            item.violation_type,
            item.issued_by,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(search));
    });
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredItems.value.length / pageSize)),
);

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredItems.value.slice(start, start + pageSize);
});

const totalFineAmount = computed(() =>
    filteredItems.value.reduce((sum, item) => sum + Number(item.fine_amount || 0), 0),
);
const paidCount = computed(() => filteredItems.value.filter((item) => item.is_paid).length);
const unpaidCount = computed(() => filteredItems.value.filter((item) => !item.is_paid).length);

watch([listSearchQuery, filterDriverId, filterVehicleId, filterDateFrom, filterDateTo, filterViolationType], () => {
    currentPage.value = 1;
});

watch(totalPages, (value) => {
    if (currentPage.value > value) currentPage.value = value;
});

function clearErrors() {
    Object.keys(errors).forEach((key) => delete errors[key]);
}

function scopeClass(scope) {
    return {
        "is-business": scope === "Business",
        "is-personal": scope === "Personal",
    };
}

function resetForm() {
    clearErrors();
    activeId.value = null;
    detailItem.value = null;
    Object.assign(form, {
        id: "",
        ticket_code: "",
        issue_date: new Date().toISOString().slice(0, 10),
        due_date: "",
        vehicle_id: "",
        driver_id: "",
        violation_type: "",
        issued_by: "",
        fine_amount: "",
        points: "0",
        ticket_scope: "Business",
        status: "1",
        is_paid: "0",
        payment_method: "",
        paid_date: "",
        notes: "",
    });
}

function applyItemToForm(item) {
    Object.assign(form, {
        id: item.id || "",
        ticket_code: item.ticket_code || "",
        issue_date: item.issue_date || "",
        due_date: item.due_date || "",
        vehicle_id: item.vehicle_id ? String(item.vehicle_id) : "",
        driver_id: item.driver_id ? String(item.driver_id) : "",
        violation_type: item.violation_type || "",
        issued_by: item.issued_by || "",
        fine_amount:
            item.fine_amount !== null && item.fine_amount !== undefined
                ? String(item.fine_amount)
                : "",
        points: item.points !== null && item.points !== undefined ? String(item.points) : "0",
        ticket_scope: item.ticket_scope || "Business",
        status: item.status ? String(item.status) : "1",
        is_paid: item.is_paid ? "1" : "0",
        payment_method: item.payment_method || "",
        paid_date: item.paid_date || "",
        notes: item.notes || "",
    });
}

function buildPayload() {
    return {
        issue_date: form.issue_date,
        due_date: form.due_date || null,
        vehicle_id: Number(form.vehicle_id),
        driver_id: Number(form.driver_id),
        violation_type: form.violation_type,
        issued_by: form.issued_by || null,
        fine_amount: form.fine_amount !== "" ? Number(form.fine_amount) : null,
        points: form.points !== "" ? Number(form.points) : 0,
        ticket_scope: form.ticket_scope || "Business",
        status: Number(form.status || 1),
        is_paid: form.is_paid === "1",
        payment_method: form.payment_method || null,
        paid_date: form.paid_date || null,
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
    const { data } = await ticketingComplienceService.getItems({ per_page: 200 });
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
    const { data } = await ticketingComplienceService.getItem(id);
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
    const { data } = await ticketingComplienceService.getItemForEdit(id);
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
            response = await ticketingComplienceService.updateItem(activeId.value, payload);
        } else {
            response = await ticketingComplienceService.createItem(payload);
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
    if (!window.confirm(`Delete ticket ${item.ticket_code || ""}?`)) return;
    await ticketingComplienceService.deleteItem(item.id);
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
        ticket_code: form.ticket_code || detailItem.value?.ticket_code,
    });
}

function printCurrent() {
    window.print();
}

function exportPdf() {
    window.print();
}

function exportExcel() {
    window.print();
}

function formatMoney(value) {
    if (value === null || value === undefined || value === "") return "--";
    return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 2,
    }).format(Number(value) || 0);
}

function formatShortDate(value) {
    if (!value) return "--";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    const month = date.getMonth() + 1;
    const day = date.getDate();
    const year = String(date.getFullYear()).slice(-2);
    return `${month}/${day}/${year}`;
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
.pm-ticket-page {
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

.pm-ticket-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.pm-view-toggle,
.pm-ticket-primary-btn {
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
.pm-ticket-primary-btn {
    background: #ef1c24;
    border-color: #ef1c24;
    color: #fff;
}

.pm-ticket-hero {
    margin-bottom: 1rem;
    padding: 1.7rem 1.8rem;
    border-radius: 18px;
    background: linear-gradient(120deg, #ef1c24 0%, #ff5f67 100%);
    color: #fff;
    box-shadow: 0 20px 45px rgba(239, 28, 36, 0.2);
}

.pm-ticket-hero-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pm-ticket-hero-icon {
    width: 58px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.14);
}

.pm-ticket-hero-icon svg,
.pm-ticket-list-icon svg,
.pm-ticket-profile-icon svg {
    width: 28px;
    height: 28px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pm-ticket-hero h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
}

.pm-ticket-hero p {
    margin: 0.35rem 0 0;
    color: rgba(255, 255, 255, 0.88);
}

.pm-ticket-card {
    background: #fff;
    border: 1px solid #dce3ef;
    border-radius: 18px;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.05);
}

.pm-ticket-filter-panel,
.pm-ticket-search-panel,
.pm-ticket-list-card,
.pm-ticket-summary-table-card,
.pm-ticket-export-bar,
.pm-ticket-list-footer {
    margin-bottom: 1.25rem;
}

.pm-ticket-filter-panel {
    overflow: hidden;
}

.pm-ticket-filter-title {
    padding: 1rem 1.2rem;
    background: linear-gradient(90deg, #fff1f2 0%, #fee2e2 100%);
    border-bottom: 1px solid #fecaca;
    color: #ef1c24;
    font-weight: 700;
}

.pm-ticket-filter-grid {
    padding: 1.2rem;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.pm-ticket-date-range {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.pm-ticket-search-panel {
    padding: 0.9rem 1rem;
}

.pm-ticket-search-wrap {
    position: relative;
}

.pm-ticket-search-wrap svg {
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

.pm-ticket-search-wrap input {
    padding-left: 2.4rem;
}

.pm-ticket-list-card,
.pm-ticket-summary-table-card {
    overflow: hidden;
}

.pm-ticket-list-head {
    padding: 1.2rem 1.3rem;
    background: linear-gradient(90deg, #fff1f2 0%, #fee2e2 100%);
    border-bottom: 1px solid #fecaca;
}

.pm-ticket-list-title {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.pm-ticket-list-icon {
    color: #ef1c24;
}

.pm-ticket-table,
.pm-ticket-summary-table {
    margin: 0;
}

.pm-ticket-table thead th,
.pm-ticket-summary-table thead th {
    padding: 1rem 0.8rem;
    color: #111827;
    background: #f8fafc;
    border-bottom-width: 1px;
    white-space: nowrap;
}

.pm-ticket-table td,
.pm-ticket-summary-table td {
    padding: 0.9rem 0.8rem;
    vertical-align: middle;
}

.pm-ticket-link-btn {
    border: 0;
    padding: 0;
    background: transparent;
    color: #ef1c24;
    font-weight: 700;
}

.pm-ticket-type-badge {
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

.pm-ticket-amount {
    font-weight: 700;
    color: #ef1c24;
}

.pm-points-pill {
    display: inline-flex;
    min-width: 26px;
    justify-content: center;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    background: #e5e7eb;
    color: #475569;
    font-weight: 700;
}

.pm-scope-pill,
.pm-paid-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.28rem 0.72rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
}

.pm-scope-pill.is-business {
    background: #111827;
    color: #fff;
}

.pm-scope-pill.is-personal {
    background: #f3f4f6;
    color: #4b5563;
}

.pm-paid-pill.is-paid {
    background: #22c55e;
    color: #fff;
}

.pm-paid-pill.is-unpaid {
    background: #ef4444;
    color: #fff;
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

.pm-ticket-summary-head {
    padding: 1rem 1.2rem;
    background: linear-gradient(90deg, #dbeafe 0%, #eff6ff 100%);
    border-bottom: 1px solid #bfdbfe;
    color: #2563eb;
    font-weight: 700;
}

.pm-ticket-stats-grid {
    margin-bottom: 1.25rem;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.pm-stat-card {
    border-radius: 16px;
    padding: 1rem 1.2rem;
    border: 1px solid;
    text-align: center;
    background: #fff;
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

.pm-stat-card.is-purple {
    border-color: #d8b4fe;
    background: #faf5ff;
    color: #9333ea;
}

.pm-stat-card.is-blue {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #2563eb;
}

.pm-stat-card.is-green {
    border-color: #86efac;
    background: #ecfdf5;
    color: #16a34a;
}

.pm-stat-card.is-red {
    border-color: #fca5a5;
    background: #fef2f2;
    color: #ef4444;
}

.pm-ticket-export-bar,
.pm-ticket-list-footer,
.pm-ticket-action-bar,
.pm-ticket-form-actions {
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    align-items: center;
}

.pm-ticket-list-footer {
    justify-content: space-between;
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

.pm-ticket-detail-layout,
.pm-ticket-form-layout {
    display: grid;
    gap: 1.4rem;
}

.pm-ticket-detail-layout {
    grid-template-columns: 320px minmax(0, 1fr);
}

.pm-ticket-full-row {
    grid-column: 1 / -1;
}

.pm-ticket-profile-card {
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

.pm-ticket-profile-icon {
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

.pm-ticket-profile-icon svg {
    width: 42px;
    height: 42px;
}

.pm-ticket-profile-card h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: #991b1b;
}

.pm-ticket-profile-card p,
.pm-ticket-profile-card small {
    margin: 0.2rem 0 0;
    color: #ef1c24;
}

.pm-ticket-profile-meta {
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid #fecaca;
    width: 100%;
}

.pm-ticket-profile-meta span,
.pm-ticket-profile-meta strong {
    display: block;
}

.pm-ticket-profile-meta span {
    color: #b91c1c;
}

.pm-ticket-profile-meta strong {
    margin-bottom: 0.45rem;
    font-size: 1.1rem;
    color: #ef1c24;
}

.pm-ticket-detail-card,
.pm-ticket-form-card {
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

.pm-detail-row span {
    color: #475569;
    font-weight: 600;
}

.pm-detail-row strong {
    color: #1f2937;
    text-align: right;
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

.pm-ticket-notes {
    margin: 0;
    color: #334155;
    white-space: pre-wrap;
}

@media (max-width: 1200px) {
    .pm-ticket-table {
        min-width: 1420px;
    }
}

@media (max-width: 991px) {
    .pm-page-heading--with-actions,
    .pm-ticket-filter-grid,
    .pm-ticket-date-range,
    .pm-ticket-stats-grid,
    .pm-ticket-detail-layout,
    .pm-detail-grid {
        grid-template-columns: 1fr;
    }

    .pm-page-heading--with-actions {
        display: grid;
    }

    .pm-ticket-toolbar {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}

@media (max-width: 767px) {
    .pm-page-heading h1 {
        font-size: 1.7rem;
    }

    .pm-ticket-list-footer,
    .pm-detail-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .pm-detail-row strong {
        text-align: left;
    }
}
</style>
