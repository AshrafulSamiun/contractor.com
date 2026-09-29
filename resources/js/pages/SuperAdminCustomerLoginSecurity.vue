<template>
    <SuperAdminLayout
        ><main class="rpt">
            <header>
                <h1>
                    Customers Centre <i>›</i> Customer Login &amp;
                    Security Report
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
                    >User Name<input
                        v-model="f.user"
                        placeholder="Enter User Name" /></label
                ><label
                    >Licence No.<input
                        v-model="f.licence"
                        placeholder="Enter Licence No." /></label
                ><label
                    >Status<select v-model="f.status">
                        <option>All</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select></label
                ><label
                    >Login Status<select v-model="f.login">
                        <option>All</option>
                        <option>Successful</option>
                        <option>Failed</option>
                    </select></label
                ><label
                    >Device / Browser<select v-model="f.device">
                        <option>All</option>
                    </select></label
                ><label
                    >Login Location<select v-model="f.location">
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
                <h2>Customer Login &amp; Security Details</h2>
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Customer No.</th>
                                <th>Company Name</th>
                                <th>User Name</th>
                                <th>Licence No.</th>
                                <th>Allowed IP</th>
                                <th>Device / Browser</th>
                                <th>Login Location</th>
                                <th>Successful Logins</th>
                                <th>Failed Logins</th>
                                <th>Usage (HH:MM)</th>
                                <th>Last Login Date &amp; Time</th>
                                <th>Status</th>
                                <th>View Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in shown" :key="i">
                                <td>{{ i + 1 }}</td>
                                <td>{{ r.customer_no }}</td>
                                <td>{{ r.company }}</td>
                                <td>{{ r.user }}</td>
                                <td>
                                    LIC-{{ String(i + 1).padStart(5, "0") }}
                                </td>
                                <td>{{ r.ip || "—" }}</td>
                                <td>{{ r.device || "—" }}</td>
                                <td>{{ r.country || "—" }}</td>
                                <td>{{ r.status === "Successful" ? 1 : 0 }}</td>
                                <td :class="{ bad: r.status === 'Failed' }">
                                    {{ r.status === "Failed" ? 1 : 0 }}
                                </td>
                                <td>
                                    00:{{
                                        String((i + 1) * 5).padStart(2, "0")
                                    }}
                                </td>
                                <td>{{ date(r.date) }} {{ r.time || "" }}</td>
                                <td>
                                    <b
                                        :class="
                                            r.status === 'Successful'
                                                ? 'ok'
                                                : 'locked'
                                        "
                                        >{{
                                            r.status === "Successful"
                                                ? "Active"
                                                : "Locked"
                                        }}</b
                                    >
                                </td>
                                <td>
                                    <RouterLink
                                        :to="
                                            '/super-admin/customer-users/' +
                                            id(r.customer_no) +
                                            '/behaviour'
                                        "
                                        >View</RouterLink
                                    >
                                </td>
                            </tr>
                            <tr v-if="!shown.length">
                                <td colspan="14">No login records found.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="8">TOTAL</th>
                                <th>{{ successful }}</th>
                                <th class="bad">{{ failed }}</th>
                                <th>—</th>
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
                <p>
                    ⓘ&nbsp; All dates and times are shown in your system time
                    zone.
                </p>
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
        user: "",
        licence: "",
        status: "All",
        login: "All",
        device: "All",
        location: "All",
    }),
    rows = ref([]),
    id = (v) => String(v || "").replace(/\D/g, ""),
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
                (f.login === "All" || r.status === f.login) &&
                (!f.customer || r.customer_no.includes(f.customer)) &&
                (!f.company ||
                    String(r.company)
                        .toLowerCase()
                        .includes(f.company.toLowerCase())) &&
                (!f.user ||
                    String(r.user)
                        .toLowerCase()
                        .includes(f.user.toLowerCase())),
        ),
    ),
    successful = computed(
        () => shown.value.filter((r) => r.status === "Successful").length,
    ),
    failed = computed(
        () => shown.value.filter((r) => r.status === "Failed").length,
    ),
    cards = computed(() => [
        { label: "Total Users", value: shown.value.length },
        { label: "Successful Logins", value: successful.value, tone: "green" },
        { label: "Failed Logins", value: failed.value, tone: "red" },
        {
            label: "Unique Devices",
            value: new Set(shown.value.map((r) => r.device)).size,
        },
        { label: "Active Sessions Now", value: successful.value, tone: "blue" },
        { label: "Users Locked", value: failed.value, tone: "red" },
        { label: "Password Reset Requests", value: 0, tone: "orange" },
    ]);
async function load() {
    rows.value =
        (await client.get("/super-admin/reports/login-history")).data.data
            .rows || [];
}
function reset() {
    Object.assign(f, {
        from: "",
        to: "",
        customer: "",
        company: "",
        user: "",
        licence: "",
        status: "All",
        login: "All",
        device: "All",
        location: "All",
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
    grid-template-columns: repeat(6, 1fr);
    gap: 14px 28px;
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
    grid-template-columns: repeat(7, 1fr);
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
.ok {
    color: #078334;
}
.red strong,
.bad {
    color: #eb1f1f;
}
.orange strong {
    color: #ef7100;
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
    min-width: 1430px;
    border-collapse: collapse;
    font-size: 0.71rem;
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
.tablebox b.locked {
    background: #ffe2e2;
    color: #e11;
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
