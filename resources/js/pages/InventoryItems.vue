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
                        <h2 class="pm-account-holder-title">Inventory Items</h2>
                        <div
                            class="pm-page-subtitle pm-account-holder-subtitle"
                        >
                            Dashboard &gt; Profiles &gt; Inventory Items &gt;
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
                            <h5 class="pm-form-title">Inventory Item List</h5>
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
                                    placeholder="Search items..."
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
                                            {{ item.item_no || "--" }}
                                        </span>
                                    </td>
                                    <td>
                                        <div
                                            class="pm-account-holder-table-primary"
                                        >
                                            {{ item.item_name || "--" }}
                                        </div>
                                        <div
                                            class="pm-account-holder-table-secondary"
                                        >
                                            {{ item.unit_of_measure || "--" }}
                                        </div>
                                    </td>
                                    <td>{{ formatPrice(item.price) }}</td>
                                    <td>
                                        <span
                                            class="pm-account-holder-type-badge"
                                            :class="
                                                item.sales_tax_applicable
                                                    ? 'is-customer'
                                                    : 'is-default'
                                            "
                                        >
                                            {{
                                                item.sales_tax_applicable
                                                    ? "Yes"
                                                    : "No"
                                            }}
                                        </span>
                                    </td>
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
                                        No inventory items match your search.
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
                                Item Number
                            </div>
                            <div class="pm-account-holder-detail-code-value">
                                {{ detailItem.item_no || "--" }}
                            </div>
                        </div>
                        <div class="pm-account-holder-detail-badges">
                            <span
                                class="pm-account-holder-type-badge"
                                :class="
                                    detailItem.sales_tax_applicable
                                        ? 'is-customer'
                                        : 'is-default'
                                "
                            >
                                {{
                                    detailItem.sales_tax_applicable
                                        ? "Tax Applicable"
                                        : "No Tax"
                                }}
                            </span>
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
                            Item Details
                        </div>
                        <div
                            class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                        >
                            <div class="pm-account-holder-detail-grid">
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Item Name:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.item_name || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Unit of Measure:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.unit_of_measure || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Price:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            formatPrice(detailItem.price)
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Sales Tax Applicable:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailItem.sales_tax_applicable
                                                ? "Yes"
                                                : "No"
                                        }}</span
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
                            <h5 class="pm-form-title">Item Information</h5>
                            <span class="pm-account-holder-section-tag"
                                >Core</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="pm-field-label">Item No</label>
                                <input
                                    class="form-control"
                                    v-model="form.item_no"
                                    placeholder="Auto-generated"
                                    disabled
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Item Name
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.item_name"
                                    placeholder="Enter item name"
                                />
                                <div
                                    v-if="errors.item_name"
                                    class="pm-form-error"
                                >
                                    {{ errors.item_name }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Unit of Measure</label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.unit_of_measure"
                                    placeholder="e.g., Each, Box, kg"
                                />
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="pm-field-label">Price</label>
                                <input
                                    class="form-control"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Sales Tax Applicable</label
                                >
                                <div class="pm-checkbox-row">
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="checkbox"
                                            value="1"
                                            v-model="form.sales_tax_applicable"
                                        />
                                        <span class="pm-checkbox-label"
                                            >Yes</span
                                        >
                                    </label>
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="checkbox"
                                            value="0"
                                            v-model="form.sales_tax_applicable"
                                        />
                                        <span class="pm-checkbox-label"
                                            >No</span
                                        >
                                    </label>
                                </div>
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
import { itemService } from "../api/item";
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
    { key: "item_no", label: "Item No" },
    { key: "item_name", label: "Item Name" },
    { key: "price", label: "Price" },
    { key: "sales_tax_applicable", label: "Tax" },
    { key: "status", label: "Status" },
    { key: "created_at", label: "Created" },
];

const form = reactive({
    item_no: "",
    item_name: "",
    unit_of_measure: "",
    price: "",
    sales_tax_applicable: [],
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

const formatPrice = (value) => {
    if (value == null || value === "") return "--";
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
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

    if (key === "sales_tax_applicable") {
        return item.sales_tax_applicable ? "Yes" : "No";
    }

    if (key === "price") {
        return parseFloat(item.price) || 0;
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

        if (listSortKey.value === "price") {
            return listSortDirection.value === "asc"
                ? leftValue - rightValue
                : rightValue - leftValue;
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
        const { data } = await itemService.getItems();
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
    form.item_no = "";
    form.item_name = "";
    form.unit_of_measure = "";
    form.price = "";
    form.sales_tax_applicable = [];
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
        const { data } = await itemService.getItem(id);
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
        const { data } = await itemService.getItemForEdit(id);
        const item = data.data;
        if (!item) return;
        activeId.value = item.id;
        form.item_no = item.item_no || "";
        form.item_name = item.item_name || "";
        form.unit_of_measure = item.unit_of_measure || "";
        form.price = item.price || "";
        form.sales_tax_applicable =
            item.sales_tax_applicable !== undefined
                ? [item.sales_tax_applicable ? "1" : "0"]
                : [];
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
        const salesTaxArray = Array.isArray(form.sales_tax_applicable)
            ? form.sales_tax_applicable
            : [];

        const payload = {
            item_name: form.item_name,
            unit_of_measure: form.unit_of_measure || null,
            price: form.price ? parseFloat(form.price) : 0,
            sales_tax_applicable: salesTaxArray.includes("1") || false,
            status: form.status ? Number(form.status) : 1,
            note: form.note ? form.note.trim() : null,
        };

        if (activeId.value) {
            await itemService.updateItem(activeId.value, payload);
        } else {
            const { data } = await itemService.createItem(payload);
            const message = data?.message || "";
            if (message.includes("**")) {
                const parts = message.split("**");
                activeId.value = parts[1];
                form.item_no = parts[2];
            } else if (data?.data) {
                activeId.value = data.data.id;
                form.item_no = data.data.item_no || "";
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
    if (!confirm("Delete this inventory item?")) return;
    try {
        await itemService.deleteItem(activeId.value);
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
