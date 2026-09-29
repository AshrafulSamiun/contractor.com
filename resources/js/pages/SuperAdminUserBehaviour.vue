<template>
    <SuperAdminLayout
        ><main class="ub-page">
            <header>
                <h1>
                    Customers Centre <span>›</span> User Behaviour (Single User)
                </h1>
                <RouterLink to="/super-admin/customer-users"
                    >←　Back to Users List</RouterLink
                >
            </header>
            <template v-if="data.user"
                ><section class="ub-info">
                    <h2>User Information</h2>
                    <div>
                        <p>
                            <small>Company ID</small
                            ><b>{{ data.user.customer_no }}</b>
                        </p>
                        <p>
                            <small>Company Name</small
                            ><b>{{ data.user.company }}</b>
                        </p>
                        <p>
                            <small>User Name</small><b>{{ data.user.name }}</b>
                        </p>
                        <p>
                            <small>User ID / Email</small
                            ><b>{{ data.user.email }}</b>
                        </p>
                        <p>
                            <small>User Licence No.</small
                            ><b>{{ data.user.license_no }}</b>
                        </p>
                        <p>
                            <small>Allowed IP Address</small
                            ><b>{{
                                data.user.allowed_ip || "Not configured"
                            }}</b
                            ><em v-if="data.user.allowed_ip"
                                >(Only this IP is allowed)</em
                            >
                        </p>
                    </div>
                    <form @submit.prevent="load">
                        <label>Period</label
                        ><input v-model="f.from_date" type="date" /><i>–</i
                        ><input v-model="f.to_date" type="date" /><button>
                            Search
                        </button>
                    </form>
                </section>
                <div class="ub-body">
                    <section>
                        <div class="ub-cards">
                            <article>
                                <span>Total Logins</span
                                ><strong>{{
                                    data.summary?.total_logins || 0
                                }}</strong>
                            </article>
                            <article>
                                <span>Failed Login Attempts</span
                                ><strong class="red">{{
                                    data.summary?.failed_logins || 0
                                }}</strong>
                            </article>
                            <article>
                                <span>Total Usage (Selected Period)</span
                                ><strong
                                    >{{ duration(data.logs?.total || 0) }}
                                    <small>HH:MM</small></strong
                                >
                            </article>
                            <article>
                                <span>Last Login</span
                                ><strong class="date">{{
                                    date(data.summary?.last_login)
                                }}</strong>
                            </article>
                            <article>
                                <span>Last Logout</span
                                ><strong class="date">{{
                                    date(data.summary?.last_logout)
                                }}</strong>
                            </article>
                        </div>
                        <div class="ub-table">
                            <h2>User Activity Log</h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Login Date & Time</th>
                                        <th>Logout Date & Time</th>
                                        <th>Session Duration<br />(HH:MM)</th>
                                        <th>Fail Login<br />Attempts</th>
                                        <th>IP Address</th>
                                        <th>Login Location</th>
                                        <th>Device / Browser</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(r, i) in data.logs?.data || []"
                                        :key="i"
                                    >
                                        <td>{{ i + 1 }}</td>
                                        <td>{{ date(r.login_at) }}</td>
                                        <td>{{ date(r.logout_at) }}</td>
                                        <td>{{ r.duration || "—" }}</td>
                                        <td
                                            :class="
                                                r.failed_attempts ? 'red' : ''
                                            "
                                        >
                                            {{ r.failed_attempts }}
                                        </td>
                                        <td>{{ r.ip || "—" }}</td>
                                        <td>{{ r.location || "—" }}</td>
                                        <td>{{ r.device || "—" }}</td>
                                        <td>
                                            <b class="success">{{
                                                r.status || "Success"
                                            }}</b>
                                        </td>
                                        <td>{{ r.notes || "—" }}</td>
                                    </tr>
                                    <tr v-if="!(data.logs?.data || []).length">
                                        <td colspan="10" class="empty">
                                            No activity found in this period.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <footer>
                                <span>Showing {{ data.logs?.data?.length || 0 }} of {{ data.logs?.total || 0 }} records</span>
                                <div>
                                    <button :disabled="!data.logs?.prev_page_url" @click="load((data.logs?.current_page || 1) - 1)">‹</button>
                                    <b>{{ data.logs?.current_page || 1 }}</b>
                                    <button :disabled="!data.logs?.next_page_url" @click="load((data.logs?.current_page || 1) + 1)">›</button>
                                </div>
                            </footer>
                        </div>
                        <p class="notice">
                            ⓘ　Only the allowed IP address can be used to access
                            this account. Any login attempt from other IPs is
                            blocked.
                        </p>
                    </section>
                    <aside>
                        <Info
                            title="Security & Access"
                            :items="securityItems"
                        /><Info
                            title="Device Information"
                            :items="deviceItems"
                        />
                        <section class="actions">
                            <h2>Actions</h2>
                            <button>⟳　 Reset Password Auto</button
                            ><button>♙　 Reset Password Manual</button
                            ><button>View Full Audit Logs</button>
                        </section>
                    </aside>
                </div></template
            >
        </main></SuperAdminLayout
    >
</template>
<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import SuperAdminLayout from "../components/SuperAdminLayout.vue";
import client from "../api/client";
const route = useRoute(),
    router = useRouter(),
    data = ref({}),
    f = reactive({ from_date: "", to_date: "" });
const date = (v) =>
        v
            ? new Intl.DateTimeFormat("en-US", {
                  month: "short",
                  day: "2-digit",
                  year: "numeric",
                  hour: "2-digit",
                  minute: "2-digit",
              }).format(new Date(v))
            : "—",
    duration = (v) => `${String(v || 0).padStart(2, "0")}:00`;
const Info = defineComponent({
    props: { title: String, items: Array },
    setup: (p) => () =>
        h("section", { class: "side-info" }, [
            h("h2", p.title),
            ...p.items.map((x) =>
                h("p", [h("span", x[0]), h("b", x[1] || "—")]),
            ),
        ]),
});
const securityItems = computed(() => [
        ["Allowed IP Address", data.value.security?.allowed_ip],
        ["Current IP Address", data.value.security?.current_ip],
        ["IP Status", data.value.security?.ip_status],
        [
            "Unauthorized IP Login Attempts",
            data.value.security?.unauthorized_attempts,
        ],
        ["Account Lock Status", "Unlocked"],
        ["Two-Factor Authentication", data.value.security?.mfa_enabled],
        [
            "Password Last Changed",
            date(data.value.security?.password_changed_at),
        ],
    ]),
    deviceItems = computed(() => [
        [
            "Device / Browser",
            `${data.value.device?.name || "—"} / ${data.value.device?.browser || "—"}`,
        ],
        ["Device Name", data.value.device?.name],
        ["Device Type", data.value.device?.type],
        ["First Used", date(data.value.device?.first_used)],
    ]);
async function load(page = 1) {
    if (!route.params.id) {
        const { data: users } = await client.get("/super-admin/customer-users", {
            params: { page: 1 },
        });
        const firstUser = users.data?.data?.[0];
        if (firstUser?.id) {
            router.replace(`/super-admin/customer-users/${firstUser.id}/behaviour`);
            return;
        }
        data.value = {};
        return;
    }
    const { data: r } = await client.get(
        `/super-admin/customer-users/${route.params.id}/behaviour`,
        { params: { ...f, page } },
    );
    data.value = r.data || {};
}
onMounted(load);
</script>
<style>
.ub-page {
    color: #071541;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}
.ub-page > header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 6px 0 15px;
}
.ub-page h1 {
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
}
.ub-page h1 span {
    margin: 0 13px;
}
.ub-page > header a {
    border: 1px solid #d3deec;
    border-radius: 5px;
    padding: 10px 17px;
    color: #071541;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 700;
}
.ub-info {
    border: 1px solid #dbe4ef;
    border-radius: 6px;
    background: #fff;
}
.ub-info h2,
.ub-table h2,
.side-info h2,
.actions h2 {
    margin: 0;
    padding: 13px 15px;
    color: #0747d0;
    font-size: 1.05rem;
}
.ub-info > div {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    padding: 0 15px;
}
.ub-info p {
    border-right: 1px solid #e5eaf2;
    padding: 0 12px;
    margin: 4px 0 18px;
}
.ub-info small {
    display: block;
    font-size: 0.72rem;
}
.ub-info b {
    display: block;
    margin-top: 8px;
    font-size: 0.85rem;
}
.ub-info em {
    display: block;
    color: #087530;
    font-size: 0.68rem;
    font-style: normal;
}
.ub-info form {
    display: grid;
    grid-template-columns: auto minmax(0, 260px) auto minmax(0, 260px) auto;
    gap: 13px;
    align-items: center;
    border-top: 1px solid #e2e8f0;
    padding: 16px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
.ub-info input {
    height: 38px;
    border: 1px solid #ccd9ed;
    border-radius: 5px;
    padding: 0 12px;
    min-width: 0;
    width: 100%;
    box-sizing: border-box;
}
.ub-info button {
    height: 38px;
    border: 0;
    border-radius: 5px;
    background: #0647d2;
    color: #fff;
    padding: 0 26px;
}
.ub-body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 278px;
    gap: 18px;
    margin-top: 16px;
}
.ub-cards {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
}
.ub-cards article,
.side-info,
.actions {
    border: 1px solid #dce5ef;
    border-radius: 6px;
    background: #fff;
}
.ub-cards article {
    padding: 18px;
}
.ub-cards span {
    font-size: 0.75rem;
}
.ub-cards strong {
    display: block;
    font-size: 1.55rem;
    margin-top: 12px;
}
.ub-cards .date {
    font-size: 0.85rem;
}
.red {
    color: #f11;
}
.ub-table {
    border: 1px solid #dce5ef;
    border-radius: 6px;
    background: #fff;
    margin-top: 15px;
    overflow: auto;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
.ub-table table {
    width: 100%;
    min-width: 0;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 0.7rem;
}
.ub-table th,
.ub-table td {
    padding: 10px 7px;
    border: 1px solid #e3e9f2;
    text-align: center;
    white-space: normal;
    overflow-wrap: anywhere;
}
.ub-table th {
    height: 55px;
    background: linear-gradient(100deg, #03205f, #0751c7);
    color: #fff;
}
.success {
    display: inline-block;
    background: #dff7e5;
    color: #08732a;
    padding: 3px 5px;
}
.ub-table footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-top: 1px solid #dce5ef;
    font-size: 0.75rem;
}
.ub-table footer div{display:flex;gap:10px}.ub-table footer button,.ub-table footer b{display:grid;place-items:center;min-width:40px;height:40px;border:1px solid #d6e0ee;border-radius:6px;background:#fff;color:#071541}.ub-table footer b{background:#0647d2;border-color:#0647d2;color:#fff}
.notice {
    border: 1px solid #dce5ef;
    border-radius: 5px;
    background: #f5f8fd;
    padding: 12px;
    font-size: 0.75rem;
}
.side-info {
    margin-bottom: 14px;
}
.side-info p {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin: 0;
    padding: 9px 15px;
    border-top: 1px solid #e6ebf2;
    font-size: 0.7rem;
}
.side-info b {
    text-align: right;
}
.actions button {
    display: block;
    width: calc(100% - 30px);
    margin: 8px 15px;
    border: 1px solid #9fb4d8;
    background: #fff;
    border-radius: 4px;
    padding: 8px;
    color: #071541;
    font-weight: 700;
}
.actions button:last-child {
    background: #0647d2;
    color: #fff;
    border-color: #0647d2;
}
@media (max-width: 1100px) {
    .ub-body {
        grid-template-columns: 1fr;
    }
    .ub-info > div {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 700px) {
    .ub-page > header {
        display: block;
    }
    .ub-page h1 {
        font-size: 1.6rem;
    }
    .ub-info > div,
    .ub-cards {
        grid-template-columns: 1fr;
    }
    .ub-info form {
        grid-template-columns: 1fr;
    }
    .ub-table footer{flex-direction:column;align-items:flex-start;gap:12px}
}
</style>
