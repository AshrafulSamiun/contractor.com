<template>
    <SuperAdminLayout
        ><main class="uw-page">
            <header>
                <h1>
                    Customers Centre <span>›</span> Customer / User Usage
                    Weekly
                </h1>
                <p>
                    Home　/　Customers Centre　/　6.3 Customer / User Usage
                    Weekly
                </p>
            </header>
            <form class="uw-filters" @submit.prevent="load(1)">
                <h2>Filters <button type="button">More Filters⌄</button></h2>
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
                    >User Licenses No.<input
                        v-model.number="f.licenses"
                        placeholder="Enter License No" /></label
                ><label
                    >Usage Date
                    <div class="dates">
                        <input v-model="f.from_date" type="date" /><i>–</i
                        ><input v-model="f.to_date" type="date" /></div
                ></label>
                <div class="uw-actions">
                    <button>Search</button
                    ><button type="button" @click="reset">Reset</button>
                </div>
            </form>
            <section class="uw-kpis">
                <article>
                    <span>Total Companies</span
                    ><strong>{{ meta.total || 0 }}</strong>
                </article>
                <article>
                    <span>Total User Licenses</span
                    ><strong>{{ licenseTotal }}</strong>
                </article>
                <article>
                    <span>Total Daily Usage (This Week)</span
                    ><strong
                        >{{ time(dailyTotal) }} <small>HH:MM</small></strong
                    >
                </article>
                <article>
                    <span>Last Week Usage (Total)</span
                    ><strong>{{ time(weekTotal) }} <small>HH:MM</small></strong>
                </article>
                <article>
                    <span>Last Month Usage (Total)</span
                    ><strong
                        >{{ time(monthTotal) }} <small>HH:MM</small></strong
                    >
                </article>
            </section>
            <div class="uw-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>License No.</th>
                            <th>Company ID</th>
                            <th>Company Name</th>
                            <th>Plan</th>
                            <th>User Licenses No.</th>
                            <th>Usage Date</th>
                            <th>Daily Usage<br />(HH-MM)</th>
                            <th>Last Week Usage<br />(HH-MM)</th>
                            <th>Last Month Usage<br />(HH-MM)</th>
                            <th>View Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in shown" :key="r.id">
                            <td>{{ number(i) }}</td>
                            <td>LIC-{{ String(r.id).padStart(6, "0") }}</td>
                            <td>{{ r.customer_no }}</td>
                            <td>{{ r.company }}</td>
                            <td>{{ r.plan }}</td>
                            <td>{{ r.qty_users }}</td>
                            <td>{{ date(r.usage_date) }}</td>
                            <td>{{ time(r.daily_usage) }}</td>
                            <td>{{ time(r.week_usage) }}</td>
                            <td>{{ time(r.month_usage) }}</td>
                            <td>
                                <RouterLink
                                    :to="`/super-admin/customer-users/${r.id}/behaviour`"
                                    >View Details</RouterLink
                                >
                            </td>
                        </tr>
                        <tr v-if="!shown.length">
                            <td colspan="11" class="empty">
                                No weekly usage records found.
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
        </main></SuperAdminLayout
    >
</template>
<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { RouterLink } from "vue-router";
import SuperAdminLayout from "../components/SuperAdminLayout.vue";
import client from "../api/client";
const f = reactive({
        id: "",
        search: "",
        plan: "",
        licenses: "",
        from_date: "",
        to_date: "",
    }),
    rows = ref([]),
    meta = ref({});
const plans = computed(() => [
        ...new Set(rows.value.map((x) => x.plan).filter(Boolean)),
    ]),
    shown = computed(() =>
        rows.value.filter(
            (r) => f.licenses === "" || r.qty_users === f.licenses,
        ),
    ),
    sum = (k) =>
        computed(() => shown.value.reduce((n, x) => n + Number(x[k] || 0), 0)),
    licenseTotal = sum("qty_users"),
    dailyTotal = sum("daily_usage"),
    weekTotal = sum("week_usage"),
    monthTotal = sum("month_usage");
const time = (v) => `${String(v || 0).padStart(2, "0")}-00`,
    date = (v) =>
        v
            ? new Intl.DateTimeFormat("en-US", {
                  month: "short",
                  day: "2-digit",
                  year: "numeric",
              }).format(new Date(v + "T00:00:00"))
            : "—",
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
        licenses: "",
        from_date: "",
        to_date: "",
    });
    load();
}
onMounted(load);
</script>
<style>
.uw-page {
    color: #071541;
}
.uw-page header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.uw-page h1 {
    font-size: 2rem;
    font-weight: 800;
    margin: 7px 0 17px;
}
.uw-page h1 span {
    margin: 0 13px;
}
.uw-page header p {
    color: #0645d0;
    font-size: 0.75rem;
}
.uw-filters {
    display: grid;
    grid-template-columns: 1fr 1.25fr 0.85fr 1fr 1.8fr auto;
    gap: 18px 23px;
    align-items: end;
    padding: 19px;
    border: 1px solid #dbe4ef;
    border-radius: 6px;
    background: #fff;
    margin-bottom: 20px;
}
.uw-filters h2 {
    grid-column: 1/-1;
    margin: 0;
    color: #0748cf;
    font-size: 1.1rem;
}
.uw-filters h2 button {
    float: right;
    border: 0;
    background: none;
    color: #0645d1;
    font-weight: 700;
}
.uw-filters label {
    display: grid;
    gap: 8px;
    font-size: 0.8rem;
    font-weight: 700;
}
.uw-filters input,
.uw-filters select {
    height: 43px;
    border: 1px solid #ccd9ed;
    border-radius: 5px;
    padding: 0 11px;
    color: #5b6d94;
    font: inherit;
}
.dates {
    display: grid;
    grid-template-columns: 1fr 12px 1fr;
    gap: 7px;
    align-items: center;
}
.dates i {
    text-align: center;
    font-style: normal;
}
.uw-actions {
    display: flex;
    gap: 15px;
}
.uw-actions button {
    height: 43px;
    min-width: 96px;
    border: 1px solid #d2ddec;
    border-radius: 5px;
    background: #fff;
    color: #071541;
    font-weight: 700;
}
.uw-actions button:first-child {
    background: #0648d5;
    border-color: #0648d5;
    color: #fff;
}
.uw-kpis {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.uw-kpis article {
    height: 118px;
    padding: 25px;
    border: 1px solid #dce5ef;
    border-radius: 6px;
    background: #fff;
}
.uw-kpis span {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
}
.uw-kpis strong {
    display: block;
    margin-top: 12px;
    font-size: 1.9rem;
}
.uw-kpis small {
    font-size: 0.7rem;
    color: #5f6f8e;
}
.uw-table-wrap {
    overflow: auto;
    border: 1px solid #dce5ef;
    border-radius: 6px;
    background: #fff;
}
.uw-table-wrap table {
    width: 100%;
    min-width: 1300px;
    border-collapse: collapse;
    font-size: 0.78rem;
}
.uw-table-wrap th,
.uw-table-wrap td {
    padding: 12px 10px;
    border: 1px solid #e3e9f2;
    text-align: center;
    white-space: nowrap;
}
.uw-table-wrap th {
    background: linear-gradient(#fafbfd, #f0f3f8);
    font-weight: 800;
}
.uw-table-wrap a {
    color: #0048e5;
    text-decoration: none;
    font-weight: 700;
}
.empty {
    height: 80px;
    color: #718096;
}
.uw-table-wrap footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 18px;
}
.uw-table-wrap footer div {
    display: flex;
    gap: 10px;
}
.uw-table-wrap footer button,
.uw-table-wrap footer b {
    display: grid;
    place-items: center;
    min-width: 36px;
    height: 36px;
    border: 1px solid #d7e0ed;
    border-radius: 5px;
    background: #fff;
}
.uw-table-wrap footer b {
    background: #0647d2;
    border-color: #0647d2;
    color: #fff;
}
@media (max-width: 1100px) {
    .uw-filters {
        grid-template-columns: repeat(3, 1fr);
    }
    .uw-kpis {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 650px) {
    .uw-page header {
        display: block;
    }
    .uw-page h1 {
        font-size: 1.6rem;
    }
    .uw-filters,
    .uw-kpis {
        grid-template-columns: 1fr;
    }
}
</style>
