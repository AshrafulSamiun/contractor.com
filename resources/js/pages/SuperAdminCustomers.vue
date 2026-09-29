<template>
    <SuperAdminLayout
        ><main class="cl-page">
            <header>
                <h1>Customers Centre <span>›</span> Customers List</h1>
                <div>
                    <button class="primary" @click="router.push('/register')">
                        ＋ New Customer</button
                    ><small>Last Updated: {{ formattedNow }}</small>
                </div>
            </header>
            <form class="filters" @submit.prevent="load(1)">
                <label
                    >ID No.<input
                        v-model="f.id"
                        placeholder="Enter ID No." /></label
                ><label
                    >Name<input
                        v-model="f.search"
                        placeholder="Enter company name" /></label
                ><label
                    >Phone<input
                        v-model="f.phone"
                        placeholder="Enter phone number" /></label
                ><label
                    >Sign Up Date
                    <div class="dates">
                        <input v-model="f.from_date" type="date" /><input
                            v-model="f.to_date"
                            type="date"
                        /></div></label
                ><label
                    >Country<select v-model="f.country">
                        <option value="">Select country</option>
                        <option v-for="x in countries" :key="x" :value="x">
                            {{ x }}
                        </option>
                    </select></label
                ><label
                    >State / Province<select v-model="f.state">
                        <option value="">Select state / province</option>
                        <option v-for="x in states" :key="x" :value="x">
                            {{ x }}
                        </option>
                    </select></label
                ><label
                    >City<select v-model="f.city">
                        <option value="">Select city</option>
                        <option v-for="x in cities" :key="x" :value="x">
                            {{ x }}
                        </option>
                    </select></label
                >
                <div class="actions">
                    <button class="primary">Search</button
                    ><button type="button" @click="reset">Clear</button>
                </div>
            </form>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ID No.</th>
                            <th>Company Name</th>
                            <th>Sign Up Date</th>
                            <th>Y. In Service</th>
                            <th>Country</th>
                            <th>State / Province</th>
                            <th>City</th>
                            <th>Plan</th>
                            <th>Monthly Pmt</th>
                            <th>Status</th>
                            <th>View Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(c, i) in rows" :key="c.id">
                            <td>{{ number(i) }}</td>
                            <td>
                                <RouterLink
                                    :to="`/super-admin/customers/${c.id}`"
                                    >{{ c.customer_number }}</RouterLink
                                >
                            </td>
                            <td>{{ c.company_name }}</td>
                            <td>{{ date(c.created_at) }}</td>
                            <td>{{ years(c.created_at) }}</td>
                            <td>{{ c.country || "—" }}</td>
                            <td>{{ c.state || "—" }}</td>
                            <td>{{ c.city || "—" }}</td>
                            <td>{{ c.plan }}</td>
                            <td>{{ money(c.monthly_payment) }}</td>
                            <td>
                                <b
                                    :class="['status', c.status.toLowerCase()]"
                                    >{{ c.status }}</b
                                >
                            </td>
                            <td>
                                <RouterLink
                                    :to="`/super-admin/customers/${c.id}`"
                                    >View Profile</RouterLink
                                >
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td class="empty" colspan="12">
                                No customers match the selected filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <footer>
                    <span
                        >Showing {{ rows.length }} of
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
        </main></SuperAdminLayout
    >
</template>
<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import SuperAdminLayout from "../components/SuperAdminLayout.vue";
import client from "../api/client";
const router = useRouter(),
    f = reactive({
        id: "",
        search: "",
        phone: "",
        from_date: "",
        to_date: "",
        country: "",
        state: "",
        city: "",
        status: "all",
    }),
    rows = ref([]),
    meta = ref({});
const unique = (k) =>
    computed(() => [...new Set(rows.value.map((x) => x[k]).filter(Boolean))]);
const countries = unique("country"),
    states = unique("state"),
    cities = unique("city");
const date = (v) =>
        v
            ? new Intl.DateTimeFormat("en-US", {
                  month: "short",
                  day: "2-digit",
                  year: "numeric",
              }).format(new Date(v + "T00:00:00"))
            : "—",
    money = (v) =>
        Number(v || 0).toLocaleString("en-US", {
            style: "currency",
            currency: "USD",
        }),
    years = (v) =>
        v
            ? `${Math.max(0, new Date().getFullYear() - new Date(v).getFullYear())} Y`
            : "—",
    number = (i) =>
        ((meta.value.current_page || 1) - 1) * (meta.value.per_page || 10) +
        i +
        1,
    formattedNow = new Intl.DateTimeFormat("en-US", {
        month: "long",
        day: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date());
async function load(page = 1) {
    const { data } = await client.get("/super-admin/customers", {
        params: { ...f, page },
    });
    rows.value = data.data?.data || [];
    meta.value = data.data || {};
}
function reset() {
    Object.assign(f, {
        id: "",
        search: "",
        phone: "",
        from_date: "",
        to_date: "",
        country: "",
        state: "",
        city: "",
        status: "all",
    });
    load();
}
onMounted(load);
</script>
<style>
.cl-page {
    color: #071541;
}
.cl-page header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin: 0 0 10px;
}
.cl-page h1 {
    margin: 8px 0;
    font-size: 2.2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
}
.cl-page h1 span {
    margin: 0 14px;
}
.cl-page header div {
    text-align: right;
}
.cl-page small {
    display: block;
    margin-top: 8px;
    font-size: 0.75rem;
}
.primary {
    height: 46px;
    padding: 0 25px;
    border: 1px solid #0746cc;
    border-radius: 5px;
    background: linear-gradient(100deg, #0636bd, #0753df);
    color: #fff;
    font-weight: 700;
}
.filters {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px 42px;
    padding: 27px;
    border: 1px solid #dbe4ef;
    border-radius: 6px;
    background: #fff;
    margin-bottom: 23px;
}
.filters label {
    display: grid;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 700;
}
.filters input,
.filters select {
    height: 46px;
    border: 1px solid #ccd9ed;
    border-radius: 6px;
    padding: 0 12px;
    color: #596b91;
    font: inherit;
}
.dates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.actions {
    display: flex;
    gap: 17px;
    align-items: end;
}
.actions button {
    min-width: 160px;
    height: 46px;
    border: 1px solid #c8d6ec;
    border-radius: 5px;
    background: #fff;
    color: #0645d4;
    font-weight: 700;
}
.actions .primary {
    border-color: #0746cc;
    color: #fff;
}
.table-wrap {
    overflow: auto;
    border: 1px solid #dce5ef;
    border-radius: 6px;
}
.cl-page table {
    width: 100%;
    min-width: 100%;
    border-collapse: collapse;
    background: #fff;
    font-size: 0.78rem;
}
.cl-page th,
.cl-page td {
    padding: 11px 12px;
    border: 1px solid #e4eaf2;
    text-align: center;
    white-space: nowrap;
}
.cl-page th {
    background: linear-gradient(#fafbfd, #f1f4f9);
    font-weight: 800;
}
.cl-page a {
    color: #0049e7;
    text-decoration: none;
    font-weight: 700;
}
.status {
    display: inline-block;
    padding: 4px 13px;
    border: 1px solid #a7dcaf;
    border-radius: 5px;
    background: #e9f9ed;
    color: #08732a;
    font-size: 0.72rem;
}
.status.inactive {
    border-color: #cbd3de;
    background: #f4f6f8;
    color: #546172;
}
.empty {
    height: 80px;
    color: #718096;
}
.cl-page footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 10px 25px;
    border: 1px solid #dce5ef;
    border-radius: 6px;
    background: #fff;
    font-size: 0.88rem;
}
.cl-page footer strong span {
    margin-left: 18px;
}
.cl-page footer div {
    display: flex;
    gap: 10px;
}
.cl-page footer button,
.cl-page footer b {
    display: grid;
    place-items: center;
    min-width: 38px;
    height: 36px;
    border: 1px solid #d7e0ed;
    border-radius: 5px;
    background: #fff;
}
.cl-page footer b {
    background: #0646ce;
    border-color: #0646ce;
    color: #fff;
}
@media (max-width: 1100px) {
    .filters {
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }
}
@media (max-width: 650px) {
    .cl-page header {
        display: block;
    }
    .cl-page header div {
        text-align: left;
    }
    .cl-page h1 {
        font-size: 1.65rem;
    }
    .filters {
        grid-template-columns: 1fr;
        padding: 18px;
    }
    .cl-page footer {
        flex-direction: column;
        align-items: start;
        gap: 12px;
    }
}
.filters label {
    min-width: 0;
}
.filters .dates {
    min-width: 0;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
}
.filters .dates input {
    min-width: 0;
    width: 100%;
}
.cl-page th:last-child,
.cl-page td:last-child {
    min-width: 140px;
    text-align: center;
}
.table-wrap {
    background: #fff;
}
.cl-page th {
    height: 55px;
    background: linear-gradient(100deg, #03205f, #0751c7);
    color: #fff;
}
.cl-page .table-wrap footer {
    margin-top: 0;
    padding: 20px 24px;
    border: 0;
    border-top: 1px solid #dce5ef;
    border-radius: 0;
    background: #fff;
}
.cl-page .table-wrap footer div {
    display: flex;
    gap: 10px;
}
.cl-page .table-wrap footer button,
.cl-page .table-wrap footer b {
    min-width: 40px;
    height: 40px;
    border-color: #d6e0ee;
    color: #071541;
}
.cl-page .table-wrap footer b {
    background: #0647d2;
    border-color: #0647d2;
    color: #fff;
}
@media (max-width: 650px) {
    .cl-page .table-wrap footer {
        flex-direction: column;
        align-items: start;
        gap: 12px;
    }
}
</style>
