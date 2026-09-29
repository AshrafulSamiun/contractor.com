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
                        placeholder="Search estimation..."
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
                    <button
                        class="pm-icon-btn"
                        type="button"
                        aria-label="Settings"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z"
                            />
                        </svg>
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
                <div class="container pm-ops-page pm-estimation-page">
                    <section v-if="viewMode !== 'form'" class="pm-account-holder-hero">
                        <div>
                            <div class="pm-account-holder-kicker">
                                Accounting Sales
                            </div>
                            <h2 class="pm-account-holder-title">Estimation / Quotes</h2>
                            <div
                                class="pm-page-subtitle pm-account-holder-subtitle"
                            >
                                Accounting &gt; Sales &gt;
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
                            <button
                                v-if="viewMode === 'detail' || (viewMode === 'form' && form.id)"
                                class="pm-hero-action-button pm-hero-action-button--success"
                                type="button"
                                @click="convertToQuotation"
                            >
                                <span
                                    class="pm-hero-action-icon"
                                    aria-hidden="true"
                                    >&#8644;</span
                                >
                                <span>Convert to Quotation</span>
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

                    <section v-if="viewMode === 'form'" class="pm-estimate-quote-form">
                        <header class="pm-estimate-quote-header">
                            <div>
                                <h1>New Estimate / Quote</h1>
                                <div class="pm-estimate-steps" aria-label="Job order workflow">
                                    <span class="is-active"><b>1</b>Estimate / Quote</span>
                                    <span><b>2</b>Job Orders</span>
                                    <span><b>3</b>Work Schedule</span>
                                    <span><b>4</b>Technician Report</span>
                                    <span><b>5</b>Sales Invoice</span>
                                    <span><b>6</b>Pay Bills / Close</span>
                                </div>
                            </div>
                            <div class="pm-estimate-header-actions">
                                <button class="pm-quote-list-button" type="button" @click="openListView">☰ List</button>
                                <button class="pm-quote-primary" type="button" @click="convertToJobOrder">Convert to Job Order <span>→</span></button>
                            </div>
                        </header>

                        <div class="pm-quote-top-fields">
                            <label>Estimate #<input :value="estimationNumber || 'Auto-generated'" class="form-control" readonly /></label>
                            <label>Created Date<input v-model="form.dateIssued" class="form-control" type="date" /></label>
                            <label>Expiry Date &amp; Time <em>*</em><input v-model="form.expiryAt" class="form-control" type="datetime-local" /></label>
                            <label>Valid Until<input :value="form.expiryAt ? form.expiryAt.slice(0, 10) : ''" class="form-control" readonly /></label>
                            <fieldset class="pm-quote-approval"><legend>Customer Approval <em>*</em></legend><label><input v-model="form.depositRequired" type="radio" :value="true" /> Yes</label><label><input v-model="form.depositRequired" type="radio" :value="false" /> No</label></fieldset>
                        </div>

                        <div class="pm-quote-two-columns">
                            <section class="pm-quote-panel"><h2><b>1</b> Customer Information</h2><h3>Customer Details</h3>
                                <div class="pm-quote-grid customer-grid">
                                    <label>Customer Type <em>*</em><select v-model="form.customerType" class="form-control"><option>Residential</option><option>Commercial</option></select></label>
                                    <label>Customer Name <em>*</em><select v-model="form.customerId" @change="handleCustomerChange" class="form-control"><option :value="null">Select customer</option><option v-for="cust in customers" :key="cust.id" :value="cust.id">{{ cust.account_name }}</option></select></label>
                                    <label>Contact Person <em>*</em><input v-model="form.customerName" class="form-control" /></label>
                                    <label>Phone <em>*</em><input v-model="form.customerContact" class="form-control" /></label>
                                    <label>Email<input v-model="form.customerAddress" class="form-control" /></label>
                                </div>
                                <h3>Company Details</h3><div class="pm-quote-grid customer-grid"><label>Company Name <em>*</em><input v-model="form.customerCompany" class="form-control" /></label><label>Phone <em>*</em><input v-model="form.customerContact" class="form-control" /></label><label>Email<input v-model="form.customerAddress" class="form-control" /></label><label class="span-2">Website<input v-model="form.mapLink" class="form-control" placeholder="www.example.com" /></label></div>
                            </section>
                            <section class="pm-quote-panel"><h2><b>2</b> Job Site Location</h2><div class="pm-quote-grid site-grid">
                                <label>Building / Property Name <em>*</em><select v-model="form.jobSiteId" @change="handleJobSiteChange" class="form-control"><option :value="null">Select job site</option><option v-for="site in jobSites" :key="site.id" :value="site.id">{{ site.job_site_name }}</option></select></label><label>Floor / Level<input v-model="form.siteFloorLevel" class="form-control" /></label><label>Suite / Unit<input v-model="form.siteSuiteUnit" class="form-control" /></label>
                                <label class="span-3">Site Address <em>*</em><input v-model="form.jobSiteAddress" class="form-control" /></label><label>City<input v-model="form.siteCity" class="form-control" /></label><label>Province<select v-model="form.siteProvince" class="form-control"><option value="">Select province</option><option>British Columbia</option><option>Ontario</option><option>Alberta</option></select></label><label>Postal Code<input v-model="form.sitePostalCode" class="form-control" /></label><label class="span-3">Location / Access Details<textarea v-model="form.siteAccessDetails" class="form-control" rows="3" /></label>
                            </div></section>
                        </div>

                        <section class="pm-quote-summary-strip"><h2><b>3</b> Estimate / Cost Summary <small>(Shortcut)</small></h2><div class="pm-quote-summary-cards"><div><span>Sub Total</span><strong>{{ currencyLabel }} {{ formatMoney(subtotal) }}</strong></div><div><span>Discount</span><label><input v-model.number="discountAmount" class="form-control" type="number" min="0" step="0.01" /><small>{{ currencyLabel }} {{ formatMoney(discountAmount) }}</small></label></div><div><span>Discount Amount</span><strong>{{ currencyLabel }} {{ formatMoney(discountAmount) }}</strong></div><div><span>Sales Tax ({{ form.taxRate }}%)</span><strong>{{ currencyLabel }} {{ formatMoney(summaryTax) }}</strong></div><div class="total"><span>Total Estimate</span><strong>{{ currencyLabel }} {{ formatMoney(summaryTotal) }}</strong></div></div></section>

                        <div class="pm-quote-settings-grid">
                            <section class="pm-quote-panel"><h2><b>4</b> Sales Tax</h2><label>Tax Type<select class="form-control"><option>GST (5%)</option><option>HST</option><option>PST</option></select></label><label>Tax Rate<input v-model.number="form.taxRate" class="form-control" type="number" min="0" step="0.01" /></label><label>Tax Registration No.<input v-model="form.taxRegistrationNo" class="form-control" /></label></section>
                            <section class="pm-quote-panel"><h2><b>5</b> Payment Method</h2><div class="pm-payment-options"><label v-for="method in paymentMethods" :key="method"><input v-model="form.paymentMethods" type="checkbox" :value="method" /> {{ method }}</label></div></section>
                            <section class="pm-quote-panel"><h2><b>6</b> Payment Terms &amp; Timeline</h2><label>Payment Terms <em>*</em><select v-model="form.paymentTerms" class="form-control"><option>Net 15</option><option>Net 30</option><option>Due on receipt</option></select></label><div class="pm-quote-mini-grid"><label>Deposit Required<input v-model.number="form.depositPercentage" class="form-control" type="number" min="0" max="100" /></label><label>Deposit Amount<input v-model.number="form.depositAmount" class="form-control" type="number" min="0" /></label></div><label>Estimated Start Date<input v-model="form.startAt" class="form-control" type="datetime-local" /></label><label>Estimated Completion Date<input v-model="form.endAt" class="form-control" type="datetime-local" /></label></section>
                            <section class="pm-quote-panel"><h2><b>7</b> Notes to Customer</h2><textarea v-model="form.notesToCustomer" class="form-control pm-notes" rows="8" placeholder="Add customer-facing notes..." /></section>
                            <section class="pm-quote-panel"><h2><b>8</b> Service Invoice (If Accepted)</h2><label>Invoice To<select v-model="form.invoiceTo" class="form-control"><option>Same as Customer</option><option>Other</option></select></label><label>Invoice Title<input v-model="form.invoiceTitle" class="form-control" /></label><div class="pm-quote-mini-grid"><label>Invoice Prefix<input v-model="form.invoicePrefix" class="form-control" /></label><label>Next #<input v-model.number="form.invoiceNextNumber" class="form-control" type="number" /></label></div><label class="pm-check"><input v-model="form.createInvoiceAfterApproval" type="checkbox" /> Create Invoice after Customer Approval</label></section>
                        </div>
                        <div class="pm-quote-actions" role="group" aria-label="Estimate actions"><button type="button" @click="resetForm">＋ New</button><button type="button" :disabled="!form.id" @click="notifyAction('Update')">✎ Edit</button><button type="button" :disabled="!form.id" @click="notifyAction('Delete')">♲ Delete</button><button type="button" @click="notifyAction('Save')">▣ Save</button><button type="button" @click="notifyAction('Save PDF')">▧ Save PDF</button><button type="button" @click="notifyAction('Print')">▣ Print</button><button type="button" @click="notifyAction('Email')">✉ Email</button><button type="button" @click="convertToJobOrder">⇥ Save &amp; Out</button></div>
                    </section>

                    <template v-if="viewMode !== 'form'">
                    <section
                        v-if="viewMode === 'list'"
                        class="pm-card pm-ops-card p-4 pm-account-holder-list-shell"
                    >
                        <div class="pm-account-holder-list-toolbar">
                            <div>
                                <h5 class="pm-form-title">Estimation List</h5>
                                <div class="pm-account-holder-list-meta">
                                    {{ filteredItems.length }} records found
                                </div>
                            </div>
                            <div class="pm-account-holder-list-search-row">
                                <div class="pm-account-holder-list-search">
                                    <input
                                        v-model.trim="listSearchQuery"
                                        class="form-control"
                                        type="search"
                                        placeholder="Search estimations..."
                                    />
                                </div>
                                <div class="pm-filter-row">
                                    <select
                                        v-model="filterStatus"
                                        class="form-control pm-filter-select"
                                    >
                                        <option value="">All Status</option>
                                        <option value="1">Pending</option>
                                        <option value="2">Approved</option>
                                        <option value="3">Cancelled</option>
                                        <option value="4">Expired</option>
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
                                        <th
                                            v-for="column in listColumns"
                                            :key="column.key"
                                            scope="col"
                                        >
                                            <button
                                                class="pm-account-holder-sort"
                                                type="button"
                                                @click="toggleSort(column.key)"
                                            >
                                                <span>{{ column.label }}</span>
                                                <span
                                                    class="pm-account-holder-sort-icon"
                                                    :class="{
                                                        'is-active':
                                                            listSortKey ===
                                                            column.key,
                                                        'is-desc':
                                                            listSortKey ===
                                                                column.key &&
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
                                            <span
                                                class="pm-account-holder-table-code"
                                            >
                                                {{ item.estimation_no || "--" }}
                                            </span>
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
                                                        ?.name || "--"
                                                }}
                                            </div>
                                            <div
                                                class="pm-account-holder-table-secondary"
                                            >
                                                {{
                                                    item.customer?.company_name ||
                                                    "--"
                                                }}
                                            </div>
                                        </td>
                                        <td>
                                            {{ item.job_site?.name || "--" }}
                                        </td>
                                        <td class="pm-estimation-total-green">
                                            {{ item.currency_label || "USD" }}
                                            {{ formatMoney(item.total) }}
                                        </td>
                                        <td>
                                            <span
                                                class="pm-account-holder-status"
                                                :class="
                                                    item.status === 1
                                                        ? 'is-pending'
                                                        : item.status === 2
                                                          ? 'is-approved'
                                                          : item.status === 3
                                                            ? 'is-cancelled'
                                                            : 'is-expired'
                                                "
                                            >
                                                {{ item.status_label || "--" }}
                                            </span>
                                        </td>
                                        <td>
                                            {{
                                                item.customer_approve_by || "--"
                                            }}
                                        </td>
                                        <td>
                                            <div
                                                class="pm-account-holder-table-actions"
                                            >
                                                <button
                                                    class="btn btn-outline-primary btn-sm"
                                                    type="button"
                                                    @click="startView(item)"
                                                    title="View"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="16"
                                                        height="16"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    >
                                                        <path
                                                            d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                                        ></path>
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="3"
                                                        ></circle>
                                                    </svg>
                                                </button>
                                                <button
                                                    class="btn btn-primary btn-sm"
                                                    type="button"
                                                    @click="startEdit(item)"
                                                    title="Edit"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="16"
                                                        height="16"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    >
                                                        <path
                                                            d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                                                        ></path>
                                                        <path
                                                            d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                                                        ></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td
                                            class="pm-account-holder-table-empty"
                                            :colspan="listColumns.length + 1"
                                        >
                                            No estimations match your search.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pm-account-holder-list-footer">
                            <div class="pm-account-holder-pagination-summary">
                                Showing {{ paginationStart }}-{{
                                    paginationEnd
                                }}
                                of
                                {{ filteredItems.length }}
                            </div>
                            <div
                                v-if="totalPages > 1"
                                class="pm-account-holder-pagination"
                            >
                                <button
                                    class="pm-account-holder-page-button"
                                    type="button"
                                    :disabled="listPage === 1"
                                    @click="changePage(listPage - 1)"
                                >
                                    Prev
                                </button>
                                <button
                                    v-for="page in visiblePages"
                                    :key="page"
                                    class="pm-account-holder-page-button"
                                    :class="{ 'is-active': page === listPage }"
                                    type="button"
                                    @click="changePage(page)"
                                >
                                    {{ page }}
                                </button>
                                <button
                                    class="pm-account-holder-page-button"
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
                        class="pm-card pm-ops-card p-4 pm-account-holder-detail-shell"
                    >
                        <section
                            class="pm-card pm-ops-card p-4 pm-account-holder-detail-code-card"
                        >
                            <div>
                                <div
                                    class="pm-account-holder-detail-code-label"
                                >
                                    Estimation Number
                                </div>
                                <div
                                    class="pm-account-holder-detail-code-value"
                                >
                                    {{ detailItem.estimation_no || "--" }}
                                </div>
                            </div>
                            <div class="pm-account-holder-detail-badges">
                                <span
                                    class="pm-account-holder-status"
                                    :class="
                                        detailItem.status === 1
                                            ? 'is-pending'
                                            : detailItem.status === 2
                                              ? 'is-approved'
                                              : detailItem.status === 3
                                                ? 'is-cancelled'
                                                : 'is-expired'
                                    "
                                >
                                    {{ detailItem.status_label || "--" }}
                                </span>
                            </div>
                        </section>

                        <section class="pm-account-holder-detail-section">
                            <div class="pm-account-holder-detail-section-head">
                                Estimation Information
                            </div>
                            <div
                                class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                            >
                                <div class="pm-account-holder-detail-grid">
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Date Issued:</span
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                formatDate(
                                                    detailItem.issue_date,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Expiry Date:</span
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                formatDate(
                                                    detailItem.expire_date,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Job Type:</span
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                detailItem.job_type_label ||
                                                "--"
                                            }}</span
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Customer:</span
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                detailItem.customer?.name ||
                                                "--"
                                            }}</span
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Job Site:</span
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                detailItem.job_site?.name ||
                                                "--"
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-account-holder-detail-section">
                            <div class="pm-account-holder-detail-section-head">
                                Financial Summary
                            </div>
                            <div
                                class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                            >
                                <div class="pm-account-holder-detail-grid">
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Subtotal:</span
                                        >
                                        >
                                        <span
                                            class="pm-account-holder-detail-value pm-estimation-total-green"
                                            >{{
                                                detailItem.currency_label ||
                                                "USD"
                                            }}
                                            {{
                                                formatMoney(
                                                    detailItem.sub_total,
                                                )
                                            }}</span
                                        >
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Tax:</span
                                        >
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                detailItem.currency_label ||
                                                "USD"
                                            }}
                                            {{
                                                formatMoney(detailItem.tax)
                                            }}</span
                                        >
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Discount:</span
                                        >
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                detailItem.currency_label ||
                                                "USD"
                                            }}
                                            {{
                                                formatMoney(detailItem.discount)
                                            }}</span
                                        >
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Grand Total:</span
                                        >
                                        >
                                        <span
                                            class="pm-account-holder-detail-value pm-estimation-total-green"
                                            >{{
                                                detailItem.currency_label ||
                                                "USD"
                                            }}
                                            {{
                                                formatMoney(detailItem.total)
                                            }}</span
                                        >
                                        >
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section
                            v-if="detailItem.note"
                            class="pm-account-holder-detail-section"
                        >
                            <div class="pm-account-holder-detail-section-head">
                                Notes
                            </div>
                            <div
                                class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                            >
                                <div class="pm-account-holder-detail-grid">
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{ detailItem.note }}</span
                                        >
                                        >
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-account-holder-detail-section">
                            <div
                                class="pm-account-holder-detail-section-head pm-account-holder-detail-section-head--muted"
                            >
                                System Information
                            </div>
                            <div
                                class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                            >
                                <div class="pm-account-holder-detail-grid">
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Created Date:</span
                                        >
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                formatDate(
                                                    detailItem.created_at,
                                                )
                                            }}</span
                                        >
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Last Modified:</span
                                        >
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{
                                                formatDate(
                                                    detailItem.updated_at,
                                                )
                                            }}</span
                                        >
                                        >
                                    </div>
                                    <div class="pm-account-holder-detail-row">
                                        <span
                                            class="pm-account-holder-detail-key"
                                            >Record ID:</span
                                        >
                                        >
                                        <span
                                            class="pm-account-holder-detail-value"
                                            >{{ detailItem.id || "--" }}</span
                                        >
                                        >
                                    </div>
                                </div>
                            </div>
                        </section>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">
                            Basic Information
                        </div>
                        <div class="pm-estimation-card-body">
                            <div class="pm-estimation-grid">
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Estimation No. (Auto Generate)</label
                                    >
                                    <input
                                        :value="estimationNumber"
                                        class="form-control"
                                        type="text"
                                        readonly
                                    />
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label">Status</label>
                                    <div
                                        class="pm-choice-list pm-choice-list--two"
                                    >
                                        <label
                                            v-for="status in statuses"
                                            :key="status"
                                            class="pm-choice-item"
                                        >
                                            <input
                                                v-model="form.status"
                                                type="radio"
                                                :value="status"
                                            />
                                            <span>{{ status }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Date Issued</label
                                    >
                                    <div
                                        class="pm-date-display-text"
                                        v-if="form.dateIssued"
                                    >
                                        {{ formattedIssuedDate }}
                                    </div>
                                    <input
                                        v-model="form.dateIssued"
                                        class="form-control"
                                        type="date"
                                    />
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Job Type</label
                                    >
                                    <div
                                        class="pm-choice-list pm-choice-list--two"
                                    >
                                        <label
                                            v-for="jobType in jobTypes"
                                            :key="jobType"
                                            class="pm-choice-item"
                                        >
                                            <input
                                                v-model="form.jobTypes"
                                                type="checkbox"
                                                :value="jobType"
                                            />
                                            <span>{{ jobType }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="pm-field-block">
                                    <div>
                                        <label class="pm-field-label"
                                            >Expiry Date / Time</label
                                        >
                                        <input
                                            v-model="form.expiryAt"
                                            class="form-control"
                                            type="datetime-local"
                                        />
                                    </div>
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Currency</label
                                    >
                                    <select
                                        v-model="form.currency"
                                        class="form-control"
                                    >
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                        <option value="BDT">BDT</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pm-field-block">
                                <label class="pm-field-label"
                                    >Job Description</label
                                >
                                <textarea
                                    v-model="form.jobDescription"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Enter detailed job description..."
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">Customer</div>
                        <div class="pm-estimation-card-body">
                            <div
                                class="pm-estimation-grid pm-estimation-grid--compact"
                            >
                                <div class="pm-field-block">
                                    <label class="pm-field-label">Name</label>
                                    <select
                                        v-model="form.customerId"
                                        @change="handleCustomerChange"
                                        class="form-control"
                                    >
                                        <option :value="null">
                                            Select customer from list
                                        </option>
                                        <option
                                            v-for="cust in customers"
                                            :key="cust.id"
                                            :value="cust.id"
                                        >
                                            {{ cust.account_name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Company</label
                                    >
                                    <input
                                        v-model="form.customerCompany"
                                        class="form-control"
                                        type="text"
                                        placeholder="Auto-loaded"
                                    />
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Contact</label
                                    >
                                    <input
                                        v-model="form.customerContact"
                                        class="form-control"
                                        type="text"
                                        placeholder="Auto-loaded"
                                    />
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Address</label
                                    >
                                    <input
                                        v-model="form.customerAddress"
                                        class="form-control"
                                        type="text"
                                        placeholder="Auto-loaded"
                                    />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">Job Site</div>
                        <div class="pm-estimation-card-body">
                            <div
                                class="pm-estimation-grid pm-estimation-grid--compact"
                            >
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Job Site No - Name</label
                                    >
                                    <select
                                        v-model="form.jobSiteId"
                                        @change="handleJobSiteChange"
                                        class="form-control"
                                    >
                                        <option :value="null">
                                            Select job site
                                        </option>
                                        <option
                                            v-for="site in jobSites"
                                            :key="site.id"
                                            :value="site.id"
                                        >
                                            {{ site.job_site_name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Address</label
                                    >
                                    <input
                                        v-model="form.jobSiteAddress"
                                        class="form-control"
                                        type="text"
                                        placeholder="Auto-loaded"
                                    />
                                </div>
                                <div
                                    class="pm-field-block pm-field-block--full"
                                >
                                    <label class="pm-field-label"
                                        >Map Link</label
                                    >
                                    <input
                                        v-model="form.mapLink"
                                        class="form-control"
                                        type="text"
                                        placeholder="Auto-loaded"
                                    />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">Schedule</div>
                        <div class="pm-estimation-card-body">
                            <div
                                class="pm-estimation-grid pm-estimation-grid--compact"
                            >
                                <div
                                    class="pm-field-block pm-field-block--split"
                                >
                                    <div>
                                        <label class="pm-field-label"
                                            >Start Date & Time</label
                                        >
                                        <input
                                            v-model="form.startAt"
                                            class="form-control"
                                            type="datetime-local"
                                        />
                                    </div>
                                    <div>
                                        <label class="pm-field-label"
                                            >End Date & Time</label
                                        >
                                        <input
                                            v-model="form.endAt"
                                            class="form-control"
                                            type="datetime-local"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="pm-estimation-highlight">
                                <div class="pm-estimation-highlight-label">
                                    Net Duration (Auto Calculate)
                                </div>
                                <strong>{{ netDurationLabel }}</strong>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">Scope of Work</div>
                        <div class="pm-estimation-card-body">
                            <textarea
                                v-model="form.scopeOfWork"
                                class="form-control"
                                rows="5"
                                placeholder="Example:
- Remove existing plumbing lines
- Install new pipes
- Test water pressure"
                            ></textarea>
                            <small class="pm-estimation-hint"
                                >Use bullet points or numbered lists for
                                clarity.</small
                            >
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div
                            class="pm-estimation-card-head pm-estimation-card-head--between"
                        >
                            <span>Cost Breakdown</span>
                            <button
                                class="pm-estimation-add-row"
                                type="button"
                                @click="addCostRow"
                            >
                                Add Row
                            </button>
                        </div>
                        <div class="pm-estimation-card-body">
                            <div class="table-responsive">
                                <table
                                    class="table pm-estimation-table align-middle"
                                >
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Item</th>
                                            <th>Description</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Sales Tax %</th>
                                            <th>Sales Tax Amount</th>
                                            <th>Total</th>
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
                                                    placeholder="e.g. Material"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model="row.description"
                                                    class="form-control form-control-sm"
                                                    type="text"
                                                    placeholder="e.g. PVC Pipe"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="row.qty"
                                                    class="form-control form-control-sm"
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="
                                                        row.unitPrice
                                                    "
                                                    class="form-control form-control-sm"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="
                                                        row.salesTax
                                                    "
                                                    class="form-control form-control-sm"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                />
                                            </td>
                                            <td>
                                                {{ currencyLabel }}
                                                {{
                                                    formatMoney(
                                                        calcRowSalesTaxAmount(
                                                            row,
                                                        ),
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{ currencyLabel }}
                                                {{
                                                    formatMoney(
                                                        calcRowLineTotal(row),
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <button
                                                    class="pm-estimation-delete-row"
                                                    type="button"
                                                    @click="
                                                        removeCostRow(row.id)
                                                    "
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="pm-estimation-summary">
                                <div class="pm-estimation-summary-row">
                                    <span>Subtotal:</span>
                                    <strong
                                        >{{ currencyLabel }}
                                        {{ formatMoney(subtotal) }}</strong
                                    >
                                </div>
                                <div class="pm-estimation-summary-row">
                                    <span>Discount:</span>
                                    <div class="pm-summary-input">
                                        {{ currencyLabel }}
                                        <input
                                            v-model.number="discountAmount"
                                            class="form-control form-control-sm"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                        />
                                    </div>
                                </div>
                                <div class="pm-estimation-summary-row">
                                    <span>Tax:</span>
                                    <div class="pm-summary-input">
                                        {{ currencyLabel }}
                                        <input
                                            v-model.number="taxAmount"
                                            class="form-control form-control-sm"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="pm-estimation-summary-row pm-estimation-summary-row--total"
                                >
                                    <span>Total Amount:</span>
                                    <strong
                                        >{{ currencyLabel }}
                                        {{ formatMoney(grandTotal) }}</strong
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">
                            Exclusions / Notes
                        </div>
                        <div class="pm-estimation-card-body">
                            <textarea
                                v-model="form.exclusions"
                                class="form-control"
                                rows="4"
                                placeholder="Example:
                                    - Painting not included
                                    - Permit fees excluded
                                    - Material price may vary"
                            ></textarea>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">
                            Payment Terms & Method
                        </div>
                        <div class="pm-estimation-card-body">
                            <div class="pm-estimation-grid">
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Payment Method</label
                                    >
                                    <div
                                        class="pm-choice-list pm-choice-list--split"
                                    >
                                        <div class="pm-choice-column">
                                            <label
                                                v-for="method in paymentMethods.slice(
                                                    0,
                                                    3,
                                                )"
                                                :key="method"
                                                class="pm-choice-item"
                                            >
                                                <input
                                                    v-model="
                                                        form.paymentMethods
                                                    "
                                                    type="checkbox"
                                                    :value="method"
                                                />
                                                <span>{{ method }}</span>
                                            </label>
                                        </div>
                                        <div class="pm-choice-column">
                                            <label
                                                v-for="method in paymentMethods.slice(
                                                    3,
                                                    6,
                                                )"
                                                :key="method"
                                                class="pm-choice-item"
                                            >
                                                <input
                                                    v-model="
                                                        form.paymentMethods
                                                    "
                                                    type="checkbox"
                                                    :value="method"
                                                />
                                                <span>{{ method }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Deposit Required</label
                                    >
                                    <div class="pm-inline-choice">
                                        <label class="pm-choice-item">
                                            <input
                                                v-model="form.depositRequired"
                                                type="radio"
                                                :value="true"
                                            />
                                            <span>Yes</span>
                                        </label>
                                        <label class="pm-choice-item">
                                            <input
                                                v-model="form.depositRequired"
                                                type="radio"
                                                :value="false"
                                            />
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>
                                <div
                                    class="pm-field-block pm-field-block--full"
                                >
                                    <label class="pm-field-label"
                                        >Payment Terms</label
                                    >
                                    <textarea
                                        v-model="form.paymentTerms"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Example:
50% advance
50% after completion"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">
                            Terms & Conditions
                        </div>
                        <div class="pm-estimation-card-body">
                            <textarea
                                v-model="form.termsConditions"
                                class="form-control"
                                rows="4"
                                placeholder="Example:
- Estimate valid for 7 days
- Price subject to material availability
- Work begins after deposit confirmation"
                            ></textarea>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card"
                    >
                        <div class="pm-estimation-card-head">
                            Customer Approval
                        </div>
                        <div class="pm-estimation-card-body">
                            <div class="pm-estimation-grid">
                                <div
                                    class="pm-field-block pm-field-block--split"
                                >
                                    <div>
                                        <label class="pm-field-label"
                                            >Date / Time</label
                                        >
                                        <input
                                            v-model="form.approvalAt"
                                            class="form-control"
                                            type="datetime-local"
                                        />
                                    </div>
                                    <div>
                                        <label class="pm-field-label"
                                            >Approved By</label
                                        >
                                        <input
                                            v-model="form.approvedBy"
                                            class="form-control"
                                            type="text"
                                            placeholder="Enter name"
                                        />
                                    </div>
                                </div>
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >Approval Method</label
                                    >
                                    <div
                                        class="pm-choice-list pm-choice-list--split"
                                    >
                                        <div class="pm-choice-column">
                                            <label
                                                v-for="method in approvalMethods.slice(
                                                    0,
                                                    2,
                                                )"
                                                :key="method"
                                                class="pm-choice-item"
                                            >
                                                <input
                                                    v-model="
                                                        form.approvalMethods
                                                    "
                                                    type="checkbox"
                                                    :value="method"
                                                />
                                                <span>{{ method }}</span>
                                            </label>
                                        </div>
                                        <div class="pm-choice-column">
                                            <label
                                                v-for="method in approvalMethods.slice(
                                                    2,
                                                    4,
                                                )"
                                                :key="method"
                                                class="pm-choice-item"
                                            >
                                                <input
                                                    v-model="
                                                        form.approvalMethods
                                                    "
                                                    type="checkbox"
                                                    :value="method"
                                                />
                                                <span>{{ method }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-estimation-card pm-estimation-card--success"
                    >
                        <div
                            class="pm-estimation-card-head pm-estimation-card-head--success"
                        >
                            Convert to Job Order
                        </div>
                        <div class="pm-estimation-card-body">
                            <div
                                class="pm-inline-choice pm-inline-choice--highlight"
                            >
                                <span>Convert to Job Order:</span>
                                <label class="pm-choice-item">
                                    <input
                                        v-model="form.convertToJobOrder"
                                        :disabled="!form.id"
                                        type="radio"
                                        :value="true"
                                    />
                                    <span>Yes</span>
                                </label>
                                <label class="pm-choice-item">
                                    <input
                                        v-model="form.convertToJobOrder"
                                        :disabled="!form.id"
                                        type="radio"
                                        :value="false"
                                    />
                                    <span>No</span>
                                </label>
                            </div>
                            <div class="pm-estimation-convert-action">
                                <button
                                    class="pm-estimation-convert-btn"
                                    type="button"
                                    :disabled="!form.id"
                                    @click="convertToJobOrder"
                                >
                                    Convert to Job Order
                                </button>
                            </div>
                        </div>
                    </section>

                    <div
                        v-if="viewMode === 'form'"
                        class="pm-estimation-footer-actions"
                    >
                        <div class="pm-estimation-footer-group">
                            <button
                                class="pm-estimation-action-btn"
                                type="button"
                                :disabled="!form.id"
                                @click="notifyAction('New')"
                            >
                                New
                            </button>
                            <button
                                class="pm-estimation-action-btn"
                                type="button"
                                :disabled="!form.id"
                                @click="notifyAction('Update')"
                            >
                                Update
                            </button>
                            <button
                                class="pm-estimation-action-btn pm-estimation-action-btn--danger"
                                type="button"
                                :disabled="!form.id"
                                @click="notifyAction('Delete')"
                            >
                                Delete
                            </button>
                        </div>
                        <div class="pm-estimation-footer-group">
                            <button
                                v-if="!form.id"
                                class="pm-estimation-action-btn pm-estimation-action-btn--primary"
                                type="button"
                                @click="notifyAction('Save')"
                            >
                                Save
                            </button>
                            <button
                                class="pm-estimation-action-btn"
                                type="button"
                                @click="notifyAction('Save PDF')"
                            >
                                Save PDF
                            </button>
                            <button
                                class="pm-estimation-action-btn"
                                type="button"
                                @click="notifyAction('Print')"
                            >
                                Print
                            </button>
                            <button
                                class="pm-estimation-action-btn"
                                type="button"
                                @click="notifyAction('Email')"
                            >
                                Email
                            </button>
                            <button
                                class="pm-estimation-action-btn pm-estimation-action-btn--success"
                                type="button"
                                @click="convertToJobOrder"
                            >
                                Convert to Job Order
                            </button>
                        </div>
                    </div>
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
import { computed, onMounted, reactive, ref, watch, nextTick } from "vue";
import { useRouter, useRoute } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
import { clearToken, logout as logoutRequest } from "../api/auth";
import { authState } from "../store/auth";
import { setFlash } from "../store/flash";
import client from "../api/client";

const router = useRouter();
const route = useRoute();
const searchQuery = ref("");
const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const userName = ref("John Doe");
const topActions = ["Open", "List", "Save", "PDF", "Print", "Email"];
const statuses = ["Pending", "Approved", "Cancelled", "Expired"];
const jobTypes = [
    "Plumbing",
    "Painting",
    "Electrical",
    "Cleaning",
    "Carpentry",
    "Others",
];
const paymentMethods = [
    "Cash",
    "Credit Card",
    "Debit Card",
    "Bank Transfer",
    "Check",
    "Others",
];
const approvalMethods = [
    "Phone Call",
    "Text Message",
    "Email",
    "In Person",
    "Mail",
];

const currencyMap = { USD: 1, EUR: 2, GBP: 3, BDT: 4 };
const statusMap = { Pending: 1, Approved: 2, Cancelled: 3, Expired: 4 };
const jobTypeMap = {
    Plumbing: 1,
    Painting: 2,
    Electrical: 3,
    Cleaning: 4,
    Carpentry: 5,
    Others: 6,
};
const paymentMethodMap = {
    Cash: 1,
    "Credit Card": 2,
    "Debit Card": 3,
    "Bank Transfer": 4,
    Check: 5,
    Others: 6,
};
const approvalMethodMap = {
    "Phone Call": 1,
    "Text Message": 2,
    Email: 3,
    "In Person": 4,
    Mail: 5,
};

const reverseCurrencyMap = { 1: "USD", 2: "EUR", 3: "GBP", 4: "BDT" };
const reverseStatusMap = {
    1: "Pending",
    2: "Approved",
    3: "Cancelled",
    4: "Expired",
};
const reverseJobTypeMap = {
    1: "Plumbing",
    2: "Painting",
    3: "Electrical",
    4: "Cleaning",
    5: "Carpentry",
    6: "Others",
};
const reversePaymentMethodMap = {
    1: "Cash",
    2: "Credit Card",
    3: "Debit Card",
    4: "Bank Transfer",
    5: "Check",
    6: "Others",
};
const reverseApprovalMethodMap = {
    1: "Phone Call",
    2: "Text Message",
    3: "Email",
    4: "In Person",
    5: "Mail",
};
let costRowId = 2;

// List view state
// Start on the records list, matching the Job Orders workflow. The existing
// New action switches to the estimate/quote editor.
const viewMode = ref("list");
const activeId = ref(null);
const detailItem = ref(null);
const listSearchQuery = ref("");
const listSortKey = ref("");
const listSortDirection = ref("asc");
const listPage = ref(1);
const perPage = 15;
const listItems = ref([]);
const filterStatus = ref("");

const listColumns = [
    { key: "estimation_no", label: "Estimate No" },
    { key: "issue_date", label: "Date Issued" },
    { key: "customer", label: "Customer" },
    { key: "job_site", label: "Job Site" },
    { key: "total", label: "Grand Total" },
    { key: "status", label: "Status" },
    { key: "customer_approve_by", label: "Prepared By" },
];

const filteredItems = computed(() => {
    let items = listItems.value;
    if (listSearchQuery.value) {
        const q = listSearchQuery.value.toLowerCase();
        items = items.filter(
            (item) =>
                (item.estimation_no || "").toLowerCase().includes(q) ||
                (item.customer?.name || "").toLowerCase().includes(q) ||
                (item.job_site?.name || "").toLowerCase().includes(q) ||
                (item.customer_approve_by || "").toLowerCase().includes(q),
        );
    }
    if (filterStatus.value !== "") {
        items = items.filter(
            (item) => String(item.status) === filterStatus.value,
        );
    }
    if (listSortKey.value) {
        const key = listSortKey.value;
        const dir = listSortDirection.value === "asc" ? 1 : -1;
        items = [...items].sort((a, b) => {
            let valA = a[key];
            let valB = b[key];
            if (key === "customer") {
                valA = a.customer?.name || "";
                valB = b.customer?.name || "";
            }
            if (key === "job_site") {
                valA = a.job_site?.name || "";
                valB = b.job_site?.name || "";
            }
            if (typeof valA === "string") return valA.localeCompare(valB) * dir;
            return ((valA || 0) - (valB || 0)) * dir;
        });
    }
    return items;
});

const totalPages = computed(() =>
    Math.ceil(filteredItems.value.length / perPage),
);
const paginationStart = computed(() => (listPage.value - 1) * perPage + 1);
const paginationEnd = computed(() =>
    Math.min(listPage.value * perPage, filteredItems.value.length),
);
const paginatedItems = computed(() =>
    filteredItems.value.slice(
        (listPage.value - 1) * perPage,
        listPage.value * perPage,
    ),
);
const visiblePages = computed(() => {
    const pages = [];
    const maxVisible = 5;
    let start = Math.max(1, listPage.value - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages.value, start + maxVisible - 1);
    if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
});

function openListView() {
    viewMode.value = "list";
    activeId.value = null;
    detailItem.value = null;
    loadList();
}
function openFormView() {
    viewMode.value = "form";
    activeId.value = null;
    detailItem.value = null;
    resetForm();
}
function startView(item) {
    detailItem.value = item;
    viewMode.value = "detail";
}
function startEdit(item) {
    activeId.value = item.id;
    viewMode.value = "form";
    loadEstimation(item.id);
}
function toggleSort(key) {
    if (listSortKey.value === key) {
        listSortDirection.value =
            listSortDirection.value === "asc" ? "desc" : "asc";
    } else {
        listSortKey.value = key;
        listSortDirection.value = "asc";
    }
}
function changePage(page) {
    if (page >= 1 && page <= totalPages.value) listPage.value = page;
}
async function loadList() {
    try {
        const { data } = await client.get("/estimations");
        listItems.value = data?.data?.data || [];
    } catch (err) {
        console.error("Failed to load estimations", err);
    }
}

const form = reactive({
    id: null,
    status: "Pending",
    dateIssued: formatDateInput(new Date()),
    expiryAt: formatDateTimeLocal(addDays(new Date(), 7)),
    currency: "USD",
    jobTypes: ["Plumbing"],
    jobDescription: "",
    customerId: null,
    customerName: "",
    customerCompany: "",
    customerContact: "",
    customerAddress: "",
    jobSiteId: null,
    jobSite: "",
    jobSiteAddress: "",
    mapLink: "",
    startAt: "",
    endAt: "",
    scopeOfWork: "",
    exclusions: "",
    paymentMethods: ["Cash"],
    depositRequired: false,
    paymentTerms: "",
    termsConditions: "",
    approvalAt: "",
    approvedBy: "",
    approvalMethods: ["Email"],
    convertToJobOrder: false,
    customerType: "Residential",
    siteFloorLevel: "",
    siteSuiteUnit: "",
    siteCity: "",
    siteProvince: "",
    sitePostalCode: "",
    siteAccessDetails: "",
    taxRate: 0,
    taxRegistrationNo: "",
    depositPercentage: 0,
    depositAmount: 0,
    notesToCustomer: "",
    invoiceTo: "Same as Customer",
    invoiceTitle: "",
    invoicePrefix: "INV-",
    invoiceNextNumber: 1001,
    createInvoiceAfterApproval: false,
});

const costRows = ref([
    {
        id: 1,
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

const estimationNumber = ref("");
const customers = ref([]);
const jobSites = ref([]);

const currencyLabel = computed(() => form.currency || "USD");

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

function calcRowSalesTaxAmount(row) {
    const qty = toNumber(row.qty);
    const unitPrice = toNumber(row.unitPrice);
    const salesTax = toNumber(row.salesTax);
    const baseAmount = qty * unitPrice;
    return baseAmount * (salesTax / 100);
}

function calcRowLineTotal(row) {
    const qty = toNumber(row.qty);
    const unitPrice = toNumber(row.unitPrice);
    const salesTax = toNumber(row.salesTax);
    const baseAmount = qty * unitPrice;
    const salesTaxAmount = baseAmount * (salesTax / 100);
    return baseAmount + salesTaxAmount;
}

const subtotal = computed(() =>
    costRowsWithTotals.value.reduce((sum, row) => sum + row.lineTotal, 0),
);
const taxAmount = ref(0);
const discountAmount = ref(0);
const grandTotal = computed(
    () =>
        subtotal.value +
        toNumber(taxAmount.value) -
        toNumber(discountAmount.value),
);

const summaryTax = computed(
    () => Math.max(0, subtotal.value - toNumber(discountAmount.value)) * (toNumber(form.taxRate) / 100),
);
const summaryTotal = computed(
    () => Math.max(0, subtotal.value - toNumber(discountAmount.value)) + summaryTax.value,
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

const formattedIssuedDate = computed(() => {
    if (!form.dateIssued) return "";
    const date = new Date(form.dateIssued);
    const day = date.getDate();
    const month = date.toLocaleString("en-US", { month: "long" }).toLowerCase();
    const year = date.getFullYear();

    const getOrdinal = (n) => {
        const s = ["th", "st", "nd", "rd"],
            v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    };

    return `${getOrdinal(day)} ${month}, ${year}`;
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

const fetchData = async () => {
    try {
        const [custRes, siteRes] = await Promise.all([
            client.get("/estimations/customers"),
            client.get("/estimations/job-sites"),
        ]);

        customers.value = custRes.data?.data || [];
        jobSites.value = siteRes.data?.data || [];
    } catch (err) {
        console.error("Failed to load estimation metadata", err);
    }
};

onMounted(async () => {
    await fetchData();
    await loadList();
    if (!form.startAt) {
        form.startAt = formatDateTimeLocal(new Date());
        form.endAt = formatDateTimeLocal(addHours(new Date(), 4));
    }
    const estId = route.params.id;
    if (estId) {
        viewMode.value = "form";
        await loadEstimation(estId);
    }
});

async function loadEstimation(id) {
    try {
        const { data } = await client.get(`/estimations/${id}`);
        const est = data?.data;
        if (!est) {
            setFlash("Estimation not found.", "warning", 2200);
            return;
        }
        form.id = est.id;
        form.status = reverseStatusMap[est.status] || "Pending";
        form.dateIssued = est.issue_date ? est.issue_date.split(" ")[0] : "";
        form.expiryAt = est.expire_date ? est.expire_date.slice(0, 16) : "";
        form.currency = reverseCurrencyMap[est.currency] || "USD";
        form.jobTypes = est.job_type ? [reverseJobTypeMap[est.job_type]] : [];
        form.jobDescription = est.job_description || "";
        form.customerId = est.customer_id || null;
        form.jobSiteId = est.job_site_id || null;
        form.startAt = est.schedule_start_date ? est.schedule_start_date.slice(0, 16) : "";
        form.endAt = est.schedule_end_date ? est.schedule_end_date.slice(0, 16) : "";
        form.scopeOfWork = est.scope_of_work || "";
        form.exclusions = est.note || "";
        form.paymentMethods = est.payment_method
            ? [reversePaymentMethodMap[est.payment_method]]
            : [];
        form.depositRequired = est.deposit_required || false;
        form.paymentTerms = est.payment_term || "";
        form.approvalAt = est.customer_approval_date ? est.customer_approval_date.slice(0, 16) : "";
        form.approvedBy = est.customer_approve_by || "";
        form.approvalMethods = est.approval_method
            ? [reverseApprovalMethodMap[est.approval_method]]
            : [];
        form.convertToJobOrder = est.convert_to_job_order || false;
        form.customerType = est.customer_type || "Residential";
        form.siteFloorLevel = est.site_floor_level || "";
        form.siteSuiteUnit = est.site_suite_unit || "";
        form.siteCity = est.site_city || "";
        form.siteProvince = est.site_province || "";
        form.sitePostalCode = est.site_postal_code || "";
        form.siteAccessDetails = est.site_access_details || "";
        form.taxRate = est.tax_rate ?? 0;
        form.taxRegistrationNo = est.tax_registration_no || "";
        form.depositPercentage = est.deposit_percentage ?? 0;
        form.depositAmount = est.deposit_amount ?? 0;
        form.notesToCustomer = est.notes_to_customer || "";
        form.invoiceTo = est.invoice_to || "Same as Customer";
        form.invoiceTitle = est.invoice_title || "";
        form.invoicePrefix = est.invoice_prefix || "INV-";
        form.invoiceNextNumber = est.invoice_next_number ?? 1001;
        form.createInvoiceAfterApproval = Boolean(est.create_invoice_after_approval);
        taxAmount.value = est.tax ?? 0;
        discountAmount.value = est.discount ?? 0;
        estimationNumber.value = est.estimation_no || "";
        costRows.value = (est.details || []).map((row, idx) => ({
            id: costRowId++,
            item: row.item_name || "",
            description: row.item_description || "",
            qty: row.quantity || 1,
            unitPrice: row.unit_price || 0,
            salesTax: row.sale_tax_percentage || 0,
        }));
        if (costRows.value.length === 0) {
            costRows.value.push({
                id: costRowId++,
                item: "",
                description: "",
                qty: 1,
                unitPrice: 0,
                salesTax: 0,
            });
        }
        // Populate customer fields directly from loaded estimation data
        if (est.customer) {
            form.customerName = est.customer.name || est.customer.account_name || "";
            form.customerCompany = est.customer.company_name || "";
            form.customerContact = [est.customer.cell_phone, est.customer.email].filter(Boolean).join(", ");
            form.customerAddress = [est.customer.house_number, est.customer.street_number, est.customer.city, est.customer.state].filter(Boolean).join(" ");
        }
        // Populate job site fields directly from loaded estimation data
        if (est.job_site) {
            form.jobSite = est.job_site.name || est.job_site.job_site_name || "";
            form.jobSiteAddress = est.job_site.address || "";
            form.mapLink = est.job_site.map_link || "";
        }
    } catch (err) {
        console.error("Failed to load estimation", err);
    }
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

function handleCustomerChange() {
    const selected = customers.value.find((c) => c.id === form.customerId);
    if (selected) {
        form.customerName = selected.account_name;
        form.customerCompany = selected.company_name || "";
        form.customerContact =
            selected.cell_phone + ", " + selected.email || "";
        form.customerAddress =
            `${selected.house_number || ""} ${selected.street_number || ""}${selected.city ? ", " + selected.city : ""}${selected.state ? ", " + selected.state : ""}`.trim();
    }
}

function handleJobSiteChange() {
    const selected = jobSites.value.find((s) => s.id === form.jobSiteId);
    if (selected) {
        form.jobSite = selected.job_site_name;
        form.jobSiteAddress = selected.address || "";
        form.mapLink = selected.map_link || "";
    }
}

function notifyAction(action) {
    if (action === "Save") {
        saveEstimation();
    } else if (action === "Update") {
        updateEstimation();
    } else if (action === "Delete") {
        deleteEstimation();
    } else if (action === "New") {
        resetForm();
    } else {
        setFlash(
            `${action} action is ready for estimation entry.`,
            "info",
            1800,
        );
    }
}

function convertToJobOrder() {
    form.convertToJobOrder = true;
    setFlash("Estimation marked for job order conversion.", "success", 2200);
}

async function convertToQuotation() {
    try {
        const estimationId = form.id || detailItem.value?.id;
        if (!estimationId) {
            setFlash("No estimation selected for conversion.", "warning", 2200);
            return;
        }

        const payload = {
            estimation_id: estimationId,
            issue_date: form.dateIssued || detailItem.value?.issue_date,
            expire_date: form.expiryAt || detailItem.value?.expire_date,
            currency: currencyMap[form.currency] || 1,
            status: statusMap[form.status] || 1,
            job_type: form.jobTypes.length ? jobTypeMap[form.jobTypes[0]] || 1 : 1,
            job_description: form.jobDescription || detailItem.value?.job_description || "",
            customer_id: form.customerId || detailItem.value?.customer_id,
            job_site_id: form.jobSiteId || detailItem.value?.job_site_id,
            schedule_start_date: form.startAt || detailItem.value?.schedule_start_date || null,
            schedule_end_date: form.endAt || detailItem.value?.schedule_end_date || null,
            scope_of_work: form.scopeOfWork || detailItem.value?.scope_of_work || "",
            sub_total: subtotal.value || detailItem.value?.sub_total || 0,
            tax: taxAmount.value || detailItem.value?.tax || null,
            discount: discountAmount.value || detailItem.value?.discount || null,
            total: grandTotal.value || detailItem.value?.total || 0,
            note: form.exclusions || detailItem.value?.note || "",
            payment_method: form.paymentMethods.length
                ? paymentMethodMap[form.paymentMethods[0]] || 1
                : null,
            deposit_required: form.depositRequired || detailItem.value?.deposit_required || false,
            payment_term: form.paymentTerms || detailItem.value?.payment_term || "",
            customer_approval_date: form.approvalAt || detailItem.value?.customer_approval_date || null,
            customer_approve_by: form.approvedBy || detailItem.value?.customer_approve_by || "",
            approval_method: form.approvalMethods.length
                ? approvalMethodMap[form.approvalMethods[0]] || 3
                : null,
            details: costRows.value.length
                ? costRows.value.map((row) => ({
                    item_name: row.item,
                    item_description: row.description,
                    quantity: row.qty,
                    unit_price: row.unitPrice,
                    sale_tax_percentage: row.salesTax,
                }))
                : (detailItem.value?.details || []).map((row) => ({
                    item_name: row.item_name || "",
                    item_description: row.item_description || "",
                    quantity: row.quantity || 1,
                    unit_price: row.unit_price || 0,
                    sale_tax_percentage: row.sale_tax_percentage || 0,
                })),
        };

        const { data } = await client.post("/quotations", payload);

        if (data?.data?.id) {
            setFlash("Quotation created from estimation successfully.", "success", 2200);
            router.push(`/job-orders/quotation/${data.data.id}`);
        }
    } catch (err) {
        console.error("Failed to convert estimation to quotation", err);
        const errorMessage = err.response?.data?.message || "Failed to convert estimation to quotation. Please try again.";
        setFlash(errorMessage, "warning", 3000);
    }
}

async function logout() {
    try {
        await logoutRequest();
    } catch {
        // Ignore request errors and clear client state locally.
    } finally {
        clearToken();
        router.push("/login");
    }
}

function buildPayload() {
    return {
        issue_date: form.dateIssued,
        expire_date: form.expiryAt,
        currency: currencyMap[form.currency] || 1,
        status: statusMap[form.status] || 1,
        job_type: form.jobTypes.length ? jobTypeMap[form.jobTypes[0]] || 1 : 1,
        job_description: form.jobDescription,
        customer_id: form.customerId,
        job_site_id: form.jobSiteId,
        schedule_start_date: form.startAt,
        schedule_end_date: form.endAt,
        scope_of_work: form.scopeOfWork,
        note: form.exclusions,
        payment_method: form.paymentMethods.length
            ? paymentMethodMap[form.paymentMethods[0]] || 1
            : null,
        deposit_required: form.depositRequired,
        payment_term: form.paymentTerms,
        customer_approval_date: form.approvalAt,
        customer_approve_by: form.approvedBy,
        approval_method: form.approvalMethods.length
            ? approvalMethodMap[form.approvalMethods[0]] || 3
            : null,
        convert_to_job_order: form.convertToJobOrder,
        customer_type: form.customerType,
        site_floor_level: form.siteFloorLevel,
        site_suite_unit: form.siteSuiteUnit,
        site_city: form.siteCity,
        site_province: form.siteProvince,
        site_postal_code: form.sitePostalCode,
        site_access_details: form.siteAccessDetails,
        tax_rate: form.taxRate || null,
        tax_registration_no: form.taxRegistrationNo,
        payment_methods: form.paymentMethods,
        deposit_percentage: form.depositPercentage || null,
        deposit_amount: form.depositAmount || null,
        notes_to_customer: form.notesToCustomer,
        invoice_to: form.invoiceTo,
        invoice_title: form.invoiceTitle,
        invoice_prefix: form.invoicePrefix,
        invoice_next_number: form.invoiceNextNumber || null,
        create_invoice_after_approval: form.createInvoiceAfterApproval,
        tax: taxAmount.value || null,
        discount: discountAmount.value || null,
        details: costRows.value.map((row) => ({
            item_name: row.item,
            item_description: row.description,
            quantity: row.qty,
            unit_price: row.unitPrice,
            sale_tax_percentage: row.salesTax,
        })),
    };
}

async function saveEstimation() {
    try {
        const payload = buildPayload();
        const { data } = await client.post("/estimations", payload);
        if (data?.data?.id) {
            form.id = data.data.id;
            estimationNumber.value = data.data.estimation_no || "";
            setFlash("Estimation saved successfully.", "success", 2200);
            if (form.convertToJobOrder) {
                router.push({
                    path: "/job-orders/orders",
                    query: { estimation_id: data.data.id },
                });
            }
        }
    } catch (err) {
        console.error("Failed to save estimation", err);
        setFlash(
            "Failed to save estimation. Please try again.",
            "warning",
            3000,
        );
    }
}

async function updateEstimation() {
    if (!form.id) {
        setFlash("No estimation to update.", "warning", 2200);
        return;
    }
    try {
        const payload = buildPayload();
        const { data } = await client.put(`/estimations/${form.id}`, payload);
        if (data?.success) {
            setFlash("Estimation updated successfully.", "success", 2200);
            // Reload the list to reflect changes
            await loadList();
        }
    } catch (err) {
        console.error("Failed to update estimation", err);
        setFlash(
            "Failed to update estimation. Please try again.",
            "warning",
            3000,
        );
    }
}

async function deleteEstimation() {
    if (!form.id) {
        setFlash("No estimation to delete.", "warning", 2200);
        return;
    }
    const confirmed = confirm(
        "Are you sure you want to delete this estimation?",
    );
    if (!confirmed) return;
    try {
        await client.delete(`/estimations/${form.id}`);
        setFlash("Estimation deleted successfully.", "success", 2200);
        resetForm();
    } catch (err) {
        console.error("Failed to delete estimation", err);
        setFlash(
            "Failed to delete estimation. Please try again.",
            "warning",
            3000,
        );
    }
}

function resetForm() {
    form.id = null;
    form.status = "Pending";
    form.dateIssued = formatDateInput(new Date());
    form.expiryAt = formatDateTimeLocal(addDays(new Date(), 7));
    form.currency = "USD";
    form.jobTypes = ["Plumbing"];
    form.jobDescription = "";
    form.customerId = null;
    form.customerName = "";
    form.customerCompany = "";
    form.customerContact = "";
    form.customerAddress = "";
    form.jobSiteId = null;
    form.jobSite = "";
    form.jobSiteAddress = "";
    form.mapLink = "";
    form.startAt = formatDateTimeLocal(new Date());
    form.endAt = formatDateTimeLocal(addHours(new Date(), 4));
    form.scopeOfWork = "";
    form.exclusions = "";
    form.paymentMethods = ["Cash"];
    form.depositRequired = false;
    form.paymentTerms = "";
    form.termsConditions = "";
    form.approvalAt = "";
    form.approvedBy = "";
    form.approvalMethods = ["Email"];
    form.convertToJobOrder = false;
    form.customerType = "Residential";
    form.siteFloorLevel = "";
    form.siteSuiteUnit = "";
    form.siteCity = "";
    form.siteProvince = "";
    form.sitePostalCode = "";
    form.siteAccessDetails = "";
    form.taxRate = 0;
    form.taxRegistrationNo = "";
    form.depositPercentage = 0;
    form.depositAmount = 0;
    form.notesToCustomer = "";
    form.invoiceTo = "Same as Customer";
    form.invoiceTitle = "";
    form.invoicePrefix = "INV-";
    form.invoiceNextNumber = 1001;
    form.createInvoiceAfterApproval = false;
    taxAmount.value = 0;
    discountAmount.value = 0;
    estimationNumber.value = "";
    costRowId++;
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

function addDays(date, days) {
    const next = new Date(date);
    next.setDate(next.getDate() + days);
    return next;
}

function addHours(date, hours) {
    const next = new Date(date);
    next.setHours(next.getHours() + hours);
    return next;
}

function formatDate(date) {
    if (!date) return "--";
    return new Date(date).toLocaleDateString();
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

function toNumber(value) {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
}

function formatMoney(value) {
    return toNumber(value).toFixed(2);
}
</script>

<style scoped>
.pm-estimation-page {
    padding-bottom: 40px;
}

.pm-estimation-page .pm-account-holder-table thead th {
    background: linear-gradient(90deg, #0736df, #1457ef);
    color: #fff;
    border-color: #0736df;
}

.pm-estimation-page .pm-account-holder-table thead .pm-account-holder-sort,
.pm-estimation-page .pm-account-holder-table thead .pm-account-holder-sort-icon {
    color: #fff;
}

.pm-estimation-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.pm-estimation-head h2 {
    margin: 0 0 4px;
    font-size: 1.9rem;
    font-weight: 700;
    color: #0f2747;
}

.pm-estimation-head-actions,
.pm-estimation-footer-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.pm-estimation-status-chip {
    padding: 7px 10px;
    border-radius: 999px;
    background: #ffedd5;
    color: #ea580c;
    font-size: 0.82rem;
    font-weight: 700;
}

.pm-estimation-card {
    overflow: hidden;
    border: 1px solid rgba(33, 85, 188, 0.1);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.08);
    margin-bottom: 16px;
}

.pm-estimation-card-head {
    padding: 14px 18px;
    background: linear-gradient(90deg, #2563eb, #2340b8);
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
}

.pm-estimation-card-head--between {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.pm-estimation-card-body {
    padding: 18px;
}

.pm-estimation-card--success {
    border-color: rgba(22, 163, 74, 0.18);
    box-shadow: 0 10px 26px rgba(22, 163, 74, 0.09);
}

.pm-estimation-card-head--success {
    background: linear-gradient(90deg, #16a34a, #0f7c36);
}

.pm-estimation-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px 16px;
}

.pm-estimation-grid--compact {
    gap: 14px 16px;
}

.pm-field-block {
    min-width: 0;
}

.pm-field-block--full {
    grid-column: 1 / -1;
}

.pm-field-block--split {
    grid-column: span 1;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.pm-date-display-text {
    font-size: 0.85rem;
    color: #2563eb;
    margin-bottom: 4px;
    font-weight: 600;
}

.pm-choice-list {
    display: grid;
    gap: 8px;
    padding: 12px 14px;
    border: 1px solid #e4eaf5;
    border-radius: 12px;
    background: #f9fbff;
}

.pm-choice-list--split {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.pm-choice-column {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
}

.pm-choice-list--two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.pm-choice-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #29415f;
    font-size: 0.92rem;
}

.pm-inline-choice {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
    padding: 12px 14px;
    border: 1px solid #e4eaf5;
    border-radius: 12px;
    background: #f9fbff;
}

.pm-inline-choice--highlight {
    background: #effcf4;
    border-color: #cff3db;
}

.pm-estimation-highlight {
    margin-top: 14px;
    padding: 14px 16px;
    border-left: 4px solid #2563eb;
    border-radius: 10px;
    background: #eaf2ff;
    color: #1d4ed8;
}

.pm-estimation-highlight-label,
.pm-estimation-hint {
    display: block;
    color: #6b7b94;
    font-size: 0.82rem;
}

.pm-estimation-table th {
    white-space: nowrap;
    font-size: 0.82rem;
    color: #4d607d;
}

.pm-estimation-table td {
    min-width: 82px;
    vertical-align: middle;
}

.pm-estimation-summary {
    width: min(100%, 260px);
    margin-left: auto;
    margin-top: 16px;
    padding: 16px;
    border: 1px solid #cfe0ff;
    border-radius: 14px;
    background: linear-gradient(180deg, #eef5ff, #e4efff);
}

.pm-estimation-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
    color: #34506f;
    font-size: 0.92rem;
}

.pm-estimation-summary-row:last-child {
    margin-bottom: 0;
}

.pm-estimation-summary-row--total {
    padding-top: 10px;
    border-top: 1px solid #b8d0fb;
    color: #0f4ed8;
    font-weight: 700;
}

.pm-summary-input {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.pm-summary-input .form-control-sm {
    width: 100px;
    padding: 2px 6px;
    font-size: 0.85rem;
}

.pm-estimation-action-btn,
.pm-estimation-add-row,
.pm-estimation-delete-row,
.pm-estimation-convert-btn {
    border: 1px solid #d7dfef;
    border-radius: 10px;
    background: #fff;
    color: #213a5b;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 8px 12px;
    transition: all 0.2s ease;
}

.pm-estimation-action-btn:hover,
.pm-estimation-add-row:hover,
.pm-estimation-delete-row:hover,
.pm-estimation-convert-btn:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.pm-estimation-action-btn--primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-estimation-action-btn--success,
.pm-estimation-convert-btn {
    background: #0f9f45;
    border-color: #0f9f45;
    color: #fff;
}

.pm-estimation-action-btn--danger,
.pm-estimation-delete-row {
    color: #dc2626;
}

.pm-estimation-convert-action {
    display: flex;
    justify-content: center;
    margin-top: 16px;
}

.pm-estimation-footer-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 14px 0 4px;
}

.pm-estimation-delete-row {
    background: transparent;
}

.form-control[readonly] {
    background: #f5f7fb;
}

/* Compact estimate / quote workspace */
.pm-estimate-quote-form { padding: 8px 0 24px; color: #07165c; }
.pm-estimate-quote-header { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; margin-bottom: 18px; }
.pm-estimate-quote-header h1 { margin: 0; color: #06145c; font-size: clamp(1.45rem, 2.1vw, 2rem); font-weight: 800; }
.pm-estimate-steps { display: flex; min-width: 720px; margin-top: 18px; justify-content: space-between; counter-reset: step; }
.pm-estimate-steps span { position: relative; flex: 1; padding-top: 32px; text-align: center; color: #15225f; font-size: .74rem; font-weight: 700; }
.pm-estimate-steps span::before { content: ""; position: absolute; height: 1px; background: #8791b7; left: 0; right: 0; top: 14px; z-index: 0; }
.pm-estimate-steps b { position: absolute; z-index: 1; top: 0; left: calc(50% - 14px); display: grid; width: 28px; height: 28px; place-items: center; border: 1px solid #7380a7; border-radius: 50%; background: #fff; font-size: .9rem; }
.pm-estimate-steps .is-active b { border-color: #073ee6; background: #0c3ded; color: #fff; }
.pm-estimate-header-actions { display: flex; align-items: center; gap: 12px; }.pm-quote-primary, .pm-quote-actions button { border: 0; border-radius: 4px; background: #0736df; color: #fff; font-weight: 700; box-shadow: 0 3px 7px #10296d33; }.pm-quote-list-button { border: 1px solid #aebbd4; border-radius: 4px; background: #fff; color: #0736df; font-weight: 700; padding: 11px 16px; }
.pm-quote-primary { white-space: nowrap; padding: 12px 20px; margin-top: 4px; }.pm-quote-primary span { font-size: 1.35rem; margin-left: 8px; }
.pm-quote-top-fields { display: grid; grid-template-columns: 1.05fr 1.5fr 1.35fr .9fr 1fr; gap: 14px; margin-bottom: 14px; align-items: end; }
.pm-estimate-quote-form label, .pm-estimate-quote-form legend { display: grid; gap: 6px; color: #09185b; font-size: .73rem; font-weight: 700; }.pm-estimate-quote-form em { color: #e11d48; font-style: normal; }
.pm-estimate-quote-form .form-control { min-height: 34px; border-color: #cbd2e0; color: #09185b; font-size: .82rem; }.pm-estimate-quote-form textarea.form-control { min-height: auto; }
.pm-quote-approval { min-height: 74px; margin: 0; padding: 9px 16px; border: 1px solid #79c8a6; border-radius: 4px; }.pm-quote-approval legend { float: none; width: auto; padding: 0; margin: 0 0 3px; }.pm-quote-approval label, .pm-check { display: inline-flex !important; align-items: center; margin-right: 24px; gap: 8px; }
.pm-quote-two-columns { display: grid; grid-template-columns: .95fr 1.3fr; gap: 14px; }.pm-quote-panel, .pm-quote-summary-strip { border: 1px solid #e2e7f1; border-radius: 5px; background: #fff; padding: 12px 14px; }
.pm-quote-panel h2, .pm-quote-summary-strip h2 { margin: 0 0 12px; color: #083ce5; font-size: .94rem; font-weight: 800; }.pm-quote-panel h2 b, .pm-quote-summary-strip h2 b { display: inline-grid; width: 23px; height: 23px; place-items: center; border-radius: 50%; color: #fff; background: #0840e8; margin-right: 7px; }.pm-quote-panel h3 { margin: 0 0 10px; font-size: .72rem; font-weight: 800; }.pm-quote-panel h3:not(:first-of-type) { margin-top: 15px; }
.pm-quote-grid { display: grid; gap: 12px 22px; }.customer-grid { grid-template-columns: repeat(3, minmax(0,1fr)); }.site-grid { grid-template-columns: 1.2fr .8fr .8fr; }.span-2 { grid-column: span 2; }.span-3 { grid-column: 1 / -1; }
.pm-quote-summary-strip { margin: 12px 0; padding: 9px 14px; }.pm-quote-summary-strip h2 { margin-bottom: 8px; }.pm-quote-summary-strip h2 small { font-size: .8rem; }.pm-quote-summary-cards { display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: 10px; }.pm-quote-summary-cards > div { min-height: 64px; display: flex; flex-direction: column; justify-content: space-between; padding: 10px 14px; border: 1px solid #dce1eb; border-radius: 4px; }.pm-quote-summary-cards span { font-size: .72rem; font-weight: 700; }.pm-quote-summary-cards strong { align-self: flex-end; font-size: .86rem; }.pm-quote-summary-cards label { grid-template-columns: 1fr auto; align-items: center; }.pm-quote-summary-cards input { width: 78px; }.pm-quote-summary-cards small { text-align: right; }.pm-quote-summary-cards .total { color: #053be8; }.pm-quote-summary-cards .total strong { font-size: 1.35rem; }
.pm-quote-settings-grid { display: grid; grid-template-columns: .8fr 1fr .95fr 1fr 1fr; gap: 12px; align-items: stretch; }.pm-quote-settings-grid .pm-quote-panel { display: flex; flex-direction: column; gap: 10px; }.pm-payment-options { display: grid; gap: 9px; }.pm-payment-options label { display: flex; align-items: center; gap: 8px; }.pm-quote-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }.pm-notes { flex: 1; }.pm-check { margin-top: 4px; line-height: 1.35; }
.pm-quote-actions { display: flex; gap: 18px; margin-top: 16px; padding: 0; justify-content: center; flex-wrap: wrap; background: transparent; color: inherit; }.pm-quote-actions button { min-width: 125px; padding: 10px 15px; font-size: .8rem; }.pm-quote-actions button:disabled { opacity: .55; cursor: not-allowed; }
@media (max-width: 1200px) { .pm-quote-top-fields { grid-template-columns: repeat(3, 1fr); }.pm-quote-settings-grid { grid-template-columns: repeat(3, 1fr); }.pm-estimate-steps { min-width: 0; }.pm-estimate-steps span { font-size: .65rem; } }
@media (max-width: 800px) { .pm-estimate-quote-header, .pm-quote-two-columns { display: block; }.pm-estimate-header-actions { margin: 14px 0; }.pm-quote-primary { margin: 0; }.pm-estimate-steps { overflow-x: auto; }.pm-estimate-steps span { min-width: 110px; }.pm-quote-top-fields, .pm-quote-settings-grid, .pm-quote-summary-cards { grid-template-columns: 1fr 1fr; }.customer-grid, .site-grid { grid-template-columns: 1fr 1fr; }.span-2, .span-3 { grid-column: 1 / -1; }.pm-quote-two-columns .pm-quote-panel { margin-bottom: 12px; } }
@media (max-width: 520px) { .pm-quote-top-fields, .pm-quote-settings-grid, .pm-quote-summary-cards, .customer-grid, .site-grid { grid-template-columns: 1fr; }.span-2, .span-3 { grid-column: auto; }.pm-quote-actions button { flex: 1 1 42%; min-width: 0; } }

@media (max-width: 991.98px) {
    .pm-estimation-head,
    .pm-estimation-footer-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .pm-estimation-grid,
    .pm-field-block--split {
        grid-template-columns: 1fr;
    }

    .pm-choice-list--two {
        grid-template-columns: 1fr;
    }

    .pm-estimation-total-green {
        color: #16a34a;
        font-weight: 600;
    }

    .pm-account-holder-status.is-pending {
        background: #ffedd5;
        color: #ea580c;
    }

    .pm-account-holder-status.is-approved {
        background: #dcfce7;
        color: #16a34a;
    }

    .pm-account-holder-status.is-cancelled {
        background: #fee2e2;
        color: #dc2626;
    }

    .pm-account-holder-status.is-expired {
        background: #f1f5f9;
        color: #475569;
    }
}
</style>
