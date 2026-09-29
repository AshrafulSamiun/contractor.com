<template>
    <div class="pm-register-shell">
        <div class="pm-register-layout">
            <aside
                class="pm-register-aside"
                :style="{ '--pm-register-hero': `url(${heroImage})` }"
            >
                <div class="pm-register-aside-overlay"></div>
                <div class="pm-register-aside-inner">
                    <div class="pm-register-lang">
                        <LanguageSwitcher compact />
                    </div>

                    <RouterLink
                        to="/"
                        class="pm-register-brand"
                        aria-label="Go to home"
                    >
                        <img
                            :src="logoWhite"
                            alt="Contractor.com logo"
                            class="pm-register-brand-logo"
                        />
                        <span>Contractor.com</span>
                    </RouterLink>

                    <div class="pm-register-copy">
                        <p class="pm-register-kicker">Welcome To</p>
                        <h1>Contractor.com</h1>
                        <p class="pm-register-description">
                            The complete contractor business management platform
                            for estimates, quotations, jobs, customers,
                            vehicles, invoicing, and accounting.
                        </p>
                    </div>

                    <div class="pm-register-feature-list">
                        <article
                            v-for="feature in featureCards"
                            :key="feature.title"
                            class="pm-register-feature"
                        >
                            <div class="pm-register-feature-icon">
                                {{ feature.icon }}
                            </div>
                            <div>
                                <h2>{{ feature.title }}</h2>
                                <p>{{ feature.text }}</p>
                            </div>
                        </article>
                    </div>

                    <div class="pm-register-support">
                        <div class="pm-register-feature-icon">?</div>
                        <div>
                            <strong>Dedicated Contractor Support</strong>
                            <p>
                                Our support team is here to assist you every
                                step of the way.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="pm-register-main">
                <div class="pm-register-topbar">
                    <span>Already have an account?</span>
                    <RouterLink to="/login">Log In</RouterLink>
                </div>

                <section class="pm-register-panel">
                    <header class="pm-register-header">
                        <h2>Sign Up</h2>
                        <p>
                            Create your account to get started with
                            Contractor.com
                        </p>
                    </header>

                    <ol
                        class="pm-register-steps"
                        aria-label="Registration steps"
                    >
                        <li
                            v-for="(step, index) in steps"
                            :key="step.label"
                            :class="[
                                'pm-register-step',
                                { active: index === 0, upcoming: index > 0 },
                            ]"
                        >
                            <span class="pm-register-step-index">{{
                                step.number
                            }}</span>
                            <span class="pm-register-step-label">{{
                                step.label
                            }}</span>
                        </li>
                    </ol>

                    <div
                        v-if="summaryErrors.length"
                        class="pm-register-summary"
                    >
                        <strong
                            >Please fix the following ({{
                                summaryErrors.length
                            }}):</strong
                        >
                        <ul>
                            <li v-for="(item, idx) in summaryErrors" :key="idx">
                                {{ item }}
                            </li>
                        </ul>
                    </div>

                    <form
                        class="pm-register-form"
                        @submit.prevent="onRegister"
                        novalidate
                    >
                        <div class="pm-register-grid pm-register-grid-top">
                            <section class="pm-register-card">
                                <div class="pm-register-card-head">
                                    <div class="pm-register-card-icon">1</div>
                                    <div>
                                        <h3>Account Information</h3>
                                        <p>
                                            Enter your personal details and
                                            account reference.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="pm-register-fields pm-register-fields-inline"
                                >
                                    <label
                                        class="pm-register-field pm-register-field-inline pm-register-field-full"
                                    >
                                        <span class="pm-field-label"
                                            >Account No</span
                                        >
                                        <div class="pm-register-field-control">
                                            <input
                                                v-model="form.account_number"
                                                class="form-control"
                                                placeholder="Enter account number if you already have one"
                                            />
                                        </div>
                                        <small
                                            class="pm-register-hint pm-register-hint-inline"
                                        >
                                            Leave this blank if you do not have
                                            an account number yet. The next step
                                            will be creating your account.
                                        </small>
                                    </label>

                                    <label
                                        class="pm-register-field pm-register-field-inline pm-register-field-full"
                                    >
                                        <span class="pm-field-label"
                                            >Your Legal Name<span
                                                class="pm-required"
                                                >*</span
                                            ></span
                                        >
                                        <div class="pm-register-field-control">
                                            <input
                                                v-model="form.name"
                                                class="form-control"
                                                :class="{
                                                    'pm-input-error':
                                                        errors.name,
                                                }"
                                                placeholder="Enter your legal name"
                                            />
                                            <span
                                                v-if="errors.name"
                                                class="pm-inline-error"
                                                >{{ errors.name[0] }}</span
                                            >
                                        </div>
                                    </label>

                                    <label
                                        class="pm-register-field pm-register-field-inline pm-register-field-full"
                                    >
                                        <span class="pm-field-label"
                                            >Your Position</span
                                        >
                                        <div class="pm-register-field-control">
                                            <input
                                                v-model="form.position"
                                                class="form-control"
                                                placeholder="Enter your position"
                                            />
                                        </div>
                                    </label>

                                    <label
                                        class="pm-register-field pm-register-field-inline pm-register-field-full"
                                    >
                                        <span class="pm-field-label"
                                            >User Name</span
                                        >
                                        <div class="pm-register-field-control">
                                            <input
                                                v-model="form.username"
                                                class="form-control"
                                                :class="{
                                                    'pm-input-error':
                                                        errors.username,
                                                }"
                                                placeholder="Enter user name"
                                            />
                                            <span
                                                v-if="errors.username"
                                                class="pm-inline-error"
                                                >{{ errors.username[0] }}</span
                                            >
                                        </div>
                                    </label>
                                </div>
                            </section>

                            <section class="pm-register-card">
                                <div class="pm-register-card-head">
                                    <div class="pm-register-card-icon">2</div>
                                    <div>
                                        <h3>Company Information</h3>
                                        <p>
                                            Tell us about your business and how
                                            to reach you.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="pm-register-fields pm-register-fields-inline"
                                >
                                    <label
                                        class="pm-register-field pm-register-field-inline pm-register-field-full"
                                    >
                                        <span class="pm-field-label"
                                            >Company Name<span
                                                class="pm-required"
                                                >*</span
                                            ></span
                                        >
                                        <div class="pm-register-field-control">
                                            <input
                                                v-model="form.company"
                                                class="form-control"
                                                :class="{
                                                    'pm-input-error':
                                                        errors.company,
                                                }"
                                                placeholder="Enter company name"
                                            />
                                            <span
                                                v-if="errors.company"
                                                class="pm-inline-error"
                                                >{{ errors.company[0] }}</span
                                            >
                                        </div>
                                    </label>

                                    <label
                                        class="pm-register-field pm-register-field-inline pm-register-field-full"
                                    >
                                        <span class="pm-field-label"
                                            >Business Email<span
                                                class="pm-required"
                                                >*</span
                                            ></span
                                        >
                                        <div class="pm-register-field-control">
                                            <input
                                                v-model="form.email"
                                                class="form-control"
                                                :class="{
                                                    'pm-input-error':
                                                        errors.email,
                                                }"
                                                placeholder="Enter business email"
                                            />
                                            <span
                                                v-if="errors.email"
                                                class="pm-inline-error"
                                                >{{ errors.email[0] }}</span
                                            >
                                        </div>
                                    </label>

                                    <label
                                        class="pm-register-field pm-register-field-inline pm-register-field-full"
                                    >
                                        <span class="pm-field-label"
                                            >Business Phone<span
                                                class="pm-required"
                                                >*</span
                                            ></span
                                        >
                                        <div class="pm-register-field-control">
                                            <input
                                                v-model="form.phoneNo"
                                                class="form-control"
                                                :class="{
                                                    'pm-input-error':
                                                        errors.phoneNo,
                                                }"
                                                placeholder="Enter business phone in international format"
                                            />
                                            <small class="pm-register-hint"
                                                >Use format like
                                                `+14165550100`.</small
                                            >
                                            <span
                                                v-if="errors.phoneNo"
                                                class="pm-inline-error"
                                                >{{ errors.phoneNo[0] }}</span
                                            >
                                        </div>
                                    </label>
                                </div>
                            </section>
                        </div>

                        <section class="pm-register-card">
                            <div class="pm-register-card-head">
                                <div class="pm-register-card-icon">3</div>
                                <div>
                                    <h3>Business Address</h3>
                                    <p>
                                        Add the address that will be used for
                                        your account records.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="pm-register-fields pm-register-fields-address"
                            >
                                <label
                                    class="pm-register-field pm-register-field-span-2"
                                >
                                    <span class="pm-field-label"
                                        >Company Address</span
                                    >
                                    <input
                                        v-model="form.address"
                                        class="form-control"
                                        placeholder="Enter company address"
                                    />
                                </label>

                                <label class="pm-register-field">
                                    <span class="pm-field-label"
                                        >Country<span class="pm-required"
                                            >*</span
                                        ></span
                                    >
                                    <select
                                        v-model.number="form.country_id"
                                        class="form-control"
                                        :class="{
                                            'pm-input-error': errors.country_id,
                                        }"
                                    >
                                        <option value="">Select country</option>
                                        <option
                                            v-for="c in countries"
                                            :key="c.id"
                                            :value="c.id"
                                        >
                                            {{ c.country_name }}
                                        </option>
                                    </select>
                                    <span
                                        v-if="errors.country_id"
                                        class="pm-inline-error"
                                        >{{ errors.country_id[0] }}</span
                                    >
                                </label>

                                <label class="pm-register-field">
                                    <span class="pm-field-label"
                                        >State / Province</span
                                    >
                                    <input
                                        v-model="form.state"
                                        class="form-control"
                                        placeholder="Enter state or province"
                                    />
                                </label>

                                <label class="pm-register-field">
                                    <span class="pm-field-label">City</span>
                                    <input
                                        v-model="form.city"
                                        class="form-control"
                                        placeholder="Enter city"
                                    />
                                </label>

                                <label class="pm-register-field">
                                    <span class="pm-field-label"
                                        >Zip / Postal Code<span
                                            class="pm-required"
                                            >*</span
                                        ></span
                                    >
                                    <input
                                        v-model="form.zip"
                                        class="form-control"
                                        :class="{
                                            'pm-input-error': errors.zip,
                                        }"
                                        placeholder="Enter zip or postal code"
                                    />
                                    <span
                                        v-if="errors.zip"
                                        class="pm-inline-error"
                                        >{{ errors.zip[0] }}</span
                                    >
                                </label>
                            </div>
                        </section>

                        <div class="pm-register-grid">
                            <section class="pm-register-card">
                                <div class="pm-register-card-head">
                                    <div class="pm-register-card-icon">4</div>
                                    <div>
                                        <h3>Verification</h3>
                                        <p>
                                            Choose how you want to receive your
                                            verification code.
                                        </p>
                                    </div>
                                </div>

                                <div class="pm-register-verify-grid">
                                    <label
                                        :class="[
                                            'pm-register-option',
                                            {
                                                selected:
                                                    form.verifyVia === 'email',
                                            },
                                        ]"
                                        for="verify-email"
                                    >
                                        <input
                                            id="verify-email"
                                            v-model="form.verifyVia"
                                            type="radio"
                                            value="email"
                                            name="verifyVia"
                                        />
                                        <div>
                                            <strong>Email Verification</strong>
                                            <p>
                                                We will send a code and secure
                                                verification link to your
                                                business email.
                                            </p>
                                        </div>
                                    </label>

                                    <label
                                        :class="[
                                            'pm-register-option',
                                            {
                                                selected:
                                                    form.verifyVia === 'sms',
                                            },
                                        ]"
                                        for="verify-sms"
                                    >
                                        <input
                                            id="verify-sms"
                                            v-model="form.verifyVia"
                                            type="radio"
                                            value="sms"
                                            name="verifyVia"
                                        />
                                        <div>
                                            <strong>Phone Verification</strong>
                                            <p>
                                                We will send a verification code
                                                to your mobile phone by SMS.
                                            </p>
                                        </div>
                                    </label>
                                </div>

                                <div class="pm-register-plan-note">
                                    <strong>Next step:</strong>
                                    <span v-if="!hasAccountNumber"
                                        >After sign up, you will continue to
                                        create your account setup.</span
                                    >
                                    <span v-else
                                        >After sign up, we will continue with
                                        verification and account review.</span
                                    >
                                </div>
                            </section>

                            <section class="pm-register-card">
                                <div class="pm-register-card-head">
                                    <div class="pm-register-card-icon">5</div>
                                    <div>
                                        <h3>Security &amp; Access</h3>
                                        <p>
                                            Set your login password and complete
                                            the security check.
                                        </p>
                                    </div>
                                </div>

                                <div class="pm-register-fields">
                                    <label class="pm-register-field">
                                        <span class="pm-field-label"
                                            >Password<span class="pm-required"
                                                >*</span
                                            ></span
                                        >
                                        <input
                                            v-model="form.password"
                                            type="password"
                                            class="form-control"
                                            :class="{
                                                'pm-input-error':
                                                    errors.password,
                                            }"
                                            placeholder="Enter password"
                                        />
                                        <small class="pm-register-hint"
                                            >Password required with minimum 8
                                            characters.</small
                                        >
                                        <span
                                            v-if="errors.password"
                                            class="pm-inline-error"
                                            >{{ errors.password[0] }}</span
                                        >
                                    </label>

                                    <label class="pm-register-field">
                                        <span class="pm-field-label"
                                            >Confirm Password<span
                                                class="pm-required"
                                                >*</span
                                            ></span
                                        >
                                        <input
                                            v-model="form.password_confirmation"
                                            type="password"
                                            class="form-control"
                                            :class="{
                                                'pm-input-error':
                                                    errors.password_confirmation,
                                            }"
                                            placeholder="Confirm password"
                                        />
                                        <span
                                            v-if="errors.password_confirmation"
                                            class="pm-inline-error"
                                        >
                                            {{
                                                errors.password_confirmation[0]
                                            }}
                                        </span>
                                    </label>
                                </div>

                                <div class="pm-register-captcha">
                                    <ImageCaptcha
                                        ref="captchaRef"
                                        @change="onCaptchaChange"
                                    />
                                    <span
                                        v-if="errors.captcha_value"
                                        class="pm-inline-error"
                                        >{{ errors.captcha_value[0] }}</span
                                    >
                                </div>
                            </section>
                        </div>

                        <section class="pm-register-card">
                            <div class="pm-register-card-head">
                                <div class="pm-register-card-icon">6</div>
                                <div>
                                    <h3>Agreement</h3>
                                    <p>Please accept the terms to continue.</p>
                                </div>
                            </div>

                            <div class="pm-register-agreements">
                                <label class="pm-register-check">
                                    <input
                                        v-model="form.agree"
                                        type="checkbox"
                                        id="agreeTerms"
                                    />
                                    <span>
                                        I agree to the
                                        <a
                                            :href="termsUrl"
                                            target="_blank"
                                            rel="noopener"
                                            >Terms &amp; Conditions</a
                                        >
                                    </span>
                                </label>
                                <span
                                    v-if="errors.agree"
                                    class="pm-inline-error"
                                    >{{ errors.agree[0] }}</span
                                >
                            </div>
                        </section>

                        <div v-if="error" class="pm-register-error">
                            {{ error }}
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary pm-register-submit"
                            :disabled="loading"
                        >
                            {{ loading ? t("common.loading") : submitLabel }}
                        </button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</template>

<script setup>
import logoWhite from "../assets/logo-white.png";
import signupImage from "../assets/signup.png";
import { reactive, ref, computed, onMounted } from "vue";
import { useRouter, useRoute, RouterLink } from "vue-router";
import { useI18n } from "vue-i18n";
import { register, saveToken } from "../api/auth";
import { normalizeApiError } from "../api/client";
import { setFlash } from "../store/flash";
import { FLASH } from "../config/messages";
import { setUser } from "../store/auth";
import ImageCaptcha from "../components/ImageCaptcha.vue";
import LanguageSwitcher from "../components/LanguageSwitcher.vue";
import client from "../api/client";

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const loading = ref(false);
const error = ref("");
const errors = ref({});
const captchaRef = ref(null);
const heroImage = signupImage;
const termsUrl = `${import.meta.env.BASE_URL}terms`;

const featureCards = [
    {
        icon: "E",
        title: "Estimates & Quotations",
        text: "Create professional estimates and quotations quickly with accurate pricing and detailed breakdowns.",
    },
    {
        icon: "C",
        title: "Customer Management",
        text: "Manage customers, contacts, project history, communication, and follow-ups.",
    },
    {
        icon: "J",
        title: "Job & Project Management",
        text: "Track jobs, schedules, work orders, project progress, and team assignments.",
    },
    {
        icon: "V",
        title: "Vehicle Management",
        text: "Monitor vehicles, maintenance, mileage, fuel expenses, insurance, and drivers.",
    },
    {
        icon: "A",
        title: "Accounting & Invoicing",
        text: "Manage invoices, payments, expenses, payroll, taxes, and financial reports.",
    },
];

const steps = computed(() => [
    { number: 1, label: "Account & Company" },
    { number: 2, label: "Verification" },
    { number: 3, label: "Final Step" },
    {
        number: 4,
        label: hasAccountNumber.value ? "Account Review" : "Create Account",
    },
    { number: 5, label: "Login" },
]);

const form = reactive({
    account_number: "",
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    username: "",
    phoneNo: "",
    company: "",
    country_id: "",
    zip: "",
    verifyVia: "email",
    plan: "standard",
    agree: false,
    position: "",
    address: "",
    state: "",
    city: "",
});
const captchaKey = ref("");
const captchaValue = ref("");
const countries = ref([]);

const hasAccountNumber = computed(
    () => String(form.account_number || "").trim().length > 0,
);

const submitLabel = computed(() => {
    if (hasAccountNumber.value) return "Complete Sign Up";
    return "Continue to Create Account";
});

const normalizePhoneNumber = (value) => {
    const raw = String(value || "").trim();
    if (!raw) return "";

    let normalized = raw.replace(/[^\d+]/g, "");
    if (!normalized) return "";

    if (normalized.startsWith("00")) {
        normalized = `+${normalized.slice(2)}`;
    }

    if (normalized.startsWith("+")) {
        normalized = `+${normalized.slice(1).replace(/\D/g, "")}`;
    } else {
        normalized = `+${normalized.replace(/\D/g, "")}`;
    }

    return normalized === "+" ? "" : normalized;
};

const isE164PhoneNumber = (value) =>
    /^\+[1-9]\d{7,14}$/.test(String(value || "").trim());

const summaryErrors = computed(() => {
    const list = [];
    for (const key of Object.keys(errors.value || {})) {
        const msg = errors.value[key]?.[0];
        if (msg) list.push(msg);
    }
    return list;
});

const loadCountries = async () => {
    try {
        const { data } = await client.get("/countries");
        if (data?.success) {
            countries.value = data.data || [];
        }
    } catch {
        countries.value = [];
    }
};

loadCountries();

onMounted(() => {
    const plan = route.query.plan;
    if (plan === "basic" || plan === "standard" || plan === "enterprise") {
        form.plan = plan;
    }
});

const persistSignupIntent = () => {
    if (hasAccountNumber.value) {
        sessionStorage.setItem(
            "pm_signup_account_number",
            String(form.account_number).trim(),
        );
        sessionStorage.setItem("pm_signup_next_step", "account-review");
        return;
    }

    sessionStorage.removeItem("pm_signup_account_number");
    sessionStorage.setItem("pm_signup_next_step", "create-account");
};

const onRegister = async () => {
    error.value = "";
    errors.value = {};
    if (!form.name) errors.value.name = ["Full name is required."];
    if (!form.company) errors.value.company = ["Company name is required."];
    if (!form.email) {
        errors.value.email = ["Email is required."];
    } else {
        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);
        if (!emailOk)
            errors.value.email = ["Please enter a valid email address."];
    }
    if (!form.country_id) errors.value.country_id = ["Country is required."];
    if (!form.zip) errors.value.zip = ["Zip / Postal Code is required."];
    form.phoneNo = normalizePhoneNumber(form.phoneNo);
    if (!form.phoneNo) {
        errors.value.phoneNo = ["Phone number is required."];
    } else if (!isE164PhoneNumber(form.phoneNo)) {
        errors.value.phoneNo = [
            "Phone number must be in E.164 format (e.g. +14165550100).",
        ];
    }
    if (!form.password) errors.value.password = ["Password is required."];
    if (!form.password_confirmation) {
        errors.value.password_confirmation = ["Confirm your password."];
    } else if (form.password !== form.password_confirmation) {
        errors.value.password_confirmation = [
            "Password confirmation does not match.",
        ];
    }
    if (!captchaKey.value || !captchaValue.value) {
        errors.value.captcha_value = ["Please enter the captcha."];
    }
    if (!form.agree) {
        errors.value.agree = ["You must agree to the terms."];
    }
    if (Object.keys(errors.value).length) return;

    persistSignupIntent();
    loading.value = true;
    try {
        const payload = {
            name: form.name,
            username: form.username,
            email: form.email,
            phoneNo: form.phoneNo,
            password: form.password,
            password_confirmation: form.password_confirmation,
            company: form.company,
            country_id: form.country_id,
            zip: form.zip,
            account_number: form.account_number,
            position: form.position,
            address: form.address,
            city: form.city,
            state: form.state,
            verifyVia: form.verifyVia,
            plan: form.plan,
            captcha_key: captchaKey.value,
            captcha_value: captchaValue.value,
        };
        const res = await register(payload);
        if (res?.verification_required) {
            localStorage.setItem(
                "pm_verify_session",
                res.data?.verify_session || "",
            );
            localStorage.setItem(
                "pm_verify_via",
                res.data?.verify_via || "email",
            );
            router.push("/verify");
            return;
        }
        if (res?.success && res.data?.token) {
            saveToken(res.data.token);
            if (res.data.user) setUser(res.data.user);
            setFlash(FLASH.REGISTER_SUCCESS, "success", 3500);
            if (!res.data.user?.account_setup_completed_at) {
                router.push("/account-setup");
            } else {
                router.push("/");
            }
        } else {
            error.value = "Registration failed.";
        }
    } catch (e) {
        const parsed = normalizeApiError(e);
        errors.value = parsed.errors || {};
        if (e?.response?.status === 429) {
            error.value =
                "Too many attempts. Please wait a minute and try again.";
        } else {
            error.value = parsed.message || "Registration failed.";
        }
        if (errors.value?.captcha_value) {
            captchaRef.value?.refresh?.();
        }
    } finally {
        loading.value = false;
    }
};

const onCaptchaChange = (payload) => {
    captchaKey.value = payload.captcha_key;
    captchaValue.value = payload.captcha_value;
    errors.value.captcha_value = null;
};
</script>

<style scoped>
.pm-register-shell {
    min-height: 100vh;
    background:
        radial-gradient(
            circle at top left,
            rgba(37, 99, 235, 0.1),
            transparent 32%
        ),
        linear-gradient(180deg, #eff4fb 0%, #f8fbff 100%);
}

.pm-register-layout {
    display: grid;
    grid-template-columns: minmax(320px, 470px) minmax(0, 1fr);
    min-height: 100vh;
}

.pm-register-aside {
    position: relative;
    overflow: hidden;
    background:
        linear-gradient(180deg, rgba(7, 26, 68, 0.84), rgba(7, 26, 68, 0.94)),
        var(--pm-register-hero) center/cover no-repeat;
    color: #ffffff;
}

.pm-register-aside-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(11, 29, 70, 0.26), rgba(7, 26, 68, 0.75)),
        radial-gradient(
            circle at top right,
            rgba(96, 165, 250, 0.28),
            transparent 38%
        );
}

.pm-register-aside-inner {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    min-height: 100%;
    padding: 24px 28px 22px;
    gap: 22px;
}

.pm-register-lang {
    display: flex;
    justify-content: flex-end;
}

.pm-register-brand {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    color: #ffffff;
    font-size: 1rem;
    font-weight: 700;
}

.pm-register-brand:hover {
    color: #ffffff;
}

.pm-register-brand-logo {
    width: 74px;
    height: 46px;
    object-fit: contain;
}

.pm-register-copy {
    max-width: 292px;
}

.pm-register-kicker {
    margin: 0 0 6px;
    font-size: 0.95rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.95);
}

.pm-register-copy h1 {
    margin: 0 0 10px;
    font-size: clamp(1.8rem, 2.4vw, 2.5rem);
    line-height: 1.08;
    color: #7ea6ff;
}

.pm-register-description {
    margin: 0;
    font-size: 0.84rem;
    line-height: 1.55;
    color: rgba(241, 245, 249, 0.92);
}

.pm-register-feature-list {
    display: grid;
    gap: 14px;
    margin-top: auto;
}

.pm-register-feature,
.pm-register-support {
    display: grid;
    grid-template-columns: 52px minmax(0, 1fr);
    gap: 14px;
    align-items: start;
}

.pm-register-feature h2,
.pm-register-support strong {
    margin: 0 0 6px;
    font-size: 0.96rem;
    font-weight: 700;
}

.pm-register-feature p,
.pm-register-support p {
    margin: 0;
    font-size: 0.76rem;
    color: rgba(226, 232, 240, 0.92);
    line-height: 1.45;
}

.pm-register-feature-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2d63e5, #4b82f0);
    color: #ffffff;
    font-size: 0.88rem;
    font-weight: 800;
    box-shadow: 0 12px 24px rgba(45, 99, 229, 0.34);
}

.pm-register-support {
    margin-top: 8px;
    padding-top: 18px;
    border-top: 1px solid rgba(191, 219, 254, 0.24);
}

.pm-register-main {
    padding: 36px 48px;
}

.pm-register-topbar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 6px;
    margin-bottom: 22px;
    color: #64748b;
}

.pm-register-panel {
    max-width: 1100px;
    margin: 0 auto;
}

.pm-register-header h2 {
    margin: 0;
    font-size: clamp(2.4rem, 4vw, 3.5rem);
    line-height: 1.02;
    color: #1e40af;
    font-weight: 800;
}

.pm-register-header p {
    margin: 12px 0 0;
    color: #475569;
    font-size: 1.02rem;
}

.pm-register-steps {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    padding: 0;
    margin: 30px 0 24px;
    list-style: none;
}

.pm-register-step {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #475569;
    position: relative;
    flex: 0 0 auto;
}

.pm-register-step:not(:last-child)::after {
    content: "->";
    position: static;
    color: #94a3b8;
    font-weight: 700;
    margin-left: 6px;
}

.pm-register-step-index {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #94a3b8;
    background: #ffffff;
    font-weight: 700;
    flex: 0 0 auto;
}

.pm-register-step-label {
    font-size: 0.9rem;
    font-weight: 600;
}

.pm-register-step.active {
    color: #2563eb;
}

.pm-register-step.active .pm-register-step-index {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
    box-shadow: 0 12px 26px rgba(37, 99, 235, 0.24);
}

.pm-register-step.upcoming .pm-register-step-index {
    color: #334155;
}

.pm-register-summary,
.pm-register-error {
    margin-bottom: 20px;
    border-radius: 18px;
    padding: 16px 18px;
    border: 1px solid rgba(220, 38, 38, 0.16);
    background: #fff4f4;
    color: #991b1b;
}

.pm-register-summary ul {
    margin: 10px 0 0;
    padding-left: 20px;
}

.pm-register-form {
    display: grid;
    gap: 20px;
}

.pm-register-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.pm-register-card {
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 20px;
    padding: 22px;
    box-shadow: 0 20px 38px rgba(15, 23, 42, 0.06);
}

.pm-register-card-head {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 18px;
}

.pm-register-card-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, 0.12);
    color: #2563eb;
    font-weight: 800;
    flex: 0 0 auto;
}

.pm-register-card-head h3 {
    margin: 0 0 4px;
    color: #2563eb;
    font-size: 1.15rem;
    font-weight: 800;
}

.pm-register-card-head p {
    margin: 0;
    color: #64748b;
}

.pm-register-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.pm-register-fields-inline {
    grid-template-columns: 1fr;
    gap: 12px;
}

.pm-register-fields-address {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.pm-register-field {
    display: grid;
    gap: 7px;
}

.pm-register-field-inline {
    grid-template-columns: 132px minmax(0, 1fr);
    align-items: center;
    column-gap: 14px;
    row-gap: 6px;
}

.pm-register-field-control {
    display: grid;
    gap: 6px;
    min-width: 0;
}

.pm-register-field-inline .pm-field-label {
    margin-bottom: 0;
    font-size: 0.98rem;
}

.pm-register-hint-inline {
    grid-column: 2;
    margin-top: -2px;
}

.pm-register-field-full {
    grid-column: 1 / -1;
}

.pm-register-field-span-2 {
    grid-column: span 2;
}

.pm-register-hint,
.pm-inline-error {
    font-size: 0.84rem;
    line-height: 1.45;
}

.pm-register-hint {
    color: #64748b;
}

.pm-inline-error {
    color: #dc2626;
}

.pm-register-verify-grid {
    display: grid;
    gap: 14px;
}

.pm-register-option {
    display: grid;
    grid-template-columns: 20px minmax(0, 1fr);
    gap: 12px;
    align-items: start;
    border: 1px solid #dbe2ea;
    border-radius: 16px;
    padding: 16px;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.pm-register-option:hover,
.pm-register-option.selected {
    border-color: rgba(37, 99, 235, 0.45);
    box-shadow: 0 14px 26px rgba(37, 99, 235, 0.08);
    transform: translateY(-1px);
}

.pm-register-option strong {
    display: block;
    margin-bottom: 4px;
    color: #1e3a8a;
}

.pm-register-option p {
    margin: 0;
    color: #64748b;
}

.pm-register-plan-note {
    margin-top: 16px;
    padding: 14px 16px;
    border-radius: 14px;
    background: #f8fbff;
    border: 1px solid rgba(37, 99, 235, 0.12);
    color: #1e293b;
}

.pm-register-captcha {
    margin-top: 4px;
    display: grid;
    gap: 10px;
}

.pm-register-agreements {
    display: grid;
    gap: 10px;
}

.pm-register-check {
    display: inline-flex;
    align-items: flex-start;
    gap: 10px;
    color: #334155;
}

.pm-register-check input {
    margin-top: 3px;
}

.pm-register-submit {
    min-height: 56px;
    border-radius: 16px;
    font-size: 1rem;
    font-weight: 700;
}

@media (max-width: 1200px) {
    .pm-register-main {
        padding: 28px 30px;
    }
}

@media (max-width: 1024px) {
    .pm-register-layout {
        grid-template-columns: 1fr;
    }

    .pm-register-aside {
        min-height: auto;
    }

    .pm-register-feature-list {
        margin-top: 0;
    }
}

@media (max-width: 768px) {
    .pm-register-main {
        padding: 22px 16px 28px;
    }

    .pm-register-aside-inner {
        padding: 22px 16px;
    }

    .pm-register-topbar {
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .pm-register-steps,
    .pm-register-grid,
    .pm-register-fields,
    .pm-register-fields-address {
        grid-template-columns: 1fr;
    }

    .pm-register-steps {
        display: grid;
    }

    .pm-register-step:not(:last-child)::after {
        display: none;
    }

    .pm-register-field-inline {
        grid-template-columns: 1fr;
    }

    .pm-register-hint-inline {
        grid-column: auto;
    }

    .pm-register-field-span-2 {
        grid-column: auto;
    }

    .pm-register-brand {
        font-size: 1.45rem;
    }
}
</style>
