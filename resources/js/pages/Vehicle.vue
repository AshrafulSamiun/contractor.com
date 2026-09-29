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

                <div class="pm-topbar-page-title">Vehicle Entry</div>

                <div class="pm-topbar-search">
                    <input
                        v-model="topbarSearch"
                        class="form-control"
                        placeholder="Search..."
                    />
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
                        <span class="pm-topbar-dot">5</span>
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

            <div class="container pm-vehicle-page">
                <header class="pm-page-header">
                    <div class="pm-title-block">
                        <h1>
                            {{ mode === "list" ? "Vehicles List" : formTitle }}
                        </h1>
                        <div class="pm-breadcrumbs">
                            <span>Dashboard</span>
                            <span>Vehicle Management</span>
                            <span>Vehicles</span>
                            <span v-if="mode === 'form'">{{
                                activeId ? "Edit Vehicle" : "New Vehicle"
                            }}</span>
                        </div>
                    </div>

                    <div class="pm-header-actions">
                        <template v-if="mode === 'list'">
                            <button
                                class="pm-outline-btn"
                                type="button"
                                @click="exportList"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 4v10" />
                                    <path d="m8 10 4 4 4-4" />
                                    <path d="M5 19h14" />
                                </svg>
                                <span>Export</span>
                            </button>
                            <button
                                class="pm-outline-btn"
                                type="button"
                                @click="printPage"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M7 8V4h10v4" />
                                    <path
                                        d="M6 18H5a2 2 0 0 1-2-2v-5a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v5a2 2 0 0 1-2 2h-1"
                                    />
                                    <path d="M7 14h10v6H7z" />
                                </svg>
                                <span>Print</span>
                            </button>
                            <button
                                class="pm-primary-btn"
                                type="button"
                                @click="startCreate"
                            >
                                <span>+ Add New Vehicle</span>
                            </button>
                        </template>

                        <template v-else>
                            <button
                                class="pm-outline-btn"
                                type="button"
                                @click="backToList"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M15 6 9 12l6 6" />
                                </svg>
                                <span>Back to List</span>
                            </button>
                            <button
                                class="pm-primary-btn"
                                type="button"
                                @click="startCreate"
                            >
                                <span>+ New Vehicle</span>
                            </button>
                        </template>
                    </div>
                </header>

                <template v-if="mode === 'list'">
                    <div class="pm-vehicle-list-top">
                    <section class="pm-filter-shell">
                        <div class="pm-filter-bar">
                            <div class="pm-search-field">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                                    />
                                </svg>
                                <input
                                    v-model.trim="filters.search"
                                    type="search"
                                    class="form-control"
                                    placeholder="Search by Vehicle No., VIN"
                                />
                            </div>

                            <select
                                v-model="filters.status"
                                class="form-control"
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
                                v-model="filters.make_brand"
                                class="form-control"
                            >
                                <option value="">All Makes</option>
                                <option
                                    v-for="item in availableMakes"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ item }}
                                </option>
                            </select>

                            <select
                                v-model="filters.fuel_type"
                                class="form-control"
                            >
                                <option value="">All Fuel Types</option>
                                <option
                                    v-for="item in availableFuelTypes"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ item }}
                                </option>
                            </select>

                            <button
                                class="pm-ghost-link"
                                type="button"
                                @click="showMoreFilters = !showMoreFilters"
                            >
                                {{
                                    showMoreFilters
                                        ? "Less Filters"
                                        : "More Filters"
                                }}
                            </button>

                            <div class="pm-filter-actions">
                                <button
                                    class="pm-outline-btn"
                                    type="button"
                                    @click="resetFilters"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20 6v5h-5" />
                                        <path
                                            d="M20 11a8 8 0 1 1-2.34-5.66L20 6"
                                        />
                                    </svg>
                                    <span>Reset</span>
                                </button>
                                <button
                                    class="pm-primary-btn"
                                    type="button"
                                    @click="fetchVehicles"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z" />
                                    </svg>
                                    <span>Apply Filters</span>
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="showMoreFilters"
                            class="pm-filter-bar pm-filter-bar-extra"
                        >
                            <select
                                v-model="filters.vehicle_type"
                                class="form-control"
                            >
                                <option value="">All Types</option>
                                <option
                                    v-for="item in vehicleTypeOptions"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ item }}
                                </option>
                            </select>

                            <select
                                v-model="filters.vehicle_year"
                                class="form-control"
                            >
                                <option value="">All Years</option>
                                <option
                                    v-for="item in availableYears"
                                    :key="item"
                                    :value="String(item)"
                                >
                                    {{ item }}
                                </option>
                            </select>

                            <select
                                v-model="filters.in_service"
                                class="form-control"
                            >
                                <option value="">All Service Modes</option>
                                <option value="1">In Service</option>
                                <option value="0">Not In Service</option>
                            </select>
                        </div>
                    </section>

                    <section class="pm-stats-grid">
                        <article
                            v-for="card in statCards"
                            :key="card.key"
                            class="pm-stat-card"
                            :class="card.className"
                        >
                            <div class="pm-stat-copy">
                                <span>{{ card.label }}</span>
                                <strong>{{ card.value }}</strong>
                            </div>
                            <div class="pm-stat-icon" :class="card.className">
                                <svg
                                    v-if="card.key === 'total'"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M5 14.6v-2c0-.7.57-1.3 1.28-1.3h1.06l1.18-2.14c.23-.42.67-.68 1.15-.68h4.64c.49 0 .95.22 1.25.6l1.6 2.22h.95c.7 0 1.28.6 1.28 1.3v2"
                                    />
                                    <path d="M6.9 14.6h1.55m5.05 0h2.55" />
                                    <circle cx="9.45" cy="15.2" r="1.45" />
                                    <circle cx="15.8" cy="15.2" r="1.45" />
                                </svg>
                                <svg
                                    v-else-if="card.key === 'service'"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="8.3" />
                                    <path d="m8.7 12 2.2 2.25 4.65-4.75" />
                                </svg>
                                <svg
                                    v-else-if="card.key === 'available'"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="8.3" />
                                    <path d="M12 8.1v4.75" />
                                    <circle
                                        cx="12"
                                        cy="15.95"
                                        r="0.82"
                                        fill="currentColor"
                                        stroke="none"
                                    />
                                </svg>
                                <svg
                                    v-else-if="card.key === 'documents'"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <rect x="5" y="4" width="14" height="16" rx="2" />
                                    <path d="M8 3v4m8-4v4M8 11h8m-8 4h5" />
                                </svg>
                                <svg
                                    v-else-if="card.key === 'maintenance'"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m14.7 6.25 3.05 3.05-7.05 7.05-3.65.78.78-3.66 6.87-6.86Z"
                                    />
                                    <path d="m13.82 7.12 3.05 3.05" />
                                </svg>
                            </div>
                        </article>
                    </section>
                    </div>

                    <section class="pm-table-card">
                        <div class="table-responsive">
                            <table class="table pm-vehicle-table">
                                <thead>
                                    <tr>
                                        <th>Vehicle No.</th>
                                        <th>Vehicle Photo</th>
                                        <th>Driver Name - Photo</th>
                                        <th>Make/Model</th>
                                        <th>Vehicle Age</th>
                                        <th>Year</th>
                                        <th>VIN</th>
                                        <th>License Plate</th>
                                        <th>Fuel Type</th>
                                        <th>Status</th>
                                        <th>Current Mileage</th>
                                        <th>Vehicle Keys Tag No.</th>
                                        <th>View Profile</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loadingList">
                                        <td colspan="14" class="text-center">
                                            Loading vehicles...
                                        </td>
                                    </tr>
                                    <tr v-else-if="!paginatedVehicles.length">
                                        <td colspan="14" class="text-center">
                                            No vehicles found.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="item in paginatedVehicles"
                                        :key="item.id"
                                    >
                                        <td class="pm-vehicle-no-cell">{{ item.vehicle_number }}</td>
                                        <td>
                                            <div class="pm-vehicle-photo">
                                                <img v-if="item.primary_photo" :src="item.primary_photo" alt="Vehicle" />
                                                <svg v-else viewBox="0 0 24 24" aria-hidden="true">
                                                    <path
                                                        d="M5 16l1.2-5A2 2 0 0 1 8.15 9h7.7a2 2 0 0 1 1.95 2l1.2 5M4 16h16M6 19v-3M18 19v-3"
                                                    />
                                                </svg>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="pm-driver-chip">
                                                <span class="pm-driver-avatar">
                                                    {{
                                                        item.driver_initials ||
                                                        deriveInitials(
                                                            item.driver_name,
                                                        )
                                                    }}
                                                </span>
                                                <span><strong>{{ item.driver_name || "Unassigned" }}</strong><small>Driver</small></span>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{
                                                item.make_brand
                                            }}</strong>
                                            <div class="pm-table-sub">
                                                {{ item.model }}
                                            </div>
                                        </td>
                                        <td>{{ vehicleAge(item.vehicle_year) }}</td>
                                        <td>{{ item.vehicle_year || "--" }}</td>
                                        <td>{{ item.vin || "--" }}</td>
                                        <td>{{ item.plate_number || "--" }}</td>
                                        <td>{{ item.fuel_type || "--" }}</td>
                                        <td>
                                            <span
                                                class="pm-status-pill"
                                                :class="
                                                    statusClass(item.status)
                                                "
                                            >
                                                {{ item.status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            {{
                                                formatMileage(
                                                    item.current_mileage,
                                                )
                                            }}
                                        </td>
                                        <td>{{ item.vehicle_keys_tag_no || "--" }}</td>
                                        <td>
                                            <button class="pm-table-link pm-view-profile" type="button" @click="editVehicle(item.id)">View Profile</button>
                                        </td>
                                        <td>
                                            <div class="pm-row-actions">
                                                <button
                                                    class="pm-table-link"
                                                    type="button"
                                                    @click="
                                                        editVehicle(item.id)
                                                    "
                                                >
                                                    View Details
                                                </button>
                                                <button
                                                    class="pm-dots-btn"
                                                    type="button"
                                                    @click="
                                                        editVehicle(item.id)
                                                    "
                                                >
                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        aria-hidden="true"
                                                    >
                                                        <circle
                                                            cx="12"
                                                            cy="5"
                                                            r="1.8"
                                                        />
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="1.8"
                                                        />
                                                        <circle
                                                            cx="12"
                                                            cy="19"
                                                            r="1.8"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pm-table-footer">
                            <div>
                                Showing
                                {{
                                    paginatedVehicles.length ? pageStart + 1 : 0
                                }}
                                to {{ pageStart + paginatedVehicles.length }} of
                                {{ filteredVehicles.length }} entries
                            </div>

                            <div class="pm-pagination-wrap">
                                <div class="pm-pagination">
                                    <button
                                        class="pm-outline-btn pm-page-nav"
                                        type="button"
                                        :disabled="currentPage === 1"
                                        @click="currentPage -= 1"
                                    >
                                        ‹
                                    </button>
                                    <button
                                        v-for="page in totalPages"
                                        :key="page"
                                        class="pm-page-btn"
                                        type="button"
                                        :class="{
                                            active: page === currentPage,
                                        }"
                                        @click="currentPage = page"
                                    >
                                        {{ page }}
                                    </button>
                                    <button
                                        class="pm-outline-btn pm-page-nav"
                                        type="button"
                                        :disabled="currentPage === totalPages"
                                        @click="currentPage += 1"
                                    >
                                        ›
                                    </button>
                                </div>

                                <select
                                    v-model.number="perPage"
                                    class="form-control pm-per-page"
                                >
                                    <option :value="8">8 per page</option>
                                    <option :value="10">10 per page</option>
                                    <option :value="20">20 per page</option>
                                    <option :value="50">50 per page</option>
                                </select>
                            </div>
                        </div>
                    </section>
                </template>

                <template v-else>
                    <form class="pm-form-shell" @submit.prevent="saveVehicle">
                        <div class="pm-form-grid">
                            <section class="pm-section-card pm-card-blue">
                                <div class="pm-section-title">
                                    <span
                                        class="pm-section-icon"
                                        aria-hidden="true"
                                        >🚙</span
                                    >
                                    <span>Basic Info.</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label
                                            >Vehicle No. <span>*</span></label
                                        >
                                        <input
                                            v-model="form.vehicle_number"
                                            class="form-control"
                                        />
                                        <small
                                            v-if="errors.vehicle_number"
                                            class="pm-error"
                                            >{{ errors.vehicle_number }}</small
                                        >
                                    </div>
                                    <div class="pm-field">
                                        <label>Make <span>*</span></label>
                                        <select
                                            v-model="form.make_brand"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select make
                                            </option>
                                            <option
                                                v-for="item in makeOptions"
                                                :key="item"
                                                :value="item"
                                            >
                                                {{ item }}
                                            </option>
                                        </select>
                                        <small
                                            v-if="errors.make_brand"
                                            class="pm-error"
                                            >{{ errors.make_brand }}</small
                                        >
                                    </div>

                                    <div class="pm-field">
                                        <label>VIN Number <span>*</span></label>
                                        <input
                                            v-model="form.vin"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>Year <span>*</span></label>
                                        <select
                                            v-model="form.vehicle_year"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select year
                                            </option>
                                            <option
                                                v-for="item in yearOptions"
                                                :key="item"
                                                :value="String(item)"
                                            >
                                                {{ item }}
                                            </option>
                                        </select>
                                        <small
                                            v-if="errors.vehicle_year"
                                            class="pm-error"
                                            >{{ errors.vehicle_year }}</small
                                        >
                                    </div>

                                    <div class="pm-field">
                                        <label>Model <span>*</span></label>
                                        <input
                                            v-model="form.model"
                                            class="form-control"
                                        />
                                        <small
                                            v-if="errors.model"
                                            class="pm-error"
                                            >{{ errors.model }}</small
                                        >
                                    </div>
                                    <div class="pm-field">
                                        <label>Type</label>
                                        <select
                                            v-model="form.vehicle_type"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select type
                                            </option>
                                            <option
                                                v-for="item in vehicleTypeOptions"
                                                :key="item"
                                                :value="item"
                                            >
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="pm-field">
                                        <label
                                            >Plate Number <span>*</span></label
                                        >
                                        <input
                                            v-model="form.plate_number"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>Color</label>
                                        <select
                                            v-model="form.color"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select color
                                            </option>
                                            <option
                                                v-for="item in colorOptions"
                                                :key="item"
                                                :value="item"
                                            >
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Fuel Type</label>
                                        <select
                                            v-model="form.fuel_type"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select fuel type
                                            </option>
                                            <option
                                                v-for="item in fuelTypeOptions"
                                                :key="item"
                                                :value="item"
                                            >
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="pm-field">
                                        <label
                                            >Vehicle Keys Tag No.
                                            <span>*</span></label
                                        >
                                        <input
                                            v-model="form.vehicle_keys_tag_no"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label
                                            >Plate Expiry Date
                                            <span>*</span></label
                                        >
                                        <input
                                            v-model="form.plate_expiry_date"
                                            class="form-control"
                                            type="date"
                                        />
                                    </div>

                                    <div class="pm-field">
                                        <label
                                            >Assignment Start Date
                                            <span>*</span></label
                                        >
                                        <input
                                            v-model="form.assignment_start_date"
                                            class="form-control"
                                            type="date"
                                        />
                                    </div>
                                    <div class="pm-field pm-switch-row">
                                        <label>In Service</label>
                                        <label class="pm-switch">
                                            <input
                                                v-model="form.in_service"
                                                type="checkbox"
                                            />
                                            <span></span>
                                        </label>
                                    </div>

                                    <div class="pm-field pm-field-full">
                                        <label>Status <span>*</span></label>
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

                                    <div class="pm-field pm-field-full">
                                        <button
                                            class="pm-outline-ghost-btn"
                                            type="button"
                                        >
                                            Go to Mileage
                                        </button>
                                    </div>
                                </div>
                            </section>

                            <div class="pm-right-stack">
                                <section class="pm-section-card pm-card-green">
                                    <div class="pm-section-title">
                                        <span
                                            class="pm-section-icon"
                                            aria-hidden="true"
                                            >🛡️</span
                                        >
                                        <span>Insurance Info.</span>
                                    </div>

                                    <div class="pm-grid-2">
                                        <div class="pm-field pm-field-full">
                                            <label
                                                >Insurance Company
                                                <span>*</span></label
                                            >
                                            <input
                                                v-model="
                                                    form.insurance_provider
                                                "
                                                class="form-control"
                                            />
                                        </div>
                                        <div class="pm-field pm-field-full">
                                            <label
                                                >Policy Number
                                                <span>*</span></label
                                            >
                                            <input
                                                v-model="form.policy_number"
                                                class="form-control"
                                            />
                                        </div>
                                        <div class="pm-field">
                                            <label
                                                >Policy Start Date
                                                <span>*</span></label
                                            >
                                            <input
                                                v-model="
                                                    form.insurance_start_date
                                                "
                                                class="form-control"
                                                type="date"
                                            />
                                        </div>
                                        <div class="pm-field">
                                            <label
                                                >Policy Expiry Date
                                                <span>*</span></label
                                            >
                                            <input
                                                v-model="
                                                    form.insurance_expiry_date
                                                "
                                                class="form-control"
                                                type="date"
                                            />
                                        </div>
                                        <div
                                            class="pm-field pm-switch-row pm-field-full"
                                        >
                                            <label
                                                >Expired <span>*</span></label
                                            >
                                            <label class="pm-switch">
                                                <input
                                                    v-model="
                                                        form.insurance_expired
                                                    "
                                                    type="checkbox"
                                                />
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </section>

                                <section class="pm-section-card pm-card-orange">
                                    <div class="pm-section-title">
                                        <span
                                            class="pm-section-icon"
                                            aria-hidden="true"
                                            >📤</span
                                        >
                                        <span>Car Photos</span>
                                    </div>

                                    <div class="pm-upload-stack">
                                        <div class="pm-photo-upload">
                                            <button
                                                class="pm-outline-ghost-btn"
                                                type="button"
                                                @click="addPhoto"
                                            >
                                                Upload Photos
                                            </button>
                                        </div>

                                        <div class="pm-photo-grid">
                                            <div
                                                v-for="(
                                                    photo, index
                                                ) in form.car_photos"
                                                :key="`photo-${index}`"
                                                class="pm-photo-card"
                                            >
                                                <input
                                                    v-model="
                                                        form.car_photos[index]
                                                    "
                                                    class="form-control"
                                                    :placeholder="
                                                        index === 0
                                                            ? 'Primary photo file name'
                                                            : 'Additional photo file name'
                                                    "
                                                />
                                                <button
                                                    v-if="
                                                        form.car_photos.length >
                                                        1
                                                    "
                                                    class="pm-photo-remove"
                                                    type="button"
                                                    @click="removePhoto(index)"
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <section class="pm-section-card pm-card-purple">
                                <div class="pm-section-title">
                                    <span
                                        class="pm-section-icon"
                                        aria-hidden="true"
                                        >🧑</span
                                    >
                                    <span>Seller Info.</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field pm-field-full">
                                        <label>Name <span>*</span></label>
                                        <input
                                            v-model="form.seller_name"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label
                                            >Company Name <span>*</span></label
                                        >
                                        <input
                                            v-model="form.seller_company_name"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Phone <span>*</span></label>
                                        <input
                                            v-model="form.seller_phone"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Email <span>*</span></label>
                                        <input
                                            v-model="form.seller_email"
                                            class="form-control"
                                            type="email"
                                        />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Website</label>
                                        <input
                                            v-model="form.seller_website"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                            </section>

                            <section class="pm-section-card pm-card-green">
                                <div class="pm-section-title">
                                    <span
                                        class="pm-section-icon"
                                        aria-hidden="true"
                                        >💲</span
                                    >
                                    <span>Financial</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label
                                            >Purchase Invoice No.
                                            <span>*</span></label
                                        >
                                        <input
                                            v-model="
                                                form.purchase_invoice_number
                                            "
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label
                                            >Invoice Date <span>*</span></label
                                        >
                                        <input
                                            v-model="form.invoice_date"
                                            class="form-control"
                                            type="date"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>Sales Tax <span>*</span></label>
                                        <input
                                            v-model="form.sales_tax"
                                            class="form-control"
                                            type="number"
                                            step="0.01"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>Subtotal <span>*</span></label>
                                        <input
                                            v-model="form.subtotal"
                                            class="form-control"
                                            type="number"
                                            step="0.01"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label
                                            >Number of Installments
                                            <span>*</span></label
                                        >
                                        <select
                                            v-model="
                                                form.number_of_installments
                                            "
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select installments
                                            </option>
                                            <option
                                                v-for="item in installmentOptions"
                                                :key="item"
                                                :value="String(item)"
                                            >
                                                {{ item }} Installments
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Total Paid <span>*</span></label>
                                        <input
                                            v-model="form.total_paid"
                                            class="form-control"
                                            type="number"
                                            step="0.01"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label
                                            >First Installment
                                            <span>*</span></label
                                        >
                                        <input
                                            v-model="
                                                form.first_installment_amount
                                            "
                                            class="form-control"
                                            type="number"
                                            step="0.01"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>First Installment Date</label>
                                        <input
                                            v-model="
                                                form.first_installment_date
                                            "
                                            class="form-control"
                                            type="date"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label
                                            >Last Installment
                                            <span>*</span></label
                                        >
                                        <input
                                            v-model="
                                                form.last_installment_amount
                                            "
                                            class="form-control"
                                            type="number"
                                            step="0.01"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>Last Installment Date</label>
                                        <input
                                            v-model="form.last_installment_date"
                                            class="form-control"
                                            type="date"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>Purchase Price</label>
                                        <input
                                            v-model="form.purchase_price"
                                            class="form-control"
                                            type="number"
                                            step="0.01"
                                        />
                                    </div>
                                    <div class="pm-field">
                                        <label>Odometer</label>
                                        <input
                                            v-model="form.current_mileage"
                                            class="form-control"
                                            type="number"
                                        />
                                    </div>
                                </div>
                            </section>

                            <section class="pm-section-card pm-card-blue">
                                <div class="pm-section-title">
                                    <span
                                        class="pm-section-icon"
                                        aria-hidden="true"
                                        >🧑‍✈️</span
                                    >
                                    <span>Driver(s) Info.</span>
                                </div>

                                <div class="pm-repeater-stack">
                                    <div
                                        v-for="(
                                            driver, index
                                        ) in form.driver_profiles"
                                        :key="`driver-${index}`"
                                        class="pm-repeater-card"
                                    >
                                        <div class="pm-grid-2">
                                            <div class="pm-field">
                                                <label
                                                    >Driver Name
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="driver.name"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >Phone <span>*</span></label
                                                >
                                                <input
                                                    v-model="driver.phone"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >Email <span>*</span></label
                                                >
                                                <input
                                                    v-model="driver.email"
                                                    class="form-control"
                                                    type="email"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >Address
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="driver.address"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >Driver License Number
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="
                                                        driver.license_number
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >License Expiry Date
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="
                                                        driver.license_expiry_date
                                                    "
                                                    class="form-control"
                                                    type="date"
                                                />
                                            </div>
                                            <div class="pm-field pm-field-full">
                                                <label
                                                    >License Expired
                                                    <span>*</span></label
                                                >
                                                <select
                                                    v-model="
                                                        driver.license_expired
                                                    "
                                                    class="form-control"
                                                >
                                                    <option :value="false">
                                                        No
                                                    </option>
                                                    <option :value="true">
                                                        Yes
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="pm-repeater-actions">
                                            <button
                                                class="pm-outline-btn"
                                                type="button"
                                                @click="removeDriver(index)"
                                            >
                                                Remove Driver
                                            </button>
                                        </div>
                                    </div>

                                    <button
                                        class="pm-outline-ghost-btn"
                                        type="button"
                                        @click="addDriver"
                                    >
                                        + Add Driver
                                    </button>
                                </div>
                            </section>

                            <section class="pm-section-card pm-card-green">
                                <div class="pm-section-title">
                                    <span
                                        class="pm-section-icon"
                                        aria-hidden="true"
                                        >🛡️</span
                                    >
                                    <span>Insurance Documents</span>
                                </div>

                                <div class="pm-repeater-stack">
                                    <div
                                        v-for="(
                                            document, index
                                        ) in form.insurance_documents"
                                        :key="`document-${index}`"
                                        class="pm-repeater-card pm-document-card"
                                    >
                                        <div class="pm-grid-2">
                                            <div class="pm-field">
                                                <label
                                                    >Insurance Company
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="
                                                        document.insurance_company
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >Policy Number
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="
                                                        document.policy_number
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >Policy Start Date
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="
                                                        document.policy_start_date
                                                    "
                                                    class="form-control"
                                                    type="date"
                                                />
                                            </div>
                                            <div class="pm-field">
                                                <label
                                                    >Policy Expiry Date
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="
                                                        document.policy_expiry_date
                                                    "
                                                    class="form-control"
                                                    type="date"
                                                />
                                            </div>
                                            <div class="pm-field pm-field-full">
                                                <label
                                                    >Expired
                                                    <span>*</span></label
                                                >
                                                <select
                                                    v-model="document.expired"
                                                    class="form-control"
                                                >
                                                    <option :value="false">
                                                        No
                                                    </option>
                                                    <option :value="true">
                                                        Yes
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-field pm-field-full">
                                                <label
                                                    >Document (Policy Copy)
                                                    <span>*</span></label
                                                >
                                                <input
                                                    v-model="
                                                        document.document_name
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>

                                        <div class="pm-upload-doc-row">
                                            <button
                                                class="pm-outline-ghost-btn"
                                                type="button"
                                            >
                                                Upload
                                            </button>
                                        </div>

                                        <div class="pm-repeater-actions">
                                            <button
                                                class="pm-outline-btn"
                                                type="button"
                                                @click="
                                                    removeInsuranceDocument(
                                                        index,
                                                    )
                                                "
                                            >
                                                Remove Insurance
                                            </button>
                                        </div>
                                    </div>

                                    <button
                                        class="pm-outline-ghost-btn"
                                        type="button"
                                        @click="addInsuranceDocument"
                                    >
                                        + Add Another Insurance
                                    </button>
                                </div>
                            </section>

                            <section
                                class="pm-section-card pm-section-card-full pm-card-orange"
                            >
                                <div class="pm-section-title-row">
                                    <div class="pm-section-title">
                                        <span
                                            class="pm-section-icon"
                                            aria-hidden="true"
                                            >⚠️</span
                                        >
                                        <span>Safety Equipments</span>
                                    </div>
                                    <button
                                        class="pm-outline-btn"
                                        type="button"
                                        @click="addSafetyEquipment"
                                    >
                                        + Add Equipment
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table pm-safety-table">
                                        <thead>
                                            <tr>
                                                <th>Equipment</th>
                                                <th>Available</th>
                                                <th>Condition</th>
                                                <th>Notes</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    equipment, index
                                                ) in form.safety_equipments"
                                                :key="`safety-${index}`"
                                            >
                                                <td>
                                                    <input
                                                        v-model="
                                                            equipment.equipment
                                                        "
                                                        class="form-control"
                                                        placeholder="Equipment name"
                                                    />
                                                </td>
                                                <td>
                                                    <div
                                                        class="pm-yes-no-toggle"
                                                    >
                                                        <button
                                                            class="pm-binary-btn"
                                                            :class="{
                                                                active: equipment.available,
                                                            }"
                                                            type="button"
                                                            @click="
                                                                equipment.available = true
                                                            "
                                                        >
                                                            Yes
                                                        </button>
                                                        <button
                                                            class="pm-binary-btn"
                                                            :class="{
                                                                active: !equipment.available,
                                                            }"
                                                            type="button"
                                                            @click="
                                                                equipment.available = false
                                                            "
                                                        >
                                                            No
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="bg-gray-100">
                                                    <select
                                                        v-model="
                                                            equipment.condition
                                                        "
                                                        class="form-control"
                                                    >
                                                        <option value="Good">
                                                            Good
                                                        </option>
                                                        <option
                                                            value="Needs Attention"
                                                        >
                                                            Needs Attention
                                                        </option>
                                                        <option value="Replace">
                                                            Replace
                                                        </option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input
                                                        v-model="
                                                            equipment.notes
                                                        "
                                                        class="form-control"
                                                    />
                                                </td>
                                                <td class="text-end">
                                                    <button
                                                        class="pm-outline-btn"
                                                        type="button"
                                                        @click="
                                                            removeSafetyEquipment(
                                                                index,
                                                            )
                                                        "
                                                    >
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>

                        <div class="pm-form-actions">
                            <button
                                class="pm-primary-btn"
                                type="button"
                                @click="startCreate"
                            >
                                + New
                            </button>
                            <button
                                class="pm-outline-btn"
                                type="button"
                                :disabled="saving"
                                @click="saveVehicle"
                            >
                                Update
                            </button>
                            <button
                                class="pm-danger-btn"
                                type="button"
                                :disabled="!activeId"
                                @click="deleteCurrent"
                            >
                                Delete
                            </button>
                            <button
                                class="pm-success-btn"
                                type="submit"
                                :disabled="saving"
                            >
                                {{ saving ? "Saving..." : "Save" }}
                            </button>
                            <button
                                class="pm-outline-btn"
                                type="button"
                                @click="printPage"
                            >
                                Save PDF
                            </button>
                            <button
                                class="pm-outline-btn"
                                type="button"
                                @click="printPage"
                            >
                                Print
                            </button>
                            <button
                                class="pm-outline-btn"
                                type="button"
                                @click="emailCurrent"
                            >
                                Email
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
import { clearToken, logout as apiLogout } from "../api/auth";
import vehicleService from "../api/vehicle";
import { authState } from "../store/auth";

const router = useRouter();

const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const topbarSearch = ref("");
const userName = ref("John Doe");

const mode = ref("list");
const loadingList = ref(false);
const saving = ref(false);
const showMoreFilters = ref(false);
const vehicles = ref([]);
const activeId = ref(null);
const currentPage = ref(1);
const perPage = ref(10);

const summary = reactive({
    total_vehicles: 0,
    in_service: 0,
    maintenance: 0,
    out_of_service: 0,
    expiring_documents: 0,
});

const filters = reactive({
    search: "",
    status: "",
    make_brand: "",
    fuel_type: "",
    vehicle_type: "",
    vehicle_year: "",
    in_service: "",
});

const errors = reactive({});

const statusOptions = [
    { value: 1, label: "In Service" },
    { value: 2, label: "Available" },
    { value: 3, label: "Maintenance" },
    { value: 4, label: "Out of Service" },
];

const fuelTypeOptions = ["Gasoline", "Diesel", "Hybrid", "Electric", "CNG"];
const vehicleTypeOptions = ["Sedan", "Truck", "SUV", "Van", "Pickup"];
const makeOptions = [
    "Toyota",
    "Honda",
    "Ford",
    "Chevrolet",
    "Nissan",
    "Mitsubishi",
    "Isuzu",
];
const colorOptions = [
    "Silver",
    "White",
    "Blue",
    "Black",
    "Gray",
    "Pearl White",
    "Graphite",
    "Red",
];
const installmentOptions = Array.from({ length: 12 }, (_, index) => index + 1);
const yearOptions = Array.from({ length: 10 }, (_, index) => 2026 - index);

const form = reactive(createDefaultForm());

const userInitials = computed(() => deriveInitials(userName.value));

const formTitle = computed(() => {
    if (activeId.value) return "Vehicle Management - Update Vehicle";
    return "Vehicle Management - New Vehicle";
});

const availableMakes = computed(() => {
    return [
        ...new Set(
            vehicles.value.map((item) => item.make_brand).filter(Boolean),
        ),
    ].sort();
});

const availableFuelTypes = computed(() => {
    return [
        ...new Set(
            vehicles.value.map((item) => item.fuel_type).filter(Boolean),
        ),
    ].sort();
});

const availableYears = computed(() => {
    return [
        ...new Set(
            vehicles.value.map((item) => item.vehicle_year).filter(Boolean),
        ),
    ].sort((a, b) => b - a);
});

const statCards = computed(() => [
    {
        key: "total",
        label: "Total Vehicles",
        value: summary.total_vehicles,
        className: "is-total",
    },
    {
        key: "service",
        label: "In Service",
        value: summary.in_service,
        className: "is-service",
    },
    {
        key: "maintenance",
        label: "In Maintenance",
        value: summary.maintenance,
        className: "is-available",
    },
    {
        key: "outOfService",
        label: "Out of Service",
        value: summary.out_of_service,
        className: "is-maintenance",
    },
    {
        key: "documents",
        label: "Expiring Documents",
        value: summary.expiring_documents,
        className: "is-documents",
    },
]);

const filteredVehicles = computed(() => {
    const search = String(filters.search || "")
        .trim()
        .toLowerCase();

    return vehicles.value.filter((item) => {
        const matchesSearch =
            !search ||
            [
                item.vehicle_number,
                item.vin,
                item.driver_name,
                item.make_brand,
                item.model,
                item.plate_number,
            ]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(search));

        const matchesStatus =
            !filters.status || String(item.status) === filters.status;
        const matchesMake =
            !filters.make_brand || item.make_brand === filters.make_brand;
        const matchesFuel =
            !filters.fuel_type || item.fuel_type === filters.fuel_type;
        const matchesType =
            !filters.vehicle_type || item.vehicle_type === filters.vehicle_type;
        const matchesYear =
            !filters.vehicle_year ||
            String(item.vehicle_year) === filters.vehicle_year;
        const matchesService =
            filters.in_service === "" ||
            String(Number(!!item.in_service)) === filters.in_service;

        return (
            matchesSearch &&
            matchesStatus &&
            matchesMake &&
            matchesFuel &&
            matchesType &&
            matchesYear &&
            matchesService
        );
    });
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredVehicles.value.length / perPage.value)),
);
const pageStart = computed(() => (currentPage.value - 1) * perPage.value);
const paginatedVehicles = computed(() =>
    filteredVehicles.value.slice(
        pageStart.value,
        pageStart.value + perPage.value,
    ),
);

watch(filteredVehicles, () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value;
    }
});

watch(
    () => [
        filters.search,
        filters.status,
        filters.make_brand,
        filters.fuel_type,
        filters.vehicle_type,
        filters.vehicle_year,
        filters.in_service,
    ],
    () => {
        currentPage.value = 1;
    },
);

watch(
    () => form.driver_profiles,
    (items) => {
        const primaryDriver = items.find(
            (item) => String(item.name || "").trim() !== "",
        );
        form.assigned_driver = primaryDriver?.name || "";
    },
    { deep: true },
);

watch(
    () => form.status,
    (value) => {
        form.in_service = String(value) === "1";
    },
);

watch(
    () => form.in_service,
    (value) => {
        if (value) {
            form.status = "1";
            return;
        }

        if (String(form.status) === "1") {
            form.status = "2";
        }
    },
);

function createDefaultDriver() {
    return {
        name: "",
        phone: "",
        email: "",
        address: "",
        license_number: "",
        license_expiry_date: "",
        license_expired: false,
    };
}

function createDefaultInsuranceDocument() {
    return {
        insurance_company: "",
        policy_number: "",
        policy_start_date: "",
        policy_expiry_date: "",
        expired: false,
        document_name: "",
    };
}

function defaultSafetyEquipments() {
    return [
        {
            equipment: "Dashcam",
            available: true,
            condition: "Good",
            notes: "Front & Rear",
        },
        {
            equipment: "Fire Extinguisher",
            available: true,
            condition: "Good",
            notes: "2.5 kg ABC",
        },
        {
            equipment: "First Aid Kit",
            available: true,
            condition: "Good",
            notes: "Complete",
        },
        {
            equipment: "Reflective Triangles",
            available: true,
            condition: "Good",
            notes: "Set of 3",
        },
        {
            equipment: "GPS Tracker",
            available: true,
            condition: "Good",
            notes: "Installed",
        },
    ];
}

function createDefaultForm() {
    return {
        vehicle_number: "",
        make_brand: "",
        model: "",
        vehicle_type: "",
        vehicle_year: "",
        color: "",
        vin: "",
        fuel_type: "",
        vehicle_keys_tag_no: "",
        plate_number: "",
        plate_expiry_date: "",
        assignment_start_date: "",
        purchase_date: "",
        purchase_price: "",
        purchase_invoice_number: "",
        invoice_date: "",
        sales_tax: "",
        subtotal: "",
        total_paid: "",
        number_of_installments: "",
        first_installment_amount: "",
        first_installment_date: "",
        last_installment_amount: "",
        last_installment_date: "",
        current_mileage: "",
        insurance_provider: "",
        policy_number: "",
        insurance_start_date: "",
        insurance_expiry_date: "",
        insurance_expired: false,
        assigned_driver: "",
        in_service: false,
        seller_name: "",
        seller_company_name: "",
        seller_phone: "",
        seller_email: "",
        seller_website: "",
        car_photos: ["", ""],
        driver_profiles: [createDefaultDriver()],
        insurance_documents: [createDefaultInsuranceDocument()],
        safety_equipments: defaultSafetyEquipments(),
        status: "2",
        last_maintenance_date: "",
        next_maintenance_date: "",
        notes: "",
    };
}

function fillForm(item) {
    Object.assign(form, createDefaultForm(), {
        vehicle_number: item.vehicle_number || "",
        make_brand: item.make_brand || "",
        model: item.model || "",
        vehicle_type: item.vehicle_type || "",
        vehicle_year: item.vehicle_year ? String(item.vehicle_year) : "",
        color: item.color || "",
        vin: item.vin || "",
        fuel_type: item.fuel_type || "",
        vehicle_keys_tag_no: item.vehicle_keys_tag_no || "",
        plate_number: item.plate_number || "",
        plate_expiry_date: item.plate_expiry_date || "",
        assignment_start_date: item.assignment_start_date || "",
        purchase_date: item.purchase_date || "",
        purchase_price: item.purchase_price ?? "",
        purchase_invoice_number: item.purchase_invoice_number || "",
        invoice_date: item.invoice_date || "",
        sales_tax: item.sales_tax ?? "",
        subtotal: item.subtotal ?? "",
        total_paid: item.total_paid ?? "",
        number_of_installments: item.number_of_installments
            ? String(item.number_of_installments)
            : "",
        first_installment_amount: item.first_installment_amount ?? "",
        first_installment_date: item.first_installment_date || "",
        last_installment_amount: item.last_installment_amount ?? "",
        last_installment_date: item.last_installment_date || "",
        current_mileage: item.current_mileage ?? "",
        insurance_provider: item.insurance_provider || "",
        policy_number: item.policy_number || "",
        insurance_start_date: item.insurance_start_date || "",
        insurance_expiry_date: item.insurance_expiry_date || "",
        insurance_expired: !!item.insurance_expired,
        assigned_driver: item.assigned_driver || "",
        in_service: !!item.in_service,
        seller_name: item.seller_name || "",
        seller_company_name: item.seller_company_name || "",
        seller_phone: item.seller_phone || "",
        seller_email: item.seller_email || "",
        seller_website: item.seller_website || "",
        car_photos: normalizePhotoList(item.car_photos),
        driver_profiles:
            Array.isArray(item.driver_profiles) && item.driver_profiles.length
                ? item.driver_profiles.map((driver) => ({
                      ...createDefaultDriver(),
                      ...driver,
                  }))
                : [createDefaultDriver()],
        insurance_documents:
            Array.isArray(item.insurance_documents) &&
            item.insurance_documents.length
                ? item.insurance_documents.map((document) => ({
                      ...createDefaultInsuranceDocument(),
                      ...document,
                  }))
                : [createDefaultInsuranceDocument()],
        safety_equipments:
            Array.isArray(item.safety_equipments) &&
            item.safety_equipments.length
                ? item.safety_equipments.map((equipment) => ({ ...equipment }))
                : defaultSafetyEquipments(),
        status: item.status ? String(item.status) : "2",
        last_maintenance_date: item.last_maintenance_date || "",
        next_maintenance_date: item.next_maintenance_date || "",
        notes: item.notes || "",
    });
}

function normalizePhotoList(items) {
    const photos = Array.isArray(items) ? items.filter(Boolean) : [];
    if (!photos.length) return ["", ""];
    if (photos.length === 1) return [photos[0], ""];
    return photos;
}

function resetForm() {
    clearErrors();
    activeId.value = null;
    Object.assign(form, createDefaultForm());
}

function clearErrors() {
    Object.keys(errors).forEach((key) => delete errors[key]);
}

function assignErrors(error) {
    clearErrors();
    const serverErrors = error?.response?.data?.errors || {};
    Object.entries(serverErrors).forEach(([key, value]) => {
        errors[key] = Array.isArray(value) ? value[0] : value;
    });
}

function normalizeRepeater(items, requiredKey = null) {
    return items
        .map((item) => ({ ...item }))
        .filter((item) => {
            if (requiredKey) {
                return String(item[requiredKey] || "").trim() !== "";
            }

            return Object.values(item).some((value) => {
                if (typeof value === "boolean") return value;
                return String(value || "").trim() !== "";
            });
        });
}

function numberOrNull(value) {
    if (value === "" || value === null || value === undefined) return null;
    const parsed = Number(value);
    return Number.isNaN(parsed) ? null : parsed;
}

function booleanFromMixed(value) {
    return value === true || value === "true" || value === 1 || value === "1";
}

function buildPayload() {
    const drivers = normalizeRepeater(form.driver_profiles, "name").map(
        (item) => ({
            ...item,
            license_expired: booleanFromMixed(item.license_expired),
        }),
    );

    const documents = normalizeRepeater(
        form.insurance_documents,
        "policy_number",
    ).map((item) => ({
        ...item,
        expired: booleanFromMixed(item.expired),
    }));

    const photos = form.car_photos
        .map((item) => String(item || "").trim())
        .filter(Boolean);
    const safetyEquipments = normalizeRepeater(
        form.safety_equipments,
        "equipment",
    ).map((item) => ({
        ...item,
        available: booleanFromMixed(item.available),
    }));

    return {
        vehicle_number: form.vehicle_number,
        make_brand: form.make_brand,
        model: form.model,
        vehicle_type: form.vehicle_type || null,
        vehicle_year: numberOrNull(form.vehicle_year),
        color: form.color || null,
        vin: form.vin || null,
        fuel_type: form.fuel_type || null,
        vehicle_keys_tag_no: form.vehicle_keys_tag_no || null,
        plate_number: form.plate_number || null,
        plate_expiry_date: form.plate_expiry_date || null,
        assignment_start_date: form.assignment_start_date || null,
        purchase_date: form.purchase_date || null,
        purchase_price: numberOrNull(form.purchase_price),
        purchase_invoice_number: form.purchase_invoice_number || null,
        invoice_date: form.invoice_date || null,
        sales_tax: numberOrNull(form.sales_tax),
        subtotal: numberOrNull(form.subtotal),
        total_paid: numberOrNull(form.total_paid),
        number_of_installments: numberOrNull(form.number_of_installments),
        first_installment_amount: numberOrNull(form.first_installment_amount),
        first_installment_date: form.first_installment_date || null,
        last_installment_amount: numberOrNull(form.last_installment_amount),
        last_installment_date:
            form.last_installment_date || form.first_installment_date || null,
        current_mileage: numberOrNull(form.current_mileage),
        insurance_provider: form.insurance_provider || null,
        policy_number: form.policy_number || null,
        insurance_start_date: form.insurance_start_date || null,
        insurance_expiry_date: form.insurance_expiry_date || null,
        insurance_expired: !!form.insurance_expired,
        assigned_driver: drivers[0]?.name || form.assigned_driver || null,
        in_service: !!form.in_service,
        seller_name: form.seller_name || null,
        seller_company_name: form.seller_company_name || null,
        seller_phone: form.seller_phone || null,
        seller_email: form.seller_email || null,
        seller_website: form.seller_website || null,
        car_photos: photos,
        driver_profiles: drivers,
        insurance_documents: documents,
        safety_equipments: safetyEquipments,
        status: Number(form.status || 2),
        last_maintenance_date: form.last_maintenance_date || null,
        next_maintenance_date: form.next_maintenance_date || null,
        notes: form.notes || null,
    };
}

async function fetchVehicles() {
    loadingList.value = true;

    try {
        const params = {
            per_page: 200,
        };

        if (filters.search) params.search = filters.search;
        if (filters.status) params.status = filters.status;
        if (filters.make_brand) params.make_brand = filters.make_brand;
        if (filters.fuel_type) params.fuel_type = filters.fuel_type;
        if (filters.vehicle_type) params.vehicle_type = filters.vehicle_type;
        if (filters.vehicle_year) params.vehicle_year = filters.vehicle_year;
        if (filters.in_service !== "") params.in_service = filters.in_service;

        const { data } = await vehicleService.getItems(params);
        vehicles.value = Array.isArray(data?.data?.data) ? data.data.data : [];

        Object.assign(
            summary,
            data?.summary || {
                total_vehicles: vehicles.value.length,
                in_service: vehicles.value.filter(
                    (item) => Number(item.status) === 1,
                ).length,
                maintenance: vehicles.value.filter(
                    (item) => Number(item.status) === 3,
                ).length,
                out_of_service: vehicles.value.filter(
                    (item) => Number(item.status) === 4,
                ).length,
                expiring_documents: 0,
            },
        );
    } finally {
        loadingList.value = false;
    }
}

function resetFilters() {
    filters.search = "";
    filters.status = "";
    filters.make_brand = "";
    filters.fuel_type = "";
    filters.vehicle_type = "";
    filters.vehicle_year = "";
    filters.in_service = "";
    fetchVehicles();
}

function startCreate() {
    resetForm();
    mode.value = "form";
}

async function editVehicle(id) {
    const { data } = await vehicleService.getItemForEdit(id);
    activeId.value = id;
    fillForm(data?.data || {});
    mode.value = "form";
}

function backToList() {
    mode.value = "list";
    resetForm();
}

async function saveVehicle() {
    saving.value = true;
    clearErrors();

    try {
        const payload = buildPayload();

        if (activeId.value) {
            await vehicleService.updateItem(activeId.value, payload);
        } else {
            const response = await vehicleService.createItem(payload);
            activeId.value = response?.data?.data?.id || null;
        }

        await fetchVehicles();
        mode.value = "list";
        resetForm();
    } catch (error) {
        assignErrors(error);
    } finally {
        saving.value = false;
    }
}

async function deleteCurrent() {
    if (!activeId.value) return;
    if (!window.confirm(`Delete vehicle ${form.vehicle_number}?`)) return;

    await vehicleService.deleteItem(activeId.value);
    await fetchVehicles();
    backToList();
}

function addDriver() {
    form.driver_profiles.push(createDefaultDriver());
}

function removeDriver(index) {
    if (form.driver_profiles.length === 1) {
        form.driver_profiles.splice(index, 1, createDefaultDriver());
        return;
    }

    form.driver_profiles.splice(index, 1);
}

function addInsuranceDocument() {
    form.insurance_documents.push(createDefaultInsuranceDocument());
}

function removeInsuranceDocument(index) {
    if (form.insurance_documents.length === 1) {
        form.insurance_documents.splice(
            index,
            1,
            createDefaultInsuranceDocument(),
        );
        return;
    }

    form.insurance_documents.splice(index, 1);
}

function addPhoto() {
    form.car_photos.push("");
}

function removePhoto(index) {
    if (form.car_photos.length <= 2) {
        form.car_photos.splice(index, 1, "");
        return;
    }

    form.car_photos.splice(index, 1);
}

function addSafetyEquipment() {
    form.safety_equipments.push({
        equipment: "",
        available: true,
        condition: "Good",
        notes: "",
    });
}

function removeSafetyEquipment(index) {
    if (form.safety_equipments.length === 1) return;
    form.safety_equipments.splice(index, 1);
}

function deriveInitials(value) {
    const parts = String(value || "NA")
        .trim()
        .split(/\s+/)
        .filter(Boolean);
    return parts
        .slice(0, 2)
        .map((item) => item.charAt(0).toUpperCase())
        .join("");
}

function formatMileage(value) {
    if (value === null || value === undefined || value === "") return "--";
    return `${Number(value).toLocaleString()} mi`;
}

function vehicleAge(year) {
    if (!year) return "--";
    const years = Math.max(0, new Date().getFullYear() - Number(year));
    return `${years} ${years === 1 ? "Year" : "Years"}`;
}

function statusClass(status) {
    return {
        service: Number(status) === 1,
        available: Number(status) === 2,
        maintenance: Number(status) === 3,
        neutral: Number(status) === 4,
    };
}

function printPage() {
    window.print();
}

function exportList() {
    const headers = [
        "Vehicle No.",
        "Driver Name",
        "Make",
        "Model",
        "Year",
        "Type",
        "VIN",
        "Plate Number",
        "Color",
        "Fuel Type",
        "Status",
        "Odometer",
    ];

    const rows = filteredVehicles.value.map((item) => [
        item.vehicle_number,
        item.driver_name || "",
        item.make_brand || "",
        item.model || "",
        item.vehicle_year || "",
        item.vehicle_type || "",
        item.vin || "",
        item.plate_number || "",
        item.color || "",
        item.fuel_type || "",
        item.status_label || "",
        item.current_mileage || "",
    ]);

    const csvContent = [headers, ...rows]
        .map((row) =>
            row
                .map((cell) => `"${String(cell ?? "").replace(/"/g, '""')}"`)
                .join(","),
        )
        .join("\n");

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "vehicles.csv";
    link.click();
    URL.revokeObjectURL(url);
}

function emailCurrent() {
    const subject = encodeURIComponent(
        `Vehicle Record: ${form.vehicle_number || "New Vehicle"}`,
    );
    const body = encodeURIComponent(
        [
            `Vehicle Number: ${form.vehicle_number || ""}`,
            `Make / Model: ${form.make_brand || ""} ${form.model || ""}`.trim(),
            `Driver: ${form.assigned_driver || ""}`,
            `Status: ${statusOptions.find((item) => String(item.value) === String(form.status))?.label || ""}`,
        ].join("\n"),
    );

    window.location.href = `mailto:?subject=${subject}&body=${body}`;
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
    await fetchVehicles();
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

.pm-vehicle-page {
    width: auto;
    max-width: none;
    box-sizing: border-box;
    padding: 2.15rem 1.2rem 2.5rem;
}

.pm-vehicle-list-top {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
}

.pm-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.8rem;
}

.pm-page-kicker {
    color: #15233d;
    font-size: 2rem;
    font-weight: 800;
}

.pm-page-header h1 {
    margin: 0 0 0.5rem;
    font-size: 1.35rem;
    font-weight: 800;
    color: #121826;
}

.pm-breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    color: #2563eb;
    font-size: 0.94rem;
}

.pm-breadcrumbs span:not(:last-child)::after {
    content: "›";
    margin-left: 0.5rem;
    color: #94a3b8;
}

.pm-header-actions,
.pm-filter-actions,
.pm-form-actions,
.pm-pagination-wrap,
.pm-row-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
}

.pm-header-actions {
    margin-left: auto;
}

.pm-primary-btn,
.pm-outline-btn,
.pm-danger-btn,
.pm-success-btn,
.pm-page-btn,
.pm-binary-btn,
.pm-outline-ghost-btn {
    border-radius: 12px;
    font-weight: 700;
    padding: 0.72rem 1rem;
    border: 1px solid transparent;
    transition: 0.2s ease;
    box-sizing: border-box;
    line-height: 1;
}

.pm-primary-btn,
.pm-outline-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    min-height: 54px;
    padding-top: 0;
    padding-bottom: 0;
    white-space: nowrap;
}

.pm-primary-btn svg,
.pm-outline-btn svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.9;
    flex-shrink: 0;
}

.pm-primary-btn {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-success-btn {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}

.pm-outline-btn,
.pm-page-btn,
.pm-outline-ghost-btn {
    background: #fff;
    border-color: #d8e1ef;
    color: #1f2937;
}

.pm-outline-ghost-btn {
    width: 100%;
    padding: 0.58rem 0.9rem;
    border-style: dashed;
}

.pm-danger-btn {
    background: #fff5f5;
    border-color: #fecaca;
    color: #ef4444;
}

.pm-filter-shell {
    margin-bottom: 1.3rem;
}

.pm-filter-bar,
.pm-table-card,
.pm-section-card {
    background: #fff;
    border: 1px solid #e5ebf5;
    border-radius: 18px;
    box-shadow: 0 16px 38px rgba(15, 23, 42, 0.05);
}

.pm-filter-bar {
    display: grid;
    grid-template-columns:
        minmax(260px, 2.1fr) repeat(3, minmax(135px, 0.72fr))
        auto auto;
    gap: 1rem;
    padding: 1.25rem;
    align-items: center;
}

.pm-filter-bar-extra {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: 0.75rem;
}

.pm-search-field {
    position: relative;
    min-height: 56px;
}

.pm-search-field svg {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    fill: none;
    stroke: #94a3b8;
    stroke-width: 1.8;
    pointer-events: none;
    z-index: 1;
}

.pm-search-field input {
    height: 56px;
    padding-left: 3rem;
    padding-right: 1rem;
    box-sizing: border-box;
    margin: 0;
}

.pm-filter-bar .form-control,
.pm-filter-bar .pm-ghost-link,
.pm-filter-bar .pm-filter-actions > button {
    min-height: 56px;
    height: 56px;
    box-sizing: border-box;
    margin: 0;
}

.pm-filter-bar .form-control {
    border-radius: 20px;
    font-size: 1rem;
    line-height: 1.2;
    display: flex;
    align-items: center;
}

.pm-filter-bar select.form-control {
    height: 56px;
    padding-top: 0;
    padding-bottom: 0;
    padding-right: 2.1rem;
}

.pm-filter-actions {
    margin-top: 0;
    justify-content: flex-end;
    align-items: center;
}

.pm-ghost-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 56px;
    height: 56px;
    padding: 0 0.2rem;
    white-space: nowrap;
    line-height: 1;
}

.pm-ghost-link {
    border: 0;
    background: transparent;
    color: #2563eb;
    font-weight: 700;
    justify-self: start;
    align-self: center;
}

.pm-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1.2rem;
    margin-bottom: 1.35rem;
}

.pm-stat-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 124px;
    padding: 1.35rem 1.4rem;
    border: 1px solid #dbe5f2;
    border-radius: 20px;
    background: #fff;
}

.pm-stat-card.is-total {
    border-color: #d9e5ff;
}

.pm-stat-card.is-service {
    border-color: #c5f0cf;
}

.pm-stat-card.is-available {
    border-color: #ffe0bd;
}

.pm-stat-card.is-maintenance {
    border-color: #ffd0d0;
}

.pm-stat-card.is-documents { border-color: #e9d5ff; }

.pm-stat-copy span {
    display: block;
    margin-bottom: 0.6rem;
    color: #7c889d;
}

.pm-stat-copy strong {
    font-size: 2.05rem;
    line-height: 1;
    color: #121826;
}

.pm-stat-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 53px;
    height: 53px;
    border-radius: 999px;
    flex-shrink: 0;
}

.pm-stat-icon svg {
    width: 21px;
    height: 21px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2.05;
    stroke-linecap: round;
    stroke-linejoin: round;
    overflow: visible;
}

.pm-stat-icon.is-total {
    background: #dbeafe;
    color: #2563eb;
}

.pm-stat-icon.is-service {
    background: #dcfce7;
    color: #16a34a;
}

.pm-stat-icon.is-available {
    background: #ffedd5;
    color: #f97316;
}

.pm-stat-icon.is-maintenance {
    background: #ffe4e6;
    color: #ef4444;
}

.pm-stat-icon.is-documents { background: #f3e8ff; color: #9333ea; }

.pm-table-card {
    overflow: hidden;
    width: 100%;
    min-width: 0;
    max-width: 100%;
}

.pm-table-card .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overscroll-behavior-inline: contain;
}

.pm-vehicle-table {
    margin: 0;
    table-layout: auto;
    min-width: 1550px;
}

.pm-vehicle-table thead th {
    background: #f8fafc;
    color: #1f2937;
    font-size: 0.77rem;
    font-weight: 700;
    white-space: nowrap;
}

.pm-vehicle-table td,
.pm-vehicle-table th {
    padding: 0.8rem 0.58rem;
    vertical-align: middle;
}

.pm-vehicle-table tbody tr {
    height: 68px;
}

.pm-vehicle-table td {
    font-size: 0.78rem;
    white-space: nowrap;
    line-height: 1.2;
}

.pm-vehicle-no-cell {
    font-weight: 700;
    color: #1f2937;
}

.pm-vehicle-photo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 66px;
    height: 42px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #94a3b8;
    flex-shrink: 0;
    overflow: hidden;
}

.pm-vehicle-photo img { width: 100%; height: 100%; object-fit: cover; }

.pm-vehicle-photo svg {
    width: 18px;
    height: 18px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.9;
}

.pm-driver-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    min-height: 32px;
}

.pm-driver-avatar {
    width: 26px;
    height: 26px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #d9e7ff;
    color: #2563eb;
    font-size: 0.72rem;
    font-weight: 800;
}

.pm-driver-chip small { display: block; color: #7c889d; font-size: .68rem; margin-top: .18rem; }

.pm-table-sub {
    color: #8894a7;
    font-size: 0.72rem;
    margin-top: 0.15rem;
    line-height: 1.15;
}

.pm-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.28rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 700;
}

.pm-status-pill.service {
    background: #111827;
    color: #fff;
}

.pm-status-pill.available {
    background: #eef2ff;
    color: #4b5563;
}

.pm-status-pill.maintenance {
    background: #ef4444;
    color: #fff;
}

.pm-status-pill.neutral {
    background: #334155;
    color: #fff;
}

.pm-table-link,
.pm-dots-btn {
    border: 0;
    background: transparent;
    color: #2563eb;
    font-weight: 700;
    font-size: 0.78rem;
}

.pm-view-profile { border: 1px solid #dbe5f4; border-radius: 5px; padding: .38rem .66rem; white-space: nowrap; }

.pm-row-actions {
    min-height: 32px;
    justify-content: flex-end;
}

.pm-dots-btn {
    color: #111827;
    padding: 0.25rem;
}

.pm-dots-btn svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
}

.pm-table-footer {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    padding: 1rem 1.1rem;
    border-top: 1px solid #e5e7eb;
    color: #6b7280;
}

.pm-pagination {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.pm-page-btn {
    min-width: 40px;
}

.pm-page-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-page-nav {
    min-width: 40px;
    font-size: 0;
}

.pm-page-nav::before {
    font-size: 1rem;
    line-height: 1;
}

.pm-page-nav:first-child::before {
    content: "‹";
}

.pm-page-nav:last-child::before {
    content: "›";
}

.pm-per-page {
    width: 124px;
}

.pm-form-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.pm-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.pm-right-stack {
    display: grid;
    gap: 1rem;
}

.pm-section-card {
    padding: 1rem 1.1rem;
}

.pm-section-card-full {
    grid-column: 1 / -1;
}

.pm-card-blue {
    border-color: #cfe0ff;
}

.pm-card-green {
    border-color: #c9f0d6;
}

.pm-card-orange {
    border-color: #ffdcb7;
}

.pm-card-purple {
    border-color: #ead9ff;
}

.pm-section-title,
.pm-section-title-row {
    color: #2563eb;
    font-size: 1.3rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.pm-section-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pm-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.9rem 1rem;
}

.pm-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.pm-field-full {
    grid-column: 1 / -1;
}

.pm-field label {
    color: #1f2937;
    font-weight: 700;
    font-size: 0.9rem;
}

.pm-field label span {
    color: #ef4444;
}

.pm-switch-row {
    justify-content: flex-end;
}

.pm-switch {
    position: relative;
    display: inline-flex;
    align-items: center;
    width: 50px;
    height: 28px;
}

.pm-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.pm-switch span {
    position: absolute;
    inset: 0;
    background: #dbe3ef;
    border-radius: 999px;
}

.pm-switch span::before {
    content: "";
    position: absolute;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #fff;
    top: 3px;
    left: 3px;
    transition: 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.pm-switch input:checked + span {
    background: #2563eb;
}

.pm-switch input:checked + span::before {
    transform: translateX(22px);
}

.pm-error {
    color: #dc2626;
}

.pm-upload-stack,
.pm-repeater-stack {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.pm-photo-upload {
    border: 1px dashed #fdba74;
    border-radius: 12px;
    padding: 0.7rem;
    background: #fffaf4;
}

.pm-photo-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.pm-photo-card {
    position: relative;
    min-height: 64px;
    border-radius: 12px;
    border: 1px solid #e5ebf5;
    background: #f8fafc;
    padding: 0.7rem;
}

.pm-photo-remove {
    position: absolute;
    top: 0.35rem;
    right: 0.45rem;
    border: 0;
    background: transparent;
    color: #ef4444;
    font-size: 1.1rem;
}

.pm-repeater-card {
    border: 1px solid #e5ebf5;
    border-radius: 14px;
    padding: 0.9rem;
    background: #fbfdff;
}

.pm-document-card {
    border-color: #cbf3d7;
}

.pm-repeater-actions,
.pm-upload-doc-row {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 0.8rem;
}

.pm-safety-table {
    margin: 0;
}

.pm-safety-table th,
.pm-safety-table td {
    vertical-align: middle;
    padding: 0.9rem 0.7rem;
}

.pm-yes-no-toggle {
    display: inline-flex;
    gap: 0.3rem;
    background: #f5f7fb;
    padding: 0.2rem;
    border-radius: 999px;
}

.pm-binary-btn {
    padding: 0.28rem 0.7rem;
    border-radius: 999px;
    font-size: 0.78rem;
    background: transparent;
    border: 0;
    color: #64748b;
}

.pm-binary-btn.active {
    background: #16a34a;
    color: #fff;
}

.pm-form-actions {
    gap: 0.65rem;
}

.pm-topbar-page-title {
    min-width: 180px;
    font-size: 1.15rem;
    font-weight: 800;
    color: #1f2937;
}

.pm-page-header {
    margin-bottom: 1.4rem;
}

.pm-page-header h1 {
    margin: 0 0 0.35rem;
    font-size: 1.15rem;
    font-weight: 800;
}

.pm-breadcrumbs {
    gap: 0.35rem;
    color: #2563eb;
    font-size: 0.78rem;
}

.pm-breadcrumbs span:last-child {
    color: #334155;
}

.pm-form-shell {
    gap: 1.15rem;
}

.pm-form-grid {
    gap: 0.95rem;
    align-items: start;
}

.pm-right-stack {
    gap: 0.95rem;
}

.pm-section-card {
    border-radius: 16px;
    padding: 1rem 1.15rem 1.1rem;
    box-shadow: none;
}

.pm-section-title,
.pm-section-title-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    color: #2563eb;
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 0.95rem;
}

.pm-section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.92rem;
    line-height: 1;
}

.pm-grid-2 {
    gap: 0.75rem 0.9rem;
}

.pm-field {
    gap: 0.3rem;
}

.pm-field label {
    font-size: 0.76rem;
    font-weight: 700;
    color: #374151;
}

.pm-field :deep(.form-control) {
    min-height: 32px;
    border-radius: 10px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    color: #0f172a;
    font-size: 0.84rem;
    box-shadow: none;
}

.pm-field :deep(select.form-control) {
    padding-right: 2rem;
}

.pm-field :deep(.form-control:focus) {
    border-color: #c7d7fe;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
}

.pm-switch-row {
    align-items: end;
    justify-content: flex-start;
}

.pm-switch {
    width: 34px;
    height: 20px;
}

.pm-switch span::before {
    width: 14px;
    height: 14px;
    top: 3px;
    left: 3px;
}

.pm-switch input:checked + span::before {
    transform: translateX(14px);
}

.pm-outline-ghost-btn {
    min-height: 32px;
    padding: 0.5rem 0.9rem;
    border-radius: 10px;
    font-size: 0.78rem;
}

.pm-photo-upload {
    padding: 0.65rem;
    border-radius: 12px;
}

.pm-photo-grid {
    gap: 0.55rem;
}

.pm-photo-card,
.pm-repeater-card {
    border-radius: 12px;
    padding: 0.75rem;
    background: #ffffff;
}

.pm-safety-table th {
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 700;
    padding-top: 0.55rem;
    padding-bottom: 0.55rem;
}

.pm-safety-table td {
    font-size: 0.84rem;
    padding-top: 0.42rem;
    padding-bottom: 0.42rem;
}

.pm-safety-table :deep(.form-control) {
    min-height: 28px;
    padding-top: 0.3rem;
    padding-bottom: 0.3rem;
}

.pm-safety-table td:nth-child(3) :deep(.form-control),
.pm-safety-table td:nth-child(4) :deep(.form-control) {
    background: #f1f3f7;
    border-color: #f1f3f7;
}

.pm-safety-table td:nth-child(3) :deep(.form-control:focus),
.pm-safety-table td:nth-child(4) :deep(.form-control:focus) {
    background: #eceff4;
    border-color: #dbe2ea;
}

.pm-safety-table .pm-yes-no-toggle {
    padding: 0.14rem;
    gap: 0.2rem;
}

.pm-safety-table .pm-binary-btn {
    min-height: 24px;
    padding: 0.18rem 0.6rem;
    font-size: 0.74rem;
}

.pm-safety-table .pm-outline-btn {
    min-height: 30px;
    padding: 0 0.9rem;
}

.pm-form-actions {
    gap: 0.55rem;
    align-items: center;
}

.pm-form-actions > button {
    min-height: 36px;
    border-radius: 10px;
    padding: 0 0.95rem;
    font-size: 0.82rem;
}

.pm-topbar-dot {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 20px;
    height: 20px;
    border-radius: 999px;
    background: #ef4444;
    color: #fff;
    font-size: 0.7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 1200px) {
    .pm-filter-bar {
        grid-template-columns: 1fr 1fr;
    }

    .pm-stats-grid,
    .pm-form-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 1500px) {
    .pm-filter-bar {
        grid-template-columns: minmax(240px, 2fr) repeat(3, minmax(125px, 0.8fr)) auto;
    }

    .pm-filter-actions {
        grid-column: 1 / -1;
        justify-content: flex-end;
    }
}

@media (max-width: 991px) {
    .pm-page-header,
    .pm-table-footer,
    .pm-form-actions,
    .pm-section-title-row,
    .pm-pagination-wrap {
        flex-direction: column;
        align-items: stretch;
    }

    .pm-filter-bar,
    .pm-filter-bar-extra,
    .pm-stats-grid,
    .pm-form-grid,
    .pm-grid-2,
    .pm-photo-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1200px) {
    .pm-filter-bar {
        grid-template-columns: 1fr 1fr;
    }

    .pm-filter-actions {
        grid-column: auto;
    }
}

@media (max-width: 991px) {
    .pm-filter-bar,
    .pm-filter-bar-extra,
    .pm-stats-grid,
    .pm-form-grid,
    .pm-grid-2,
    .pm-photo-grid {
        grid-template-columns: 1fr;
    }
}
</style>
