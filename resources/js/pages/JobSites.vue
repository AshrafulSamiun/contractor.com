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
                                d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66Z"
                            />
                        </svg>
                    </button>
                </div>
                <div
                    class="pm-topbar-user"
                    @click="toggleUserMenu"
                    ref="userMenuRef"
                >
                    <div class="pm-topbar-avatar">JA</div>
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
                            @click="goProfile"
                        >
                            Profile
                        </button>
                        <button
                            class="pm-user-item"
                            type="button"
                            @click="goAccount"
                        >
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

            <div class="container pm-ops-page pm-account-holder-page">
                <section class="pm-account-holder-hero">
                    <div>
                        <div class="pm-account-holder-kicker">
                            Profiles Module
                        </div>
                        <h2 class="pm-account-holder-title">Job Sites</h2>
                        <div
                            class="pm-page-subtitle pm-account-holder-subtitle"
                        >
                            Dashboard &gt; Profiles &gt; Job Sites &gt;
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
                            class="pm-hero-action-button pm-hero-action-button--primary"
                            type="button"
                            @click="startEdit(detailItem)"
                            v-if="viewMode === 'detail'"
                        >
                            <span class="pm-hero-action-icon" aria-hidden="true"
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
                            <h5 class="pm-form-title">Job Site List</h5>
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
                                    placeholder="Search job sites..."
                                />
                            </div>
                            <div class="pm-filter-row">
                                <select
                                    v-model="filterStatus"
                                    class="form-control pm-filter-select"
                                >
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive pm-account-holder-table-wrap">
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
                                            {{ item.job_site_no || "--" }}
                                        </span>
                                    </td>
                                    <td>
                                        <div
                                            class="pm-account-holder-table-primary"
                                        >
                                            {{ item.job_site_name || "--" }}
                                        </div>
                                        <div
                                            class="pm-account-holder-table-secondary"
                                        >
                                            {{ item.customer_name || "--" }}
                                        </div>
                                    </td>
                                    <td>{{ item.contact_person || "--" }}</td>
                                    <td>{{ item.phone || "--" }}</td>
                                    <td>
                                        <span
                                            class="pm-account-holder-status"
                                            :class="
                                                item.status === 1
                                                    ? 'is-active'
                                                    : 'is-inactive'
                                            "
                                        >
                                            {{
                                                item.status === 1
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate(item.created_at) }}</td>
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
                                        No job sites match your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pm-account-holder-list-footer">
                        <div class="pm-account-holder-pagination-summary">
                            Showing {{ paginationStart }}-{{ paginationEnd }} of
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
                    class="pm-account-holder-detail-shell"
                >
                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-detail-code-card"
                    >
                        <div>
                            <div class="pm-account-holder-detail-code-label">
                                Job Site Number
                            </div>
                            <div class="pm-account-holder-detail-code-value">
                                {{ detailItem.job_site_no || "--" }}
                            </div>
                        </div>
                        <div class="pm-account-holder-detail-badges">
                            <span
                                class="pm-account-holder-status"
                                :class="
                                    detailItem.status === 1
                                        ? 'is-active'
                                        : 'is-inactive'
                                "
                            >
                                {{
                                    detailItem.status === 1
                                        ? "Active"
                                        : "Inactive"
                                }}
                            </span>
                        </div>
                    </section>

                    <section class="pm-account-holder-detail-section">
                        <div class="pm-account-holder-detail-section-head">
                            Job Site Information
                        </div>
                        <div
                            class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                        >
                            <div class="pm-account-holder-detail-grid">
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Job Site Name:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.job_site_name || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Contact No:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.contact_no || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Start Date:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            formatDate(detailItem.start_date)
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >End Date:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            formatDate(detailItem.end_date)
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Description:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.description || "--"
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-account-holder-detail-section">
                        <div class="pm-account-holder-detail-section-head">
                            Customer Information
                        </div>
                        <div
                            class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                        >
                            <div class="pm-account-holder-detail-grid">
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Customer No:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.customer_no || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Customer Name:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.customer_name || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Address:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.address || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Contact Person:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.contact_person || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Phone:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailItem.phone || "--" }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Email:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailItem.email || "--" }}</span
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
                                    <span class="pm-account-holder-detail-key"
                                        >Created Date:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            formatDate(detailItem.created_at)
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Last Modified:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            formatDate(detailItem.updated_at)
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Record ID:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailItem.id || "--" }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </section>
                </section>

                <div v-else class="pm-account-holder-form-shell">
                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-section"
                    >
                        <div class="pm-account-holder-section-head">
                            <h5 class="pm-form-title">Job Site Information</h5>
                            <span class="pm-account-holder-section-tag"
                                >Core</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="pm-field-label">Job Site No</label>
                                <input
                                    class="form-control"
                                    v-model="form.job_site_no"
                                    placeholder="Auto-generated"
                                    disabled
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Job Site Name
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.job_site_name"
                                    placeholder="Enter job site name"
                                />
                                <div
                                    v-if="errors.job_site_name"
                                    class="pm-form-error"
                                >
                                    {{ errors.job_site_name }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Contact No</label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.contact_no"
                                    placeholder="Contact number"
                                />
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="pm-field-label">Start Date</label>
                                <input
                                    class="form-control"
                                    v-model="form.start_date"
                                    type="date"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label">End Date</label>
                                <input
                                    class="form-control"
                                    v-model="form.end_date"
                                    type="date"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label">Status</label>
                                <div class="pm-checkbox-row">
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="radio"
                                            name="status"
                                            value="1"
                                            v-model="form.status"
                                        />
                                        <span class="pm-checkbox-label"
                                            >Active</span
                                        >
                                    </label>
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="radio"
                                            name="status"
                                            value="2"
                                            v-model="form.status"
                                        />
                                        <span class="pm-checkbox-label"
                                            >Inactive</span
                                        >
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="pm-field-label"
                                    >Description</label
                                >
                                <textarea
                                    class="form-control"
                                    v-model="form.description"
                                    rows="2"
                                    placeholder="Enter description..."
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-section"
                    >
                        <div class="pm-account-holder-section-head">
                            <h5 class="pm-form-title">Customer Information</h5>
                            <span class="pm-account-holder-section-tag"
                                >Required</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Customer No
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.customer_no"
                                    placeholder="Customer number"
                                />
                                <div
                                    v-if="errors.customer_no"
                                    class="pm-form-error"
                                >
                                    {{ errors.customer_no }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Customer Name
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.customer_name"
                                    placeholder="Customer name"
                                />
                                <div
                                    v-if="errors.customer_name"
                                    class="pm-form-error"
                                >
                                    {{ errors.customer_name }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Contact Person
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.contact_person"
                                    placeholder="Contact person name"
                                />
                                <div
                                    v-if="errors.contact_person"
                                    class="pm-form-error"
                                >
                                    {{ errors.contact_person }}
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Phone
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.phone"
                                    placeholder="Phone number"
                                />
                                <div
                                    v-if="errors.phone"
                                    class="pm-form-error"
                                >
                                    {{ errors.phone }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label">Email</label>
                                <input
                                    class="form-control"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Email address"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label">Address</label>
                                <input
                                    class="form-control"
                                    v-model="form.address"
                                    placeholder="Address"
                                />
                            </div>
                        </div>
                    </section>

                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-section"
                    >
                        <div class="pm-account-holder-section-head">
                            <h5 class="pm-form-title">
                                Additional Information
                            </h5>
                            <span class="pm-account-holder-section-tag"
                                >Optional</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="pm-field-label">Note</label>
                                <textarea
                                    class="form-control"
                                    v-model="form.note"
                                    rows="3"
                                    placeholder="Enter any additional notes..."
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <section
                        class="pm-card pm-ops-card p-3 pm-account-holder-actions-sticky"
                    >
                        <div class="pm-form-actions">
                            <button
                                class="btn btn-outline-primary"
                                type="button"
                                :disabled="!!activeId"
                                @click="resetForm"
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
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                New
                            </button>
                            <button
                                class="btn btn-outline-danger"
                                type="button"
                                :disabled="!activeId"
                                @click="deleteCurrent"
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
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path
                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                    ></path>
                                </svg>
                                Delete
                            </button>
                            <button
                                class="btn btn-primary"
                                type="button"
                                :disabled="saving"
                                @click="saveItem"
                            >
                                <svg
                                    v-if="!activeId"
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
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <svg
                                    v-else
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
                                {{
                                    saving
                                        ? "Saving..."
                                        : activeId
                                          ? "Update"
                                          : "Save"
                                }}
                            </button>
                        </div>
                    </section>
                </div>
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
import { jobSiteService } from "../api/jobSite";
import { authState } from "../store/auth";

const router = useRouter();
const route = useRoute();
const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const searchQuery = ref("");
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const userName = ref("User");

const viewMode = ref("form");
const saving = ref(false);
const loadingList = ref(false);
const activeId = ref(null);
const detailItem = ref(null);
const errors = reactive({});

const items = ref([]);
const listSearchQuery = ref("");
const listPage = ref(1);
const listPageSize = 8;
const listSortKey = ref("created_at");
const listSortDirection = ref("desc");
const filterStatus = ref("");

const listColumns = [
    { key: "job_site_no", label: "Job Site No" },
    { key: "job_site_name", label: "Job Site / Customer" },
    { key: "contact_person", label: "Contact Person" },
    { key: "phone", label: "Phone" },
    { key: "status", label: "Status" },
    { key: "created_at", label: "Created" },
];

const form = reactive({
    job_site_no: "",
    job_site_name: "",
    contact_no: "",
    start_date: "",
    end_date: "",
    description: "",
    customer_no: "",
    customer_name: "",
    address: "",
    contact_person: "",
    phone: "",
    email: "",
    status: 1,
    note: "",
    id: "",
});

const flattenSearchValue = (value) => {
    if (Array.isArray(value)) {
        return value.map(flattenSearchValue).join(" ");
    }

    if (value && typeof value === "object") {
        return Object.values(value).map(flattenSearchValue).join(" ");
    }

    return value == null ? "" : String(value);
};

const formatDate = (value) => {
    if (!value) return "--";

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getSortableValue = (item, key) => {
    if (key === "status") {
        return item.status === 1 ? "Active" : "Inactive";
    }

    if (key === "created_at") {
        return new Date(item.created_at).getTime() || 0;
    }

    return item[key] ?? "";
};

const filteredItems = computed(() => {
    const query = listSearchQuery.value.trim().toLowerCase();
    let result = items.value;

    if (query) {
        result = result.filter((item) =>
            flattenSearchValue({
                ...item,
            })
                .toLowerCase()
                .includes(query),
        );
    }

    if (filterStatus.value !== "") {
        result = result.filter(
            (item) => item.status === Number(filterStatus.value),
        );
    }

    return result;
});

const sortedItems = computed(() => {
    const itemsArray = [...filteredItems.value];

    itemsArray.sort((left, right) => {
        const leftValue = getSortableValue(left, listSortKey.value);
        const rightValue = getSortableValue(right, listSortKey.value);

        if (listSortKey.value === "created_at") {
            const leftTime = new Date(leftValue).getTime() || 0;
            const rightTime = new Date(rightValue).getTime() || 0;
            return listSortDirection.value === "asc"
                ? leftTime - rightTime
                : rightTime - leftTime;
        }

        const comparison = String(leftValue).localeCompare(
            String(rightValue),
            undefined,
            {
                numeric: true,
                sensitivity: "base",
            },
        );

        return listSortDirection.value === "asc" ? comparison : -comparison;
    });

    return itemsArray;
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(sortedItems.value.length / listPageSize)),
);

const paginatedItems = computed(() => {
    const start = (listPage.value - 1) * listPageSize;
    return sortedItems.value.slice(start, start + listPageSize);
});

const paginationStart = computed(() => {
    if (!filteredItems.value.length) return 0;
    return (listPage.value - 1) * listPageSize + 1;
});

const paginationEnd = computed(() =>
    Math.min(listPage.value * listPageSize, filteredItems.value.length),
);

const visiblePages = computed(() => {
    const pages = [];
    const start = Math.max(1, listPage.value - 2);
    const end = Math.min(totalPages.value, start + 4);

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
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
    userMenuOpen.value = false;
    router.push("/account/profile");
};

const goAccount = () => {
    userMenuOpen.value = false;
    router.push("/account");
};

const logout = () => {
    userMenuOpen.value = false;
    router.push("/login");
};

const handleOutsideClick = (event) => {
    if (!userMenuRef.value) return;
    if (!userMenuRef.value.contains(event.target)) {
        userMenuOpen.value = false;
    }
};

const handleEsc = (event) => {
    if (event.key === "Escape") {
        userMenuOpen.value = false;
    }
};

watch(sidebarOpen, (value) => {
    document.body.classList.toggle("pm-no-scroll", value);
});

const mapErrors = (errs) => {
    Object.keys(errors).forEach((k) => delete errors[k]);
    if (!errs) return;
    Object.entries(errs).forEach(([key, value]) => {
        errors[key] = Array.isArray(value) ? value[0] : value;
    });
};

const loadItems = async () => {
    loadingList.value = true;
    try {
        const { data } = await jobSiteService.getItems();
        if (data.data) {
            items.value = Array.isArray(data.data.data)
                ? data.data.data
                : data.data;
        }
    } catch {
        // handled by toast
    } finally {
        loadingList.value = false;
    }
};

const resetForm = () => {
    activeId.value = null;
    detailItem.value = null;
    mapErrors(null);
    form.job_site_no = "";
    form.job_site_name = "";
    form.contact_no = "";
    form.start_date = "";
    form.end_date = "";
    form.description = "";
    form.customer_no = "";
    form.customer_name = "";
    form.address = "";
    form.contact_person = "";
    form.phone = "";
    form.email = "";
    form.status = 1;
    form.note = "";
    form.id = "";
};

const openFormView = () => {
    viewMode.value = "form";
    resetForm();
    router.replace({ query: { ...route.query, id: undefined } });
};

const openListView = async () => {
    viewMode.value = "list";
    detailItem.value = null;
    await loadItems();
};

const changePage = (page) => {
    if (page < 1 || page > totalPages.value) return;
    listPage.value = page;
};

const toggleSort = (key) => {
    if (listSortKey.value === key) {
        listSortDirection.value =
            listSortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    listSortKey.value = key;
    listSortDirection.value = key === "created_at" ? "desc" : "asc";
};

const startEdit = async (item) => {
    viewMode.value = "form";
    await loadItem(item.id);
    router.replace({ query: { ...route.query, id: item.id } });
};

const startView = async (item) => {
    await loadItemDetail(item.id);
};

const loadItemDetail = async (id) => {
    try {
        const { data } = await jobSiteService.getItem(id);
        const item = data.data;
        if (!item) return;

        detailItem.value = item;
        viewMode.value = "detail";
    } catch {
        // handled by toast
    }
};

const loadItem = async (id) => {
    try {
        const { data } = await jobSiteService.getItemForEdit(id);
        const item = data.data;
        if (!item) return;
        activeId.value = item.id;
        form.job_site_no = item.job_site_no || "";
        form.job_site_name = item.job_site_name || "";
        form.contact_no = item.contact_no || "";
        form.start_date = item.start_date || "";
        form.end_date = item.end_date || "";
        form.description = item.description || "";
        form.customer_no = item.customer_no || "";
        form.customer_name = item.customer_name || "";
        form.address = item.address || "";
        form.contact_person = item.contact_person || "";
        form.phone = item.phone || "";
        form.email = item.email || "";
        form.status = item.status ?? 1;
        form.note = item.note || "";
        viewMode.value = "form";
    } catch {
        // handled by toast
    }
};

const saveItem = async () => {
    saving.value = true;
    mapErrors(null);
    try {
        const payload = {
            job_site_name: form.job_site_name,
            contact_no: form.contact_no || null,
            start_date: form.start_date || null,
            end_date: form.end_date || null,
            description: form.description || null,
            customer_no: form.customer_no,
            customer_name: form.customer_name,
            address: form.address || null,
            contact_person: form.contact_person,
            phone: form.phone,
            email: form.email || null,
            status: form.status ? Number(form.status) : 1,
            note: form.note ? form.note.trim() : null,
        };

        if (activeId.value) {
            await jobSiteService.updateItem(activeId.value, payload);
        } else {
            const { data } = await jobSiteService.createItem(payload);
            const message = data?.message || "";
            if (message.includes("**")) {
                const parts = message.split("**");
                activeId.value = parts[1];
                form.job_site_no = parts[2];
            } else if (data?.data) {
                activeId.value = data.data.id;
                form.job_site_no = data.data.job_site_no || "";
            }
        }
        await loadItems();
    } catch (err) {
        if (err?.response?.status === 422) {
            mapErrors(err.response.data.errors);
        }
    } finally {
        saving.value = false;
    }
};

const deleteCurrent = async () => {
    if (!activeId.value) return;
    if (!confirm("Delete this job site?")) return;
    try {
        await jobSiteService.deleteItem(activeId.value);
        resetForm();
        await loadItems();
        viewMode.value = "list";
        router.replace({ query: { ...route.query, id: undefined } });
    } catch {
        // handled by toast
    }
};

onMounted(async () => {
    if (authState.user?.name) userName.value = authState.user.name;
    document.addEventListener("click", handleOutsideClick);
    document.addEventListener("keydown", handleEsc);
    await loadItems();
    if (route.query?.id) loadItem(route.query.id);
});

onUnmounted(() => {
    document.removeEventListener("click", handleOutsideClick);
    document.removeEventListener("keydown", handleEsc);
});

watch(
    () => route.query?.id,
    async (id) => {
        if (id) {
            loadItem(id);
        } else {
            resetForm();
        }
    },
);

watch(listSearchQuery, () => {
    listPage.value = 1;
});

watch(filteredItems, () => {
    if (listPage.value > totalPages.value) {
        listPage.value = totalPages.value;
    }
});

watch(filterStatus, () => {
    listPage.value = 1;
});
</script>