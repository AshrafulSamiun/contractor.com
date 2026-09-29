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
                            <div class="pm-setup-brand-copy">
                                <p class="pm-setup-title">Contractor<span>.com</span></p>
                                <p class="pm-setup-sidebar-heading">Create Account</p>
                            </div>
                        </div>
                        <div class="pm-setup-progress">
                            <div
                                class="pm-progress-circle"
                                :style="{ '--pm-setup-progress': `${progressPercent}%` }"
                            >
                                <span>{{ progressPercent }}%</span>
                            </div>
                            <div class="pm-setup-progress-copy">
                                <strong>{{ completedSidebarSteps }}</strong>
                                <span>of {{ sidebarSteps.length }} steps completed</span>
                            </div>
                        </div>
                        <div class="pm-setup-steps">
                            <button
                                v-for="(step, index) in sidebarSteps"
                                :key="step.key"
                                type="button"
                                :class="[
                                    'pm-setup-step',
                                    {
                                        active: currentSidebarStep === index + 1,
                                        done: currentSidebarStep > index + 1,
                                        locked: currentSidebarStep < index + 1,
                                    },
                                ]"
                                @click="goToStep(step.contentStep)"
                            >
                                <span
                                    v-if="currentSidebarStep === index + 1"
                                    class="pm-step-active-dot"
                                ></span>
                                <span class="pm-step-index" aria-hidden="true">{{
                                    index + 1
                                }}</span>
                                <span
                                    class="pm-step-icon"
                                    aria-hidden="true"
                                    v-html="step.iconHtml"
                                ></span>
                                <div class="pm-step-copy">
                                    <p class="pm-step-title">
                                        {{ step.title }}
                                    </p>
                                </div>
                                <span
                                    class="pm-step-status-icon"
                                    :class="currentSidebarStep >= index + 1 ? 'done' : 'locked'"
                                    aria-hidden="true"
                                    v-html="currentSidebarStep >= index + 1 ? '&#10003;' : '&#128274;'"
                                ></span>
                            </button>
                        </div>
                        <div class="pm-setup-footer">
                            Step {{ currentSidebarStep }} of {{ sidebarSteps.length }}
                        </div>
                    </aside>

                    <main class="pm-setup-content">
                        <div class="pm-setup-card">
                            <div
                                v-if="currentStep !== 18 && currentStep !== 2"
                                class="pm-setup-card-header"
                            >
                                <template v-if="currentStep === 1">
                                    <div class="pm-step1-page-head">
                                        <h5>Create Account</h5>
                                        <div class="pm-step1-mini-progress">
                                            <div class="pm-step1-mini-ring">
                                                <strong>0%</strong>
                                                <span>Complete</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <h5>{{ steps[currentStep - 1].title }}</h5>
                                    <p class="pm-muted">
                                        {{ steps[currentStep - 1].subtitle }}
                                    </p>
                                </template>
                            </div>

                            <div
                                v-if="currentStep === 1"
                                class="pm-setup-form pm-step1-premium"
                            >
                                <div class="pm-step1-welcome-card">
                                    <div class="pm-step1-hero-row">
                                    <div class="pm-step1-celebration" aria-hidden="true">
                                        <span class="pm-step1-burst pm-step1-burst-left">✦</span>
                                        <span class="pm-step1-burst pm-step1-burst-center">✧</span>
                                        <span class="pm-step1-burst pm-step1-burst-right">✦</span>
                                        <div class="pm-step1-avatar-badge">👤</div>
                                    </div>
                                    <h2 class="pm-step1-welcome-title">Welcome!</h2>
                                    </div>
                                    <p class="pm-step1-welcome-lead">
                                        We&rsquo;re excited to have you on board!
                                    </p>
                                    <span class="pm-step1-welcome-divider"></span>
                                    <p class="pm-step1-welcome-copy">
                                        You&rsquo;re about to take an important step toward growing your business
                                        with Contractor.com, your trusted partner for success.
                                        We&rsquo;ll guide you through a simple, secure, and easy account setup process.
                                    </p>
                                    <p class="pm-step1-welcome-cta">
                                        Let&rsquo;s get started! <span aria-hidden="true">&#10084;</span>
                                    </p>
                                </div>

                                <div class="pm-step1-instructions-card">
                                    <div class="pm-step1-instructions-icon" aria-hidden="true">
                                        📝
                                    </div>
                                    <div class="pm-step1-instructions-copy">
                                        <h3>Instructions</h3>
                                        <ul class="pm-step1-instructions-list">
                                            <li>Please complete each step in order.</li>
                                            <li>You can save your progress at any time and return later.</li>
                                            <li>All information you provide is secure and protected.</li>
                                            <li>Fields marked with <em>*</em> are required.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="pm-step1-support-note">
                                    <span class="pm-step1-support-emoji" aria-hidden="true">🙂</span>
                                    <div>
                                        <p>We&rsquo;re here to support you every step of the way.</p>
                                        <strong>Thank you for choosing Contractor.com! <span aria-hidden="true">&#10084;</span></strong>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 2"
                                class="pm-setup-form pm-step2-premium"
                            >
                                <div class="pm-step2-page-head">
                                    <div class="pm-step2-kicker">
                                        Create account
                                    </div>
                                    <h2 class="pm-step2-page-title">
                                        My profile
                                    </h2>
                                    <span class="pm-step2-page-divider"></span>
                                </div>

                                <div class="pm-step2-stage">
                                    <div class="pm-step2-shell">
                                        <div class="pm-step2-photo-panel">
                                            <h3>Profile photo</h3>
                                            <div class="pm-step2-photo-frame">
                                                <div
                                                    v-if="
                                                        form.company_logo_url ||
                                                        form.company_logo_preview
                                                    "
                                                    class="pm-step2-photo-preview"
                                                >
                                                    <img
                                                        :src="
                                                            form.company_logo_url ||
                                                            form.company_logo_preview
                                                        "
                                                        alt="Company profile"
                                                    />
                                                </div>
                                                <div
                                                    v-else
                                                    class="pm-step2-photo-placeholder"
                                                    aria-hidden="true"
                                                >
                                                    &#128100;
                                                </div>
                                                <label
                                                    class="pm-step2-camera-badge"
                                                    for="companyLogoStep2"
                                                    aria-label="Upload profile photo"
                                                >
                                                    &#128247;
                                                </label>
                                            </div>
                                            <input
                                                id="companyLogoStep2"
                                                class="pm-file-input"
                                                type="file"
                                                accept="image/png,image/jpeg,image/gif"
                                                @change="onLogoSelect"
                                            />
                                            <p class="pm-step2-photo-hint">
                                                JPG, PNG or GIF.<br />
                                                Max size 5MB.
                                            </p>
                                            <label
                                                class="pm-step2-upload-btn"
                                                for="companyLogoStep2"
                                            >
                                                Upload photo
                                            </label>
                                        </div>

                                        <div class="pm-step2-fields">
                                            <div class="pm-step2-field pm-step2-field-span-2">
                                                <label class="pm-field-label"
                                                    >Full name <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.full_name"
                                                    class="form-control"
                                                    placeholder="John Michael Anderson"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Job title / Role / position
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.admin_role"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select role / position
                                                    </option>
                                                    <option
                                                        v-if="
                                                            form.admin_role &&
                                                            !adminRoleOptions.includes(
                                                                form.admin_role,
                                                            )
                                                        "
                                                        :value="form.admin_role"
                                                    >
                                                        {{ form.admin_role }}
                                                    </option>
                                                    <option
                                                        v-for="option in adminRoleOptions"
                                                        :key="`admin-role-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Phone number
                                                    <em>*</em></label
                                                >
                                                <div class="pm-step2-phone-wrap">
                                                    <span class="pm-step2-phone-badge"
                                                        >+1</span
                                                    >
                                                    <input
                                                        v-model="
                                                            form.contact_primary_phone
                                                        "
                                                        class="form-control"
                                                        placeholder="+1 (555) 123-4567"
                                                    />
                                                </div>
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Email address
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="
                                                        form.contact_business_email
                                                    "
                                                    class="form-control"
                                                    type="email"
                                                    placeholder="john.anderson@contractor.com"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Date of birth
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="
                                                        form.profile_date_of_birth
                                                    "
                                                    class="form-control"
                                                    type="date"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Nationality
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="
                                                        form.profile_nationality
                                                    "
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select nationality
                                                    </option>
                                                    <option
                                                        v-if="
                                                            form.profile_nationality &&
                                                            !nationalityOptions.includes(
                                                                form.profile_nationality,
                                                            )
                                                        "
                                                        :value="form.profile_nationality"
                                                    >
                                                        {{ form.profile_nationality }}
                                                    </option>
                                                    <option
                                                        v-for="option in nationalityOptions"
                                                        :key="`nationality-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Address
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_address"
                                                    class="form-control"
                                                    placeholder="123 Main Street, Suite 500"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Address line 2
                                                    <span>(optional)</span></label
                                                >
                                                <input
                                                    v-model="
                                                        form.company_address_line_2
                                                    "
                                                    class="form-control"
                                                    placeholder="Enter address line 2"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >City <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_city"
                                                    class="form-control"
                                                    placeholder="Vancouver"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >State / Province
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_state"
                                                    class="form-control"
                                                    placeholder="British Columbia"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Postal / Zip code
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_zip"
                                                    class="form-control"
                                                    placeholder="V6B 2N9"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Country
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.company_country_id"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select country
                                                    </option>
                                                    <option
                                                        v-for="country in countries"
                                                        :key="`step2-country-${country.id}`"
                                                        :value="country.id"
                                                    >
                                                        {{ country.country_name }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Preferred language
                                                    <em>*</em></label
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
                                                                form.pref_language,
                                                            )
                                                        "
                                                        :value="form.pref_language"
                                                    >
                                                        {{ form.pref_language }}
                                                    </option>
                                                    <option
                                                        v-for="lang in languageOptions"
                                                        :key="`step2-language-${lang.code}`"
                                                        :value="lang.code"
                                                    >
                                                        {{ lang.label }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >LinkedIn profile
                                                    <span>(optional)</span></label
                                                >
                                                <input
                                                    v-model="
                                                        form.linkedin_profile
                                                    "
                                                    class="form-control"
                                                    placeholder="https://www.linkedin.com/in/johnanderson"
                                                />
                                            </div>

                                            <div class="pm-step2-field">
                                                <label class="pm-field-label"
                                                    >Company website
                                                    <span>(optional)</span></label
                                                >
                                                <input
                                                    v-model="form.contact_website"
                                                    class="form-control"
                                                    placeholder="https://www.examplecompany.com"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pm-step2-confirmation-card">
                                        <label class="pm-step2-confirmation-check">
                                            <input
                                                v-model="
                                                    form.step_2_confirmation_ack
                                                "
                                                type="checkbox"
                                            />
                                            <span
                                                class="pm-step2-confirmation-box"
                                                aria-hidden="true"
                                            ></span>
                                            <span
                                                class="pm-step2-confirmation-text"
                                            >
                                                <strong>Confirmation</strong>
                                                <span>
                                                    I confirm that I am an
                                                    authorized person legally
                                                    from the company owner /
                                                    director and I have the
                                                    authority to act on behalf
                                                    of the company.
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="pm-step2-meta">
                                    Complete your personal and contact details
                                    carefully. These details are used for
                                    account identity, communication, and setup
                                    verification.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 3"
                                class="pm-setup-form pm-step3-premium"
                            >
                                <div class="pm-step3-shell">
                                    <aside class="pm-step3-aside">
                                        <h3>Company Profile</h3>
                                        <p>
                                            Please provide your company
                                            information.
                                        </p>
                                        <p>
                                            This information will be used to
                                            verify your business and ensure
                                            compliance.
                                        </p>
                                        <div class="pm-step3-important">
                                            <strong>Important</strong>
                                            <p>
                                                All information must be
                                                accurate and up to date. You
                                                will not be able to proceed
                                                without completing all required
                                                fields.
                                            </p>
                                        </div>
                                    </aside>

                                    <section
                                        class="pm-step3-panel pm-step3-panel-hero"
                                    >
                                        <h4 class="pm-step3-section-title">
                                            1. Company Identification
                                        </h4>
                                        <div class="pm-step3-grid">
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Company ID Number</label
                                                >
                                                <input
                                                    v-model="form.company_id_number"
                                                    class="form-control"
                                                    readonly
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Legal Business Name
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_name"
                                                    class="form-control"
                                                    placeholder="Anderson Construction Ltd."
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Business Number
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.business_number"
                                                    class="form-control"
                                                    placeholder="123456789RM0001"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Business Registration
                                                    Number <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.business_registration_number"
                                                    class="form-control"
                                                    placeholder="123456789"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Incorporation Date</label
                                                >
                                                <input
                                                    v-model="form.incorporation_date"
                                                    class="form-control"
                                                    type="date"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Company Status
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.company_status"
                                                    class="form-control"
                                                >
                                                    <option
                                                        v-for="status in companyStatusOptions"
                                                        :key="`company-status-${status}`"
                                                        :value="status"
                                                    >
                                                        {{ status }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Business Type
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.business_type"
                                                    class="form-control"
                                                >
                                                    <option
                                                        v-for="option in businessTypeOptions"
                                                        :key="`business-type-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Operating Structure
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.business_structure"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select operating
                                                        structure
                                                    </option>
                                                    <option
                                                        v-for="option in businessStructureOptions"
                                                        :key="`business-structure-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >GST/HST Number</label
                                                >
                                                <input
                                                    v-model="form.tax_number"
                                                    class="form-control"
                                                    placeholder="123456789 RT 0001"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >PST/QST Number</label
                                                >
                                                <input
                                                    v-model="form.pst_qst_number"
                                                    class="form-control"
                                                    placeholder="PQ1234567"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Currency
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.currency_code"
                                                    class="form-control"
                                                >
                                                    <option
                                                        v-for="currency in currencyOptions"
                                                        :key="`currency-${currency.code}`"
                                                        :value="currency.code"
                                                    >
                                                        {{ currency.code }} -
                                                        {{ currency.label }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <div class="pm-step3-sections">
                                    <section class="pm-step3-panel">
                                        <h4 class="pm-step3-section-title">
                                            2. Business Address &
                                            Registration
                                        </h4>
                                        <div class="pm-step3-grid pm-step3-grid-compact">
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Registered Country
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model.number="form.company_country_id"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select country
                                                    </option>
                                                    <option
                                                        v-for="c in countries"
                                                        :key="`company-country-${c.id}`"
                                                        :value="c.id"
                                                    >
                                                        {{ c.country_name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Registered Business
                                                    Address <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_address"
                                                    class="form-control"
                                                    placeholder="123 Business Street"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >City <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_city"
                                                    class="form-control"
                                                    placeholder="Toronto"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Province / State
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_state"
                                                    class="form-control"
                                                    placeholder="Ontario"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Postal / ZIP Code
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.company_zip"
                                                    class="form-control"
                                                    placeholder="M1B 2C3"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Business Location (if
                                                    different from above)</label
                                                >
                                                <input
                                                    v-model="form.business_location"
                                                    class="form-control"
                                                    placeholder="456 Site Road, Toronto, Ontario, M1B 3D4"
                                                />
                                            </div>
                                        </div>
                                    </section>

                                    <section class="pm-step3-panel">
                                        <h4 class="pm-step3-section-title">
                                            3. Contact Information
                                        </h4>
                                        <div class="pm-step3-grid pm-step3-grid-compact">
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Business Phone
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.contact_primary_phone"
                                                    class="form-control"
                                                    placeholder="+14165550123"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Business Email
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.contact_business_email"
                                                    class="form-control"
                                                    type="email"
                                                    placeholder="info@andersonconstruction.com"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Secondary Email</label
                                                >
                                                <input
                                                    v-model="form.contact_alt_email"
                                                    class="form-control"
                                                    type="email"
                                                    placeholder="accounts@andersonconstruction.com"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Fax Number</label
                                                >
                                                <input
                                                    v-model="form.fax_number"
                                                    class="form-control"
                                                    placeholder="+14165550199"
                                                />
                                            </div>
                                        </div>
                                    </section>

                                    <section class="pm-step3-panel">
                                        <h4 class="pm-step3-section-title">
                                            4. Website & Social Media
                                        </h4>
                                        <div class="pm-step3-grid pm-step3-grid-compact">
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Company Website
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.contact_website"
                                                    class="form-control"
                                                    placeholder="https://www.andersonconstruction.com"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >LinkedIn</label
                                                >
                                                <input
                                                    v-model="form.linkedin_profile"
                                                    class="form-control"
                                                    placeholder="https://www.linkedin.com/company/anderson-construction"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Facebook</label
                                                >
                                                <input
                                                    v-model="form.facebook_profile"
                                                    class="form-control"
                                                    placeholder="https://www.facebook.com/andersonconstruction"
                                                />
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Instagram</label
                                                >
                                                <input
                                                    v-model="form.instagram_profile"
                                                    class="form-control"
                                                    placeholder="https://www.instagram.com/andersonconstruction"
                                                />
                                            </div>
                                        </div>
                                    </section>

                                    <section class="pm-step3-panel">
                                        <h4 class="pm-step3-section-title">
                                            6. Industry & Services
                                        </h4>
                                        <div class="pm-step3-grid pm-step3-grid-compact">
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Industry Type
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.industry_type"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select industry type
                                                    </option>
                                                    <option
                                                        v-for="option in industryTypeOptions"
                                                        :key="`industry-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Primary Services
                                                    <em>*</em></label
                                                >
                                                <div class="pm-step3-service-tags">
                                                    <button
                                                        v-for="option in primaryServiceOptions"
                                                        :key="`service-${option}`"
                                                        type="button"
                                                        :class="[
                                                            'pm-step3-service-tag',
                                                            {
                                                                active: form.primary_services.includes(
                                                                    option,
                                                                ),
                                                            },
                                                        ]"
                                                        @click="togglePrimaryService(option)"
                                                    >
                                                        {{ option }}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Years in Business
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.years_in_business"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select years
                                                    </option>
                                                    <option
                                                        v-for="option in yearsInBusinessOptions"
                                                        :key="`years-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Number of Employees
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.number_of_employees"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select employee range
                                                    </option>
                                                    <option
                                                        v-for="option in employeeCountOptions"
                                                        :key="`employees-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="pm-step3-panel">
                                        <h4 class="pm-step3-section-title">
                                            7. Additional Information
                                        </h4>
                                        <div class="pm-step3-grid pm-step3-grid-compact">
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Annual Revenue Range
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.annual_revenue_range"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select annual revenue
                                                    </option>
                                                    <option
                                                        v-for="option in revenueRangeOptions"
                                                        :key="`revenue-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >Business Description
                                                    <em>*</em></label
                                                >
                                                <textarea
                                                    v-model="form.company_description"
                                                    class="form-control"
                                                    rows="4"
                                                    placeholder="We provide general contracting, renovation, and project management services."
                                                ></textarea>
                                            </div>
                                            <div class="pm-step3-field pm-step3-field-span-2">
                                                <label class="pm-field-label"
                                                    >How did you hear about us?
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="form.hear_about_source"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select one
                                                    </option>
                                                    <option
                                                        v-for="option in hearAboutOptions"
                                                        :key="`hear-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="pm-step3-panel">
                                        <h4 class="pm-step3-section-title">
                                            8. Company Representatives
                                        </h4>
                                        <div class="pm-step3-grid pm-step3-grid-compact">
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Owner Name
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.owner_name"
                                                    class="form-control"
                                                    placeholder="John Michael Anderson"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Director Name</label
                                                >
                                                <input
                                                    v-model="form.director_name"
                                                    class="form-control"
                                                    placeholder="John Michael Anderson"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Authorized Contact Name
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.authorized_contact_name"
                                                    class="form-control"
                                                    placeholder="Sarah Anderson"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Designation / Title
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.designation_title"
                                                    class="form-control"
                                                    placeholder="President"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Contact Phone
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.authorized_contact_phone"
                                                    class="form-control"
                                                    placeholder="+14165550145"
                                                />
                                            </div>
                                            <div class="pm-step3-field">
                                                <label class="pm-field-label"
                                                    >Contact Email
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="form.authorized_contact_email"
                                                    class="form-control"
                                                    type="email"
                                                    placeholder="sarah.anderson@andersonconstruction.com"
                                                />
                                            </div>
                                        </div>
                                    </section>
                                </div>
                                <div class="pm-step3-meta">
                                    These details help Contractor.com verify
                                    your organization, tailor onboarding, and
                                    prefill later setup steps.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 4"
                                class="pm-setup-form pm-step4-premium"
                            >
                                <div class="pm-step4-head">
                                    <div>
                                        <div class="pm-step4-kicker">
                                            Step 04 - Professional Licenses
                                        </div>
                                        <h6 class="pm-step4-title">
                                            Capture license and authority
                                            details
                                        </h6>
                                        <p class="pm-step4-subtitle">
                                            Add every active professional
                                            license your business relies on so
                                            verification, renewals, and
                                            compliance reviews stay organized.
                                        </p>
                                    </div>
                                    <div class="pm-step4-chip">
                                        Verified Credentials
                                    </div>
                                </div>

                                <div class="pm-step4-list">
                                    <section
                                        v-for="(license, idx) in form.professional_licenses"
                                        :key="`professional-license-${idx}`"
                                        class="pm-step4-license-card"
                                    >
                                        <div class="pm-step4-card-head">
                                            <div>
                                                <h4>
                                                    License Information
                                                </h4>
                                                <span>
                                                    License {{ idx + 1 }}
                                                </span>
                                            </div>
                                            <button
                                                v-if="form.professional_licenses.length > 1"
                                                class="btn btn-link text-danger p-0"
                                                type="button"
                                                @click="removeProfessionalLicense(idx)"
                                            >
                                                Remove
                                            </button>
                                        </div>

                                        <div class="pm-step4-grid">
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >License Name
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="license.license_name"
                                                    class="form-control"
                                                    placeholder="Professional Contractor License"
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >License Number
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="license.license_number"
                                                    class="form-control"
                                                    placeholder="LIC-2026-0001"
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >Legal Business Name on
                                                    License <em>*</em></label
                                                >
                                                <input
                                                    v-model="license.legal_business_name"
                                                    class="form-control"
                                                    placeholder="Anderson Construction Ltd."
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >License Type
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="license.license_type"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select license type
                                                    </option>
                                                    <option
                                                        v-for="option in professionalLicenseTypeOptions"
                                                        :key="`license-type-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >Issuing Country
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model.number="license.issuing_country_id"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select country
                                                    </option>
                                                    <option
                                                        v-for="c in countries"
                                                        :key="`license-country-${idx}-${c.id}`"
                                                        :value="c.id"
                                                    >
                                                        {{ c.country_name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >Issued By (Authority /
                                                    Board) <em>*</em></label
                                                >
                                                <input
                                                    v-model="license.issuing_authority"
                                                    class="form-control"
                                                    placeholder="State Contractors Board"
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >Issue Date
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="license.issue_date"
                                                    class="form-control"
                                                    type="date"
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >Expiry Date
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="license.expiry_date"
                                                    class="form-control"
                                                    type="date"
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >License Status
                                                    <em>*</em></label
                                                >
                                                <select
                                                    v-model="license.license_status"
                                                    class="form-control"
                                                >
                                                    <option value="">
                                                        Select status
                                                    </option>
                                                    <option
                                                        v-for="option in professionalLicenseStatusOptions"
                                                        :key="`license-status-${option}`"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >License Website
                                                    <em>*</em></label
                                                >
                                                <input
                                                    v-model="license.license_website"
                                                    class="form-control"
                                                    placeholder="https://licensing-authority.example.com"
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >License Verification URL</label
                                                >
                                                <input
                                                    v-model="license.verification_url"
                                                    class="form-control"
                                                    placeholder="https://verify.example.com/license/123"
                                                />
                                            </div>
                                            <div class="pm-step4-field">
                                                <label class="pm-field-label"
                                                    >Description / Scope</label
                                                >
                                                <textarea
                                                    v-model="license.description_scope"
                                                    class="form-control"
                                                    rows="3"
                                                    placeholder="Optional notes about this license and its permitted scope of work."
                                                ></textarea>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <div class="pm-step4-toolbar">
                                    <button
                                        class="btn btn-primary pm-step4-add"
                                        type="button"
                                        @click="addProfessionalLicense"
                                    >
                                        + Add Another License
                                    </button>
                                </div>

                                <div class="pm-setup-note pm-step4-note">
                                    <strong>License Notes</strong>
                                    <ul>
                                        <li>
                                            Add each active or trackable
                                            professional license separately.
                                        </li>
                                        <li>
                                            Use the authority website and
                                            verification URL whenever they are
                                            available.
                                        </li>
                                        <li>
                                            Keep expiry dates current to avoid
                                            verification issues during review.
                                        </li>
                                    </ul>
                                </div>
                                <div class="pm-step4-meta">
                                    Tip: include the exact legal business name
                                    shown on the license to reduce approval
                                    delays.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 5"
                                class="pm-setup-form pm-step5-premium"
                            >
                                <div class="pm-step5-hero">
                                    <div class="pm-step5-hero-badge" aria-hidden="true">
                                        &#128737;
                                    </div>
                                    <div>
                                        <div class="pm-step5-kicker">
                                            Step 05 - Liability Insurance
                                        </div>
                                        <h3 class="pm-step5-heading">
                                            Liability Insurance
                                        </h3>
                                    </div>
                                </div>

                                <section class="pm-step5-card">
                                    <h4 class="pm-step5-section-title">
                                        Insurance Information
                                    </h4>

                                    <div class="pm-step5-grid">
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Insurance Company Name
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_company_name"
                                                class="form-control"
                                                placeholder="Northbridge Insurance"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Policy Number
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_policy_number"
                                                class="form-control"
                                                placeholder="POL-2026-000145"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Legal Business Name on Policy
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_legal_business_name"
                                                class="form-control"
                                                placeholder="Anderson Construction Ltd."
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Coverage Type
                                                <em>*</em></label
                                            >
                                            <select
                                                v-model="form.insurance_coverage_type"
                                                class="form-control"
                                            >
                                                <option value="">
                                                    Select coverage type
                                                </option>
                                                <option
                                                    v-for="option in insuranceCoverageTypeOptions"
                                                    :key="`insurance-type-${option}`"
                                                    :value="option"
                                                >
                                                    {{ option }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Policy Start Date
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_policy_start_date"
                                                class="form-control"
                                                type="date"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Policy End Date
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_policy_end_date"
                                                class="form-control"
                                                type="date"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Coverage Amount
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_coverage_amount"
                                                class="form-control"
                                                placeholder="2000000"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Deductible Amount</label
                                            >
                                            <input
                                                v-model="form.insurance_deductible_amount"
                                                class="form-control"
                                                placeholder="5000"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Issuing Country
                                                <em>*</em></label
                                            >
                                            <select
                                                v-model.number="form.insurance_issuing_country_id"
                                                class="form-control"
                                            >
                                                <option value="">
                                                    Select country
                                                </option>
                                                <option
                                                    v-for="c in countries"
                                                    :key="`insurance-country-${c.id}`"
                                                    :value="c.id"
                                                >
                                                    {{ c.country_name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Issued By (Authority / Insurer)
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_issuing_authority"
                                                class="form-control"
                                                placeholder="Northbridge Insurance"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Insurance Certificate No.
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_certificate_number"
                                                class="form-control"
                                                placeholder="CERT-2026-7781"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Issue Date
                                                <em>*</em></label
                                            >
                                            <input
                                                v-model="form.insurance_issue_date"
                                                class="form-control"
                                                type="date"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Description of Coverage</label
                                            >
                                            <input
                                                v-model="form.insurance_description"
                                                class="form-control"
                                                placeholder="General liability coverage for construction operations."
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Additional Insured
                                                <span class="pm-muted"
                                                    >(If applicable)</span
                                                ></label
                                            >
                                            <input
                                                v-model="form.insurance_additional_insured"
                                                class="form-control"
                                                placeholder="City of Toronto"
                                            />
                                        </div>
                                        <div class="pm-step5-field">
                                            <label class="pm-field-label"
                                                >Certificate Upload (PDF / Image)
                                                <em>*</em></label
                                            >
                                            <input
                                                id="insuranceCertificateFile"
                                                class="pm-file-input"
                                                type="file"
                                                accept=".pdf,image/png,image/jpeg,image/webp"
                                                @change="onInsuranceCertificateSelect"
                                            />
                                            <label
                                                class="pm-step5-upload-btn"
                                                for="insuranceCertificateFile"
                                            >
                                                Upload File
                                            </label>
                                            <div
                                                v-if="form.insurance_certificate_name"
                                                class="pm-step5-upload-name"
                                            >
                                                {{ form.insurance_certificate_name }}
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <div class="pm-step5-meta">
                                    Upload the active certificate that matches
                                    the policy details above so compliance
                                    reviews can be completed without follow-up.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 6"
                                class="pm-setup-form pm-step6-premium"
                            >
                                <div class="pm-step6-hero">
                                    <div class="pm-step6-hero-main">
                                        <div
                                            class="pm-step6-hero-badge"
                                            aria-hidden="true"
                                        >
                                            &#128230;
                                        </div>
                                        <div>
                                            <h3 class="pm-step6-heading">
                                                Service Plans
                                            </h3>
                                            <p class="pm-step6-subtitle">
                                                Choose the service plan that best
                                                fits your company needs.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="pm-step6-info-card">
                                        <div
                                            class="pm-step6-info-icon"
                                            aria-hidden="true"
                                        >
                                            i
                                        </div>
                                        <div>
                                            <strong
                                                >Why we need this
                                                information</strong
                                            >
                                            <p>
                                                This helps us assign the right
                                                features, user limits, and
                                                subscription options for your
                                                account.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <section class="pm-step6-plans-card">
                                    <div class="pm-step6-plans-head">
                                        <h4>Available Plans</h4>
                                        <p>
                                            <span>All plans are</span>
                                            <strong>billed annually</strong>
                                        </p>
                                    </div>

                                    <div class="pm-step6-plan-grid">
                                        <button
                                            v-for="plan in servicePlanOptions"
                                            :key="`service-plan-${plan.key}`"
                                            type="button"
                                            class="pm-step6-plan-card"
                                            :class="{
                                                selected:
                                                    form.subscription_plan ===
                                                    plan.key,
                                            }"
                                            @click="selectPlan(plan.key)"
                                        >
                                            <div class="pm-step6-plan-radio">
                                                <span></span>
                                            </div>
                                            <div class="pm-step6-plan-top">
                                                <div>
                                                    <div
                                                        class="pm-step6-plan-title"
                                                    >
                                                        {{ plan.label }}
                                                    </div>
                                                    <div
                                                        class="pm-step6-plan-subtitle"
                                                    >
                                                        {{ plan.subtitle }}
                                                    </div>
                                                </div>
                                                <span
                                                    v-if="plan.recommended"
                                                    class="pm-step6-plan-badge"
                                                >
                                                    Recommended
                                                </span>
                                            </div>
                                            <template v-if="plan.yearlyPrice">
                                                <div
                                                    class="pm-step6-plan-monthly"
                                                >
                                                    <span class="pm-step6-plan-old-price"
                                                        >${{
                                                            plan.monthlyPrice
                                                        }}</span
                                                    >
                                                    <span>/ month</span>
                                                </div>
                                                <div
                                                    class="pm-step6-plan-yearly"
                                                >
                                                    ${{
                                                        plan.yearlyPrice
                                                    }}
                                                    <span>/ year</span>
                                                </div>
                                                <div
                                                    class="pm-step6-plan-save"
                                                >
                                                    Save 5% (Pay Annually)
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div
                                                    class="pm-step6-plan-contact"
                                                >
                                                    Contact Sales
                                                </div>
                                            </template>
                                            <ul class="pm-step6-plan-features">
                                                <li
                                                    v-for="feature in plan.features"
                                                    :key="`${plan.key}-${feature}`"
                                                >
                                                    <span aria-hidden="true"
                                                        >&#10003;</span
                                                    >
                                                    {{ feature }}
                                                </li>
                                            </ul>
                                        </button>
                                    </div>
                                </section>

                                <div class="pm-step6-alert pm-step6-alert-warn">
                                    <strong>Upgrade / Downgrade Option</strong>
                                    <p>
                                        You can upgrade your plan at any time.
                                        Downgrade is not available during an
                                        active annual subscription.
                                    </p>
                                </div>

                                <div class="pm-step6-alert pm-step6-alert-info">
                                    <div
                                        class="pm-step6-alert-icon"
                                        aria-hidden="true"
                                    >
                                        i
                                    </div>
                                    <div>
                                        <ul class="pm-step6-alert-list">
                                            <li>
                                                The prices shown above are for
                                                30 days of service for 1 user
                                                (ADMIN with full access).
                                            </li>
                                            <li>
                                                We charge your account 3 days
                                                before each 30-day period to
                                                ensure uninterrupted service.
                                            </li>
                                            <li>
                                                Your billing is based on 30-day
                                                cycles (not calendar months).
                                            </li>
                                            <li>
                                                The selected plan above is for 1
                                                user only (ADMIN with full
                                                access).
                                            </li>
                                            <li>
                                                You can add more users in the
                                                next step (User Licenses).
                                            </li>
                                            <li>
                                                All data and features are locked
                                                to the device you are currently
                                                using.
                                            </li>
                                        </ul>
                                        <div class="pm-step6-lock-note">
                                            This device is now locked and
                                            secured for ADMIN use only.
                                        </div>
                                    </div>
                                </div>

                                <div class="pm-step6-meta">
                                    The selected plan is saved to your account
                                    setup and billed annually.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 7"
                                class="pm-setup-form pm-step7-premium"
                            >
                                <div class="pm-step7-hero">
                                    <div class="pm-step7-hero-main">
                                        <div class="pm-step7-hero-badge" aria-hidden="true">
                                            &#128101;
                                        </div>
                                        <div>
                                            <h3 class="pm-step7-heading">
                                                User Licenses
                                            </h3>
                                            <p class="pm-step7-subtitle">
                                                Add the number of user licenses
                                                you need for your team.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="pm-step7-info-card">
                                        <div class="pm-step7-info-icon" aria-hidden="true">
                                            i
                                        </div>
                                        <div>
                                            <strong>Important Information</strong>
                                            <p>
                                                Each license is for one user only.
                                                The first user (ADMIN with full
                                                access) is already included in your
                                                selected plan.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <section class="pm-step7-summary-card">
                                    <div class="pm-step7-summary-grid">
                                        <div class="pm-step7-summary-block">
                                            <label
                                                class="pm-step7-summary-label"
                                                for="additionalUserLicenses"
                                            >
                                                How many additional user licenses do you need?
                                            </label>
                                            <div class="pm-step7-count-row">
                                                <input
                                                    id="additionalUserLicenses"
                                                    v-model.number="form.additional_user_licenses"
                                                    class="form-control pm-step7-count-input"
                                                    type="number"
                                                    min="1"
                                                    step="1"
                                                />
                                                <span class="pm-step7-count-copy">
                                                    User Licenses
                                                </span>
                                            </div>
                                        </div>
                                        <div class="pm-step7-summary-block">
                                            <div class="pm-step7-summary-label">
                                                Plan Selected
                                            </div>
                                            <div class="pm-step7-summary-value">
                                                {{ selectedServicePlan.label }}
                                            </div>
                                        </div>
                                        <div class="pm-step7-summary-block">
                                            <div class="pm-step7-summary-label">
                                                Price per License
                                            </div>
                                            <div class="pm-step7-summary-value">
                                                ${{ userLicensePrice.toFixed(2) }} /
                                                30 Days
                                            </div>
                                        </div>
                                        <div class="pm-step7-summary-block">
                                            <div class="pm-step7-summary-label">
                                                Number of Licenses
                                            </div>
                                            <div class="pm-step7-summary-value">
                                                {{ additionalUserLicenseCount }}
                                            </div>
                                        </div>
                                        <div class="pm-step7-summary-block due">
                                            <div class="pm-step7-summary-label">
                                                Total Due Today
                                            </div>
                                            <div class="pm-step7-summary-total">
                                                ${{ userLicensesTotalDue.toFixed(2) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pm-step7-summary-note">
                                        All user licenses are valid for 30 days from the payment date.
                                    </div>
                                </section>

                                <section class="pm-step7-table-card">
                                    <h4 class="pm-step7-table-title">
                                        List of User Licenses
                                    </h4>
                                    <div class="pm-step7-license-table">
                                        <div class="pm-step7-license-row header">
                                            <div>#</div>
                                            <div>User Full Name <em>*</em></div>
                                            <div>Phone Number <em>*</em></div>
                                            <div>
                                                User License Number
                                                <span class="pm-step7-inline-note"
                                                    >(Auto Generated)</span
                                                >
                                            </div>
                                            <div>
                                                Expiry Date
                                                <span class="pm-step7-inline-note"
                                                    >(Announced 30 days after payment)</span
                                                >
                                            </div>
                                        </div>
                                        <div
                                            v-for="(license, idx) in form.user_licenses"
                                            :key="`user-license-${idx}`"
                                            class="pm-step7-license-row"
                                        >
                                            <div>{{ idx + 1 }}</div>
                                            <div>
                                                <input
                                                    v-model="license.full_name"
                                                    class="form-control"
                                                    :placeholder="`User ${idx + 1} full name`"
                                                />
                                            </div>
                                            <div>
                                                <input
                                                    v-model="license.phone_number"
                                                    class="form-control"
                                                    placeholder="(604) 555-1234"
                                                />
                                            </div>
                                            <div>
                                                <input
                                                    v-model="license.license_number"
                                                    class="form-control"
                                                    readonly
                                                />
                                            </div>
                                            <div class="pm-step7-expiry-note">
                                                {{ license.expiry_note }}
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <div class="pm-step7-warning-card">
                                    <strong>
                                        WARNING: USER LICENSE SHARING IS ABSOLUTELY NOT ALLOWED.
                                    </strong>
                                    <ul class="pm-step7-warning-list">
                                        <li>
                                            Each user license you purchase is
                                            valid for 30 days only.
                                        </li>
                                        <li>
                                            User licenses are non-transferable
                                            and automatically attached to a
                                            specific device.
                                        </li>
                                        <li>
                                            No user is allowed to use another
                                            person's license or account.
                                        </li>
                                        <li>
                                            We strongly enforce this policy
                                            because user license sharing creates
                                            high liability risks and significant
                                            costs for our business.
                                        </li>
                                        <li>
                                            Please cooperate with Contractor.com
                                            to help us prevent misuse and avoid
                                            extra charges.
                                        </li>
                                        <li>
                                            Additional costs or penalties due to
                                            policy violations may be charged to
                                            your account with your consent.
                                        </li>
                                    </ul>
                                    <div class="pm-step7-warning-footer">
                                        Thank you for your understanding and cooperation.
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 8"
                                class="pm-setup-form pm-step8-premium"
                            >
                                <div class="pm-step8-head">
                                    <div class="pm-step8-head-main">
                                        <div class="pm-step8-hero-icon" aria-hidden="true">
                                            &#128197;
                                        </div>
                                        <div>
                                            <div class="pm-step8-kicker">
                                                Create Account - 8
                                            </div>
                                            <h6 class="pm-step8-title">
                                                Payment Schedule
                                            </h6>
                                            <p class="pm-step8-subtitle">
                                                Review your 12-month payment schedule
                                                based on 30-day service periods.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="pm-step8-progress-card">
                                        <div
                                            class="pm-step8-progress-ring"
                                            :style="{
                                                '--pm-step8-progress':
                                                    `${paymentScheduleCompletionPercent}%`,
                                            }"
                                        >
                                            <strong
                                                >{{
                                                    paymentScheduleCompletionPercent
                                                }}%</strong
                                            >
                                            <span>Complete</span>
                                        </div>
                                    </div>
                                </div>

                                <section class="pm-step8-schedule-card">
                                    <div class="pm-step8-schedule-head">
                                        <div class="pm-step8-kicker">
                                            12-Month Payment Schedule
                                        </div>
                                        <div class="pm-step8-schedule-meta">
                                            <span>(Based on 30-Day Service Periods)</span>
                                        </div>
                                    </div>
                                    <div class="pm-step8-start-row">
                                        <span>Start Date (First Service Day):</span>
                                        <strong>{{
                                            paymentScheduleStartDateLabel
                                        }}</strong>
                                    </div>

                                    <div class="pm-step8-table-wrap">
                                        <div class="pm-step8-table">
                                            <div class="pm-step8-table-row header">
                                                <div>Pmt. No.</div>
                                                <div>Service Period From - To</div>
                                                <div>Due Date <span>(Auto Charge Date)</span></div>
                                                <div>
                                                    Charging Date
                                                    <span>(3 Days Before)</span>
                                                </div>
                                                <div>Amount in USD</div>
                                            </div>
                                            <div
                                                v-for="item in paymentScheduleRows"
                                                :key="`payment-schedule-${item.payment_number}`"
                                                class="pm-step8-table-row"
                                            >
                                                <div class="pm-step8-payment-number">
                                                    {{ item.payment_number }}
                                                </div>
                                                <div class="pm-step8-period-cell">
                                                    <strong>{{
                                                        paymentScheduleServiceLabel
                                                    }}</strong>
                                                    <span
                                                        >{{ item.service_period_start }}
                                                        to
                                                        {{ item.service_period_end }}</span
                                                    >
                                                </div>
                                                <div class="pm-step8-date-blue">
                                                    {{ item.auto_charge_date }}
                                                </div>
                                                <div class="pm-step8-date-red">
                                                    {{ item.charging_date }}
                                                </div>
                                                <div class="pm-step8-amount">
                                                    ${{
                                                        formatCurrencyAmount(
                                                            item.amount,
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <div class="pm-step8-note-card">
                                    <div class="pm-step8-note-icon" aria-hidden="true">
                                        i
                                    </div>
                                    <div class="pm-step8-note-copy">
                                        If your service period is From
                                        <strong>{{
                                            paymentScheduleFirstPeriodSummary
                                        }}</strong
                                        >,
                                        we will charge your account 3 days in
                                        advance on
                                        <strong class="pm-step8-date-red">{{
                                            paymentScheduleFirstChargeSummary
                                        }}</strong
                                        >.
                                    </div>
                                </div>

                                <div class="pm-step8-agree">
                                    <label>
                                        <input
                                            type="checkbox"
                                            v-model="form.payment_schedule_ack"
                                        />
                                        I reviewed and agree to this payment
                                        schedule *
                                    </label>
                                </div>

                                <div class="pm-step8-meta">
                                    Your payment schedule starts on
                                    {{ paymentScheduleStartDateLabel }} and is
                                    saved with your account setup.
                                </div>
                            </div>

                            <div v-if="currentStep === 9" class="pm-setup-form pm-step9-payment">
                                <div class="pm-step9-payment-intro">
                                    <div class="pm-step9-payment-icon">▰</div>
                                    <div><h4>Payment Method</h4><p>Add your credit card information.<br />We only accept credit card payments.</p></div>
                                </div>
                                <!-- Legacy plan comparison intentionally removed from Step 9. -->
                                <div v-if="false" class="table-responsive">
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
                                <section class="pm-payment-card-section">
                                    <div class="pm-payment-card-heading"><h5>Primary Card <span>(For Auto-Charge)</span></h5><div class="pm-card-brands"><b>VISA</b><b class="mastercard">●●</b><b class="amex">AMERICAN<br />EXPRESS</b></div></div>
                                    <div class="row g-3">
                                        <div class="col-12"><label class="form-label">Cardholder Name <span class="text-danger">*</span><small>Name on card: Company</small></label><input v-model.trim="form.primary_cardholder_name" class="form-control" autocomplete="cc-name" placeholder="John Smith Technologies Inc." /></div>
                                        <div class="col-md-8"><label class="form-label">Card Number <span class="text-danger">*</span></label><input v-model.trim="form.primary_card_number" class="form-control" inputmode="numeric" autocomplete="cc-number" :placeholder="primaryCardPlaceholder" /></div>
                                        <div class="col-md-4"><label class="form-label">Card Type</label><div class="pm-card-type">{{ primaryCardType }}</div></div>
                                        <div class="col-md-6"><label class="form-label">Expiry Date <span class="text-danger">*</span></label><input v-model.trim="form.primary_card_expiry" class="form-control" autocomplete="cc-exp" placeholder="MM / YYYY" /></div>
                                        <div class="col-md-6"><label class="form-label">CVV <span class="text-danger">*</span></label><input v-model.trim="form.primary_card_cvv" class="form-control" type="password" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="•••" /></div>
                                    </div>
                                </section>
                                <section class="pm-payment-card-section">
                                    <div class="pm-payment-card-heading"><h5>Backup Card <span>(Optional)</span></h5><div class="pm-card-brands"><b>VISA</b><b class="mastercard">●●</b><b class="amex">AMERICAN<br />EXPRESS</b></div></div>
                                    <div class="row g-3">
                                        <div class="col-12"><label class="form-label">Cardholder Name <small>Name on card: Person</small></label><input v-model.trim="form.backup_cardholder_name" class="form-control" autocomplete="cc-name" placeholder="John Smith" /></div>
                                        <div class="col-md-8"><label class="form-label">Card Number</label><input v-model.trim="form.backup_card_number" class="form-control" inputmode="numeric" autocomplete="cc-number" :placeholder="backupCardPlaceholder" /></div>
                                        <div class="col-md-4"><label class="form-label">Card Type</label><div class="pm-card-type">{{ backupCardType }}</div></div>
                                        <div class="col-md-6"><label class="form-label">Expiry Date</label><input v-model.trim="form.backup_card_expiry" class="form-control" autocomplete="cc-exp" placeholder="MM / YYYY" /></div>
                                        <div class="col-md-6"><label class="form-label">CVV</label><input v-model.trim="form.backup_card_cvv" class="form-control" type="password" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="•••" /></div>
                                    </div>
                                </section>
                                <label class="pm-payment-consent"><input v-model="form.payment_method_ack" type="checkbox" /><span>I confirm that I am the authorized representative of the company or the authorized cardholder listed above. I consent to Contractor.com charging my credit card automatically 3 days before each 30-day service period as described.</span></label>
                            </div>
                            <div
                                v-if="currentStep === 10"
                                class="pm-setup-form pm-step10-contacts"
                            >
                                <div class="pm-step10-contacts-head">
                                    <div>
                                        <h4>Contacts</h4>
                                        <p>Please provide key contact information for your company.</p>
                                    </div>
                                    <button class="btn btn-primary" type="button" @click="addSetupContact">+ Add Contact</button>
                                </div>
                                <div class="table-responsive pm-setup-contacts-table-wrap">
                                    <table class="table pm-setup-contacts-table align-middle">
                                        <thead><tr><th>Department Name</th><th>Contact Person<br />(Legal Name)</th><th>Phone Number</th><th>Email Address</th><th>Position / Title</th><th class="pm-contact-actions-heading"></th></tr></thead>
                                        <tbody>
                                            <tr v-for="(contact, index) in form.setup_contacts" :key="`setup-contact-${index}`">
                                                <td><input v-model.trim="contact.department_name" class="form-control" placeholder="Accounts Payable" /></td>
                                                <td><input v-model.trim="contact.contact_person" class="form-control" placeholder="Sarah Johnson" /></td>
                                                <td><input v-model.trim="contact.phone_number" class="form-control" inputmode="tel" placeholder="(604) 555-0187" /></td>
                                                <td><input v-model.trim="contact.email" class="form-control" type="email" placeholder="name@company.com" /></td>
                                                <td><input v-model.trim="contact.position_title" class="form-control" placeholder="Accounts Payable Manager" /></td>
                                                <td class="pm-contact-actions"><button v-if="form.setup_contacts.length > 1" class="btn btn-link text-danger" type="button" @click="removeSetupContact(index)">Remove</button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="false" class="pm-step10-head">
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
                                <div v-if="false" class="pm-license-card">
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
                                <div v-if="false" class="pm-step10-meta">
                                    Only authorized users should accept this
                                    policy on behalf of the organization.
                                </div>
                            </div>

                            <div v-if="currentStep === 11" class="pm-setup-form pm-step11-security">
                                <section class="pm-security-section">
                                    <h4>1. Temporary Login Credentials <small>(Valid for 48 Hours)</small></h4>
                                    <p>These temporary credentials are provided for your initial access only and will expire in 48 hours.</p>
                                    <div class="pm-security-credentials">
                                        <label>Username (Email)<input v-model.trim="form.security_username" class="form-control" type="email" placeholder="john.doe@yourcompany.com" /><em>Expires in 48 hours</em></label>
                                        <label>Temporary Password<input v-model="form.security_password" class="form-control" type="password" autocomplete="new-password" placeholder="Enter temporary password" /><em>Expires in 48 hours</em></label>
                                        <label>Temporary Password (Confirm)<input v-model="form.security_password_confirm" class="form-control" :class="{ 'is-invalid': form.security_password_confirm && form.security_password !== form.security_password_confirm }" type="password" autocomplete="new-password" placeholder="Must match temporary password" /><em>{{ form.security_password_confirm && form.security_password !== form.security_password_confirm ? 'Passwords must match' : 'Expires in 48 hours' }}</em></label>
                                        <label>Security PIN Code<input v-model="form.security_pin" class="form-control" type="password" inputmode="text" autocomplete="off" placeholder="6G7H-9K2L" /><em>Expires in 48 hours</em></label>
                                        <label>Security PIN Code (Confirm)<input v-model="form.security_pin_confirm" class="form-control" :class="{ 'is-invalid': form.security_pin_confirm && form.security_pin !== form.security_pin_confirm }" type="password" inputmode="text" autocomplete="off" placeholder="Must match security PIN" /><em>{{ form.security_pin_confirm && form.security_pin !== form.security_pin_confirm ? 'PIN codes must match' : 'Expires in 48 hours' }}</em></label>
                                    </div>
                                    <div class="pm-security-info"><strong>i</strong><ul><li>After logging in, you have 48 hours to go to Admin Panel &gt; My Account &gt; Login Credentials to change your Password and Security PIN Code.</li><li>For your protection, you must change your Password and Security PIN Code every 30 days.</li></ul></div>
                                </section>
                                <section class="pm-security-section">
                                    <h4>2. Account Recovery</h4><p>These contact details will be used to recover your account if needed.</p>
                                    <div class="row g-3"><div class="col-md-6"><label class="pm-field-label">Primary Phone Number<input v-model.trim="form.contact_primary_phone" class="form-control" placeholder="(604) 555-0101" /></label></div><div class="col-md-6"><label class="pm-field-label">Alternate Phone Number<input v-model.trim="form.recovery_phone" class="form-control" placeholder="(604) 555-0102" /></label></div><div class="col-md-6"><label class="pm-field-label">Primary Email Address<input v-model.trim="form.contact_business_email" class="form-control" type="email" placeholder="john.doe@yourcompany.com" /></label></div><div class="col-md-6"><label class="pm-field-label">Alternate Email Address<input v-model.trim="form.recovery_email" class="form-control" type="email" placeholder="john.alt@yourcompany.com" /></label></div></div>
                                </section>
                                <section class="pm-security-section">
                                    <h4>3. Multi-Factor Authentication <small>(MFA) – Mandatory</small></h4><p>Every time you login, MFA verification is required for your account security.</p>
                                    <div class="pm-mfa-field"><span>Verified Email (Used for MFA)</span><div>{{ form.contact_business_email || form.security_username || 'Email address' }} <b>{{ form.email_verify_status === 'verified' ? 'Verified' : 'Pending' }}</b></div></div>
                                    <div class="pm-mfa-field"><span>Verified Phone Number (Used for MFA)</span><div>{{ form.contact_primary_phone || 'Phone number' }} <b>Verified</b></div></div>
                                    <div class="pm-security-warning">MFA verification using email and phone is mandatory for all logins. Additional security layers are recommended for enhanced protection.</div>
                                </section>
                                <div class="pm-security-note"><strong>i</strong><div><b>Please Note</b><br />After completing this step and saving your information, you will be logged in. To access My Account and make any changes, you will need to log in to the system again.</div></div>
                            </div>
                            <div
                                v-if="false"
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

                            <div v-if="currentStep === 12" class="pm-setup-form pm-step12-declaration">
                                <div class="pm-step12-declaration-head"><h4>Declaration &amp; Agreement</h4><p>Please read this entire Declaration carefully. By accepting below, you confirm your legal agreement.</p></div>
                                <section class="pm-declaration-copy">
                                    <h5>1. Accuracy of Information</h5><p>I declare that all information provided during the account creation process, including personal details, company information, contact information, payment details, and uploaded documents, is true, complete, accurate, and to the best of my knowledge.</p>
                                    <h5>2. Authority</h5><p>I confirm that I have the full legal authority to create this account on behalf of myself or the entity I represent and to bind such entity to these terms and conditions.</p>
                                    <h5>3. Use of Services</h5><p>I agree to use Contractor.com services only for lawful purposes and in accordance with all applicable laws, regulations, and the Terms of Service.</p>
                                    <h5>4. License Sharing – Strictly Prohibited</h5><p>I understand and agree that the license, login credentials, and account access are for my exclusive use only.</p><p class="pm-declaration-prohibited">LICENSE SHARING IS STRICTLY, EXPLICITLY, AND STRONGLY PROHIBITED.</p>
                                    <h5>5. Financial Responsibility</h5><p>I agree to pay all fees, charges, and taxes applicable to my account and services.</p>
                                    <h5>6. Limitation of Liability</h5><p>To the maximum extent permitted by law, Contractor.com shall not be liable for indirect, incidental, consequential, punitive, or special damages arising from use of the services.</p>
                                    <h5>7. Indemnification</h5><p>I agree to indemnify, defend, and hold harmless Contractor.com from claims, liabilities, damages, losses, costs, and expenses arising from my use of the services.</p>
                                    <h5>8. Governing Law and Jurisdiction</h5><p>This Declaration shall be governed by the laws applicable to the country from which the account is accessed.</p>
                                    <h5>9. Acknowledgment</h5><p>I have read this entire Declaration carefully and understand it completely. This Declaration has the same legal force and effect as if I had signed a written contract.</p>
                                    <label class="pm-declaration-ack"><input v-model="form.declaration_ack" type="checkbox" /><span><b>I Clarify, Accept &amp; Agree</b><br />By checking this box, I confirm that I have read, understood, and agree to be bound by this Declaration.</span></label>
                                    <div class="row g-3 pm-declaration-fields"><div class="col-md-4"><label>Full Legal Name <b>*</b><input v-model.trim="form.declaration_full_name" class="form-control" placeholder="Type your full legal name" /></label></div><div class="col-md-4"><label>Position / Title <b>*</b><input v-model.trim="form.declaration_position_title" class="form-control" placeholder="Type your position or title" /></label></div><div class="col-md-4"><label>Date (YYYY-MM-DD) <b>*</b><input v-model="form.declaration_signed_at" class="form-control" type="date" /></label></div><div class="col-md-4"><label>Initials <b>*</b><input v-model.trim="form.declaration_initials" class="form-control" placeholder="First and last initial" /></label></div><div class="col-md-8"><label>Electronic Signature (Type Full Name) <b>*</b><input v-model.trim="form.declaration_signature" class="form-control" placeholder="Type your full name to sign" /></label></div></div>
                                </section>
                                <div class="pm-declaration-note"><b>Important Note</b><br />After you accept and continue, you will need to log in to your account again to make changes.</div>
                            </div>
                            <div
                                v-if="false"
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

                            <div v-if="currentStep === 13" class="pm-setup-form pm-step13-preview">
                                <div class="pm-step13-preview-head"><h4>Preview &amp; Confirm</h4><p>Please review all the information below carefully. If everything is correct, confirm and proceed to payment.</p></div>
                                <div class="pm-preview-grid">
                                    <section><h5>1. Account Summary</h5><dl><dt>Company Legal Name</dt><dd>{{ form.company_name || '--' }}</dd><dt>Business Type</dt><dd>{{ form.business_type || '--' }}</dd><dt>Primary Contact</dt><dd>{{ form.full_name || '--' }}</dd><dt>Primary Contact Email</dt><dd>{{ form.contact_business_email || '--' }}</dd><dt>Primary Contact Phone</dt><dd>{{ form.contact_primary_phone || '--' }}</dd></dl></section>
                                    <section><h5>2. Service Plan &amp; Users</h5><dl><dt>Selected Plan</dt><dd>{{ selectedServicePlan?.label || form.subscription_plan || '--' }}</dd><dt>Service Cycle</dt><dd>30-Day Service Cycle</dd><dt>Total User Licenses</dt><dd>{{ form.user_licenses.length }}</dd><dt>Additional User Licenses</dt><dd>{{ Math.max(0, form.user_licenses.length - 1) }}</dd></dl><div class="pm-preview-warning">All users are authorized only for this account.<br /><b>License sharing is strictly prohibited.</b></div></section>
                                    <section><h5>3. Payment Schedule Summary</h5><dl><dt>First Service Start Date</dt><dd>{{ paymentScheduleStartDateLabel }}</dd><dt>Billing Frequency</dt><dd>Every 30 days</dd><dt>Auto-Charge Timing</dt><dd>3 days before each service cycle</dd><dt>Recurring Amount</dt><dd>USD {{ paymentScheduleAmount.toFixed(2) }}</dd></dl></section>
                                    <section><h5>4. Pricing &amp; Total</h5><dl><dt>Service Plan</dt><dd>USD {{ Number(selectedServicePlan?.monthlyPrice || 0).toFixed(2) }}</dd><dt>Additional User Licenses</dt><dd>USD {{ userLicensesTotalDue.toFixed(2) }}</dd><dt>Applicable Taxes (5%)</dt><dd>USD {{ (paymentScheduleAmount * .05).toFixed(2) }}</dd></dl><div class="pm-preview-total">TOTAL DUE TODAY <b>USD {{ (paymentScheduleAmount * 1.05).toFixed(2) }}</b></div></section>
                                    <section><h5>5. Uploaded Documents</h5><p>Professional License: {{ form.professional_licenses[0]?.license_number || 'Pending' }}</p><p>Liability Insurance: {{ form.insurance_certificate_name || 'Pending' }}</p></section>
                                    <section><h5>6. Security &amp; Access Overview</h5><dl><dt>Username (Email)</dt><dd>{{ form.security_username || '--' }}</dd><dt>Temporary Password</dt><dd>Issued after payment</dd><dt>Security PIN Code</dt><dd>Issued after payment</dd><dt>Multi-Factor Authentication</dt><dd>Mandatory</dd></dl></section>
                                </div>
                                <section class="pm-preview-contacts"><h5>8. Contacts</h5><div class="table-responsive"><table class="table"><thead><tr><th>Department</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Position / Title</th></tr></thead><tbody><tr v-for="(contact, index) in form.setup_contacts" :key="`preview-contact-${index}`"><td>{{ contact.department_name }}</td><td>{{ contact.contact_person }}</td><td>{{ contact.phone_number }}</td><td>{{ contact.email }}</td><td>{{ contact.position_title }}</td></tr></tbody></table></div></section>
                                <section class="pm-preview-final"><h5>10. Final Confirmation</h5><label><input v-model="form.preview_confirm_ack" type="checkbox" /> I have reviewed all information, documents, selected services, contacts, payment details, and legal declarations. I confirm that the information is complete, accurate, and ready for final payment and submission.</label><div class="row g-3 mt-1"><div class="col-md-4"><input v-model.trim="form.preview_full_name" class="form-control" placeholder="Full legal name" /></div><div class="col-md-4"><input v-model.trim="form.preview_position_title" class="form-control" placeholder="Position / Title" /></div><div class="col-md-4"><input v-model.trim="form.preview_initials" class="form-control" placeholder="Initials" /></div><div class="col-12"><input v-model.trim="form.preview_signature" class="form-control" placeholder="Electronic signature (type full name)" /></div></div></section>
                            </div>
                            <div
                                v-if="false"
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
                                            Step 14 - Checkout & Payment
                                        </div>
                                        <h6 class="pm-step14-title">
                                            Review your order before payment
                                        </h6>
                                        <p class="pm-step14-subtitle">
                                            Your selected plan and licences are
                                            ready for checkout. Online payment
                                            will be connected in a later update.
                                        </p>
                                    </div>
                                    <div class="pm-step14-chip">
                                        Payment Coming Soon
                                    </div>
                                </div>
                                <div
                                    class="pm-terms-progress pm-step14-progress"
                                >
                                    <div class="pm-info-title">
                                        <span
                                            class="pm-info-icon"
                                            aria-hidden="true"
                                            >$</span
                                        >
                                        <span>Order Summary</span>
                                    </div>
                                    <div class="pm-step14-summary-grid">
                                        <div>
                                            <span>Service plan</span>
                                            <strong>{{ paymentScheduleServiceLabel }}</strong>
                                        </div>
                                        <div>
                                            <span>User licences</span>
                                            <strong>{{ additionalUserLicenseCount }}</strong>
                                        </div>
                                        <div>
                                            <span>Amount due</span>
                                            <strong>${{ paymentScheduleAmount.toFixed(2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="pm-step14-checkout-grid">
                                    <section class="pm-policy-card pm-step14-order-card">
                                        <h6>1. Order Summary <small>(30-Day Service Cycle)</small></h6>
                                        <dl class="pm-step14-details">
                                            <dt>Selected Plan</dt><dd>{{ selectedServicePlan.label }}</dd>
                                            <dt>Service Cycle</dt><dd>30-Day Service Cycle</dd>
                                            <dt>Total Authorized Users</dt><dd>{{ additionalUserLicenseCount + 1 }}</dd>
                                            <dt>First Service Start Date</dt><dd>{{ paymentScheduleStartDateLabel }}</dd>
                                            <dt>Billing Frequency</dt><dd>Every 30 days</dd>
                                            <dt>Auto-Charge Timing</dt><dd>3 days before each cycle</dd>
                                        </dl>
                                    </section>
                                    <section class="pm-policy-card pm-step14-payment-card">
                                        <h6>2. Payment Summary</h6>
                                        <dl class="pm-step14-details">
                                            <dt>Service Plan</dt><dd>USD {{ servicePlanAmount.toFixed(2) }}</dd>
                                            <dt>Additional User Licenses</dt><dd>USD {{ userLicensesTotalDue.toFixed(2) }}</dd>
                                            <dt>Subtotal</dt><dd>USD {{ paymentScheduleAmount.toFixed(2) }}</dd>
                                            <dt>Applicable Taxes (5%)</dt><dd>USD {{ paymentTaxAmount.toFixed(2) }}</dd>
                                        </dl>
                                        <div class="pm-step14-total"><span>TOTAL DUE TODAY</span><strong>USD {{ paymentTotalDue.toFixed(2) }}</strong></div>
                                        <div class="pm-step14-payment-note"><strong>Today's Payment</strong><p>USD {{ paymentTotalDue.toFixed(2) }} will be charged when secure payment processing is enabled. No charge is made now.</p></div>
                                    </section>
                                </div>
                                <section class="pm-policy-card pm-step14-methods">
                                    <h6>3. Payment Method</h6>
                                    <div class="pm-step14-card-grid">
                                        <div><strong>Primary Payment Method</strong><p>{{ primaryCardType }} ending in {{ form.primary_card_last_four || '----' }}</p><small>{{ form.primary_cardholder_name || 'Company cardholder' }} · {{ form.primary_card_expiry || 'Expiry pending' }}</small></div>
                                        <div><strong>Backup Payment Method</strong><p>{{ backupCardType }} ending in {{ form.backup_card_last_four || '----' }}</p><small>{{ form.backup_cardholder_name || 'Optional backup card' }} · {{ form.backup_card_expiry || 'Not provided' }}</small></div>
                                    </div>
                                    <div class="pm-step14-secure-note">🔒 Your payment will be securely processed. We do not store full card details.</div>
                                </section>
                                <section class="pm-policy-card pm-step14-authorization">
                                    <h6>4. Payment Authorization &amp; Agreement</h6>
                                    <label><input type="checkbox" checked disabled /> I authorize Contractor.com to charge USD {{ paymentTotalDue.toFixed(2) }} when payment processing is enabled and to automatically charge future 30-day service cycles.</label>
                                    <p>Payment processing is currently a front-end preview only. You may continue without being charged.</p>
                                </section>
                                <div
                                    class="pm-policy-card pm-policy-warning pm-step14-warning"
                                >
                                    <strong>Payment integration is not active yet</strong>
                                    <p>
                                        No charge will be made on this screen.
                                        You can continue with the remaining
                                        account setup steps.
                                    </p>
                                </div>
                                <div class="pm-policy-card pm-step14-help">
                                    <strong>What will be available later?</strong>
                                    <ul>
                                        <li>
                                            Secure payment by credit card.
                                        </li>
                                        <li>
                                            Confirmation of your selected plan,
                                            licences, and payment schedule.
                                        </li>
                                        <li>Payment receipt and billing history.</li>
                                    </ul>
                                </div>
                                <div class="pm-step14-meta">
                                    Select Next to save this checkout review and
                                    continue to Step 15. Payment is not
                                    processed at this stage.
                                </div>
                            </div>

                            <div
                                v-if="currentStep === 15"
                                class="pm-setup-form pm-step15-activation"
                            >
                                <div class="pm-step15-head">
                                    <div>
                                        <div class="pm-step15-kicker">
                                            Create Account - 15
                                        </div>
                                        <h6 class="pm-step15-title">
                                            {{ isActivationComplete ? "Activation Complete" : "Account Activation" }}
                                        </h6>
                                        <p class="pm-step15-subtitle">
                                            {{ isActivationComplete ? "Your account has been successfully activated and is ready to use." : "Your checkout review is complete. Your account is now being activated." }}
                                        </p>
                                    </div>
                                    <div class="pm-step15-chip">{{ isActivationComplete ? "Activation Complete" : "75% Complete" }}</div>
                                </div>
                                <section class="pm-step15-banner"><span>✓</span><div><strong>{{ isActivationComplete ? "ACCOUNT ACTIVATION IS COMPLETE!" : "ACCOUNT ACTIVATION HAS BEEN INITIATED!" }}</strong><p>{{ isActivationComplete ? "Your account is active and ready to use. Please check your email for login instructions." : "Your payment will be processed when the payment service is enabled. Your account activation is in progress and temporary login credentials will be sent shortly." }}</p></div></section>
                                <div class="pm-step15-grid">
                                    <section class="pm-policy-card"><h6>1. Activation Progress</h6><ol class="pm-step15-timeline"><li class="done"><strong>Payment Review Confirmed</strong><span>Checkout preview saved successfully.</span><small>{{ activationInitiatedAtLabel }}</small></li><li class="done"><strong>Account Created</strong><span>Your account setup details have been saved.</span></li><li :class="isActivationComplete ? 'done' : 'active'"><strong>Account Activation</strong><span>{{ isActivationComplete ? 'Your account has been activated.' : 'We are preparing your account.' }}</span><small>{{ isActivationComplete ? 'Activation confirmed' : 'Estimated time: 1 – 5 minutes' }}</small></li><li :class="{ done: isActivationComplete }"><strong>Activation Complete</strong><span>{{ isActivationComplete ? 'Your account is active and ready to use.' : 'You will receive your login credentials by email.' }}</span></li></ol></section>
                                    <section class="pm-policy-card"><h6>2. What Happens Next</h6><div class="pm-step15-next-list"><div><b>✉</b><p><strong>Check Your Email</strong><span>We will email {{ form.recovery_email || form.contact_business_email || 'your verified email address' }} shortly.</span></p></div><div><b>🔒</b><p><strong>Temporary Login Credentials</strong><span>Your username, temporary password, and PIN are valid for 48 hours.</span></p></div><div><b>◯</b><p><strong>First Login Required</strong><span>Log in within 48 hours to activate your account.</span></p></div><div><b>✓</b><p><strong>Change Password &amp; PIN</strong><span>Change your temporary credentials immediately after your first login.</span></p></div></div></section>
                                </div>
                                <div class="pm-step15-grid">
                                    <section class="pm-policy-card"><h6>3. Your Account Information</h6><dl class="pm-step14-details"><dt>Company Name</dt><dd>{{ form.company_name || 'Not provided' }}</dd><dt>Account Type</dt><dd>Business Account</dd><dt>Service Plan</dt><dd>{{ selectedServicePlan.label }}</dd><dt>Service Cycle</dt><dd>30-Day Service Cycle</dd><dt>Total Authorized Users</dt><dd>{{ additionalUserLicenseCount + 1 }}</dd><dt>First Service Start Date</dt><dd>{{ paymentScheduleStartDateLabel }}</dd></dl></section>
                                    <section class="pm-policy-card"><h6>4. Activation Details</h6><dl class="pm-step14-details"><dt>Application ID</dt><dd>{{ activationApplicationId }}</dd><dt>Company Account ID</dt><dd>{{ activationAccountId }}</dd><dt>Payment Method</dt><dd>{{ primaryCardType }} ending in {{ form.primary_card_last_four || '----' }}</dd><dt>Amount Due</dt><dd>USD {{ paymentTotalDue.toFixed(2) }}</dd><dt>Status</dt><dd><span class="pm-step15-status">{{ isActivationComplete ? 'Activation Complete' : 'Activation In Progress' }}</span></dd></dl></section>
                                </div>
                                <section class="pm-policy-card pm-step15-security"><h6>5. Important Security Reminders</h6><div><p><strong>🔒 Protect Your Login Credentials</strong><span>Do not share your username, password, or PIN code.</span></p><p><strong>👥 License Sharing is Prohibited</strong><span>Each user licence is for one user only.</span></p><p><strong>↻ Change Every 30 Days</strong><span>Change your password and PIN every 30 days.</span></p><p><strong>▣ Use Authorized Devices Only</strong><span>New devices require verification.</span></p></div></section>
                                <section class="pm-step15-email-note"><strong>✉ Check Your Email Soon!</strong><span>We will send a confirmation to {{ form.recovery_email || form.contact_business_email || 'your email address' }}. Please check your spam folder if it does not arrive within 10 minutes.</span></section>
                            </div>

                            <div
                                v-if="currentStep === 16"
                                class="pm-setup-form pm-step16-reminder"
                            >
                                <div class="pm-step16-head">
                                    <div>
                                        <div class="pm-step16-kicker">
                                            Create Account - Step 16
                                        </div>
                                        <h6 class="pm-step16-title">
                                            Reminder &amp; Log Out
                                        </h6>
                                        <p class="pm-step16-subtitle">
                                            Your account has been created and activated.
                                        </p>
                                    </div>
                                    <div class="pm-step16-chip">100% Complete</div>
                                </div>
                                <section class="pm-step16-complete"><span>✓</span><div><strong>Account Setup Completed</strong><p>Thank you for choosing Contractor.com. Your account has been successfully created and activated.</p></div></section>
                                <section class="pm-step16-thanks"><b>🤝</b><div><strong>Thank You for Choosing Contractor.com</strong><p>We appreciate your trust and look forward to supporting your business.</p></div></section>
                                <div class="pm-step16-grid">
                                    <section class="pm-policy-card pm-step16-reminder-card"><h6>Reminder</h6><ol><li><strong>Change your temporary password within the next 48 hours.</strong><span>Go to: My Account &gt; Login Credentials</span></li><li><strong>Change your Password and Security PIN every 30 days.</strong><span>Go to: My Account &gt; Login Credentials</span></li><li><strong>User licence sharing is strictly prohibited.</strong><span>Each licence is for one user only.</span></li><li><strong>Never share your username, password, or security PIN.</strong><span>You are responsible for all activity on your account.</span></li></ol></section>
                                    <section class="pm-policy-card pm-step16-before"><h6>1. Before You Log Out</h6><ul><li>Save all your account setup information in PDF format now.</li><li>After logout, this preview/setup information will no longer be available.</li><li>Download and keep a copy for your records.</li></ul><button class="btn btn-primary pm-step16-download" type="button" @click="downloadSetupSummary">Download PDF Summary</button></section>
                                    <section class="pm-policy-card pm-step16-security"><h6>2. Security Reminders</h6><ul><li>Temporary credentials are valid for 48 hours only.</li><li>Do not share your credentials with anyone.</li><li>Each licence is for one authorized user only.</li><li>Unauthorized sharing may result in suspension.</li></ul></section>
                                </div>
                                <div class="pm-step16-bottom-grid"><section class="pm-step16-respect"><strong>Respect &amp; Responsibility</strong><span>We respectfully ask you to follow all security and licensing rules so we can continue providing reliable service and support.</span></section><section class="pm-step16-help"><strong>Need Help?</strong><span>Email: support@contractor.com · Phone: +1 (800) 123-4567 · Monday – Friday, 8:00 AM – 6:00 PM</span></section></div>
                                <section class="pm-step16-warning"><strong>Reminder:</strong> Please download your account information before logging out.</section>
                                <div class="pm-step16-acknowledgements"><label class="pm-step16-ack"><input type="checkbox" v-model="form.safety_ack" /> I remember the reminders above.</label><label class="pm-step16-ack"><input type="checkbox" v-model="summaryDownloaded" /> I will save / download my account information in PDF format.</label></div>
                                <div class="pm-step16-actions"><button class="btn btn-outline-primary" type="button" @click="goToDashboard">← Back to Dashboard</button><button class="btn btn-primary pm-step16-logout" type="button" :disabled="loading" @click="logoutSecurely">{{ loading ? "Logging out..." : "Log Out Securely" }} →</button></div>
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

                            <div
                                v-if="currentStep === 1"
                                class="pm-step1-actions"
                            >
                                <button
                                    class="btn btn-outline-primary pm-step1-save-btn"
                                    type="button"
                                    :disabled="loading"
                                    @click="saveStep"
                                >
                                    Save
                                </button>
                                <button
                                    class="btn btn-primary pm-step1-next-btn"
                                    type="button"
                                    :disabled="loading"
                                    @click="next"
                                >
                                    Next
                                </button>
                            </div>

                            <div
                                v-else-if="!isFinalStep && currentStep !== 16"
                                class="pm-setup-actions"
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
                                    {{
                                        currentStep === 8
                                            ? "I Agree & Continue"
                                            : isFinalStep
                                              ? "Finish Setup"
                                              : "Next"
                                    }}
                                </button>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.pm-step14-checkout-grid, .pm-step14-card-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.pm-step14-premium h6 { color: #0738de; font-weight: 700; }
.pm-step14-summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 14px; }
.pm-step14-summary-grid div, .pm-step14-card-grid > div { display: grid; gap: 4px; }
.pm-step14-summary-grid span, .pm-step14-details dt, .pm-step14-card-grid small { color: #50617c; font-size: .84rem; }
.pm-step14-summary-grid strong { color: #09224f; }
.pm-step14-details { display: grid; grid-template-columns: 1fr auto; gap: 12px; margin: 16px 0 0; font-size: .9rem; }
.pm-step14-details dt, .pm-step14-details dd { margin: 0; padding-bottom: 9px; border-bottom: 1px solid #e8eef9; }
.pm-step14-details dd { color: #10204d; text-align: right; font-weight: 600; }
.pm-step14-total { display:flex; justify-content:space-between; align-items:center; margin-top: 16px; padding: 16px; border: 1px solid #c7d8ff; border-radius: 7px; color: #063de0; }
.pm-step14-total strong { font-size: 1.25rem; }
.pm-step14-payment-note { margin-top: 16px; padding: 14px; background: #fffaf0; border: 1px solid #ffe1ae; border-radius: 7px; }
.pm-step14-payment-note p, .pm-step14-authorization p { margin: 6px 0 0; font-size: .88rem; }
.pm-step14-methods, .pm-step14-authorization { margin-top: 16px; }
.pm-step14-card-grid > div { padding: 14px; border: 1px solid #dce6f7; border-radius: 7px; }
.pm-step14-card-grid p { margin: 8px 0 0; color: #10204d; }
.pm-step14-secure-note { margin-top: 14px; padding: 10px 12px; color: #08763c; background: #f0fbf4; border: 1px solid #bce8cc; border-radius: 6px; font-size: .88rem; }
.pm-step14-authorization label { display: flex; gap: 10px; color: #10204d; line-height: 1.5; }
.pm-step14-authorization input { margin-top: 4px; accent-color: #0b41e7; }
.pm-step15-activation { display: grid; gap: 16px; }
.pm-step15-banner { display: flex; gap: 16px; padding: 18px 20px; color: #08752d; background: #f2fff5; border: 1px solid #9adbb0; border-radius: 8px; }
.pm-step15-banner > span { display: grid; place-items: center; flex: 0 0 36px; height: 36px; border-radius: 50%; color: #fff; background: #1d9b31; font-size: 1.35rem; font-weight: 700; }
.pm-step15-banner strong { font-size: 1.05rem; }.pm-step15-banner p { margin: 7px 0 0; color: #173a22; font-size: .9rem; }
.pm-step15-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.pm-step15-timeline { display: grid; gap: 18px; margin: 18px 0 0; padding: 0; list-style: none; }.pm-step15-timeline li { position: relative; display: grid; gap: 4px; padding-left: 42px; color: #14295d; }.pm-step15-timeline li::before { content: '○'; position: absolute; left: 4px; top: -4px; color: #90a2c5; font-size: 1.7rem; }.pm-step15-timeline li.done::before { content: '✓'; display: grid; place-items: center; width: 25px; height: 25px; border-radius: 50%; color: #fff; background: #1b9c3a; font-size: .9rem; }.pm-step15-timeline li.active::before { content: '✦'; display: grid; place-items: center; width: 25px; height: 25px; border-radius: 50%; color: #fff; background: #144ce5; font-size: .8rem; }.pm-step15-timeline span, .pm-step15-timeline small { font-size: .84rem; }
.pm-step15-next-list { display: grid; gap: 14px; margin-top: 16px; }.pm-step15-next-list > div { display: flex; gap: 12px; }.pm-step15-next-list b { display: grid; place-items: center; width: 34px; height: 34px; border: 1px solid #cddafe; border-radius: 6px; color: #0c43df; }.pm-step15-next-list p { display: grid; gap: 3px; margin: 0; }.pm-step15-next-list span { color: #23355f; font-size: .84rem; }.pm-step15-status { display: inline-block; padding: 3px 9px; color: #107032; background: #f1fbf4; border: 1px solid #b6dfc2; border-radius: 5px; font-size: .78rem; }
.pm-step15-security > div { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-top: 14px; }.pm-step15-security p { display: grid; gap: 7px; margin: 0; padding: 0 14px; border-right: 1px solid #dbe4f4; }.pm-step15-security p:first-child { padding-left: 0; }.pm-step15-security p:last-child { border: 0; }.pm-step15-security span { color: #30436d; font-size: .8rem; }
.pm-step15-email-note { display: grid; gap: 5px; padding: 14px 18px; color: #083bdf; background: #f3f7ff; border: 1px solid #b9cbff; border-radius: 7px; }.pm-step15-email-note span { font-size: .86rem; }
.pm-step16-reminder { display: grid; gap: 16px; }.pm-step16-complete, .pm-step16-thanks, .pm-step16-help { display: flex; gap: 16px; padding: 18px 22px; border-radius: 8px; }.pm-step16-complete { color: #12712f; background: #f1fff3; border: 1px solid #93d5a4; }.pm-step16-complete > span { display:grid; place-items:center; width:42px; height:42px; border-radius:50%; color:#fff; background:#168b2e; font-size:1.7rem; font-weight:700; }.pm-step16-complete strong, .pm-step16-thanks strong { display:block; font-size:1.1rem; }.pm-step16-complete p, .pm-step16-thanks p { margin:5px 0 0; color:#172751; }.pm-step16-thanks { color:#093bc7; background:#f3f7ff; border:1px solid #bfd1ff; }.pm-step16-thanks b { font-size:2.5rem; }.pm-step16-grid { display:grid; grid-template-columns:1.15fr .95fr .95fr; gap:16px; }.pm-step16-grid ol, .pm-step16-grid ul { display:grid; gap:13px; padding-left:22px; color:#172751; font-size:.88rem; }.pm-step16-grid h6 { color:#0742dc; }.pm-step16-security { border-color:#f5c676; }.pm-step16-help { align-items:center; color:#083bdf; background:#f4f8ff; border:1px solid #bdd1ff; }.pm-step16-help span { font-size:.88rem; }.pm-step16-warning { padding:12px 18px; color:#9a1212; background:#fff4f2; border:1px solid #ffaaa1; border-radius:6px; }.pm-step16-ack { color:#172751; font-size:.9rem; }.pm-step16-ack input { margin-right:8px; accent-color:#0a45df; }
.pm-step16-reminder-card { border-color: #20304d; }.pm-step16-reminder-card li { display:grid; gap:4px; padding-bottom:11px; border-bottom:1px solid #dce3ef; }.pm-step16-reminder-card li:last-child { border:0; }.pm-step16-reminder-card span { color:#30436d; font-size:.8rem; }.pm-step16-before { border-color:#bdd1ff; }.pm-step16-download { width:100%; margin-top:16px; }.pm-step16-bottom-grid { display:grid; grid-template-columns:1fr 1.4fr; gap:16px; }.pm-step16-respect { display:grid; gap:6px; padding:16px; color:#083bdf; background:#f5f8ff; border:1px solid #bed0ff; border-radius:7px; }.pm-step16-respect span { color:#20365f; font-size:.85rem; }.pm-step16-acknowledgements { display:grid; gap:7px; }.pm-step16-actions { display:flex; justify-content:space-between; gap:16px; padding-top:4px; }.pm-step16-logout { min-width:220px; }.pm-step16-actions .btn { font-weight:600; }
@media (max-width: 767px) { .pm-step14-checkout-grid, .pm-step14-card-grid { grid-template-columns: 1fr; } .pm-step14-summary-grid { grid-template-columns: 1fr; } }
@media (max-width: 767px) { .pm-step15-grid, .pm-step15-security > div, .pm-step16-grid, .pm-step16-bottom-grid { grid-template-columns: 1fr; }.pm-step15-security p { padding: 12px 0; border-right: 0; border-bottom: 1px solid #dbe4f4; }.pm-step16-actions { align-items:stretch; flex-direction:column; }.pm-step16-logout { min-width:0; } }
</style>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { jsPDF } from "jspdf";
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
const summaryDownloaded = ref(false);

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
        title: "Company Profile",
        subtitle: "Provide business, address, service, and representative details.",
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
        title: "Service Plans",
        subtitle: "Choose the service plan that best fits your company needs.",
    },
    {
        key: "notifications",
        title: "User Licenses",
        subtitle: "Add the number of user licenses you need for your team.",
    },
    {
        key: "security",
        title: "Payment Schedule",
        subtitle: "Review your 12-month payment schedule.",
    },
    {
        key: "subscription",
        title: "Payment Method",
        subtitle: "Add your primary and optional backup credit card.",
    },
    {
        key: "licenses",
        title: "Contacts",
        subtitle: "Provide key contact information for your company.",
    },
    {
        key: "billing",
        title: "Account Safety & Security",
        subtitle: "Set temporary credentials, recovery contacts, and MFA.",
    },
    {
        key: "admin",
        title: "Declaration & Agreement",
        subtitle: "Review and accept the account declaration.",
    },
    {
        key: "recovery",
        title: "Preview & Confirm",
        subtitle: "Review account setup details before payment.",
    },
    {
        key: "checkout",
        title: "Check Out & Payment",
        subtitle: "Review your order and payment details.",
    },
    {
        key: "activation",
        title: "Account Activation",
        subtitle: "Your account activation is in progress.",
    },
    {
        key: "safety",
        title: "Reminder & Log Out",
        subtitle: "Save your account information and review security reminders.",
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

const sidebarSteps = [
    { key: "welcome", title: "Welcome", iconHtml: '&#128075;', contentStep: 1 },
    { key: "profile", title: "My Profile", iconHtml: '&#128100;', contentStep: 2 },
    { key: "company", title: "Company Profile", iconHtml: '&#127970;', contentStep: 3 },
    {
        key: "licenses",
        title: "Professional Licenses",
        iconHtml: '&#127380;',
        contentStep: 4,
    },
    {
        key: "insurance",
        title: "Liability Insurance",
        iconHtml: '&#128737;',
        contentStep: 5,
    },
    { key: "plans", title: "Service Plans", iconHtml: '&#128194;', contentStep: 6 },
    { key: "users", title: "User Licenses", iconHtml: '&#128101;', contentStep: 7 },
    {
        key: "schedule",
        title: "Payments Schedule",
        iconHtml: '&#128467;',
        contentStep: 8,
    },
    {
        key: "payment",
        title: "Payment Method(s)",
        iconHtml: '&#128179;',
        contentStep: 9,
    },
    { key: "contacts", title: "Contacts", iconHtml: '&#128222;', contentStep: 10 },
    {
        key: "security",
        title: "Account Safety & Security",
        iconHtml: '&#128272;',
        contentStep: 11,
    },
    {
        key: "agreement",
        title: "Declaration & Agreement",
        iconHtml: '&#128221;',
        contentStep: 12,
    },
    {
        key: "preview",
        title: "Preview & Confirm",
        iconHtml: '&#128065;',
        contentStep: 13,
    },
    {
        key: "checkout",
        title: "Check Out & Payment",
        iconHtml: '&#128722;',
        contentStep: 14,
    },
    {
        key: "activation",
        title: "Account Activation",
        iconHtml: '&#128640;',
        contentStep: 15,
    },
    {
        key: "logout",
        title: "Log out & Reminder",
        iconHtml: '&#9203;',
        contentStep: 16,
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

const getTodayIsoDate = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const parseIsoDate = (value) => {
    if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
    const [year, month, day] = value.split("-").map(Number);
    return new Date(Date.UTC(year, month - 1, day));
};

const formatIsoDate = (date) => {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) return "";
    return date.toISOString().slice(0, 10);
};

const addDaysToIsoDate = (value, days) => {
    const parsed = parseIsoDate(value);
    if (!parsed) return "";
    parsed.setUTCDate(parsed.getUTCDate() + days);
    return formatIsoDate(parsed);
};

const getDefaultPaymentScheduleStartDate = () =>
    addDaysToIsoDate(getTodayIsoDate(), 6) || getTodayIsoDate();

const formatHumanMonthDay = (value) => {
    const parsed = parseIsoDate(value);
    if (!parsed) return "--";
    return new Intl.DateTimeFormat("en-US", {
        month: "short",
        day: "numeric",
        timeZone: "UTC",
    }).format(parsed);
};
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

const selectedServicePlan = computed(
    () =>
        servicePlanOptions.find(
            (plan) => plan.key === form.value.subscription_plan,
        ) || servicePlanOptions[0],
);

const userLicensePrice = computed(() => {
    const plan = selectedServicePlan.value;
    return Number(plan?.monthlyPrice || 0);
});

const additionalUserLicenseCount = computed(() => {
    const raw = Number(form.value.additional_user_licenses || 0);
    if (!Number.isFinite(raw)) return 0;
    return Math.max(0, Math.floor(raw));
});

const userLicensesTotalDue = computed(
    () => additionalUserLicenseCount.value * userLicensePrice.value,
);

const paymentScheduleCompletionPercent = computed(() => 40);

const cardTypeFromNumber = (number) => {
    const digits = String(number || "").replace(/\D/g, "");
    if (/^4/.test(digits)) return "VISA";
    if (/^(5[1-5]|2[2-7])/.test(digits)) return "Mastercard";
    if (/^3[47]/.test(digits)) return "American Express";
    return "Card type";
};

const primaryCardType = computed(() => cardTypeFromNumber(form.value.primary_card_number));
const backupCardType = computed(() => cardTypeFromNumber(form.value.backup_card_number));
const maskedCardPlaceholder = (lastFour) => {
    const dots = String.fromCharCode(8226).repeat(4);
    return lastFour ? `${dots}  ${dots}  ${dots}  ${lastFour}` : "1234 5678 9012 3456";
};
const primaryCardPlaceholder = computed(() => maskedCardPlaceholder(form.value.primary_card_last_four));
const backupCardPlaceholder = computed(() => maskedCardPlaceholder(form.value.backup_card_last_four));

const paymentScheduleAmount = computed(() => {
    const basePlanAmount = Number(selectedServicePlan.value?.monthlyPrice || 0);
    return Number((basePlanAmount + userLicensesTotalDue.value).toFixed(2));
});

const servicePlanAmount = computed(() =>
    Number(selectedServicePlan.value?.monthlyPrice || 0),
);
const paymentTaxAmount = computed(() =>
    Number((paymentScheduleAmount.value * 0.05).toFixed(2)),
);
const paymentTotalDue = computed(() =>
    Number((paymentScheduleAmount.value + paymentTaxAmount.value).toFixed(2)),
);

const isActivationComplete = computed(
    () => form.value.activation_status === "active",
);

const activationInitiatedAtLabel = computed(() => {
    const value = form.value.activation_initiated_at;
    if (!value) return "Activation will begin when this step is saved.";
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
});
const activationApplicationId = computed(() =>
    `APP-${String(authState.user?.id || "PENDING").padStart(6, "0")}`,
);
const activationAccountId = computed(() =>
    `ACC-${String(authState.user?.id || "PENDING").padStart(6, "0")}`,
);

const paymentScheduleServiceLabel = computed(() =>
    `${selectedServicePlan.value?.label || "Service"} Plan`.toUpperCase(),
);

const paymentScheduleRows = computed(() => {
    const startDate =
        form.value.payment_schedule_start_date ||
        getDefaultPaymentScheduleStartDate();

    return Array.from({ length: 12 }, (_, index) => {
        const serviceStart = addDaysToIsoDate(startDate, index * 30);
        const serviceEnd = addDaysToIsoDate(serviceStart, 29);
        const autoChargeDate = addDaysToIsoDate(serviceStart, -3);
        const chargingDate = addDaysToIsoDate(serviceStart, -6);

        return {
            payment_number: index + 1,
            service_period_start: serviceStart,
            service_period_end: serviceEnd,
            auto_charge_date: autoChargeDate,
            charging_date: chargingDate,
            amount: paymentScheduleAmount.value,
        };
    });
});

const paymentScheduleStartDateLabel = computed(() =>
    form.value.payment_schedule_start_date ||
        getDefaultPaymentScheduleStartDate(),
);

const paymentScheduleFirstPeriodSummary = computed(() => {
    const firstRow = paymentScheduleRows.value[0];
    if (!firstRow) return "--";
    return `${formatHumanMonthDay(firstRow.service_period_start)} to ${formatHumanMonthDay(firstRow.service_period_end)}`;
});

const paymentScheduleFirstChargeSummary = computed(() => {
    const firstRow = paymentScheduleRows.value[0];
    return firstRow ? formatHumanMonthDay(firstRow.auto_charge_date) : "--";
});

const formatCurrencyAmount = (value) => {
    const amount = Number(value || 0);
    return amount.toFixed(2);
};

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
const nationalityOptions = computed(() =>
    countries.value
        .map((country) => String(country.country_name || "").trim())
        .filter(Boolean),
);

const languageOptions = LANGUAGE_OPTIONS;
const languageCodeSet = new Set(LANGUAGE_CODES);
const businessStructureOptions = [
    "Sole Proprietorship",
    "Partnership",
    "Incorporated",
    "Corporation",
    "Limited Liability Company (LLC)",
    "Nonprofit Organization",
    "Other",
];

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
const companyStatusOptions = [
    "Active",
    "Inactive",
    "Pending",
    "Suspended",
    "Dissolved",
];
const businessTypeOptions = [
    "Operate as a Company",
    "Operate as an Individual",
    "Operate as a Partnership",
    "Operate as a Nonprofit",
];
const currencyOptions = [
    { code: "CAD", label: "Canadian Dollar (CAD)" },
    { code: "USD", label: "US Dollar (USD)" },
    { code: "EUR", label: "Euro (EUR)" },
    { code: "GBP", label: "British Pound Sterling (GBP)" },
    { code: "BDT", label: "Bangladeshi Taka (BDT)" },
    { code: "AUD", label: "Australian Dollar (AUD)" },
];
const industryTypeOptions = [
    "Construction",
    "Renovation",
    "Electrical",
    "Plumbing",
    "HVAC",
    "Roofing",
    "Landscaping",
    "General Contracting",
    "Property Maintenance",
    "Other",
];
const primaryServiceOptions = [
    "General Contracting",
    "Renovations",
    "Project Management",
    "New Construction",
    "Electrical",
    "Plumbing",
    "HVAC",
    "Roofing",
    "Painting",
    "Landscaping",
];
const yearsInBusinessOptions = [
    "0-1 Year",
    "1-3 Years",
    "3-5 Years",
    "5-10 Years",
    "10+ Years",
];
const employeeCountOptions = [
    "1-10",
    "11-50",
    "51-200",
    "201-500",
    "500+",
];
const revenueRangeOptions = [
    "Under $250K",
    "$250K - $1M",
    "$1M - $5M",
    "$5M - $10M",
    "$10M+",
];
const hearAboutOptions = [
    "Search Engine (Google, Bing, etc.)",
    "Social Media",
    "Referral",
    "Advertisement",
    "Industry Event",
    "Other",
];
const professionalLicenseTypeOptions = [
    "Business License",
    "Trade License",
    "General Contractor License",
    "Electrical License",
    "Plumbing License",
    "HVAC License",
    "Engineering License",
    "Other",
];
const professionalLicenseStatusOptions = [
    "Active",
    "Pending Renewal",
    "Expired",
    "Suspended",
    "Inactive",
];
const servicePlanOptions = [
    {
        key: "basic",
        label: "Starter",
        subtitle: "For small teams getting started",
        monthlyPrice: 49,
        yearlyPrice: "588",
        features: [
            "Up to 5 users",
            "10 GB storage",
            "Basic reports",
            "Email support",
            "Core features access",
        ],
    },
    {
        key: "pro",
        label: "Professional",
        subtitle: "For growing businesses",
        monthlyPrice: 99,
        yearlyPrice: "1,188",
        recommended: true,
        features: [
            "Up to 25 users",
            "100 GB storage",
            "Advanced reports",
            "Priority email support",
            "All core features",
        ],
    },
    {
        key: "premium",
        label: "Business",
        subtitle: "For larger operations",
        monthlyPrice: 199,
        yearlyPrice: "2,388",
        features: [
            "Up to 100 users",
            "500 GB storage",
            "Custom reports",
            "Phone & email support",
            "All advanced features",
        ],
    },
    {
        key: "enterprise",
        label: "Enterprise",
        subtitle: "For custom requirements",
        monthlyPrice: null,
        yearlyPrice: null,
        features: [
            "Unlimited users",
            "Custom storage",
            "Custom reports",
            "Dedicated support",
            "All features + custom options",
        ],
    },
];
const insuranceCoverageTypeOptions = [
    "General Liability",
    "Professional Liability",
    "Workers Compensation",
    "Commercial Auto",
    "Umbrella Liability",
    "Builder's Risk",
    "Other",
];

const createProfessionalLicense = () => ({
    license_name: "",
    license_number: "",
    legal_business_name: "",
    license_type: "",
    issuing_country_id: "",
    issuing_country: "",
    issuing_authority: "",
    issue_date: "",
    expiry_date: "",
    license_status: "Active",
    license_website: "",
    verification_url: "",
    description_scope: "",
});

const USER_LICENSE_BASE_DATE = "2026-07-18";
const buildUserLicenseNumber = (index) =>
    `UL-260718-${String(index + 1).padStart(4, "0")}`;

const createUserLicenseEntry = (index) => ({
    full_name: "",
    phone_number: "",
    license_number: buildUserLicenseNumber(index),
    expiry_note: "Announced after payment",
});

const createSetupContact = () => ({
    department_name: "",
    contact_person: "",
    phone_number: "",
    email: "",
    position_title: "",
});

const form = ref({
    full_name: "",
    company_name: "",
    business_registration_number: "",
    company_id_number: "",
    business_number: "",
    incorporation_date: "",
    company_status: "Active",
    business_type: "Operate as a Company",
    currency_code: "CAD",
    operating_name: "",
    tax_number: "",
    pst_qst_number: "",
    year_established: "",
    business_structure: "",
    company_description: "",
    industry_type: "",
    primary_services: [],
    years_in_business: "",
    number_of_employees: "",
    annual_revenue_range: "",
    hear_about_source: "",
    owner_name: "",
    director_name: "",
    authorized_contact_name: "",
    designation_title: "",
    authorized_contact_phone: "",
    authorized_contact_email: "",
    profile_date_of_birth: "",
    profile_nationality: "",
    company_logo_url: "",
    company_logo_path: "",
    company_logo_preview: "",
    company_address: "",
    company_address_line_2: "",
    company_city: "",
    company_state: "",
    company_zip: "",
    company_country_id: "",
    company_country: "",
    business_location: "",
    company_phone: "",
    linkedin_profile: "",
    facebook_profile: "",
    instagram_profile: "",
    fax_number: "",
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
    professional_licenses: [createProfessionalLicense()],
    insurance_company_name: "",
    insurance_policy_number: "",
    insurance_legal_business_name: "",
    insurance_coverage_type: "",
    insurance_policy_start_date: "",
    insurance_policy_end_date: "",
    insurance_coverage_amount: "",
    insurance_deductible_amount: "",
    insurance_issuing_country_id: "",
    insurance_issuing_country: "",
    insurance_issuing_authority: "",
    insurance_certificate_number: "",
    insurance_issue_date: "",
    insurance_description: "",
    insurance_additional_insured: "",
    insurance_certificate_path: "",
    insurance_certificate_url: "",
    insurance_certificate_name: "",
    additional_user_licenses: 1,
    user_licenses: [createUserLicenseEntry(0)],
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
    step_2_confirmation_ack: false,
    notify_email: true,
    notify_sms: false,
    notify_arrival: true,
    notify_security: true,
    notify_marketing: false,
    payment_schedule_start_date: getDefaultPaymentScheduleStartDate(),
    payment_schedule_ack: false,
    primary_cardholder_name: "",
    primary_card_number: "",
    primary_card_last_four: "",
    primary_card_expiry: "",
    primary_card_cvv: "",
    backup_cardholder_name: "",
    backup_card_number: "",
    backup_card_last_four: "",
    backup_card_expiry: "",
    backup_card_cvv: "",
    payment_method_ack: false,
    setup_contacts: [createSetupContact()],
    security_username: "",
    security_password: "",
    security_password_confirm: "",
    security_pin: "",
    security_pin_confirm: "",
    declaration_ack: false,
    declaration_full_name: "",
    declaration_position_title: "",
    declaration_signed_at: getTodayIsoDate(),
    declaration_initials: "",
    declaration_signature: "",
    preview_confirm_ack: false,
    preview_full_name: "",
    preview_position_title: "",
    preview_initials: "",
    preview_signature: "",
    security_2fa: false,
    security_policy_ack: false,
    subscription_plan: "pro",
    license_ack: false,
    billing_method: "",
    billing_address: "",
    billing_cycle: "annually",
    billing_auto_renew: false,
    billing_policy_ack: false,
    admin_first_name: "",
    admin_last_name: "",
    admin_role: "",
    admin_role_other: "",
    admin_is_system: false,
    admin_phone: "",
    admin_email: "",
    admin_created_date: getTodayIsoDate(),
    recovery_email: "",
    recovery_phone: "",
    email_verify_status: "pending",
    activation_status: "not_started",
    activation_initiated_at: "",
    activation_completed_at: "",
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
    if (!form.value.security_username && authState.user?.email) {
        form.value.security_username = authState.user.email;
    }
};

const currentSidebarStep = computed(() => {
    const matchIndex = sidebarSteps.findIndex(
        (step) => currentStep.value <= step.contentStep,
    );
    return matchIndex >= 0 ? matchIndex + 1 : sidebarSteps.length;
});

const completedSidebarSteps = computed(() =>
    Math.round(sidebarSteps.length * 0.2),
);

const progressPercent = computed(() => {
    return 20;
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

const downloadSetupSummary = () => {
    const pdf = new jsPDF({ unit: "pt", format: "letter" });
    const rows = [
        ["Account Setup Summary", ""],
        ["Company", form.value.company_name || "Not provided"],
        ["Account status", form.value.activation_status || "In progress"],
        ["Service plan", selectedServicePlan.value.label],
        ["Service cycle", "30-Day Service Cycle"],
        ["Authorized users", String(additionalUserLicenseCount.value + 1)],
        ["Primary email", form.value.recovery_email || form.value.contact_business_email || "Not provided"],
        ["Start date", paymentScheduleStartDateLabel.value],
        ["Amount due", `USD ${paymentTotalDue.value.toFixed(2)}`],
    ];
    let y = 54;
    pdf.setFontSize(18);
    pdf.text("Contractor.com", 48, y);
    y += 30;
    pdf.setFontSize(14);
    pdf.text("Account Setup Summary", 48, y);
    y += 28;
    pdf.setFontSize(10);
    rows.slice(1).forEach(([label, value]) => {
        pdf.setFont("helvetica", "bold");
        pdf.text(`${label}:`, 48, y);
        pdf.setFont("helvetica", "normal");
        pdf.text(String(value), 180, y, { maxWidth: 340 });
        y += 24;
    });
    pdf.setFontSize(8);
    pdf.text(`Generated ${new Date().toLocaleString()}`, 48, 730);
    pdf.save("contractor-account-setup-summary.pdf");
    summaryDownloaded.value = true;
};

const goToDashboard = () => router.push("/dashboard");

const logoutSecurely = async () => {
    loading.value = true;
    try {
        await logout();
    } catch {
        // Clear local access even if the logout request cannot reach the server.
    } finally {
        clearToken();
        router.push("/login");
        loading.value = false;
    }
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
        2: ["contact_primary_phone"],
        3: ["contact_primary_phone", "authorized_contact_phone"],
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
    if (payload.insurance_issuing_country_id) {
        payload.insurance_issuing_country =
            resolveCountryNameById(payload.insurance_issuing_country_id) ||
            payload.insurance_issuing_country ||
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
    if (Array.isArray(payload.professional_licenses)) {
        payload.professional_licenses = payload.professional_licenses.map(
            (row) => {
                if (!row) return row;
                const next = { ...row };
                if (next.issuing_country_id) {
                    next.issuing_country =
                        resolveCountryNameById(next.issuing_country_id) ||
                        next.issuing_country ||
                        "";
                }
                return next;
            },
        );
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
                !next.insurance_issuing_country_id &&
                next.insurance_issuing_country
            ) {
                next.insurance_issuing_country_id = resolveCountryIdByName(
                    next.insurance_issuing_country,
                );
            }
            if (
                !next.insurance_issuing_country &&
                next.insurance_issuing_country_id
            ) {
                next.insurance_issuing_country = resolveCountryNameById(
                    next.insurance_issuing_country_id,
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
            if (
                Array.isArray(setup.professional_licenses) &&
                setup.professional_licenses.length
            ) {
                next.professional_licenses = setup.professional_licenses.map(
                    (row) => ({
                        license_name: row.license_name || "",
                        license_number: row.license_number || "",
                        legal_business_name:
                            row.legal_business_name || "",
                        license_type: row.license_type || "",
                        issuing_country_id:
                            row.issuing_country_id ||
                            resolveCountryIdByName(row.issuing_country),
                        issuing_country:
                            row.issuing_country ||
                            resolveCountryNameById(row.issuing_country_id),
                        issuing_authority: row.issuing_authority || "",
                        issue_date: row.issue_date || "",
                        expiry_date: row.expiry_date || "",
                        license_status: row.license_status || "Active",
                        license_website: row.license_website || "",
                        verification_url: row.verification_url || "",
                        description_scope: row.description_scope || "",
                    }),
                );
            }
            if (Array.isArray(setup.setup_contacts) && setup.setup_contacts.length) {
                next.setup_contacts = setup.setup_contacts.map((row) => ({
                    department_name: row.department_name || "",
                    contact_person: row.contact_person || "",
                    phone_number: row.phone_number || "",
                    email: row.email || "",
                    position_title: row.position_title || "",
                }));
            }
            if (
                Array.isArray(setup.user_licenses) &&
                setup.user_licenses.length
            ) {
                next.user_licenses = setup.user_licenses.map((row, index) => ({
                    full_name: row.full_name || "",
                    phone_number: row.phone_number || "",
                    license_number:
                        row.license_number || buildUserLicenseNumber(index),
                    expiry_note:
                        row.expiry_note || "Announced after payment",
                }));
                next.additional_user_licenses = next.user_licenses.length;
            }
            if (!next.company_name && authState.user?.company_name) {
                next.company_name = authState.user.company_name;
            }
            if (!next.payment_schedule_start_date) {
                next.payment_schedule_start_date =
                    getDefaultPaymentScheduleStartDate();
            }
            normalizeAdminRoleSelection(next);
            form.value = next;
        }
    } catch {
        if (!form.value.company_name && authState.user?.company_name) {
            form.value.company_name = authState.user.company_name;
        }
        if (!form.value.payment_schedule_start_date) {
            form.value.payment_schedule_start_date =
                getDefaultPaymentScheduleStartDate();
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

const onInsuranceCertificateSelect = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    error.value = "";
    const formData = new FormData();
    formData.append("certificate", file);
    try {
        const { data } = await client.post(
            "/account-setup/insurance-certificate",
            formData,
            {
                headers: { "Content-Type": "multipart/form-data" },
            },
        );
        if (data?.success) {
            form.value.insurance_certificate_path = data.data.path || "";
            form.value.insurance_certificate_url = data.data.url || "";
            form.value.insurance_certificate_name = data.data.name || file.name;
        }
    } catch {
        error.value = "Insurance certificate upload failed. Please try again.";
    } finally {
        event.target.value = "";
    }
};

const saveStep = async () => {
    error.value = "";
    success.value = false;
    normalizeFormPhoneFieldsForStep(currentStep.value);
    if (currentStep.value === 1) {
        success.value = true;
        return true;
    }
    if (currentStep.value === 2) {
        if (!String(form.value.full_name || "").trim()) {
            error.value = "Full name is required.";
            return false;
        }
        if (!String(form.value.admin_role || "").trim()) {
            error.value = "Role / position is required.";
            return false;
        }
        if (!form.value.contact_primary_phone) {
            error.value = "Phone number is required.";
            return false;
        }
        if (!isE164PhoneNumber(form.value.contact_primary_phone)) {
            error.value =
                "Phone number must be in E.164 format (e.g. +14165550100).";
            return false;
        }
        if (!String(form.value.contact_business_email || "").trim()) {
            error.value = "Email address is required.";
            return false;
        }
        if (!String(form.value.profile_date_of_birth || "").trim()) {
            error.value = "Date of birth is required.";
            return false;
        }
        if (!String(form.value.profile_nationality || "").trim()) {
            error.value = "Nationality is required.";
            return false;
        }
        if (!String(form.value.company_address || "").trim()) {
            error.value = "Address is required.";
            return false;
        }
        if (!String(form.value.company_city || "").trim()) {
            error.value = "City is required.";
            return false;
        }
        if (!String(form.value.company_state || "").trim()) {
            error.value = "State / Province is required.";
            return false;
        }
        if (!String(form.value.company_zip || "").trim()) {
            error.value = "Postal / ZIP code is required.";
            return false;
        }
        if (!form.value.company_country_id) {
            error.value = "Country is required.";
            return false;
        }
        if (!String(form.value.pref_language || "").trim()) {
            error.value = "Preferred language is required.";
            return false;
        }
        if (!String(form.value.company_logo_path || "").trim()) {
            error.value = "Profile photo is required.";
            return false;
        }
        if (!form.value.step_2_confirmation_ack) {
            error.value = "You must confirm that you are authorized to act on behalf of the company.";
            return false;
        }
    }
    if (currentStep.value === 3) {
        if (!String(form.value.company_name || "").trim()) {
            error.value = "Legal business name is required.";
            return false;
        }
        if (!String(form.value.business_number || "").trim()) {
            error.value = "Business number is required.";
            return false;
        }
        if (!String(form.value.business_registration_number || "").trim()) {
            error.value = "Business registration number is required.";
            return false;
        }
        if (!String(form.value.company_status || "").trim()) {
            error.value = "Company status is required.";
            return false;
        }
        if (!String(form.value.business_type || "").trim()) {
            error.value = "Business type is required.";
            return false;
        }
        if (!String(form.value.business_structure || "").trim()) {
            error.value = "Operating structure is required.";
            return false;
        }
        if (!String(form.value.currency_code || "").trim()) {
            error.value = "Currency is required.";
            return false;
        }
        if (!String(form.value.company_address || "").trim()) {
            error.value = "Registered business address is required.";
            return false;
        }
        if (!String(form.value.company_city || "").trim()) {
            error.value = "City is required.";
            return false;
        }
        if (!String(form.value.company_state || "").trim()) {
            error.value = "Province / State is required.";
            return false;
        }
        if (!String(form.value.company_zip || "").trim()) {
            error.value = "Postal / ZIP code is required.";
            return false;
        }
        if (!form.value.company_country_id) {
            error.value = "Registered country is required.";
            return false;
        }
        if (!String(form.value.contact_primary_phone || "").trim()) {
            error.value = "Business phone is required.";
            return false;
        }
        if (!isE164PhoneNumber(form.value.contact_primary_phone)) {
            error.value =
                "Phone number must be in E.164 format (e.g. +14165550100).";
            return false;
        }
        if (!String(form.value.contact_business_email || "").trim()) {
            error.value = "Business email is required.";
            return false;
        }
        if (!String(form.value.contact_website || "").trim()) {
            error.value = "Company website is required.";
            return false;
        }
        if (!String(form.value.industry_type || "").trim()) {
            error.value = "Industry type is required.";
            return false;
        }
        if (!Array.isArray(form.value.primary_services) || !form.value.primary_services.length) {
            error.value = "Please select at least one primary service.";
            return false;
        }
        if (!String(form.value.years_in_business || "").trim()) {
            error.value = "Years in business is required.";
            return false;
        }
        if (!String(form.value.number_of_employees || "").trim()) {
            error.value = "Number of employees is required.";
            return false;
        }
        if (!String(form.value.annual_revenue_range || "").trim()) {
            error.value = "Annual revenue range is required.";
            return false;
        }
        if (!String(form.value.company_description || "").trim()) {
            error.value = "Business description is required.";
            return false;
        }
        if (!String(form.value.hear_about_source || "").trim()) {
            error.value = "How you heard about us is required.";
            return false;
        }
        if (!String(form.value.owner_name || "").trim()) {
            error.value = "Owner name is required.";
            return false;
        }
        if (!String(form.value.authorized_contact_name || "").trim()) {
            error.value = "Authorized contact name is required.";
            return false;
        }
        if (!String(form.value.designation_title || "").trim()) {
            error.value = "Designation / title is required.";
            return false;
        }
        if (!String(form.value.authorized_contact_phone || "").trim()) {
            error.value = "Authorized contact phone is required.";
            return false;
        }
        if (!isE164PhoneNumber(form.value.authorized_contact_phone)) {
            error.value =
                "Authorized contact phone must be in E.164 format (e.g. +14165550100).";
            return false;
        }
        if (!String(form.value.authorized_contact_email || "").trim()) {
            error.value = "Authorized contact email is required.";
            return false;
        }
    }
    if (currentStep.value === 4) {
        if (!form.value.professional_licenses.length) {
            error.value = "Please add at least one professional license.";
            return false;
        }
        const invalidRowIndex = form.value.professional_licenses.findIndex(
            (row) => {
                const issueDate = String(row.issue_date || "").trim();
                const expiryDate = String(row.expiry_date || "").trim();
                return (
                    !String(row.license_name || "").trim() ||
                    !String(row.license_number || "").trim() ||
                    !String(row.legal_business_name || "").trim() ||
                    !String(row.license_type || "").trim() ||
                    !row.issuing_country_id ||
                    !String(row.issuing_authority || "").trim() ||
                    !issueDate ||
                    !expiryDate ||
                    !String(row.license_status || "").trim() ||
                    !String(row.license_website || "").trim()
                );
            },
        );
        if (invalidRowIndex !== -1) {
            error.value = `Please complete all required fields in License row ${invalidRowIndex + 1}.`;
            return false;
        }
        const invalidDateRowIndex = form.value.professional_licenses.findIndex(
            (row) => {
                const issueDate = String(row.issue_date || "").trim();
                const expiryDate = String(row.expiry_date || "").trim();
                return issueDate && expiryDate && expiryDate < issueDate;
            },
        );
        if (invalidDateRowIndex !== -1) {
            error.value = `Expiry date must be on or after issue date in License row ${invalidDateRowIndex + 1}.`;
            return false;
        }
    }
    if (currentStep.value === 5) {
        if (!String(form.value.insurance_company_name || "").trim()) {
            error.value = "Insurance company name is required.";
            return false;
        }
        if (!String(form.value.insurance_policy_number || "").trim()) {
            error.value = "Policy number is required.";
            return false;
        }
        if (!String(form.value.insurance_legal_business_name || "").trim()) {
            error.value = "Legal business name on policy is required.";
            return false;
        }
        if (!String(form.value.insurance_coverage_type || "").trim()) {
            error.value = "Coverage type is required.";
            return false;
        }
        if (!String(form.value.insurance_policy_start_date || "").trim()) {
            error.value = "Policy start date is required.";
            return false;
        }
        if (!String(form.value.insurance_policy_end_date || "").trim()) {
            error.value = "Policy end date is required.";
            return false;
        }
        if (
            String(form.value.insurance_policy_end_date || "").trim() <
            String(form.value.insurance_policy_start_date || "").trim()
        ) {
            error.value = "Policy end date must be on or after start date.";
            return false;
        }
        if (!String(form.value.insurance_coverage_amount || "").trim()) {
            error.value = "Coverage amount is required.";
            return false;
        }
        if (!form.value.insurance_issuing_country_id) {
            error.value = "Issuing country is required.";
            return false;
        }
        if (!String(form.value.insurance_issuing_authority || "").trim()) {
            error.value = "Issued by (authority / insurer) is required.";
            return false;
        }
        if (!String(form.value.insurance_certificate_number || "").trim()) {
            error.value = "Insurance certificate number is required.";
            return false;
        }
        if (!String(form.value.insurance_issue_date || "").trim()) {
            error.value = "Issue date is required.";
            return false;
        }
        if (!String(form.value.insurance_certificate_path || "").trim()) {
            error.value = "Certificate upload is required.";
            return false;
        }
    }
    if (currentStep.value === 6) {
        if (!form.value.subscription_plan) {
            error.value = "Please select a service plan.";
            return false;
        }
        if (!form.value.billing_cycle) {
            error.value = "Billing cycle is required.";
            return false;
        }
    }
    if (currentStep.value === 7) {
        if (additionalUserLicenseCount.value < 1) {
            error.value = "Please add at least one user license.";
            return false;
        }
        const invalidRowIndex = form.value.user_licenses.findIndex((row) => {
            return (
                !String(row.full_name || "").trim() ||
                !String(row.phone_number || "").trim() ||
                !String(row.license_number || "").trim()
            );
        });
        if (invalidRowIndex !== -1) {
            error.value = `Please complete all required fields in user license row ${invalidRowIndex + 1}.`;
            return false;
        }
    }
    if (currentStep.value === 8) {
        if (!form.value.payment_schedule_start_date) {
            error.value = "Payment schedule start date is required.";
            return false;
        }
        if (!form.value.payment_schedule_ack) {
            error.value = "Please agree to the payment schedule to continue.";
            return false;
        }
    }
    if (currentStep.value === 9) {
        if (!form.value.primary_cardholder_name || !form.value.primary_card_number || !form.value.primary_card_expiry || !form.value.primary_card_cvv) {
            error.value = "Please complete all required primary card fields.";
            return false;
        }
        if (!form.value.payment_method_ack) {
            error.value = "Please confirm the payment authorization to continue.";
            return false;
        }
    }
    if (currentStep.value === 10) {
        const invalidContactIndex = form.value.setup_contacts.findIndex((contact) => {
            return !String(contact.department_name || "").trim()
                || !String(contact.contact_person || "").trim()
                || !String(contact.phone_number || "").trim()
                || !String(contact.email || "").trim()
                || !String(contact.position_title || "").trim();
        });
        if (invalidContactIndex !== -1) {
            error.value = `Please complete all contact fields in row ${invalidContactIndex + 1}.`;
            return false;
        }
    }
    if (currentStep.value === 11) {
        if (!form.value.security_username || !form.value.security_password || !form.value.security_password_confirm || !form.value.security_pin || !form.value.security_pin_confirm) {
            error.value = "Please complete all temporary login credentials.";
            return false;
        }
        if (form.value.security_password !== form.value.security_password_confirm) {
            error.value = "Temporary passwords do not match.";
            return false;
        }
        if (form.value.security_pin !== form.value.security_pin_confirm) {
            error.value = "Security PIN codes do not match.";
            return false;
        }
        if (!form.value.contact_primary_phone || !form.value.recovery_phone || !form.value.contact_business_email || !form.value.recovery_email) {
            error.value = "Please complete all account recovery fields.";
            return false;
        }
    }
    if (currentStep.value === 12) {
        if (!form.value.declaration_ack || !form.value.declaration_full_name || !form.value.declaration_position_title || !form.value.declaration_signed_at || !form.value.declaration_initials || !form.value.declaration_signature) {
            error.value = "Please complete and accept the Declaration & Agreement.";
            return false;
        }
        if (form.value.declaration_signature.trim() !== form.value.declaration_full_name.trim()) {
            error.value = "Electronic signature must match the full legal name.";
            return false;
        }
    }
    if (false && currentStep.value === 12) {
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
        if (!form.value.preview_confirm_ack || !form.value.preview_full_name || !form.value.preview_position_title || !form.value.preview_initials || !form.value.preview_signature) {
            error.value = "Please complete the final confirmation fields.";
            return false;
        }
        if (form.value.preview_signature.trim() !== form.value.preview_full_name.trim()) {
            error.value = "Electronic signature must match the full legal name.";
            return false;
        }
    }
    if (false && currentStep.value === 13) {
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
        payload.insurance_issuing_country_id =
            payload.insurance_issuing_country_id || null;
        if (Array.isArray(payload.facility_usage)) {
            payload.facility_usage = payload.facility_usage.map((row) => ({
                ...row,
                country_id: row.country_id || null,
            }));
        }
        if (Array.isArray(payload.professional_licenses)) {
            payload.professional_licenses = payload.professional_licenses.map(
                (row) => ({
                    ...row,
                    issuing_country_id: row.issuing_country_id || null,
                }),
            );
        }
        if (currentStep.value === 8) {
            payload.payment_schedule_items = paymentScheduleRows.value.map(
                (row) => ({
                    payment_number: row.payment_number,
                    service_period_start: row.service_period_start,
                    service_period_end: row.service_period_end,
                    auto_charge_date: row.auto_charge_date,
                    charging_date: row.charging_date,
                    amount: row.amount,
                }),
            );
            payload.payment_schedule_service_label =
                paymentScheduleServiceLabel.value;
            payload.payment_schedule_summary_note = `Auto charge begins on ${paymentScheduleRows.value[0]?.auto_charge_date || paymentScheduleStartDateLabel.value}.`;
            payload.payment_schedule_amount = paymentScheduleAmount.value;
        }
        if (currentStep.value === 14) {
            // Step 14 is a checkout preview only. Do not submit or process card data here.
            delete payload.primary_card_number;
            delete payload.primary_card_cvv;
            delete payload.backup_card_number;
            delete payload.backup_card_cvv;
        }
        delete payload.company_logo_preview;
        delete payload.captcha_token;
        if (currentStep.value !== 11) {
            delete payload.security_password;
            delete payload.security_password_confirm;
            delete payload.security_pin;
            delete payload.security_pin_confirm;
        }
        await client.post("/account-setup", {
            step: currentStep.value,
            data: payload,
        });
        if (currentStep.value === 11) {
            form.value.security_password = "";
            form.value.security_password_confirm = "";
            form.value.security_pin = "";
            form.value.security_pin_confirm = "";
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

const addProfessionalLicense = () => {
    form.value.professional_licenses.push(createProfessionalLicense());
};

const removeProfessionalLicense = (index) => {
    form.value.professional_licenses.splice(index, 1);
    if (!form.value.professional_licenses.length) {
        form.value.professional_licenses.push(createProfessionalLicense());
    }
};

const togglePrimaryService = (service) => {
    if (!Array.isArray(form.value.primary_services)) {
        form.value.primary_services = [];
    }

    if (form.value.primary_services.includes(service)) {
        form.value.primary_services = form.value.primary_services.filter(
            (item) => item !== service,
        );
        return;
    }

    if (form.value.primary_services.length >= 6) {
        return;
    }

    form.value.primary_services = [...form.value.primary_services, service];
};

const selectPlan = (plan) => {
    form.value.subscription_plan = plan;
    form.value.billing_cycle = "annually";
};

const syncUserLicenseRows = (requestedCount) => {
    const count = Math.max(0, Number(requestedCount || 0));
    const nextRows = Array.isArray(form.value.user_licenses)
        ? [...form.value.user_licenses]
        : [];

    while (nextRows.length < count) {
        nextRows.push(createUserLicenseEntry(nextRows.length));
    }

    if (nextRows.length > count) {
        nextRows.length = count;
    }

    form.value.user_licenses = nextRows.map((row, index) => ({
        full_name: row?.full_name || "",
        phone_number: row?.phone_number || "",
        license_number:
            row?.license_number || buildUserLicenseNumber(index),
        expiry_note: row?.expiry_note || "Announced after payment",
    }));
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

const addSetupContact = () => {
    form.value.setup_contacts.push(createSetupContact());
};

const removeSetupContact = (index) => {
    if (form.value.setup_contacts.length <= 1) return;
    form.value.setup_contacts.splice(index, 1);
};

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

watch(
    () => form.value.additional_user_licenses,
    (count) => {
        syncUserLicenseRows(count);
    },
    { immediate: true },
);

watch(currentStep, async (step) => {
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
