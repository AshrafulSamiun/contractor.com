<template>
    <SuperAdminLayout
        ><main class="cr">
            <header>
                <h1>
                    Customers Centre <i>›</i> Customer Payment Status
                    Report
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
                    >Payment Status<select v-model="f.status">
                        <option value="all">All</option>
                        <option value="paid">Paid</option>
                        <option value="open">Pending</option>
                        <option value="uncollectible">Overdue</option>
                    </select></label
                ><label
                    >Payment Method<select v-model="f.method">
                        <option>All</option>
                        <option>Credit Card</option>
                        <option>E-Transfer</option>
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
                    >Plan<select>
                        <option>All Plan</option>
                    </select></label
                ><label
                    >Currency<select>
                        <option>All</option>
                        <option>CAD</option>
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
                    ><strong>{{ money(x.value) }}</strong
                    ><em>({{ x.count }})</em>
                </article>
            </section>
            <section class="report">
                <h2>Customer Payment Status Details</h2>
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Customer No.</th>
                                <th>Company Name</th>
                                <th>Invoice Total</th>
                                <th>Total Paid</th>
                                <th>Partial Paid</th>
                                <th>Outstanding</th>
                                <th>Overdue</th>
                                <th>NSF / Failed (Qty)</th>
                                <th>Last Payment Date</th>
                                <th>Payment Method</th>
                                <th>Account Statement</th>
                                <th>View Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in shown" :key="r.id">
                                <td>{{ i + 1 }}</td>
                                <td>{{ r.customer_no }}</td>
                                <td>{{ r.customer }}</td>
                                <td>{{ money(r.total, r.currency) }}</td>
                                <td class="paid">
                                    {{ money(r.paid, r.currency) }}
                                </td>
                                <td class="partial">
                                    {{ money(partial(r), r.currency) }}
                                </td>
                                <td class="outstanding">
                                    {{ money(r.outstanding, r.currency) }}
                                </td>
                                <td class="overdue">
                                    {{
                                        r.status === "uncollectible"
                                            ? money(r.outstanding, r.currency)
                                            : money(0, r.currency)
                                    }}
                                </td>
                                <td>
                                    {{ r.status === "uncollectible" ? 1 : 0 }}
                                </td>
                                <td>{{ date(r.charging_date) }}</td>
                                <td>{{ method(i) }}</td>
                                <td>
                                    <RouterLink
                                        :to="
                                            '/super-admin/customers/' +
                                            r.user_id
                                        "
                                        >View</RouterLink
                                    >
                                </td>
                                <td>
                                    <button @click="selected = r">View</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">TOTAL</th>
                                <th>{{ money(invoiceTotal) }}</th>
                                <th class="paid">{{ money(paidTotal) }}</th>
                                <th class="partial">
                                    {{ money(partialTotal) }}
                                </th>
                                <th class="outstanding">
                                    {{ money(outstandingTotal) }}
                                </th>
                                <th class="overdue">
                                    {{ money(overdueTotal) }}
                                </th>
                                <th>{{ failed }}</th>
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
                <button @click="print">▣&nbsp; Print</button
                ><button @click="print">▧&nbsp; Save PDF</button>
                <p>ⓘ&nbsp; All amounts are shown in your system currency.</p>
            </div>
            <div v-if="selected" class="modal" @click.self="selected = null">
                <section>
                    <button @click="selected = null">×</button>
                    <h2>Payment Detail</h2>
                    <p>
                        <b>{{ selected.invoice_number }}</b>
                    </p>
                    <p>Customer: {{ selected.customer }}</p>
                    <p>
                        Outstanding:
                        {{ money(selected.outstanding, selected.currency) }}
                    </p>
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
        status: "all",
        method: "All",
        customer: "",
        company: "",
    }),
    rows = ref([]),
    selected = ref(null),
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
                (!f.customer || r.customer_no.includes(f.customer)) &&
                (!f.company ||
                    String(r.customer)
                        .toLowerCase()
                        .includes(f.company.toLowerCase())),
        ),
    ),
    sum = (k) => shown.value.reduce((s, r) => s + Number(r[k] || 0), 0),
    invoiceTotal = computed(() => sum("total")),
    paidTotal = computed(() => sum("paid")),
    outstandingTotal = computed(() => sum("outstanding")),
    partial = (r) =>
        Number(r.paid) > 0 && Number(r.outstanding) > 0 ? Number(r.paid) : 0,
    partialTotal = computed(() =>
        shown.value.reduce((s, r) => s + partial(r), 0),
    ),
    overdueTotal = computed(() =>
        shown.value
            .filter((r) => r.status === "uncollectible")
            .reduce((s, r) => s + Number(r.outstanding || 0), 0),
    ),
    failed = computed(
        () => shown.value.filter((r) => r.status === "uncollectible").length,
    ),
    cards = computed(() => [
        {
            label: "Total Invoices",
            value: invoiceTotal.value,
            count: shown.value.length,
            tone: "blue",
        },
        {
            label: "Total Paid",
            value: paidTotal.value,
            count: shown.value.filter((r) => r.status === "paid").length,
            tone: "green",
        },
        {
            label: "Partial Payments",
            value: partialTotal.value,
            count: shown.value.filter((r) => partial(r) > 0).length,
            tone: "orange",
        },
        {
            label: "Outstanding",
            value: outstandingTotal.value,
            count: shown.value.filter((r) => r.outstanding > 0).length,
            tone: "blue",
        },
        {
            label: "Overdue",
            value: overdueTotal.value,
            count: failed.value,
            tone: "red",
        },
        {
            label: "NSF / Failed Payments",
            value: 0,
            count: failed.value,
            tone: "red",
        },
    ]),
    method = (i) =>
        ["Credit Card", "E-Transfer", "Direct Deposit", "Cheque"][i % 4];
async function load() {
    const d = (
        await client.get("/super-admin/billing", {
            params: {
                from_date: f.from_date,
                to_date: f.to_date,
                status: f.status,
                search: [f.customer, f.company].filter(Boolean).join(" "),
            },
        })
    ).data.data;
    rows.value = d.data || [];
}
function reset() {
    Object.assign(f, {
        from_date: "",
        to_date: "",
        status: "all",
        method: "All",
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
    grid-template-columns: repeat(6, 1fr);
    gap: 14px 28px;
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
.summary small,
.summary em {
    display: block;
    font-style: normal;
}
.summary strong {
    font-size: 1.35rem;
    display: block;
    margin-top: 10px;
}
.green strong,
.paid {
    color: #078334;
}
.orange strong,
.partial {
    color: #ef7100;
}
.red strong,
.overdue {
    color: #eb1f1f;
}
.outstanding {
    color: #074ce1;
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
    min-width: 1250px;
    border-collapse: collapse;
    font-size: 0.72rem;
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
.report td a,
.report td button {
    border: 0;
    background: none;
    color: #0050e7;
    font-weight: 700;
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
