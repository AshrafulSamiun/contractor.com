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
                        <h2 class="pm-account-holder-title">Account Holders</h2>
                        <div
                            class="pm-page-subtitle pm-account-holder-subtitle"
                        >
                            Dashboard &gt; Profiles &gt; Account Holders &gt;
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
                            @click="startEdit(detailAccount)"
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
                            <h5 class="pm-form-title">Account Holder List</h5>
                            <div class="pm-account-holder-list-meta">
                                {{ filteredAccountHolders.length }} records
                                found
                            </div>
                        </div>
                        <div class="pm-account-holder-list-search">
                            <input
                                v-model.trim="listSearchQuery"
                                class="form-control"
                                type="search"
                                placeholder="Search all account holder fields"
                            />
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
                            <tbody v-if="paginatedAccountHolders.length">
                                <tr
                                    v-for="item in paginatedAccountHolders"
                                    :key="item.id"
                                >
                                    <td>
                                        <span
                                            class="pm-account-holder-table-code"
                                        >
                                            {{ item.system_no || "--" }}
                                        </span>
                                    </td>
                                    <td>
                                        <div
                                            class="pm-account-holder-table-primary"
                                        >
                                            {{ item.account_name || "--" }}
                                        </div>
                                        <div
                                            class="pm-account-holder-table-secondary"
                                        >
                                            {{
                                                item.email ||
                                                item.cell_phone ||
                                                "--"
                                            }}
                                        </div>
                                    </td>
                                    <td>{{ item.company_name || "--" }}</td>
                                    <td>
                                        <span
                                            class="pm-account-holder-type-badge"
                                            :class="
                                                getAccountTypeBadgeClass(
                                                    item.account_type,
                                                )
                                            "
                                        >
                                            {{
                                                formatListValue(
                                                    item.account_type,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td>{{ item.cell_phone || "--" }}</td>
                                    <td>
                                        {{
                                            [
                                                item.city,
                                                item.state,
                                                item.country_name,
                                            ]
                                                .filter(Boolean)
                                                .join(", ") || "--"
                                        }}
                                    </td>
                                    <td>
                                        <span
                                            class="pm-account-holder-status"
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
                                        class="pm-account-holder-table-empty"
                                        :colspan="listColumns.length + 1"
                                    >
                                        No account holders match your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pm-account-holder-list-footer">
                        <div class="pm-account-holder-pagination-summary">
                            Showing {{ paginationStart }}-{{ paginationEnd }} of
                            {{ filteredAccountHolders.length }}
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
                    v-else-if="viewMode === 'detail' && detailAccount"
                    class="pm-account-holder-detail-shell"
                >
                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-detail-code-card"
                    >
                        <div>
                            <div class="pm-account-holder-detail-code-label">
                                Account Number
                            </div>
                            <div class="pm-account-holder-detail-code-value">
                                {{ detailAccount.system_no || "--" }}
                            </div>
                        </div>
                        <div class="pm-account-holder-detail-badges">
                            <span
                                class="pm-account-holder-type-badge"
                                :class="
                                    getAccountTypeBadgeClass(
                                        detailAccount.account_type,
                                    )
                                "
                            >
                                {{
                                    formatListValue(detailAccount.account_type)
                                }}
                            </span>
                            <span
                                class="pm-account-holder-status"
                                :class="
                                    detailAccount.status_active
                                        ? 'is-active'
                                        : 'is-inactive'
                                "
                            >
                                {{
                                    detailAccount.status_active
                                        ? "Active"
                                        : "Inactive"
                                }}
                            </span>
                        </div>
                    </section>

                    <section class="pm-account-holder-detail-section">
                        <div class="pm-account-holder-detail-section-head">
                            Basic Information
                        </div>
                        <div
                            class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                        >
                            <div class="pm-account-holder-detail-grid">
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Name:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.account_name || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Company Name:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.company_name || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Type:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            formatListValue(
                                                detailAccount.account_type,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Email:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailAccount.email || "--" }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Office Phone:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.office_phone || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Mobile:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.cell_phone || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Website:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.website || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Preferred Contact:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            getContactMethodLabel(
                                                detailAccount.prefer_contact_method,
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-account-holder-detail-section">
                        <div class="pm-account-holder-detail-section-head">
                            Address Information
                        </div>
                        <div
                            class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                        >
                            <div class="pm-account-holder-detail-grid">
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Address:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailAddressLine }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >City:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailAccount.city || "--" }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Province/State:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailAccount.state || "--" }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Postal/Zip Code:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.zip_code || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Country:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.country_name || "--"
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-account-holder-detail-section">
                        <div class="pm-account-holder-detail-section-head">
                            Transaction Information
                        </div>
                        <div
                            class="pm-card pm-ops-card p-4 pm-account-holder-detail-card"
                        >
                            <div class="pm-account-holder-detail-grid">
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Sales Transaction:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.linked_transaction_sales ||
                                            "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Purchase Transaction:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.linked_transaction_purchase ||
                                            "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Tax ID:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.tax_id_no || "--"
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Business Number:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{
                                            detailAccount.business_number ||
                                            "--"
                                        }}</span
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
                                            formatDate(detailAccount.created_at)
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
                                            formatDate(detailAccount.updated_at)
                                        }}</span
                                    >
                                </div>
                                <div class="pm-account-holder-detail-row">
                                    <span class="pm-account-holder-detail-key"
                                        >Record ID:</span
                                    >
                                    <span
                                        class="pm-account-holder-detail-value"
                                        >{{ detailAccount.id || "--" }}</span
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
                            <h5 class="pm-form-title">
                                Account Type
                                <span class="pm-required-star">*</span>
                            </h5>
                            <span class="pm-account-holder-section-tag"
                                >Classification</span
                            >
                        </div>
                        <div class="pm-checkbox-grid">
                            <label
                                class="pm-checkbox-item"
                                v-for="type in accountTypeOptions"
                                :key="type.value"
                            >
                                <input
                                    type="radio"
                                    name="account_type"
                                    :value="type.value"
                                    v-model="form.account_type"
                                />
                                <span class="pm-checkbox-label">{{
                                    type.label
                                }}</span>
                            </label>
                        </div>
                        <div v-if="errors.account_type" class="pm-form-error">
                            {{ errors.account_type }}
                        </div>
                    </section>

                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-section"
                    >
                        <div class="pm-account-holder-section-head">
                            <h5 class="pm-form-title">Basic Information</h5>
                            <span class="pm-account-holder-section-tag"
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
                                    >Account Name
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.account_name"
                                    placeholder="Enter account name"
                                />
                                <div
                                    v-if="errors.account_name"
                                    class="pm-form-error"
                                >
                                    {{ errors.account_name }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Company Name</label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.company_name"
                                    placeholder="Enter company name"
                                />
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="pm-field-label"
                                    >Business Number</label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.business_number"
                                    placeholder="Business number"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label">Tax ID No</label>
                                <input
                                    class="form-control"
                                    v-model="form.tax_id_no"
                                    placeholder="Tax ID number"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="pm-field-label">Currency</label>
                                <select
                                    class="form-control"
                                    v-model="form.currency_id"
                                >
                                    <option value="">Select currency</option>
                                    <option
                                        v-for="curr in currencies"
                                        :key="curr.id"
                                        :value="curr.id"
                                    >
                                        {{ curr.currency_code }} -
                                        {{ curr.currency_name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-section"
                    >
                        <div class="pm-account-holder-section-head">
                            <h5 class="pm-form-title">Contact Information</h5>
                            <span class="pm-account-holder-section-tag"
                                >Communication</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label"
                                    >Office Phone</label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.office_phone"
                                    placeholder="+1 (555) 000-0000"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label"
                                    >Cell Phone
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.cell_phone"
                                    placeholder="+1 (555) 000-0000"
                                />
                                <div
                                    v-if="errors.cell_phone"
                                    class="pm-form-error"
                                >
                                    {{ errors.cell_phone }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label"
                                    >Email
                                    <span class="pm-required-star"
                                        >*</span
                                    ></label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="email@example.com"
                                />
                                <div v-if="errors.email" class="pm-form-error">
                                    {{ errors.email }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Website</label>
                                <input
                                    class="form-control"
                                    v-model="form.website"
                                    placeholder="https://example.com"
                                />
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="pm-field-label"
                                    >Preferred Contact Method</label
                                >
                                <div class="pm-checkbox-row">
                                    <label
                                        class="pm-checkbox-item"
                                        v-for="method in contactMethods"
                                        :key="method.value"
                                    >
                                        <input
                                            type="radio"
                                            name="prefer_contact_method"
                                            :value="method.value"
                                            v-model="form.prefer_contact_method"
                                        />
                                        <span class="pm-checkbox-label">{{
                                            method.label
                                        }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-section"
                    >
                        <div class="pm-account-holder-section-head">
                            <h5 class="pm-form-title">Address</h5>
                            <span class="pm-account-holder-section-tag"
                                >Location</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pm-field-label"
                                    >House Number</label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.house_number"
                                    placeholder="House/Office number"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label"
                                    >Street Number</label
                                >
                                <input
                                    class="form-control"
                                    v-model="form.street_number"
                                    placeholder="Street number"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">City</label>
                                <input
                                    class="form-control"
                                    v-model="form.city"
                                    placeholder="City"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">State</label>
                                <input
                                    class="form-control"
                                    v-model="form.state"
                                    placeholder="State"
                                />
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Country</label>
                                <select
                                    class="form-control"
                                    v-model="form.country"
                                >
                                    <option value="">Select country</option>
                                    <option
                                        v-for="(name, id) in countryOptions"
                                        :key="id"
                                        :value="id"
                                    >
                                        {{ name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="pm-field-label">Zip Code</label>
                                <input
                                    class="form-control"
                                    v-model="form.zip_code"
                                    placeholder="Zip code"
                                />
                            </div>
                        </div>
                    </section>

                    <section
                        class="pm-card pm-ops-card p-4 pm-account-holder-section"
                    >
                        <div class="pm-account-holder-section-head">
                            <h5 class="pm-form-title">Transaction & Status</h5>
                            <span class="pm-account-holder-section-tag"
                                >Settings</span
                            >
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="pm-field-label"
                                    >Linked Transaction - Sales</label
                                >
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form.linked_transaction_sales"
                                    placeholder="Enter Sales transaction"
                                />
                            </div>
                            <div class="col-md-12">
                                <label class="pm-field-label"
                                    >Linked Transaction - Purchase</label
                                >
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form.linked_transaction_purchase"
                                    placeholder="Enter Purchase transaction"
                                />
                            </div>
                            <div class="col-md-12">
                                <label class="pm-field-label">Status</label>
                                <div class="pm-checkbox-row">
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="checkbox"
                                            value="1"
                                            v-model="form.status_active"
                                        />
                                        <span class="pm-checkbox-label"
                                            >Active</span
                                        >
                                    </label>
                                    <label class="pm-checkbox-item">
                                        <input
                                            type="checkbox"
                                            value="0"
                                            v-model="form.status_active"
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
                        class="pm-card pm-ops-card p-3 pm-account-holder-actions-sticky"
                    >
                        <div class="pm-form-actions pm-form-actions-right">
                            <button
                                class="btn btn-outline-primary"
                                type="button"
                                :disabled="!!activeId"
                                @click="resetForm"
                            >
                                New
                            </button>
                            <button
                                class="btn btn-outline-danger"
                                type="button"
                                :disabled="!activeId"
                                @click="deleteCurrent"
                            >
                                Delete
                            </button>
                            <button
                                class="btn btn-primary"
                                type="button"
                                :disabled="saving"
                                @click="saveAccountHolder"
                            >
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
import client from "../api/client";
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
const detailAccount = ref(null);
const errors = reactive({});
const countryOptions = reactive({});
const currencies = ref([]);
const accountHolders = ref([]);
const listSearchQuery = ref("");
const listPage = ref(1);
const listPageSize = 8;
const listSortKey = ref("created_at");
const listSortDirection = ref("desc");

const accountTypeOptions = [
    { value: 1, label: "Customer" },
    { value: 2, label: "Seller" },
    { value: 3, label: "Service Provider" },
    { value: 4, label: "Employee" },
    { value: 5, label: "Bank" },
    { value: 6, label: "Credit Card" },
    { value: 7, label: "Government" },
    { value: 8, label: "Tax Office" },
    { value: 9, label: "Shareholder" },
];

const contactMethods = [
    { value: 1, label: "Phone" },
    { value: 2, label: "Email" },
    { value: 3, label: "Other" },
];

const listColumns = [
    { key: "system_no", label: "System No" },
    { key: "account_name", label: "Account Name" },
    { key: "company_name", label: "Company" },
    { key: "account_type", label: "Account Type" },
    { key: "cell_phone", label: "Phone" },
    { key: "location", label: "Location" },
    { key: "status_active", label: "Status" },
    { key: "created_at", label: "Created" },
];

const form = reactive({
    system_no: "",
    account_name: "",
    company_name: "",
    business_number: "",
    tax_id_no: "",
    currency_id: "",
    account_type: "",
    house_number: "",
    street_number: "",
    city: "",
    state: "",
    country: "",
    zip_code: "",
    office_phone: "",
    cell_phone: "",
    email: "",
    website: "",
    prefer_contact_method: "",
    linked_transaction_sales: "",
    linked_transaction_purchase: "",
    status_active: [],
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

const accountTypeLabelMap = Object.fromEntries(
    accountTypeOptions.map((option) => [option.value, option.label]),
);

const contactMethodLabelMap = Object.fromEntries(
    contactMethods.map((option) => [option.value, option.label]),
);

const getAccountTypeLabel = (value) =>
    accountTypeLabelMap[Number(value)] || "--";

const getContactMethodLabel = (value) =>
    contactMethodLabelMap[Number(value)] || "--";

const getAccountTypeBadgeClass = (value) => {
    const classes = {
        1: "is-customer",
        2: "is-seller",
        3: "is-service-provider",
        4: "is-employee",
        5: "is-bank",
        6: "is-credit-card",
        7: "is-government",
        8: "is-tax-office",
        9: "is-shareholder",
    };

    return classes[Number(value)] || "is-default";
};

const formatListValue = (value) => {
    if (value == null || value === "") {
        return "--";
    }

    return getAccountTypeLabel(value);
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
    if (key === "account_type") {
        return formatListValue(item.account_type);
    }

    if (key === "location") {
        return [item.city, item.state, item.country_name]
            .filter(Boolean)
            .join(", ");
    }

    if (key === "status_active") {
        return item.status_active ? "Active" : "Inactive";
    }

    return item[key] ?? "";
};

const filteredAccountHolders = computed(() => {
    const query = listSearchQuery.value.trim().toLowerCase();

    if (!query) {
        return accountHolders.value;
    }

    return accountHolders.value.filter((item) =>
        flattenSearchValue({
            ...item,
            account_type_label: getAccountTypeLabel(item.account_type),
            prefer_contact_method_label: getContactMethodLabel(
                item.prefer_contact_method,
            ),
        })
            .toLowerCase()
            .includes(query),
    );
});

const sortedAccountHolders = computed(() => {
    const items = [...filteredAccountHolders.value];

    items.sort((left, right) => {
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

    return items;
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(sortedAccountHolders.value.length / listPageSize)),
);

const paginatedAccountHolders = computed(() => {
    const start = (listPage.value - 1) * listPageSize;
    return sortedAccountHolders.value.slice(start, start + listPageSize);
});

const paginationStart = computed(() => {
    if (!filteredAccountHolders.value.length) return 0;
    return (listPage.value - 1) * listPageSize + 1;
});

const paginationEnd = computed(() =>
    Math.min(
        listPage.value * listPageSize,
        filteredAccountHolders.value.length,
    ),
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

const detailAddressLine = computed(() => {
    if (!detailAccount.value) return "--";

    return (
        [detailAccount.value.house_number, detailAccount.value.street_number]
            .filter(Boolean)
            .join(", ") || "--"
    );
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

const loadOptions = async () => {
    loadingList.value = true;
    try {
        const { data } = await client.get("/account-holders");
        if (data.country_arr) {
            Object.assign(countryOptions, data.country_arr);
        }
        if (data.currencies) {
            currencies.value = data.currencies;
        }
        accountHolders.value = Array.isArray(data.account_holders)
            ? data.account_holders
            : [];
    } catch {
        // handled by toast
    } finally {
        loadingList.value = false;
    }
};

const resetForm = () => {
    activeId.value = null;
    detailAccount.value = null;
    mapErrors(null);
    form.system_no = "";
    form.account_name = "";
    form.company_name = "";
    form.business_number = "";
    form.tax_id_no = "";
    form.currency_id = "";
    form.account_type = "";
    form.house_number = "";
    form.street_number = "";
    form.city = "";
    form.state = "";
    form.country = "";
    form.zip_code = "";
    form.office_phone = "";
    form.cell_phone = "";
    form.email = "";
    form.website = "";
    form.prefer_contact_method = "";
    form.linked_transaction_sales = "";
    form.linked_transaction_purchase = "";
    form.status_active = [];
    form.id = "";
};

const openFormView = () => {
    viewMode.value = "form";
    resetForm();
    router.replace({ query: { ...route.query, id: undefined } });
};

const openListView = async () => {
    viewMode.value = "list";
    detailAccount.value = null;
    await loadOptions();
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
    await loadAccountHolder(item.id);
    router.replace({ query: { ...route.query, id: item.id } });
};

const startView = async (item) => {
    await loadAccountHolderDetail(item.id);
};

const loadAccountHolderDetail = async (id) => {
    try {
        const { data } = await client.get(`/account-holders/${id}/edit`);
        const item = data.account_holder;
        if (!item) return;

        detailAccount.value = {
            ...item,
            account_type_label: getAccountTypeLabel(item.account_type),
            prefer_contact_method_label: getContactMethodLabel(
                item.prefer_contact_method,
            ),
        };
        viewMode.value = "detail";
    } catch {
        // handled by toast
    }
};

const loadAccountHolder = async (id) => {
    try {
        const { data } = await client.get(`/account-holders/${id}/edit`);
        const item = data.account_holder;
        if (!item) return;
        activeId.value = item.id;
        form.system_no = item.system_no || "";
        form.account_name = item.account_name || "";
        form.company_name = item.company_name || "";
        form.business_number = item.business_number || "";
        form.tax_id_no = item.tax_id_no || "";
        form.currency_id = item.currency_id || "";
        form.account_type = item.account_type ?? "";
        form.house_number = item.house_number || "";
        form.street_number = item.street_number || "";
        form.city = item.city || "";
        form.state = item.state || "";
        form.country = item.country || "";
        form.zip_code = item.zip_code || "";
        form.office_phone = item.office_phone || "";
        form.cell_phone = item.cell_phone || "";
        form.email = item.email || "";
        form.website = item.website || "";
        form.prefer_contact_method = item.prefer_contact_method ?? "";
        form.linked_transaction_sales = item.linked_transaction_sales || "";
        form.linked_transaction_purchase =
            item.linked_transaction_purchase || "";
        form.status_active =
            item.status_active !== undefined
                ? [item.status_active ? "1" : "0"]
                : [];
        viewMode.value = "form";
    } catch {
        // handled by toast
    }
};

const saveAccountHolder = async () => {
    saving.value = true;
    mapErrors(null);
    try {
        const payload = {
            ...form,
            account_type: form.account_type ? Number(form.account_type) : null,
            prefer_contact_method: form.prefer_contact_method
                ? Number(form.prefer_contact_method)
                : null,
            status_active: form.status_active.includes("1") ? 1 : 0,
        };

        if (activeId.value) {
            await client.put(`/account-holders/${activeId.value}`, payload);
        } else {
            const { data } = await client.post("/account-holders", payload);
            const message = data?.message || "";
            if (message.includes("**")) {
                const parts = message.split("**");
                activeId.value = parts[1];
                form.system_no = parts[2];
            }
        }
        await loadOptions();
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
    if (!confirm("Delete this account holder?")) return;
    try {
        await client.delete(`/account-holders/${activeId.value}`);
        resetForm();
        await loadOptions();
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
    await loadOptions();
    if (route.query?.id) loadAccountHolder(route.query.id);
});

onUnmounted(() => {
    document.removeEventListener("click", handleOutsideClick);
    document.removeEventListener("keydown", handleEsc);
});

watch(
    () => route.query?.id,
    async (id) => {
        if (id) {
            loadAccountHolder(id);
        } else {
            resetForm();
        }
    },
);

watch(listSearchQuery, () => {
    listPage.value = 1;
});

watch(filteredAccountHolders, () => {
    if (listPage.value > totalPages.value) {
        listPage.value = totalPages.value;
    }
});
</script>
