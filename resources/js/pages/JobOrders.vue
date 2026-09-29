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
                        placeholder="Search job orders..."
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
                <div class="container pm-ops-page pm-job-order-page">
                    <section v-if="viewMode !== 'form'" class="pm-account-holder-hero">
                        <div>
                            <div class="pm-account-holder-kicker">
                                Job Order Module
                            </div>
                            <h2 class="pm-account-holder-title">Job Orders</h2>
                            <div
                                class="pm-page-subtitle pm-account-holder-subtitle"
                            >
                                Dashboard &gt; Job Orders &gt;
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

                        <div class="pm-account-holder-hero-actions">
                            <button
                                v-if="viewMode === 'detail'"
                                class="pm-hero-action-button pm-hero-action-button--primary"
                                type="button"
                                @click="startEdit(detailItem)"
                            >
                                <span
                                    class="pm-hero-action-icon"
                                    aria-hidden="true"
                                    >&crarr;</span
                                >
                                <span>Edit</span>
                            </button>
                            <span class="pm-account-holder-chip">{{
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
                                    <span
                                        class="pm-hero-action-icon"
                                        aria-hidden="true"
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
                                    <span
                                        class="pm-hero-action-icon pm-hero-action-icon--list"
                                        aria-hidden="true"
                                    >
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </span>
                                    <span>List</span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <template v-if="viewMode === 'list'">
                        <section class="pm-job-order-metric-grid">
                            <article class="pm-job-order-metric-card">
                                <div class="pm-job-order-metric-icon is-blue">
                                    &#128188;
                                </div>
                                <div>
                                    <div class="pm-job-order-metric-label">
                                        Total Job Orders
                                    </div>
                                    <div class="pm-job-order-metric-value">
                                        {{ filteredItems.length }}
                                    </div>
                                </div>
                            </article>
                            <article class="pm-job-order-metric-card">
                                <div class="pm-job-order-metric-icon is-green">
                                    $
                                </div>
                                <div>
                                    <div class="pm-job-order-metric-label">
                                        Total Value
                                    </div>
                                    <div class="pm-job-order-metric-value">
                                        ${{ formatMoney(totalValue) }}
                                    </div>
                                </div>
                            </article>
                            <article class="pm-job-order-metric-card">
                                <div class="pm-job-order-metric-icon is-sky">
                                    &#9654;
                                </div>
                                <div>
                                    <div class="pm-job-order-metric-label">
                                        In Progress
                                    </div>
                                    <div class="pm-job-order-metric-value">
                                        {{ inProgressCount }}
                                    </div>
                                </div>
                            </article>
                            <article class="pm-job-order-metric-card">
                                <div class="pm-job-order-metric-icon is-emerald">
                                    &#10003;
                                </div>
                                <div>
                                    <div class="pm-job-order-metric-label">
                                        Completed
                                    </div>
                                    <div class="pm-job-order-metric-value">
                                        {{ completedCount }}
                                    </div>
                                </div>
                            </article>
                        </section>

                        <section
                            class="pm-card pm-ops-card p-4 pm-account-holder-list-shell"
                        >
                            <div class="pm-account-holder-list-toolbar">
                                <div>
                                    <h5 class="pm-form-title">Job Order List</h5>
                                    <div class="pm-account-holder-list-meta">
                                        Showing {{ filteredItems.length }} of
                                        {{ listItems.length }} job orders
                                    </div>
                                </div>
                                <div class="pm-account-holder-list-search-row">
                                    <div class="pm-account-holder-list-search">
                                        <input
                                            v-model.trim="listSearchQuery"
                                            class="form-control"
                                            type="search"
                                            placeholder="Search by job order no, customer, or project..."
                                        />
                                    </div>
                                    <div class="pm-filter-row">
                                        <select
                                            v-model="filterStatus"
                                            class="form-control pm-filter-select"
                                        >
                                            <option value="">All Status</option>
                                            <option
                                                v-for="status in statusOptions"
                                                :key="status"
                                                :value="status"
                                            >
                                                {{ status }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="table-responsive pm-account-holder-table-wrap"
                            >
                                <table
                                    class="table pm-account-holder-table align-middle"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Job Order No</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Customer</th>
                                            <th scope="col">Project</th>
                                            <th scope="col">Schedule</th>
                                            <th scope="col">Progress</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredItems.length">
                                        <tr
                                            v-for="item in filteredItems"
                                            :key="item.id"
                                        >
                                            <td>
                                                <div
                                                    class="pm-account-holder-table-code"
                                                >
                                                    {{ item.job_order_no }}
                                                </div>
                                                <div
                                                    class="pm-job-order-source-tag"
                                                    v-if="
                                                        item.quotation ||
                                                        item.estimation
                                                    "
                                                >
                                                    From
                                                    {{
                                                        item.quotation
                                                            ?.quotation_no ||
                                                        item.estimation
                                                            ?.estimation_no
                                                    }}
                                                </div>
                                            </td>
                                            <td>
                                                {{ formatDate(item.issue_date) }}
                                            </td>
                                            <td>
                                                <div
                                                    class="pm-account-holder-table-primary"
                                                >
                                                    {{
                                                        item.customer
                                                            ?.company_name ||
                                                        item.customer?.name ||
                                                        "--"
                                                    }}
                                                </div>
                                                <div
                                                    class="pm-account-holder-table-secondary"
                                                >
                                                    {{
                                                        item.customer?.contact ||
                                                        "--"
                                                    }}
                                                </div>
                                            </td>
                                            <td>
                                                {{
                                                    item.job_description || "--"
                                                }}
                                            </td>
                                            <td>
                                                <div
                                                    class="pm-account-holder-table-secondary"
                                                >
                                                    {{
                                                        formatDate(
                                                            item.schedule_start_date,
                                                        )
                                                    }}
                                                    -
                                                    {{
                                                        formatDate(
                                                            item.schedule_end_date,
                                                        )
                                                    }}
                                                </div>
                                            </td>
                                            <td>
                                                <div
                                                    class="pm-job-order-progress"
                                                >
                                                    <span
                                                        >{{
                                                            item.progress || 0
                                                        }}%</span
                                                    >
                                                    <div
                                                        class="pm-job-order-progress-bar"
                                                    >
                                                        <span
                                                            :style="{
                                                                width: `${item.progress || 0}%`,
                                                            }"
                                                        ></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="pm-job-order-total-green"
                                            >
                                                ${{
                                                    formatMoney(item.total)
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    class="pm-account-holder-status"
                                                    :class="
                                                        statusBadgeClass(
                                                            item.status_label,
                                                        )
                                                    "
                                                >
                                                    {{
                                                        item.status_label ||
                                                        "--"
                                                    }}
                                                </span>
                                            </td>
                                            <td>
                                                <div
                                                    class="pm-account-holder-table-actions"
                                                >
                                                    <button
                                                        class="btn btn-outline-primary btn-sm"
                                                        type="button"
                                                        @click="startView(item)"
                                                    >
                                                        View
                                                    </button>
                                                    <button
                                                        class="btn btn-outline-secondary btn-sm"
                                                        type="button"
                                                        @click="startEdit(item)"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        class="btn btn-outline-danger btn-sm"
                                                        type="button"
                                                        @click="
                                                            quickDelete(item.id)
                                                        "
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                No job orders found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </template>

                    <section
                        v-else-if="viewMode === 'detail' && detailItem"
                        class="pm-card pm-ops-card p-4"
                    >
                        <div class="pm-job-order-detail-head">
                            <div>
                                <h4 class="pm-form-title">
                                    {{ detailItem.job_order_no }}
                                </h4>
                                <div class="pm-page-subtitle">
                                    {{ detailItem.job_description || "No project description" }}
                                </div>
                            </div>
                            <span
                                class="pm-account-holder-status"
                                :class="
                                    statusBadgeClass(detailItem.status_label)
                                "
                            >
                                {{ detailItem.status_label }}
                            </span>
                        </div>

                        <div class="pm-job-order-detail-grid">
                            <div class="pm-job-order-detail-card">
                                <h6>Customer</h6>
                                <p>
                                    {{
                                        detailItem.customer?.company_name ||
                                        detailItem.customer?.name ||
                                        "--"
                                    }}
                                </p>
                                <span>{{
                                    detailItem.customer?.address || "--"
                                }}</span>
                            </div>
                            <div class="pm-job-order-detail-card">
                                <h6>Schedule</h6>
                                <p>
                                    {{
                                        formatDateTime(
                                            detailItem.schedule_start_date,
                                        )
                                    }}
                                </p>
                                <span>{{
                                    formatDateTime(
                                        detailItem.schedule_end_date,
                                    )
                                }}</span>
                            </div>
                            <div class="pm-job-order-detail-card">
                                <h6>Payment</h6>
                                <p>
                                    {{ detailItem.payment_status_label }}
                                </p>
                                <span
                                    >Outstanding ${{
                                        formatMoney(
                                            detailItem.outstanding_balance,
                                        )
                                    }}</span
                                >
                            </div>
                            <div class="pm-job-order-detail-card">
                                <h6>Total</h6>
                                <p>${{ formatMoney(detailItem.total) }}</p>
                                <span
                                    >Progress
                                    {{ detailItem.progress || 0 }}%</span
                                >
                            </div>
                        </div>

                        <div class="pm-job-order-detail-block">
                            <h6>Scope of Work</h6>
                            <p>{{ detailItem.scope_of_work || "--" }}</p>
                        </div>

                        <div class="table-responsive">
                            <table class="table pm-account-holder-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="row in detailItem.details || []"
                                        :key="row.id"
                                    >
                                        <td>{{ row.item_name }}</td>
                                        <td>{{ row.item_description || "--" }}</td>
                                        <td>{{ row.quantity }}</td>
                                        <td>${{ formatMoney(row.unit_price) }}</td>
                                        <td>
                                            ${{
                                                formatMoney(
                                                    row.sale_tax_amount,
                                                )
                                            }}
                                        </td>
                                        <td>${{ formatMoney(row.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <template v-else-if="false">
                        <section class="pm-job-order-form-section">
                            <div class="pm-job-order-section-head">
                                Job Order Information
                            </div>
                            <div class="pm-job-order-section-body">
                                <div class="pm-job-order-grid">
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Job Order No</label
                                        >
                                        <input
                                            :value="
                                                jobOrderNumber ||
                                                'Auto generate after save'
                                            "
                                            class="form-control"
                                            readonly
                                            type="text"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Job Order Date</label
                                        >
                                        <input
                                            v-model="form.issueDate"
                                            class="form-control"
                                            type="date"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Converted From Quotation</label
                                        >
                                        <select
                                            v-model="form.quotationId"
                                            class="form-control"
                                            @change="
                                                handleQuotationSourceChange
                                            "
                                        >
                                            <option :value="null">
                                                Select quotation
                                            </option>
                                            <option
                                                v-for="item in quotations"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.quotation_no }} -
                                                {{ item.job_description }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Converted From Estimation</label
                                        >
                                        <select
                                            v-model="form.estimationId"
                                            class="form-control"
                                            @change="
                                                handleEstimationSourceChange
                                            "
                                        >
                                            <option :value="null">
                                                Select estimation
                                            </option>
                                            <option
                                                v-for="item in estimations"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.estimation_no }} -
                                                {{ item.job_description }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div class="pm-job-order-section-head">
                                Job Status
                            </div>
                            <div class="pm-job-order-section-body">
                                <div class="pm-job-order-status-list">
                                    <button
                                        v-for="status in statusOptions"
                                        :key="status"
                                        class="pm-job-order-status-pill"
                                        :class="{
                                            'is-active':
                                                form.status === status,
                                        }"
                                        type="button"
                                        @click="form.status = status"
                                    >
                                        {{ status }}
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div class="pm-job-order-section-head">
                                Customer Information
                            </div>
                            <div class="pm-job-order-section-body">
                                <div class="pm-job-order-grid">
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Customer Name</label
                                        >
                                        <select
                                            v-model="form.customerId"
                                            class="form-control"
                                            @change="handleCustomerChange"
                                        >
                                            <option :value="null">
                                                Select customer
                                            </option>
                                            <option
                                                v-for="item in customers"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.account_name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Company Name</label
                                        >
                                        <input
                                            v-model="form.customerCompany"
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Contact Number</label
                                        >
                                        <input
                                            v-model="form.customerContact"
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Email</label
                                        >
                                        <input
                                            v-model="form.customerEmail"
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                    <div
                                        class="pm-field-block pm-field-block--full"
                                    >
                                        <label class="pm-field-label"
                                            >Billing Address</label
                                        >
                                        <input
                                            v-model="form.customerAddress"
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div class="pm-job-order-section-head">
                                Job Site Information
                            </div>
                            <div class="pm-job-order-section-body">
                                <div class="pm-job-order-grid">
                                    <div
                                        class="pm-field-block pm-field-block--full"
                                    >
                                        <label class="pm-field-label"
                                            >Job Type</label
                                        >
                                        <div class="pm-choice-inline">
                                            <label
                                                v-for="type in jobTypeOptions"
                                                :key="type"
                                                class="pm-choice-item"
                                            >
                                                <input
                                                    v-model="form.jobTypes"
                                                    :value="type"
                                                    type="checkbox"
                                                />
                                                <span>{{ type }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Job Site</label
                                        >
                                        <select
                                            v-model="form.jobSiteId"
                                            class="form-control"
                                            @change="handleJobSiteChange"
                                        >
                                            <option :value="null">
                                                Select job site
                                            </option>
                                            <option
                                                v-for="item in jobSites"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.job_site_name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Map Link</label
                                        >
                                        <input
                                            v-model="form.mapLink"
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                    <div
                                        class="pm-field-block pm-field-block--full"
                                    >
                                        <label class="pm-field-label"
                                            >Job Site Address</label
                                        >
                                        <input
                                            v-model="form.jobSiteAddress"
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Site Contact Person</label
                                        >
                                        <input
                                            v-model="
                                                form.siteContactPerson
                                            "
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Site Contact Number</label
                                        >
                                        <input
                                            v-model="
                                                form.siteContactNumber
                                            "
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div class="pm-job-order-section-head">Schedule</div>
                            <div class="pm-job-order-section-body">
                                <div class="pm-job-order-grid">
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Start Date & Time</label
                                        >
                                        <input
                                            v-model="form.startAt"
                                            class="form-control"
                                            type="datetime-local"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >End Date & Time</label
                                        >
                                        <input
                                            v-model="form.endAt"
                                            class="form-control"
                                            type="datetime-local"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Progress (%)</label
                                        >
                                        <input
                                            v-model.number="form.progress"
                                            class="form-control"
                                            max="100"
                                            min="0"
                                            type="number"
                                        />
                                    </div>
                                </div>
                                <div class="pm-job-order-highlight">
                                    <div class="pm-job-order-highlight-label">
                                        Net Duration
                                    </div>
                                    <strong>{{ netDurationLabel }}</strong>
                                </div>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div class="pm-job-order-section-head">
                                Scope of Work
                            </div>
                            <div class="pm-job-order-section-body">
                                <textarea
                                    v-model="form.jobDescription"
                                    class="form-control mb-3"
                                    placeholder="Project or job description..."
                                    rows="3"
                                ></textarea>
                                <textarea
                                    v-model="form.scopeOfWork"
                                    class="form-control"
                                    placeholder="Describe the scope of work..."
                                    rows="5"
                                ></textarea>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div
                                class="pm-job-order-section-head pm-job-order-section-head--between"
                            >
                                <span>Cost Breakdown</span>
                                <button
                                    class="pm-job-order-add-row"
                                    type="button"
                                    @click="addCostRow"
                                >
                                    Add Row
                                </button>
                            </div>
                            <div class="pm-job-order-section-body">
                                <div class="table-responsive">
                                    <table
                                        class="table pm-account-holder-table align-middle"
                                    >
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Item</th>
                                                <th>Description</th>
                                                <th>Qty</th>
                                                <th>Unit Price</th>
                                                <th>Tax %</th>
                                                <th>Tax Amount</th>
                                                <th>Line Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(row, index) in costRows"
                                                :key="row.id"
                                            >
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    <input
                                                        v-model="row.item"
                                                        class="form-control form-control-sm"
                                                        type="text"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model="
                                                            row.description
                                                        "
                                                        class="form-control form-control-sm"
                                                        type="text"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model.number="
                                                            row.qty
                                                        "
                                                        class="form-control form-control-sm"
                                                        min="0"
                                                        step="1"
                                                        type="number"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model.number="
                                                            row.unitPrice
                                                        "
                                                        class="form-control form-control-sm"
                                                        min="0"
                                                        step="0.01"
                                                        type="number"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model.number="
                                                            row.salesTax
                                                        "
                                                        class="form-control form-control-sm"
                                                        min="0"
                                                        step="0.01"
                                                        type="number"
                                                    />
                                                </td>
                                                <td>
                                                    ${{
                                                        formatMoney(
                                                            calcRowSalesTaxAmount(
                                                                row,
                                                            ),
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    ${{
                                                        formatMoney(
                                                            calcRowLineTotal(
                                                                row,
                                                            ),
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <button
                                                        class="pm-job-order-delete-row"
                                                        type="button"
                                                        @click="
                                                            removeCostRow(
                                                                row.id,
                                                            )
                                                        "
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="pm-job-order-summary">
                                    <div class="pm-job-order-summary-row">
                                        <span>Subtotal</span>
                                        <strong
                                            >${{
                                                formatMoney(subtotal)
                                            }}</strong
                                        >
                                    </div>
                                    <div class="pm-job-order-summary-row">
                                        <span>Total Sales Tax</span>
                                        <strong
                                            >${{
                                                formatMoney(rowTaxTotal)
                                            }}</strong
                                        >
                                    </div>
                                    <div
                                        class="pm-job-order-summary-row pm-job-order-summary-row--total"
                                    >
                                        <span>Total Job Order Amount</span>
                                        <strong
                                            >${{
                                                formatMoney(grandTotal)
                                            }}</strong
                                        >
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div class="pm-job-order-section-head">
                                Payment Tracking
                            </div>
                            <div class="pm-job-order-section-body">
                                <div class="pm-job-order-grid">
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Deposit Received</label
                                        >
                                        <input
                                            v-model.number="
                                                form.depositReceived
                                            "
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            type="number"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Amount Paid</label
                                        >
                                        <input
                                            v-model.number="form.amountPaid"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            type="number"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Outstanding Balance</label
                                        >
                                        <input
                                            :value="
                                                formatMoney(
                                                    outstandingBalance,
                                                )
                                            "
                                            class="form-control"
                                            readonly
                                            type="text"
                                        />
                                    </div>
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Payment Method</label
                                        >
                                        <div class="pm-choice-inline">
                                            <label
                                                v-for="method in paymentMethodOptions"
                                                :key="method"
                                                class="pm-choice-item"
                                            >
                                                <input
                                                    v-model="
                                                        form.paymentMethods
                                                    "
                                                    :value="method"
                                                    type="checkbox"
                                                />
                                                <span>{{ method }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div
                                        class="pm-field-block pm-field-block--full"
                                    >
                                        <label class="pm-field-label"
                                            >Payment Status</label
                                        >
                                        <div class="pm-choice-inline">
                                            <label
                                                v-for="status in paymentStatusOptions"
                                                :key="status"
                                                class="pm-choice-item"
                                            >
                                                <input
                                                    v-model="
                                                        form.paymentStatus
                                                    "
                                                    :value="status"
                                                    type="radio"
                                                />
                                                <span>{{ status }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-job-order-form-section">
                            <div
                                class="pm-job-order-section-head pm-job-order-section-head--success"
                            >
                                Conversion to Sales Invoice
                            </div>
                            <div class="pm-job-order-section-body">
                                <div class="pm-choice-inline mb-3">
                                    <label class="pm-choice-item">
                                        <input
                                            v-model="form.convertedToInvoice"
                                            :value="true"
                                            type="radio"
                                        />
                                        <span>Yes</span>
                                    </label>
                                    <label class="pm-choice-item">
                                        <input
                                            v-model="form.convertedToInvoice"
                                            :value="false"
                                            type="radio"
                                        />
                                        <span>No</span>
                                    </label>
                                </div>
                                <div class="pm-job-order-grid">
                                    <div class="pm-field-block">
                                        <label class="pm-field-label"
                                            >Invoice Reference</label
                                        >
                                        <input
                                            v-model="form.invoiceReference"
                                            class="form-control"
                                            type="text"
                                        />
                                    </div>
                                    <div
                                        class="pm-field-block pm-field-block--full"
                                    >
                                        <label class="pm-field-label"
                                            >Notes</label
                                        >
                                        <textarea
                                            v-model="form.notes"
                                            class="form-control"
                                            rows="3"
                                        ></textarea>
                                    </div>
                                </div>
                                <button
                                    class="pm-job-order-generate-btn"
                                    type="button"
                                    @click="notifyAction('Convert Invoice')"
                                >
                                    Generate Sales Invoice
                                </button>
                            </div>
                        </section>

                        <div class="pm-job-order-footer-actions">
                            <div class="pm-job-order-footer-group">
                                <button
                                    class="pm-job-order-action-btn"
                                    type="button"
                                    @click="notifyAction('New')"
                                >
                                    New
                                </button>
                                <button
                                    class="pm-job-order-action-btn"
                                    :disabled="!form.id"
                                    type="button"
                                    @click="notifyAction('Update')"
                                >
                                    Update
                                </button>
                                <button
                                    class="pm-job-order-action-btn pm-job-order-action-btn--danger"
                                    :disabled="!form.id"
                                    type="button"
                                    @click="notifyAction('Delete')"
                                >
                                    Delete
                                </button>
                            </div>
                            <div class="pm-job-order-footer-group">
                                <button
                                    v-if="!form.id"
                                    class="pm-job-order-action-btn pm-job-order-action-btn--primary"
                                    type="button"
                                    @click="notifyAction('Save')"
                                >
                                    Save
                                </button>
                                <button
                                    class="pm-job-order-action-btn"
                                    type="button"
                                    @click="notifyAction('Print')"
                                >
                                    Print
                                </button>
                                <button
                                    class="pm-job-order-action-btn"
                                    type="button"
                                    @click="notifyAction('Email')"
                                >
                                    Email
                                </button>
                                <button
                                    class="pm-job-order-action-btn pm-job-order-action-btn--success"
                                    type="button"
                                    @click="notifyAction('Convert Invoice')"
                                >
                                    Convert to Invoice
                                </button>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <section class="pm-job-workspace">
                            <header class="pm-job-workspace-head"><div><div class="pm-job-steps" aria-label="Job order workflow"><span><b>1</b>Estimate / Quote</span><span class="active"><b>2</b>Job Orders</span><span><b>3</b>Work Schedule</span><span><b>4</b>Technician Report</span><span><b>5</b>Sales Invoice</span><span><b>6</b>Pay Bills / Close</span></div><h1>Job Order</h1></div><div class="pm-job-workspace-actions"><button class="pm-job-list-button" type="button" @click="openListView">☰ List</button><button type="button" @click="notifyAction('Convert Invoice')">▧ Convert to Service Invoice →</button></div></header>
                            <div class="pm-job-top-grid"><label>Job Order No.<input :value="jobOrderNumber || 'Auto generate after save'" class="form-control" readonly /></label><label>Related Estimate / Quote No.<select v-model="form.estimationId" class="form-control" @change="handleEstimationSourceChange"><option :value="null">Select estimate</option><option v-for="item in estimations" :key="item.id" :value="item.id">{{ item.estimation_no }}</option></select></label><label>Job Order Date<input v-model="form.issueDate" type="date" class="form-control" /></label><label>Job Order Time<input v-model="form.jobOrderTime" type="time" class="form-control" /></label><label>Priority<select v-model="form.priority" class="form-control"><option>Normal</option><option>High</option><option>Urgent</option></select></label><label>Status<select v-model="form.status" class="form-control"><option v-for="status in statusOptions" :key="status">{{ status }}</option></select></label><fieldset><legend>Customer Approved</legend><label><input v-model="form.customerApproved" type="radio" :value="true" /> Yes</label><label><input v-model="form.customerApproved" type="radio" :value="false" /> No</label></fieldset></div>
                            <div class="pm-job-layout"><section><h2><b>1</b> Customer Information</h2><div class="pm-job-fields three"><label>Customer Type<select v-model="form.customerType" class="form-control"><option>Residential</option><option>Commercial</option></select></label><label>Customer Name<select v-model="form.customerId" class="form-control" @change="handleCustomerChange"><option :value="null">Select customer</option><option v-for="item in customers" :key="item.id" :value="item.id">{{ item.account_name }}</option></select></label><label>Contact Person<input v-model="form.customerName" class="form-control" /></label><label>Phone<input v-model="form.customerContact" class="form-control" /></label><label>Email<input v-model="form.customerEmail" class="form-control" /></label></div><h3>Company Information</h3><div class="pm-job-fields three"><label>Company Name<input v-model="form.customerCompany" class="form-control" /></label><label>Phone<input v-model="form.customerContact" class="form-control" /></label><label>Email<input v-model="form.customerEmail" class="form-control" /></label><label class="full">Website<input v-model="form.mapLink" class="form-control" /></label></div></section><section><h2><b>2</b> Job Site / Location</h2><div class="pm-job-fields three"><label>Building / Property Name<select v-model="form.jobSiteId" class="form-control" @change="handleJobSiteChange"><option :value="null">Select job site</option><option v-for="item in jobSites" :key="item.id" :value="item.id">{{ item.job_site_name }}</option></select></label><label>Floor / Level<input v-model="form.siteFloorLevel" class="form-control" /></label><label>Suite / Unit / Room No.<input v-model="form.siteSuiteUnit" class="form-control" /></label><label class="full">Site Address<input v-model="form.jobSiteAddress" class="form-control" /></label><label>City<input v-model="form.siteCity" class="form-control" /></label><label>Province<input v-model="form.siteProvince" class="form-control" /></label><label>Postal Code<input v-model="form.sitePostalCode" class="form-control" /></label><label class="full">Location / Access Details<textarea v-model="form.siteAccessDetails" class="form-control" rows="2" /></label></div></section><section><h2><b>3</b> Job Order Details</h2><div class="pm-job-fields three"><label class="full">Job Title / Work Description<input v-model="form.jobDescription" class="form-control" /></label><label class="full">Problem / Work Required<textarea v-model="form.scopeOfWork" class="form-control" rows="3" /></label><label>Request Date<input v-model="form.requestDate" type="date" class="form-control" /></label><label>Request Time<input v-model="form.requestTime" type="time" class="form-control" /></label><label>Requested By<input v-model="form.requestedBy" class="form-control" /></label><label>Request Method<select v-model="form.requestMethod" class="form-control"><option>Phone</option><option>Email</option><option>Website</option></select></label><label>Reference / PO No.<input v-model="form.referenceNo" class="form-control" /></label><label>Account No.<input v-model="form.accountNo" class="form-control" /></label></div></section></div>
                            <div class="pm-job-bottom-layout"><section><h2><b>4</b> Schedule Information</h2><div class="pm-job-fields four"><label>Service Required Start Date<input v-model="form.startAt" type="datetime-local" class="form-control" /></label><label>Start Time<input v-model="form.startAt" type="datetime-local" class="form-control" /></label><label>End Time<input v-model="form.endAt" type="datetime-local" class="form-control" /></label><label>Estimated Duration<input :value="netDurationLabel" class="form-control" readonly /></label><label>Alternate Date<input v-model="form.alternateDate" type="date" class="form-control" /></label><label>Alternate Start Time<input v-model="form.alternateStartTime" type="time" class="form-control" /></label><label>Alternate End Time<input v-model="form.alternateEndTime" type="time" class="form-control" /></label><label>Alternate Duration<input v-model="form.alternateDuration" class="form-control" /></label><label class="full">Service Notes / Special Instructions<textarea v-model="form.serviceNotes" class="form-control" rows="2" /></label></div></section><section><h2><b>5</b> Job Site Requirements</h2><div class="pm-job-fields two"><label class="full">Access Requirements<textarea v-model="form.accessRequirements" class="form-control" rows="2" /></label><label>Key / Fob Required<select v-model="form.keyFobRequired" class="form-control"><option>Yes</option><option>No</option></select></label><label>Provided By<input v-model="form.keyProvidedBy" class="form-control" /></label><label class="full">Equipment / Tools Required<textarea v-model="form.equipmentRequired" class="form-control" rows="2" /></label><label>Permits / Approvals Required<select v-model="form.permitsRequired" class="form-control"><option>No</option><option>Yes</option></select></label><label>Details<input v-model="form.permitDetails" class="form-control" /></label><label class="full">Safety / Other Requirements<textarea v-model="form.safetyRequirements" class="form-control" rows="2" /></label></div></section><section><h2><b>6</b> Assigned Contractor(s)</h2><div class="pm-job-fields three"><label>Primary Contact<input v-model="form.siteContactPerson" class="form-control" /></label><label>Contact Phone<input v-model="form.siteContactNumber" class="form-control" /></label><label>Contact Email<input v-model="form.customerEmail" class="form-control" /></label><label>Insurance Provided<select v-model="form.insuranceProvided" class="form-control"><option>Yes</option><option>No</option></select></label><label>Insurance Expiry Date<input v-model="form.insuranceExpiryDate" type="date" class="form-control" /></label><label>WCB No.<input v-model="form.wcbNo" class="form-control" /></label></div></section><section><h2><b>7</b> Cost Summary (From Estimate)</h2><div class="pm-job-cost"><div><small>Subtotal</small><strong>${{ formatMoney(subtotal) }}</strong></div><div><small>Tax</small><strong>${{ formatMoney(rowTaxTotal) }}</strong></div><div><small>Total Estimated Cost</small><strong>${{ formatMoney(grandTotal) }}</strong></div></div></section><section><h2><b>8</b> Notes / Internal</h2><div class="pm-job-fields two"><label>Internal Notes<textarea v-model="form.notes" class="form-control" rows="4" /></label><label>Customer Notes<textarea v-model="form.customerNotes" class="form-control" rows="4" /></label></div></section></div>
                            <div class="pm-job-actions" role="group"><button type="button" @click="openFormView">＋ New</button><button :disabled="!form.id" type="button" @click="notifyAction('Update')">✎ Edit</button><button :disabled="!form.id" type="button" @click="notifyAction('Delete')">♲ Delete</button><button type="button" @click="notifyAction('Save')">▣ Save</button><button type="button" @click="notifyAction('Save & Stay')">▧ Save & Stay</button><button type="button" @click="notifyAction('Preview')">◉ Preview</button><button type="button" @click="notifyAction('Print')">▣ Print</button><button type="button" @click="notifyAction('Email')">✉ Email</button><button type="button" @click="notifyAction('Save & Out')">⇥ Save & Out</button></div>
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
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
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

const route = useRoute();
const router = useRouter();

const searchQuery = ref("");
const listSearchQuery = ref("");
const filterStatus = ref("");
const viewMode = ref("list");
const activeId = ref(null);
const detailItem = ref(null);
const userName = ref("John Doe");
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const listItems = ref([]);
const customers = ref([]);
const jobSites = ref([]);
const quotations = ref([]);
const estimations = ref([]);

const statusOptions = [
    "Pending",
    "Scheduled",
    "In Progress",
    "On Hold",
    "Completed",
    "Cancelled",
];
const jobTypeOptions = [
    "Plumbing",
    "Painting",
    "Electrical",
    "Cleaning",
    "Carpentry",
    "Others",
];
const paymentMethodOptions = [
    "Cash",
    "Credit Card",
    "Debit Card",
    "Bank Transfer",
    "Other",
];
const paymentStatusOptions = ["Not Paid", "Partially Paid", "Fully Paid"];

const statusMap = {
    Pending: 1,
    Scheduled: 2,
    "In Progress": 3,
    "On Hold": 4,
    Completed: 5,
    Cancelled: 6,
};
const reverseStatusMap = Object.fromEntries(
    Object.entries(statusMap).map(([key, value]) => [value, key]),
);
const jobTypeMap = {
    Plumbing: 1,
    Painting: 2,
    Electrical: 3,
    Cleaning: 4,
    Carpentry: 5,
    Others: 6,
};
const reverseJobTypeMap = Object.fromEntries(
    Object.entries(jobTypeMap).map(([key, value]) => [value, key]),
);
const paymentMethodMap = {
    Cash: 1,
    "Credit Card": 2,
    "Debit Card": 3,
    "Bank Transfer": 4,
    Other: 5,
};
const reversePaymentMethodMap = Object.fromEntries(
    Object.entries(paymentMethodMap).map(([key, value]) => [value, key]),
);
const paymentStatusMap = {
    "Not Paid": 1,
    "Partially Paid": 2,
    "Fully Paid": 3,
};
const reversePaymentStatusMap = Object.fromEntries(
    Object.entries(paymentStatusMap).map(([key, value]) => [value, key]),
);

const form = reactive({
    id: null,
    quotationId: null,
    estimationId: null,
    issueDate: formatDateInput(new Date()),
    status: "Pending",
    jobTypes: ["Plumbing"],
    jobDescription: "",
    customerId: null,
    customerName: "",
    customerCompany: "",
    customerContact: "",
    customerEmail: "",
    customerAddress: "",
    jobSiteId: null,
    jobSiteAddress: "",
    mapLink: "",
    siteContactPerson: "",
    siteContactNumber: "",
    startAt: formatDateTimeLocal(new Date()),
    endAt: formatDateTimeLocal(addHours(new Date(), 4)),
    scopeOfWork: "",
    paymentMethods: ["Cash"],
    depositReceived: 0,
    amountPaid: 0,
    paymentStatus: "Not Paid",
    progress: 0,
    convertedToInvoice: false,
    invoiceReference: "",
    notes: "",
    jobOrderTime: "",
    priority: "Normal",
    customerApproved: true,
    customerType: "Residential",
    siteFloorLevel: "",
    siteSuiteUnit: "",
    siteCity: "",
    siteProvince: "",
    sitePostalCode: "",
    siteAccessDetails: "",
    requestDate: "",
    requestTime: "",
    requestedBy: "",
    requestMethod: "Phone",
    referenceNo: "",
    accountNo: "",
    alternateDate: "",
    alternateStartTime: "",
    alternateEndTime: "",
    alternateDuration: "",
    serviceNotes: "",
    accessRequirements: "",
    keyFobRequired: "No",
    keyProvidedBy: "",
    equipmentRequired: "",
    permitsRequired: "No",
    permitDetails: "",
    safetyRequirements: "",
    insuranceProvided: "No",
    insuranceExpiryDate: "",
    wcbNo: "",
    customerNotes: "",
});

let costRowId = 1;
const costRows = ref([
    {
        id: costRowId,
        item: "",
        description: "",
        qty: 1,
        unitPrice: 0,
        salesTax: 0,
    },
]);

const userInitials = computed(() => {
    const parts = String(userName.value || "U")
        .trim()
        .split(/\s+/);
    return (parts[0]?.[0] || "U").concat(parts[1]?.[0] || "").toUpperCase();
});

const filteredItems = computed(() => {
    const keyword = `${searchQuery.value} ${listSearchQuery.value}`
        .trim()
        .toLowerCase();

    return listItems.value.filter((item) => {
        const matchesKeyword =
            !keyword ||
            [
                item.job_order_no,
                item.job_description,
                item.customer?.name,
                item.customer?.company_name,
                item.quotation?.quotation_no,
                item.estimation?.estimation_no,
            ]
                .filter(Boolean)
                .some((value) =>
                    String(value).toLowerCase().includes(keyword),
                );

        const matchesStatus =
            !filterStatus.value || item.status_label === filterStatus.value;

        return matchesKeyword && matchesStatus;
    });
});

const totalValue = computed(() =>
    filteredItems.value.reduce((sum, item) => sum + toNumber(item.total), 0),
);
const inProgressCount = computed(
    () =>
        filteredItems.value.filter((item) => item.status_label === "In Progress")
            .length,
);
const completedCount = computed(
    () =>
        filteredItems.value.filter((item) => item.status_label === "Completed")
            .length,
);
const jobOrderNumber = computed(() => detailItem.value?.job_order_no || "");

const costRowsWithTotals = computed(() =>
    costRows.value.map((row) => {
        const qty = toNumber(row.qty);
        const unitPrice = toNumber(row.unitPrice);
        const salesTax = toNumber(row.salesTax);
        const baseAmount = qty * unitPrice;
        const salesTaxAmount = baseAmount * (salesTax / 100);

        return {
            ...row,
            salesTaxAmount,
            lineTotal: baseAmount + salesTaxAmount,
        };
    }),
);

const subtotal = computed(() =>
    costRowsWithTotals.value.reduce((sum, row) => sum + row.lineTotal, 0),
);
const rowTaxTotal = computed(() =>
    costRowsWithTotals.value.reduce((sum, row) => sum + row.salesTaxAmount, 0),
);
const grandTotal = computed(() => subtotal.value);
const outstandingBalance = computed(() =>
    Math.max(grandTotal.value - toNumber(form.amountPaid), 0),
);
const netDurationLabel = computed(() => {
    if (!form.startAt || !form.endAt) return "Not calculated yet";
    const start = new Date(form.startAt);
    const end = new Date(form.endAt);

    if (
        Number.isNaN(start.getTime()) ||
        Number.isNaN(end.getTime()) ||
        end <= start
    ) {
        return "End time must be later than start time";
    }

    const diffMinutes = Math.round((end.getTime() - start.getTime()) / 60000);
    const days = Math.floor(diffMinutes / 1440);
    const hours = Math.floor((diffMinutes % 1440) / 60);
    const minutes = diffMinutes % 60;
    const parts = [];

    if (days) parts.push(`${days}d`);
    if (hours) parts.push(`${hours}h`);
    if (minutes || !parts.length) parts.push(`${minutes}m`);

    return parts.join(" ");
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

watch(
    () => [form.amountPaid, grandTotal.value],
    () => {
        const paid = toNumber(form.amountPaid);
        if (paid <= 0) {
            form.paymentStatus = "Not Paid";
        } else if (paid >= grandTotal.value && grandTotal.value > 0) {
            form.paymentStatus = "Fully Paid";
        } else {
            form.paymentStatus = "Partially Paid";
        }
    },
);

onMounted(async () => {
    await Promise.all([fetchMetadata(), loadList()]);

    const jobOrderId = route.params.id;
    if (jobOrderId) {
        await loadJobOrder(jobOrderId);
        viewMode.value = "form";
        return;
    }

    if (route.query.quotation_id) {
        openFormView();
        form.quotationId = Number(route.query.quotation_id);
        await handleQuotationSourceChange();
    } else if (route.query.estimation_id) {
        openFormView();
        form.estimationId = Number(route.query.estimation_id);
        await handleEstimationSourceChange();
    }
});

async function fetchMetadata() {
    try {
        const [customerRes, siteRes, quotationRes, estimationRes] =
            await Promise.all([
                client.get("/job-orders/customers"),
                client.get("/job-orders/job-sites"),
                client.get("/job-orders/quotations"),
                client.get("/job-orders/estimations"),
            ]);

        customers.value = customerRes.data?.data || [];
        jobSites.value = siteRes.data?.data || [];
        quotations.value = quotationRes.data?.data || [];
        estimations.value = estimationRes.data?.data || [];
    } catch (error) {
        console.error("Failed to load job order metadata", error);
        setFlash("Failed to load job order metadata.", "warning", 3000);
    }
}

async function loadList() {
    try {
        const { data } = await client.get("/job-orders", {
            params: { per_page: 100 },
        });
        listItems.value = data?.data?.data || [];
    } catch (error) {
        console.error("Failed to load job orders", error);
        setFlash("Failed to load job orders.", "warning", 3000);
    }
}

async function loadJobOrder(id) {
    try {
        const { data } = await client.get(`/job-orders/${id}`);
        const item = data?.data;

        if (!item) {
            setFlash("Job order not found.", "warning", 2200);
            return;
        }

        activeId.value = item.id;
        detailItem.value = item;
        form.id = item.id;
        form.quotationId = item.quotation_id || null;
        form.estimationId = item.estimation_id || null;
        form.issueDate = item.issue_date || formatDateInput(new Date());
        form.status = reverseStatusMap[item.status] || "Pending";
        form.jobTypes = item.job_type ? [reverseJobTypeMap[item.job_type]] : [];
        form.jobDescription = item.job_description || "";
        form.customerId = item.customer_id || null;
        form.customerCompany = item.customer?.company_name || "";
        form.customerName = item.customer?.name || "";
        form.customerContact = item.customer?.contact || "";
        form.customerEmail = item.customer?.email || "";
        form.customerAddress = item.customer?.address || "";
        form.jobSiteId = item.job_site_id || null;
        form.jobSiteAddress = item.job_site?.address || "";
        form.mapLink = item.map_link || item.job_site?.map_link || "";
        form.siteContactPerson = item.site_contact_person || "";
        form.siteContactNumber = item.site_contact_number || "";
        form.startAt = item.schedule_start_date
            ? item.schedule_start_date.slice(0, 16)
            : "";
        form.endAt = item.schedule_end_date
            ? item.schedule_end_date.slice(0, 16)
            : "";
        form.scopeOfWork = item.scope_of_work || "";
        form.paymentMethods = item.payment_method
            ? [reversePaymentMethodMap[item.payment_method]]
            : [];
        form.depositReceived = item.deposit_received || 0;
        form.amountPaid = item.amount_paid || 0;
        form.paymentStatus =
            reversePaymentStatusMap[item.payment_status] || "Not Paid";
        form.progress = item.progress || 0;
        form.convertedToInvoice = !!item.converted_to_invoice;
        form.invoiceReference = item.invoice_reference || "";
        form.notes = item.note || "";
        Object.assign(form, {
            jobOrderTime: item.job_order_time || "", priority: item.priority || "Normal", customerApproved: !!item.customer_approved,
            customerType: item.customer_type || "Residential", siteFloorLevel: item.site_floor_level || "", siteSuiteUnit: item.site_suite_unit || "",
            siteCity: item.site_city || "", siteProvince: item.site_province || "", sitePostalCode: item.site_postal_code || "", siteAccessDetails: item.site_access_details || "",
            requestDate: item.request_date?.slice(0, 10) || "", requestTime: item.request_time || "", requestedBy: item.requested_by || "", requestMethod: item.request_method || "Phone",
            referenceNo: item.reference_no || "", accountNo: item.account_no || "", alternateDate: item.alternate_date?.slice(0, 10) || "", alternateStartTime: item.alternate_start_time || "",
            alternateEndTime: item.alternate_end_time || "", alternateDuration: item.alternate_duration || "", serviceNotes: item.service_notes || "", accessRequirements: item.access_requirements || "",
            keyFobRequired: item.key_fob_required || "No", keyProvidedBy: item.key_provided_by || "", equipmentRequired: item.equipment_required || "", permitsRequired: item.permits_required || "No",
            permitDetails: item.permit_details || "", safetyRequirements: item.safety_requirements || "", insuranceProvided: item.insurance_provided || "No",
            insuranceExpiryDate: item.insurance_expiry_date?.slice(0, 10) || "", wcbNo: item.wcb_no || "", customerNotes: item.customer_notes || "",
        });

        costRows.value = (item.details || []).map((row) => ({
            id: costRowId++,
            item: row.item_name || "",
            description: row.item_description || "",
            qty: row.quantity || 1,
            unitPrice: row.unit_price || 0,
            salesTax: row.sale_tax_percentage || 0,
        }));

        if (!costRows.value.length) {
            resetCostRows();
        }
    } catch (error) {
        console.error("Failed to load job order", error);
        setFlash("Failed to load job order.", "warning", 3000);
    }
}

function openListView() {
    viewMode.value = "list";
    activeId.value = null;
    detailItem.value = null;
}

function openFormView() {
    viewMode.value = "form";
    resetForm();
}

function startView(item) {
    detailItem.value = item;
    activeId.value = item.id;
    viewMode.value = "detail";
}

async function startEdit(item) {
    viewMode.value = "form";
    await loadJobOrder(item.id);
}

async function quickDelete(id) {
    const confirmed = confirm("Delete this job order?");
    if (!confirmed) return;

    try {
        await client.delete(`/job-orders/${id}`);
        listItems.value = listItems.value.filter((item) => item.id !== id);
        setFlash("Job order deleted successfully.", "success", 2200);
    } catch (error) {
        console.error("Failed to delete job order", error);
        setFlash("Failed to delete job order.", "warning", 3000);
    }
}

function handleCustomerChange() {
    const selected = customers.value.find((item) => item.id === form.customerId);
    if (!selected) return;

    form.customerName = selected.account_name || "";
    form.customerCompany = selected.company_name || "";
    form.customerContact = selected.cell_phone || "";
    form.customerEmail = selected.email || "";
    form.customerAddress = [
        selected.house_number,
        selected.street_number,
        selected.city,
        selected.state,
    ]
        .filter(Boolean)
        .join(", ");
}

function handleJobSiteChange() {
    const selected = jobSites.value.find((item) => item.id === form.jobSiteId);
    if (!selected) return;

    form.jobSiteAddress = selected.address || "";
    form.mapLink = selected.map_link || "";
}

async function handleQuotationSourceChange() {
    if (!form.quotationId) return;
    form.estimationId = null;

    try {
        const { data } = await client.get(`/quotations/${form.quotationId}`);
        applySourceData(data?.data, "quotation");
    } catch (error) {
        console.error("Failed to load quotation source", error);
        setFlash("Failed to load quotation source.", "warning", 3000);
    }
}

async function handleEstimationSourceChange() {
    if (!form.estimationId) return;
    form.quotationId = null;

    try {
        const { data } = await client.get(`/estimations/${form.estimationId}`);
        applySourceData(data?.data, "estimation");
    } catch (error) {
        console.error("Failed to load estimation source", error);
        setFlash("Failed to load estimation source.", "warning", 3000);
    }
}

function applySourceData(source, sourceType) {
    if (!source) return;

    form.jobDescription = source.job_description || "";
    form.jobTypes = source.job_type ? [reverseJobTypeMap[source.job_type]] : [];
    form.customerId = source.customer_id || null;
    form.jobSiteId = source.job_site_id || null;
    form.startAt = source.schedule_start_date
        ? source.schedule_start_date.slice(0, 16)
        : form.startAt;
    form.endAt = source.schedule_end_date
        ? source.schedule_end_date.slice(0, 16)
        : form.endAt;
    form.scopeOfWork = source.scope_of_work || "";
    form.notes = source.note || "";
    form.paymentMethods = source.payment_method
        ? [reversePaymentMethodMap[source.payment_method]]
        : form.paymentMethods;

    if (source.customer) {
        form.customerName = source.customer.name || "";
        form.customerCompany = source.customer.company_name || "";
        form.customerContact = source.customer.contact || "";
        form.customerEmail = source.customer.email || "";
        form.customerAddress = source.customer.address || "";
    } else {
        handleCustomerChange();
    }

    if (source.job_site) {
        form.jobSiteAddress = source.job_site.address || "";
        form.mapLink = source.job_site.map_link || "";
    } else {
        handleJobSiteChange();
    }

    costRows.value = (source.details || []).map((row) => ({
        id: costRowId++,
        item: row.item_name || "",
        description: row.item_description || "",
        qty: row.quantity || 1,
        unitPrice: row.unit_price || 0,
        salesTax: row.sale_tax_percentage || 0,
    }));

    if (!costRows.value.length) {
        resetCostRows();
    }

    setFlash(
        `${sourceType === "quotation" ? "Quotation" : "Estimation"} data loaded into the job order form.`,
        "success",
        2200,
    );
}

function buildPayload() {
    return {
        quotation_id: form.quotationId,
        estimation_id: form.estimationId,
        issue_date: form.issueDate,
        status: statusMap[form.status] || 1,
        job_type: form.jobTypes.length ? jobTypeMap[form.jobTypes[0]] || 1 : null,
        job_description: form.jobDescription,
        customer_id: form.customerId,
        job_site_id: form.jobSiteId,
        site_contact_person: form.siteContactPerson,
        site_contact_number: form.siteContactNumber,
        map_link: form.mapLink,
        schedule_start_date: form.startAt || null,
        schedule_end_date: form.endAt || null,
        scope_of_work: form.scopeOfWork,
        tax: 0,
        discount: 0,
        note: form.notes,
        job_order_time: form.jobOrderTime || null,
        priority: form.priority,
        customer_approved: form.customerApproved,
        customer_type: form.customerType,
        site_floor_level: form.siteFloorLevel,
        site_suite_unit: form.siteSuiteUnit,
        site_city: form.siteCity,
        site_province: form.siteProvince,
        site_postal_code: form.sitePostalCode,
        site_access_details: form.siteAccessDetails,
        request_date: form.requestDate || null,
        request_time: form.requestTime || null,
        requested_by: form.requestedBy,
        request_method: form.requestMethod,
        reference_no: form.referenceNo,
        account_no: form.accountNo,
        alternate_date: form.alternateDate || null,
        alternate_start_time: form.alternateStartTime || null,
        alternate_end_time: form.alternateEndTime || null,
        alternate_duration: form.alternateDuration,
        service_notes: form.serviceNotes,
        access_requirements: form.accessRequirements,
        key_fob_required: form.keyFobRequired,
        key_provided_by: form.keyProvidedBy,
        equipment_required: form.equipmentRequired,
        permits_required: form.permitsRequired,
        permit_details: form.permitDetails,
        safety_requirements: form.safetyRequirements,
        insurance_provided: form.insuranceProvided,
        insurance_expiry_date: form.insuranceExpiryDate || null,
        wcb_no: form.wcbNo,
        customer_notes: form.customerNotes,
        payment_method: form.paymentMethods.length
            ? paymentMethodMap[form.paymentMethods[0]] || 1
            : null,
        deposit_received: form.depositReceived,
        amount_paid: form.amountPaid,
        payment_status: paymentStatusMap[form.paymentStatus] || 1,
        progress: form.progress || 0,
        converted_to_invoice: form.convertedToInvoice,
        invoice_reference: form.invoiceReference,
        details: costRows.value.map((row) => ({
            item_name: row.item,
            item_description: row.description,
            quantity: row.qty,
            unit_price: row.unitPrice,
            sale_tax_percentage: row.salesTax,
        })),
    };
}

async function saveJobOrder() {
    try {
        const { data } = await client.post("/job-orders", buildPayload());
        if (data?.data?.id) {
            activeId.value = data.data.id;
            form.id = data.data.id;
            detailItem.value = data.data;
            setFlash("Job order saved successfully.", "success", 2200);
            await loadList();
            await loadJobOrder(data.data.id);
        }
    } catch (error) {
        console.error("Failed to save job order", error);
        setFlash("Failed to save job order.", "warning", 3000);
    }
}

async function updateJobOrder() {
    if (!form.id) {
        setFlash("No job order selected for update.", "warning", 2200);
        return;
    }

    try {
        const { data } = await client.put(
            `/job-orders/${form.id}`,
            buildPayload(),
        );
        detailItem.value = data?.data || detailItem.value;
        setFlash("Job order updated successfully.", "success", 2200);
        await loadList();
    } catch (error) {
        console.error("Failed to update job order", error);
        setFlash("Failed to update job order.", "warning", 3000);
    }
}

async function deleteJobOrder() {
    if (!form.id) {
        setFlash("No job order selected for delete.", "warning", 2200);
        return;
    }

    const confirmed = confirm("Are you sure you want to delete this job order?");
    if (!confirmed) return;

    try {
        await client.delete(`/job-orders/${form.id}`);
        setFlash("Job order deleted successfully.", "success", 2200);
        resetForm();
        viewMode.value = "list";
        await loadList();
    } catch (error) {
        console.error("Failed to delete job order", error);
        setFlash("Failed to delete job order.", "warning", 3000);
    }
}

function notifyAction(action) {
    if (action === "Save") {
        saveJobOrder();
    } else if (action === "Update") {
        updateJobOrder();
    } else if (action === "Delete") {
        deleteJobOrder();
    } else if (action === "New") {
        openFormView();
    } else if (action === "Convert Invoice") {
        form.convertedToInvoice = true;
        setFlash("Job order marked for invoice conversion.", "success", 2200);
    } else {
        setFlash(`${action} action is ready for job orders.`, "info", 1800);
    }
}

function addCostRow() {
    costRows.value.push({
        id: costRowId++,
        item: "",
        description: "",
        qty: 1,
        unitPrice: 0,
        salesTax: 0,
    });
}

function removeCostRow(id) {
    if (costRows.value.length === 1) {
        setFlash("At least one cost row is required.", "warning", 2200);
        return;
    }

    costRows.value = costRows.value.filter((row) => row.id !== id);
}

function calcRowSalesTaxAmount(row) {
    const qty = toNumber(row.qty);
    const unitPrice = toNumber(row.unitPrice);
    const salesTax = toNumber(row.salesTax);
    return qty * unitPrice * (salesTax / 100);
}

function calcRowLineTotal(row) {
    const qty = toNumber(row.qty);
    const unitPrice = toNumber(row.unitPrice);
    const subtotalValue = qty * unitPrice;
    return subtotalValue + calcRowSalesTaxAmount(row);
}

function resetCostRows() {
    costRowId += 1;
    costRows.value = [
        {
            id: costRowId,
            item: "",
            description: "",
            qty: 1,
            unitPrice: 0,
            salesTax: 0,
        },
    ];
}

function resetForm() {
    activeId.value = null;
    detailItem.value = null;
    form.id = null;
    form.quotationId = null;
    form.estimationId = null;
    form.issueDate = formatDateInput(new Date());
    form.status = "Pending";
    form.jobTypes = ["Plumbing"];
    form.jobDescription = "";
    form.customerId = null;
    form.customerName = "";
    form.customerCompany = "";
    form.customerContact = "";
    form.customerEmail = "";
    form.customerAddress = "";
    form.jobSiteId = null;
    form.jobSiteAddress = "";
    form.mapLink = "";
    form.siteContactPerson = "";
    form.siteContactNumber = "";
    form.startAt = formatDateTimeLocal(new Date());
    form.endAt = formatDateTimeLocal(addHours(new Date(), 4));
    form.scopeOfWork = "";
    form.paymentMethods = ["Cash"];
    form.depositReceived = 0;
    form.amountPaid = 0;
    form.paymentStatus = "Not Paid";
    form.progress = 0;
    form.convertedToInvoice = false;
    form.invoiceReference = "";
    form.notes = "";
    Object.assign(form, {
        jobOrderTime: "", priority: "Normal", customerApproved: true, customerType: "Residential",
        siteFloorLevel: "", siteSuiteUnit: "", siteCity: "", siteProvince: "", sitePostalCode: "", siteAccessDetails: "",
        requestDate: "", requestTime: "", requestedBy: "", requestMethod: "Phone", referenceNo: "", accountNo: "",
        alternateDate: "", alternateStartTime: "", alternateEndTime: "", alternateDuration: "", serviceNotes: "",
        accessRequirements: "", keyFobRequired: "No", keyProvidedBy: "", equipmentRequired: "", permitsRequired: "No",
        permitDetails: "", safetyRequirements: "", insuranceProvided: "No", insuranceExpiryDate: "", wcbNo: "", customerNotes: "",
    });
    resetCostRows();
}

function statusBadgeClass(status) {
    if (status === "Completed") return "is-approved";
    if (status === "In Progress") return "is-pending";
    if (status === "On Hold") return "is-hold";
    if (status === "Cancelled") return "is-cancelled";
    if (status === "Scheduled") return "is-scheduled";
    return "is-expired";
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

function addHours(date, hours) {
    const next = new Date(date);
    next.setHours(next.getHours() + hours);
    return next;
}

function formatDateInput(date) {
    const copy = new Date(date);
    copy.setMinutes(copy.getMinutes() - copy.getTimezoneOffset());
    return copy.toISOString().slice(0, 10);
}

function formatDateTimeLocal(date) {
    const copy = new Date(date);
    copy.setMinutes(copy.getMinutes() - copy.getTimezoneOffset());
    return copy.toISOString().slice(0, 16);
}

function formatDate(value) {
    if (!value) return "--";
    return new Date(value).toLocaleDateString();
}

function formatDateTime(value) {
    if (!value) return "--";
    return new Date(value).toLocaleString();
}

function toNumber(value) {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
}

function formatMoney(value) {
    return toNumber(value).toFixed(2);
}
</script>

<style scoped>
.pm-job-order-page {
    padding-bottom: 48px;
}

.pm-job-workspace-actions { display: flex; align-items: center; gap: 12px; }
.pm-job-workspace-actions > button { border: 0; border-radius: 4px; background: #0736df; color: #fff; font-weight: 700; padding: 11px 16px; }
.pm-job-workspace-actions .pm-job-list-button { border: 1px solid #aebbd4; background: #fff; color: #0736df; }

.pm-job-workspace { color: #06145c; padding: 4px 0 22px; }
.pm-job-workspace-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 16px; }.pm-job-workspace-head h1 { margin: 18px 0 0; font-size: 1.75rem; font-weight: 800; }.pm-job-workspace-head > button,.pm-job-actions button { border: 0; border-radius: 4px; background: #0736df; color: #fff; font-weight: 700; padding: 11px 16px; }.pm-job-steps { display: flex; min-width: 760px; margin-top: 4px; justify-content: space-between; }.pm-job-steps span { position: relative; flex: 1; padding-top: 32px; text-align: center; color: #15225f; font-size: .74rem; font-weight: 700; }.pm-job-steps span::before { content: ''; position: absolute; height: 1px; background: #8791b7; left: 0; right: 0; top: 14px; z-index: 0; }.pm-job-steps b { position: absolute; z-index: 1; top: 0; left: calc(50% - 14px); display: grid; width: 28px; height: 28px; place-items: center; border: 1px solid #7380a7; border-radius: 50%; background: #fff; font-size: .9rem; }.pm-job-steps .active b { border-color: #073ee6; background: #0c3ded; color: #fff; }.pm-job-steps .active { color: #073bea; }.pm-job-top-grid { display: grid; grid-template-columns: 1fr 1.05fr .9fr .85fr .8fr .8fr .95fr; gap: 12px; align-items: end; margin-bottom: 14px; }.pm-job-workspace label,.pm-job-workspace legend { display: grid; gap: 5px; font-size: .69rem; font-weight: 700; }.pm-job-workspace .form-control { min-height: 30px; font-size: .78rem; color: #06145c; border-color: #cdd5e3; }.pm-job-top-grid fieldset { min-height: 65px; padding: 7px 12px; margin: 0; border: 1px solid #83caab; border-radius: 4px; }.pm-job-top-grid fieldset label { display: inline-flex; margin-right: 18px; gap: 7px; }.pm-job-layout { display: grid; grid-template-columns: 1.05fr 1.1fr 1.25fr; gap: 10px; }.pm-job-bottom-layout { display: grid; grid-template-columns: 1.1fr .75fr 1.25fr; gap: 10px; margin-top: 10px; }.pm-job-layout section,.pm-job-bottom-layout section { border: 1px solid #dfe5ef; border-radius: 5px; padding: 10px; background: #fff; }.pm-job-layout h2,.pm-job-bottom-layout h2 { margin: -10px -10px 10px; padding: 8px 10px; border-bottom: 1px solid #dfe5ef; color: #063ce3; font-size: .88rem; font-weight: 800; }.pm-job-layout h2 b,.pm-job-bottom-layout h2 b { display: inline-grid; place-items: center; width: 22px; height: 22px; margin-right: 6px; border-radius: 50%; color: #fff; background: #073ee6; }.pm-job-workspace h3 { font-size: .76rem; margin: 13px 0 8px; }.pm-job-fields { display: grid; gap: 10px 12px; }.pm-job-fields.three { grid-template-columns: repeat(3,minmax(0,1fr)); }.pm-job-fields.two { grid-template-columns: repeat(2,minmax(0,1fr)); }.pm-job-fields.four { grid-template-columns: repeat(4,minmax(0,1fr)); }.pm-job-fields .full { grid-column: 1 / -1; }.pm-job-cost { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }.pm-job-cost div { display: flex; min-height: 68px; flex-direction: column; justify-content: space-between; padding: 10px; border: 1px solid #e0e5ee; border-radius: 4px; }.pm-job-cost small { font-size: .68rem; font-weight: 700; }.pm-job-cost strong { text-align: right; color: #073be8; }.pm-job-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 14px; background: transparent; }.pm-job-actions button { min-width: 116px; padding: 10px; font-size: .77rem; }.pm-job-actions button:disabled { opacity: .55; }
@media (max-width: 1200px) { .pm-job-top-grid { grid-template-columns: repeat(4,1fr); }.pm-job-layout,.pm-job-bottom-layout { grid-template-columns: 1fr 1fr; }.pm-job-layout section:last-child,.pm-job-bottom-layout section:last-child { grid-column: 1 / -1; } }.pm-job-workspace textarea { min-height: auto; }
@media (max-width: 760px) { .pm-job-workspace-head { display: block; }.pm-job-workspace-head > button { margin: 15px 0; }.pm-job-steps { min-width: 700px; overflow-x: auto; }.pm-job-top-grid,.pm-job-layout,.pm-job-bottom-layout { grid-template-columns: 1fr; }.pm-job-layout section:last-child,.pm-job-bottom-layout section:last-child { grid-column: auto; }.pm-job-fields.three,.pm-job-fields.four { grid-template-columns: 1fr 1fr; } }.pm-job-fields textarea { resize: vertical; }

.pm-job-order-metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.pm-job-order-metric-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    border: 1px solid #dbe5f2;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15, 39, 71, 0.06);
}

.pm-job-order-metric-icon {
    display: inline-flex;
    width: 48px;
    height: 48px;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    color: #fff;
    font-size: 1.35rem;
    font-weight: 700;
}

.pm-job-order-metric-icon.is-blue,
.pm-job-order-progress-bar span {
    background: #2563eb;
}

.pm-job-order-metric-icon.is-green {
    background: #16a34a;
}

.pm-job-order-metric-icon.is-sky {
    background: #3b82f6;
}

.pm-job-order-metric-icon.is-emerald {
    background: #22c55e;
}

.pm-job-order-metric-label {
    color: #60728c;
    font-size: 0.9rem;
}

.pm-job-order-metric-value {
    color: #10243f;
    font-size: 1.9rem;
    font-weight: 700;
    line-height: 1.1;
}

.pm-job-order-form-section {
    overflow: hidden;
    margin-bottom: 18px;
    border: 1px solid #d6e3f5;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.07);
}

.pm-job-order-section-head {
    padding: 14px 18px;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
}

.pm-job-order-section-head--between {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.pm-job-order-section-head--success {
    background: linear-gradient(90deg, #16a34a, #22c55e);
}

.pm-job-order-section-body {
    padding: 18px;
}

.pm-job-order-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px 16px;
}

.pm-field-block {
    min-width: 0;
}

.pm-field-block--full {
    grid-column: 1 / -1;
}

.pm-choice-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    padding: 12px 14px;
    border: 1px solid #e0e8f5;
    border-radius: 12px;
    background: #f8fbff;
}

.pm-choice-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #324861;
    font-size: 0.9rem;
}

.pm-job-order-status-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.pm-job-order-status-pill {
    border: 1px solid #d9e0ec;
    border-radius: 10px;
    background: #fff;
    color: #2b405b;
    font-size: 0.9rem;
    padding: 8px 14px;
}

.pm-job-order-status-pill.is-active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-job-order-highlight {
    margin-top: 16px;
    padding: 14px 16px;
    border-left: 4px solid #2563eb;
    border-radius: 12px;
    background: #edf4ff;
    color: #1d4ed8;
}

.pm-job-order-highlight-label {
    display: block;
    margin-bottom: 4px;
    color: #6781a6;
    font-size: 0.82rem;
}

.pm-job-order-add-row,
.pm-job-order-action-btn,
.pm-job-order-generate-btn {
    border: 1px solid #d7dfef;
    border-radius: 10px;
    background: #fff;
    color: #203a5a;
    font-size: 0.86rem;
    font-weight: 600;
    padding: 8px 12px;
    transition: all 0.2s ease;
}

.pm-job-order-add-row:hover,
.pm-job-order-action-btn:hover,
.pm-job-order-generate-btn:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.pm-job-order-action-btn--primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-job-order-action-btn--success,
.pm-job-order-generate-btn {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}

.pm-job-order-action-btn--danger,
.pm-job-order-delete-row {
    color: #dc2626;
}

.pm-job-order-delete-row {
    border: none;
    background: transparent;
    font-size: 0.84rem;
}

.pm-job-order-summary {
    width: min(100%, 290px);
    margin-left: auto;
    margin-top: 16px;
    padding: 16px;
    border: 1px solid #cfe0ff;
    border-radius: 14px;
    background: linear-gradient(180deg, #f2f7ff, #e8f1ff);
}

.pm-job-order-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
    color: #34506f;
}

.pm-job-order-summary-row:last-child {
    margin-bottom: 0;
}

.pm-job-order-summary-row--total {
    padding-top: 10px;
    border-top: 1px solid #bed4ff;
    color: #1d4ed8;
    font-weight: 700;
}

.pm-job-order-footer-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 16px;
    border: 1px solid #d7dfef;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 39, 71, 0.07);
}

.pm-job-order-footer-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.pm-job-order-detail-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.pm-job-order-detail-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.pm-job-order-detail-card {
    padding: 16px;
    border: 1px solid #e1e8f2;
    border-radius: 14px;
    background: #f9fbff;
}

.pm-job-order-detail-card h6,
.pm-job-order-detail-block h6 {
    margin: 0 0 8px;
    color: #0f2747;
    font-weight: 700;
}

.pm-job-order-detail-card p,
.pm-job-order-detail-block p {
    margin: 0;
    color: #1f3654;
}

.pm-job-order-detail-card span {
    display: block;
    margin-top: 6px;
    color: #6a7a91;
    font-size: 0.85rem;
}

.pm-job-order-detail-block {
    margin-bottom: 18px;
}

.pm-job-order-source-tag {
    display: inline-flex;
    margin-top: 6px;
    padding: 4px 8px;
    border: 1px solid #d9e2f1;
    border-radius: 999px;
    color: #53667f;
    font-size: 0.78rem;
}

.pm-job-order-progress {
    display: grid;
    gap: 6px;
}

.pm-job-order-progress-bar {
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #dfe6f1;
}

.pm-job-order-progress-bar span {
    display: block;
    height: 100%;
    border-radius: inherit;
}

.pm-job-order-total-green {
    color: #16a34a;
    font-weight: 700;
}

.pm-account-holder-status.is-hold {
    background: #ffedd5;
    color: #ea580c;
}

.pm-account-holder-status.is-scheduled {
    background: #dbeafe;
    color: #2563eb;
}

@media (max-width: 1199.98px) {
    .pm-job-order-metric-grid,
    .pm-job-order-detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .pm-job-order-grid,
    .pm-job-order-metric-grid,
    .pm-job-order-detail-grid {
        grid-template-columns: 1fr;
    }

    .pm-job-order-detail-head,
    .pm-job-order-footer-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
