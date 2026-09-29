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
                                <div class="pm-muted">
                                    Front Desk Parcel Management
                                </div>
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

                    <main class="pm-setup-content">
                        <div v-if="currentStep === 1" class="pm-setup-banner">
                            <div class="pm-setup-banner-icon">
                                <img :src="brandLogo" :alt="brandName" />
                            </div>
                            <div>
                                <h4>
                                    Welcome to the Parcel Management Platform
                                </h4>
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
                                <div class="pm-step1-head">
                                    <div>
                                        <div class="pm-step1-kicker">
                                            Step 01 - Brand Identity
                                        </div>
                                        <h6 class="pm-step1-title">
                                            Set up your company profile
                                        </h6>
                                        <p class="pm-step1-subtitle">
                                            Your brand name and logo will appear
                                            in the dashboard, notifications, and
                                            account documents.
                                        </p>
                                    </div>
                                    <div class="pm-step1-chip">
                                        Secure Setup
                                    </div>
                                </div>
                                <div class="pm-step1-grid">
                                    <div class="pm-step1-panel">
                                        <label class="pm-field-label"
                                            >Company / Organization Name
                                            *</label
                                        >
                                        <input
                                            v-model="form.company_name"
                                            class="form-control"
                                            placeholder="Enter your company name"
                                            readonly
                                            disabled
                                        />
                                    </div>
                                    <div class="pm-step1-panel">
                                        <label class="pm-field-label"
                                            >Company Logo (Attach File)</label
                                        >
                                        <div
                                            class="pm-file-drop"
                                            :class="{
                                                'is-ready':
                                                    form.company_logo_url ||
                                                    form.company_logo_preview,
                                            }"
                                        >
                                            <input
                                                id="companyLogo"
                                                class="pm-file-input"
                                                type="file"
                                                accept="image/png,image/jpeg"
                                                @change="onLogoSelect"
                                            />
                                            <label
                                                class="pm-file-label"
                                                for="companyLogo"
                                            >
                                                <span class="pm-file-icon"
                                                    >&#8679;</span
                                                >
                                                <span>Upload company logo</span>
                                                <span class="pm-file-hint"
                                                    >JPG or PNG (max 5MB)</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="
                                        form.company_logo_url ||
                                        form.company_logo_preview
                                    "
                                    class="pm-logo-preview"
                                >
                                    <img
                                        :src="
                                            form.company_logo_url ||
                                            form.company_logo_preview
                                        "
                                        alt="Company logo"
                                    />
                                </div>
                                <div class="pm-step1-meta">
                                    Recommended: square logo 512x512 for best
                                    quality.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 2"
                                class="pm-setup-form pm-step2-premium"
                            >
                                <div class="pm-step2-head">
                                    <div>
                                        <div class="pm-step2-kicker">
                                            Step 02 - Location Profile
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
                                    <div class="pm-step2-panel">
                                        <label class="pm-field-label"
                                            >City *</label
                                        >
                                        <input
                                            v-model="form.company_city"
                                            class="form-control"
                                            placeholder="Enter city"
                                        />
                                    </div>
                                    <div class="pm-step2-panel">
                                        <label class="pm-field-label"
                                            >State / Province *</label
                                        >
                                        <input
                                            v-model="form.company_state"
                                            class="form-control"
                                            placeholder="Enter state/province"
                                        />
                                    </div>
                                    <div class="pm-step2-panel">
                                        <label class="pm-field-label"
                                            >ZIP / Postal Code *</label
                                        >
                                        <input
                                            v-model="form.company_zip"
                                            class="form-control"
                                            placeholder="Enter ZIP/postal code"
                                        />
                                    </div>
                                    <div class="pm-step2-panel">
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
                                    <div
                                        class="pm-step2-panel pm-step2-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Main Phone Number *</label
                                        >
                                        <input
                                            v-model="form.company_phone"
                                            class="form-control"
                                            placeholder="+1 (555) 123-4567"
                                        />
                                    </div>
                                </div>
                                <div class="pm-step2-meta">
                                    Use your primary office details so support
                                    and invoice records stay consistent.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 3"
                                class="pm-setup-form pm-step3-premium"
                            >
                                <div class="pm-step3-head">
                                    <div>
                                        <div class="pm-step3-kicker">
                                            Step 03 - Facility Profile
                                        </div>
                                        <h6 class="pm-step3-title">
                                            Define your primary building details
                                        </h6>
                                        <p class="pm-step3-subtitle">
                                            Keep facility information accurate
                                            so parcel routing, internal
                                            tracking, and access policies stay
                                            aligned.
                                        </p>
                                    </div>
                                    <div class="pm-step3-chip">
                                        Operations Ready
                                    </div>
                                </div>
                                <div class="pm-step3-grid">
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >Facility / Building Name *</label
                                        >
                                        <input
                                            v-model="form.facility_name"
                                            class="form-control"
                                            placeholder="Enter facility name"
                                        />
                                    </div>
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >Part Number *</label
                                        >
                                        <input
                                            v-model="form.facility_part_number"
                                            class="form-control"
                                            placeholder="Enter part number"
                                        />
                                    </div>
                                    <div
                                        class="pm-step3-panel pm-step3-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Street Address *</label
                                        >
                                        <input
                                            v-model="form.facility_address"
                                            class="form-control"
                                            placeholder="Enter street address"
                                        />
                                    </div>
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >City *</label
                                        >
                                        <input
                                            v-model="form.facility_city"
                                            class="form-control"
                                            placeholder="Enter city"
                                        />
                                    </div>
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >State / Province *</label
                                        >
                                        <input
                                            v-model="form.facility_state"
                                            class="form-control"
                                            placeholder="Enter state/province"
                                        />
                                    </div>
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >ZIP / Postal Code *</label
                                        >
                                        <input
                                            v-model="form.facility_zip"
                                            class="form-control"
                                            placeholder="Enter ZIP/postal code"
                                        />
                                    </div>
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >Country *</label
                                        >
                                        <select
                                            v-model.number="
                                                form.facility_country_id
                                            "
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select country
                                            </option>
                                            <option
                                                v-for="c in countries"
                                                :key="`facility-country-${c.id}`"
                                                :value="c.id"
                                            >
                                                {{ c.country_name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >Phone Number *</label
                                        >
                                        <input
                                            v-model="form.facility_phone"
                                            class="form-control"
                                            placeholder="+1 (555) 123-4567"
                                        />
                                    </div>
                                    <div class="pm-step3-panel">
                                        <label class="pm-field-label"
                                            >Email Address *</label
                                        >
                                        <input
                                            v-model="form.facility_email"
                                            class="form-control"
                                            placeholder="facility@company.com"
                                        />
                                    </div>
                                </div>
                                <div class="pm-step3-meta">
                                    These details will be used as the default
                                    receiving location in your workflow.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 4"
                                class="pm-setup-form pm-step4-premium"
                            >
                                <div class="pm-step4-head">
                                    <div>
                                        <div class="pm-step4-kicker">
                                            Step 04 - Facility Allocation
                                        </div>
                                        <h6 class="pm-step4-title">
                                            Track facility usage and user
                                            assignment
                                        </h6>
                                        <p class="pm-step4-subtitle">
                                            Add each facility you operate and
                                            keep license assignment within
                                            policy limits to avoid compliance
                                            issues.
                                        </p>
                                    </div>
                                    <div class="pm-step4-chip">
                                        License Guard
                                    </div>
                                </div>
                                <div
                                    class="pm-setup-table-header pm-step4-toolbar"
                                >
                                    <div class="pm-table-title">
                                        Facility Usage Table
                                    </div>
                                    <button
                                        class="btn btn-primary btn-sm pm-step4-add"
                                        type="button"
                                        @click="addFacilityRow"
                                    >
                                        + Add Facility
                                    </button>
                                </div>
                                <div
                                    class="table-responsive pm-step4-table-wrap"
                                >
                                    <table
                                        class="table pm-setup-table pm-step4-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th>Facility Name</th>
                                                <th>
                                                    Facility ID / Part Number
                                                </th>
                                                <th>City</th>
                                                <th>Country</th>
                                                <th class="text-center">
                                                    Assigned Users
                                                </th>
                                                <th class="text-center">
                                                    Status
                                                </th>
                                                <th class="text-center">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    row, idx
                                                ) in form.facility_usage"
                                                :key="`facility-${idx}`"
                                            >
                                                <td>
                                                    <input
                                                        v-model="row.name"
                                                        class="form-control"
                                                        placeholder="Facility name"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model="row.part"
                                                        class="form-control"
                                                        placeholder="Part number"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model="row.city"
                                                        class="form-control"
                                                        placeholder="City"
                                                    />
                                                </td>
                                                <td>
                                                    <select
                                                        v-model.number="
                                                            row.country_id
                                                        "
                                                        class="form-control"
                                                    >
                                                        <option value="">
                                                            Select country
                                                        </option>
                                                        <option
                                                            v-for="c in countries"
                                                            :key="`usage-country-${idx}-${c.id}`"
                                                            :value="c.id"
                                                        >
                                                            {{ c.country_name }}
                                                        </option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input
                                                        v-model="row.assigned"
                                                        class="form-control pm-setup-count"
                                                        type="number"
                                                        min="0"
                                                    />
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        :class="[
                                                            'pm-status-pill',
                                                            row.status ===
                                                            'active'
                                                                ? 'active'
                                                                : '',
                                                        ]"
                                                    >
                                                        {{
                                                            row.status ===
                                                            "active"
                                                                ? "Active"
                                                                : "Inactive"
                                                        }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button
                                                        class="btn btn-link p-0"
                                                        type="button"
                                                        @click="
                                                            toggleStatus(idx)
                                                        "
                                                    >
                                                        &#9998;
                                                    </button>
                                                    <button
                                                        class="btn btn-link text-danger p-0 ms-2"
                                                        type="button"
                                                        @click="
                                                            removeFacilityRow(
                                                                idx,
                                                            )
                                                        "
                                                    >
                                                        &#128465;
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr
                                                v-if="
                                                    !form.facility_usage.length
                                                "
                                            >
                                                <td
                                                    colspan="7"
                                                    class="text-center pm-muted pm-step4-empty"
                                                >
                                                    No facilities added yet.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="pm-setup-note pm-step4-note">
                                    <strong>Usage Rules & Notes</strong>
                                    <ul>
                                        <li>
                                            Each user license may be assigned to
                                            a maximum of two (2) facilities
                                            only.
                                        </li>
                                        <li>
                                            Assigning a single user to more than
                                            two facilities is considered a
                                            license violation.
                                        </li>
                                        <li>
                                            License violations may result in:
                                        </li>
                                        <li class="ps-4">Account suspension</li>
                                        <li class="ps-4">
                                            Additional licensing fees
                                        </li>
                                        <li class="ps-4">
                                            Enforcement or administrative review
                                        </li>
                                    </ul>
                                </div>
                                <div class="pm-step4-meta">
                                    Tip: mark inactive facilities to keep active
                                    assignment counts accurate.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 5"
                                class="pm-setup-form pm-step5-premium"
                            >
                                <div class="pm-step5-head">
                                    <div>
                                        <div class="pm-step5-kicker">
                                            Step 05 - Contact Profile
                                        </div>
                                        <h6 class="pm-step5-title">
                                            Configure your primary communication
                                            channels
                                        </h6>
                                        <p class="pm-step5-subtitle">
                                            These contacts are used for account
                                            alerts, operational updates, and
                                            support follow-up.
                                        </p>
                                    </div>
                                    <div class="pm-step5-chip">
                                        Communication Ready
                                    </div>
                                </div>
                                <div class="pm-step5-grid">
                                    <div class="pm-step5-panel">
                                        <label class="pm-field-label"
                                            >Primary Phone Number *</label
                                        >
                                        <input
                                            v-model="form.contact_primary_phone"
                                            class="form-control"
                                            placeholder="+1 (555) 123-4567"
                                        />
                                    </div>
                                    <div class="pm-step5-panel">
                                        <label class="pm-field-label"
                                            >Mobile Number
                                            <span class="pm-muted"
                                                >(Optional)</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.contact_mobile_phone"
                                            class="form-control"
                                            placeholder="+1 (555) 987-6543"
                                        />
                                    </div>
                                    <div
                                        class="pm-step5-panel pm-step5-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Business Email Address *</label
                                        >
                                        <input
                                            v-model="
                                                form.contact_business_email
                                            "
                                            class="form-control"
                                            placeholder="contact@company.com"
                                        />
                                    </div>
                                    <div class="pm-step5-panel">
                                        <label class="pm-field-label"
                                            >Alternate Email
                                            <span class="pm-muted"
                                                >(Optional)</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.contact_alt_email"
                                            class="form-control"
                                            placeholder="alternate@company.com"
                                        />
                                    </div>
                                    <div class="pm-step5-panel">
                                        <label class="pm-field-label"
                                            >Website
                                            <span class="pm-muted"
                                                >(Optional)</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.contact_website"
                                            class="form-control"
                                            placeholder="https://www.company.com"
                                        />
                                    </div>
                                </div>
                                <div class="pm-step5-meta">
                                    Use monitored contacts to avoid missing
                                    verification and service notices.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 6"
                                class="pm-setup-form pm-step6-premium"
                            >
                                <div class="pm-step6-head">
                                    <div>
                                        <div class="pm-step6-kicker">
                                            Step 06 - Regional Preferences
                                        </div>
                                        <h6 class="pm-step6-title">
                                            Set timezone, language, and date
                                            format
                                        </h6>
                                        <p class="pm-step6-subtitle">
                                            These settings control how dates,
                                            times, and system labels appear for
                                            your daily operations.
                                        </p>
                                    </div>
                                    <div class="pm-step6-chip">
                                        Localized View
                                    </div>
                                </div>
                                <div class="pm-step6-grid">
                                    <div class="pm-step6-panel">
                                        <label class="pm-field-label"
                                            >Time Zone *</label
                                        >
                                        <select
                                            v-model="form.pref_timezone"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select time zone
                                            </option>
                                            <option
                                                v-if="
                                                    form.pref_timezone &&
                                                    !timezoneOptions.includes(
                                                        form.pref_timezone,
                                                    )
                                                "
                                                :value="form.pref_timezone"
                                            >
                                                {{ form.pref_timezone }}
                                            </option>
                                            <option
                                                v-for="tz in timezoneOptions"
                                                :key="tz"
                                                :value="tz"
                                            >
                                                {{ tz }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="pm-step6-panel">
                                        <label class="pm-field-label"
                                            >Preferred Language *</label
                                        >
                                        <select
                                            v-model="form.pref_language"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select language
                                            </option>
                                            <option
                                                v-if="
                                                    form.pref_language &&
                                                    !languageCodeSet.has(
                                                        String(
                                                            form.pref_language,
                                                        ).toLowerCase(),
                                                    )
                                                "
                                                :value="form.pref_language"
                                            >
                                                {{ form.pref_language }}
                                            </option>
                                            <option
                                                v-for="lang in languageOptions"
                                                :key="lang.code"
                                                :value="lang.code"
                                            >
                                                {{ lang.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        class="pm-step6-panel pm-step6-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Date Format *</label
                                        >
                                        <select
                                            v-model="form.pref_date_format"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select date format
                                            </option>
                                            <option
                                                v-for="fmt in dateFormatOptions"
                                                :key="fmt"
                                                :value="fmt"
                                            >
                                                {{ fmt }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="pm-step6-meta">
                                    Choose the format your team uses most to
                                    reduce entry mistakes.
                                </div>
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

                            <div
                                v-if="currentStep === 8"
                                class="pm-setup-form pm-step8-premium"
                            >
                                <div class="pm-step8-head">
                                    <div>
                                        <div class="pm-step8-kicker">
                                            Step 08 - Account Security
                                        </div>
                                        <h6 class="pm-step8-title">
                                            Protect access with credentials and
                                            policy controls
                                        </h6>
                                        <p class="pm-step8-subtitle">
                                            Configure secure login details,
                                            access PIN, and optional 2FA to
                                            protect your organization account.
                                        </p>
                                    </div>
                                    <div class="pm-step8-chip">
                                        Security Layer
                                    </div>
                                </div>

                                <div class="pm-step8-grid">
                                    <div class="pm-setup-panel pm-step8-panel">
                                        <h6>Login Credentials</h6>
                                        <div class="mb-3">
                                            <label class="pm-field-label"
                                                >Username *</label
                                            >
                                            <input
                                                v-model="form.security_username"
                                                class="form-control"
                                                placeholder="Choose a username"
                                            />
                                        </div>
                                        <div class="mb-3">
                                            <label class="pm-field-label"
                                                >Password *</label
                                            >
                                            <input
                                                v-model="form.security_password"
                                                type="password"
                                                class="form-control"
                                                placeholder="Create a strong password"
                                            />
                                        </div>
                                        <div class="mb-3">
                                            <label class="pm-field-label"
                                                >Confirm Password *</label
                                            >
                                            <input
                                                v-model="
                                                    form.security_password_confirm
                                                "
                                                type="password"
                                                class="form-control"
                                                placeholder="Confirm your password"
                                            />
                                        </div>
                                    </div>

                                    <div class="pm-step8-side">
                                        <div
                                            class="pm-setup-panel pm-step8-panel"
                                        >
                                            <label class="pm-field-label"
                                                >Security PIN *</label
                                            >
                                            <input
                                                v-model="form.security_pin"
                                                class="form-control"
                                                placeholder="Enter 4-6 digit PIN"
                                            />
                                        </div>

                                        <div
                                            class="pm-toggle-row pm-step8-toggle"
                                        >
                                            <div>
                                                <div class="pm-option-title">
                                                    Enable Two-Factor
                                                    Authentication
                                                    <span class="pm-muted"
                                                        >(Optional)</span
                                                    >
                                                </div>
                                                <div class="pm-muted">
                                                    Add extra security with 2FA
                                                    verification codes
                                                </div>
                                            </div>
                                            <label class="pm-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.security_2fa"
                                                />
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="pm-policy-card pm-policy-warning pm-step8-policy"
                                >
                                    <strong>Security & Device Policy</strong>
                                    <p>
                                        Multiple device login or 3 failed
                                        attempts will automatically lock your
                                        account for security.
                                    </p>
                                    <div class="pm-policy-confirm">
                                        I confirm this device is authorized for
                                        business use only
                                    </div>
                                </div>

                                <div
                                    class="pm-policy-card pm-policy-danger pm-step8-policy"
                                >
                                    <strong
                                        >Password & PIN Rotation Policy</strong
                                    >
                                    <ul>
                                        <li>
                                            Authorized users are required to
                                            update their password and security
                                            PIN every thirty (30) days.
                                        </li>
                                        <li>
                                            The system sends three (3) reminder
                                            notifications before expiration.
                                        </li>
                                        <li>
                                            If not updated after the third
                                            reminder, your account will be
                                            automatically locked.
                                        </li>
                                        <li>
                                            Enforcement occurs 24 hours after
                                            the third notification.
                                        </li>
                                        <li>
                                            Locked accounts require password/PIN
                                            update or admin verification.
                                        </li>
                                    </ul>
                                    <div class="pm-policy-ack">
                                        <label>
                                            <input
                                                type="checkbox"
                                                v-model="
                                                    form.security_policy_ack
                                                "
                                            />
                                            I understand and agree to comply
                                            with the Password & PIN Rotation
                                            Policy *
                                        </label>
                                    </div>
                                </div>

                                <div class="pm-step8-meta">
                                    Use a strong password and keep policy
                                    acknowledgement enabled to continue setup.
                                </div>
                            </div>

                            <div v-if="currentStep === 9" class="pm-setup-form">
                                <div class="text-center mb-3">
                                    <h4 class="fw-semibold">
                                        Contractor.com Service Plans
                                    </h4>
                                    <p class="pm-muted">
                                        Choose the plan that best fits your
                                        organization's needs
                                    </p>
                                </div>
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
                                                        v-if="plan.popular"
                                                        class="pm-popular-badge"
                                                        >Most Popular</span
                                                    >
                                                    <div>{{ plan.label }}</div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th class="pm-plan-head">
                                                    Price (per user/month)
                                                </th>
                                                <th
                                                    v-for="plan in planOptions"
                                                    :key="`${plan.key}-price`"
                                                    class="text-center"
                                                    :class="
                                                        plan.key === 'standard'
                                                            ? 'pm-plan-highlight'
                                                            : ''
                                                    "
                                                >
                                                    ${{ plan.price }} USD
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
                                v-if="currentStep === 10"
                                class="pm-setup-form pm-step10-premium"
                            >
                                <div class="pm-step10-head">
                                    <div>
                                        <div class="pm-step10-kicker">
                                            Step 10 - User Licenses
                                        </div>
                                        <h6 class="pm-step10-title">
                                            Review license rules before
                                            continuing
                                        </h6>
                                        <p class="pm-step10-subtitle">
                                            Confirm user-license policy and
                                            assignment limits to avoid
                                            suspension, penalties, or compliance
                                            actions.
                                        </p>
                                    </div>
                                    <div class="pm-step10-chip">
                                        Mandatory Review
                                    </div>
                                </div>
                                <div class="pm-license-card">
                                    <div class="pm-license-header">
                                        <strong
                                            >User License Policy - Mandatory
                                            Review</strong
                                        >
                                        <div class="pm-muted">
                                            The amount charged under your
                                            selected subscription plan is
                                            calculated per user license.
                                        </div>
                                    </div>
                                    <div class="pm-license-body">
                                        <ol>
                                            <li>
                                                One user license is strictly
                                                assigned to one individual user
                                                only.
                                            </li>
                                            <li>
                                                Sharing a single user license
                                                with multiple users is strictly
                                                prohibited and considered a
                                                serious account violation.
                                            </li>
                                            <li>
                                                Each additional user requires
                                                the purchase of a separate user
                                                license.
                                            </li>
                                            <li>
                                                Each user license may be
                                                assigned to a maximum of two (2)
                                                facilities only.
                                            </li>
                                            <li>
                                                Assigning one user to more than
                                                two facilities is a license
                                                violation and may result in:
                                                <ul>
                                                    <li>Account suspension</li>
                                                    <li>
                                                        Additional licensing
                                                        fees
                                                    </li>
                                                    <li>
                                                        Enforcement action by
                                                        the Company
                                                    </li>
                                                </ul>
                                            </li>
                                        </ol>
                                        <div class="pm-license-alert">
                                            <strong>Important Notice</strong>
                                            <p>
                                                Violations of the User License
                                                policy may result in immediate
                                                suspension, account termination,
                                                financial penalties, or legal
                                                action, depending on severity.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="pm-license-ack">
                                        <label>
                                            <input
                                                type="checkbox"
                                                v-model="form.license_ack"
                                            />
                                            I understand and agree to comply
                                            with the User License rules *
                                        </label>
                                    </div>
                                </div>
                                <div class="pm-step10-meta">
                                    Only authorized users should accept this
                                    policy on behalf of the organization.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 11"
                                class="pm-setup-form pm-step11-premium"
                            >
                                <div class="pm-step11-head">
                                    <div>
                                        <div class="pm-step11-kicker">
                                            Step 11 - Billing Information
                                        </div>
                                        <h6 class="pm-step11-title">
                                            Set your payment and renewal
                                            preferences
                                        </h6>
                                        <p class="pm-step11-subtitle">
                                            Configure billing method, cycle, and
                                            policy acknowledgment to keep your
                                            subscription active.
                                        </p>
                                    </div>
                                    <div class="pm-step11-chip">
                                        Billing Control
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="pm-field-label"
                                        >Payment Method *</label
                                    >
                                    <div
                                        class="pm-method-grid pm-step11-method-grid"
                                    >
                                        <button
                                            v-for="method in billingMethods"
                                            :key="method.value"
                                            type="button"
                                            :class="[
                                                'pm-method-card',
                                                'pm-step11-method-card',
                                                {
                                                    active:
                                                        form.billing_method ===
                                                        method.value,
                                                },
                                            ]"
                                            @click="
                                                form.billing_method =
                                                    method.value
                                            "
                                        >
                                            <span class="pm-method-icon">{{
                                                method.icon
                                            }}</span>
                                            <span class="pm-method-label">{{
                                                method.label
                                            }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="pm-field-label"
                                        >Billing Address *</label
                                    >
                                    <input
                                        v-model="form.billing_address"
                                        class="form-control"
                                        placeholder="Enter complete billing address"
                                    />
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="pm-field-label"
                                            >Billing Cycle</label
                                        >
                                        <input
                                            v-model="form.billing_cycle"
                                            class="form-control"
                                            placeholder="Monthly (Charged in Advance)"
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="pm-field-label"
                                            >Auto-Renew Subscription *</label
                                        >
                                        <div
                                            class="pm-toggle-row pm-step11-toggle"
                                        >
                                            <div class="pm-muted">
                                                Automatic monthly renewal
                                            </div>
                                            <label class="pm-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="
                                                        form.billing_auto_renew
                                                    "
                                                />
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="pm-policy-card pm-policy-info pm-step11-policy"
                                >
                                    <strong
                                        >Billing Policy (Mandatory - Read
                                        Carefully)</strong
                                    >
                                    <ul>
                                        <li>
                                            All subscription charges occur at
                                            the beginning of each month, billed
                                            in advance.
                                        </li>
                                        <li>
                                            If a payment method is declined, the
                                            system will automatically attempt
                                            one (1) retry after 48 hours.
                                        </li>
                                        <li>
                                            If the second attempt fails, the
                                            account will be suspended until
                                            valid payment information is
                                            updated.
                                        </li>
                                        <li>
                                            Prorated billing applies for
                                            mid-month service starts.
                                        </li>
                                        <li>
                                            All service fees are strictly
                                            non-refundable, including partial
                                            months or unused time.
                                        </li>
                                        <li>
                                            All fees include applicable sales
                                            tax or VAT as required by local tax
                                            authorities.
                                        </li>
                                    </ul>
                                </div>
                                <div class="pm-policy-ack-inline pm-step11-ack">
                                    <label>
                                        <input
                                            type="checkbox"
                                            v-model="form.billing_policy_ack"
                                        />
                                        I have read and agree to the Billing
                                        Policy *
                                    </label>
                                </div>
                                <div class="pm-step11-meta">
                                    Billing acknowledgement is required before
                                    moving to the next step.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 12"
                                class="pm-setup-form pm-step12-premium"
                            >
                                <div class="pm-step12-head">
                                    <div>
                                        <div class="pm-step12-kicker">
                                            Step 12 - Account Administrator
                                        </div>
                                        <h6 class="pm-step12-title">
                                            Assign primary admin contact and
                                            authority
                                        </h6>
                                        <p class="pm-step12-subtitle">
                                            Add the responsible administrator
                                            who manages account controls, user
                                            access, and security approvals.
                                        </p>
                                    </div>
                                    <div class="pm-step12-chip">
                                        Admin Setup
                                    </div>
                                </div>
                                <div class="pm-step12-grid">
                                    <div class="pm-step12-panel">
                                        <label class="pm-field-label"
                                            >First Name *</label
                                        >
                                        <input
                                            v-model="form.admin_first_name"
                                            class="form-control"
                                            placeholder="Enter first name"
                                        />
                                    </div>
                                    <div class="pm-step12-panel">
                                        <label class="pm-field-label"
                                            >Last Name *</label
                                        >
                                        <input
                                            v-model="form.admin_last_name"
                                            class="form-control"
                                            placeholder="Enter last name"
                                        />
                                    </div>
                                    <div
                                        class="pm-step12-panel pm-step12-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Role / Position *</label
                                        >
                                        <select
                                            v-model="form.admin_role"
                                            class="form-control"
                                        >
                                            <option value="">
                                                Select role / position
                                            </option>
                                            <option
                                                v-for="role in adminRoleOptions"
                                                :key="`admin-role-${role}`"
                                                :value="role"
                                            >
                                                {{ role }}
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        v-if="form.admin_role === 'Other'"
                                        class="pm-step12-panel pm-step12-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Custom Role / Position *</label
                                        >
                                        <input
                                            v-model="form.admin_role_other"
                                            class="form-control"
                                            placeholder="Enter custom role"
                                        />
                                    </div>
                                    <div
                                        class="pm-step12-panel pm-step12-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >System Administrator</label
                                        >
                                        <div
                                            class="pm-toggle-row pm-step12-toggle"
                                        >
                                            <div class="pm-muted">
                                                Grant full administrative
                                                privileges to this user
                                            </div>
                                            <label class="pm-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="
                                                        form.admin_is_system
                                                    "
                                                />
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="pm-step12-panel">
                                        <label class="pm-field-label"
                                            >Phone Number *</label
                                        >
                                        <input
                                            v-model="form.admin_phone"
                                            class="form-control"
                                            placeholder="+1 (555) 123-4567"
                                        />
                                    </div>
                                    <div class="pm-step12-panel">
                                        <label class="pm-field-label"
                                            >Business Email Address *</label
                                        >
                                        <input
                                            v-model="form.admin_email"
                                            class="form-control"
                                            placeholder="admin@company.com"
                                        />
                                    </div>
                                    <div
                                        class="pm-step12-panel pm-step12-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Account Creation Date *</label
                                        >
                                        <input
                                            v-model="form.admin_created_date"
                                            type="date"
                                            class="form-control"
                                        />
                                        <div class="pm-muted mt-1">
                                            Auto-filled with today's date
                                        </div>
                                    </div>
                                </div>
                                <div class="pm-step12-meta">
                                    Use an active business email so the admin
                                    can receive critical system notifications.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 13"
                                class="pm-setup-form pm-step13-premium"
                            >
                                <div class="pm-step13-head">
                                    <div>
                                        <div class="pm-step13-kicker">
                                            Step 13 - Account Recovery & Support
                                        </div>
                                        <h6 class="pm-step13-title">
                                            Add trusted recovery contact
                                            channels
                                        </h6>
                                        <p class="pm-step13-subtitle">
                                            These contacts are used for account
                                            recovery, security escalation, and
                                            urgent support communication.
                                        </p>
                                    </div>
                                    <div class="pm-step13-chip">
                                        Recovery Ready
                                    </div>
                                </div>
                                <div class="pm-step13-grid">
                                    <div
                                        class="pm-step13-panel pm-step13-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Recovery Email Address *</label
                                        >
                                        <input
                                            v-model="form.recovery_email"
                                            class="form-control"
                                            placeholder="recovery@company.com"
                                        />
                                        <div class="pm-muted mt-1">
                                            This email will be used to recover
                                            your account if needed.
                                        </div>
                                    </div>
                                    <div
                                        class="pm-step13-panel pm-step13-panel-wide"
                                    >
                                        <label class="pm-field-label"
                                            >Security Contact Number
                                            <span class="pm-muted"
                                                >(Optional)</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.recovery_phone"
                                            class="form-control"
                                            placeholder="+1 (555) 123-4567"
                                        />
                                        <div class="pm-muted mt-1">
                                            Alternate contact number for account
                                            recovery purposes.
                                        </div>
                                    </div>
                                </div>
                                <div class="pm-step13-meta">
                                    Use contacts that stay monitored outside
                                    regular business hours.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 14"
                                class="pm-setup-form pm-step14-premium"
                            >
                                <div class="pm-step14-head">
                                    <div>
                                        <div class="pm-step14-kicker">
                                            Step 14 - Email Verification
                                        </div>
                                        <h6 class="pm-step14-title">
                                            Confirm your primary email to
                                            activate access
                                        </h6>
                                        <p class="pm-step14-subtitle">
                                            Verification protects account
                                            ownership and ensures alerts are
                                            delivered to the correct mailbox.
                                        </p>
                                    </div>
                                    <div class="pm-step14-chip">
                                        Action Required
                                    </div>
                                </div>
                                <div
                                    class="pm-terms-progress pm-step14-progress"
                                >
                                    <div class="pm-info-title">
                                        <span
                                            class="pm-info-icon"
                                            aria-hidden="true"
                                            >@</span
                                        >
                                        <span>Email Verification Required</span>
                                    </div>
                                    <div class="pm-muted">
                                        A verification email will be sent to
                                        your primary email address. Please check
                                        your inbox and follow the verification
                                        link to activate your account.
                                    </div>
                                </div>
                                <div
                                    class="pm-policy-card pm-policy-warning pm-step14-warning"
                                >
                                    <strong>Important Notice</strong>
                                    <p>
                                        Your account will remain inactive until
                                        verified. Please complete email
                                        verification within 48 hours.
                                    </p>
                                </div>
                                <div class="pm-policy-card pm-step14-help">
                                    <strong>Didn't receive the email?</strong>
                                    <ul>
                                        <li>Check your spam or junk folder.</li>
                                        <li>
                                            Ensure the email address is correct.
                                        </li>
                                        <li>
                                            Contact support if you need
                                            assistance.
                                        </li>
                                    </ul>
                                </div>
                                <div
                                    class="pm-step14-status"
                                    :class="{
                                        verified:
                                            form.email_verify_status ===
                                            'verified',
                                    }"
                                >
                                    {{
                                        form.email_verify_status === "verified"
                                            ? "Email status: Verified"
                                            : "Email status: Pending verification"
                                    }}
                                </div>
                                <div class="pm-step14-actions">
                                    <button
                                        class="btn btn-outline-primary btn-sm"
                                        type="button"
                                        :disabled="
                                            step14Loading ||
                                            form.email_verify_status ===
                                                'verified'
                                        "
                                        @click="resendStep14VerificationEmail"
                                    >
                                        {{
                                            step14Loading &&
                                            step14Action === "resend"
                                                ? "Sending..."
                                                : "Resend Verification Email"
                                        }}
                                    </button>
                                    <button
                                        class="btn btn-outline-secondary btn-sm"
                                        type="button"
                                        :disabled="step14Loading"
                                        @click="refreshStep14VerificationStatus"
                                    >
                                        {{
                                            step14Loading &&
                                            step14Action === "refresh"
                                                ? "Refreshing..."
                                                : "Refresh Status"
                                        }}
                                    </button>
                                </div>
                                <div
                                    v-if="step14Message"
                                    class="pm-step14-message"
                                >
                                    {{ step14Message }}
                                </div>
                                <div class="pm-step14-meta">
                                    Use a business mailbox that is monitored
                                    regularly by your admin team.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 15"
                                class="pm-setup-form pm-step15-premium"
                            >
                                <div class="pm-step15-head">
                                    <div>
                                        <div class="pm-step15-kicker">
                                            Step 15 - Captcha Verification
                                        </div>
                                        <h6 class="pm-step15-title">
                                            Confirm human verification before
                                            final steps
                                        </h6>
                                        <p class="pm-step15-subtitle">
                                            This checkpoint helps prevent
                                            automated misuse and protects
                                            account integrity.
                                        </p>
                                    </div>
                                    <div class="pm-step15-chip">
                                        Human Check
                                    </div>
                                </div>
                                <div
                                    class="pm-terms-box text-center pm-step15-box"
                                >
                                    <div class="pm-captcha-icon">Shield</div>
                                    <div class="pm-terms-header">
                                        Security Verification
                                    </div>
                                    <div class="pm-muted">
                                        Please verify that you are human to
                                        proceed with account creation.
                                    </div>
                                    <div class="pm-step15-widget-wrap mt-3">
                                        <div
                                            v-if="!captchaSiteKey"
                                            class="pm-step15-error"
                                        >
                                            Captcha site key is missing. Set
                                            <code>VITE_RECAPTCHA_SITE_KEY</code>
                                            and refresh the page.
                                        </div>
                                        <template v-else>
                                            <div
                                                v-show="!form.captcha_ack"
                                                ref="captchaRef"
                                                class="pm-step15-widget"
                                            ></div>
                                            <div
                                                class="pm-step15-ack"
                                                :class="{
                                                    verified: form.captcha_ack,
                                                }"
                                            >
                                                {{
                                                    form.captcha_ack
                                                        ? "Verification complete."
                                                        : "Complete the 'I am not a robot' challenge."
                                                }}
                                            </div>
                                            <button
                                                class="btn btn-outline-primary btn-sm pm-step15-reset"
                                                type="button"
                                                @click="
                                                    resetCaptchaVerification
                                                "
                                            >
                                                Reset Verification
                                            </button>
                                        </template>
                                        <div
                                            v-if="captchaLoading"
                                            class="pm-step15-meta"
                                        >
                                            Loading captcha challenge...
                                        </div>
                                        <div
                                            v-if="captchaError"
                                            class="pm-step15-error"
                                        >
                                            {{ captchaError }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="pm-policy-card pm-policy-info pm-step15-note"
                                >
                                    <strong>Note:</strong>
                                    <p>
                                        This verification helps protect our
                                        platform from automated abuse and
                                        ensures the security of all user
                                        accounts.
                                    </p>
                                </div>
                                <div class="pm-step15-meta">
                                    Please complete this check from a trusted
                                    browser and network.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 16"
                                class="pm-setup-form pm-step16-premium"
                            >
                                <div class="pm-step16-head">
                                    <div>
                                        <div class="pm-step16-kicker">
                                            Step 16 - Account Safety Rules
                                        </div>
                                        <h6 class="pm-step16-title">
                                            Security requirements for all
                                            administrators and users
                                        </h6>
                                        <p class="pm-step16-subtitle">
                                            These rules are mandatory and
                                            designed to reduce credential
                                            compromise, account takeover, and
                                            unauthorized access.
                                        </p>
                                    </div>
                                    <div class="pm-step16-chip">Mandatory</div>
                                </div>

                                <div
                                    class="pm-policy-card pm-policy-danger pm-step16-card"
                                >
                                    <div
                                        class="pm-policy-title pm-step16-policy-title"
                                    >
                                        <span class="pm-safety-icon"
                                            >Shield</span
                                        >
                                        Mandatory Security Awareness Section
                                    </div>
                                    <div
                                        class="pm-license-body pm-step16-rules"
                                    >
                                        <ol>
                                            <li>
                                                Users must not save their
                                                username, password, or security
                                                PIN in any web browser.
                                            </li>
                                            <li>
                                                Users must not store credentials
                                                in documents, notes, phone apps,
                                                screenshots, or physical files.
                                            </li>
                                            <li>
                                                Users must not share login
                                                credentials with unauthorized
                                                parties, vendors, or
                                                contractors.
                                            </li>
                                            <li>
                                                Only authorized personnel may
                                                change passwords or security
                                                PINs.
                                            </li>
                                            <li>
                                                Automatic logout: Users will be
                                                logged out after one (1) hour of
                                                inactivity.
                                            </li>
                                            <li>
                                                Users must follow standard web
                                                application security best
                                                practices.
                                            </li>
                                        </ol>
                                    </div>
                                </div>

                                <div
                                    class="pm-policy-card pm-policy-warning pm-step16-alert"
                                >
                                    <strong>Enforcement Notice</strong>
                                    <p>
                                        Policy violations may result in account
                                        restrictions, temporary suspension, or
                                        formal compliance review.
                                    </p>
                                </div>

                                <div
                                    v-if="form.safety_ack_at"
                                    class="pm-policy-card pm-policy-info pm-step16-proof"
                                >
                                    <strong>Compliance Record</strong>
                                    <p>
                                        Accepted at
                                        {{ safetyAcceptedAtLabel }} under policy
                                        version
                                        {{
                                            form.safety_policy_version ||
                                            "legacy"
                                        }}.
                                    </p>
                                </div>

                                <div
                                    class="pm-policy-ack-inline pm-step16-ack"
                                    :class="{ locked: !!form.safety_ack_at }"
                                >
                                    <label>
                                        <input
                                            type="checkbox"
                                            v-model="form.safety_ack"
                                            :disabled="!!form.safety_ack_at"
                                        />
                                        {{
                                            form.safety_ack_at
                                                ? "Account Safety requirements already accepted and recorded."
                                                : "I understand and agree to follow all Account Safety requirements *"
                                        }}
                                    </label>
                                </div>

                                <div class="pm-step16-meta">
                                    Access is monitored and security events can
                                    be audited by authorized company personnel.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 17"
                                class="pm-setup-form pm-step17-premium"
                            >
                                <div class="pm-step17-head">
                                    <div>
                                        <div class="pm-step17-kicker">
                                            Step 17 - Users Terms & Conditions
                                        </div>
                                        <h6 class="pm-step17-title">
                                            Review legal terms and confirm
                                            compliance acceptance
                                        </h6>
                                        <p class="pm-step17-subtitle">
                                            You must scroll the full legal
                                            document at least twice before
                                            agreement checkboxes become
                                            available.
                                        </p>
                                    </div>
                                    <div class="pm-step17-chip">
                                        Legal Review
                                    </div>
                                </div>

                                <div
                                    class="pm-terms-progress pm-step17-progress"
                                >
                                    <div class="pm-step17-progress-top">
                                        <div class="pm-terms-progress-label">
                                            Scroll Progress:
                                            {{ termsScrollCount }} of 2 required
                                        </div>
                                        <div class="pm-terms-progress-hint">
                                            Remaining: {{ termsRemainingHint }}
                                        </div>
                                    </div>
                                    <div class="pm-step17-track">
                                        <span
                                            :style="{
                                                width: `${termsProgressPercent}%`,
                                            }"
                                        ></span>
                                    </div>
                                </div>

                                <div class="pm-terms-box pm-step17-box">
                                    <div class="pm-terms-header">
                                        Terms and Conditions (Full Legal
                                        Version)
                                    </div>
                                    <div
                                        class="pm-terms-body pm-step17-body"
                                        ref="termsScrollRef"
                                        @scroll="onTermsScroll"
                                    >
                                        <ol>
                                            <li>
                                                The Platform is strictly for
                                                parcel and delivery management
                                                purposes only.
                                            </li>
                                            <li>
                                                The Company is not responsible
                                                for lost, damaged, delayed,
                                                stolen, or missing parcels.
                                            </li>
                                            <li>
                                                No illegal, prohibited, or
                                                forbidden items may be managed,
                                                recorded, stored, or tracked
                                                using this Platform.
                                            </li>
                                            <li>
                                                Users must not use the Platform
                                                for illegal activities, abuse,
                                                fraud, or unlawful purposes.
                                            </li>
                                            <li>
                                                All service fees are strictly
                                                non-refundable, including
                                                partial months or unused time.
                                            </li>
                                            <li>
                                                Users are fully responsible for
                                                actions under their account,
                                                including actions by staff or
                                                agents.
                                            </li>
                                            <li>
                                                Credential sharing is
                                                prohibited. Unauthorized sharing
                                                may result in account closure
                                                and legal action.
                                            </li>
                                            <li>
                                                The Platform is provided "as is"
                                                without any guarantee of
                                                uninterrupted or error-free
                                                service.
                                            </li>
                                            <li>
                                                These Terms are governed by the
                                                Company's legal jurisdiction,
                                                and users consent to that
                                                jurisdiction.
                                            </li>
                                            <li>
                                                Account closure requests must be
                                                submitted via Contact Us at
                                                least 14 days in advance.
                                            </li>
                                        </ol>
                                        <div class="pm-terms-highlight">
                                            Unauthorized use of the Platform
                                            (including development, testing,
                                            training, or simulation without
                                            approval) is prohibited.
                                        </div>
                                        <div class="pm-terms-updated">
                                            Last Updated: January 26, 2026
                                        </div>
                                    </div>
                                    <div class="pm-terms-footer">
                                        <span
                                            class="pm-terms-warning"
                                            :class="{
                                                'is-ready':
                                                    termsAcceptedEnabled,
                                            }"
                                        >
                                            {{
                                                termsAcceptedEnabled
                                                    ? "Scroll requirement complete. You can accept the legal terms now."
                                                    : `Please scroll to the bottom ${termsRemainingHint} to enable acceptance`
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="pm-step17-acks">
                                    <div
                                        class="pm-terms-ack pm-step17-ack"
                                        :class="{ ready: termsAcceptedEnabled }"
                                    >
                                        <label>
                                            <input
                                                type="checkbox"
                                                v-model="form.terms_ack"
                                                :disabled="
                                                    !termsAcceptedEnabled
                                                "
                                            />
                                            I have read, understood, and agree
                                            to the Users Terms and Conditions *
                                        </label>
                                    </div>
                                    <div
                                        class="pm-terms-ack pm-step17-ack"
                                        :class="{ ready: termsAcceptedEnabled }"
                                    >
                                        <label>
                                            <input
                                                type="checkbox"
                                                v-model="form.privacy_ack"
                                                :disabled="
                                                    !termsAcceptedEnabled
                                                "
                                            />
                                            I accept the Privacy Policy *
                                        </label>
                                    </div>
                                </div>

                                <div class="pm-step17-meta">
                                    These acknowledgements are retained in your
                                    setup compliance record.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 18"
                                class="pm-setup-form pm-step18-premium"
                            >
                                <div class="pm-step18-head">
                                    <div>
                                        <div class="pm-step18-kicker">
                                            Step 18 - Final Confirmation
                                        </div>
                                        <h6 class="pm-step18-title">
                                            Review setup details and complete
                                            registration
                                        </h6>
                                        <p class="pm-step18-subtitle">
                                            Confirm your key data points below,
                                            then submit to create your account
                                            and trigger final activation flow.
                                        </p>
                                    </div>
                                    <div class="pm-step18-chip">
                                        Ready to Submit
                                    </div>
                                </div>

                                <div class="pm-final-summary pm-step18-summary">
                                    <div class="pm-final-summary-title">
                                        Registration Snapshot
                                    </div>
                                    <div
                                        class="pm-final-summary-grid pm-step18-summary-grid"
                                    >
                                        <div
                                            v-for="item in summaryItems"
                                            :key="item.label"
                                            class="pm-step18-summary-item"
                                        >
                                            <div class="pm-final-label">
                                                {{ item.label }}:
                                            </div>
                                            <div>{{ item.value }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pm-step18-grid">
                                    <div
                                        class="pm-final-reminders pm-step18-reminders"
                                    >
                                        <strong>Important Reminders</strong>
                                        <ul>
                                            <li>
                                                A verification email will be
                                                sent to
                                                {{
                                                    form.recovery_email ||
                                                    "your primary email"
                                                }}.
                                            </li>
                                            <li>
                                                Your account will remain
                                                inactive until email
                                                verification is complete.
                                            </li>
                                            <li>
                                                Please verify your email within
                                                48 hours.
                                            </li>
                                            <li>
                                                Remember to update your password
                                                and PIN every 30 days.
                                            </li>
                                            <li>
                                                Monthly billing will begin after
                                                account activation.
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="pm-final-next pm-step18-next">
                                        <strong>What happens next?</strong>
                                        <ol>
                                            <li>
                                                Your account will be created and
                                                a confirmation email sent to
                                                your inbox.
                                            </li>
                                            <li>
                                                Click the verification link in
                                                the email to activate your
                                                account.
                                            </li>
                                            <li>
                                                Log in with your username and
                                                password to access the platform.
                                            </li>
                                            <li>
                                                Complete your profile setup and
                                                start managing your parcels.
                                            </li>
                                        </ol>
                                    </div>
                                </div>

                                <div class="pm-final-ack pm-step18-ack">
                                    <span
                                        >You have agreed to the Terms &
                                        Conditions and Privacy Policy</span
                                    >
                                </div>

                                <div class="pm-final-actions pm-step18-actions">
                                    <button
                                        class="btn btn-outline-primary"
                                        type="button"
                                        @click="goToStep(17)"
                                    >
                                        Back to Review
                                    </button>
                                    <button
                                        class="btn btn-success"
                                        type="button"
                                        @click="next"
                                    >
                                        Save & Complete Registration
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="
                                    currentStep < 1 ||
                                    currentStep > steps.length
                                "
                                class="pm-setup-placeholder"
                            >
                                <p>
                                    Step content will appear here. You can
                                    continue to the next step and we will attach
                                    the next screen when you provide it.
                                </p>
                            </div>

                            <div v-if="error" class="pm-form-error">
                                {{ error }}
                            </div>
                            <div v-if="success" class="pm-form-success">
                                Saved. Continue to the next step.
                            </div>

                            <div v-if="!isFinalStep" class="pm-setup-actions">
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
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import logoWhite from "../assets/logo-white.png";
import client from "../api/client";
import { clearToken, logout } from "../api/auth";
import { authState, setUser } from "../store/auth";
import { LANGUAGE_OPTIONS, LANGUAGE_CODES } from "../config/languages";

const router = useRouter();
const loading = ref(false);
const error = ref("");
const success = ref(false);
const countries = ref([]);
const step14Loading = ref(false);
const step14Message = ref("");
const step14Action = ref("");

const steps = [
    {
        key: "company",
        title: "Company Information",
        subtitle: "Please provide your company details and logo.",
    },
    {
        key: "address",
        title: "Company Address",
        subtitle: "Provide office address and location details.",
    },
    {
        key: "facility",
        title: "Building / Facility Details",
        subtitle: "Share building configuration and size.",
    },
    {
        key: "usage",
        title: "Facility Usage",
        subtitle: "Tell us how parcels are handled today.",
    },
    {
        key: "contact",
        title: "Contact Information",
        subtitle: "Primary contacts for your account.",
    },
    {
        key: "preferences",
        title: "Account Preferences",
        subtitle: "Set operational preferences and defaults.",
    },
    {
        key: "notifications",
        title: "Notification Preferences",
        subtitle: "Select email and SMS delivery rules.",
    },
    {
        key: "security",
        title: "Account Security",
        subtitle: "Configure security and recovery options.",
    },
    {
        key: "subscription",
        title: "Subscription Plan",
        subtitle: "Choose plan and billing cadence.",
    },
    {
        key: "licenses",
        title: "User Licenses",
        subtitle: "Allocate staff access and roles.",
    },
    {
        key: "billing",
        title: "Billing Information",
        subtitle: "Provide billing address and invoice details.",
    },
    {
        key: "admin",
        title: "Account Administrator",
        subtitle: "Set primary admin information.",
    },
    {
        key: "recovery",
        title: "Account Recovery & Support",
        subtitle: "Recovery contacts and escalation.",
    },
    {
        key: "email",
        title: "Email Verification",
        subtitle: "Confirm primary email routing.",
    },
    {
        key: "captcha",
        title: "Captcha Verification",
        subtitle: "Add security verification.",
    },
    {
        key: "safety",
        title: "Account Safety Rules",
        subtitle: "Set usage and compliance rules.",
    },
    {
        key: "terms",
        title: "Users Terms & Conditions",
        subtitle: "Confirm acceptance of platform terms.",
    },
    {
        key: "final",
        title: "Final Confirmation",
        subtitle: "Review and submit your setup.",
    },
];

const RECAPTCHA_TEST_SITE_KEY = "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI";

const currentStep = ref(1);
const termsScrollRef = ref(null);
const termsScrollCount = ref(0);
const captchaRef = ref(null);
const captchaLoading = ref(false);
const captchaError = ref("");
const captchaToken = ref("");
const captchaWidgetId = ref(null);
let captchaScriptPromise = null;
const captchaSiteKey = computed(() => {
    const configured = String(
        import.meta.env.VITE_RECAPTCHA_SITE_KEY || "",
    ).trim();
    if (configured) return configured;

    if (typeof window === "undefined") return "";
    const host = String(window.location?.hostname || "").toLowerCase();
    const isLocalHost =
        host === "localhost" || host === "127.0.0.1" || host === "::1";
    return isLocalHost ? RECAPTCHA_TEST_SITE_KEY : "";
});
const safetyAcceptedAtLabel = computed(() => {
    const raw = form.value.safety_ack_at;
    if (!raw) return "";

    const parsed = new Date(raw);
    if (Number.isNaN(parsed.getTime())) {
        return String(raw);
    }

    return parsed.toLocaleString();
});
const termsAcceptedEnabled = computed(() => termsScrollCount.value >= 2);
const termsRemainingHint = computed(() => {
    const remaining = Math.max(0, 2 - termsScrollCount.value);
    if (remaining === 0) return "done";
    if (remaining === 1) return "1 more time";
    return `${remaining} more times`;
});
const termsProgressPercent = computed(() =>
    Math.min(100, Math.round((termsScrollCount.value / 2) * 100)),
);
const summaryItems = computed(() => {
    const adminName = [form.value.admin_first_name, form.value.admin_last_name]
        .filter(Boolean)
        .join(" ");
    const items = [
        { label: "Company", value: form.value.company_name },
        { label: "Facility", value: form.value.facility_name },
        {
            label: "Business Email",
            value:
                form.value.contact_business_email || form.value.recovery_email,
        },
        { label: "Username", value: form.value.security_username },
        { label: "Subscription Plan", value: form.value.subscription_plan },
        { label: "Administrator", value: adminName },
    ];
    return items.map((item) => ({
        label: item.label,
        value: item.value || "Not Provided",
    }));
});

const timezoneOptions = computed(() => {
    if (
        typeof Intl !== "undefined" &&
        typeof Intl.supportedValuesOf === "function"
    ) {
        try {
            const values = Intl.supportedValuesOf("timeZone");
            if (Array.isArray(values) && values.length) {
                return values;
            }
        } catch {
            // Fall back to static list.
        }
    }

    return [
        "UTC",
        "America/New_York",
        "America/Chicago",
        "America/Denver",
        "America/Los_Angeles",
        "America/Toronto",
        "Europe/London",
        "Europe/Paris",
        "Asia/Dhaka",
        "Asia/Kolkata",
        "Asia/Dubai",
        "Asia/Singapore",
        "Asia/Tokyo",
        "Australia/Sydney",
    ];
});

const languageOptions = LANGUAGE_OPTIONS;
const languageCodeSet = new Set(LANGUAGE_CODES);

const dateFormatOptions = [
    "YYYY-MM-DD",
    "DD-MM-YYYY",
    "MM-DD-YYYY",
    "DD/MM/YYYY",
    "MM/DD/YYYY",
    "YYYY/MM/DD",
];

const adminRoleOptions = [
    "System Administrator",
    "Manager",
    "Operations Manager",
    "Front Desk Manager",
    "Security Supervisor",
    "IT Administrator",
    "Compliance Manager",
    "Other",
];

const form = ref({
    company_name: "",
    company_logo_url: "",
    company_logo_path: "",
    company_logo_preview: "",
    company_address: "",
    company_city: "",
    company_state: "",
    company_zip: "",
    company_country_id: "",
    company_country: "",
    company_phone: "",
    facility_name: "",
    facility_part_number: "",
    facility_address: "",
    facility_city: "",
    facility_state: "",
    facility_zip: "",
    facility_country_id: "",
    facility_country: "",
    facility_phone: "",
    facility_email: "",
    facility_usage: [
        {
            name: "",
            part: "",
            city: "",
            country_id: "",
            country: "",
            assigned: 1,
            status: "active",
        },
    ],
    contact_primary_phone: "",
    contact_mobile_phone: "",
    contact_business_email: "",
    contact_alt_email: "",
    contact_website: "",
    pref_timezone: "",
    pref_language: "",
    pref_date_format: "",
    notify_email: true,
    notify_sms: false,
    notify_arrival: true,
    notify_security: true,
    notify_marketing: false,
    security_username: "",
    security_password: "",
    security_password_confirm: "",
    security_pin: "",
    security_2fa: false,
    security_policy_ack: false,
    subscription_plan: "standard",
    license_ack: false,
    billing_method: "",
    billing_address: "",
    billing_cycle: "Monthly (Charged in Advance)",
    billing_auto_renew: false,
    billing_policy_ack: false,
    admin_first_name: "",
    admin_last_name: "",
    admin_role: "",
    admin_role_other: "",
    admin_is_system: false,
    admin_phone: "",
    admin_email: "",
    admin_created_date: new Date().toISOString().slice(0, 10),
    recovery_email: "",
    recovery_phone: "",
    email_verify_status: "pending",
    captcha_ack: false,
    safety_ack: false,
    safety_ack_at: "",
    safety_policy_version: "",
    terms_ack: false,
    privacy_ack: false,
    final_ack: true,
});

const brandName = computed(
    () =>
        (
            form.value.company_name ||
            authState.user?.company_name ||
            ""
        ).trim() || "DeskDrop",
);
const brandLogo = computed(
    () =>
        form.value.company_logo_url ||
        form.value.company_logo_preview ||
        logoWhite,
);

const applyAuthDefaults = () => {
    if (!form.value.company_name && authState.user?.company_name) {
        form.value.company_name = authState.user.company_name;
    }
};

const progressPercent = computed(() => {
    if (currentStep.value >= steps.length) return 100;
    const raw = Math.round(((currentStep.value - 1) / steps.length) * 100);
    return raw > 0 ? raw : 5;
});
const isFinalStep = computed(() => currentStep.value >= steps.length);

const stepStatus = (stepIndex) => {
    if (currentStep.value === stepIndex) return "In Progress";
    if (currentStep.value > stepIndex) return "Completed";
    return "Pending";
};

const goToStep = (step) => {
    if (step < currentStep.value) {
        currentStep.value = step;
    }
};

const goSupport = () => {
    router.push("/contact");
};

const resolveCountryIdByName = (name) => {
    const normalized = (name || "").trim().toLowerCase();
    if (!normalized) return "";
    const found = countries.value.find(
        (country) => (country.country_name || "").toLowerCase() === normalized,
    );
    return found?.id || "";
};

const resolveCountryNameById = (id) => {
    if (!id) return "";
    const found = countries.value.find(
        (country) => Number(country.id) === Number(id),
    );
    return found?.country_name || "";
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

const normalizeFormPhoneFieldsForStep = (step) => {
    const fieldsByStep = {
        2: ["company_phone"],
        3: ["facility_phone"],
        5: ["contact_primary_phone", "contact_mobile_phone"],
        12: ["admin_phone"],
        13: ["recovery_phone"],
    };

    const fields = fieldsByStep[step] || [];
    fields.forEach((field) => {
        form.value[field] = normalizePhoneNumber(form.value[field]);
    });
};

const syncCountryNamesFromIds = (payload) => {
    if (payload.company_country_id) {
        payload.company_country =
            resolveCountryNameById(payload.company_country_id) ||
            payload.company_country ||
            "";
    }
    if (payload.facility_country_id) {
        payload.facility_country =
            resolveCountryNameById(payload.facility_country_id) ||
            payload.facility_country ||
            "";
    }
    if (Array.isArray(payload.facility_usage)) {
        payload.facility_usage = payload.facility_usage.map((row) => {
            if (!row) return row;
            const next = { ...row };
            if (next.country_id) {
                next.country =
                    resolveCountryNameById(next.country_id) ||
                    next.country ||
                    "";
            }
            return next;
        });
    }
};

const normalizeAdminRoleSelection = (target) => {
    const role = String(target.admin_role || "").trim();
    if (!role) {
        target.admin_role = "";
        target.admin_role_other = "";
        return;
    }

    if (adminRoleOptions.includes(role)) {
        target.admin_role = role;
        if (role !== "Other") {
            target.admin_role_other = "";
        }
        return;
    }

    target.admin_role = "Other";
    target.admin_role_other = role;
};

const applyAdminRolePayload = (payload) => {
    const selected = String(payload.admin_role || "").trim();
    const custom = String(payload.admin_role_other || "").trim();
    payload.admin_role = selected === "Other" ? custom : selected;
    delete payload.admin_role_other;
};

const ensureCaptchaScript = async () => {
    if (typeof window === "undefined") return null;
    if (window.grecaptcha && typeof window.grecaptcha.render === "function") {
        return window.grecaptcha;
    }
    if (captchaScriptPromise) return captchaScriptPromise;

    captchaLoading.value = true;
    captchaError.value = "";
    captchaScriptPromise = new Promise((resolve, reject) => {
        const existing = document.querySelector(
            'script[data-recaptcha="google-v2"]',
        );
        if (existing) {
            existing.addEventListener(
                "load",
                () => {
                    if (
                        window.grecaptcha &&
                        typeof window.grecaptcha.ready === "function"
                    ) {
                        window.grecaptcha.ready(() =>
                            resolve(window.grecaptcha),
                        );
                        return;
                    }
                    reject(new Error("grecaptcha unavailable after load"));
                },
                { once: true },
            );
            existing.addEventListener(
                "error",
                () => reject(new Error("captcha script load failed")),
                { once: true },
            );
            return;
        }

        const script = document.createElement("script");
        script.src = "https://www.google.com/recaptcha/api.js?render=explicit";
        script.async = true;
        script.defer = true;
        script.dataset.recaptcha = "google-v2";
        script.onload = () => {
            if (
                window.grecaptcha &&
                typeof window.grecaptcha.ready === "function"
            ) {
                window.grecaptcha.ready(() => resolve(window.grecaptcha));
                return;
            }
            reject(new Error("grecaptcha unavailable after load"));
        };
        script.onerror = () => reject(new Error("captcha script load failed"));
        document.head.appendChild(script);
    });

    try {
        return await captchaScriptPromise;
    } finally {
        captchaLoading.value = false;
    }
};

const markCaptchaUnverified = () => {
    captchaToken.value = "";
    form.value.captcha_ack = false;
};

const renderCaptchaWidget = async () => {
    if (currentStep.value !== 15 || form.value.captcha_ack) return;
    if (!captchaSiteKey.value) return;

    await nextTick();
    const host = captchaRef.value;
    if (!host) return;

    try {
        const grecaptcha = await ensureCaptchaScript();
        if (!grecaptcha) return;
        captchaError.value = "";

        if (captchaWidgetId.value !== null) {
            grecaptcha.reset(captchaWidgetId.value);
            return;
        }

        captchaWidgetId.value = grecaptcha.render(host, {
            sitekey: captchaSiteKey.value,
            callback: (token) => {
                captchaToken.value = String(token || "").trim();
                form.value.captcha_ack = captchaToken.value.length > 0;
                if (form.value.captcha_ack) {
                    error.value = "";
                }
            },
            "expired-callback": () => {
                markCaptchaUnverified();
            },
            "error-callback": () => {
                markCaptchaUnverified();
                captchaError.value = "Captcha error occurred. Please retry.";
            },
        });
    } catch {
        captchaError.value =
            "Captcha could not be loaded. Please check your network and retry.";
    }
};

const resetCaptchaVerification = async () => {
    markCaptchaUnverified();
    if (!captchaSiteKey.value) return;

    try {
        const grecaptcha = await ensureCaptchaScript();
        if (!grecaptcha) return;
        if (captchaWidgetId.value === null) {
            await renderCaptchaWidget();
            return;
        }
        grecaptcha.reset(captchaWidgetId.value);
    } catch {
        captchaError.value = "Captcha reset failed. Please refresh this step.";
    }
};

const loadCountries = async () => {
    try {
        const { data } = await client.get("/countries");
        if (data?.success && Array.isArray(data.data)) {
            countries.value = data.data;
            return;
        }
    } catch {
        // Keep UI usable even if country API fails.
    }
    countries.value = [];
};

const loadSetup = async () => {
    try {
        const { data } = await client.get("/account-setup");
        if (data?.success && data?.data) {
            const setup = data.data;
            currentStep.value = Number(setup.current_step) || 1;
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
            if (!next.facility_country_id && next.facility_country) {
                next.facility_country_id = resolveCountryIdByName(
                    next.facility_country,
                );
            }
            if (!next.facility_country && next.facility_country_id) {
                next.facility_country = resolveCountryNameById(
                    next.facility_country_id,
                );
            }
            if (
                Array.isArray(setup.facility_usage) &&
                setup.facility_usage.length
            ) {
                next.facility_usage = setup.facility_usage.map((row) => ({
                    name: row.name || "",
                    part: row.part || "",
                    city: row.city || "",
                    country_id:
                        row.country_id || resolveCountryIdByName(row.country),
                    country:
                        row.country || resolveCountryNameById(row.country_id),
                    assigned: row.assigned ?? 0,
                    status: row.status || "active",
                }));
            }
            if (!next.company_name && authState.user?.company_name) {
                next.company_name = authState.user.company_name;
            }
            normalizeAdminRoleSelection(next);
            form.value = next;
        }
    } catch {
        if (!form.value.company_name && authState.user?.company_name) {
            form.value.company_name = authState.user.company_name;
        }
    }
};

const refreshStep14VerificationStatus = async () => {
    step14Message.value = "";
    step14Action.value = "refresh";
    step14Loading.value = true;
    try {
        const { data } = await client.get("/me");
        if (data?.success && data?.data) {
            setUser(data.data);
            form.value.email_verify_status = data.data.email_verified_at
                ? "verified"
                : "pending";
            step14Message.value = data.data.email_verified_at
                ? "Email verified. You can continue to the next step."
                : "Email is still pending verification. Please check your inbox.";
            return;
        }
        step14Message.value =
            "Unable to refresh verification status right now.";
    } catch (e) {
        step14Message.value =
            e?.response?.data?.message ||
            "Unable to refresh verification status right now.";
    } finally {
        step14Loading.value = false;
        step14Action.value = "";
    }
};

const resendStep14VerificationEmail = async () => {
    step14Message.value = "";
    step14Action.value = "resend";
    step14Loading.value = true;
    try {
        const { data } = await client.post("/verify/email/resend");
        step14Message.value =
            data?.message ||
            "Verification email resent. Please check your inbox.";
    } catch (e) {
        step14Message.value =
            e?.response?.data?.message ||
            "Failed to resend verification email.";
    } finally {
        step14Loading.value = false;
        step14Action.value = "";
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
    } catch {
        error.value = "Logo upload failed. Please try again.";
    }
};

const saveStep = async () => {
    error.value = "";
    success.value = false;
    normalizeFormPhoneFieldsForStep(currentStep.value);
    if (currentStep.value === 1 && !form.value.company_name) {
        error.value = "Company name is required.";
        return false;
    }
    if (currentStep.value === 2) {
        if (!form.value.company_address) {
            error.value = "Street address is required.";
            return false;
        }
        if (!form.value.company_city) {
            error.value = "City is required.";
            return false;
        }
        if (!form.value.company_state) {
            error.value = "State / Province is required.";
            return false;
        }
        if (!form.value.company_zip) {
            error.value = "ZIP / Postal Code is required.";
            return false;
        }
        if (!form.value.company_country_id) {
            error.value = "Country is required.";
            return false;
        }
        if (!form.value.company_phone) {
            error.value = "Main phone number is required.";
            return false;
        }
        if (!isE164PhoneNumber(form.value.company_phone)) {
            error.value =
                "Phone number must be in E.164 format (e.g. +14165550100).";
            return false;
        }
    }
    if (currentStep.value === 3) {
        if (!form.value.facility_name) {
            error.value = "Facility name is required.";
            return false;
        }
        if (!form.value.facility_part_number) {
            error.value = "Part number is required.";
            return false;
        }
        if (!form.value.facility_address) {
            error.value = "Street address is required.";
            return false;
        }
        if (!form.value.facility_city) {
            error.value = "City is required.";
            return false;
        }
        if (!form.value.facility_state) {
            error.value = "State / Province is required.";
            return false;
        }
        if (!form.value.facility_zip) {
            error.value = "ZIP / Postal Code is required.";
            return false;
        }
        if (!form.value.facility_country_id) {
            error.value = "Country is required.";
            return false;
        }
        if (!form.value.facility_phone) {
            error.value = "Phone number is required.";
            return false;
        }
        if (!isE164PhoneNumber(form.value.facility_phone)) {
            error.value =
                "Phone number must be in E.164 format (e.g. +14165550100).";
            return false;
        }
        if (!form.value.facility_email) {
            error.value = "Email address is required.";
            return false;
        }
    }
    if (currentStep.value === 4) {
        if (!form.value.facility_usage.length) {
            error.value = "Please add at least one facility.";
            return false;
        }
        const invalidRowIndex = form.value.facility_usage.findIndex((row) => {
            const assigned = Number(row.assigned);
            return (
                !row.name ||
                !row.part ||
                !row.city ||
                !row.country_id ||
                Number.isNaN(assigned) ||
                assigned < 0
            );
        });
        if (invalidRowIndex !== -1) {
            error.value = `Please complete all required fields in Facility row ${invalidRowIndex + 1}.`;
            return false;
        }
        const activeAssignedRows = form.value.facility_usage.filter((row) => {
            const assigned = Number(row.assigned);
            return (
                (row.status || "active") === "active" &&
                !Number.isNaN(assigned) &&
                assigned > 0
            );
        });
        if (activeAssignedRows.length > 2) {
            error.value =
                "Each user license may be assigned to at most two active facilities.";
            return false;
        }
    }
    if (currentStep.value === 5) {
        if (!form.value.contact_primary_phone) {
            error.value = "Primary phone number is required.";
            return false;
        }
        if (!isE164PhoneNumber(form.value.contact_primary_phone)) {
            error.value =
                "Phone number must be in E.164 format (e.g. +14165550100).";
            return false;
        }
        if (
            form.value.contact_mobile_phone &&
            !isE164PhoneNumber(form.value.contact_mobile_phone)
        ) {
            error.value =
                "Mobile phone must be in E.164 format (e.g. +14165550100).";
            return false;
        }
        if (!form.value.contact_business_email) {
            error.value = "Business email address is required.";
            return false;
        }
    }
    if (currentStep.value === 6) {
        if (!form.value.pref_timezone) {
            error.value = "Time zone is required.";
            return false;
        }
        if (!form.value.pref_language) {
            error.value = "Preferred language is required.";
            return false;
        }
        if (!form.value.pref_date_format) {
            error.value = "Date format is required.";
            return false;
        }
    }
    if (currentStep.value === 7) {
        if (!form.value.notify_email && !form.value.notify_sms) {
            error.value = "Select at least one notification channel.";
            return false;
        }
    }
    if (currentStep.value === 8) {
        if (!form.value.security_username) {
            error.value = "Username is required.";
            return false;
        }
        if (!form.value.security_password) {
            error.value = "Password is required.";
            return false;
        }
        if (form.value.security_password.length < 8) {
            error.value = "Password must be at least 8 characters.";
            return false;
        }
        if (
            form.value.security_password !==
            form.value.security_password_confirm
        ) {
            error.value = "Passwords do not match.";
            return false;
        }
        if (!form.value.security_pin) {
            error.value = "Security PIN is required.";
            return false;
        }
        if (!/^\d{4,6}$/.test(form.value.security_pin)) {
            error.value = "Security PIN must be 4 to 6 digits.";
            return false;
        }
        if (!form.value.security_policy_ack) {
            error.value = "You must accept the Password & PIN Rotation Policy.";
            return false;
        }
    }
    if (currentStep.value === 9) {
        if (!form.value.subscription_plan) {
            error.value = "Please select a subscription plan.";
            return false;
        }
    }
    if (currentStep.value === 10) {
        if (!form.value.license_ack) {
            error.value = "You must accept the User License rules.";
            return false;
        }
    }
    if (currentStep.value === 11) {
        if (!form.value.billing_method) {
            error.value = "Please select a payment method.";
            return false;
        }
        if (!form.value.billing_address) {
            error.value = "Billing address is required.";
            return false;
        }
        if (!form.value.billing_policy_ack) {
            error.value = "You must accept the Billing Policy.";
            return false;
        }
    }
    if (currentStep.value === 12) {
        if (!form.value.admin_first_name) {
            error.value = "First name is required.";
            return false;
        }
        if (!form.value.admin_last_name) {
            error.value = "Last name is required.";
            return false;
        }
        if (!form.value.admin_role) {
            error.value = "Role / position is required.";
            return false;
        }
        if (
            form.value.admin_role === "Other" &&
            !String(form.value.admin_role_other || "").trim()
        ) {
            error.value = "Custom role / position is required.";
            return false;
        }
        if (!form.value.admin_phone) {
            error.value = "Phone number is required.";
            return false;
        }
        if (!isE164PhoneNumber(form.value.admin_phone)) {
            error.value =
                "Phone number must be in E.164 format (e.g. +14165550100).";
            return false;
        }
        if (!form.value.admin_email) {
            error.value = "Business email is required.";
            return false;
        }
        if (!form.value.admin_created_date) {
            error.value = "Account creation date is required.";
            return false;
        }
    }
    if (currentStep.value === 13) {
        if (!form.value.recovery_email) {
            error.value = "Recovery email address is required.";
            return false;
        }
        if (
            form.value.recovery_phone &&
            !isE164PhoneNumber(form.value.recovery_phone)
        ) {
            error.value =
                "Recovery phone must be in E.164 format (e.g. +14165550100).";
            return false;
        }
    }
    if (currentStep.value === 14) {
        // allow save to mark step as done
    }
    if (currentStep.value === 15) {
        if (!form.value.captcha_ack) {
            error.value = "Please complete captcha verification.";
            return false;
        }
    }
    if (currentStep.value === 16) {
        if (!form.value.safety_ack) {
            error.value = "You must accept the Account Safety requirements.";
            return false;
        }
    }
    if (currentStep.value === 17) {
        if (!termsAcceptedEnabled.value) {
            error.value =
                "Please scroll through the Terms and Conditions twice.";
            return false;
        }
        if (!form.value.terms_ack) {
            error.value = "You must accept the Users Terms and Conditions.";
            return false;
        }
        if (!form.value.privacy_ack) {
            error.value = "You must accept the Privacy Policy.";
            return false;
        }
    }
    if (currentStep.value === 18) {
        return true;
    }
    loading.value = true;
    try {
        const payload = { ...form.value };
        syncCountryNamesFromIds(payload);
        applyAdminRolePayload(payload);
        payload.company_country_id = payload.company_country_id || null;
        payload.facility_country_id = payload.facility_country_id || null;
        if (Array.isArray(payload.facility_usage)) {
            payload.facility_usage = payload.facility_usage.map((row) => ({
                ...row,
                country_id: row.country_id || null,
            }));
        }
        delete payload.company_logo_preview;
        if (currentStep.value === 15) {
            payload.captcha_token = captchaToken.value || null;
        } else {
            delete payload.captcha_token;
        }
        if (currentStep.value !== 8) {
            delete payload.security_password;
            delete payload.security_pin;
            delete payload.security_password_confirm;
        }
        await client.post("/account-setup", {
            step: currentStep.value,
            data: payload,
        });
        if (currentStep.value === 15) {
            captchaToken.value = "";
        }
        success.value = true;
        return true;
    } catch (e) {
        error.value =
            e?.response?.data?.message || "Failed to save. Please try again.";
        return false;
    } finally {
        loading.value = false;
    }
};

const next = async () => {
    const ok = await saveStep();
    if (!ok) return;
    if (isFinalStep.value) {
        loading.value = true;
        error.value = "";
        try {
            await client.post("/account-setup/complete");
            sessionStorage.setItem(
                "account_setup_summary",
                JSON.stringify({
                    company: form.value.company_name,
                    facility: form.value.facility_name,
                    username: form.value.security_username,
                    email:
                        form.value.contact_business_email ||
                        form.value.recovery_email,
                    plan: form.value.subscription_plan,
                    created: form.value.admin_created_date,
                }),
            );
            try {
                await logout();
            } catch {
                // ignore logout errors, local token is still cleared
            }
            clearToken();
            router.push("/account-setup/success");
            return;
        } catch (e) {
            error.value =
                e?.response?.data?.message ||
                "Failed to complete setup. Please try again.";
            return;
        } finally {
            loading.value = false;
        }
    }
    currentStep.value += 1;
};

const prev = () => {
    if (currentStep.value > 1) currentStep.value -= 1;
};

const addFacilityRow = () => {
    form.value.facility_usage.push({
        name: "",
        part: "",
        city: "",
        country_id: "",
        country: "",
        assigned: 1,
        status: "active",
    });
};

const removeFacilityRow = (index) => {
    form.value.facility_usage.splice(index, 1);
};

const toggleStatus = (index) => {
    const row = form.value.facility_usage[index];
    row.status = row.status === "active" ? "inactive" : "active";
};

const selectPlan = (plan) => {
    form.value.subscription_plan = plan;
};

const planOptions = [
    { key: "basic", label: "Basic Plan", price: 15 },
    { key: "standard", label: "Standard Plan", price: 20, popular: true },
    { key: "enterprise", label: "Enterprise Plan", price: 25 },
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

const billingMethods = [
    { value: "card", label: "Credit Card", icon: "CC" },
    { value: "bank", label: "Bank Transfer", icon: "Bank" },
    { value: "paypal", label: "PayPal", icon: "PayPal" },
    { value: "other", label: "Other", icon: "Other" },
];

const onTermsScroll = () => {
    const el = termsScrollRef.value;
    if (!el) return;
    const nearBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 4;
    if (!nearBottom) return;
    if (termsScrollCount.value < 2) {
        termsScrollCount.value += 1;
    }
};

onMounted(async () => {
    await loadCountries();
    await loadSetup();
    applyAuthDefaults();
    if (currentStep.value === 15 && !form.value.captcha_ack) {
        await renderCaptchaWidget();
    }
});

watch(
    () => authState.user?.company_name,
    () => applyAuthDefaults(),
);

watch(
    () => form.value.admin_role,
    (role) => {
        if (role !== "Other") {
            form.value.admin_role_other = "";
        }
    },
);

watch(currentStep, async (step) => {
    if (step === 14) {
        await refreshStep14VerificationStatus();
    } else {
        step14Message.value = "";
        step14Action.value = "";
    }

    if (step === 15) {
        captchaError.value = "";
        if (!form.value.captcha_ack) {
            await renderCaptchaWidget();
        }
        return;
    }

    if (step !== 17) {
        termsScrollCount.value = 0;
        return;
    }

    termsScrollCount.value = 0;
    await nextTick();
    const el = termsScrollRef.value;
    if (!el) return;
    if (el.scrollHeight <= el.clientHeight + 2) {
        termsScrollCount.value = 2;
    }
});

watch(
    () => form.value.captcha_ack,
    async (accepted) => {
        if (currentStep.value !== 15) return;
        if (accepted) return;
        await renderCaptchaWidget();
    },
);
</script>
