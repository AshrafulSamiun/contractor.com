<template>
    <SuperAdminLayout
        ><main class="rep">
            <header>
                <h1>
                    Customers Centre <i>›</i> Customer Master Report
                </h1>
                <RouterLink to="/super-admin/reports"
                    >←&nbsp; Back to Reports List</RouterLink
                >
            </header>
            <form @submit.prevent="load">
                <h2>Filter</h2>
                <label>Date From<input v-model="f.from" type="date" /></label
                ><label>Date To<input v-model="f.to" type="date" /></label
                ><label
                    >Customer No.<input
                        v-model="f.customer"
                        placeholder="Enter Customer No." /></label
                ><label
                    >Company Name<input
                        v-model="f.company"
                        placeholder="Enter Company Name" /></label
                ><label
                    >Plan<select>
                        <option>All Plan</option>
                    </select></label
                ><label
                    >Status<select v-model="f.status">
                        <option>All Status</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select></label
                ><label
                    >Payment Status<select>
                        <option>All</option>
                    </select></label
                ><label
                    >Country<select>
                        <option>All</option>
                    </select></label
                ><label
                    >Sales Rep.<select>
                        <option>All Sales Reps</option>
                    </select></label
                ><label class="check"
                    ><input type="checkbox" /> Include Closed Customers</label
                >
                <div>
                    <button>Search</button
                    ><button type="button" @click="reset">Reset</button>
                </div>
            </form>
            <section class="summary">
                <h2>Summary (Selected Period)</h2>
                <article v-for="x in cards" :key="x.label" :class="x.tone">
                    <small>{{ x.label }}</small
                    ><strong>{{ x.money ? money(x.value) : x.value }}</strong>
                </article>
            </section>
            <section class="master">
                <h2>Customer Master Report Details</h2>
                <div class="master-panels">
                    <section
                        v-for="(r, i) in shown.slice(0, 1)"
                        :key="r.customer_no"
                    >
                        <div>
                            <h3>Customer Profile</h3>
                            <p>
                                Customer No. <b>{{ r.customer_no }}</b>
                            </p>
                            <p>
                                Company Name <b>{{ r.company }}</b>
                            </p>
                            <p>
                                Signup Date <b>{{ date(r.since) }}</b>
                            </p>
                            <p>
                                Status <b class="ok">{{ r.status }}</b>
                            </p>
                            <a>View Full Profile</a>
                        </div>
                        <div>
                            <h3>Plan &amp; Licences</h3>
                            <p>
                                Plan Name <b>{{ r.plan }}</b>
                            </p>
                            <p>
                                Monthly Payment <b>{{ money(0) }}</b>
                            </p>
                            <p>
                                Licences Purchased <b>{{ r.qty_users }}</b>
                            </p>
                            <a>View Plan Details</a>
                        </div>
                        <div>
                            <h3>Users &amp; Usage</h3>
                            <p>
                                Total Users <b>{{ r.qty_users }}</b>
                            </p>
                            <p>
                                Active Users <b>{{ r.qty_users }}</b>
                            </p>
                            <p>Usage (HH:MM) <b>00:00</b></p>
                            <a>View Users</a>
                        </div>
                        <div>
                            <h3>Invoice &amp; Payment</h3>
                            <p>
                                Total Billed <b>{{ money(0) }}</b>
                            </p>
                            <p>
                                Total Paid <b class="ok">{{ money(0) }}</b>
                            </p>
                            <p>
                                Outstanding <b>{{ money(0) }}</b>
                            </p>
                            <a>View Invoices / Payments</a>
                        </div>
                    </section>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Customer No.</th>
                            <th>Company Name</th>
                            <th>Plan Name</th>
                            <th>Status</th>
                            <th>Users (Active / Total)</th>
                            <th>Licences Purchased</th>
                            <th>Logged In Now</th>
                            <th>Total Billed</th>
                            <th>Total Paid</th>
                            <th>Outstanding</th>
                            <th>Last Payment Date</th>
                            <th>Years/Months in Service</th>
                            <th>View Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in shown" :key="r.customer_no">
                            <td>{{ i + 1 }}</td>
                            <td>{{ r.customer_no }}</td>
                            <td>{{ r.company }}</td>
                            <td>{{ r.plan }}</td>
                            <td>
                                <b class="ok">{{ r.status }}</b>
                            </td>
                            <td>{{ r.qty_users }} / {{ r.qty_users }}</td>
                            <td>{{ r.qty_users }}</td>
                            <td>0</td>
                            <td>{{ money(0) }}</td>
                            <td class="ok">{{ money(0) }}</td>
                            <td>{{ money(0) }}</td>
                            <td>—</td>
                            <td>{{ duration(r.since) }}</td>
                            <td>
                                <RouterLink
                                    :to="
                                        '/super-admin/customers/' +
                                        id(r.customer_no)
                                    "
                                    >View</RouterLink
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
                <footer>
                    Showing 1 to {{ shown.length }} of
                    {{ shown.length }} records
                    <span>‹　<b>1</b>　2　3　…　›</span>
                </footer>
            </section>
            <div class="bottom">
                <button @click="print">Print</button
                ><button @click="print">Save PDF</button>
                <p>ⓘ&nbsp; All amounts are shown in your system currency.</p>
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
        from: "",
        to: "",
        customer: "",
        company: "",
        status: "All Status",
    }),
    rows = ref([]),
    id = (v) => String(v || "").replace(/\D/g, ""),
    money = (v, c = "CAD") =>
        Number(v || 0).toLocaleString("en-CA", {
            style: "currency",
            currency: c,
        }),
    date = (v) =>
        v
            ? new Intl.DateTimeFormat("en-CA", {
                  month: "short",
                  day: "numeric",
                  year: "numeric",
              }).format(new Date(`${v}T00:00:00`))
            : "—",
    shown = computed(() =>
        rows.value.filter(
            (r) =>
                (f.status === "All Status" || r.status === f.status) &&
                (!f.customer || r.customer_no.includes(f.customer)) &&
                (!f.company ||
                    r.company.toLowerCase().includes(f.company.toLowerCase())),
        ),
    ),
    duration = (v) => {
        const m = v
            ? Math.max(0, Math.floor((Date.now() - new Date(v)) / 2629800000))
            : 0;
        return `${Math.floor(m / 12)} Y ${m % 12} M`;
    },
    cards = computed(() => [
        { label: "Total Customers", value: shown.value.length },
        {
            label: "Active Customers",
            value: shown.value.filter((r) => r.status === "Active").length,
            tone: "green",
        },
        {
            label: "Suspended Customers",
            value: shown.value.filter((r) => r.status === "Inactive").length,
            tone: "orange",
        },
        { label: "Closed Customers", value: 0, tone: "red" },
        {
            label: "Total Users",
            value: shown.value.reduce(
                (s, r) => s + Number(r.qty_users || 0),
                0,
            ),
            tone: "blue",
        },
        {
            label: "Licences Purchased",
            value: shown.value.reduce(
                (s, r) => s + Number(r.qty_users || 0),
                0,
            ),
            tone: "purple",
        },
        { label: "Total Billed", value: 0, money: true },
        { label: "Total Paid", value: 0, money: true, tone: "green" },
        { label: "Outstanding", value: 0, money: true, tone: "red" },
    ]);
async function load() {
    const d = (
        await client.get("/super-admin/customer-users", {
            params: {
                search: [f.customer, f.company].filter(Boolean).join(" "),
            },
        })
    ).data.data;
    rows.value = (d.data || []).map((r) => ({ ...r, status: "Active" }));
}
function reset() {
    Object.assign(f, {
        from: "",
        to: "",
        customer: "",
        company: "",
        status: "All Status",
    });
    load();
}
function print() {
    window.print();
}
onMounted(load);
</script>
<style scoped>
.rep {
    color: #071541;
}
.rep header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.rep h1 {
    font-size: 1.82rem;
    margin: 6px 0 15px;
}
.rep h1 i {
    font-style: normal;
    margin: 0 12px;
}
.rep header a {
    color: #071541;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.75rem;
    border: 1px solid #d5dfec;
    padding: 10px 14px;
    border-radius: 5px;
}
.rep form,
.summary,
.master {
    border: 1px solid #dbe4ef;
    background: #fff;
    border-radius: 6px;
    padding: 14px;
    margin-bottom: 14px;
}
.rep form {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px 28px;
}
.rep form h2,
.summary h2,
.master h2 {
    grid-column: 1/-1;
    color: #0648d8;
    font-size: 1rem;
    margin: 0;
}
.rep label {
    display: grid;
    gap: 7px;
    font-size: 0.73rem;
    font-weight: 700;
}
.rep input,
.rep select {
    height: 37px;
    border: 1px solid #ccd9ed;
    border-radius: 4px;
    padding: 0 10px;
}
.rep label.check {
    display: flex;
    align-items: end;
    height: 37px;
}
.rep form div {
    display: flex;
    align-items: end;
    gap: 10px;
}
.rep button {
    border: 1px solid #d0ddeb;
    border-radius: 4px;
    background: #fff;
    color: #0648d8;
    font-weight: 700;
    padding: 9px 19px;
}
.rep form button:first-child,
.bottom button:first-child {
    background: #0648d8;
    color: #fff;
}
.summary {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    gap: 10px;
}
.summary h2 {
    margin-bottom: -3px;
}
.summary article {
    padding: 13px;
    border: 1px solid #dce5ef;
    border-radius: 5px;
}
.summary small {
    display: block;
}
.summary strong {
    font-size: 1.15rem;
    display: block;
    margin-top: 10px;
}
.green strong,
.ok {
    color: #078334;
}
.red strong {
    color: #eb1f1f;
}
.orange strong {
    color: #ef7100;
}
.purple strong {
    color: #8719d4;
}
.master {
    padding: 0;
    overflow: auto;
}
.master > h2 {
    padding: 12px 15px;
}
.master-panels section {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    min-width: 900px;
}
.master-panels div {
    padding: 10px;
    border: 1px solid #e2e9f1;
    font-size: 0.67rem;
}
.master h3 {
    margin: 0 0 8px;
    color: #0648d8;
    font-size: 0.76rem;
    text-align: center;
}
.master p {
    display: flex;
    justify-content: space-between;
    margin: 6px 0;
}
.master a,
.master table a {
    color: #0050e7;
    text-decoration: none;
    font-weight: 700;
}
.master table {
    width: 100%;
    min-width: 1300px;
    border-collapse: collapse;
    font-size: 0.69rem;
}
.master th,
.master td {
    border: 1px solid #e2e9f1;
    padding: 7px;
    text-align: center;
}
.master th {
    background: #f4f6f9;
}
.master td:nth-child(3) {
    text-align: left;
}
.master footer {
    padding: 13px 15px;
}
.master footer span {
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
@media (max-width: 900px) {
    .rep form,
    .summary {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
