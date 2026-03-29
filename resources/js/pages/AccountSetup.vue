<template>
    <div class="pm-setup-page">
        <div class="pm-setup-topbar">
            <div class="container pm-setup-topbar-inner">
                <button class="pm-setup-help" type="button" @click="goSupport">
                    Help &amp; Support
                </button>
            </div>
        </div>
        <section class="pm-setup">
            <div class="container">
                <div class="pm-setup-grid">
                    <aside class="pm-setup-sidebar">
                        <div class="pm-setup-brand">
                            <img :src="brandLogo" :alt="brandName" />
                            <div>
                                <div class="pm-setup-title">
                                    {{ brandName }}
                                </div>
                                <div class="pm-muted">Contractor.com</div>
                            </div>
                        </div>
                        <div class="pm-setup-progress">
                            <div class="pm-progress-circle">
                                <span>{{ progressPercent }}%</span>
                                <small>Complete</small>
                            </div>
                            <div class="pm-muted">
                                Complete all steps to create your account.
                            </div>
                        </div>
                        <div class="pm-setup-steps">
                            <button
                                v-for="(step, index) in steps"
                                :key="step.key"
                                type="button"
                                :class="[
                                    'pm-setup-step',
                                    {
                                        active: currentStep === index + 1,
                                        done: currentStep > index + 1,
                                    },
                                ]"
                                @click="goToStep(index + 1)"
                            >
                                <span
                                    class="pm-step-index"
                                    aria-hidden="true"
                                    >{{
                                        currentStep > index + 1 ? "V" : ""
                                    }}</span
                                >
                                <div>
                                    <div class="pm-step-title">
                                        {{ step.title }}
                                    </div>
                                    <div class="pm-step-status">
                                        {{ stepStatus(index + 1) }}
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div class="pm-setup-footer">
                            Step {{ currentStep }} of {{ steps.length }}
                        </div>
                    </aside>

                    <main
                        class="pm-setup-content mx-auto"
                        :class="{ 'w-3/5': currentStep !== 5 }"
                    >
                        <div v-if="currentStep === 1" class="pm-setup-banner">
                            <div class="pm-setup-banner-icon">
                                <img :src="brandLogo" :alt="brandName" />
                            </div>
                            <div>
                                <h4>Welcome to the Contractor.com</h4>
                                <p>
                                    We are pleased to welcome you. Please
                                    complete all required fields accurately.
                                    Before submitting, you are required to read
                                    the Users Terms and Conditions at least
                                    twice and confirm your agreement.
                                </p>
                            </div>
                        </div>

                        <div class="pm-setup-card">
                            <div
                                v-if="currentStep !== 18"
                                class="pm-setup-card-header"
                            >
                                <h5>{{ steps[currentStep - 1].title }}</h5>
                                <p class="pm-muted">
                                    {{ steps[currentStep - 1].subtitle }}
                                </p>
                            </div>

                            <div
                                v-if="currentStep === 1"
                                class="pm-setup-form pm-step1-premium"
                            >
                                <div class="pm-step2-grid">
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Full Name <em>*</em></label
                                        >
                                        <input
                                            v-model="form.full_name"
                                            class="form-control"
                                            placeholder="Enter Full Name"
                                        />
                                    </div>
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Company / Organization Name
                                            <em>*</em></label
                                        >
                                        <input
                                            v-model="form.company_name"
                                            class="form-control"
                                            placeholder="Enter your company name"
                                        />
                                    </div>
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Business Registration Number
                                            <em>*</em></label
                                        >
                                        <input
                                            v-model="
                                                form.business_registration_number
                                            "
                                            class="form-control"
                                            placeholder="Enter Business Registration Number"
                                        />
                                    </div>
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <label class="pm-field-label w-full"
                                            >Trade / Service Type
                                            <em>*</em></label
                                        >
                                        <select
                                            v-model="form.trade_service_type"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select trade type
                                            </option>
                                            <option
                                                v-for="option in tradeOptions"
                                                :key="option"
                                                :value="option"
                                            >
                                                {{ option }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 2"
                                class="pm-setup-form pm-step2-premium"
                            >
                                <div class="pm-step2-head">
                                    <div>
                                        <div class="pm-step2-kicker">
                                            Step 02 -Business Location
                                        </div>
                                        <h6 class="pm-step2-title">
                                            Add your official company address
                                        </h6>
                                        <p class="pm-step2-subtitle">
                                            This address is used for account
                                            records, compliance checks, and
                                            future billing communication.
                                        </p>
                                    </div>
                                    <div class="pm-step2-chip">
                                        Verified Contact
                                    </div>
                                </div>
                                <div class="pm-step2-grid">
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Street Address *</label
                                        >
                                        <input
                                            v-model="form.company_address"
                                            class="form-control"
                                            placeholder="Enter street address"
                                        />
                                    </div>
                                </div>
                                <div class="pm-step2-grid">
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide flex justify-between"
                                    >
                                        <div>
                                            <label class="pm-field-label"
                                                >City *</label
                                            >
                                            <input
                                                v-model="form.company_city"
                                                class="form-control"
                                                placeholder="Enter city"
                                            />
                                        </div>
                                        <div>
                                            <label class="pm-field-label"
                                                >State / Province *</label
                                            >
                                            <input
                                                v-model="form.company_state"
                                                class="form-control"
                                                placeholder="Enter state/province"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide flex justify-between gap-8"
                                    >
                                        <div>
                                            <label class="pm-field-label"
                                                >ZIP / Postal Code *</label
                                            >
                                            <input
                                                v-model="form.company_zip"
                                                class="form-control"
                                                placeholder="Enter ZIP/postal code"
                                            />
                                        </div>
                                        <div>
                                            <label class="pm-field-label"
                                                >Country *</label
                                            >
                                            <select
                                                v-model.number="
                                                    form.company_country_id
                                                "
                                                class="form-control"
                                            >
                                                <option value="">
                                                    Select country
                                                </option>
                                                <option
                                                    v-for="c in countries"
                                                    :key="c.id"
                                                    :value="c.id"
                                                >
                                                    {{ c.country_name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else-if="currentStep === 3"
                                class="pm-setup-form"
                            >
                                <div class="pm-step2-grid">
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <div>
                                            <label class="pm-field-label"
                                                >Registered Country *</label
                                            >
                                            <select
                                                v-model.number="
                                                    form.registration_country
                                                "
                                                class="form-control"
                                            >
                                                <option value="">
                                                    Select country
                                                </option>
                                                <option
                                                    v-for="c in countries"
                                                    :key="c.id"
                                                    :value="c.id"
                                                >
                                                    {{ c.country_name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="mt-3">
                                            <label class="pm-field-label"
                                                >Registered State / Province
                                                *</label
                                            >
                                            <input
                                                v-model="
                                                    form.registration_province
                                                "
                                                class="form-control"
                                                placeholder="Enter Registered State / Province"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else-if="currentStep === 4"
                                class="pm-setup-form"
                            >
                                <div class="pm-step2-grid">
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <div class="">
                                            <label class="pm-field-label"
                                                >Phone Number *</label
                                            >
                                            <input
                                                v-model="
                                                    form.contact_mobile_phone
                                                "
                                                class="form-control"
                                                placeholder="Example +14165550100"
                                            />
                                        </div>
                                        <div class="mt-3">
                                            <label class="pm-field-label"
                                                >Email Address *</label
                                            >
                                            <input
                                                v-model="
                                                    form.contact_business_email
                                                "
                                                class="form-control"
                                                placeholder="Enter Email Address"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="currentStep === 5" class="pm-setup-form">
                                <div class="table-responsive">
                                    <table
                                        class="table pm-plan-table text-start"
                                    >
                                        <thead>
                                            <tr>
                                                <th class="pm-plan-head">
                                                    Plan Name
                                                </th>
                                                <th
                                                    v-for="plan in planOptions"
                                                    :key="plan.key"
                                                    class="pm-plan-col text-center"
                                                    :class="
                                                        plan.key === 'standard'
                                                            ? 'pm-plan-highlight'
                                                            : ''
                                                    "
                                                >
                                                    <span
                                                        v-if="plan.badge"
                                                        class="pm-popular-badge"
                                                        >Most Popular</span
                                                    >
                                                    <div>{{ plan.label }}</div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th class="pm-second-row">
                                                    Price (per user/month)
                                                </th>
                                                <th
                                                    v-for="plan in planOptions"
                                                    :key="`${plan.key}-price`"
                                                    class="text-center pm-second-row"
                                                    :class="
                                                        plan.key === 'standard'
                                                            ? 'pm-plan-highlight'
                                                            : ''
                                                    "
                                                >
                                                    {{ plan.price }} USD
                                                </th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th
                                                    v-for="plan in planOptions"
                                                    :key="`${plan.key}-btn`"
                                                    class="text-center"
                                                    :class="
                                                        plan.key === 'standard'
                                                            ? 'pm-plan-highlight'
                                                            : ''
                                                    "
                                                >
                                                    <button
                                                        class="btn btn-sm"
                                                        :class="
                                                            form.subscription_plan ===
                                                            plan.key
                                                                ? 'btn-primary'
                                                                : 'btn-outline-primary'
                                                        "
                                                        type="button"
                                                        @click="
                                                            selectPlan(plan.key)
                                                        "
                                                    >
                                                        {{
                                                            form.subscription_plan ===
                                                            plan.key
                                                                ? "Selected"
                                                                : "Select Plan"
                                                        }}
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="row in planFeatureRows"
                                                :key="row.label"
                                            >
                                                <td>{{ row.label }}</td>
                                                <td class="text-center">
                                                    <span
                                                        :class="
                                                            row.basic
                                                                ? 'pm-check'
                                                                : 'pm-x'
                                                        "
                                                    >
                                                        <span v-if="row.basic"
                                                            >&#10003;</span
                                                        >
                                                        <span v-else
                                                            >&#10005;</span
                                                        >
                                                    </span>
                                                </td>
                                                <td
                                                    class="text-center pm-plan-highlight"
                                                >
                                                    <span
                                                        :class="
                                                            row.standard
                                                                ? 'pm-check'
                                                                : 'pm-x'
                                                        "
                                                    >
                                                        <span
                                                            v-if="row.standard"
                                                            >&#10003;</span
                                                        >
                                                        <span v-else
                                                            >&#10005;</span
                                                        >
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        :class="
                                                            row.enterprise
                                                                ? 'pm-check'
                                                                : 'pm-x'
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                row.enterprise
                                                            "
                                                            >&#10003;</span
                                                        >
                                                        <span v-else
                                                            >&#10005;</span
                                                        >
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="pm-step9-meta">
                                    Pricing is calculated per active user
                                    license and billed monthly.
                                </div>
                            </div>

                            <div
                                v-else-if="currentStep === 6"
                                class="pm-setup-form"
                            >
                                <label class="pm-field pm-field-full">
                                    <span>Username <em>*</em></span>
                                    <input
                                        v-model="form.security_username"
                                        type="text"
                                        placeholder="Choose a username"
                                    />
                                </label>

                                <label class="pm-field">
                                    <span>Password <em>*</em></span>
                                    <input
                                        v-model="form.security_password"
                                        type="password"
                                        placeholder="Create a password"
                                    />
                                </label>

                                <label class="pm-field">
                                    <span>Confirm Password <em>*</em></span>
                                    <input
                                        v-model="form.security_password_confirm"
                                        type="password"
                                        placeholder="Confirm password"
                                    />
                                </label>
                            </div>

                            <div
                                v-if="currentStep === 7"
                                class="pm-setup-form pm-step7-premium"
                            >
                                <div class="pm-step7-head">
                                    <div>
                                        <div class="pm-step7-kicker">
                                            Step 07 - Notification Preferences
                                        </div>
                                        <h6 class="pm-step7-title">
                                            Choose how your team receives alerts
                                        </h6>
                                        <p class="pm-step7-subtitle">
                                            Select mandatory and optional
                                            channels for parcel events, security
                                            updates, and operational
                                            communication.
                                        </p>
                                    </div>
                                    <div class="pm-step7-chip">
                                        Alerts Setup
                                    </div>
                                </div>
                                <div class="pm-setup-options pm-step7-options">
                                    <label class="pm-option-card">
                                        <input
                                            type="checkbox"
                                            v-model="form.notify_email"
                                        />
                                        <div>
                                            <div class="pm-option-title">
                                                Email Notifications
                                            </div>
                                            <div class="pm-muted">
                                                Receive notifications via email
                                            </div>
                                        </div>
                                    </label>
                                    <label class="pm-option-card">
                                        <input
                                            type="checkbox"
                                            v-model="form.notify_sms"
                                        />
                                        <div>
                                            <div class="pm-option-title">
                                                SMS Notifications
                                            </div>
                                            <div class="pm-muted">
                                                Receive notifications via text
                                                message
                                            </div>
                                        </div>
                                    </label>
                                    <label class="pm-option-card">
                                        <input
                                            type="checkbox"
                                            v-model="form.notify_arrival"
                                        />
                                        <div>
                                            <div class="pm-option-title">
                                                Parcel Arrival Alerts
                                            </div>
                                            <div class="pm-muted">
                                                Get notified when parcels arrive
                                            </div>
                                        </div>
                                    </label>
                                    <label class="pm-option-card">
                                        <input
                                            type="checkbox"
                                            v-model="form.notify_security"
                                        />
                                        <div>
                                            <div class="pm-option-title">
                                                System & Security Alerts
                                            </div>
                                            <div class="pm-muted">
                                                Important security and system
                                                notifications
                                            </div>
                                        </div>
                                    </label>
                                    <label class="pm-option-card">
                                        <input
                                            type="checkbox"
                                            v-model="form.notify_marketing"
                                        />
                                        <div>
                                            <div class="pm-option-title">
                                                Marketing / Promotional Updates
                                            </div>
                                            <div class="pm-muted">
                                                Optional promotional content and
                                                updates
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="pm-step7-meta">
                                    At least one primary channel (Email or SMS)
                                    must remain enabled.
                                </div>
                            </div>

                            <div v-if="error" class="pm-form-error">
                                {{ error }}
                            </div>
                            <div v-if="success" class="pm-form-success">
                                Saved. Continue to the next step.
                            </div>

                            <div
                                v-if="!isFinalStep"
                                class="flex justify-between"
                            >
                                <button
                                    class="btn btn-outline-primary"
                                    type="button"
                                    :disabled="currentStep === 1"
                                    @click="prev"
                                >
                                    Back
                                </button>
                                <button
                                    class="btn btn-primary"
                                    type="button"
                                    :disabled="loading"
                                    @click="next"
                                >
                                    {{ isFinalStep ? "Finish Setup" : "Next" }}
                                </button>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </section>
    </div>
</template>
<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import logoWhite from "../assets/logo-white.png";
import client from "../api/client";
import { clearToken, logout } from "../api/auth";
import { authState } from "../store/auth";

const router = useRouter();
const loading = ref(false);
const error = ref("");
const success = ref(false);
const countries = ref([]);
const currentStep = ref(1);

const steps = [
    {
        key: "personal",
        title: "Personal Information",
        subtitle: "Provide your basic personal and business details.",
    },
    {
        key: "location",
        title: "Business Location",
        subtitle: "Enter your official business address.",
    },
    {
        key: "registration",
        title: "Company Registration",
        subtitle: "Add your registration details and company branding.",
    },
    {
        key: "contact",
        title: "Contact Information",
        subtitle: "Set the main contact details for your account.",
    },
    {
        key: "plan",
        title: "Service Plan Selection",
        subtitle: "Choose the plan that fits your team and workload.",
    },
    {
        key: "security",
        title: "Account Security",
        subtitle: "Create your account login credentials.",
    },
    {
        key: "confirmation",
        title: "Confirmation & Agreement",
        subtitle: "Review your details and confirm the setup.",
    },
];

const tradeOptions = [
    "General Contractor",
    "Electrical Services",
    "Plumbing Services",
    "HVAC Services",
    "Roofing Services",
    "Painting Services",
    "Civil Construction",
    "Interior Fit-Out",
    "Landscaping",
    "Other",
];

const planOptions = [
    {
        value: "basic",
        label: "Basic",
        price: "$19 / month",
        description: "Good for small contractors getting started.",
    },
    {
        value: "standard",
        label: "Standard",
        price: "$49 / month",
        description: "Built for active teams managing multiple projects.",
        badge: "Popular",
    },
    {
        value: "enterprise",
        label: "Premium",
        price: "Custom pricing",
        description: "For larger operations with advanced support needs.",
    },
];

const planFeatureRows = [
    {
        label: "Parcel Logging (record arrivals and departures)",
        basic: true,
        standard: true,
        enterprise: true,
    },
    {
        label: "Track Parcel Handovers (who received what)",
        basic: true,
        standard: true,
        enterprise: true,
    },
    {
        label: "Update Parcel Status (Picked Up, Delivered)",
        basic: true,
        standard: true,
        enterprise: true,
    },
    {
        label: "Manage Storage Locations (Back Office, Lockers)",
        basic: false,
        standard: true,
        enterprise: true,
    },
    {
        label: "Confirm Parcel Pickups",
        basic: false,
        standard: true,
        enterprise: true,
    },
    {
        label: "Track Pending or Held Parcels",
        basic: false,
        standard: true,
        enterprise: true,
    },
    {
        label: "Track External Lockers (third-party lockers)",
        basic: false,
        standard: false,
        enterprise: true,
    },
    {
        label: "Delivery by Staff or Locker Access Management",
        basic: false,
        standard: false,
        enterprise: true,
    },
    {
        label: "Record Parcel Final Outcomes (Returned, Lost, Damaged)",
        basic: false,
        standard: false,
        enterprise: true,
    },
    {
        label: "Generate Reports & Basic Analytics",
        basic: false,
        standard: false,
        enterprise: true,
    },
    {
        label: "Multi-language Support",
        basic: false,
        standard: true,
        enterprise: true,
    },
    {
        label: "User Management (add users, roles, active/inactive)",
        basic: false,
        standard: false,
        enterprise: true,
    },
];

const form = ref({
    full_name: "",
    company_name: "",
    business_registration_number: "",
    trade_service_type: "",
    company_address: "",
    company_city: "",
    company_state: "",
    company_zip: "",
    company_country_id: "",
    company_country: "",
    registration_country: "",
    registration_province: "",
    registration_notes: "",
    company_logo_url: "",
    company_logo_path: "",
    company_logo_preview: "",
    contact_full_name: "",
    contact_primary_phone: "",
    contact_mobile_phone: "",
    contact_business_email: "",
    contact_website: "",
    subscription_plan: "growth",
    security_username: "",
    security_password: "",
    security_password_confirm: "",
    terms_ack: false,
    privacy_ack: false,
    final_ack: false,
});

const activeStep = computed(() => steps[currentStep.value - 1] || steps[0]);
const brandName = computed(
    () =>
        form.value.company_name || authState.user?.company_name || "Contractor",
);
const brandLogo = computed(
    () =>
        form.value.company_logo_url ||
        form.value.company_logo_preview ||
        logoWhite,
);
const progressPercent = computed(() =>
    Math.max(14, Math.round((currentStep.value / steps.length) * 100)),
);
const progressDashArray = computed(() => {
    const circumference = 2 * Math.PI * 46;
    const offset =
        circumference - (progressPercent.value / 100) * circumference;
    return `${circumference - offset} ${circumference}`;
});
const selectedPlanLabel = computed(() => {
    const found = planOptions.find(
        (plan) => plan.value === form.value.subscription_plan,
    );
    return found?.label || "Not selected";
});

const stepStatus = (step) => {
    if (step === currentStep.value) return "In Progress";
    if (step < currentStep.value) return "Completed";
    return "Pending";
};

const goSupport = () => {
    router.push("/contact");
};

const goToStep = (step) => {
    if (step <= currentStep.value) {
        currentStep.value = step;
    }
};

const prev = () => {
    if (currentStep.value > 1) {
        currentStep.value -= 1;
    }
};

const resolveCountryNameById = (id) => {
    const found = countries.value.find(
        (country) => Number(country.id) === Number(id),
    );
    return found?.country_name || "";
};

const resolveCountryIdByName = (name) => {
    const normalized = String(name || "")
        .trim()
        .toLowerCase();
    if (!normalized) return "";
    const found = countries.value.find(
        (country) =>
            String(country.country_name || "")
                .trim()
                .toLowerCase() === normalized,
    );
    return found?.id || "";
};

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

const applyAuthDefaults = () => {
    if (!form.value.company_name && authState.user?.company_name) {
        form.value.company_name = authState.user.company_name;
    }
};

const firstErrorMessage = (responseData) => {
    if (responseData?.message) return responseData.message;
    const errors = responseData?.errors;
    if (!errors || typeof errors !== "object")
        return "Failed to save. Please try again.";
    const firstKey = Object.keys(errors)[0];
    if (
        !firstKey ||
        !Array.isArray(errors[firstKey]) ||
        !errors[firstKey].length
    ) {
        return "Failed to save. Please try again.";
    }
    return errors[firstKey][0];
};

const loadCountries = async () => {
    try {
        const { data } = await client.get("/countries");
        if (data?.success && Array.isArray(data.data)) {
            countries.value = data.data;
            return;
        }
    } catch {
        // Keep page usable if countries cannot be loaded.
    }
    countries.value = [];
};

const loadSetup = async () => {
    try {
        const { data } = await client.get("/account-setup");
        if (!data?.success || !data?.data) return;

        const setup = data.data;
        const next = { ...form.value };

        Object.keys(next).forEach((key) => {
            if (setup[key] !== undefined && setup[key] !== null) {
                next[key] = setup[key];
            }
        });

        if (!next.company_country_id && next.company_country) {
            next.company_country_id = resolveCountryIdByName(
                next.company_country,
            );
        }
        if (!next.company_country && next.company_country_id) {
            next.company_country = resolveCountryNameById(
                next.company_country_id,
            );
        }

        form.value = next;
        currentStep.value = Math.min(
            steps.length,
            Math.max(1, Number(setup.current_step) || 1),
        );
    } catch {
        applyAuthDefaults();
    }
};

const onLogoSelect = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    error.value = "";
    success.value = false;

    if (form.value.company_logo_preview?.startsWith("blob:")) {
        URL.revokeObjectURL(form.value.company_logo_preview);
    }

    form.value.company_logo_preview = URL.createObjectURL(file);
    const formData = new FormData();
    formData.append("logo", file);

    try {
        const { data } = await client.post("/account-setup/logo", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        if (data?.success) {
            form.value.company_logo_url = data.data.url;
            form.value.company_logo_path = data.data.path;
            form.value.company_logo_preview =
                data.data.url || form.value.company_logo_preview;
        }
    } catch (e) {
        error.value = firstErrorMessage(e?.response?.data);
    }
};

const validateCurrentStep = () => {
    if (currentStep.value === 1) {
        if (!form.value.full_name) return "Full name is required.";
        if (!form.value.company_name) return "Company name is required.";
        if (!form.value.business_registration_number)
            return "Business registration number is required.";
        if (!form.value.trade_service_type)
            return "Trade / service type is required.";
    }

    if (currentStep.value === 2) {
        if (!form.value.company_address) return "Street address is required.";
        if (!form.value.company_city) return "City is required.";
        if (!form.value.company_state) return "State / Province is required.";
        if (!form.value.company_zip) return "ZIP / Postal Code is required.";
        if (!form.value.company_country_id) return "Country is required.";
    }

    if (currentStep.value === 3) {
        if (!form.value.registration_country)
            return "Registration Country is required.";
        if (!form.value.registration_province)
            return "Registration Province is required.";
    }

    if (currentStep.value === 4) {
        form.value.contact_mobile_phone = normalizePhoneNumber(
            form.value.contact_mobile_phone,
        );

        if (!form.value.contact_business_email)
            return "Business email is required.";

        if (
            form.value.contact_mobile_phone &&
            !isE164PhoneNumber(form.value.contact_mobile_phone)
        ) {
            return "Mobile phone must be in E.164 format (e.g. +14165550100).";
        }
    }

    if (currentStep.value === 5 && !form.value.subscription_plan) {
        return "Please select a service plan.";
    }

    if (currentStep.value === 6) {
        if (!form.value.security_username) return "Username is required.";
        if (!form.value.security_password) return "Password is required.";
        if (form.value.security_password.length < 8)
            return "Password must be at least 8 characters.";
        if (
            form.value.security_password !==
            form.value.security_password_confirm
        )
            return "Passwords do not match.";
    }

    if (currentStep.value === 7) {
        if (!form.value.terms_ack)
            return "You must accept the Terms and Conditions.";
        if (!form.value.privacy_ack)
            return "You must accept the Privacy Policy.";
        if (!form.value.final_ack)
            return "Please confirm the information is accurate.";
    }

    return "";
};

const buildPayload = () => {
    const payload = { ...form.value };
    payload.company_country_id = payload.company_country_id || null;
    payload.company_country = payload.company_country_id
        ? resolveCountryNameById(payload.company_country_id)
        : "";

    if (currentStep.value !== 6) {
        delete payload.security_password;
        delete payload.security_password_confirm;
    }

    delete payload.company_logo_preview;

    return payload;
};

const saveStep = async () => {
    error.value = "";
    success.value = false;

    const validationError = validateCurrentStep();
    if (validationError) {
        error.value = validationError;
        return false;
    }

    loading.value = true;
    try {
        await client.post("/account-setup", {
            step: currentStep.value,
            data: buildPayload(),
        });
        success.value = true;
        return true;
    } catch (e) {
        error.value = firstErrorMessage(e?.response?.data);
        return false;
    } finally {
        loading.value = false;
    }
};

const next = async () => {
    const saved = await saveStep();
    if (!saved) return;

    if (currentStep.value < steps.length) {
        currentStep.value += 1;
        return;
    }

    loading.value = true;
    try {
        await client.post("/account-setup/complete");
        sessionStorage.setItem(
            "account_setup_summary",
            JSON.stringify({
                full_name: form.value.full_name,
                company_name: form.value.company_name,
                email: form.value.contact_business_email,
                plan: form.value.subscription_plan,
            }),
        );

        try {
            await logout();
        } catch {
            // Ignore logout errors after successful completion.
        }

        clearToken();
        router.push("/account-setup/success");
    } catch (e) {
        error.value = firstErrorMessage(e?.response?.data);
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await loadCountries();
    await loadSetup();
    applyAuthDefaults();
});

watch(
    () => authState.user?.company_name,
    () => {
        applyAuthDefaults();
    },
);
</script>
