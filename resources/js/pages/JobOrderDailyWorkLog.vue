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
                        placeholder="Search daily work logs..."
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
                    <button class="pm-icon-btn" type="button">
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
                <div class="container pm-daily-log-page">
                    <section v-if="techViewMode==='list'" class="tr-list-page">
                        <header class="tr-list-hero"><div><small>TECHNICIAN REPORT MODULE</small><h1>Technician Reports</h1><p>Dashboard &gt; Job Orders &gt; Technician Report &gt; List</p></div><div><span>List View</span><button @click="newReport">＋ New</button><button class="green">☰ List</button></div></header>
                        <section class="tr-list-metrics"><article><i>▣</i><div><span>Total Reports</span><b>{{ filteredLogs.length }}</b></div></article><article><i class="green-icon">✓</i><div><span>Completed</span><b>{{ completedReports }}</b></div></article><article><i class="sky-icon">◷</i><div><span>In Progress</span><b>{{ inProgressReports }}</b></div></article><article><i class="orange-icon">▧</i><div><span>Job Orders</span><b>{{ jobOrders.length }}</b></div></article></section>
                        <section class="tr-list-card"><div class="tr-list-tools"><div><h2>Technician Report List</h2><p>Showing {{ filteredLogs.length }} of {{ logs.length }} technician reports</p></div><div><input v-model.trim="searchQuery" class="form-control" placeholder="Search by report no, job order, or technician..."/><select v-model="techStatusFilter" class="form-control"><option value="">All Status</option><option>Completed</option><option>In Progress</option><option>Pending</option></select></div></div><div class="table-responsive"><table class="tr-list-table"><thead><tr><th>REPORT NO</th><th>ISSUED DATE</th><th>JOB ORDER</th><th>PROJECT / CUSTOMER</th><th>TECHNICIAN</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody><tr v-for="log in filteredTechnicianLogs" :key="log.id"><td><button @click="openTechnicianReport(log)">{{ log.log_no || ('TR-'+log.id) }}</button></td><td>{{ formatDate(log.period_start || log.created_at) }}</td><td>{{ log.job_order?.job_order_no || '—' }}</td><td><b>{{ log.job_order?.job_description || 'Job Order' }}</b><small>{{ log.job_order?.customer?.company_name || log.project_manager || '—' }}</small></td><td>{{ log.project_manager || 'Technician' }}</td><td><span class="tr-status completed">Completed</span></td><td><button @click="openTechnicianReport(log)">View</button><button @click="openTechnicianReport(log)">Edit</button><button class="danger" @click="deleteLog(log.id)">Delete</button></td></tr><tr v-if="!filteredTechnicianLogs.length"><td colspan="7" class="empty">No technician reports found.</td></tr></tbody></table></div></section>
                    </section>
                    <section v-else-if="technicianView" class="tr-page">
                        <div class="tr-steps"><span v-for="(step,index) in workflowSteps" :key="step" :class="{active:index===2}"><b>{{ index+1 }}</b>{{ step }}</span></div><h1>3. Technician Report</h1><button class="tr-new" @click="techViewMode='list'">☰ List</button>
                        <section class="tr-top"><label>Report No.<input v-model="tech.reportNo" class="form-control"/></label><label>Issued Date<input v-model="tech.date" type="date" class="form-control"/></label><label>Issued Time<input v-model="tech.time" type="time" class="form-control"/></label><label>Job Order No.<select v-model="form.jobOrderId" class="form-control"><option :value="null">Select job order</option><option v-for="job in jobOrders" :key="job.id" :value="job.id">{{ job.job_order_no }}</option></select></label><label>Job Order Title<input :value="selectedJobOrder?.job_description||''" class="form-control" readonly/></label><label>Status<select v-model="tech.status" class="form-control"><option>Completed</option><option>In Progress</option><option>Pending</option></select></label><button @click="convertInvoice">▧ Convert to Sales Invoice</button></section>
                        <div class="tr-grid three"><section class="tr-panel"><h2><b>1</b> Technician Information</h2><div class="tr-fields two"><label>Technician Name<input v-model="tech.name" class="form-control"/></label><label>Technician ID<input v-model="tech.id" class="form-control"/></label><label>Phone<input v-model="tech.phone" class="form-control"/></label><label>Email<input v-model="tech.email" class="form-control"/></label><label>Company<input v-model="tech.company" class="form-control"/></label><label>License No.<input v-model="tech.license" class="form-control"/></label><label>Certification<input v-model="tech.certification" class="form-control"/></label><label>Supervisor / Foreman<input v-model="tech.supervisor" class="form-control"/></label></div></section><section class="tr-panel"><h2><b>2</b> Job / Location Information</h2><div class="tr-fields two"><label class="wide">Property / Customer<input :value="selectedJobOrder?.customer?.company_name||selectedJobOrder?.customer?.name||''" class="form-control" readonly/></label><label class="wide">Building / Location<input :value="selectedJobOrder?.job_site?.name||selectedJobOrder?.job_site?.address||''" class="form-control" readonly/></label><label class="wide">Address<input :value="selectedJobOrder?.job_site?.address||''" class="form-control" readonly/></label><label>Floor<input v-model="tech.floor" class="form-control"/></label><label>Suite / Unit<input v-model="tech.suite" class="form-control"/></label></div></section><section class="tr-panel"><h2><b>3</b> Visit Information</h2><div class="tr-fields two"><label>Date of Visit<input v-model="tech.date" type="date" class="form-control"/></label><label>Vehicle Used<input v-model="tech.vehicle" class="form-control"/></label><label>Arrived (On Site)<input v-model="tech.arrived" type="time" class="form-control"/></label><label>Departed (Left Site)<input v-model="tech.departed" type="time" class="form-control"/></label><label>Access Granted<select v-model="tech.access" class="form-control"><option>Yes</option><option>No</option></select></label><label>If No, Reason<input v-model="tech.accessReason" class="form-control"/></label></div></section></div>
                        <div class="tr-grid three"><section class="tr-panel"><h2><b>4</b> Work Performed / Job Details</h2><div class="tr-fields"><label>Work Performed / Service Provided<textarea v-model="tech.work" class="form-control" rows="3"/></label><label>Work Completed<select v-model="tech.completed" class="form-control"><option>Yes</option><option>No (Incomplete)</option></select></label><label>Additional Work Required<select v-model="tech.additional" class="form-control"><option>No</option><option>Yes</option></select></label><label>If Yes, Describe<textarea v-model="tech.additionalNote" class="form-control" rows="2"/></label></div></section><section class="tr-panel"><h2><b>5</b> Safety / Site Conditions</h2><div class="tr-fields two"><label>Site Safe to Work?<select v-model="tech.safe" class="form-control"><option>Yes</option><option>No</option></select></label><label>PPE Used<input v-model="tech.ppe" class="form-control"/></label><label>Any Hazards Noted?<select v-model="tech.hazards" class="form-control"><option>No</option><option>Yes</option></select></label><label>If Yes, Describe<input v-model="tech.hazardNote" class="form-control"/></label><label>Site Left Clean?<select v-model="tech.clean" class="form-control"><option>Yes</option><option>No</option></select></label><label>Waste Removed?<select v-model="tech.waste" class="form-control"><option>Yes</option><option>No</option></select></label></div></section><section class="tr-panel"><h2><b>6</b> VIP Note / Prevention (Attention to Property)</h2><div class="tr-fields two"><label>Observed Issue / Potential Problem<textarea v-model="tech.issue" class="form-control" rows="3"/></label><label>Location of Issue<input v-model="tech.issueLocation" class="form-control"/></label><label>Risk Level<select v-model="tech.risk" class="form-control"><option>Low</option><option>Medium</option><option>High</option></select></label><label>Recommended Action<textarea v-model="tech.action" class="form-control" rows="3"/></label><label>Reported To<input v-model="tech.reportedTo" class="form-control"/></label><label>Date Reported<input v-model="tech.date" type="date" class="form-control"/></label></div></section></div>
                        <div class="tr-grid bottom"><section class="tr-panel"><h2><b>7</b> Technician Notes</h2><label>Additional Notes / Comments<textarea v-model="form.notes" class="form-control" rows="4"/></label></section><section class="tr-panel"><h2><b>8</b> Technician Confirmation</h2><p class="tr-confirm">☑ I hereby confirm that the above information is true and accurate to the best of my knowledge.</p><div class="tr-fields three"><label>Technician Signature<input v-model="tech.name" class="form-control"/></label><label>Date<input v-model="tech.date" type="date" class="form-control"/></label><label>Time<input v-model="tech.time" type="time" class="form-control"/></label></div></section></div>
                        <footer class="tr-actions"><button @click="newReport">＋ New</button><button>✎ Edit</button><button>♲ Delete</button><button @click="saveLog">▣ Save</button><button @click="saveLog">▣ Save &amp; Stay</button><button @click="printReport">◉ Preview</button><button @click="printReport">▣ Print</button><button>✉ Email</button><button @click="saveLog">⇥ Save &amp; Out</button></footer>
                    </section>
                    <template v-else>
                    <section class="pm-daily-log-hero">
                        <div class="pm-daily-log-hero-copy">
                            <h2>Job Order Daily Work Log</h2>
                            <p>
                                Track daily activities and monitor project costs
                            </p>
                            <div class="pm-daily-log-hero-meta">
                                <label class="pm-daily-log-inline-field">
                                    <span>Job Order Number:</span>
                                    <select
                                        v-model="form.jobOrderId"
                                        class="form-control"
                                        @change="handleJobOrderChange"
                                    >
                                        <option :value="null">
                                            Select job order
                                        </option>
                                        <option
                                            v-for="item in jobOrders"
                                            :key="item.id"
                                            :value="item.id"
                                        >
                                            {{ item.job_order_no }}
                                        </option>
                                    </select>
                                </label>
                                <div class="pm-daily-log-inline-range">
                                    {{ formatDate(form.periodStart) }} -
                                    {{ formatDate(form.periodEnd) }}
                                </div>
                            </div>
                        </div>
                        <div class="pm-daily-log-hero-actions">
                            <button
                                class="pm-daily-log-action-btn"
                                type="button"
                                @click="notifyAction('Save')"
                            >
                                Save
                            </button>
                            <button
                                class="pm-daily-log-action-btn"
                                type="button"
                                @click="notifyAction('Print')"
                            >
                                Print
                            </button>
                            <button
                                class="pm-daily-log-action-btn"
                                type="button"
                                @click="notifyAction('Export')"
                            >
                                Export
                            </button>
                        </div>
                    </section>

                    <section class="pm-daily-log-list-card">
                        <div class="pm-daily-log-section-head">
                            Saved Daily Work Logs
                        </div>
                        <div class="pm-daily-log-section-body">
                            <div class="table-responsive">
                                <table class="table pm-daily-log-list-table">
                                    <thead>
                                        <tr>
                                            <th>Log No</th>
                                            <th>Job Order</th>
                                            <th>Project</th>
                                            <th>Period</th>
                                            <th>Total Cost</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredLogs.length">
                                        <tr
                                            v-for="item in filteredLogs"
                                            :key="item.id"
                                        >
                                            <td>{{ item.log_no }}</td>
                                            <td>
                                                {{
                                                    item.job_order
                                                        ?.job_order_no || "--"
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    item.job_order
                                                        ?.job_description ||
                                                    "--"
                                                }}
                                            </td>
                                            <td>
                                                {{ formatDate(item.period_start) }}
                                                -
                                                {{ formatDate(item.period_end) }}
                                            </td>
                                            <td>
                                                CAD
                                                {{
                                                    formatMoney(
                                                        item.total_cost,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <div
                                                    class="pm-daily-log-list-actions"
                                                >
                                                    <button
                                                        class="btn btn-outline-primary btn-sm"
                                                        type="button"
                                                        @click="loadLog(item.id)"
                                                    >
                                                        Open
                                                    </button>
                                                    <button
                                                        class="btn btn-outline-danger btn-sm"
                                                        type="button"
                                                        @click="deleteLog(item.id)"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No daily work logs found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-daily-log-card">
                        <div class="pm-daily-log-section-head">
                            Project Summary
                        </div>
                        <div class="pm-daily-log-section-body">
                            <div class="pm-daily-log-summary-grid">
                                <div class="pm-daily-log-summary-item">
                                    <span>Project Name</span>
                                    <strong>{{
                                        selectedJobOrder
                                            ?.job_description || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Job Order Budget ($)</span>
                                    <strong class="is-danger"
                                        >{{
                                            formatMoney(
                                                selectedJobOrder?.budget,
                                            )
                                        }}</strong
                                    >
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Balance</span>
                                    <strong class="is-success"
                                        >{{
                                            formatMoney(balanceAmount)
                                        }}</strong
                                    >
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Remaining Days - Hours</span>
                                    <strong class="is-warning">{{
                                        remainingDurationLabel
                                    }}</strong>
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Customer Name</span>
                                    <strong>{{
                                        selectedJobOrder
                                            ?.customer_name || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Total Cost</span>
                                    <strong class="is-link"
                                        >{{
                                            formatMoney(totalCost)
                                        }}</strong
                                    >
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Start Date</span>
                                    <strong>{{
                                        formatDate(
                                            selectedJobOrder?.start_date,
                                        )
                                    }}</strong>
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Job Site Address</span>
                                    <strong>{{
                                        selectedJobOrder
                                            ?.job_site_address || "--"
                                    }}</strong>
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>End Date</span>
                                    <strong>{{
                                        formatDate(
                                            selectedJobOrder?.end_date,
                                        )
                                    }}</strong>
                                </div>
                                <div class="pm-daily-log-summary-item">
                                    <span>Project Manager</span>
                                    <input
                                        v-model="form.projectManager"
                                        class="form-control"
                                        type="text"
                                    />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pm-daily-log-card">
                        <div
                            class="pm-daily-log-section-head pm-daily-log-section-head--between"
                        >
                            <span>Daily Activity & Cost Tracking</span>
                            <button
                                class="pm-daily-log-action-btn"
                                type="button"
                                @click="addRow"
                            >
                                Add Row
                            </button>
                        </div>
                        <div class="pm-daily-log-section-body">
                            <div class="table-responsive">
                                <table class="table pm-daily-log-entry-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Work Description</th>
                                            <th>Wages</th>
                                            <th>Materials</th>
                                            <th>Overhead</th>
                                            <th>Daily Cost</th>
                                            <th>Running Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(row, index) in entries"
                                            :key="row.id"
                                        >
                                            <td>
                                                <input
                                                    v-model="row.workDate"
                                                    class="form-control form-control-sm"
                                                    type="date"
                                                    @change="
                                                        syncRowDay(row, index)
                                                    "
                                                />
                                            </td>
                                            <td>{{ row.dayName || "--" }}</td>
                                            <td>
                                                <div
                                                    class="pm-daily-log-time-wrap"
                                                >
                                                    <input
                                                        v-model="row.startTime"
                                                        class="form-control form-control-sm"
                                                        type="time"
                                                    />
                                                    <span>-</span>
                                                    <input
                                                        v-model="row.endTime"
                                                        class="form-control form-control-sm"
                                                        type="time"
                                                    />
                                                </div>
                                            </td>
                                            <td>
                                                <input
                                                    v-model="
                                                        row.activityDescription
                                                    "
                                                    class="form-control form-control-sm"
                                                    type="text"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="row.wages"
                                                    class="form-control form-control-sm"
                                                    min="0"
                                                    step="0.01"
                                                    type="number"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="
                                                        row.materials
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
                                                        row.overhead
                                                    "
                                                    class="form-control form-control-sm"
                                                    min="0"
                                                    step="0.01"
                                                    type="number"
                                                />
                                            </td>
                                            <td>
                                                {{
                                                    formatMoney(
                                                        calcRowTotal(row),
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatMoney(
                                                        runningTotalAt(index),
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <button
                                                    class="pm-daily-log-delete-row"
                                                    type="button"
                                                    @click="removeRow(row.id)"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="pm-daily-log-card">
                        <div
                            class="pm-daily-log-section-head pm-daily-log-section-head--success"
                        >
                            Project Budget & Cost Summary
                        </div>
                        <div class="pm-daily-log-section-body">
                            <div class="pm-daily-log-budget-summary">
                                <div class="pm-daily-log-budget-row">
                                    <span>Total Wages</span>
                                    <strong
                                        >CAD
                                        {{
                                            formatMoney(totalWages)
                                        }}</strong
                                    >
                                </div>
                                <div class="pm-daily-log-budget-row">
                                    <span>Total Materials</span>
                                    <strong
                                        >CAD
                                        {{
                                            formatMoney(totalMaterials)
                                        }}</strong
                                    >
                                </div>
                                <div class="pm-daily-log-budget-row">
                                    <span>Total Overhead</span>
                                    <strong
                                        >CAD
                                        {{
                                            formatMoney(totalOverhead)
                                        }}</strong
                                    >
                                </div>
                                <div
                                    class="pm-daily-log-budget-row pm-daily-log-budget-row--highlight"
                                >
                                    <span>Total Daily Costs</span>
                                    <strong
                                        >CAD
                                        {{
                                            formatMoney(totalCost)
                                        }}</strong
                                    >
                                </div>
                                <div class="pm-daily-log-budget-row">
                                    <span>Job Order Amount</span>
                                    <strong
                                        >CAD
                                        {{
                                            formatMoney(
                                                selectedJobOrder?.budget,
                                            )
                                        }}</strong
                                    >
                                </div>
                            </div>
                            <div class="pm-daily-log-footer-actions">
                                <button
                                    class="pm-daily-log-action-btn"
                                    type="button"
                                    @click="addRow"
                                >
                                    Add Row
                                </button>
                                <button
                                    class="pm-daily-log-action-btn pm-daily-log-action-btn--primary"
                                    type="button"
                                    @click="notifyAction(form.id ? 'Update' : 'Save')"
                                >
                                    {{ form.id ? "Update" : "Save" }}
                                </button>
                                <button
                                    class="pm-daily-log-action-btn"
                                    type="button"
                                    @click="notifyAction('Print')"
                                >
                                    Print
                                </button>
                                <button
                                    class="pm-daily-log-action-btn"
                                    type="button"
                                    @click="notifyAction('Export')"
                                >
                                    Export to Excel
                                </button>
                                <button
                                    class="pm-daily-log-action-btn pm-daily-log-action-btn--danger"
                                    :disabled="!form.id"
                                    type="button"
                                    @click="notifyAction('Delete')"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
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
const technicianView = ref(true);
const techViewMode = ref("list");
const techStatusFilter = ref("");
const workflowSteps = ["Estimate / Quote", "Job Orders", "Technician Report", "Sales Invoice", "Pay Bills / Close", "Job Tracker"];
const userName = ref("John Doe");
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const logs = ref([]);
const jobOrders = ref([]);
let rowId = 1;

const form = reactive({
    id: null,
    jobOrderId: null,
    periodStart: formatDateInput(new Date()),
    periodEnd: formatDateInput(addDays(new Date(), 29)),
    projectManager: "",
    notes: "",
});
const tech = reactive({reportNo:"TR-"+new Date().getFullYear()+"-0001",date:formatDateInput(new Date()),time:"14:45",status:"Completed",name:"",id:"",phone:"",email:"",company:"",license:"",certification:"",supervisor:"",floor:"",suite:"",vehicle:"",arrived:"09:00",departed:"13:15",access:"Yes",accessReason:"",work:"",completed:"Yes",additional:"No",additionalNote:"",safe:"Yes",ppe:"",hazards:"No",hazardNote:"",clean:"Yes",waste:"Yes",issue:"",issueLocation:"",risk:"Medium",action:"",reportedTo:""})

const entries = ref([]);

const userInitials = computed(() => {
    const parts = String(userName.value || "U")
        .trim()
        .split(/\s+/);
    return (parts[0]?.[0] || "U").concat(parts[1]?.[0] || "").toUpperCase();
});

const filteredLogs = computed(() => {
    const keyword = searchQuery.value.trim().toLowerCase();
    return logs.value.filter((item) => {
        if (!keyword) return true;
        return [
            item.log_no,
            item.job_order?.job_order_no,
            item.job_order?.job_description,
        ]
            .filter(Boolean)
            .some((value) =>
                String(value).toLowerCase().includes(keyword),
            );
    });
});
const filteredTechnicianLogs = computed(() =>
    filteredLogs.value.filter((log) => !techStatusFilter.value || techStatusFilter.value === "Completed"),
);
const completedReports = computed(() => filteredTechnicianLogs.value.length);
const inProgressReports = computed(() => 0);

const selectedJobOrder = computed(() =>
    jobOrders.value.find((item) => item.id === form.jobOrderId) || null,
);

const totalWages = computed(() =>
    entries.value.reduce((sum, row) => sum + toNumber(row.wages), 0),
);
const totalMaterials = computed(() =>
    entries.value.reduce((sum, row) => sum + toNumber(row.materials), 0),
);
const totalOverhead = computed(() =>
    entries.value.reduce((sum, row) => sum + toNumber(row.overhead), 0),
);
const totalCost = computed(
    () => totalWages.value + totalMaterials.value + totalOverhead.value,
);
const balanceAmount = computed(() =>
    Math.max(toNumber(selectedJobOrder.value?.budget) - totalCost.value, 0),
);
const remainingDurationLabel = computed(() => {
    if (!selectedJobOrder.value?.end_date) return "--";
    const end = new Date(selectedJobOrder.value.end_date);
    const now = new Date();
    const diff = end.getTime() - now.getTime();
    if (diff <= 0) return "Expired";
    const totalHours = Math.floor(diff / 3600000);
    const days = Math.floor(totalHours / 24);
    const hours = totalHours % 24;
    return `${days} Days ${hours} Hours`;
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
    () => [form.periodStart, form.periodEnd],
    () => {
        if (!form.id && entries.value.length === 0) {
            seedRowsFromPeriod();
        }
    },
);

onMounted(async () => {
    await Promise.all([loadLogs(), loadJobOrders()]);
    form.jobOrderId = form.jobOrderId || jobOrders.value[0]?.id || null;
    if (route.params.id) {
        await loadLog(route.params.id);
    } else {
        resetEntries();
    }
});

async function loadLogs() {
    try {
        const { data } = await client.get("/job-order-daily-work-logs", {
            params: { per_page: 100 },
        });
        logs.value = data?.data?.data || [];
    } catch (error) {
        console.error("Failed to load daily work logs", error);
        setFlash("Failed to load daily work logs.", "warning", 3000);
    }
}

async function loadJobOrders() {
    try {
        const { data } = await client.get(
            "/job-order-daily-work-logs/job-orders",
        );
        jobOrders.value = data?.data || [];
    } catch (error) {
        console.error("Failed to load job orders for work log", error);
        setFlash("Failed to load job orders.", "warning", 3000);
    }
}

async function loadLog(id) {
    try {
        const { data } = await client.get(`/job-order-daily-work-logs/${id}`);
        const log = data?.data;
        if (!log) return;

        form.id = log.id;
        form.jobOrderId = log.job_order_id;
        form.periodStart = log.period_start;
        form.periodEnd = log.period_end;
        form.projectManager =
            log.project_manager ||
            log.job_order?.project_manager ||
            "";
        form.notes = log.notes || "";

        entries.value = (log.entries || []).map((entry) => ({
            id: rowId++,
            workDate: entry.work_date,
            dayName: entry.day_name,
            startTime: entry.start_time || "",
            endTime: entry.end_time || "",
            activityDescription: entry.activity_description || "",
            wages: entry.wages || 0,
            materials: entry.materials || 0,
            overhead: entry.overhead || 0,
        }));

        await router.replace({
            path: `/job-orders/daily-work-log/${log.id}`,
        });
    } catch (error) {
        console.error("Failed to load daily work log", error);
        setFlash("Failed to load daily work log.", "warning", 3000);
    }
}

function handleJobOrderChange() {
    const selected = selectedJobOrder.value;
    if (!selected) return;

    form.projectManager = selected.project_manager || "";

    if (selected.start_date) {
        form.periodStart = formatDateInput(selected.start_date);
    }
    if (selected.end_date) {
        form.periodEnd = formatDateInput(selected.end_date);
    }

    if (!form.id) {
        seedRowsFromPeriod();
    }
}

function seedRowsFromPeriod() {
    if (!form.periodStart || !form.periodEnd) {
        resetEntries();
        return;
    }

    const start = new Date(form.periodStart);
    const end = new Date(form.periodEnd);
    if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
        resetEntries();
        return;
    }

    const rows = [];
    const limit = 30;
    let cursor = new Date(start);
    let count = 0;

    while (cursor <= end && count < limit) {
        rows.push({
            id: rowId++,
            workDate: formatDateInput(cursor),
            dayName: cursor.toLocaleDateString("en-US", {
                weekday: "short",
            }),
            startTime: "",
            endTime: "",
            activityDescription: "",
            wages: 0,
            materials: 0,
            overhead: 0,
        });
        cursor.setDate(cursor.getDate() + 1);
        count += 1;
    }

    entries.value = rows.length ? rows : [blankRow()];
}

function blankRow() {
    return {
        id: rowId++,
        workDate: formatDateInput(new Date()),
        dayName: new Date().toLocaleDateString("en-US", { weekday: "short" }),
        startTime: "",
        endTime: "",
        activityDescription: "",
        wages: 0,
        materials: 0,
        overhead: 0,
    };
}

function resetEntries() {
    entries.value = [blankRow()];
}

function addRow() {
    entries.value.push(blankRow());
}

function removeRow(id) {
    if (entries.value.length === 1) {
        setFlash("At least one row is required.", "warning", 2200);
        return;
    }
    entries.value = entries.value.filter((row) => row.id !== id);
}

function syncRowDay(row) {
    row.dayName = row.workDate
        ? new Date(row.workDate).toLocaleDateString("en-US", {
              weekday: "short",
          })
        : "";
}

function calcRowTotal(row) {
    return (
        toNumber(row.wages) +
        toNumber(row.materials) +
        toNumber(row.overhead)
    );
}

function runningTotalAt(index) {
    return entries.value
        .slice(0, index + 1)
        .reduce((sum, row) => sum + calcRowTotal(row), 0);
}

function buildPayload() {
    return {
        job_order_id: form.jobOrderId,
        period_start: form.periodStart,
        period_end: form.periodEnd,
        project_manager: form.projectManager,
        notes: form.notes,
        entries: entries.value.map((row) => ({
            work_date: row.workDate,
            start_time: row.startTime || null,
            end_time: row.endTime || null,
            activity_description: row.activityDescription,
            wages: row.wages || 0,
            materials: row.materials || 0,
            overhead: row.overhead || 0,
        })),
    };
}

async function saveLog() {
    try {
        const { data } = await client.post(
            "/job-order-daily-work-logs",
            buildPayload(),
        );
        if (data?.data?.id) {
            setFlash("Daily work log saved successfully.", "success", 2200);
            await loadLogs();
            await loadLog(data.data.id);
        }
    } catch (error) {
        console.error("Failed to save daily work log", error);
        setFlash("Failed to save daily work log.", "warning", 3000);
    }
}

async function updateLog() {
    if (!form.id) {
        setFlash("No daily work log selected for update.", "warning", 2200);
        return;
    }

    try {
        await client.put(
            `/job-order-daily-work-logs/${form.id}`,
            buildPayload(),
        );
        setFlash("Daily work log updated successfully.", "success", 2200);
        await loadLogs();
        await loadLog(form.id);
    } catch (error) {
        console.error("Failed to update daily work log", error);
        setFlash("Failed to update daily work log.", "warning", 3000);
    }
}

async function deleteLog(id = form.id) {
    if (!id) {
        setFlash("No daily work log selected for delete.", "warning", 2200);
        return;
    }

    const confirmed = confirm(
        "Are you sure you want to delete this daily work log?",
    );
    if (!confirmed) return;

    try {
        await client.delete(`/job-order-daily-work-logs/${id}`);
        setFlash("Daily work log deleted successfully.", "success", 2200);
        await loadLogs();
        resetForm();
        await router.replace("/job-orders/daily-work-log");
    } catch (error) {
        console.error("Failed to delete daily work log", error);
        setFlash("Failed to delete daily work log.", "warning", 3000);
    }
}

function notifyAction(action) {
    if (action === "Save") {
        if (form.id) {
            updateLog();
        } else {
            saveLog();
        }
    } else if (action === "Update") {
        updateLog();
    } else if (action === "Delete") {
        deleteLog();
    } else {
        setFlash(`${action} is ready for daily work logs.`, "info", 1800);
    }
}

function resetForm() {
    form.id = null;
    form.jobOrderId = null;
    form.periodStart = formatDateInput(new Date());
    form.periodEnd = formatDateInput(addDays(new Date(), 29));
    form.projectManager = "";
    form.notes = "";
    resetEntries();
}
function newReport(){resetForm();form.jobOrderId=jobOrders.value[0]?.id||null;tech.reportNo="TR-"+new Date().getFullYear()+"-"+String(Date.now()).slice(-4);tech.date=formatDateInput(new Date());techViewMode.value="form";}
async function openTechnicianReport(log){await loadLog(log.id);tech.reportNo=log.log_no||("TR-"+log.id);tech.date=formatDateInput(log.period_start||new Date());tech.name=log.project_manager||"";techViewMode.value="form";}
function convertInvoice(){window.alert("Technician report is ready to convert to a sales invoice.");}
function printReport(){window.print();}

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

function addDays(date, days) {
    const next = new Date(date);
    next.setDate(next.getDate() + days);
    return next;
}

function formatDateInput(date) {
    if (!date) return "";
    const copy = new Date(date);
    copy.setMinutes(copy.getMinutes() - copy.getTimezoneOffset());
    return copy.toISOString().slice(0, 10);
}

function formatDate(value) {
    if (!value) return "--";
    return new Date(value).toLocaleDateString();
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
.pm-daily-log-page {
    padding-bottom: 48px;
}

.pm-daily-log-hero,
.pm-daily-log-card,
.pm-daily-log-list-card {
    overflow: hidden;
    margin-bottom: 22px;
    border: 1px solid #dce7f4;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.07);
}

.pm-daily-log-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 28px 22px;
    background: linear-gradient(90deg, #2563eb, #60a5fa);
    color: #fff;
}

.pm-daily-log-hero h2 {
    margin: 0 0 8px;
    font-size: 2rem;
    font-weight: 800;
}

.pm-daily-log-hero p {
    margin: 0 0 16px;
    color: rgba(255, 255, 255, 0.88);
}

.pm-daily-log-hero-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
}

.pm-daily-log-inline-field {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    color: #fff;
    font-weight: 600;
}

.pm-daily-log-inline-field .form-control {
    min-width: 220px;
    border-color: rgba(255, 255, 255, 0.22);
    background: rgba(255, 255, 255, 0.16);
    color: #fff;
}

.pm-daily-log-inline-field .form-control option {
    color: #10243f;
}

.pm-daily-log-inline-range {
    color: #eef6ff;
    font-weight: 600;
}

.pm-daily-log-hero-actions,
.pm-daily-log-footer-actions,
.pm-daily-log-list-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.pm-daily-log-action-btn {
    border: 1px solid #d6e0ef;
    border-radius: 10px;
    background: #fff;
    color: #1c3656;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 9px 14px;
}

.pm-daily-log-action-btn--primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-daily-log-action-btn--danger {
    color: #dc2626;
}

.pm-daily-log-section-head {
    padding: 14px 22px;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
}

.pm-daily-log-section-head--between {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pm-daily-log-section-head--success {
    background: linear-gradient(90deg, #16a34a, #22c55e);
}

.pm-daily-log-section-body {
    padding: 20px 22px;
}

.pm-daily-log-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px 18px;
}

.pm-daily-log-summary-item {
    display: grid;
    gap: 8px;
}

.pm-daily-log-summary-item span {
    color: #4d6079;
    font-size: 0.92rem;
}

.pm-daily-log-summary-item strong {
    min-height: 36px;
    padding: 9px 12px;
    border-radius: 10px;
    background: #f6f8fc;
    color: #182f4b;
    font-size: 0.98rem;
}

.pm-daily-log-summary-item strong.is-danger {
    color: #ef4444;
}

.pm-daily-log-summary-item strong.is-success {
    color: #22c55e;
}

.pm-daily-log-summary-item strong.is-warning {
    color: #fb923c;
}

.pm-daily-log-summary-item strong.is-link {
    color: #60a5fa;
}

.pm-daily-log-entry-table th,
.pm-daily-log-list-table th {
    white-space: nowrap;
    font-size: 0.82rem;
    color: #4d607d;
}

.pm-daily-log-time-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pm-daily-log-time-wrap .form-control {
    min-width: 102px;
}

.pm-daily-log-delete-row {
    border: none;
    background: transparent;
    color: #dc2626;
    font-size: 0.85rem;
}

.pm-daily-log-budget-summary {
    width: min(100%, 760px);
    border-radius: 16px;
    border: 1px solid #d6e0ef;
    overflow: hidden;
}

.pm-daily-log-budget-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid #e5edf7;
    background: #fff;
    color: #20354f;
    font-size: 1rem;
}

.pm-daily-log-budget-row:last-child {
    border-bottom: none;
}

.pm-daily-log-budget-row--highlight {
    background: #eef5ff;
    color: #2563eb;
    font-weight: 700;
}

@media (max-width: 1199.98px) {
    .pm-daily-log-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .pm-daily-log-hero {
        flex-direction: column;
    }

    .pm-daily-log-summary-grid {
        grid-template-columns: 1fr;
    }

    .pm-daily-log-section-head--between,
    .pm-daily-log-footer-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
.tr-page{color:#06145c;padding:8px 0 28px}.tr-steps{display:flex;min-width:760px;margin-bottom:26px}.tr-steps span{position:relative;flex:1;padding-top:34px;text-align:center;font-size:.7rem;font-weight:800}.tr-steps span:before{position:absolute;top:14px;left:0;right:0;z-index:0;height:1px;background:#aebbd1;content:''}.tr-steps b{position:absolute;top:0;left:calc(50% - 14px);z-index:1;display:grid;place-items:center;width:28px;height:28px;border:1px solid #8697b6;border-radius:50%;background:#fff}.tr-steps .active{color:#073bea}.tr-steps .active b{background:#073bea;color:#fff}.tr-page h1{margin:0 0 14px;font-size:2rem}.tr-new{float:right;margin-top:-55px;padding:11px 18px;border:0;border-radius:4px;background:#0736df;color:#fff;font-weight:800}.tr-top,.tr-grid{display:grid;gap:10px;margin-bottom:10px}.tr-top{grid-template-columns:repeat(6,1fr);align-items:end;padding:12px;border:1px solid #dbe3ef;border-radius:6px;background:#fff}.tr-top label,.tr-panel label{display:grid;gap:5px;font-size:.69rem;font-weight:800}.tr-top button{padding:8px;border:1px solid #35a764;border-radius:4px;background:#fff;color:#167a36;font-weight:800}.tr-grid.three{grid-template-columns:1fr 1fr 1.25fr}.tr-grid.bottom{grid-template-columns:.9fr 1.1fr}.tr-panel{padding:10px;border:1px solid #dfe6f0;border-radius:5px;background:#fff}.tr-panel h2{margin:-10px -10px 10px;padding:8px 10px;border-bottom:1px solid #dfe6f0;color:#063ce3;font-size:.88rem}.tr-panel h2 b{display:inline-grid;place-items:center;width:22px;height:22px;margin-right:7px;border-radius:3px;background:#073bea;color:#fff}.tr-fields{display:grid;gap:10px}.tr-fields.two{grid-template-columns:1fr 1fr}.tr-fields.three{grid-template-columns:1.4fr 1fr 1fr}.tr-fields .wide{grid-column:1/-1}.tr-confirm{padding:7px 10px;background:#eef8ef;color:#197237;font-size:.75rem}.tr-actions{display:flex;flex-wrap:wrap;justify-content:space-between;gap:10px;margin-top:12px;padding:0!important;background:transparent!important}.tr-actions button{min-width:105px;padding:10px;border:0;border-radius:4px;background:#061e9f;color:#fff;font-weight:800}@media(max-width:1200px){.tr-top{grid-template-columns:repeat(3,1fr)}.tr-grid.three{grid-template-columns:1fr 1fr}.tr-grid.three section:last-child{grid-column:1/-1}}@media(max-width:700px){.tr-steps{overflow:auto}.tr-top,.tr-grid.three,.tr-grid.bottom,.tr-fields.two,.tr-fields.three{grid-template-columns:1fr}.tr-grid.three section:last-child{grid-column:auto}.tr-new{float:none;margin:0 0 10px}.tr-actions{justify-content:center}}@media print{.pm-dashboard-topbar,.pm-sidebar,.tr-new,.tr-actions{display:none}.tr-page{padding:0}.tr-panel{break-inside:avoid}}
.tr-list-page{padding:18px 0 35px;color:#1d2b44}.tr-list-hero,.tr-list-hero>div:last-child,.tr-list-tools,.tr-list-tools>div:last-child{display:flex;align-items:center;justify-content:space-between;gap:14px}.tr-list-hero small{color:#6579ee;font-weight:800}.tr-list-hero h1{margin:4px 0;font-size:2rem}.tr-list-hero p,.tr-list-tools p{margin:0;color:#76859b}.tr-list-hero span{padding:8px 13px;border-radius:16px;background:#e7ebff;color:#5864da;font-size:.8rem;font-weight:700}.tr-list-hero button{padding:10px 19px;border:1px solid #e1e6ee;border-radius:12px;background:#fff;font-weight:800;color:#35445b}.tr-list-hero button.green{border:0;background:#16a34a;color:#fff}.tr-list-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:70px 0 35px}.tr-list-metrics article{display:flex;align-items:center;gap:14px;padding:20px;border:1px solid #dfe7f1;border-radius:18px;background:#fff;box-shadow:0 8px 21px #173f6c12}.tr-list-metrics i{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#2867e8;color:#fff;font-size:1.3rem;font-style:normal}.tr-list-metrics .green-icon{background:#16a34a}.tr-list-metrics .sky-icon{background:#3b82f6}.tr-list-metrics .orange-icon{background:#f97316}.tr-list-metrics span{display:block;color:#718197}.tr-list-metrics b{font-size:1.8rem}.tr-list-card{padding:24px;border-radius:19px;background:#f7faff;box-shadow:0 10px 25px #173f6c12}.tr-list-tools{margin-bottom:19px}.tr-list-tools h2{margin:0 0 5px;font-size:1.1rem}.tr-list-tools input{width:480px}.tr-list-tools select{width:175px}.tr-list-table{width:100%;border:1px solid #e0e7f0;border-collapse:separate;border-spacing:0;border-radius:16px;overflow:hidden;background:#fff}.tr-list-table th{padding:12px;background:#f4f7fb;color:#55657b;font-size:.72rem}.tr-list-table td{padding:14px 10px;border-top:1px solid #eaf0f6;font-size:.84rem;color:#506078}.tr-list-table td small{display:block;margin-top:5px;color:#8392a6}.tr-list-table td>button:first-child,.tr-list-table td:first-child button{padding:0;border:0;background:none;color:#2768eb;font-weight:800}.tr-list-table td:last-child{white-space:nowrap}.tr-list-table td:last-child button{margin:2px;padding:6px 10px;border:1px solid #78a2ff;border-radius:4px;background:#fff;color:#3470f6}.tr-list-table td:last-child .danger{border-color:#ff747b;color:#ef4444}.tr-status{padding:6px 10px;border-radius:14px;font-size:.74rem;font-weight:800}.tr-status.completed{background:#dcfce7;color:#16a34a}@media(max-width:900px){.tr-list-hero,.tr-list-tools{align-items:flex-start;flex-direction:column}.tr-list-tools>div:last-child{width:100%;align-items:stretch;flex-direction:column}.tr-list-tools input,.tr-list-tools select{width:100%}.tr-list-metrics{grid-template-columns:1fr 1fr;margin-top:35px}}@media(max-width:500px){.tr-list-metrics{grid-template-columns:1fr}}
</style>
