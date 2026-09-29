<template>
    <SuperAdminLayout
        ><main class="cr">
            <header>
                <h1>
                    Customers Centre <i>›</i> Customer Users &amp;
                    Licence Report
                </h1>
                <RouterLink to="/super-admin/reports"
                    >←&nbsp; Back to Reports List</RouterLink
                >
            </header>
            <form @submit.prevent="load">
                <h2>Filter</h2>
                <label
                    >Date From<input v-model="f.from_date" type="date" /></label
                ><label>Date To<input v-model="f.to_date" type="date" /></label
                ><label
                    >Customer No.<input
                        v-model="f.customer"
                        placeholder="Enter Customer No." /></label
                ><label
                    >Company Name<input
                        v-model="f.company"
                        placeholder="Enter Company Name" /></label
                ><label
                    >Plan<select v-model="f.plan">
                        <option value="">All Plan</option>
                        <option>Enterprise</option>
                        <option>Professional</option>
                        <option>Basic</option>
                    </select></label
                ><label
                    >Status<select v-model="f.status">
                        <option>All Status</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select></label
                >
                <div>
                    <button>Search</button
                    ><button type="button" @click="reset">Reset</button>
                </div>
            </form>
            <section class="summary">
                <h2>Summary (As of {{ today }})</h2>
                <article v-for="x in cards" :key="x.label" :class="x.tone">
                    <small>{{ x.label }}</small
                    ><strong>{{ x.value }}</strong>
                </article>
            </section>
            <section class="report">
                <h2>Customer Users &amp; Licence Details</h2>
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Customer No.</th>
                                <th>Company Name</th>
                                <th>Plan</th>
                                <th>Licences Purchased</th>
                                <th>Active Users</th>
                                <th>Available Licences</th>
                                <th>Suspected Shared Licences</th>
                                <th>Failed Credentials Qty</th>
                                <th>View Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in shown" :key="r.id">
                                <td>{{ i + 1 }}</td>
                                <td>{{ r.customer_no }}</td>
                                <td>{{ r.company }}</td>
                                <td>
                                    <b class="plan">{{ r.plan }}</b>
                                </td>
                                <td>{{ licences(r) }}</td>
                                <td>{{ r.qty_users }}</td>
                                <td>
                                    {{ Math.max(licences(r) - r.qty_users, 0) }}
                                </td>
                                <td><em class="warn">0</em></td>
                                <td><em class="danger">0</em></td>
                                <td>
                                    <button @click="openUsers(r)">View</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">TOTAL</th>
                                <th>{{ totalLicences }}</th>
                                <th>{{ totalUsers }}</th>
                                <th>{{ totalAvailable }}</th>
                                <th>0</th>
                                <th>0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <footer>
                    Showing 1 to {{ shown.length }} of
                    {{ shown.length }} records
                    <span>‹　<b>1</b>　2　3　…　›</span>
                </footer>
            </section>
            <div class="bottom">
                <button @click="print">▣&nbsp; Print</button
                ><button @click="print">▧&nbsp; Save PDF</button>
                <p>
                    ⓘ&nbsp; All licence and user counts are based on the
                    selected filter period.
                </p>
            </div>
            <div v-if="modal" class="modal" @click.self="modal = null">
                <section>
                    <button @click="modal = null">×</button>
                    <h2>{{ modal.company }} — Users</h2>
                    <p v-for="u in users" :key="u.id">
                        <b>{{ u.name }}</b
                        ><br />{{ u.email }} <small>{{ u.status }}</small>
                    </p>
                    <p v-if="!users.length">No users found.</p>
                </section>
            </div>
        </main></SuperAdminLayout
    >
</template>
<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { RouterLink } from "vue-router";
import SuperAdminLayout from "../components/SuperAdminLayout.vue";
import client from "../api/client";
const f = reactive({
        from_date: "",
        to_date: "",
        customer: "",
        company: "",
        plan: "",
        status: "All Status",
    }),
    rows = ref([]),
    modal = ref(null),
    users = ref([]),
    today = "May 15, 2025";
const shown = computed(() =>
        rows.value.filter(
            (r) =>
                (!f.customer || r.customer_no.includes(f.customer)) &&
                (!f.company ||
                    r.company
                        .toLowerCase()
                        .includes(f.company.toLowerCase())) &&
                (!f.plan ||
                    r.plan.toLowerCase().includes(f.plan.toLowerCase())),
        ),
    ),
    licences = (r) => Math.max(Number(r.qty_users || 0) + 4, 1),
    totalLicences = computed(() =>
        shown.value.reduce((s, r) => s + licences(r), 0),
    ),
    totalUsers = computed(() =>
        shown.value.reduce((s, r) => s + Number(r.qty_users || 0), 0),
    ),
    totalAvailable = computed(() => totalLicences.value - totalUsers.value),
    cards = computed(() => [
        { label: "Total Customers", value: shown.value.length, tone: "blue" },
        {
            label: "Total Licences Purchased",
            value: totalLicences.value,
            tone: "green",
        },
        { label: "Active Users", value: totalUsers.value, tone: "blue" },
        {
            label: "Available Licences",
            value: totalAvailable.value,
            tone: "green",
        },
        { label: "Suspected Shared Licences", value: 0, tone: "orange" },
        { label: "Failed Credentials", value: 0, tone: "red" },
    ]);
async function load() {
    const d = (
        await client.get("/super-admin/customer-users", {
            params: {
                search: [f.customer, f.company].filter(Boolean).join(" "),
                plan: f.plan,
                from_date: f.from_date,
                to_date: f.to_date,
            },
        })
    ).data.data;
    rows.value = d.data || [];
}
function reset() {
    Object.assign(f, {
        from_date: "",
        to_date: "",
        customer: "",
        company: "",
        plan: "",
        status: "All Status",
    });
    load();
}
async function openUsers(r) {
    modal.value = r;
    users.value =
        (await client.get(`/super-admin/customer-users/${r.id}/list`)).data.data
            .users || [];
}
function print() {
    window.print();
}
onMounted(load);
</script>
<style scoped>
.cr {
    color: #071541;
}
.cr header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cr h1 {
    font-size: 1.85rem;
    margin: 6px 0 15px;
}
.cr h1 i {
    font-style: normal;
    margin: 0 12px;
}
.cr a {
    color: #071541;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.75rem;
    border: 1px solid #d5dfec;
    padding: 10px 14px;
    border-radius: 5px;
}
.cr form,
.summary,
.report {
    border: 1px solid #dbe4ef;
    background: #fff;
    border-radius: 6px;
    padding: 14px;
    margin-bottom: 14px;
}
.cr form {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px 40px;
}
.cr form h2,
.summary h2,
.report h2 {
    grid-column: 1/-1;
    color: #0648d8;
    font-size: 1rem;
    margin: 0;
}
.cr label {
    display: grid;
    gap: 7px;
    font-size: 0.73rem;
    font-weight: 700;
}
.cr input,
.cr select {
    height: 37px;
    border: 1px solid #ccd9ed;
    border-radius: 4px;
    padding: 0 10px;
}
.cr form div {
    display: flex;
    align-items: end;
    gap: 10px;
}
.cr button {
    border: 1px solid #d0ddeb;
    border-radius: 4px;
    background: #fff;
    color: #0648d8;
    font-weight: 700;
    padding: 9px 19px;
}
.cr form button:first-child,
.bottom button:first-child {
    background: #0648d8;
    color: #fff;
}
.summary {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
}
.summary h2 {
    margin-bottom: -3px;
}
.summary article {
    padding: 17px;
    border: 1px solid #dce5ef;
    border-radius: 5px;
}
.summary small {
    display: block;
}
.summary strong {
    font-size: 1.6rem;
    display: block;
    margin-top: 10px;
}
.green strong {
    color: #078334;
}
.orange strong {
    color: #ef7b00;
}
.red strong {
    color: #eb1f1f;
}
.report {
    padding: 0;
    overflow: auto;
}
.report h2 {
    padding: 12px 15px;
}
.report table {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    font-size: 0.74rem;
}
.report th,
.report td {
    border: 1px solid #e2e9f1;
    padding: 9px;
    text-align: center;
}
.report th {
    background: #f4f6f9;
}
.report td:nth-child(3) {
    text-align: left;
}
.plan {
    background: #e4f1ff;
    padding: 5px;
    color: #0750a4;
    font-weight: 500;
}
.warn,
.danger {
    font-style: normal;
    padding: 4px 8px;
    border-radius: 4px;
}
.warn {
    color: #e27400;
    background: #fff1df;
}
.danger {
    color: #e11;
    background: #ffe6e6;
}
.report footer {
    padding: 13px 15px;
}
.report footer span {
    float: right;
}
.bottom {
    display: flex;
    gap: 22px;
    align-items: center;
}
.bottom p {
    margin-left: 20px;
    background: #f1f6fd;
    padding: 12px 20px;
    font-size: 0.75rem;
    flex: 1;
}
.modal {
    position: fixed;
    inset: 0;
    background: #0008;
    display: grid;
    place-items: center;
    z-index: 30;
}
.modal section {
    position: relative;
    background: #fff;
    border-radius: 8px;
    padding: 22px;
    min-width: 330px;
}
.modal section > button {
    position: absolute;
    right: 10px;
    top: 8px;
    border: 0;
    font-size: 1.4rem;
}
@media (max-width: 900px) {
    .cr form,
    .summary {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
