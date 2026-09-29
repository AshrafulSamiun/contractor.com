<template>
    <SuperAdminLayout
        ><main class="cu-page">
            <h1>Customers Centre <span>›</span> Customer / Users</h1>
            <form class="cu-filters" @submit.prevent="load(1)">
                <h2>
                    Filters
                    <button type="button" @click="more = !more">
                        More Filters⌄
                    </button>
                </h2>
                <label
                    >Company ID<input
                        v-model="f.id"
                        placeholder="Enter Company ID" /></label
                ><label
                    >Company Name<input
                        v-model="f.search"
                        placeholder="Enter Company Name" /></label
                ><label
                    >Plan<select v-model="f.plan">
                        <option value="">All</option>
                        <option
                            v-for="p in plans"
                            :key="p"
                            :value="p.toLowerCase()"
                        >
                            {{ p }}
                        </option>
                    </select></label
                ><label
                    >Qty Users
                    <div class="range">
                        <input
                            v-model.number="f.minUsers"
                            placeholder="From"
                        /><i>–</i
                        ><input
                            v-model.number="f.maxUsers"
                            placeholder="To"
                        /></div></label
                ><label
                    >Usage Date
                    <div class="range">
                        <input v-model="f.from_date" type="date" /><i>–</i
                        ><input v-model="f.to_date" type="date" /></div></label
                ><label v-if="more"
                    >Daily Usage (HH-MM)
                    <div class="range">
                        <input v-model="f.minUsage" placeholder="From" /><i>–</i
                        ><input v-model="f.maxUsage" placeholder="To" /></div
                ></label>
                <div class="cu-actions">
                    <button>Search</button
                    ><button type="button" @click="reset">Reset</button>
                </div>
            </form>
            <div class="cu-table-wrap">
                <table class="cu-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Company ID</th>
                            <th>Company Name</th>
                            <th>Plan</th>
                            <th>Qty Users</th>
                            <th>Usage Date</th>
                            <th>Daily Usage (HH-MM)</th>
                            <th>Last Week Usage</th>
                            <th>Last Month Usage</th>
                            <th>View Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in shown" :key="r.id">
                            <td>{{ number(i) }}</td>
                            <td>{{ r.customer_no }}</td>
                            <td>{{ r.company }}</td>
                            <td>{{ r.plan }}</td>
                            <td>{{ r.qty_users }}</td>
                            <td>{{ date(r.usage_date) }}</td>
                            <td>{{ duration(r.daily_usage) }}</td>
                            <td>{{ duration(r.week_usage) }}</td>
                            <td>{{ duration(r.month_usage) }}</td>
                            <td>
                                <button
                                    class="view-users"
                                    @click="openUsers(r)"
                                >
                                    View Users
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!shown.length">
                            <td colspan="10" class="empty">
                                No customer users found.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <footer>
                    <span
                        >Showing {{ shown.length }} of
                        {{ meta.total || 0 }} records</span
                    >
                    <div>
                        <button
                            :disabled="!meta.prev_page_url"
                            @click="load((meta.current_page || 1) - 1)"
                        >
                            ‹</button
                        ><b>{{ meta.current_page || 1 }}</b
                        ><button
                            :disabled="!meta.next_page_url"
                            @click="load((meta.current_page || 1) + 1)"
                        >
                            ›
                        </button>
                    </div>
                </footer>
            </div>
            <div
                v-if="modal"
                class="cu-modal-backdrop"
                @click.self="modal = null"
            >
                <section class="cu-modal">
                    <button class="close" @click="modal = null">×</button>
                    <h2>{{ modal.company }} — Users</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in modal.users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.phone || "—" }}</td>
                                <td>{{ user.role }}</td>
                                <td>
                                    <b :class="user.status.toLowerCase()">{{
                                        user.status
                                    }}</b>
                                </td>
                                <td>{{ date(user.created_at) }}</td>
                            </tr>
                            <tr v-if="!modal.users.length">
                                <td colspan="6">
                                    No users recorded for this company.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </main></SuperAdminLayout
    >
</template>
<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import SuperAdminLayout from "../components/SuperAdminLayout.vue";
import client from "../api/client";
const f = reactive({
        id: "",
        search: "",
        plan: "",
        from_date: "",
        to_date: "",
        minUsers: "",
        maxUsers: "",
        minUsage: "",
        maxUsage: "",
    }),
    rows = ref([]),
    meta = ref({}),
    modal = ref(null),
    more = ref(false);
const plans = computed(() => [
        ...new Set(rows.value.map((x) => x.plan).filter(Boolean)),
    ]),
    shown = computed(() =>
        rows.value.filter(
            (r) =>
                (f.minUsers === "" || r.qty_users >= f.minUsers) &&
                (f.maxUsers === "" || r.qty_users <= f.maxUsers),
        ),
    );
const date = (v) =>
        v
            ? new Intl.DateTimeFormat("en-US", {
                  day: "2-digit",
                  month: "short",
                  year: "numeric",
              }).format(new Date(v))
            : "—",
    duration = (v) => `${String(v || 0).padStart(2, "0")}-00`,
    number = (i) =>
        ((meta.value.current_page || 1) - 1) * (meta.value.per_page || 20) +
        i +
        1;
async function load(page = 1) {
    const { data } = await client.get("/super-admin/customer-users", {
        params: {
            search: f.search || f.id,
            plan: f.plan,
            from_date: f.from_date,
            to_date: f.to_date,
            page,
        },
    });
    rows.value = data.data?.data || [];
    meta.value = data.data || {};
}
function reset() {
    Object.assign(f, {
        id: "",
        search: "",
        plan: "",
        from_date: "",
        to_date: "",
        minUsers: "",
        maxUsers: "",
        minUsage: "",
        maxUsage: "",
    });
    load();
}
async function openUsers(row) {
    const { data } = await client.get(
        `/super-admin/customer-users/${row.id}/list`,
    );
    modal.value = data.data;
}
onMounted(load);
</script>
<style>
.cu-page {
    color: #071541;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}
.cu-page > h1 {
    font-size: 2rem;
    font-weight: 800;
    margin: 8px 4px 15px;
}
.cu-page > h1 span {
    margin: 0 13px;
}
.cu-filters {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr) minmax(0, 0.8fr) minmax(0, 1.25fr) minmax(0, 1.65fr) auto;
    gap: 18px 26px;
    align-items: end;
    padding: 20px;
    border: 1px solid #dbe4ef;
    border-radius: 6px;
    background: #fff;
    margin-bottom: 19px;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}
.cu-filters h2 {
    grid-column: 1/-1;
    margin: 0;
    color: #0845cf;
    font-size: 1.1rem;
}
.cu-filters h2 button {
    float: right;
    border: 0;
    background: transparent;
    color: #0645d1;
    font-weight: 700;
}
.cu-filters label {
    display: grid;
    min-width: 0;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 700;
}
.cu-filters input,
.cu-filters select {
    height: 48px;
    border: 1px solid #ccd9ed;
    border-radius: 5px;
    padding: 0 12px;
    color: #5b6d94;
    font: inherit;
    min-width: 0;
}
.range {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 12px minmax(0, 1fr);
    gap: 7px;
    align-items: center;
}
.range i {
    text-align: center;
    font-style: normal;
}
.cu-actions {
    display: flex;
    gap: 15px;
    min-width: 0;
}
.cu-actions button {
    height: 48px;
    min-width: 82px;
    border: 1px solid #d1ddec;
    border-radius: 5px;
    background: #fff;
    color: #06133d;
    font-weight: 700;
}
.cu-actions button:first-child {
    background: #0648d5;
    color: #fff;
    border-color: #0648d5;
}
.cu-table-wrap {
    overflow: auto;
    border: 1px solid #dce5ef;
    border-radius: 6px;
    background: #fff;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}
.cu-table {
    width: 100%;
    min-width: 0;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 0.82rem;
}
.cu-table th {
    background: linear-gradient(100deg, #03205f, #0751c7);
    color: #fff !important;
    height: 55px;
}
.cu-table th,
.cu-table td {
    padding: 13px 10px;
    border: 1px solid #e2e9f2;
    text-align: center;
    white-space: normal;
    overflow-wrap: anywhere;
}
.view-users {
    border: 0;
    background: none;
    color: #0049e5;
    font: inherit;
    font-weight: 700;
}
.empty {
    height: 80px;
    color: #718096;
}
.cu-table-wrap footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: #fff;
}
.cu-table-wrap footer div {
    display: flex;
    gap: 10px;
}
.cu-table-wrap footer button,
.cu-table-wrap footer b {
    display: grid;
    place-items: center;
    min-width: 40px;
    height: 40px;
    border: 1px solid #d6e0ee;
    border-radius: 6px;
    background: #fff;
    color: #071541;
}
.cu-table-wrap footer b {
    background: #0647d2;
    border-color: #0647d2;
    color: #fff;
}
.cu-modal-backdrop {
    position: fixed;
    z-index: 50;
    inset: 0;
    background: #07154180;
    display: grid;
    place-items: center;
    padding: 20px;
}
.cu-modal {
    position: relative;
    max-width: 950px;
    width: 100%;
    background: #fff;
    border-radius: 9px;
    padding: 26px;
    box-shadow: 0 20px 50px #0004;
}
.cu-modal h2 {
    margin: 0 0 18px;
    color: #063eb8;
}
.cu-modal .close {
    position: absolute;
    right: 14px;
    top: 8px;
    border: 0;
    background: none;
    font-size: 2rem;
}
.cu-modal table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.cu-modal th,
.cu-modal td {
    padding: 11px;
    border: 1px solid #e2e8f0;
    text-align: left;
}
.cu-modal th {
    background: #eef4ff;
}
.cu-modal .active {
    color: #08752b;
}
.cu-modal .inactive {
    color: #6b7280;
}
@media (max-width: 1200px) {
    .cu-filters {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 700px) {
    .cu-filters {
        grid-template-columns: 1fr;
    }
    .cu-page > h1 {
        font-size: 1.6rem;
    }
}
</style>
