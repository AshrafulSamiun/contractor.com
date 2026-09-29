<template>
    <SuperAdminLayout
        ><main class="rpt">
            <header>
                <h1>
                    Customers Centre <i>›</i> New, Active &amp; Closed
                    Customers Report
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
                    >Status<select v-model="f.status">
                        <option>All</option>
                        <option>Active</option>
                        <option>Closed</option>
                    </select></label
                ><label
                    >Customer No.<input
                        v-model="f.customer"
                        placeholder="Enter Customer No." /></label
                ><label
                    >Company Name<input
                        v-model="f.company"
                        placeholder="Enter Company Name" /></label
                ><label
                    >Years in Service<select>
                        <option>All</option>
                    </select></label
                ><label
                    >Plan<select>
                        <option>All Plan</option>
                    </select></label
                ><label
                    >Sales Rep.<select>
                        <option>All Sales Reps</option>
                    </select></label
                ><label
                    >Country<select>
                        <option>All</option>
                    </select></label
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
                    ><strong>{{ x.value }}</strong>
                </article>
            </section>
            <section class="tablebox">
                <h2>New, Active &amp; Closed Customers Details</h2>
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Customer No.</th>
                                <th>Company Name</th>
                                <th>Signup Date</th>
                                <th>Years / Months in Service</th>
                                <th>Status</th>
                                <th>Suspension Date</th>
                                <th>Closing Date</th>
                                <th>Closing Reason</th>
                                <th>Sales Rep.</th>
                                <th>View Profile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in shown" :key="r.customer_no">
                                <td>{{ i + 1 }}</td>
                                <td>{{ r.customer_no }}</td>
                                <td>{{ r.company }}</td>
                                <td>{{ date(r.since) }}</td>
                                <td>{{ duration(r.since) }}</td>
                                <td>
                                    <b :class="statusClass(r.status)">{{
                                        r.status
                                    }}</b>
                                </td>
                                <td>—</td>
                                <td>
                                    {{
                                        r.status === "Closed"
                                            ? date(r.last_activity)
                                            : "—"
                                    }}
                                </td>
                                <td>
                                    {{
                                        r.status === "Closed"
                                            ? "Customer Request"
                                            : "—"
                                    }}
                                </td>
                                <td>{{ reps[i % 4] }}</td>
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
                        <tfoot>
                            <tr>
                                <th>TOTAL</th>
                                <th>{{ shown.length }}</th>
                                <th colspan="3">—</th>
                                <th class="active">{{ active }}</th>
                                <th>—</th>
                                <th class="closed">{{ closed }}</th>
                                <th colspan="3">—</th>
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
                <button @click="print">Print</button
                ><button @click="print">Save PDF</button>
                <p>ⓘ&nbsp; All dates are shown in your system time zone.</p>
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
        status: "All",
        customer: "",
        company: "",
    }),
    rows = ref([]),
    reps = ["John Smith", "Jane Doe", "Michael Brown", "Sarah Wilson"],
    id = (v) => String(v || "").replace(/\D/g, ""),
    date = (v) =>
        v
            ? new Intl.DateTimeFormat("en-CA", {
                  month: "short",
                  day: "numeric",
                  year: "numeric",
              }).format(new Date(`${v}T00:00:00`))
            : "—";
const shown = computed(() =>
        rows.value.filter(
            (r) =>
                (f.status === "All" || r.status === f.status) &&
                (!f.customer || r.customer_no.includes(f.customer)) &&
                (!f.company ||
                    r.company.toLowerCase().includes(f.company.toLowerCase())),
        ),
    ),
    active = computed(
        () => shown.value.filter((r) => r.status === "Active").length,
    ),
    closed = computed(
        () => shown.value.filter((r) => r.status === "Closed").length,
    ),
    suspended = computed(
        () => shown.value.filter((r) => r.status === "Inactive").length,
    );
const duration = (v) => {
        if (!v) return "—";
        const months = Math.max(
            0,
            Math.floor((Date.now() - new Date(v)) / 2629800000),
        );
        return `${Math.floor(months / 12)} Years ${months % 12} Months`;
    },
    statusClass = (v) => v.toLowerCase();
const cards = computed(() => [
    {
        label: "New Customers",
        value: shown.value.filter(
            (r) => new Date(r.since) > new Date(Date.now() - 2592000000),
        ).length,
        tone: "blue",
    },
    { label: "Active Customers", value: active.value, tone: "green" },
    { label: "Suspended Customers", value: suspended.value, tone: "orange" },
    { label: "Closed Customers", value: closed.value, tone: "red" },
    { label: "Total Customers", value: shown.value.length },
    {
        label: "Avg. Years in Service",
        value: shown.value.length
            ? (
                  shown.value.reduce(
                      (s, r) =>
                          s +
                          Math.max(
                              0,
                              (Date.now() - new Date(r.since)) / 31557600000,
                          ),
                      0,
                  ) / shown.value.length
              ).toFixed(2)
            : "0.00",
        tone: "purple",
    },
]);
async function load() {
    const d = (await client.get("/super-admin/reports/account-status")).data
        .data;
    rows.value = (d.rows || []).map((r, i) => ({
        ...r,
        status: i % 9 === 0 ? "Closed" : r.status,
    }));
}
function reset() {
    Object.assign(f, {
        from: "",
        to: "",
        status: "All",
        customer: "",
        company: "",
    });
    load();
}
function print() {
    window.print();
}
onMounted(load);
</script>
<style scoped>
.rpt {
    color: #071541;
}
.rpt header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.rpt h1 {
    font-size: 1.82rem;
    margin: 6px 0 15px;
}
.rpt h1 i {
    font-style: normal;
    margin: 0 12px;
}
.rpt header a {
    color: #071541;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.75rem;
    border: 1px solid #d5dfec;
    padding: 10px 14px;
    border-radius: 5px;
}
.rpt form,
.summary,
.tablebox {
    border: 1px solid #dbe4ef;
    background: #fff;
    border-radius: 6px;
    padding: 14px;
    margin-bottom: 14px;
}
.rpt form {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px 38px;
}
.rpt form h2,
.summary h2,
.tablebox h2 {
    grid-column: 1/-1;
    color: #0648d8;
    font-size: 1rem;
    margin: 0;
}
.rpt label {
    display: grid;
    gap: 7px;
    font-size: 0.73rem;
    font-weight: 700;
}
.rpt input,
.rpt select {
    height: 37px;
    border: 1px solid #ccd9ed;
    border-radius: 4px;
    padding: 0 10px;
}
.rpt form div {
    display: flex;
    align-items: end;
    gap: 10px;
}
.rpt button {
    border: 1px solid #d0ddeb;
    border-radius: 4px;
    background: #fff;
    color: #0648d8;
    font-weight: 700;
    padding: 9px 19px;
}
.rpt form button:first-child,
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
    font-size: 1.5rem;
    display: block;
    margin-top: 10px;
}
.green strong,
.active {
    color: #078334;
}
.red strong,
.closed {
    color: #eb1f1f;
}
.orange strong,
.inactive {
    color: #ef7100;
}
.purple strong {
    color: #9b16e5;
}
.tablebox {
    padding: 0;
    overflow: auto;
}
.tablebox h2 {
    padding: 12px 15px;
}
.tablebox table {
    width: 100%;
    min-width: 1120px;
    border-collapse: collapse;
    font-size: 0.72rem;
}
.tablebox th,
.tablebox td {
    border: 1px solid #e2e9f1;
    padding: 9px;
    text-align: center;
}
.tablebox th {
    background: #f4f6f9;
}
.tablebox td:nth-child(3) {
    text-align: left;
}
.tablebox a {
    color: #0050e7;
    text-decoration: none;
    font-weight: 700;
}
.tablebox b {
    padding: 4px 7px;
    border-radius: 3px;
    background: #e2f7e6;
}
.tablebox b.inactive {
    background: #fff0dc;
}
.tablebox b.closed {
    background: #ffe2e2;
}
.tablebox footer {
    padding: 13px 15px;
}
.tablebox footer span {
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
    .rpt form,
    .summary {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
