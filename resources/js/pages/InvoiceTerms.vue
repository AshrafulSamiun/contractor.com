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
                    <button class="pm-icon-btn" type="button" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z"
                            />
                        </svg>
                        <span class="pm-topbar-badge"></span>
                    </button>
                    <button class="pm-icon-btn" type="button" aria-label="Settings">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66Z"
                            />
                        </svg>
                    </button>
                </div>
                <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
                    <div class="pm-topbar-avatar">JA</div>
                    <span class="pm-topbar-name">{{ userName }}</span>
                    <span class="pm-topbar-pill">Admin</span>
                    <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m7 10 5 5 5-5H7Z" />
                        </svg>
                    </button>
                    <div v-if="userMenuOpen" class="pm-user-menu">
                        <button class="pm-user-item" type="button" @click="goProfile">
                            Profile
                        </button>
                        <button class="pm-user-item" type="button" @click="goAccount">
                            Account
                        </button>
                        <button class="pm-user-item danger" type="button" @click="logout">
                            Log out
                        </button>
                    </div>
                </div>
            </div>

            <div class="container pm-ops-page pm-payment-method-page">
                <section class="pm-payment-method-hero">
                    <div>
                        <div class="pm-payment-method-kicker">Profiles Module</div>
                        <h2 class="pm-payment-method-title">Invoice Terms</h2>
                        <div class="pm-page-subtitle pm-payment-method-subtitle">
                            Dashboard &gt; Profiles &gt; Invoice Terms &gt;
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
                    <div
                        v-if="viewMode === 'detail'"
                        class="pm-payment-method-hero-actions"
                    >
                        <button
                            class="pm-hero-action-button pm-hero-action-button--primary"
                            type="button"
                            @click="startEdit(detailItem)"
                        >
                            <span class="pm-hero-action-icon" aria-hidden="true"
                                >&crarr;</span
                            >
                            <span>Edit</span>
                        </button>
                    </div>
                    <div
                        v-else
                        class="pm-payment-method-hero-actions"
                    >
                        <span class="pm-payment-method-chip">{{
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
                                <span class="pm-hero-action-icon" aria-hidden="true"
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
                                <span class="pm-hero-action-icon pm-hero-action-icon--list" aria-hidden="true">
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
                    class="pm-card pm-ops-card p-4 pm-payment-method-list-shell"
                >
                    <div class="pm-payment-method-list-toolbar">
                        <div>
                            <h5 class="pm-form-title">Invoice Terms List</h5>
                            <div class="pm-payment-method-list-meta">
                                {{ filteredItems.length }} records found
                            </div>
                        </div>
                        <div class="pm-payment-method-list-search">
                            <input
                                v-model.trim="listSearchQuery"
                                class="form-control"
                                type="search"
                                placeholder="Search all invoice terms fields"
                            />
                        </div>
                    </div>

                    <div class="table-responsive pm-payment-method-table-wrap">
                        <table class="table pm-payment-method-table align-middle">
                            <thead>
                                <tr>
                                    <th
                                        v-for="column in listColumns"
                                        :key="column.key"
                                        scope="col"
                                    >
                                        <button
                                            class="pm-payment-method-sort"
                                            type="button"
                                            @click="sortBy(column.key)"
                                        >
                                            {{ column.label }}
                                            <span
                                                v-if="sortKey === column.key"
                                                class="pm-sort-icon"
                                                :class="{
                                                    desc: sortOrder === 'desc',
                                                }"
                                                >^</span
                                            >
                                        </button>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in filteredItems"
                                    :key="item.id"
                                    class="pm-payment-method-row"
                                    @click="viewDetail(item)"
                                >
                                    <td v-for="column in listColumns" :key="column.key">
                                        <div
                                            v-if="column.key === 'term_id'"
                                            class="pm-term-id"
                                        >
                                            {{ item.term_id }}
                                        </div>
                                        <div
                                            v-else-if="column.key === 'term_name'"
                                            class="pm-term-name"
                                        >
                                            {{ item.term_name }}
                                        </div>
                                        <div
                                            v-else-if="column.key === 'status'"
                                            class="pm-status-badge"
                                            :class="{
                                                active: item.status === 1,
                                                inactive: item.status === 2,
                                            }"
                                        >
                                            <span class="pm-status-indicator"></span>
                                            {{ item.status === 1 ? "Active" : "Inactive" }}
                                        </div>
                                        <div v-else>
                                            {{ item[column.key] }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="pm-payment-method-actions">
                                            <button
                                                class="pm-icon-btn"
                                                type="button"
                                                title="View"
                                                @click.stop="viewDetail(item)"
                                            >
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                                                    />
                                                    <path
                                                        d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                                                    />
                                                </svg>
                                            </button>
                                            <button
                                                class="pm-icon-btn"
                                                type="button"
                                                title="Delete"
                                                @click.stop="confirmDelete(item)"
                                            >
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!filteredItems.length">
                                    <td :colspan="listColumns.length + 1">
                                        <div class="pm-empty-state">
                                            No invoice terms found.
                                            <button
                                                type="button"
                                                @click="openFormView"
                                                >Create one</button
                                            >
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section
                    v-if="viewMode === 'detail'"
                    class="pm-card pm-ops-card p-4"
                >
                    <div class="pm-form-title">Invoice Terms Details</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term ID</label>
                            <div class="pm-view-field">{{ detailItem.term_id }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="pm-view-field">
                                <span
                                    class="pm-status-badge"
                                    :class="{
                                        active: detailItem.status === 1,
                                        inactive: detailItem.status === 2,
                                    }"
                                >
                                    <span class="pm-status-indicator"></span>
                                    {{ detailItem.status === 1 ? "Active" : "Inactive" }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term Name</label>
                            <div class="pm-view-field">{{ detailItem.term_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>
                            <div class="pm-view-field">{{ detailItem.description || '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Note</label>
                            <div class="pm-view-field">{{ detailItem.note || '-' }}</div>
                        </div>
                    </div>
                    <div class="pm-form-actions">
                        <button
                            class="btn btn-secondary"
                            type="button"
                            @click="openListView"
                        >
                            Back
                        </button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            @click="startEdit(detailItem)"
                        >
                            Edit
                        </button>
                    </div>
                </section>

                <section
                    v-if="viewMode === 'form'"
                    class="pm-card pm-ops-card p-4"
                >
                    <form @submit.prevent="submitForm">
                        <div class="pm-form-title">{{ activeId ? "Edit Invoice Terms" : "New Invoice Terms" }}</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Term Name *</label>
                                <input
                                    v-model="form.term_name"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.term_name }"
                                    type="text"
                                    required
                                />
                                <div v-if="errors.term_name" class="invalid-feedback">
                                    {{ errors.term_name }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Description</label>
                                <textarea
                                    v-model="form.description"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Note</label>
                                <textarea
                                    v-model="form.note"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input
                                            v-model="form.status"
                                            class="form-check-input"
                                            type="radio"
                                            id="status_active"
                                            :value="1"
                                        />
                                        <label class="form-check-label" for="status_active">
                                            Active
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input
                                            v-model="form.status"
                                            class="form-check-input"
                                            type="radio"
                                            id="status_inactive"
                                            :value="2"
                                        />
                                        <label class="form-check-label" for="status_inactive">
                                            Inactive
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pm-form-actions">
                            <button
                                class="btn btn-secondary"
                                type="button"
                                @click="cancelForm"
                            >
                                Cancel
                            </button>
                            <button
                                class="btn btn-primary"
                                type="submit"
                                :disabled="submitting"
                            >
                                {{ submitting ? "Saving..." : "Save" }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import AppSidebar from "../components/AppSidebar.vue";
import client from "../api/client";
import { authState } from "../store/auth";

const router = useRouter();

const sidebarOpen = ref(false);
const sidebarHidden = ref(false);
const searchQuery = ref("");
const userMenuOpen = ref(false);
const userMenuRef = ref(null);

const viewMode = ref("list");
const listSearchQuery = ref("");
const sortKey = ref("term_name");
const sortOrder = ref("asc");
const activeId = ref(null);
const detailItem = ref({});
const submitting = ref(false);
const errors = reactive({});

const form = reactive({
    term_name: "",
    description: "",
    note: "",
    status: 1,
});

const listColumns = [
    { key: "term_id", label: "Term ID" },
    { key: "term_name", label: "Term Name" },
    { key: "description", label: "Description" },
    { key: "note", label: "Note" },
    { key: "status", label: "Status" },
];

const items = ref([]);

const userName = computed(() => {
    const user = authState.user;
    if (!user) return "";
    return user.username || user.email || "";
});

const filteredItems = computed(() => {
    let result = items.value;
    if (listSearchQuery.value) {
        const query = listSearchQuery.value.toLowerCase();
        result = result.filter((item) =>
            Object.values(item).some((val) =>
                String(val).toLowerCase().includes(query),
            ),
        );
    }
    if (sortKey.value) {
        result = [...result].sort((a, b) => {
            const aVal = a[sortKey.value];
            const bVal = b[sortKey.value];
            const cmp = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
            return sortOrder.value === "asc" ? cmp : -cmp;
        });
    }
    return result;
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
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
    router.push("/account/billing");
};

const logout = async () => {
    userMenuOpen.value = false;
    router.push("/login");
};

const openListView = () => {
    viewMode.value = "list";
    activeId.value = null;
};

const openFormView = () => {
    viewMode.value = "form";
    resetForm();
};

const resetForm = () => {
    form.term_name = "";
    form.description = "";
    form.note = "";
    form.status = 1;
    activeId.value = null;
    Object.keys(errors).forEach((key) => delete errors[key]);
};

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
    } else {
        sortKey.value = key;
        sortOrder.value = "asc";
    }
};

const viewDetail = (item) => {
    detailItem.value = item;
    viewMode.value = "detail";
};

const startEdit = (item) => {
    activeId.value = item.id;
    form.term_name = item.term_name;
    form.description = item.description || "";
    form.note = item.note || "";
    form.status = item.status;
    viewMode.value = "form";
};

const cancelForm = () => {
    if (activeId.value) {
        viewMode.value = "detail";
    } else {
        viewMode.value = "list";
    }
    resetForm();
};

const confirmDelete = async (item) => {
    if (!confirm(`Delete "${item.term_name}"?`)) return;
    try {
        await client.delete(`/invoice-terms/${item.id}`);
        await fetchItems();
    } catch (err) {
        alert("Failed to delete item");
    }
};

const submitForm = async () => {
    errors.term_name = "";
    if (!form.term_name.trim()) {
        errors.term_name = "Term name is required";
        return;
    }
    submitting.value = true;
    try {
        const payload = {
            term_name: form.term_name.trim(),
            description: form.description.trim() || null,
            note: form.note.trim() || null,
            status: form.status,
        };
        if (activeId.value) {
            await client.put(`/invoice-terms/${activeId.value}`, payload);
        } else {
            await client.post("/invoice-terms", payload);
        }
        await fetchItems();
        openListView();
    } catch (err) {
        if (err.response?.data?.errors) {
            Object.assign(errors, err.response.data.errors);
        } else {
            alert("Failed to save");
        }
    } finally {
        submitting.value = false;
    }
};

const fetchItems = async () => {
    try {
        const { data } = await client.get("/invoice-terms");
        items.value = data.data || [];
    } catch (err) {
        items.value = [];
    }
};

onMounted(() => {
    fetchItems();
});
</script>

<style scoped>
.pm-payment-method-page {
    max-width: 1200px;
}

.pm-payment-method-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.pm-payment-method-kicker {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.pm-payment-method-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0;
}

.pm-payment-method-subtitle {
    color: #6c757d;
    font-size: 0.875rem;
}

.pm-payment-method-chip {
    background: #f8f9fa;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
}

.pm-payment-method-hero-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pm-hero-action-button {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background: #fff;
    cursor: pointer;
    font-size: 0.875rem;
}

.pm-hero-action-button:hover {
    background: #f8f9fa;
}

.pm-hero-action-button--primary {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.pm-hero-action-button--primary:hover {
    background: #0b5ed7;
}

.pm-hero-action-icon {
    font-size: 1rem;
}

.pm-payment-method-list-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.pm-payment-method-list-search {
    max-width: 300px;
}

.pm-payment-method-table-wrap {
    overflow-x: auto;
}

.pm-payment-method-table th {
    white-space: nowrap;
    font-weight: 500;
    color: #495057;
    font-size: 0.875rem;
}

.pm-payment-method-table td {
    font-size: 0.875rem;
}

.pm-payment-method-sort {
    background: none;
    border: none;
    padding: 0;
    font: inherit;
    color: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pm-sort-icon {
    font-size: 0.75rem;
}

.pm-sort-icon.desc {
    display: inline-block;
    transform: rotate(180deg);
}

.pm-payment-method-row {
    cursor: pointer;
}

.pm-payment-method-row:hover {
    background: #f8f9fa;
}

.pm-term-id {
    font-weight: 500;
    color: #0d6efd;
}

.pm-term-name {
    font-weight: 500;
}

.pm-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.pm-status-badge.active {
    background: #d1fae5;
    color: #059669;
}

.pm-status-badge.inactive {
    background: #e5e7eb;
    color: #6b7280;
}

.pm-status-indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.pm-status-badge.active .pm-status-indicator {
    background: #059669;
}

.pm-status-badge.inactive .pm-status-indicator {
    background: #6b7280;
}

.pm-payment-method-actions {
    display: flex;
    gap: 0.25rem;
}

.pm-empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.pm-empty-state button {
    background: none;
    border: none;
    color: #0d6efd;
    cursor: pointer;
    text-decoration: underline;
}

.pm-view-field {
    padding: 0.375rem 0;
    min-height: 2.25rem;
}

.pm-form-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    margin-top: 1rem;
}
</style>