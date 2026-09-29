<template>
    <div
        class="min-h-screen bg-[linear-gradient(180deg,#2141ce_0%,#6d80ff_100%)] xl:h-screen xl:overflow-hidden"
    >
        <div
            class="grid min-h-screen grid-cols-1 xl:h-screen xl:grid-cols-[366px_minmax(0,1fr)]"
        >
            <aside
                class="relative overflow-hidden bg-[#b9c7e3] text-white"
                :style="{
                    backgroundImage: `linear-gradient(180deg, rgba(7,26,68,0.58), rgba(7,26,68,0.76)), url(${heroImage})`,
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                }"
            >
                <div
                    class="absolute inset-0 bg-[linear-gradient(180deg,rgba(11,29,70,0.03),rgba(7,26,68,0.24)),radial-gradient(circle_at_top_right,rgba(96,165,250,0.12),transparent_38%)]"
                ></div>
                <div
                    class="relative z-10 flex min-h-full flex-col gap-2.5 px-4 py-3 md:px-5"
                >
                    <div class="flex items-center gap-3">
                        <RouterLink
                            to="/"
                            class="inline-flex items-center gap-3 text-base font-bold text-white no-underline"
                            aria-label="Go to home"
                        >
                            <img
                                :src="logoWhite"
                                alt="Contractor.com logo"
                                class="h-[42px] w-[70px] object-contain"
                            />
                        </RouterLink>
                        <p class="text-[14px] font-semibold text-white/95">
                            Welcome to
                        </p>
                    </div>

                    <div>
                        <h4
                            class="mb-2 text-[26px] leading-[1.04] font-bold text-[#2792ff] md:text-[34px]"
                        >
                            Contractor.com
                        </h4>
                        <p class="text-[12px] leading-[1.42] text-slate-100/90">
                            The complete contractor business management platform
                            for estimates, quotations, jobs, customers,
                            vehicles, invoicing, and accounting.
                        </p>
                    </div>

                    <div class="mt-auto grid gap-.5">
                        <article
                            v-for="feature in featureCards"
                            :key="feature.title"
                            class="grid grid-cols-[36px_minmax(0,1fr)] gap-2.5"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-[linear-gradient(135deg,#215cf0,#3578ff)] text-[11px] font-extrabold text-white shadow-[0_10px_18px_rgba(45,99,229,0.28)]"
                            >
                                <svg
                                    v-if="feature.icon === 'document'"
                                    viewBox="0 0 24 24"
                                    class="h-4.5 w-4.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M7 3.75h7l4.25 4.25v12.25H7z" />
                                    <path d="M14 3.75V8h4.25" />
                                    <path d="M9.5 11.5h6" />
                                    <path d="M9.5 14.5h6" />
                                    <path d="M9.5 17.5h4" />
                                </svg>
                                <svg
                                    v-else-if="feature.icon === 'users'"
                                    viewBox="0 0 24 24"
                                    class="h-4.5 w-4.5"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M12 12.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Zm-6 7.25a6 6 0 0 1 12 0v.5H6v-.5Zm13.1-8.78a2.45 2.45 0 1 0 0-4.9 2.45 2.45 0 0 0 0 4.9Zm.53 1.33h-.95a5.88 5.88 0 0 1 3.32 5.29V20H19.5v-2.66a4.53 4.53 0 0 0-.87-2.7Z"
                                    />
                                </svg>
                                <svg
                                    v-else-if="feature.icon === 'briefcase'"
                                    viewBox="0 0 24 24"
                                    class="h-4.5 w-4.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"
                                    />
                                    <path d="M4.75 8.25h14.5v10.5H4.75z" />
                                    <path d="M4.75 12h14.5" />
                                    <path d="M10 12v1.25h4V12" />
                                </svg>
                                <svg
                                    v-else-if="feature.icon === 'truck'"
                                    viewBox="0 0 24 24"
                                    class="h-4.5 w-4.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M3.75 7.25h10.5v8.5H3.75z" />
                                    <path d="M14.25 10h3.25l2.75 2.75v3h-6z" />
                                    <circle cx="8" cy="17.5" r="1.75" />
                                    <circle cx="17.5" cy="17.5" r="1.75" />
                                </svg>
                                <svg
                                    v-else-if="feature.icon === 'building'"
                                    viewBox="0 0 24 24"
                                    class="h-4.5 w-4.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M5.75 4.75h9.5v14.5h-9.5z" />
                                    <path d="M15.25 8.25h3v11h-3z" />
                                    <path d="M8.5 8.25h1" />
                                    <path d="M11.5 8.25h1" />
                                    <path d="M8.5 11.25h1" />
                                    <path d="M11.5 11.25h1" />
                                    <path d="M8.5 14.25h1" />
                                    <path d="M11.5 14.25h1" />
                                </svg>
                                <svg
                                    v-else-if="feature.icon === 'shield'"
                                    viewBox="0 0 24 24"
                                    class="h-4.5 w-4.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M12 3.75 18.25 6v5.31c0 4.12-2.5 6.79-6.25 8.94-3.75-2.15-6.25-4.82-6.25-8.94V6L12 3.75Z"
                                    />
                                    <path d="m9.5 12.25 1.75 1.75 3.25-3.5" />
                                </svg>
                            </div>
                            <div>
                                <h6
                                    class="mb-1 text-[11px] font-bold text-white"
                                >
                                    {{ feature.title }}
                                </h6>
                                <p
                                    class="text-[11px] leading-[1.34] text-slate-200/90"
                                >
                                    {{ feature.text }}
                                </p>
                            </div>
                        </article>
                    </div>

                    <div
                        class="grid grid-cols-[36px_minmax(0,1fr)] gap-2.5 rounded-2xl border border-blue-400/30 bg-blue-950/20 p-3"
                    >
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-[linear-gradient(135deg,#215cf0,#3578ff)] text-[11px] font-extrabold text-white shadow-[0_10px_18px_rgba(45,99,229,0.28)]"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-4.5 w-4.5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M4.75 12a7.25 7.25 0 0 1 7.25-7.25h.25A7.25 7.25 0 0 1 19.5 12v4a2 2 0 0 1-2 2h-1.25v-5.25h3.25"
                                />
                                <path
                                    d="M4.5 12v4a2 2 0 0 0 2 2h1.25v-5.25H4.5"
                                />
                                <path
                                    d="M10 18.5h3.25a1.75 1.75 0 0 0 1.75-1.75V16"
                                />
                            </svg>
                        </div>
                        <div>
                            <strong
                                class="mb-1 block text-[13px] font-bold text-white"
                            >
                                Dedicated Contractor Support
                            </strong>
                            <p
                                class="text-[11px] leading-[1.34] text-slate-200/90"
                            >
                                Our support team is here to assist you every
                                step of the way.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <main
                class="px-2 py-1.5 md:px-3 md:py-2 xl:h-screen xl:overflow-hidden"
            >
                <section
                    class="flex h-full flex-col overflow-hidden rounded-[4px] bg-white shadow-[0_20px_50px_rgba(8,15,40,0.18)]"
                >
                    <div
                        class="min-h-0 flex-1 overflow-y-auto px-3 py-2.5 md:px-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4
                                    class="text-[30px] leading-none font-extrabold text-[#17235d] md:text-[38px]"
                                >
                                    Sign Up
                                </h4>
                                <p
                                    class="mt-.5 text-[13px] font-semibold text-slate-600"
                                >
                                    Create your contractor account
                                </p>
                            </div>
                            <div class="pt-2 text-[13px] text-slate-500">
                                <span>Already have an account?</span>
                                <RouterLink
                                    to="/login"
                                    class="ml-2 font-semibold text-blue-600 no-underline hover:text-blue-700"
                                >
                                    Log In
                                </RouterLink>
                            </div>
                        </div>

                        <div class="mt-0 overflow-x-auto">
                            <ol
                                class="flex min-w-[1080px] items-center"
                                aria-label="Registration steps"
                            >
                                <li
                                    v-for="(step, index) in compactSteps"
                                    :key="step.label"
                                    class="flex min-w-0 flex-1 items-center"
                                >
                                    <div
                                        class="flex shrink-0 items-center gap-2.5 pr-3"
                                    >
                                        <span
                                            :class="[
                                                'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border text-[13px] font-bold',
                                                index === 0
                                                    ? 'border-blue-600 bg-blue-600 text-white shadow-[0_0_0_2px_rgba(37,99,235,0.12)]'
                                                    : 'border-slate-300 bg-white text-[#17235d]',
                                            ]"
                                        >
                                            {{ step.number }}
                                        </span>
                                        <span
                                            :class="[
                                                'whitespace-nowrap text-[12px] font-semibold',
                                                index === 0
                                                    ? 'text-blue-600'
                                                    : 'text-slate-600',
                                            ]"
                                        >
                                            {{ step.label }}
                                        </span>
                                    </div>
                                    <span
                                        v-if="index < compactSteps.length - 1"
                                        class="mr-3 h-px min-w-[34px] flex-1 bg-slate-200"
                                    ></span>
                                </li>
                            </ol>
                        </div>

                        <div
                            v-if="summaryErrors.length"
                            class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-900"
                        >
                            <strong>Please fix the following:</strong>
                            <ul class="mt-1 list-disc pl-5">
                                <li
                                    v-for="(item, idx) in summaryErrors"
                                    :key="idx"
                                >
                                    {{ item }}
                                </li>
                            </ul>
                        </div>

                        <form
                            class="mt-2.5 grid gap-2"
                            @submit.prevent="onRegister"
                            novalidate
                        >
                            <div class="grid gap-2.5 xl:grid-cols-2">
                                <section
                                    class="rounded-xl border border-slate-200 p-2.5"
                                >
                                    <div
                                        class="mb-1.5 flex items-center gap-2 text-[#17235d]"
                                    >
                                        <span class="text-[18px]">A.</span>
                                        <h6 class="text-[16px] font-extrabold">
                                            Account Information
                                        </h6>
                                    </div>
                                    <div class="hidden grid gap-2">
                                        <label
                                            class="grid grid-cols-[120px_minmax(0,1fr)] items-center gap-2"
                                        >
                                            <span
                                                class="whitespace-nowrap text-[13px] font-semibold text-[#17235d]"
                                                >Account No.</span
                                            >
                                            <input
                                                v-model="form.account_number"
                                                :class="compactInputClass()"
                                                placeholder="Auto-generated"
                                            />
                                        </label>
                                        <label
                                            class="grid grid-cols-[120px_minmax(0,1fr)] items-center gap-2"
                                        >
                                            <span
                                                class="whitespace-nowrap text-[13px] font-semibold text-[#17235d]"
                                                >Your Legal Name
                                                <span class="text-red-500"
                                                    >*</span
                                                ></span
                                            >
                                            <div class="grid gap-1">
                                                <input
                                                    v-model="form.name"
                                                    :class="
                                                        compactInputClass(
                                                            errors.name,
                                                        )
                                                    "
                                                    placeholder="Enter your legal name"
                                                />
                                                <span
                                                    v-if="errors.name"
                                                    class="text-[11px] text-red-600"
                                                    >{{ errors.name[0] }}</span
                                                >
                                            </div>
                                        </label>
                                        <label
                                            class="grid grid-cols-[120px_minmax(0,1fr)] items-center gap-2"
                                        >
                                            <span
                                                class="whitespace-nowrap text-[13px] font-semibold text-[#17235d]"
                                                >Your Position
                                                <span class="text-red-500"
                                                    >*</span
                                                ></span
                                            >
                                            <div class="grid gap-1">
                                                <input
                                                    v-model="form.position"
                                                    :class="
                                                        compactInputClass(
                                                            errors.position,
                                                        )
                                                    "
                                                    placeholder="Select your position"
                                                />
                                                <span
                                                    v-if="errors.position"
                                                    class="text-[11px] text-red-600"
                                                    >{{
                                                        errors.position[0]
                                                    }}</span
                                                >
                                            </div>
                                        </label>
                                    </div>
                                    <div class="space-y-2">
                                        <!-- Account No -->
                                        <div class="flex items-center gap-3">
                                            <label
                                                class="w-32 shrink-0 text-[13px] font-semibold text-[#17235d]"
                                            >
                                                Account No.
                                            </label>

                                            <input
                                                v-model="form.account_number"
                                                :class="[
                                                    'flex-1',
                                                    compactInputClass(),
                                                ]"
                                                placeholder="Auto-generated"
                                            />
                                        </div>

                                        <!-- Legal Name -->
                                        <div class="flex items-start gap-3">
                                            <label
                                                class="w-32 shrink-0 pt-2 text-[13px] font-semibold text-[#17235d]"
                                            >
                                                Your Legal Name
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>

                                            <div class="flex-1">
                                                <input
                                                    v-model="form.name"
                                                    :class="[
                                                        'w-full',
                                                        compactInputClass(
                                                            errors.name,
                                                        ),
                                                    ]"
                                                    placeholder="Enter your legal name"
                                                />

                                                <span
                                                    v-if="errors.name"
                                                    class="mt-1 block text-[11px] text-red-600"
                                                >
                                                    {{ errors.name[0] }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Position -->
                                        <div class="flex items-center gap-3">
                                            <label
                                                class="w-32 shrink-0 text-[13px] font-semibold text-[#17235d]"
                                            >
                                                Your Position
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>

                                            <div class="flex-1">
                                                <input
                                                    v-model="form.position"
                                                    :class="[
                                                        'w-full',
                                                        compactInputClass(
                                                            errors.position,
                                                        ),
                                                    ]"
                                                    placeholder="Select your position"
                                                />

                                                <span
                                                    v-if="errors.position"
                                                    class="mt-1 block text-[11px] text-red-600"
                                                >
                                                    {{ errors.position[0] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section
                                    class="rounded-xl border border-slate-200 p-2.5"
                                >
                                    <div
                                        class="mb-1.5 flex items-center gap-2 text-[#17235d]"
                                    >
                                        <span class="text-[18px]">B.</span>
                                        <h6 class="text-[16px] font-extrabold">
                                            Company Information
                                        </h6>
                                    </div>
                                    <div class="space-y-2">
                                        <!-- Company Name -->
                                        <div class="flex items-start gap-3">
                                            <label
                                                class="w-32 shrink-0 pt-2 text-[13px] font-semibold text-[#17235d]"
                                            >
                                                Company Name
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>

                                            <div class="flex-1">
                                                <input
                                                    v-model="form.company"
                                                    :class="[
                                                        'w-full',
                                                        compactInputClass(
                                                            errors.company,
                                                        ),
                                                    ]"
                                                    placeholder="Enter company name"
                                                />

                                                <span
                                                    v-if="errors.company"
                                                    class="mt-1 block text-[11px] text-red-600"
                                                >
                                                    {{ errors.company[0] }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Business Email -->
                                        <div class="flex items-start gap-3">
                                            <label
                                                class="w-32 shrink-0 pt-2 text-[13px] font-semibold text-[#17235d]"
                                            >
                                                Business Email
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>

                                            <div class="flex-1">
                                                <input
                                                    v-model="form.email"
                                                    :class="[
                                                        'w-full',
                                                        compactInputClass(
                                                            errors.email,
                                                        ),
                                                    ]"
                                                    placeholder="Enter business email"
                                                />

                                                <span
                                                    v-if="errors.email"
                                                    class="mt-1 block text-[11px] text-red-600"
                                                >
                                                    {{ errors.email[0] }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Business Phone -->
                                        <div class="flex items-start gap-3">
                                            <label
                                                class="w-32 shrink-0 pt-2 text-[13px] font-semibold text-[#17235d]"
                                            >
                                                Business Phone
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>

                                            <div class="flex-1">
                                                <input
                                                    v-model="form.phoneNo"
                                                    :class="[
                                                        'w-full',
                                                        compactInputClass(
                                                            errors.phoneNo,
                                                        ),
                                                    ]"
                                                    placeholder="Enter business phone"
                                                />

                                                <span
                                                    v-if="errors.phoneNo"
                                                    class="mt-1 block text-[11px] text-red-600"
                                                >
                                                    {{ errors.phoneNo[0] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <section
                                class="rounded-xl border border-slate-200 p-3"
                            >
                                <div
                                    class="mb-2 flex items-center gap-2 text-[#17235d]"
                                >
                                    <span class="text-[18px]">C.</span>
                                    <h6 class="text-[16px] font-extrabold">
                                        Business Address
                                    </h6>
                                </div>
                                <div class="grid gap-2.5 md:grid-cols-3">
                                    <label class="grid gap-1 md:col-span-1">
                                        <span
                                            class="text-[13px] font-semibold text-[#17235d]"
                                            >Company Address
                                            <span class="text-red-500"
                                                >*</span
                                            ></span
                                        >
                                        <div class="grid gap-1">
                                            <input
                                                v-model="form.address"
                                                :class="
                                                    compactInputClass(
                                                        errors.address,
                                                    )
                                                "
                                                placeholder="Enter company address"
                                            />
                                            <span
                                                v-if="errors.address"
                                                class="text-[11px] text-red-600"
                                                >{{ errors.address[0] }}</span
                                            >
                                        </div>
                                    </label>
                                    <label class="grid gap-1">
                                        <span
                                            class="text-[13px] font-semibold text-[#17235d]"
                                            >Country
                                            <span class="text-red-500"
                                                >*</span
                                            ></span
                                        >
                                        <div class="grid gap-1">
                                            <select
                                                v-model.number="form.country_id"
                                                :class="
                                                    compactInputClass(
                                                        errors.country_id,
                                                    )
                                                "
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
                                            <span
                                                v-if="errors.country_id"
                                                class="text-[11px] text-red-600"
                                                >{{
                                                    errors.country_id[0]
                                                }}</span
                                            >
                                        </div>
                                    </label>
                                    <label class="grid gap-1">
                                        <span
                                            class="text-[13px] font-semibold text-[#17235d]"
                                            >State / Province
                                            <span class="text-red-500"
                                                >*</span
                                            ></span
                                        >
                                        <div class="grid gap-1">
                                            <input
                                                v-model="form.state"
                                                :class="
                                                    compactInputClass(
                                                        errors.state,
                                                    )
                                                "
                                                placeholder="Select state / province"
                                            />
                                            <span
                                                v-if="errors.state"
                                                class="text-[11px] text-red-600"
                                                >{{ errors.state[0] }}</span
                                            >
                                        </div>
                                    </label>
                                    <label class="grid gap-1">
                                        <span
                                            class="text-[13px] font-semibold text-[#17235d]"
                                            >City
                                            <span class="text-red-500"
                                                >*</span
                                            ></span
                                        >
                                        <div class="grid gap-1">
                                            <input
                                                v-model="form.city"
                                                :class="
                                                    compactInputClass(
                                                        errors.city,
                                                    )
                                                "
                                                placeholder="Enter city"
                                            />
                                            <span
                                                v-if="errors.city"
                                                class="text-[11px] text-red-600"
                                                >{{ errors.city[0] }}</span
                                            >
                                        </div>
                                    </label>
                                    <label class="grid gap-1">
                                        <span
                                            class="text-[13px] font-semibold text-[#17235d]"
                                            >Zip / Postal Code
                                            <span class="text-red-500"
                                                >*</span
                                            ></span
                                        >
                                        <div class="grid gap-1">
                                            <input
                                                v-model="form.zip"
                                                :class="
                                                    compactInputClass(
                                                        errors.zip,
                                                    )
                                                "
                                                placeholder="Enter zip / postal code"
                                            />
                                            <span
                                                v-if="errors.zip"
                                                class="text-[11px] text-red-600"
                                                >{{ errors.zip[0] }}</span
                                            >
                                        </div>
                                    </label>
                                </div>
                            </section>

                            <section
                                class="rounded-xl border border-slate-200 p-3"
                            >
                                <div
                                    class="mb-2 flex items-center gap-2 text-[#17235d]"
                                >
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-[16px] font-bold text-white"
                                        >2</span
                                    >
                                    <h6 class="text-[16px] font-extrabold">
                                        Verification
                                    </h6>
                                </div>

                                <div class="grid gap-2.5 xl:grid-cols-2">
                                    <div
                                        class="rounded-xl border border-slate-200 p-2.5"
                                    >
                                        <h5
                                            class="text-[13px] font-extrabold text-blue-700"
                                        >
                                            Email Verification
                                        </h5>
                                        <p
                                            class="mb-1.5 text-[10px] text-slate-500"
                                        >
                                            We'll send a verification code to
                                            your business email.
                                        </p>
                                        <div
                                            class="grid gap-2 md:grid-cols-[minmax(0,1fr)_96px]"
                                        >
                                            <input
                                                v-model="
                                                    verificationCodes.email
                                                "
                                                :class="
                                                    compactInputClass(
                                                        errors.verification_code &&
                                                            activeVerificationMethod ===
                                                                'email',
                                                    )
                                                "
                                                placeholder="Enter email verification code"
                                            />
                                            <button
                                                type="button"
                                                class="whitespace-nowrap rounded-lg border border-blue-500 bg-white px-2.5 py-2 text-[12px] font-semibold text-blue-600"
                                                :disabled="
                                                    verificationSending.email
                                                "
                                                @click="
                                                    sendVerificationCode(
                                                        'email',
                                                    )
                                                "
                                            >
                                                {{
                                                    verificationSending.email
                                                        ? "Sending..."
                                                        : "Send Code"
                                                }}
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            class="mt-1.5 text-[11px] font-semibold text-blue-600"
                                            :disabled="
                                                verificationSending.email
                                            "
                                            @click="
                                                sendVerificationCode('email')
                                            "
                                        >
                                            {{
                                                verificationSending.email
                                                    ? "Sending..."
                                                    : "Resend Code"
                                            }}
                                        </button>
                                        <p
                                            v-if="verificationFeedback.email"
                                            class="mt-1 text-[11px] text-slate-600"
                                        >
                                            {{ verificationFeedback.email }}
                                        </p>
                                        <span
                                            v-if="
                                                errors.verification_code &&
                                                activeVerificationMethod ===
                                                    'email'
                                            "
                                            class="text-[11px] text-red-600"
                                        >
                                            {{ errors.verification_code[0] }}
                                        </span>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 p-2.5"
                                    >
                                        <h5
                                            class="text-[13px] font-extrabold text-blue-700"
                                        >
                                            Phone Verification
                                        </h5>
                                        <p
                                            class="mb-1.5 text-[10px] text-slate-500"
                                        >
                                            We'll send a verification code to
                                            your business phone.
                                        </p>
                                        <div
                                            class="grid gap-2 md:grid-cols-[minmax(0,1fr)_96px]"
                                        >
                                            <input
                                                v-model="
                                                    verificationCodes.phone
                                                "
                                                :class="
                                                    compactInputClass(
                                                        errors.verification_code &&
                                                            activeVerificationMethod ===
                                                                'sms',
                                                    )
                                                "
                                                placeholder="Enter phone verification code"
                                            />
                                            <button
                                                type="button"
                                                class="whitespace-nowrap rounded-lg border border-blue-500 bg-white px-2.5 py-2 text-[12px] font-semibold text-blue-600"
                                                :disabled="
                                                    verificationSending.phone
                                                "
                                                @click="
                                                    sendVerificationCode('sms')
                                                "
                                            >
                                                {{
                                                    verificationSending.phone
                                                        ? "Sending..."
                                                        : "Send Code"
                                                }}
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            class="mt-1.5 text-[11px] font-semibold text-blue-600"
                                            :disabled="
                                                verificationSending.phone
                                            "
                                            @click="sendVerificationCode('sms')"
                                        >
                                            {{
                                                verificationSending.phone
                                                    ? "Sending..."
                                                    : "Resend Code"
                                            }}
                                        </button>
                                        <p
                                            v-if="verificationFeedback.phone"
                                            class="mt-1 text-[11px] text-slate-600"
                                        >
                                            {{ verificationFeedback.phone }}
                                        </p>
                                        <span
                                            v-if="
                                                errors.verification_code &&
                                                activeVerificationMethod ===
                                                    'sms'
                                            "
                                            class="text-[11px] text-red-600"
                                        >
                                            {{ errors.verification_code[0] }}
                                        </span>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 p-2.5"
                                    >
                                        <h5
                                            class="mb-1.5 text-[13px] font-extrabold text-[#17235d]"
                                        >
                                            Security & Access
                                        </h5>
                                        <div class="grid gap-2">
                                            <div class="grid gap-1">
                                                <input
                                                    v-model="form.password"
                                                    type="password"
                                                    :class="
                                                        compactInputClass(
                                                            errors.password,
                                                        )
                                                    "
                                                    placeholder="Enter password"
                                                />
                                                <span
                                                    v-if="errors.password"
                                                    class="text-[11px] text-red-600"
                                                >
                                                    {{ errors.password[0] }}
                                                </span>
                                            </div>
                                            <div class="grid gap-1">
                                                <input
                                                    v-model="
                                                        form.password_confirmation
                                                    "
                                                    type="password"
                                                    :class="
                                                        compactInputClass(
                                                            errors.password_confirmation,
                                                        )
                                                    "
                                                    placeholder="Confirm password"
                                                />
                                                <span
                                                    v-if="
                                                        errors.password_confirmation
                                                    "
                                                    class="text-[11px] text-red-600"
                                                >
                                                    {{
                                                        errors
                                                            .password_confirmation[0]
                                                    }}
                                                </span>
                                            </div>
                                            <p
                                                class="text-[10px] text-slate-500"
                                            >
                                                Password must be at least 8
                                                characters.
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 p-2.5"
                                    >
                                        <h5
                                            class="mb-1.5 text-[13px] font-extrabold text-[#17235d]"
                                        >
                                            Security Check
                                        </h5>
                                        <div class="grid gap-1">
                                            <ImageCaptcha
                                                ref="captchaRef"
                                                @change="onCaptchaChange"
                                            />
                                            <span
                                                v-if="errors.captcha_value"
                                                class="text-[11px] text-red-600"
                                            >
                                                {{ errors.captcha_value[0] }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 p-2.5"
                                    >
                                        <h5
                                            class="mb-1.5 text-[13px] font-extrabold text-[#17235d]"
                                        >
                                            Agreements
                                        </h5>
                                        <div class="grid gap-1.5">
                                            <label
                                                class="inline-flex items-start gap-2 text-[12px] text-slate-700"
                                            >
                                                <input
                                                    id="agreeTerms"
                                                    v-model="form.agree"
                                                    type="checkbox"
                                                    class="mt-0.5"
                                                />
                                                <span>
                                                    I agree to the
                                                    <a
                                                        :href="termsUrl"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="font-medium text-blue-600 hover:text-blue-700"
                                                    >
                                                        Terms of Service
                                                    </a>
                                                    <span class="text-red-500">
                                                        *</span
                                                    >
                                                </span>
                                            </label>
                                            <label
                                                class="inline-flex items-start gap-2 text-[12px] text-slate-700"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="mt-0.5"
                                                    :checked="form.agree"
                                                    @change="
                                                        form.agree = !form.agree
                                                    "
                                                />
                                                <span>
                                                    I have read the
                                                    <a
                                                        :href="termsUrl"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="font-medium text-blue-600 hover:text-blue-700"
                                                    >
                                                        Privacy Policy
                                                    </a>
                                                    <span class="text-red-500">
                                                        *</span
                                                    >
                                                </span>
                                            </label>
                                            <span
                                                v-if="errors.agree"
                                                class="text-[11px] text-red-600"
                                            >
                                                {{ errors.agree[0] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <div
                                v-if="error"
                                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] text-red-900"
                            >
                                {{ error }}
                            </div>

                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <button
                                    type="button"
                                    class="min-w-[110px] rounded-lg border border-blue-500 bg-white px-3 py-2 text-[13px] font-semibold text-blue-600"
                                >
                                    Back
                                </button>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="submit"
                                        class="min-w-[100px] rounded-lg border border-blue-500 bg-white px-3 py-2 text-[13px] font-semibold text-blue-600 disabled:opacity-60"
                                        :disabled="loading"
                                    >
                                        {{
                                            loading
                                                ? t("common.loading")
                                                : "Submit"
                                        }}
                                    </button>
                                    <button
                                        type="submit"
                                        class="min-w-[155px] rounded-lg bg-[linear-gradient(90deg,#1742ef,#1235c8)] px-3 py-2 text-[13px] font-semibold text-white disabled:opacity-60"
                                        :disabled="loading"
                                    >
                                        {{
                                            loading
                                                ? "Creating..."
                                                : submitLabel
                                        }}
                                    </button>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-green-100 bg-green-50 px-4 py-3 text-[13px] text-green-700"
                            >
                                Your account will be created after your signup
                                verification is completed on this page.
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

<script setup>
import logoWhite from "../assets/logo-white.png";
import signupImage from "../assets/signup.png";
import { reactive, ref, computed, onMounted, watch } from "vue";
import { useRouter, useRoute, RouterLink } from "vue-router";
import { useI18n } from "vue-i18n";
import {
    register,
    requestRegisterVerificationCode,
    saveToken,
} from "../api/auth";
import { normalizeApiError } from "../api/client";
import { setFlash } from "../store/flash";
import { FLASH } from "../config/messages";
import { setUser } from "../store/auth";
import ImageCaptcha from "../components/ImageCaptcha.vue";
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
        icon: "document",
        title: "Estimates & Quotations",
        text: "Create professional estimates and quotations quickly with accurate pricing and detailed breakdowns.",
    },
    {
        icon: "users",
        title: "Customer Management",
        text: "Manage customers, contacts, project history, communication, and follow-ups.",
    },
    {
        icon: "briefcase",
        title: "Job & Project Management",
        text: "Track jobs, schedules, work orders, project progress, and team assignments.",
    },
    {
        icon: "truck",
        title: "Vehicle Management",
        text: "Monitor vehicles, maintenance, mileage, fuel expenses, insurance, and drivers.",
    },
    {
        icon: "building",
        title: "Accounting & Invoicing",
        text: "Manage invoices, payments, expenses, payroll, taxes, and financial reports.",
    },
    {
        icon: "shield",
        title: "Secure Cloud Platform",
        text: "Your data is protected with secure cloud storage, encrypted access, and verification.",
    },
];

const compactSteps = [
    { number: 1, label: "Account & Company" },
    { number: 2, label: "Verification" },
    { number: 3, label: "Final Step" },
    { number: 4, label: "Create Account" },
    { number: 5, label: "Login" },
];

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
const verificationCodes = reactive({
    email: "",
    phone: "",
});
const verificationSending = reactive({
    email: false,
    phone: false,
});
const verificationFeedback = reactive({
    email: "",
    phone: "",
});
const verificationSent = reactive({
    emailTarget: "",
    phoneTarget: "",
    via: "",
});
const captchaKey = ref("");
const captchaValue = ref("");
const countries = ref([]);

const hasAccountNumber = computed(
    () => String(form.account_number || "").trim().length > 0,
);

const activeVerificationMethod = computed(() => {
    const emailCode = String(verificationCodes.email || "").trim();
    const phoneCode = String(verificationCodes.phone || "").trim();

    if (emailCode && !phoneCode) return "email";
    if (phoneCode && !emailCode) return "sms";

    return form.verifyVia;
});

const submitLabel = computed(() => {
    if (hasAccountNumber.value) return "Complete Sign Up";
    return "Create Account";
});

const statusItems = computed(() => [
    {
        icon: "1",
        label: "Account Credentials",
        status: form.name && form.company ? "In progress" : "Not started",
    },
    {
        icon: "2",
        label: "Verification Method",
        status:
            activeVerificationMethod.value === "sms"
                ? "Phone selected"
                : "Email selected",
    },
    {
        icon: "3",
        label: "Create Account",
        status: hasAccountNumber.value ? "Pending review" : "Ready",
    },
    {
        icon: "4",
        label: "Access Granted",
        status: loading.value ? "Processing" : "Waiting",
    },
]);

const securityItems = [
    "SSL Encrypted Connection",
    "Multi-Factor Verification",
    "Secure Cloud Access",
    "Session Protected",
];

const compactInputClass = (hasError = false) => [
    "w-full rounded-lg border bg-white px-3 py-2 text-[13px] text-slate-700 outline-none transition placeholder:text-slate-400 focus:ring-4",
    hasError
        ? "border-red-300 focus:border-red-400 focus:ring-red-100"
        : "border-slate-300 focus:border-blue-500 focus:ring-blue-100",
];

const compactRadioCardClass = (selected) => [
    "grid cursor-pointer grid-cols-[16px_minmax(0,1fr)] items-start gap-2 rounded-xl border bg-white p-3 transition",
    selected
        ? "border-blue-300 bg-blue-50/50 shadow-[0_10px_18px_rgba(37,99,235,0.06)]"
        : "border-slate-200 hover:border-blue-300 hover:bg-blue-50/40",
];

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

watch(
    () => form.verifyVia,
    () => {
        errors.value.verification_code = null;
        error.value = "";
    },
);

watch(
    () =>
        String(form.email || "")
            .trim()
            .toLowerCase(),
    (value, previous) => {
        if (previous !== undefined && value !== previous) {
            verificationSent.emailTarget = "";
            verificationFeedback.email = "";
        }
    },
);

watch(
    () => normalizePhoneNumber(form.phoneNo),
    (value, previous) => {
        if (previous !== undefined && value !== previous) {
            verificationSent.phoneTarget = "";
            verificationFeedback.phone = "";
        }
    },
);

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

const resolveVerificationSubmission = () => {
    const emailCode = String(verificationCodes.email || "").trim();
    const phoneCode = String(verificationCodes.phone || "").trim();

    if (emailCode && phoneCode) {
        return {
            method: null,
            code: "",
            error: "Use only one verification code: email or phone.",
        };
    }

    if (phoneCode) {
        const normalizedPhone = normalizePhoneNumber(form.phoneNo);
        if (!verificationSent.phoneTarget) {
            return {
                method: "sms",
                code: phoneCode,
                error: "Please send the phone verification code first.",
            };
        }
        if (verificationSent.phoneTarget !== normalizedPhone) {
            return {
                method: "sms",
                code: phoneCode,
                error: "Phone number changed after sending the verification code. Please send a new phone code.",
            };
        }
        return {
            method: "sms",
            code: phoneCode,
            error: null,
        };
    }

    if (emailCode) {
        const normalizedEmail = String(form.email || "")
            .trim()
            .toLowerCase();
        if (!verificationSent.emailTarget) {
            return {
                method: "email",
                code: emailCode,
                error: "Please send the email verification code first.",
            };
        }
        if (verificationSent.emailTarget !== normalizedEmail) {
            return {
                method: "email",
                code: emailCode,
                error: "Email changed after sending the verification code. Please send a new email code.",
            };
        }
        return {
            method: "email",
            code: emailCode,
            error: null,
        };
    }

    const method = form.verifyVia === "sms" ? "sms" : "email";
    return {
        method,
        code: "",
        error: null,
    };
};

const buildVerificationPayload = () => {
    const verificationSubmission = resolveVerificationSubmission();
    const verifyVia =
        verificationSubmission.method || form.verifyVia || "email";
    const verificationCode =
        verifyVia === "sms"
            ? String(verificationCodes.phone || "").trim()
            : String(verificationCodes.email || "").trim();

    return {
        verifyVia,
        verification_code: verificationCode,
        verificationSubmission,
    };
};

const validateRegisterForm = () => {
    error.value = "";
    errors.value = {};
    const {
        verifyVia: selectedVerificationMethod,
        verification_code: selectedVerificationCode,
        verificationSubmission,
    } = buildVerificationPayload();
    if (!form.name) errors.value.name = ["Full name is required."];
    if (!form.position) errors.value.position = ["Position is required."];
    if (!form.company) errors.value.company = ["Company name is required."];
    if (!form.address) errors.value.address = ["Company address is required."];
    if (!form.state) errors.value.state = ["State / Province is required."];
    if (!form.city) errors.value.city = ["City is required."];
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
    if (verificationSubmission.error) {
        errors.value.verification_code = [verificationSubmission.error];
    } else if (!selectedVerificationCode) {
        errors.value.verification_code = [
            selectedVerificationMethod === "sms"
                ? "Enter the phone verification code before submitting."
                : "Enter the email verification code before submitting.",
        ];
    } else if (!/^\d{4,6}$/.test(selectedVerificationCode)) {
        errors.value.verification_code = [
            "Verification code must be 4 to 6 digits.",
        ];
    }
    return Object.keys(errors.value).length === 0;
};

const submitRegistration = async (verifyVia = form.verifyVia) => {
    persistSignupIntent();
    loading.value = true;
    try {
        const { verifyVia: resolvedVerifyVia, verification_code } =
            buildVerificationPayload();
        const finalVerificationCode = String(verification_code || "").trim();
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
            verifyVia: resolvedVerifyVia || verifyVia,
            verification_code: finalVerificationCode,
            plan: form.plan,
            captcha_key: captchaKey.value,
            captcha_value: captchaValue.value,
        };
        const res = await register(payload);
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

const validateVerificationTarget = (via) => {
    verificationFeedback[via === "sms" ? "phone" : "email"] = "";

    if (via === "sms") {
        form.phoneNo = normalizePhoneNumber(form.phoneNo);
        if (!form.phoneNo) {
            errors.value.phoneNo = ["Phone number is required."];
            return false;
        }
        if (!isE164PhoneNumber(form.phoneNo)) {
            errors.value.phoneNo = [
                "Phone number must be in E.164 format (e.g. +14165550100).",
            ];
            return false;
        }
        errors.value.phoneNo = null;
        return true;
    }

    if (!form.email) {
        errors.value.email = ["Email is required."];
        return false;
    }

    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);
    if (!emailOk) {
        errors.value.email = ["Please enter a valid email address."];
        return false;
    }

    errors.value.email = null;
    return true;
};

const sendVerificationCode = async (via) => {
    const key = via === "sms" ? "phone" : "email";
    if (!validateVerificationTarget(via)) return;

    verificationSending[key] = true;
    verificationFeedback[key] = "";
    try {
        const res = await requestRegisterVerificationCode({
            verifyVia: via,
            email: form.email || undefined,
            phoneNo: via === "sms" ? form.phoneNo : undefined,
        });
        if (res?.data?.verify_via) {
            form.verifyVia = res.data.verify_via;
        }
        const deliveredVia = res?.data?.verify_via || via;
        verificationSent.via = deliveredVia;
        if (deliveredVia === "sms") {
            verificationSent.phoneTarget = normalizePhoneNumber(form.phoneNo);
        } else {
            verificationSent.emailTarget = String(form.email || "")
                .trim()
                .toLowerCase();
        }
        errors.value.verification_code = null;
        verificationFeedback[key] =
            res?.message ||
            (via === "sms"
                ? "Verification code sent to your phone."
                : "Verification code sent to your email.");
    } catch (e) {
        const parsed = normalizeApiError(e);
        errors.value = {
            ...errors.value,
            ...(parsed.errors || {}),
        };
        verificationFeedback[key] =
            parsed.message || "Unable to send verification code.";
    } finally {
        verificationSending[key] = false;
    }
};

const onRegister = async () => {
    if (!validateRegisterForm()) return;
    const { verifyVia } = buildVerificationPayload();
    await submitRegistration(verifyVia);
};

const onCaptchaChange = (payload) => {
    captchaKey.value = payload.captcha_key;
    captchaValue.value = payload.captcha_value;
    errors.value.captcha_value = null;
};
</script>
