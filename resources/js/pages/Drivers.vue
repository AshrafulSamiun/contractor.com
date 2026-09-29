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
                        v-model="searchQuery"
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

            <div class="container pm-driver-page">
                <header class="pm-page-header">
                    <div class="pm-title-block">
                        <h1>{{ viewMode === "list" ? "Drivers" : activeId ? "Update Driver" : "New Driver" }}</h1>
                        <div class="pm-breadcrumbs">
                            <span>Dashboard</span>
                            <span>Vehicle Management</span>
                            <span>Drivers</span>
                            <span v-if="viewMode === 'form'">{{ activeId ? "Update Driver" : "New Driver" }}</span>
                        </div>
                    </div>

                    <div class="pm-header-actions">
                        <template v-if="viewMode === 'list'">
                            <button class="pm-outline-btn" type="button" @click="exportPdf">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 4v10" />
                                    <path d="m8 10 4 4 4-4" />
                                    <path d="M5 19h14" />
                                </svg>
                                <span>Export</span>
                            </button>
                            <button class="pm-outline-btn" type="button" @click="printCurrent">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M7 8V4h10v4" />
                                    <path d="M6 18H5a2 2 0 0 1-2-2v-5a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v5a2 2 0 0 1-2 2h-1" />
                                    <path d="M7 14h10v6H7z" />
                                </svg>
                                <span>Print</span>
                            </button>
                            <button class="pm-primary-btn" type="button" @click="startCreate">
                                <span>+ Add New Driver</span>
                            </button>
                        </template>

                        <template v-else>
                            <button class="pm-outline-btn" type="button" @click="openListView">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M15 6 9 12l6 6" />
                                </svg>
                                <span>Back to Drivers</span>
                            </button>
                            <button
                                class="pm-primary-btn"
                                type="button"
                                :disabled="saving"
                                @click="saveItem"
                            >
                                <span>{{ saving ? "Saving..." : "Submit" }}</span>
                            </button>
                        </template>
                    </div>
                </header>

                <template v-if="viewMode === 'list'">
                    <div class="pm-driver-list-top">
                    <section class="pm-driver-stats">
                        <article
                            v-for="card in statCards"
                            :key="card.key"
                            class="pm-stat-card"
                            :class="card.className"
                        >
                            <span>{{ card.label }}</span>
                            <strong>{{ card.value }}</strong>
                        </article>
                    </section>

                    <section class="pm-driver-filters">
                        <div class="pm-driver-search-field">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                                />
                            </svg>
                            <input
                                v-model.trim="listSearchQuery"
                                type="search"
                                class="form-control"
                                placeholder="Search drivers..."
                            />
                        </div>

                        <select v-model="filterStatus" class="form-control">
                            <option value="">All Status</option>
                            <option
                                v-for="item in allStatusOptions"
                                :key="item.value"
                                :value="String(item.value)"
                            >
                                {{ item.label }}
                            </option>
                        </select>

                        <select v-model="filterBranch" class="form-control">
                            <option value="">All Branches</option>
                            <option v-for="item in branchOptions" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>

                        <select v-model="filterClass" class="form-control">
                            <option value="">All Classes</option>
                            <option v-for="item in licenseClassOptions" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>

                        <button class="pm-ghost-link" type="button">More Filters</button>

                        <div class="pm-filter-actions">
                            <button class="pm-outline-btn" type="button" @click="resetFilters">
                                Reset
                            </button>
                            <button class="pm-primary-btn" type="button">
                                Apply Filters
                            </button>
                        </div>
                    </section>
                    </div>

                    <section class="pm-driver-table-card">
                        <div class="table-responsive">
                            <table class="table pm-driver-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Photo</th>
                                        <th>Driver Code</th>
                                        <th>Full Name</th>
                                        <th>License Class</th>
                                        <th>Phone Number</th>
                                        <th>Email</th>
                                        <th>Branch</th>
                                        <th>Assigned Vehicle</th>
                                        <th>Status</th>
                                        <th>License Expiry</th>
                                        <th>Renewal Reminders</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in paginatedItems" :key="item.id || index">
                                        <td>{{ rowNumber(index) }}</td>
                                        <td>
                                            <span class="pm-driver-photo">{{ initialsFromName(item.driver_name) }}</span>
                                        </td>
                                        <td>{{ item.driver_code || fallbackDriverCode(index) }}</td>
                                        <td>{{ item.driver_name || "--" }}</td>
                                        <td>{{ item.license_class || "--" }}</td>
                                        <td>{{ item.contact_number || "--" }}</td>
                                        <td>{{ item.email || "--" }}</td>
                                        <td>{{ item.assigned_branch || "Main Branch" }}</td>
                                        <td>{{ item.assigned_vehicle_summary || "Unassigned" }}</td>
                                        <td>
                                            <span class="pm-status-pill" :class="statusClass(item.status)">
                                                {{ driverStatusLabel(item.status) }}
                                            </span>
                                        </td>
                                        <td>{{ formatLongDate(item.license_expiry_date) }}</td>
                                        <td>
                                            <span class="pm-reminder-pill" :class="reminderClass(item)">
                                                {{ reminderLabel(item) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                class="pm-table-link"
                                                type="button"
                                                @click="startEdit(item)"
                                            >
                                                View Profile
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!paginatedItems.length">
                                        <td colspan="13" class="text-center py-4">
                                            No drivers found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pm-driver-list-footer">
                            <span>
                                Showing {{ paginatedItems.length ? rowNumber(0) : 0 }} to
                                {{ paginatedItems.length ? rowNumber(paginatedItems.length - 1) : 0 }}
                                of {{ filteredItems.length }} entries
                            </span>

                            <div class="pm-pagination-wrap">
                                <div class="pm-pagination">
                                    <button
                                        type="button"
                                        :disabled="currentPage === 1"
                                        @click="currentPage -= 1"
                                    >
                                        ‹
                                    </button>
                                    <button
                                        v-for="page in totalPages"
                                        :key="page"
                                        type="button"
                                        :class="{ active: page === currentPage }"
                                        @click="currentPage = page"
                                    >
                                        {{ page }}
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="currentPage >= totalPages"
                                        @click="currentPage += 1"
                                    >
                                        ›
                                    </button>
                                </div>

                                <select class="form-control pm-per-page">
                                    <option>10 per page</option>
                                </select>
                            </div>
                        </div>
                    </section>
                </template>

                <template v-else>
                    <form class="pm-driver-form-shell" @submit.prevent="saveItem">
                        <div class="pm-driver-form-grid pm-driver-form-grid-3">
                            <section class="pm-driver-card pm-card-blue">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">👤</span>
                                    <span>Driver Basic Information</span>
                                </div>

                                <div class="pm-upload-photo-box">
                                    <button class="pm-outline-ghost-btn" type="button">
                                        Upload Photo
                                    </button>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field pm-field-full">
                                        <label>Full Legal Name <span>*</span></label>
                                        <input v-model="form.driver_name" class="form-control" />
                                        <small v-if="errors.driver_name" class="pm-error">{{ errors.driver_name }}</small>
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Employee ID <span>*</span></label>
                                        <input
                                            class="form-control"
                                            :value="form.driver_code || generatedDriverCode"
                                            readonly
                                        />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Date of Birth <span>*</span></label>
                                        <input v-model="form.date_of_birth" type="date" class="form-control" />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Age <span>*</span></label>
                                        <input v-model="form.age" class="form-control" />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Nationality <span>*</span></label>
                                        <select v-model="form.nationality" class="form-control">
                                            <option value="">Select nationality</option>
                                            <option v-for="item in nationalityOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Gender <span>*</span></label>
                                        <div class="pm-inline-choice">
                                            <label><input v-model="form.gender" type="radio" value="Male" /> Male</label>
                                            <label><input v-model="form.gender" type="radio" value="Female" /> Female</label>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-green">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">📞</span>
                                    <span>Contact Information</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label>Primary Phone <span>*</span></label>
                                        <input v-model="form.contact_number" class="form-control" />
                                        <small v-if="errors.contact_number" class="pm-error">{{ errors.contact_number }}</small>
                                    </div>
                                    <div class="pm-field">
                                        <label>Secondary Phone</label>
                                        <input v-model="form.secondary_phone" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Emergency Contact Name <span>*</span></label>
                                        <input v-model="form.emergency_contact_name" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Emergency Contact Number <span>*</span></label>
                                        <input v-model="form.emergency_contact_number" class="form-control" />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Address <span>*</span></label>
                                        <input v-model="form.address" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>City <span>*</span></label>
                                        <input v-model="form.city" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field">
                                        <label>State <span>*</span></label>
                                        <input v-model="form.state" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Zip Code <span>*</span></label>
                                        <input v-model="form.zip_code" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Email <span>*</span></label>
                                        <input v-model="form.email" type="email" class="form-control" placeholder="Enter" />
                                    </div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-orange">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">🛡️</span>
                                    <span>License Expiry & Renewal Reminders</span>
                                </div>

                                <div class="pm-grid-1">
                                    <div class="pm-field">
                                        <label>License Number <span>*</span></label>
                                        <input v-model="form.license_number" class="form-control" />
                                        <small v-if="errors.license_number" class="pm-error">{{ errors.license_number }}</small>
                                    </div>
                                    <div class="pm-field">
                                        <label>License Expiry Date <span>*</span></label>
                                        <input v-model="form.license_expiry_date" type="date" class="form-control" />
                                    </div>
                                    <div class="pm-days-remaining-card">
                                        <strong>{{ daysRemaining }}</strong>
                                        <span>Days Remaining</span>
                                    </div>
                                    <div class="pm-field">
                                        <label>Renewal Reminder <span>*</span></label>
                                        <div class="pm-radio-stack">
                                            <label v-for="item in renewalReminderOptions" :key="item" class="pm-radio-row">
                                                <input v-model="form.renewal_reminder" type="radio" :value="item" />
                                                <span>{{ item }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="pm-field">
                                        <label>Renewal Reminder To <span>*</span></label>
                                        <input v-model="form.renewal_reminder_to" class="form-control" />
                                    </div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-purple">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">💼</span>
                                    <span>Employment Information</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label>Employment Type <span>*</span></label>
                                        <select v-model="form.employment_type" class="form-control">
                                            <option value="">Select type</option>
                                            <option v-for="item in employmentTypeOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Hire Date <span>*</span></label>
                                        <input v-model="form.hire_date" type="date" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Department <span>*</span></label>
                                        <select v-model="form.department" class="form-control">
                                            <option value="">Select department</option>
                                            <option v-for="item in departmentOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Supervisor <span>*</span></label>
                                        <input v-model="form.supervisor" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Assigned Branch <span>*</span></label>
                                        <select v-model="form.assigned_branch" class="form-control">
                                            <option value="">Select branch</option>
                                            <option v-for="item in branchOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Status <span>*</span></label>
                                        <select v-model="form.status" class="form-control">
                                            <option v-for="item in allStatusOptions" :key="item.value" :value="String(item.value)">
                                                {{ item.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-blue">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">🪪</span>
                                    <span>Driver License Information</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field pm-field-full pm-field-caption">
                                        <label>License Identification</label>
                                        <small>View Full Details</small>
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>License Number <span>*</span></label>
                                        <input v-model="form.license_number" class="form-control" />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Issuing Province / State <span>*</span></label>
                                        <select v-model="form.issuing_state" class="form-control">
                                            <option value="">Select state</option>
                                            <option v-for="item in stateOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Driver License Class / Type <span>*</span></label>
                                        <select v-model="form.license_class" class="form-control">
                                            <option value="">Select class</option>
                                            <option v-for="item in licenseClassOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>License Dates <span>*</span></label>
                                        <input v-model="form.license_dates" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Vehicle Type <span>*</span></label>
                                        <select v-model="form.vehicle_type" class="form-control">
                                            <option value="">Select type</option>
                                            <option v-for="item in vehicleTypeOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Issue Date <span>*</span></label>
                                        <input v-model="form.license_issue_date" type="date" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Years of Experience <span>*</span></label>
                                        <input v-model="form.years_of_experience" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>License Status <span>*</span></label>
                                        <select v-model="form.license_status" class="form-control">
                                            <option value="Valid">Valid</option>
                                            <option value="Expired">Expired</option>
                                            <option value="Suspended">Suspended</option>
                                        </select>
                                    </div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-green">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">🎖️</span>
                                    <span>Certifications</span>
                                </div>

                                <div class="pm-cert-list">
                                    <div v-for="item in certificationPresets" :key="item">{{ item }}</div>
                                </div>

                                <div class="pm-field">
                                    <label>Other Certification</label>
                                    <input v-model="form.other_certification" class="form-control" placeholder="Enter certification" />
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-blue">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">♡</span>
                                    <span>Medical & Safety</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label>Blood Type</label>
                                        <select v-model="form.blood_type" class="form-control">
                                            <option value="">Select</option>
                                            <option v-for="item in bloodTypeOptions" :key="item" :value="item">
                                                {{ item }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Allergies</label>
                                        <input v-model="form.allergies" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Medical Conditions</label>
                                        <input v-model="form.medical_conditions" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field">
                                        <label>PPE Issued</label>
                                        <select v-model="form.ppe_issued" class="form-control">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>First Aid Certification</label>
                                        <select v-model="form.first_aid_certification" class="form-control">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Medical Expiry Date</label>
                                        <input v-model="form.medical_expiry_date" type="date" class="form-control" />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>FHI Status</label>
                                        <div class="pm-inline-choice">
                                            <label><input v-model="form.fhi_status" type="radio" value="Yes" /> Yes</label>
                                            <label><input v-model="form.fhi_status" type="radio" value="No" /> No</label>
                                        </div>
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Safety Training Completed?</label>
                                        <div class="pm-inline-choice">
                                            <label><input v-model="form.safety_training_completed" type="radio" value="Yes" /> Yes</label>
                                            <label><input v-model="form.safety_training_completed" type="radio" value="No" /> No</label>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-green">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">🚚</span>
                                    <span>Vehicle Assignment</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label>Assigned Vehicle</label>
                                        <select v-model="form.primary_vehicle_id" class="form-control">
                                            <option value="">Select vehicle</option>
                                            <option v-for="item in vehicleOptions" :key="item.id" :value="String(item.id)">
                                                {{ item.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Assignment Start Date</label>
                                        <input v-model="form.assignment_start_date" type="date" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Truck ID</label>
                                        <input v-model="form.truck_id" class="form-control" />
                                    </div>
                                    <div class="pm-field">
                                        <label>Fuel Card Number</label>
                                        <input v-model="form.fuel_card_number" class="form-control" placeholder="Enter" />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Fuel Card Assigned</label>
                                        <input v-model="form.fuel_card_assigned" class="form-control" />
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>GPS Access</label>
                                        <div class="pm-inline-choice">
                                            <label><input v-model="form.gps_access" type="radio" value="Yes" /> Yes</label>
                                            <label><input v-model="form.gps_access" type="radio" value="No" /> No</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="pm-vehicle-preview">
                                    <div>{{ assignedVehiclePreview }}</div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-orange">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">⚠️</span>
                                    <span>Incidents & Violations</span>
                                </div>

                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label>Previous Incidents</label>
                                        <select v-model="form.previous_incidents" class="form-control">
                                            <option value="None">None</option>
                                            <option value="Minor">Minor</option>
                                            <option value="Major">Major</option>
                                        </select>
                                    </div>
                                    <div class="pm-field">
                                        <label>Traffic Violations</label>
                                        <select v-model="form.traffic_violations" class="form-control">
                                            <option value="None">None</option>
                                            <option value="One">One</option>
                                            <option value="Multiple">Multiple</option>
                                        </select>
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Suspension History</label>
                                        <select v-model="form.suspension_history" class="form-control">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                    <div class="pm-field pm-field-full">
                                        <label>Notes</label>
                                        <textarea v-model="form.notes" class="form-control" rows="4"></textarea>
                                    </div>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-green pm-span-2">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">📤</span>
                                    <span>Documents Upload</span>
                                </div>

                                <div class="pm-document-dropzone">
                                    <div class="pm-document-dropzone-icon">⤴</div>
                                    <strong>Upload Driver Documents</strong>
                                    <span>Drag and drop or click to upload files</span>
                                    <button class="pm-outline-ghost-btn" type="button">
                                        Choose Files
                                    </button>
                                </div>
                            </section>

                            <section class="pm-driver-card pm-card-slate">
                                <div class="pm-section-title">
                                    <span class="pm-section-icon" aria-hidden="true">🛡️</span>
                                    <span>System Tracking</span>
                                </div>

                                <div class="pm-system-grid">
                                    <div>
                                        <span>Created By</span>
                                        <strong>Admin - {{ userName }}</strong>
                                    </div>
                                    <div>
                                        <span>Created Date</span>
                                        <strong>{{ trackingCreatedDate }}</strong>
                                    </div>
                                    <div>
                                        <span>Last Updated</span>
                                        <strong>{{ trackingUpdatedDate }}</strong>
                                    </div>
                                    <div>
                                        <span>Notes</span>
                                        <em>Auto-generated by system</em>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="pm-form-actions">
                            <button class="pm-primary-btn" type="button" @click="startCreate">+ New</button>
                            <button class="pm-outline-btn" type="button" :disabled="saving" @click="saveItem">Update</button>
                            <button class="pm-danger-btn" type="button" :disabled="!activeId" @click="deleteCurrent">Delete</button>
                            <button class="pm-success-btn" type="submit" :disabled="saving">
                                {{ saving ? "Saving..." : "Save" }}
                            </button>
                            <button class="pm-outline-btn" type="button" @click="exportPdf">Save PDF</button>
                            <button class="pm-outline-btn" type="button" @click="printCurrent">Print</button>
                            <button class="pm-outline-btn" type="button" @click="emailCurrent">Email</button>
                        </div>
                    </form>
                </template>
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
import { useRouter } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
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
const activeId = ref(null);
const listSearchQuery = ref("");
const filterStatus = ref("");
const filterBranch = ref("");
const filterClass = ref("");
const currentPage = ref(1);
const pageSize = 8;
const errors = reactive({});

const statusOptions = [
    { value: 1, label: "Active" },
    { value: 2, label: "Inactive" },
    { value: 3, label: "Suspended" },
];
const allStatusOptions = [
    ...statusOptions,
    { value: 4, label: "Terminated" },
];
const licenseClassOptions = ["Class A", "Class B", "Class C", "Class D"];
const employmentTypeOptions = ["Full-Time", "Part-Time", "Contract", "Temporary"];
const nationalityOptions = ["United States", "Canada", "Mexico", "Other"];
const departmentOptions = ["Operations", "Logistics", "Field Team", "Administration"];
const branchOptions = ["Main Branch", "North Branch", "South Branch", "East Branch", "West Branch", "Toronto Branch"];
const stateOptions = ["Console", "California", "Texas", "Florida", "Ontario"];
const vehicleTypeOptions = ["Commercial", "Truck", "Van", "Pickup"];
const bloodTypeOptions = ["O+", "O-", "A+", "A-", "B+", "B-", "AB+", "AB-"];
const certificationPresets = [
    "Air Brake Endorsement",
    "Hazmat Endorsement",
    "First Aid",
    "Defensive Driving",
    "Forklift Certified",
    "Tracking Endorsement",
];
const renewalReminderOptions = [
    "60 Days Before",
    "30 Days Before",
    "15 Days Before",
    "7 Days Before",
];
const vehicleOptions = ref([]);

const form = reactive(createDefaultForm());

const userInitials = computed(() => initialsFromName(userName.value));

const statCards = computed(() => [
    {
        key: "total",
        label: "Total Drivers",
        value: items.value.length,
        className: "is-total",
    },
    {
        key: "active",
        label: "Active Drivers",
        value: items.value.filter((item) => Number(item.status) === 1).length,
        className: "is-active",
    },
    {
        key: "inactive",
        label: "Inactive Drivers",
        value: items.value.filter((item) => Number(item.status) === 2).length,
        className: "is-inactive",
    },
    {
        key: "suspended",
        label: "Suspended",
        value: items.value.filter((item) => Number(item.status) === 3).length,
        className: "is-suspended",
    },
    {
        key: "terminated",
        label: "Terminated",
        value: items.value.filter((item) => Number(item.status) === 4).length,
        className: "is-terminated",
    },
]);

const filteredItems = computed(() => {
    const search = listSearchQuery.value.trim().toLowerCase();

    return items.value.filter((item) => {
        const matchesStatus =
            !filterStatus.value || String(item.status || "") === filterStatus.value;
        const matchesBranch =
            !filterBranch.value ||
            String(item.assigned_branch || "Main Branch") === filterBranch.value;
        const matchesClass =
            !filterClass.value || String(item.license_class || "") === filterClass.value;

        if (!matchesStatus || !matchesBranch || !matchesClass) return false;
        if (!search) return true;

        return [
            item.driver_code,
            item.driver_name,
            item.contact_number,
            item.email,
            item.license_number,
            item.license_class,
            item.assigned_vehicle_summary,
            item.assigned_branch,
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

const generatedDriverCode = computed(
    () => `DRV-${String(items.value.length + 1).padStart(3, "0")}`,
);

const daysRemaining = computed(() => {
    if (!form.license_expiry_date) return 0;
    const today = new Date();
    const expiry = new Date(form.license_expiry_date);
    const diff = Math.ceil((expiry.getTime() - today.getTime()) / 86400000);
    return Number.isFinite(diff) ? Math.max(diff, 0) : 0;
});

const assignedVehiclePreview = computed(() => {
    const selected = vehicleOptions.value.find(
        (item) => String(item.id) === String(form.primary_vehicle_id),
    );
    return selected?.label || "No vehicle selected";
});

const trackingCreatedDate = computed(() =>
    new Date().toLocaleString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
    }),
);
const trackingUpdatedDate = computed(() => trackingCreatedDate.value);

watch([listSearchQuery, filterStatus, filterBranch, filterClass], () => {
    currentPage.value = 1;
});

watch(totalPages, (value) => {
    if (currentPage.value > value) currentPage.value = value;
});

function createDefaultForm() {
    return {
        id: "",
        driver_code: "",
        driver_name: "",
        contact_number: "",
        secondary_phone: "",
        email: "",
        date_of_birth: "",
        age: "",
        nationality: "United States",
        gender: "Male",
        address: "",
        city: "",
        state: "",
        zip_code: "",
        emergency_contact_name: "",
        emergency_contact_number: "",
        license_number: "",
        license_expiry_date: "",
        license_class: "",
        renewal_reminder: "60 Days Before",
        renewal_reminder_to: "",
        assigned_vehicle_ids: [],
        primary_vehicle_id: "",
        assignment_start_date: "",
        hire_date: "",
        employment_type: "Full-Time",
        department: "",
        supervisor: "",
        assigned_branch: "Main Branch",
        hourly_rate: "",
        status: "1",
        issuing_state: "",
        license_dates: "",
        vehicle_type: "Commercial",
        license_issue_date: "",
        years_of_experience: "",
        license_status: "Valid",
        other_certification: "",
        blood_type: "O+",
        allergies: "",
        medical_conditions: "",
        ppe_issued: "Yes",
        first_aid_certification: "Yes",
        medical_expiry_date: "",
        fhi_status: "Yes",
        safety_training_completed: "Yes",
        truck_id: "",
        fuel_card_number: "",
        fuel_card_assigned: "",
        gps_access: "Yes",
        previous_incidents: "None",
        traffic_violations: "None",
        suspension_history: "No",
        notes: "",
    };
}

function clearErrors() {
    Object.keys(errors).forEach((key) => delete errors[key]);
}

function resetForm() {
    clearErrors();
    activeId.value = null;
    Object.assign(form, createDefaultForm(), {
        renewal_reminder_to: userName.value ? `${userName.value.toLowerCase().replace(/\s+/g, ".")}@example.com` : "",
    });
}

function applyItemToForm(item) {
    Object.assign(form, createDefaultForm(), {
        id: item.id || "",
        driver_code: item.driver_code || "",
        driver_name: item.driver_name || "",
        contact_number: item.contact_number || "",
        email: item.email || "",
        date_of_birth: item.date_of_birth || "",
        address: item.address || "",
        emergency_contact_name: item.emergency_contact_name || "",
        emergency_contact_number: item.emergency_contact_number || "",
        license_number: item.license_number || "",
        license_expiry_date: item.license_expiry_date || "",
        license_class: item.license_class || "",
        assigned_vehicle_ids: Array.isArray(item.assigned_vehicle_ids)
            ? item.assigned_vehicle_ids
            : [],
        primary_vehicle_id: item.assigned_vehicle_ids?.[0]
            ? String(item.assigned_vehicle_ids[0])
            : "",
        hire_date: item.hire_date || "",
        employment_type: item.employment_type || "Full-Time",
        hourly_rate:
            item.hourly_rate !== null && item.hourly_rate !== undefined
                ? String(item.hourly_rate)
                : "",
        status: item.status ? String(item.status) : "1",
        notes: item.notes || "",
        renewal_reminder_to: item.email || "",
        assigned_branch: item.assigned_branch || "Main Branch",
        ...(item.profile_data || {}),
    });
}

function buildPayload() {
    const selectedIds = [
        ...new Set(
            [form.primary_vehicle_id, ...form.assigned_vehicle_ids]
                .filter(Boolean)
                .map((id) => Number(id)),
        ),
    ];

    return {
        driver_name: form.driver_name,
        contact_number: form.contact_number,
        email: form.email || null,
        date_of_birth: form.date_of_birth || null,
        address: form.address || null,
        emergency_contact_name: form.emergency_contact_name || null,
        emergency_contact_number: form.emergency_contact_number || null,
        license_number: form.license_number,
        license_expiry_date: form.license_expiry_date || null,
        license_class: form.license_class || null,
        assigned_vehicle_ids: selectedIds,
        hire_date: form.hire_date || null,
        employment_type: form.employment_type || null,
        hourly_rate: form.hourly_rate !== "" ? Number(form.hourly_rate) : null,
        status: Number(form.status || 1),
        notes: form.notes || null,
        profile_data: {
            ...form,
            assigned_vehicle_ids: selectedIds,
        },
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
    const { data } = await driverService.getItems();
    items.value = Array.isArray(data?.data?.data) ? data.data.data : [];
}

async function fetchVehicles() {
    const { data } = await vehicleService.getItems({ per_page: 100 });
    const records = Array.isArray(data?.data?.data) ? data.data.data : [];
    vehicleOptions.value = records.map((item) => ({
        id: item.id,
        label: `${item.vehicle_number || "BG-001"} - ${item.make_brand || ""} ${item.model || ""}`.trim(),
    }));
}

function openListView() {
    viewMode.value = "list";
}

function startCreate() {
    resetForm();
    viewMode.value = "form";
}

async function startEdit(item) {
    const id = item?.id || activeId.value;
    if (!id) return;
    const { data } = await driverService.getItemForEdit(id);
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
        if (activeId.value) {
            await driverService.updateItem(activeId.value, payload);
        } else {
            const response = await driverService.createItem(payload);
            activeId.value = response?.data?.data?.id || null;
        }
        await fetchItems();
        openListView();
        resetForm();
    } catch (error) {
        assignValidationErrors(error);
    } finally {
        saving.value = false;
    }
}

async function deleteCurrent() {
    if (!activeId.value) return;
    if (!window.confirm(`Delete driver ${form.driver_name}?`)) return;
    await driverService.deleteItem(activeId.value);
    await fetchItems();
    openListView();
    resetForm();
}

function resetFilters() {
    listSearchQuery.value = "";
    filterStatus.value = "";
    filterBranch.value = "";
    filterClass.value = "";
}

function formatLongDate(value) {
    if (!value) return "--";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString(undefined, {
        month: "short",
        day: "2-digit",
        year: "numeric",
    });
}

function reminderDays(item) {
    if (!item?.license_expiry_date) return null;
    const today = new Date();
    const expiry = new Date(item.license_expiry_date);
    const diff = Math.ceil((expiry.getTime() - today.getTime()) / 86400000);
    return Number.isFinite(diff) ? diff : null;
}

function reminderLabel(item) {
    const days = reminderDays(item);
    if (days === null) return "N/A";
    return `${Math.max(days, 0)} days`;
}

function reminderClass(item) {
    const days = reminderDays(item);
    if (days === null) return "is-neutral";
    if (days <= 7) return "is-danger";
    if (days <= 30) return "is-warning";
    if (days <= 60) return "is-dark";
    return "is-neutral";
}

function driverStatusLabel(status) {
    return (
        allStatusOptions.find((item) => Number(item.value) === Number(status))?.label ||
        "Unknown"
    );
}

function statusClass(status) {
    return {
        "is-success": Number(status) === 1,
        "is-neutral": Number(status) === 2,
        "is-warning": Number(status) === 3,
        "is-danger": Number(status) === 4,
    };
}

function rowNumber(index) {
    return (currentPage.value - 1) * pageSize + index + 1;
}

function fallbackDriverCode(index) {
    return `DRV-${String(rowNumber(index)).padStart(3, "0")}`;
}

function initialsFromName(value) {
    return String(value || "DR")
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || "")
        .join("");
}

function printCurrent() {
    window.print();
}

function exportPdf() {
    window.print();
}

function emailCurrent() {
    const subject = encodeURIComponent(`Driver ${form.driver_name || ""}`);
    const body = encodeURIComponent(
        [
            `Driver ID: ${form.driver_code || generatedDriverCode.value}`,
            `Driver Name: ${form.driver_name || ""}`,
            `Contact: ${form.contact_number || ""}`,
            `License: ${form.license_number || ""}`,
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
    resetForm();
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

.pm-driver-page {
    width: auto;
    max-width: none;
    box-sizing: border-box;
    padding: 1.25rem 1.2rem 2.5rem;
}

.pm-driver-list-top {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
}

.pm-topbar-page-title {
    min-width: 180px;
    font-size: 1.15rem;
    font-weight: 800;
    color: #1f2937;
}

.pm-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.2rem;
}

.pm-page-header h1 {
    margin: 0 0 0.35rem;
    font-size: 1.15rem;
    font-weight: 800;
    color: #121826;
}

.pm-breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    color: #2563eb;
    font-size: 0.78rem;
}

.pm-breadcrumbs span:last-child {
    color: #334155;
}

.pm-breadcrumbs span:not(:last-child)::after {
    content: "›";
    margin-left: 0.35rem;
    color: #a3b1c7;
}

.pm-header-actions,
.pm-filter-actions,
.pm-form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    align-items: center;
}

.pm-primary-btn,
.pm-outline-btn,
.pm-danger-btn,
.pm-success-btn,
.pm-outline-ghost-btn {
    border-radius: 10px;
    font-weight: 700;
    padding: 0 0.95rem;
    min-height: 36px;
    border: 1px solid transparent;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
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
.pm-outline-ghost-btn {
    background: #fff;
    border-color: #d8e1ef;
    color: #1f2937;
}

.pm-outline-btn svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
}

.pm-outline-ghost-btn {
    width: 100%;
    border-style: dashed;
}

.pm-danger-btn {
    background: #fff5f5;
    border-color: #fecaca;
    color: #ef4444;
}

.pm-driver-stats {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.pm-stat-card {
    background: #fff;
    border: 1px solid #e5ebf5;
    border-radius: 16px;
    padding: 0.8rem 1rem;
    text-align: center;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
}

.pm-stat-card span {
    display: block;
    color: #7c889d;
    font-size: 0.76rem;
    margin-bottom: 0.25rem;
}

.pm-stat-card strong {
    font-size: 1.15rem;
    font-weight: 800;
}

.pm-stat-card.is-total {
    border-color: #cfe0ff;
}

.pm-stat-card.is-total strong {
    color: #2563eb;
}

.pm-stat-card.is-active {
    border-color: #c9f0d6;
}

.pm-stat-card.is-active strong {
    color: #16a34a;
}

.pm-stat-card.is-inactive strong {
    color: #475569;
}

.pm-stat-card.is-suspended {
    border-color: #ffdcb7;
}

.pm-stat-card.is-suspended strong {
    color: #f97316;
}

.pm-stat-card.is-terminated {
    border-color: #ffd0d0;
}

.pm-stat-card.is-terminated strong {
    color: #ef4444;
}

.pm-driver-filters,
.pm-driver-table-card,
.pm-driver-card {
    background: #fff;
    border: 1px solid #e5ebf5;
    border-radius: 16px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
}

.pm-driver-filters {
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) repeat(3, minmax(140px, 0.6fr)) auto auto;
    gap: 0.75rem;
    padding: 0.85rem;
    align-items: center;
    margin-bottom: 0.85rem;
}

.pm-driver-search-field {
    position: relative;
}

.pm-driver-search-field svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    fill: none;
    stroke: #94a3b8;
    stroke-width: 1.8;
}

.pm-driver-search-field input {
    padding-left: 2.4rem;
}

.pm-ghost-link {
    border: 0;
    background: transparent;
    color: #2563eb;
    font-weight: 700;
}

.pm-driver-table-card {
    overflow: hidden;
    width: 100%;
    min-width: 0;
    max-width: 100%;
}

.pm-driver-table-card .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overscroll-behavior-inline: contain;
}

.pm-driver-table {
    margin: 0;
    min-width: 1580px;
}

.pm-driver-table thead th {
    background: #f8fafc;
    color: #111827;
    font-size: 0.74rem;
    font-weight: 700;
    white-space: nowrap;
    padding: 0.8rem 0.5rem;
}

.pm-driver-table td {
    padding: 0.8rem 0.5rem;
    vertical-align: middle;
    font-size: 0.75rem;
    white-space: nowrap;
}

.pm-driver-photo {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: #dbeafe;
    color: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.7rem;
}

.pm-status-pill,
.pm-reminder-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.18rem 0.55rem;
    font-size: 0.68rem;
    font-weight: 700;
}

.pm-status-pill.is-success,
.pm-reminder-pill.is-neutral {
    background: #111827;
    color: #fff;
}

.pm-status-pill.is-neutral {
    background: #e2e8f0;
    color: #475569;
}

.pm-status-pill.is-warning {
    background: #f43f5e;
    color: #fff;
}

.pm-status-pill.is-danger,
.pm-reminder-pill.is-danger {
    background: #ef4444;
    color: #fff;
}

.pm-reminder-pill.is-warning {
    background: #e5e7eb;
    color: #334155;
}

.pm-reminder-pill.is-dark {
    background: #0f172a;
    color: #fff;
}

.pm-table-link {
    border: 0;
    background: transparent;
    color: #2563eb;
    font-weight: 700;
    font-size: 0.74rem;
}

.pm-driver-list-footer {
    padding: 0.85rem 1rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.pm-pagination-wrap,
.pm-pagination {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.pm-pagination button {
    min-width: 30px;
    height: 30px;
    border: 1px solid #d7dbe7;
    border-radius: 8px;
    background: #fff;
}

.pm-pagination button.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-per-page {
    width: 106px;
}

.pm-driver-form-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.pm-driver-form-grid {
    display: grid;
    gap: 0.9rem;
}

.pm-driver-form-grid-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: start;
}

.pm-span-2 {
    grid-column: span 2;
}

.pm-driver-card {
    padding: 1rem 1.1rem;
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

.pm-card-slate {
    border-color: #e5ebf5;
}

.pm-section-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    color: #2563eb;
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 0.9rem;
}

.pm-section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.92rem;
}

.pm-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem 0.9rem;
}

.pm-grid-1 {
    display: grid;
    gap: 0.75rem;
}

.pm-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.pm-field-full {
    grid-column: 1 / -1;
}

.pm-field label {
    font-size: 0.76rem;
    font-weight: 700;
    color: #374151;
}

.pm-field label span {
    color: #ef4444;
}

.pm-field small {
    color: #94a3b8;
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

.pm-field :deep(.form-control:focus) {
    border-color: #c7d7fe;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
}

.pm-error {
    color: #dc2626;
    font-size: 0.76rem;
}

.pm-inline-choice {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.pm-inline-choice label,
.pm-radio-row {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.82rem;
    color: #1f2937;
}

.pm-radio-stack {
    display: grid;
    gap: 0.35rem;
}

.pm-upload-photo-box {
    min-height: 100px;
    border: 1px dashed #bfdbfe;
    border-radius: 12px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.85rem;
}

.pm-days-remaining-card {
    border: 1px solid #ffdcb7;
    border-radius: 12px;
    background: #fff7ed;
    text-align: center;
    padding: 0.9rem;
}

.pm-days-remaining-card strong {
    display: block;
    color: #ea580c;
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
}

.pm-days-remaining-card span {
    color: #ea580c;
    font-size: 0.78rem;
}

.pm-cert-list {
    display: grid;
    gap: 0.35rem;
    margin-bottom: 0.85rem;
    font-size: 0.82rem;
    color: #1f2937;
}

.pm-vehicle-preview {
    margin-top: 0.9rem;
    min-height: 74px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0ea5e9, #1d4ed8);
    color: #fff;
    display: flex;
    align-items: end;
    padding: 0.9rem;
    font-size: 0.84rem;
    font-weight: 700;
}

.pm-document-dropzone {
    min-height: 180px;
    border: 2px dashed #86efac;
    border-radius: 14px;
    background: #f0fdf4;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    text-align: center;
    color: #166534;
}

.pm-document-dropzone-icon {
    font-size: 2rem;
    line-height: 1;
}

.pm-system-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1.25rem;
}

.pm-system-grid span {
    display: block;
    color: #64748b;
    font-size: 0.76rem;
    margin-bottom: 0.25rem;
}

.pm-system-grid strong,
.pm-system-grid em {
    color: #1f2937;
    font-size: 0.84rem;
}

.pm-form-actions {
    gap: 0.55rem;
}

@media (max-width: 1400px) {
    .pm-driver-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .pm-driver-filters {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .pm-driver-form-grid-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991px) {
    .pm-page-header,
    .pm-driver-list-footer,
    .pm-header-actions,
    .pm-filter-actions,
    .pm-form-actions,
    .pm-pagination-wrap {
        flex-direction: column;
        align-items: stretch;
    }

    .pm-driver-stats,
    .pm-driver-filters,
    .pm-driver-form-grid-3,
    .pm-grid-2,
    .pm-system-grid {
        grid-template-columns: 1fr;
    }

    .pm-span-2 {
        grid-column: auto;
    }
}
</style>
