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
                        placeholder="Search insurance..."
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

            <div class="container pm-ops-page pm-insurance-page">
                <header class="pm-page-heading pm-page-heading--with-actions">
                    <h1>{{ pageTitle }}</h1>
                    <div class="pm-insurance-toolbar">
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
                            class="pm-insurance-primary-btn"
                            type="button"
                            @click="startCreate"
                        >
                            <span>+</span>
                            New Insurance Policy
                        </button>
                    </div>
                </header>

                <section class="pm-insurance-hero">
                    <div class="pm-insurance-hero-content">
                        <div class="pm-insurance-hero-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 3l7 3v5c0 4.2-2.7 8-7 10-4.3-2-7-5.8-7-10V6l7-3Z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h2>{{ heroTitle }}</h2>
                            <p>{{ heroSubtitle }}</p>
                        </div>
                    </div>
                </section>

                <div v-if="viewMode === 'list'" class="pm-insurance-list-top">
                <section class="pm-insurance-stats">
                    <article v-for="card in statCards" :key="card.key" class="pm-insurance-stat-card" :class="card.className">
                        <span>{{ card.label }}</span><strong>{{ card.value }}</strong>
                    </article>
                </section>
                <section
                    v-if="viewMode === 'list'"
                    class="pm-insurance-card pm-insurance-list-filter"
                >
                    <div class="pm-insurance-search-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                            />
                        </svg>
                        <input
                            v-model.trim="listSearchQuery"
                            type="search"
                            class="form-control"
                            placeholder="Search by vehicle, company, policy number..."
                        />
                    </div>
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
                    <select
                        v-model="filterCoverage"
                        class="form-control pm-filter-select"
                    >
                        <option value="">All Policy Types</option>
                        <option
                            v-for="item in coverageOptions"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>
                    </select>
                    <button class="pm-filter-reset" type="button" @click="resetFilters">Reset</button>
                    <button class="pm-filter-apply" type="button" @click="currentPage = 1">Apply Filters</button>
                </section>
                </div>

                <section
                    v-if="viewMode === 'list'"
                    class="pm-insurance-card pm-insurance-list-card"
                >
                    <div class="pm-insurance-list-head">
                        <div class="pm-insurance-list-title">
                            <span class="pm-insurance-list-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M12 3l7 3v5c0 4.2-2.7 8-7 10-4.3-2-7-5.8-7-10V6l7-3Z"
                                    />
                                </svg>
                            </span>
                            Insurance List ({{ filteredItems.length }})
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table pm-insurance-table align-middle">
                            <thead>
                                <tr>
                                    <th>Policy No.</th>
                                    <th>
                                        <button
                                            class="pm-sort-button"
                                            type="button"
                                            @click="toggleSort('vehicle_number')"
                                        >
                                            Vehicle No
                                            <span class="pm-sort-indicator">{{
                                                sortIndicator("vehicle_number")
                                            }}</span>
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-button"
                                            type="button"
                                            @click="
                                                toggleSort('vehicle_make_model')
                                            "
                                        >
                                            Make / Model
                                            <span class="pm-sort-indicator">{{
                                                sortIndicator(
                                                    "vehicle_make_model",
                                                )
                                            }}</span>
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-button"
                                            type="button"
                                            @click="
                                                toggleSort(
                                                    'insurance_company',
                                                )
                                            "
                                        >
                                            Insurance Company
                                            <span class="pm-sort-indicator">{{
                                                sortIndicator(
                                                    "insurance_company",
                                                )
                                            }}</span>
                                        </button>
                                    </th>
                                    <th>Phone</th>
                                    <th>
                                        <button
                                            class="pm-sort-button"
                                            type="button"
                                            @click="toggleSort('policy_number')"
                                        >
                                            Policy No
                                            <span class="pm-sort-indicator">{{
                                                sortIndicator("policy_number")
                                            }}</span>
                                        </button>
                                    </th>
                                    <th>Coverage</th>
                                    <th>
                                        <button
                                            class="pm-sort-button"
                                            type="button"
                                            @click="toggleSort('start_date')"
                                        >
                                            Start Date
                                            <span class="pm-sort-indicator">{{
                                                sortIndicator("start_date")
                                            }}</span>
                                        </button>
                                    </th>
                                    <th>
                                        <button
                                            class="pm-sort-button"
                                            type="button"
                                            @click="toggleSort('expiry_date')"
                                        >
                                            Expiry Date
                                            <span class="pm-sort-indicator">{{
                                                sortIndicator("expiry_date")
                                            }}</span>
                                        </button>
                                    </th>
                                    <th>Premium</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, index) in paginatedItems"
                                    :key="item.id"
                                >
                                    <td><button class="pm-insurance-link-btn" type="button" @click="openDetailView(item)">{{ item.policy_number || item.insurance_code }}</button></td>
                                    <td>
                                        <button
                                            class="pm-insurance-link-btn"
                                            type="button"
                                            @click="openDetailView(item)"
                                        >
                                            {{ item.vehicle_number || "--" }}
                                        </button>
                                    </td>
                                    <td>{{ item.vehicle_make_model || "--" }}</td>
                                    <td>{{ item.insurance_company || "--" }}</td>
                                    <td>{{ item.company_phone || "--" }}</td>
                                    <td>{{ item.policy_number || "--" }}</td>
                                    <td>
                                        <span class="pm-coverage-badge">
                                            {{ item.coverage_type || "--" }}
                                        </span>
                                    </td>
                                    <td>
                                        {{
                                            formatDisplayDate(item.start_date)
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            formatDisplayDate(item.expiry_date)
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            formatMoney(
                                                item.premium_amount,
                                                item.currency || "USD",
                                            )
                                        }}
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
                                    <td colspan="12" class="text-center py-4">
                                        No insurance policies found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pm-insurance-list-footer">
                        <span>
                            Showing {{ filteredItems.length }} of
                            {{ items.length }} insurance records
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
                    class="pm-insurance-detail-layout"
                >
                    <section class="pm-insurance-card pm-insurance-profile-card">
                        <div class="pm-insurance-profile-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 3l7 3v5c0 4.2-2.7 8-7 10-4.3-2-7-5.8-7-10V6l7-3Z"
                                />
                            </svg>
                        </div>
                        <h3>{{ detailItem.vehicle_number || "--" }}</h3>
                        <p>{{ detailItem.vehicle_make_model || "--" }}</p>
                        <small>{{ detailItem.policy_number || "--" }}</small>
                        <span
                            class="pm-status-pill mt-3"
                            :class="statusClass(detailItem.status)"
                        >
                            {{ detailItem.status_label }}
                        </span>
                        <div
                            v-if="detailItem.calendar_reminder"
                            class="pm-inline-meta"
                        >
                            Calendar reminder set
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-detail-card">
                        <div class="pm-section-header">Policy Overview</div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Insurance ID</span>
                                <strong>{{
                                    detailItem.insurance_code || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Policy Number</span>
                                <strong>{{
                                    detailItem.policy_number || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Coverage Type</span>
                                <strong>{{
                                    detailItem.coverage_type || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Premium Amount</span>
                                <strong>{{
                                    formatMoney(
                                        detailItem.premium_amount,
                                        detailItem.currency || "USD",
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

                    <section class="pm-insurance-card pm-insurance-full-row">
                        <div class="pm-section-header">Vehicle Information</div>
                        <div class="pm-detail-two">
                            <div class="pm-detail-row">
                                <span>Vehicle Number</span>
                                <strong>{{
                                    detailItem.vehicle_number || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Make / Model</span>
                                <strong>{{
                                    detailItem.vehicle_make_model || "--"
                                }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-full-row">
                        <div class="pm-section-header">
                            Insurance Company Details
                        </div>
                        <div class="pm-detail-grid">
                            <div class="pm-detail-row">
                                <span>Company Name</span>
                                <strong>{{
                                    detailItem.insurance_company || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Phone Number</span>
                                <strong>{{
                                    detailItem.company_phone || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Email Address</span>
                                <strong>{{
                                    detailItem.company_email || "--"
                                }}</strong>
                            </div>
                            <div class="pm-detail-row">
                                <span>Address</span>
                                <strong>{{
                                    detailItem.company_address || "--"
                                }}</strong>
                            </div>
                        </div>
                    </section>

                    <div class="pm-insurance-detail-bottom">
                        <section class="pm-insurance-card">
                            <div class="pm-section-header">
                                Dates & Coverage
                            </div>
                            <div class="pm-detail-grid">
                                <div class="pm-detail-row">
                                    <span>Start Date</span>
                                    <strong>{{
                                        formatLongDate(
                                            detailItem.start_date,
                                        )
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Expiry Date</span>
                                    <strong>{{
                                        formatLongDate(
                                            detailItem.expiry_date,
                                        )
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Coverage Type</span>
                                    <strong>{{
                                        detailItem.coverage_type || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Coverage Amount</span>
                                    <strong>{{
                                        formatMoney(
                                            detailItem.coverage_amount,
                                            detailItem.currency || "USD",
                                        )
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Deductible</span>
                                    <strong>{{
                                        formatMoney(
                                            detailItem.deductible,
                                            detailItem.currency || "USD",
                                        )
                                    }}</strong>
                                </div>
                            </div>
                        </section>

                        <section class="pm-insurance-card">
                            <div class="pm-section-header">
                                Payment Information
                            </div>
                            <div class="pm-detail-grid">
                                <div class="pm-detail-row">
                                    <span>Premium Amount</span>
                                    <strong>{{
                                        formatMoney(
                                            detailItem.premium_amount,
                                            detailItem.currency || "USD",
                                        )
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Currency</span>
                                    <strong>{{
                                        detailItem.currency || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Payment Frequency</span>
                                    <strong>{{
                                        detailItem.payment_frequency || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-detail-row">
                                    <span>Calendar Reminder</span>
                                    <strong>{{
                                        detailItem.calendar_reminder
                                            ? "Yes"
                                            : "No"
                                    }}</strong>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section
                        v-if="detailItem.notes"
                        class="pm-insurance-card pm-insurance-notes-card pm-insurance-full-row"
                    >
                        <div class="pm-section-header">Notes</div>
                        <p>{{ detailItem.notes }}</p>
                    </section>

                    <section
                        class="pm-insurance-card pm-insurance-action-bar pm-insurance-full-row"
                    >
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
                            Edit Insurance
                        </button>
                    </section>
                </div>

                <div v-else class="pm-insurance-form-layout">
                    <section class="pm-insurance-card pm-insurance-form-card">
                        <div class="pm-section-header">Vehicle Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Insurance ID
                                </label>
                                <input
                                    class="form-control"
                                    :value="
                                        form.insurance_code ||
                                        generatedInsuranceCode
                                    "
                                    readonly
                                />
                            </div>
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
                            <div class="col-md-12">
                                <label class="pm-field-label">
                                    Vehicle Make / Model
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
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-form-card">
                        <div class="pm-section-header">
                            Insurance Company Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Insurance Company
                                    <span class="pm-required-star">*</span>
                                </label>
                                <input
                                    v-model="form.insurance_company"
                                    class="form-control"
                                    placeholder="ABC Insurance"
                                />
                                <div
                                    v-if="errors.insurance_company"
                                    class="pm-form-error"
                                >
                                    {{ errors.insurance_company }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Company Phone
                                </label>
                                <input
                                    v-model="form.company_phone"
                                    class="form-control"
                                    placeholder="01XXXXXXXXX"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Company Email
                                </label>
                                <input
                                    v-model="form.company_email"
                                    type="email"
                                    class="form-control"
                                    placeholder="info@insurance.com"
                                />
                                <div
                                    v-if="errors.company_email"
                                    class="pm-form-error"
                                >
                                    {{ errors.company_email }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Company Address
                                </label>
                                <input
                                    v-model="form.company_address"
                                    class="form-control"
                                    placeholder="123 Insurance Street"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-form-card">
                        <div class="pm-section-header">Policy Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Policy Number
                                    <span class="pm-required-star">*</span>
                                </label>
                                <input
                                    v-model="form.policy_number"
                                    class="form-control"
                                    placeholder="POL123456"
                                />
                                <div
                                    v-if="errors.policy_number"
                                    class="pm-form-error"
                                >
                                    {{ errors.policy_number }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Coverage Type
                                </label>
                                <select
                                    v-model="form.coverage_type"
                                    class="form-control"
                                >
                                    <option value="">
                                        Select coverage type
                                    </option>
                                    <option
                                        v-for="item in coverageOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                                <div
                                    v-if="errors.coverage_type"
                                    class="pm-form-error"
                                >
                                    {{ errors.coverage_type }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Start Date</label>
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Insurance Type</label>
                                <select v-model="form.insurance_type" class="form-control">
                                    <option value="">Select insurance type</option>
                                    <option>Commercial Auto Insurance</option>
                                    <option>Personal Auto Insurance</option>
                                    <option>Fleet Insurance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Expiry Date</label>
                                <input
                                    v-model="form.expiry_date"
                                    type="date"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Coverage Amount
                                </label>
                                <input
                                    v-model="form.coverage_amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="50000"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Deductible</label>
                                <input
                                    v-model="form.deductible"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="1000"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-form-card">
                        <div class="pm-section-header">Payment Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Premium Amount
                                </label>
                                <div class="pm-money-input">
                                    <select
                                        v-model="form.currency"
                                        class="form-control pm-currency-select"
                                    >
                                        <option
                                            v-for="item in currencyOptions"
                                            :key="item"
                                            :value="item"
                                        >
                                            {{ item }}
                                        </option>
                                    </select>
                                    <input
                                        v-model="form.premium_amount"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control"
                                        placeholder="1200"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">
                                    Payment Frequency
                                </label>
                                <select
                                    v-model="form.payment_frequency"
                                    class="form-control"
                                >
                                    <option value="">
                                        Select frequency
                                    </option>
                                    <option
                                        v-for="item in paymentFrequencyOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Charging Date</label>
                                <input v-model="form.charging_date" type="date" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Payment Method</label>
                                <select v-model="form.payment_method" class="form-control">
                                    <option value="">Select payment method</option>
                                    <option>Credit Card</option><option>Bank Transfer</option><option>Cash</option><option>Cheque</option>
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
                            <div class="col-md-6 pm-toggle-col">
                                <label class="pm-field-label">
                                    Reminder
                                </label>
                                <label class="pm-checkbox-row">
                                    <input
                                        v-model="form.calendar_reminder"
                                        type="checkbox"
                                    />
                                    <span>
                                        Add expiry reminder to calendar
                                    </span>
                                </label>
                            </div>
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-form-card">
                        <div class="pm-section-header">Agent Information</div>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="pm-field-label">Agent Name</label><input v-model="form.agent_name" class="form-control" placeholder="Agent name" /></div>
                            <div class="col-md-4"><label class="pm-field-label">Agent Phone</label><input v-model="form.agent_phone" class="form-control" placeholder="+1 (000) 000-0000" /></div>
                            <div class="col-md-4"><label class="pm-field-label">Agent Email</label><input v-model="form.agent_email" type="email" class="form-control" placeholder="agent@insurer.com" /></div>
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-form-card">
                        <div class="pm-section-header">Renewal Reminders</div>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="pm-field-label">30-day reminder</label><input v-model="form.reminder_30" type="date" class="form-control" /></div>
                            <div class="col-md-4"><label class="pm-field-label">21-day reminder</label><input v-model="form.reminder_21" type="date" class="form-control" /></div>
                            <div class="col-md-4"><label class="pm-field-label">2-day reminder</label><input v-model="form.reminder_2" type="date" class="form-control" /></div>
                        </div>
                    </section>

                    <section class="pm-insurance-card pm-insurance-form-card">
                        <div class="pm-section-header">Notes</div>
                        <textarea
                            v-model="form.notes"
                            class="form-control"
                            rows="4"
                            placeholder="Add any additional notes about this insurance policy..."
                        ></textarea>
                    </section>

                    <section
                        class="pm-insurance-card pm-insurance-form-actions"
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
                            {{ saving ? "Saving..." : "Save" }}
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
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            @click="emailCurrent"
                        >
                            Email
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
import insuranceService from "../api/insurance";
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
const filterCoverage = ref("");
const currentPage = ref(1);
const pageSize = 8;
const errors = reactive({});

const statusOptions = [
    { value: 1, label: "Active" },
    { value: 2, label: "Expired" },
    { value: 3, label: "Pending" },
    { value: 4, label: "Cancelled" },
];
const coverageOptions = [
    "Comprehensive",
    "Third-Party",
    "Collision",
    "Liability",
];
const currencyOptions = ["USD", "BDT", "EUR", "GBP"];
const paymentFrequencyOptions = [
    "Monthly",
    "Quarterly",
    "Semi-Annual",
    "Annual",
];
const vehicleOptions = ref([]);
const sortKey = ref("expiry_date");
const sortDirection = ref("asc");

const form = reactive({
    id: "",
    insurance_code: "",
    vehicle_id: "",
    insurance_company: "",
    company_phone: "",
    company_email: "",
    company_address: "",
    policy_number: "",
    coverage_type: "",
    start_date: "",
    expiry_date: "",
    coverage_amount: "",
    deductible: "",
    currency: "USD",
    premium_amount: "",
    payment_frequency: "",
    payment_method: "",
    charging_date: "",
    insurance_type: "",
    agent_name: "",
    agent_phone: "",
    agent_email: "",
    reminder_30: "",
    reminder_21: "",
    reminder_2: "",
    calendar_reminder: true,
    status: "1",
    notes: "",
});

const statCards = computed(() => [
    { key: "total", label: "Total Policies", value: items.value.length, className: "is-total" },
    { key: "active", label: "Active Policies", value: items.value.filter((item) => Number(item.status) === 1).length, className: "is-active" },
    { key: "expiring", label: "Expiring in 30 Days", value: items.value.filter((item) => { const days = Math.ceil((new Date(item.expiry_date) - new Date()) / 86400000); return days >= 0 && days <= 30; }).length, className: "is-expiring" },
    { key: "expired", label: "Expired Policies", value: items.value.filter((item) => Number(item.status) === 2 || new Date(item.expiry_date) < new Date()).length, className: "is-expired" },
]);

const pageTitle = computed(() => {
    if (viewMode.value === "detail") return "Insurance Details";
    if (viewMode.value === "form") {
        return activeId.value ? "Edit Insurance" : "Insurance Entry";
    }
    return "Insurance List";
});

const heroTitle = computed(() => {
    if (viewMode.value === "detail") return "Insurance Details";
    if (viewMode.value === "form") {
        return activeId.value ? "Update Insurance" : "Create New Insurance";
    }
    return "Insurance Management";
});

const heroSubtitle = computed(() => {
    if (viewMode.value === "detail") return "View insurance policy information";
    if (viewMode.value === "form") {
        return activeId.value
            ? "Review and update policy information"
            : "Add a new insurance policy";
    }
    return "Manage vehicle insurance policies";
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

const generatedInsuranceCode = computed(() => {
    const year = new Date().getFullYear();
    return `INS-${year}-${String(items.value.length + 1).padStart(3, "0")}`;
});

const selectedVehicle = computed(() =>
    vehicleOptions.value.find((item) => String(item.id) === form.vehicle_id),
);

const filteredItems = computed(() => {
    const search = listSearchQuery.value.trim().toLowerCase();

    return items.value.filter((item) => {
        const matchesStatus =
            !filterStatus.value || String(item.status) === filterStatus.value;
        const matchesCoverage =
            !filterCoverage.value ||
            item.coverage_type === filterCoverage.value;

        if (!matchesStatus || !matchesCoverage) return false;
        if (!search) return true;

        return [
            item.insurance_code,
            item.vehicle_number,
            item.vehicle_make_model,
            item.insurance_company,
            item.company_phone,
            item.policy_number,
            item.coverage_type,
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

watch([listSearchQuery, filterStatus, filterCoverage], () => {
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
        return ["start_date", "expiry_date", "premium_amount"].includes(key)
            ? 0
            : "";
    }

    if (["start_date", "expiry_date"].includes(key)) {
        const timestamp = new Date(value).getTime();
        return Number.isNaN(timestamp) ? 0 : timestamp;
    }

    if (key === "premium_amount") {
        return Number(value) || 0;
    }

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

function resetFilters() {
    listSearchQuery.value = "";
    filterStatus.value = "";
    filterCoverage.value = "";
    currentPage.value = 1;
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
        insurance_code: "",
        vehicle_id: "",
        insurance_company: "",
        company_phone: "",
        company_email: "",
        company_address: "",
        policy_number: "",
        coverage_type: "",
        start_date: "",
        expiry_date: "",
        coverage_amount: "",
        deductible: "",
        currency: "USD",
        premium_amount: "",
        payment_frequency: "",
        payment_method: "",
        charging_date: "",
        insurance_type: "",
        agent_name: "",
        agent_phone: "",
        agent_email: "",
        reminder_30: "",
        reminder_21: "",
        reminder_2: "",
        calendar_reminder: true,
        status: "1",
        notes: "",
    });
}

function applyItemToForm(item) {
    Object.assign(form, {
        id: item.id || "",
        insurance_code: item.insurance_code || "",
        vehicle_id: item.vehicle_id ? String(item.vehicle_id) : "",
        insurance_company: item.insurance_company || "",
        company_phone: item.company_phone || "",
        company_email: item.company_email || "",
        company_address: item.company_address || "",
        policy_number: item.policy_number || "",
        coverage_type: item.coverage_type || "",
        start_date: item.start_date || "",
        expiry_date: item.expiry_date || "",
        coverage_amount:
            item.coverage_amount !== null && item.coverage_amount !== undefined
                ? String(item.coverage_amount)
                : "",
        deductible:
            item.deductible !== null && item.deductible !== undefined
                ? String(item.deductible)
                : "",
        currency: item.currency || "USD",
        premium_amount:
            item.premium_amount !== null && item.premium_amount !== undefined
                ? String(item.premium_amount)
                : "",
        payment_frequency: item.payment_frequency || "",
        payment_method: item.payment_method || "",
        charging_date: item.charging_date || "",
        insurance_type: item.insurance_type || "",
        agent_name: item.agent_name || "",
        agent_phone: item.agent_phone || "",
        agent_email: item.agent_email || "",
        reminder_30: item.reminders?.reminder_30 || "",
        reminder_21: item.reminders?.reminder_21 || "",
        reminder_2: item.reminders?.reminder_2 || "",
        calendar_reminder: Boolean(item.calendar_reminder),
        status: item.status ? String(item.status) : "1",
        notes: item.notes || "",
        ...(item.policy_data || {}),
    });
}

function buildPayload() {
    return {
        vehicle_id: Number(form.vehicle_id),
        insurance_company: form.insurance_company,
        company_phone: form.company_phone || null,
        company_email: form.company_email || null,
        company_address: form.company_address || null,
        policy_number: form.policy_number,
        coverage_type: form.coverage_type || null,
        start_date: form.start_date || null,
        expiry_date: form.expiry_date || null,
        coverage_amount:
            form.coverage_amount !== "" ? Number(form.coverage_amount) : null,
        deductible: form.deductible !== "" ? Number(form.deductible) : null,
        currency: form.currency || "USD",
        premium_amount:
            form.premium_amount !== "" ? Number(form.premium_amount) : null,
        payment_frequency: form.payment_frequency || null,
        payment_method: form.payment_method || null,
        charging_date: form.charging_date || null,
        insurance_type: form.insurance_type || null,
        agent_name: form.agent_name || null,
        agent_phone: form.agent_phone || null,
        agent_email: form.agent_email || null,
        reminders: { reminder_30: form.reminder_30 || null, reminder_21: form.reminder_21 || null, reminder_2: form.reminder_2 || null },
        calendar_reminder: Boolean(form.calendar_reminder),
        status: Number(form.status || 1),
        notes: form.notes || null,
        policy_data: { ...form },
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
    const { data } = await insuranceService.getItems({ per_page: 200 });
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
        label: `${item.vehicle_number} (${item.make_brand} ${item.model})`,
    }));
}

async function loadDetail(id) {
    const { data } = await insuranceService.getItem(id);
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
    const { data } = await insuranceService.getItemForEdit(id);
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
            response = await insuranceService.updateItem(activeId.value, payload);
        } else {
            response = await insuranceService.createItem(payload);
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
    if (!window.confirm(`Delete policy ${item.policy_number || item.insurance_code}?`)) {
        return;
    }

    await insuranceService.deleteItem(item.id);

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
        policy_number: form.policy_number || detailItem.value?.policy_number,
        insurance_code:
            form.insurance_code || detailItem.value?.insurance_code,
    });
}

function printCurrent() {
    window.print();
}

function exportPdf() {
    window.print();
}

function emailCurrent() {
    const subject = encodeURIComponent(
        `Insurance ${form.policy_number || detailItem.value?.policy_number || ""}`,
    );
    const body = encodeURIComponent(
        [
            `Insurance ID: ${
                form.insurance_code ||
                detailItem.value?.insurance_code ||
                generatedInsuranceCode.value
            }`,
            `Vehicle: ${
                selectedVehicle.value?.label ||
                detailItem.value?.vehicle_number ||
                ""
            }`,
            `Company: ${
                form.insurance_company ||
                detailItem.value?.insurance_company ||
                ""
            }`,
            `Policy Number: ${
                form.policy_number || detailItem.value?.policy_number || ""
            }`,
        ].join("\n"),
    );

    window.location.href = `mailto:?subject=${subject}&body=${body}`;
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

function formatMoney(value, currency = "USD") {
    if (value === null || value === undefined || value === "") return "--";
    return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency,
        maximumFractionDigits: 2,
    }).format(Number(value) || 0);
}

function statusClass(status) {
    return {
        "is-success": Number(status) === 1,
        "is-danger": Number(status) === 2,
        "is-warning": Number(status) === 3,
        "is-neutral": Number(status) === 4,
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
    await Promise.all([fetchItems(), fetchVehicles()]);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.pm-dashboard-main {
    min-width: 0;
    overflow-x: hidden;
}

.pm-insurance-page {
    width: auto;
    max-width: none;
    box-sizing: border-box;
    padding-left: 1.2rem;
    padding-right: 1.2rem;
}

.pm-insurance-list-top {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
}

.pm-insurance-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.pm-insurance-stat-card {
    min-height: 86px;
    padding: 1rem 1.35rem;
    border: 1px solid #e5ebf5;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 12px 28px rgba(15, 23, 42, .04);
}

.pm-insurance-stat-card span { display: block; color: #65748b; font-size: .8rem; margin-bottom: .35rem; }
.pm-insurance-stat-card strong { color: #071b74; font-size: 1.55rem; }
.pm-insurance-stat-card.is-active { border-color: #bbf7d0; }
.pm-insurance-stat-card.is-active strong { color: #16a34a; }
.pm-insurance-stat-card.is-expiring { border-color: #fed7aa; }
.pm-insurance-stat-card.is-expiring strong { color: #f97316; }
.pm-insurance-stat-card.is-expired { border-color: #fecaca; }
.pm-insurance-stat-card.is-expired strong { color: #ef4444; }
.pm-insurance-page {
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

.pm-insurance-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.pm-view-toggle,
.pm-insurance-primary-btn {
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
.pm-insurance-primary-btn {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}

.pm-insurance-hero {
    margin-bottom: 1rem;
    padding: 1.7rem 1.8rem;
    border-radius: 18px;
    background: linear-gradient(120deg, #2563eb 0%, #5aa2f6 100%);
    color: #fff;
    box-shadow: 0 20px 45px rgba(37, 99, 235, 0.18);
}

.pm-insurance-hero-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pm-insurance-hero-icon {
    width: 58px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.16);
}

.pm-insurance-hero-icon svg,
.pm-insurance-list-icon svg,
.pm-insurance-profile-icon svg {
    width: 28px;
    height: 28px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pm-insurance-hero h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
}

.pm-insurance-hero p {
    margin: 0.35rem 0 0;
    color: rgba(255, 255, 255, 0.88);
}

.pm-insurance-card {
    background: #fff;
    border: 1px solid #dce3ef;
    border-radius: 18px;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.05);
}

.pm-insurance-list-filter {
    margin-bottom: 1.25rem;
    padding: 1rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 180px 180px;
    gap: 1rem;
}

.pm-filter-reset,
.pm-filter-apply {
    min-height: 42px;
    padding: 0 .9rem;
    border-radius: 8px;
    font-weight: 700;
    white-space: nowrap;
}

.pm-filter-reset { border: 1px solid #d7e1f5; background: #fff; color: #0c36cb; }
.pm-filter-apply { border: 1px solid #123ff0; background: #123ff0; color: #fff; }

.pm-insurance-search-wrap {
    position: relative;
}

.pm-insurance-search-wrap svg {
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

.pm-insurance-search-wrap input {
    padding-left: 2.4rem;
}

.pm-filter-select {
    min-height: 46px;
}

.pm-insurance-list-card {
    overflow: hidden;
    width: 100%;
    min-width: 0;
    max-width: 100%;
}

.pm-insurance-list-card .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overscroll-behavior-inline: contain;
}

.pm-insurance-list-head {
    padding: 1.2rem 1.3rem;
    background: linear-gradient(90deg, #dbeafe 0%, #eff6ff 100%);
    border-bottom: 1px solid #d7e4ff;
}

.pm-insurance-list-title {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.pm-insurance-list-icon {
    color: #2563eb;
}

.pm-insurance-table {
    margin: 0;
    min-width: 1380px;
}

.pm-insurance-table thead th {
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
    color: #2563eb;
    font-size: 0.85rem;
    line-height: 1;
}

.pm-insurance-table td {
    padding: 0.9rem 0.8rem;
    vertical-align: middle;
}

.pm-insurance-link-btn {
    border: 0;
    padding: 0;
    background: transparent;
    color: #2563eb;
    font-weight: 700;
}

.pm-coverage-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.28rem 0.72rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #dce3ef;
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

.pm-status-pill.is-success {
    background: #22c55e;
}

.pm-status-pill.is-danger {
    background: #ef4444;
}

.pm-status-pill.is-warning {
    background: #f97316;
}

.pm-status-pill.is-neutral {
    background: #64748b;
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

.pm-insurance-list-footer {
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
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}

.pm-insurance-detail-layout,
.pm-insurance-form-layout {
    display: grid;
    gap: 1.4rem;
}

.pm-insurance-detail-layout {
    grid-template-columns: 320px minmax(0, 1fr);
}

.pm-insurance-full-row {
    grid-column: 1 / -1;
}

.pm-insurance-profile-card {
    min-height: 320px;
    background: linear-gradient(180deg, #e8f1ff 0%, #f8fbff 100%);
    border-color: #bfd5ff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 1.5rem;
}

.pm-insurance-profile-icon {
    width: 84px;
    height: 84px;
    border-radius: 999px;
    background: #2563eb;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.pm-insurance-profile-icon svg {
    width: 42px;
    height: 42px;
}

.pm-insurance-profile-card h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: #1e3a8a;
}

.pm-insurance-profile-card p,
.pm-insurance-profile-card small,
.pm-inline-meta {
    margin: 0.2rem 0 0;
    color: #2563eb;
}

.pm-insurance-detail-card,
.pm-insurance-form-card,
.pm-insurance-action-bar,
.pm-insurance-notes-card {
    padding: 1.3rem 1.4rem;
}

.pm-section-header {
    margin-bottom: 1rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #e5e7eb;
    font-size: 1.4rem;
    font-weight: 700;
    color: #2563eb;
}

.pm-detail-grid,
.pm-detail-two {
    display: grid;
    gap: 1rem 1.25rem;
}

.pm-detail-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.pm-detail-two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
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

.pm-insurance-detail-bottom {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.4rem;
}

.pm-insurance-action-bar,
.pm-insurance-form-actions {
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

.pm-money-input {
    display: grid;
    grid-template-columns: 110px minmax(0, 1fr);
    gap: 0.75rem;
}

.pm-toggle-col {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.pm-checkbox-row {
    min-height: 46px;
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    color: #334155;
    font-weight: 600;
}

.pm-checkbox-row input {
    width: 18px;
    height: 18px;
}

.pm-insurance-notes-card p {
    margin: 0;
    color: #334155;
    white-space: pre-wrap;
}

@media (max-width: 1200px) {
    .pm-insurance-table {
        min-width: 1180px;
    }
}

@media (max-width: 991px) {
    .pm-page-heading--with-actions,
    .pm-insurance-list-filter,
    .pm-insurance-detail-layout,
    .pm-insurance-detail-bottom,
    .pm-detail-grid,
    .pm-detail-two,
    .pm-money-input {
        grid-template-columns: 1fr;
    }

    .pm-page-heading--with-actions {
        display: grid;
    }

    .pm-insurance-toolbar {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}

@media (max-width: 767px) {
    .pm-page-heading h1 {
        font-size: 1.7rem;
    }

    .pm-insurance-list-footer,
    .pm-detail-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .pm-detail-row strong {
        text-align: left;
    }
}
</style>
