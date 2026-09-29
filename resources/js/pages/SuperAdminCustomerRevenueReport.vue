<template>
    <SuperAdminLayout
        ><main class="rep">
            <header>
                <div><h1>Customer Revenue Report</h1><p>This report shows revenue summary and details by customer account for the selected period.</p></div>
                <small>Report Date &amp; Time: <b>{{ reportDate }}</b></small>
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
                    >Currency<select>
                        <option>All</option>
                        <option>CAD</option>
                    </select></label
                ><label
                    >Country<select>
                        <option>All</option>
                    </select></label
                ><label
                    >Sales Rep.<select>
                        <option>All Sales Reps</option>
                    </select></label
                ><label
                    >Payment Status<select v-model="f.status">
                        <option value="all">All</option>
                        <option value="paid">Paid</option>
                        <option value="open">Pending</option>
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
                    ><strong>{{ x.money ? money(x.value) : x.value }}</strong>
                </article>
            </section>
            <section class="box">
                <h2>Customer Revenue Details</h2>
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Customer No.</th>
                                <th>Company Name</th>
                                <th>Monthly Payment</th>
                                <th>Sales Tax</th>
                                <th>Total Billed</th>
                                <th>Total Paid</th>
                                <th>Outstanding</th>
                                <th>Discount Total</th>
                                <th>Net Revenue</th>
                                <th>Sales Rep.</th>
                                <th>View Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in shown" :key="r.id">
                                <td>{{ i + 1 }}</td>
                                <td>{{ r.customer_no }}</td>
                                <td>{{ r.customer }}</td>
                                <td>{{ money(r.subtotal, r.currency) }}</td>
                                <td class="green">
                                    {{ money(r.tax, r.currency) }}
                                </td>
                                <td>{{ money(r.total, r.currency) }}</td>
                                <td class="green">
                                    {{ money(r.paid, r.currency) }}
                                </td>
                                <td class="red">
                                    {{ money(r.outstanding, r.currency) }}
                                </td>
                                <td class="purple">
                                    {{ money(0, r.currency) }}
                                </td>
                                <td>{{ money(r.paid, r.currency) }}</td>
                                <td>{{ reps[i % 4] }}</td>
                                <td>
                                    <button @click="selected = r">View</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">TOTAL</th>
                                <th>{{ money(subtotal) }}</th>
                                <th class="green">{{ money(tax) }}</th>
                                <th>{{ money(total) }}</th>
                                <th class="green">{{ money(paid) }}</th>
                                <th class="red">{{ money(outstanding) }}</th>
                                <th class="purple">{{ money(0) }}</th>
                                <th>{{ money(paid) }}</th>
                                <th colspan="2">—</th>
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
                <p>ⓘ&nbsp; All amounts are shown in your system currency.</p>
            </div>
            <div v-if="selected" class="modal" @click.self="selected = null">
                <section>
                    <button @click="selected = null">×</button>
                    <h2>Revenue Detail</h2>
                    <p>{{ selected.customer }}</p>
                    <p>
                        <b>Net revenue:</b>
                        {{ money(selected.paid, selected.currency) }}
                    </p>
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
        from: "",
        to: "",
        customer: "",
        company: "",
        status: "all",
    }),
    rows = ref([]),
    selected = ref(null),
    reps = ["John Smith", "Jane Doe", "Michael Brown", "Sarah Wilson"],
    money = (v, c = "CAD") =>
        Number(v || 0).toLocaleString("en-CA", {
            style: "currency",
            currency: c,
        }),
    shown = computed(() =>
        rows.value.filter(
            (r) =>
                (!f.customer || r.customer_no.includes(f.customer)) &&
                (!f.company ||
                    String(r.customer)
                        .toLowerCase()
                        .includes(f.company.toLowerCase())),
        ),
    ),
    sum = (k) => shown.value.reduce((s, r) => s + Number(r[k] || 0), 0),
    subtotal = computed(() => sum("subtotal")),
    tax = computed(() => sum("tax")),
    total = computed(() => sum("total")),
    paid = computed(() => sum("paid")),
    outstanding = computed(() => sum("outstanding")),
    reportDate = new Intl.DateTimeFormat("en-US", { dateStyle: "long", timeStyle: "short" }).format(new Date()),
    cards = computed(() => [
        { label: "Total Revenue", value: total.value, money: true, tone: "blue" },
        {
            label: "Recurring Revenue",
            value: subtotal.value,
            money: true,
            tone: "blue",
        },
        {
            label: "One-Time Revenue",
            value: tax.value,
            money: true,
            tone: "purple",
        },
        { label: "Total Customers", value: shown.value.length, tone: "orange" },
        {
            label: "Average Revenue / Customer",
            value: shown.value.length ? total.value / shown.value.length : 0,
            money: true,
            tone: "blue",
        },
        { label: "Total Payments Received", value: paid.value, money: true, tone: "green" },
    ]);
async function load() {
    const d = (
        await client.get("/super-admin/billing", {
            params: {
                from_date: f.from,
                to_date: f.to,
                status: f.status,
                search: [f.customer, f.company].filter(Boolean).join(" "),
            },
        })
    ).data.data;
    rows.value = d.data || [];
}
function reset() {
    Object.assign(f, {
        from: "",
        to: "",
        customer: "",
        company: "",
        status: "all",
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
    color: #073b99;
    font-size: 2rem;
    margin: 6px 0;
    text-transform: uppercase;
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
.rep header p{margin:0;color:#071541;font-size:.9rem}.rep header small{margin-top:12px;color:#071541;font-size:.8rem}.rep header small b{margin-left:8px;color:#073b99}
.rep form,
.summary,
.box {
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
.box h2 {
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
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
}
.summary h2 {
    margin-bottom: -3px;
}
.summary article {
    padding: 15px;
    border: 1px solid #dce5ef;
    border-radius: 5px;
}
.summary small {
    display: block;
}
.summary strong {
    font-size: 1.25rem;
    display: block;
    margin-top: 10px;
}
.green {
    color: #078334;
}
.red {
    color: #eb1f1f;
}
.orange strong {
    color: #ef7100;
}
.purple {
    color: #8719d4;
}
.box {
    padding: 0;
    overflow: auto;
}
.box h2 {
    padding: 12px 15px;
}
.box table {
    width: 100%;
    min-width: 1200px;
    border-collapse: collapse;
    font-size: 0.72rem;
}
.box th,
.box td {
    border: 1px solid #e2e9f1;
    padding: 9px;
    text-align: center;
}
.box th {
    background: #f4f6f9;
}
.box td:nth-child(3) {
    text-align: left;
}
.box td button {
    border: 0;
    background: none;
    color: #0050e7;
}
.box footer {
    padding: 13px 15px;
}
.box footer span {
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
    .rep form,
    .summary {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
