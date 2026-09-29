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
                        placeholder="Search quotation..."
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
                <div class="container pm-ops-page pm-quotation-page">
                    <section class="pm-account-holder-hero">
                        <div>
                            <div class="pm-account-holder-kicker">
                                quotation Module
                            </div>
                            <h2 class="pm-account-holder-title">quotations</h2>
                            <div
                                class="pm-page-subtitle pm-account-holder-subtitle"
                            >
                                Dashboard &gt; quotation &gt;
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

                    <section
                        v-if="viewMode === 'list'"
                        class="pm-card pm-ops-card p-4 pm-account-holder-list-shell"
                    >
                        <div class="pm-account-holder-list-toolbar">
                            <div>
                                <h5 class="pm-form-title">quotation List</h5>
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
                                        placeholder="Search quotations..."
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
                                                {{ item.quotation_no || "--" }}
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
                                        <td class="pm-quotation-total-green">
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
                                            No quotations match your search.
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
                                    quotation Number
                                </div>
                                <div
                                    class="pm-account-holder-detail-code-value"
                                >
                                    {{ detailItem.quotation_no || "--" }}
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
                                quotation Information
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
                                            class="pm-account-holder-detail-value pm-quotation-total-green"
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
                                            class="pm-account-holder-detail-value pm-quotation-total-green"
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">
                            Basic Information
                        </div>
                        <div class="pm-quotation-card-body">
                            <div class="pm-quotation-grid">
                                <div class="pm-field-block">
                                    <label class="pm-field-label"
                                        >quotation No. (Auto Generate)</label
                                    >
                                    <input
                                        :value="quotationNumber"
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">Estimation</div>
                        <div class="pm-quotation-card-body">
                            <div class="pm-quotation-grid pm-quotation-grid--compact">
                                <div class="pm-field-block">
                                    <label class="pm-field-label">Estimation No</label>
                                    <select
                                        v-model="form.estimationId"
                                        class="form-control"
                                    >
                                        <option :value="null">
                                            Select estimation (optional)
                                        </option>
                                        <option
                                            v-for="est in estimations"
                                            :key="est.id"
                                            :value="est.id"
                                        >
                                            {{ est.estimation_no }} - {{ est.job_description }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">Customer</div>
                        <div class="pm-quotation-card-body">
                            <div
                                class="pm-quotation-grid pm-quotation-grid--compact"
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">Job Site</div>
                        <div class="pm-quotation-card-body">
                            <div
                                class="pm-quotation-grid pm-quotation-grid--compact"
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">Schedule</div>
                        <div class="pm-quotation-card-body">
                            <div
                                class="pm-quotation-grid pm-quotation-grid--compact"
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

                            <div class="pm-quotation-highlight">
                                <div class="pm-quotation-highlight-label">
                                    Net Duration (Auto Calculate)
                                </div>
                                <strong>{{ netDurationLabel }}</strong>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">Scope of Work</div>
                        <div class="pm-quotation-card-body">
                            <textarea
                                v-model="form.scopeOfWork"
                                class="form-control"
                                rows="5"
                                placeholder="Example:
- Remove existing plumbing lines
- Install new pipes
- Test water pressure"
                            ></textarea>
                            <small class="pm-quotation-hint"
                                >Use bullet points or numbered lists for
                                clarity.</small
                            >
                        </div>
                    </section>

                    <section
                        v-if="viewMode === 'form'"
                        class="pm-quotation-card"
                    >
                        <div
                            class="pm-quotation-card-head pm-quotation-card-head--between"
                        >
                            <span>Cost Breakdown</span>
                            <button
                                class="pm-quotation-add-row"
                                type="button"
                                @click="addCostRow"
                            >
                                Add Row
                            </button>
                        </div>
                        <div class="pm-quotation-card-body">
                            <div class="table-responsive">
                                <table
                                    class="table pm-quotation-table align-middle"
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
                                                    class="pm-quotation-delete-row"
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

                            <div class="pm-quotation-summary">
                                <div class="pm-quotation-summary-row">
                                    <span>Subtotal:</span>
                                    <strong
                                        >{{ currencyLabel }}
                                        {{ formatMoney(subtotal) }}</strong
                                    >
                                </div>
                                <div class="pm-quotation-summary-row">
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
                                <div class="pm-quotation-summary-row">
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
                                    class="pm-quotation-summary-row pm-quotation-summary-row--total"
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">
                            Exclusions / Notes
                        </div>
                        <div class="pm-quotation-card-body">
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">
                            Payment Terms & Method
                        </div>
                        <div class="pm-quotation-card-body">
                            <div class="pm-quotation-grid">
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">
                            Terms & Conditions
                        </div>
                        <div class="pm-quotation-card-body">
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
                        class="pm-quotation-card"
                    >
                        <div class="pm-quotation-card-head">
                            Customer Approval
                        </div>
                        <div class="pm-quotation-card-body">
                            <div class="pm-quotation-grid">
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
                        class="pm-quotation-card pm-quotation-card--success"
                    >
                        <div
                            class="pm-quotation-card-head pm-quotation-card-head--success"
                        >
                            Convert to Job Order
                        </div>
                        <div class="pm-quotation-card-body">
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
                            <div class="pm-quotation-convert-action">
                                <button
                                    class="pm-quotation-convert-btn"
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
                        class="pm-quotation-footer-actions"
                    >
                        <div class="pm-quotation-footer-group">
                            <button
                                class="pm-quotation-action-btn"
                                type="button"
                                :disabled="!form.id"
                                @click="notifyAction('New')"
                            >
                                New
                            </button>
                            <button
                                class="pm-quotation-action-btn"
                                type="button"
                                :disabled="!form.id"
                                @click="notifyAction('Update')"
                            >
                                Update
                            </button>
                            <button
                                class="pm-quotation-action-btn pm-quotation-action-btn--danger"
                                type="button"
                                :disabled="!form.id"
                                @click="notifyAction('Delete')"
                            >
                                Delete
                            </button>
                        </div>
                        <div class="pm-quotation-footer-group">
                            <button
                                v-if="!form.id"
                                class="pm-quotation-action-btn pm-quotation-action-btn--primary"
                                type="button"
                                @click="notifyAction('Save')"
                            >
                                Save
                            </button>
                            <button
                                class="pm-quotation-action-btn"
                                type="button"
                                @click="notifyAction('Save PDF')"
                            >
                                Save PDF
                            </button>
                            <button
                                class="pm-quotation-action-btn"
                                type="button"
                                @click="notifyAction('Print')"
                            >
                                Print
                            </button>
                            <button
                                class="pm-quotation-action-btn"
                                type="button"
                                @click="notifyAction('Email')"
                            >
                                Email
                            </button>
                            <button
                                class="pm-quotation-action-btn pm-quotation-action-btn--success"
                                type="button"
                                @click="convertToJobOrder"
                            >
                                Convert to Job Order
                            </button>
                        </div>
                    </div>
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
const viewMode = ref("form");
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
    { key: "quotation_no", label: "Estimate No" },
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
                (item.quotation_no || "").toLowerCase().includes(q) ||
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
    loadquotation(item.id);
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
        const { data } = await client.get("/quotations");
        listItems.value = data?.data?.data || [];
    } catch (err) {
        console.error("Failed to load quotations", err);
    }
}

const form = reactive({
    id: null,
    estimationId: null,
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

const quotationNumber = ref("");
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

const estimations = ref([]);

const fetchData = async () => {
    try {
        const [custRes, siteRes, estRes] = await Promise.all([
            client.get("/quotations/customers"),
            client.get("/quotations/job-sites"),
            client.get("/quotations/estimations"),
        ]);

        customers.value = custRes.data?.data || [];
        jobSites.value = siteRes.data?.data || [];
        estimations.value = estRes.data?.data || [];
    } catch (err) {
        console.error("Failed to load quotation metadata", err);
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
        await loadquotation(estId);
    }
});

async function loadquotation(id) {
    try {
        const { data } = await client.get(`/quotations/${id}`);
        const est = data?.data;
        if (!est) {
            setFlash("quotation not found.", "warning", 2200);
            return;
        }
        activeId.value = est.id;
        form.id = est.id;
        form.estimationId = est.estimation_id || null;
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
        form.termsConditions =
            est.terms_conditions || est.terms_condition || "";
        form.approvalAt = est.customer_approval_date ? est.customer_approval_date.slice(0, 16) : "";
        form.approvedBy = est.customer_approve_by || "";
        form.approvalMethods = est.approval_method
            ? [reverseApprovalMethodMap[est.approval_method]]
            : [];
        form.convertToJobOrder = est.convert_to_job_order || false;
        taxAmount.value = est.tax ?? 0;
        discountAmount.value = est.discount ?? 0;
        quotationNumber.value = est.quotation_no || "";
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
        // Populate customer fields directly from loaded quotation data
        if (est.customer) {
            form.customerName = est.customer.name || est.customer.account_name || "";
            form.customerCompany = est.customer.company_name || "";
            form.customerContact = [est.customer.cell_phone, est.customer.email].filter(Boolean).join(", ");
            form.customerAddress = [est.customer.house_number, est.customer.street_number, est.customer.city, est.customer.state].filter(Boolean).join(" ");
        }
        // Populate job site fields directly from loaded quotation data
        if (est.job_site) {
            form.jobSite = est.job_site.name || est.job_site.job_site_name || "";
            form.jobSiteAddress = est.job_site.address || "";
            form.mapLink = est.job_site.map_link || "";
        }
    } catch (err) {
        console.error("Failed to load quotation", err);
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
        savequotation();
    } else if (action === "Update") {
        updatequotation();
    } else if (action === "Delete") {
        deletequotation();
    } else if (action === "New") {
        activeId.value = null;
        detailItem.value = null;
        resetForm();
    } else {
        setFlash(
            `${action} action is ready for quotation entry.`,
            "info",
            1800,
        );
    }
}

function convertToJobOrder() {
    form.convertToJobOrder = true;
    setFlash("quotation marked for job order conversion.", "success", 2200);
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
        estimation_id: form.estimationId,
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
        terms_conditions: form.termsConditions,
        customer_approval_date: form.approvalAt,
        customer_approve_by: form.approvedBy,
        approval_method: form.approvalMethods.length
            ? approvalMethodMap[form.approvalMethods[0]] || 3
            : null,
        convert_to_job_order: form.convertToJobOrder,
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

async function savequotation() {
    try {
        const payload = buildPayload();
        const { data } = await client.post("/quotations", payload);
        if (data?.data?.id) {
            form.id = data.data.id;
            quotationNumber.value = data.data.quotation_no || "";
            setFlash("quotation saved successfully.", "success", 2200);
            if (form.convertToJobOrder) {
                router.push({
                    path: "/job-orders/orders",
                    query: { quotation_id: data.data.id },
                });
            }
        }
    } catch (err) {
        console.error("Failed to save quotation", err);
        setFlash(
            "Failed to save quotation. Please try again.",
            "warning",
            3000,
        );
    }
}

async function updatequotation() {
    if (!form.id) {
        setFlash("No quotation to update.", "warning", 2200);
        return;
    }
    try {
        const payload = buildPayload();
        const { data } = await client.put(`/quotations/${form.id}`, payload);
        if (data?.success) {
            setFlash("quotation updated successfully.", "success", 2200);
            // Reload the list to reflect changes
            await loadList();
        }
    } catch (err) {
        console.error("Failed to update quotation", err);
        setFlash(
            "Failed to update quotation. Please try again.",
            "warning",
            3000,
        );
    }
}

async function deletequotation() {
    if (!form.id) {
        setFlash("No quotation to delete.", "warning", 2200);
        return;
    }
    const confirmed = confirm(
        "Are you sure you want to delete this quotation?",
    );
    if (!confirmed) return;
    try {
        await client.delete(`/quotations/${form.id}`);
        setFlash("quotation deleted successfully.", "success", 2200);
        activeId.value = null;
        detailItem.value = null;
        resetForm();
    } catch (err) {
        console.error("Failed to delete quotation", err);
        setFlash(
            "Failed to delete quotation. Please try again.",
            "warning",
            3000,
        );
    }
}

function resetForm() {
    activeId.value = null;
    detailItem.value = null;
    form.id = null;
    form.estimationId = null;
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
    taxAmount.value = 0;
    discountAmount.value = 0;
    quotationNumber.value = "";
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
.pm-quotation-page {
    padding-bottom: 40px;
}

.pm-quotation-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.pm-quotation-head h2 {
    margin: 0 0 4px;
    font-size: 1.9rem;
    font-weight: 700;
    color: #0f2747;
}

.pm-quotation-head-actions,
.pm-quotation-footer-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.pm-quotation-status-chip {
    padding: 7px 10px;
    border-radius: 999px;
    background: #ffedd5;
    color: #ea580c;
    font-size: 0.82rem;
    font-weight: 700;
}

.pm-quotation-card {
    overflow: hidden;
    border: 1px solid rgba(33, 85, 188, 0.1);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.08);
    margin-bottom: 16px;
}

.pm-quotation-card-head {
    padding: 14px 18px;
    background: linear-gradient(90deg, #2563eb, #2340b8);
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
}

.pm-quotation-card-head--between {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.pm-quotation-card-body {
    padding: 18px;
}

.pm-quotation-card--success {
    border-color: rgba(22, 163, 74, 0.18);
    box-shadow: 0 10px 26px rgba(22, 163, 74, 0.09);
}

.pm-quotation-card-head--success {
    background: linear-gradient(90deg, #16a34a, #0f7c36);
}

.pm-quotation-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px 16px;
}

.pm-quotation-grid--compact {
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

.pm-quotation-highlight {
    margin-top: 14px;
    padding: 14px 16px;
    border-left: 4px solid #2563eb;
    border-radius: 10px;
    background: #eaf2ff;
    color: #1d4ed8;
}

.pm-quotation-highlight-label,
.pm-quotation-hint {
    display: block;
    color: #6b7b94;
    font-size: 0.82rem;
}

.pm-quotation-table th {
    white-space: nowrap;
    font-size: 0.82rem;
    color: #4d607d;
}

.pm-quotation-table td {
    min-width: 82px;
    vertical-align: middle;
}

.pm-quotation-summary {
    width: min(100%, 260px);
    margin-left: auto;
    margin-top: 16px;
    padding: 16px;
    border: 1px solid #cfe0ff;
    border-radius: 14px;
    background: linear-gradient(180deg, #eef5ff, #e4efff);
}

.pm-quotation-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
    color: #34506f;
    font-size: 0.92rem;
}

.pm-quotation-summary-row:last-child {
    margin-bottom: 0;
}

.pm-quotation-summary-row--total {
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

.pm-quotation-action-btn,
.pm-quotation-add-row,
.pm-quotation-delete-row,
.pm-quotation-convert-btn {
    border: 1px solid #d7dfef;
    border-radius: 10px;
    background: #fff;
    color: #213a5b;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 8px 12px;
    transition: all 0.2s ease;
}

.pm-quotation-action-btn:hover,
.pm-quotation-add-row:hover,
.pm-quotation-delete-row:hover,
.pm-quotation-convert-btn:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.pm-quotation-action-btn--primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-quotation-action-btn--success,
.pm-quotation-convert-btn {
    background: #0f9f45;
    border-color: #0f9f45;
    color: #fff;
}

.pm-quotation-action-btn--danger,
.pm-quotation-delete-row {
    color: #dc2626;
}

.pm-quotation-convert-action {
    display: flex;
    justify-content: center;
    margin-top: 16px;
}

.pm-quotation-footer-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 14px 0 4px;
}

.pm-quotation-delete-row {
    background: transparent;
}

.form-control[readonly] {
    background: #f5f7fb;
}

@media (max-width: 991.98px) {
    .pm-quotation-head,
    .pm-quotation-footer-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .pm-quotation-grid,
    .pm-field-block--split {
        grid-template-columns: 1fr;
    }

    .pm-choice-list--two {
        grid-template-columns: 1fr;
    }

    .pm-quotation-total-green {
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
