<template>
  <div class="pm-dashboard-layout" :class="{ 'pm-sidebar-hidden': sidebarHidden }">
    <AppSidebar :isOpen="sidebarOpen" @close="closeSidebar" />
    <div class="pm-dashboard-main">
      <div class="pm-dashboard-topbar">
        <button class="pm-icon-btn pm-menu-btn" type="button" aria-label="Open menu" @click="toggleSidebar">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16" />
          </svg>
        </button>
        <button class="pm-icon-btn pm-hide-btn" type="button" aria-label="Toggle sidebar" @click="toggleSidebarHidden">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 6 3 12l6 6M21 12H4" />
          </svg>
        </button>
        <div class="pm-topbar-search">
          <input v-model="searchQuery" class="form-control" placeholder="Search parcels, recipients, tracking..." />
          <button
            v-if="searchQuery"
            class="pm-clear-btn"
            type="button"
            aria-label="Clear search"
            @click="searchQuery = ''"
          >
            &times;
          </button>
        </div>
        <div class="pm-topbar-actions">
          <button class="pm-icon-btn" type="button" aria-label="Notifications" @click="goNotifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
            <span class="pm-topbar-badge"></span>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings" @click="goSettings">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.22 7.22 0 0 0-1.63-.94l-.38-2.65A.5.5 0 0 0 13.78 1h-3.56a.5.5 0 0 0-.49.41l-.38 2.65a7.22 7.22 0 0 0-1.63.94l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64L4.86 11.06a7.43 7.43 0 0 0-.05.94 7.43 7.43 0 0 0 .05.94L2.75 14.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.49-1c.5.38 1.05.7 1.63.94l.38 2.65a.5.5 0 0 0 .49.41h3.56a.5.5 0 0 0 .49-.41l.38-2.65c.58-.24 1.13-.56 1.63-.94l2.49 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.66ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
            </svg>
          </button>
        </div>
        <div class="pm-topbar-user" @click="toggleUserMenu" ref="userMenuRef">
          <div class="pm-topbar-avatar">{{ userInitials }}</div>
          <span class="pm-topbar-name">{{ userName }}</span>
          <span class="pm-topbar-pill">Admin</span>
          <button class="pm-icon-btn pm-chevron-btn" type="button" aria-label="User menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="m7 10 5 5 5-5H7Z" />
            </svg>
          </button>
          <div v-if="userMenuOpen" class="pm-user-menu">
            <button class="pm-user-item" type="button" @click="goProfile">Profile</button>
            <button class="pm-user-item" type="button" @click="goAccount">Account</button>
            <button class="pm-user-item danger" type="button" @click="logout">Log out</button>
          </div>
        </div>
      </div>

      <section class="pm-dashboard-content">
        <div class="container pm-ops-page pm-cal-v2-page">
          <div class="pm-page-head">
            <div>
              <h2>Calendar</h2>
              <div class="pm-page-subtitle">This module is currently under development.</div>
            </div>
            <div class="pm-page-actions">
              <button v-if="canCreateCalendar" class="btn btn-primary" type="button" @click="prepareNew">+ New Event</button>
              <button v-if="canImportCalendar" class="btn btn-outline-secondary" type="button" @click="triggerImport">Import</button>
              <button v-if="canExportCalendar" class="btn btn-outline-primary" type="button" @click="exportCalendar">Export</button>
              <input ref="importInput" type="file" accept=".ics,text/calendar" class="d-none" @change="handleImport" />
            </div>
          </div>

          <div class="pm-cal-v2-top">
            <div class="pm-card pm-ops-card pm-cal-v2-board">
              <div class="pm-cal-v2-board-head">
                <h3>{{ monthLabel }}</h3>
                <div class="pm-cal-v2-nav">
                  <button class="btn btn-outline-primary btn-sm" type="button" @click="goToday">Today</button>
                  <button class="pm-icon-btn" type="button" aria-label="Previous month" @click="shiftMonth(-1)">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 6-6 6 6 6" /></svg>
                  </button>
                  <button class="pm-icon-btn" type="button" aria-label="Next month" @click="shiftMonth(1)">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6" /></svg>
                  </button>
                </div>
              </div>

              <div class="pm-cal-v2-weekdays">
                <div v-for="day in weekDays" :key="day" class="pm-cal-v2-weekday">{{ day }}</div>
              </div>
              <div class="pm-cal-v2-grid">
                <button
                  v-for="cell in calendarCells"
                  :key="cell.key"
                  type="button"
                  class="pm-cal-v2-cell"
                  :class="{
                    'is-today': cell.isToday,
                    'is-outside': cell.isOutside,
                    'is-selected': isSameDate(cell.date, selectedDate),
                  }"
                  @click="selectDate(cell.date)"
                >
                  <span class="pm-cal-v2-date">{{ cell.date.getDate() }}</span>
                  <span
                    v-if="cell.events.length"
                    class="pm-cal-v2-count"
                    :class="`is-${calendarCellTone(cell.events.length)}`"
                    role="button"
                    tabindex="0"
                    @click.stop="openDayEvents(cell)"
                    @keydown.enter.prevent="openDayEvents(cell)"
                    @keydown.space.prevent="openDayEvents(cell)"
                  >
                    {{ formatCellEventCount(cell.events.length) }}
                  </span>
                </button>
              </div>
            </div>

            <aside class="pm-card pm-ops-card pm-cal-v2-upcoming">
              <div class="pm-cal-v2-upcoming-head">Upcoming Events</div>
              <div class="pm-cal-v2-upcoming-list">
                <div v-if="upcomingEvents.length === 0" class="pm-cal-v2-empty">No upcoming events scheduled.</div>
                <button
                  v-for="item in upcomingEvents"
                  :key="item.id + '-upcoming'"
                  type="button"
                  class="pm-cal-v2-upcoming-item"
                  @click="openEventDetail(item)"
                >
                  <div class="pm-cal-v2-upcoming-top">
                    <strong>{{ item.title }}</strong>
                    <span class="pm-cal-v2-priority" :class="`is-${priorityClassForEvent(item)}`">
                      {{ priorityLabelForEvent(item) }}
                    </span>
                  </div>
                  <p>{{ item.description || 'No description added yet.' }}</p>
                  <div class="pm-cal-v2-upcoming-meta">
                    {{ formatShortDate(new Date(item.start_at)) }}
                    <span>{{ item.all_day ? 'All day' : formatTimeRange(item) }}</span>
                  </div>
                </button>
              </div>
              <button class="btn btn-primary w-100" type="button" @click="openAllEvents">View All Events</button>
            </aside>
          </div>

          <div class="pm-card pm-ops-card pm-cal-v2-form-card">
            <div class="pm-cal-v2-form-head">+ New Event</div>
            <form class="pm-cal-v2-form" @submit.prevent="saveEvent">
              <div class="pm-cal-v2-grid-2">
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Event No.</label>
                  <input class="form-control" type="text" :value="eventNoDisplay" readonly />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Location</label>
                  <input v-model="form.location" class="form-control" type="text" placeholder="Enter location" />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Title <span class="pm-required">*</span></label>
                  <input v-model="form.title" class="form-control" type="text" placeholder="Enter event title" required />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Location on Map</label>
                  <input v-model="form.location_map" class="form-control" type="text" placeholder="Map coordinates" />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Expiry Date</label>
                  <input v-model="form.end_date" class="form-control" type="date" />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Event Details</label>
                  <input v-model="form.description" class="form-control" type="text" placeholder="Optional event details" />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Added By</label>
                  <input class="form-control" type="text" :value="form.added_by_label || currentUserLabel" readonly />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Required Action</label>
                  <input v-model="form.required_action" class="form-control" type="text" placeholder="Enter required action" />
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Event Type</label>
                  <select v-model="form.event_type" class="form-control">
                    <option value="general">General</option>
                    <option value="meeting">Meeting</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="review">Review</option>
                    <option value="security">Security</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="pm-cal-v2-field">
                  <label class="pm-field-label">Priority</label>
                  <select v-model="form.priority" class="form-control">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                  </select>
                </div>
              </div>

              <div class="pm-cal-v2-field">
                <label class="pm-field-label">Notification</label>
                <input v-model="form.attendees_input" class="form-control" type="text" placeholder="alice@company.com, bob@company.com" @blur="sanitizeAttendees" />
                <div v-if="invalidAttendees.length" class="pm-help-text pm-error-text">
                  Invalid emails: {{ invalidAttendees.join(', ') }}
                </div>
              </div>

              <div class="pm-cal-v2-block">
                <h4>Event Recurring Time</h4>
                <div class="pm-cal-v2-grid-3">
                  <div class="pm-cal-v2-field">
                    <label class="pm-field-label">Start</label>
                    <div class="pm-cal-v2-inline-time">
                      <input v-model="form.start_date" class="form-control" type="date" required />
                      <input v-if="!form.all_day" v-model="form.start_time" class="form-control" type="time" />
                    </div>
                  </div>
                  <div class="pm-cal-v2-field">
                    <label class="pm-field-label">End</label>
                    <div class="pm-cal-v2-inline-time">
                      <input v-model="form.end_date" class="form-control" type="date" />
                      <input v-if="!form.all_day" v-model="form.end_time" class="form-control" type="time" />
                    </div>
                  </div>
                  <div class="pm-cal-v2-field">
                    <label class="pm-field-label">Duration</label>
                    <input class="form-control" type="text" :value="eventDurationLabel" readonly />
                  </div>
                </div>
              </div>

              <div class="pm-cal-v2-block">
                <h4>Recurrence Pattern</h4>
                <div class="pm-cal-v2-rec-options">
                  <label v-for="option in recurrenceChoices" :key="option.value" class="pm-cal-v2-radio">
                    <input type="radio" :value="option.value" v-model="form.recurrence_freq" />
                    <span>{{ option.label }}</span>
                  </label>
                  <label class="pm-cal-v2-radio">
                    <input type="radio" value="" v-model="form.recurrence_freq" />
                    <span>None</span>
                  </label>
                </div>
                <div v-if="form.recurrence_freq" class="pm-cal-v2-rec-repeat">
                  <label class="pm-field-label">Repeat every</label>
                  <input v-model.number="form.recurrence_interval" class="form-control" type="number" min="1" max="365" />
                  <span>
                    {{ recurrenceUnitLabel }}
                    <span v-if="form.recurrence_freq === 'weekly'">on</span>
                  </span>
                </div>
                <div v-if="form.recurrence_freq === 'weekly'" class="pm-cal-v2-weekdays-wrap">
                  <label class="pm-field-label">Weekly day selection</label>
                  <div class="pm-cal-v2-weekdays-picks">
                    <label
                      v-for="option in weeklyDayChoices"
                      :key="option.value"
                      class="pm-cal-v2-check"
                      :class="{ 'is-active': form.recurrence_days.includes(option.value) }"
                    >
                      <input
                        type="checkbox"
                        :checked="form.recurrence_days.includes(option.value)"
                        @change="toggleRecurrenceDay(option.value)"
                      />
                      <span>{{ option.label }}</span>
                    </label>
                  </div>
                  <div class="pm-help-text">For weekly recurrence, only selected days will repeat.</div>
                </div>
                <div v-if="form.recurrence_freq === 'monthly'" class="pm-cal-v2-monthly-wrap">
                  <label class="pm-field-label">Monthly on day</label>
                  <input
                    v-model.number="form.monthly_day_of_month"
                    class="form-control"
                    type="number"
                    min="1"
                    max="31"
                  />
                </div>
                <div v-if="form.recurrence_freq === 'yearly'" class="pm-cal-v2-yearly-wrap">
                  <label class="pm-field-label">Yearly on</label>
                  <div class="pm-cal-v2-yearly-row">
                    <select v-model.number="form.yearly_month" class="form-control">
                      <option v-for="item in yearlyMonthChoices" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>
                    <input
                      v-model.number="form.yearly_day_of_month"
                      class="form-control"
                      type="number"
                      min="1"
                      max="31"
                    />
                  </div>
                </div>
              </div>

              <div class="pm-cal-v2-block">
                <h4>Range of Recurrence</h4>
                <div class="pm-cal-v2-grid-2">
                  <div class="pm-cal-v2-field">
                    <label class="pm-field-label">Starts on</label>
                    <input v-model="form.start_date" class="form-control" type="date" required />
                  </div>
                  <div class="pm-cal-v2-field pm-cal-v2-range-options">
                    <label class="pm-cal-v2-radio">
                      <input type="radio" value="none" v-model="form.recurrence_mode" />
                      <span>No end date</span>
                    </label>
                    <div class="pm-cal-v2-range-row">
                      <label class="pm-cal-v2-radio">
                        <input type="radio" value="after" v-model="form.recurrence_mode" />
                        <span>End after</span>
                      </label>
                      <input
                        v-model.number="form.recurrence_end_after"
                        class="form-control"
                        type="number"
                        min="1"
                        max="999"
                        :disabled="form.recurrence_mode !== 'after'"
                      />
                      <span>occurrence(s)</span>
                    </div>
                    <div class="pm-cal-v2-range-row">
                      <label class="pm-cal-v2-radio">
                        <input type="radio" value="by" v-model="form.recurrence_mode" />
                        <span>End by</span>
                      </label>
                      <input
                        v-model="form.recurrence_until"
                        class="form-control"
                        type="date"
                        :disabled="form.recurrence_mode !== 'by'"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div class="pm-cal-v2-block">
                <h4>Reminders</h4>
                <div class="pm-cal-v2-reminder-row">
                  <label>1st Reminder</label>
                  <input v-model="form.reminder_1_date" class="form-control" type="date" />
                  <input v-model="form.reminder_1_time" class="form-control" type="time" />
                </div>
                <div class="pm-cal-v2-reminder-row">
                  <label>2nd Reminder</label>
                  <input v-model="form.reminder_2_date" class="form-control" type="date" />
                  <input v-model="form.reminder_2_time" class="form-control" type="time" />
                </div>
                <div class="pm-cal-v2-reminder-row">
                  <label>3rd Reminder</label>
                  <input v-model="form.reminder_3_date" class="form-control" type="date" />
                  <input v-model="form.reminder_3_time" class="form-control" type="time" />
                </div>
              </div>

              <div class="pm-cal-v2-actions">
                <button class="btn btn-outline-secondary" type="button" @click="resetForm">Cancel</button>
                <button
                  v-if="form.id && canDeleteCalendar"
                  class="btn btn-outline-danger"
                  type="button"
                  :disabled="saving"
                  @click="deleteEvent"
                >
                  Delete
                </button>
                <button class="btn btn-primary" type="submit" :disabled="saving || !canManageCurrentEvent">
                  {{ saving ? 'Saving...' : form.id ? 'Update Event' : 'Save Event' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </div>

    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>

    <div v-if="dayEventsOpen" class="pm-modal-overlay" @click.self="closeDayEvents">
      <div class="pm-modal pm-events-sheet">
        <div class="pm-modal-header">
          <div>
            <div class="pm-modal-title">{{ dayEventsTitle }}</div>
            <div class="pm-modal-subtitle">{{ dayEventsItems.length }} event(s)</div>
          </div>
          <button class="pm-icon-btn" type="button" aria-label="Close" @click="closeDayEvents">x</button>
        </div>
        <div class="pm-modal-body pm-events-sheet-body">
          <div v-if="!dayEventsItems.length" class="pm-cal-v2-empty">No events available for this date.</div>
          <button
            v-for="item in dayEventsItems"
            :key="String(item.id) + '-day-list'"
            type="button"
            class="pm-event-row"
            @click="openEventDetail(item)"
          >
            <div class="pm-event-row-top">
              <strong>{{ item.title }}</strong>
              <span class="pm-cal-v2-priority" :class="`is-${priorityClassForEvent(item)}`">
                {{ priorityLabelForEvent(item) }}
              </span>
            </div>
            <div class="pm-event-row-meta">
              {{ eventDateTimeLabel(item) }}
              <span v-if="item.location"> | {{ item.location }}</span>
            </div>
            <div class="pm-event-row-desc">{{ item.description || 'No description added yet.' }}</div>
          </button>
        </div>
        <div class="pm-modal-actions">
          <button class="btn btn-outline-secondary" type="button" @click="closeDayEvents">Close</button>
        </div>
      </div>
    </div>

    <div v-if="allEventsOpen" class="pm-modal-overlay" @click.self="closeAllEvents">
      <div class="pm-modal pm-events-sheet pm-events-sheet-wide">
        <div class="pm-modal-header">
          <div>
            <div class="pm-modal-title">All Events</div>
            <div class="pm-modal-subtitle">{{ allEventsSorted.length }} event(s) in current view</div>
          </div>
          <button class="pm-icon-btn" type="button" aria-label="Close" @click="closeAllEvents">x</button>
        </div>
        <div class="pm-modal-body pm-events-sheet-body">
          <div v-if="!allEventsSorted.length" class="pm-cal-v2-empty">No events found.</div>
          <button
            v-for="item in allEventsSorted"
            :key="String(item.id) + '-all-list'"
            type="button"
            class="pm-event-row"
            @click="openEventDetail(item)"
          >
            <div class="pm-event-row-top">
              <strong>{{ item.title }}</strong>
              <span class="pm-cal-v2-priority" :class="`is-${priorityClassForEvent(item)}`">
                {{ priorityLabelForEvent(item) }}
              </span>
            </div>
            <div class="pm-event-row-meta">
              {{ eventDateTimeLabel(item) }}
              <span v-if="item.location"> | {{ item.location }}</span>
            </div>
            <div class="pm-event-row-desc">{{ item.description || 'No description added yet.' }}</div>
          </button>
        </div>
        <div class="pm-modal-actions">
          <button class="btn btn-outline-secondary" type="button" @click="closeAllEvents">Close</button>
        </div>
      </div>
    </div>

    <div v-if="eventDetailOpen" class="pm-modal-overlay" @click.self="closeEventDetail">
      <div class="pm-modal">
        <div class="pm-modal-header">
          <div>
            <div class="pm-modal-title">{{ detailEvent?.title }}</div>
            <div class="pm-modal-subtitle">
              {{ detailEvent?.all_day ? 'All day' : formatTimeRange(detailEvent) }}
              <span v-if="detailEvent?.location">- {{ detailEvent.location }}</span>
            </div>
          </div>
          <button class="pm-icon-btn" type="button" aria-label="Close" @click="closeEventDetail">x</button>
        </div>
        <div class="pm-modal-body">
          <div class="pm-modal-row">
            <span class="pm-dot" :style="{ background: detailEventColor }"></span>
            <span class="pm-modal-chip">{{ detailEvent?.status || 'scheduled' }}</span>
            <span class="pm-modal-chip">{{ detailEvent?.visibility || 'private' }}</span>
          </div>
          <div v-if="detailEvent?.description" class="pm-modal-description">
            {{ detailEvent.description }}
          </div>
          <div class="pm-modal-meta">
            <div><strong>Event No:</strong> {{ detailEvent?.event_no || '-' }}</div>
            <div><strong>Event Type:</strong> {{ detailEvent?.event_type || '-' }}</div>
            <div><strong>Required Action:</strong> {{ detailEvent?.required_action || '-' }}</div>
            <div><strong>Added By:</strong> {{ creatorLabelForEvent(detailEvent) }}</div>
            <div><strong>Priority:</strong> {{ priorityLabelForEvent(detailEvent) }}</div>
            <div><strong>Start:</strong> {{ detailEvent?.start_at ? formatLongDate(new Date(detailEvent.start_at)) : '-' }}</div>
            <div><strong>End:</strong> {{ detailEvent?.end_at ? formatLongDate(new Date(detailEvent.end_at)) : '-' }}</div>
            <div v-if="detailEvent?.location_map"><strong>Location Map:</strong> {{ detailEvent.location_map }}</div>
          </div>
        </div>
        <div class="pm-modal-actions">
          <button class="btn btn-outline-secondary" type="button" @click="closeEventDetail">Close</button>
          <button v-if="canEditCalendar" class="btn btn-primary" type="button" @click="editEvent(detailEvent)">Edit Event</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppSidebar from '../components/AppSidebar.vue'
import client from '../api/client'
import { authState } from '../store/auth'
import { setFlash } from '../store/flash'
import { hasUserPermission } from '../config/permissions'

const router = useRouter()
const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const searchQuery = ref('')
const userName = ref('User')

const events = ref([])
const selectedDate = ref(new Date())
const currentMonth = ref(new Date())
const saving = ref(false)
const eventDetailOpen = ref(false)
const detailEvent = ref(null)
const dayEventsOpen = ref(false)
const dayEventsTitle = ref('')
const dayEventsItems = ref([])
const allEventsOpen = ref(false)
const canReadCalendar = computed(() => hasUserPermission(authState.user, 'calendar', 'read'))
const canCreateCalendar = computed(() => hasUserPermission(authState.user, 'calendar', 'create'))
const canEditCalendar = computed(() => hasUserPermission(authState.user, 'calendar', 'edit'))
const canDeleteCalendar = computed(() => hasUserPermission(authState.user, 'calendar', 'delete'))
const canImportCalendar = computed(() => canCreateCalendar.value)
const canExportCalendar = computed(() => canReadCalendar.value)
const canManageCurrentEvent = computed(() => (form.id ? canEditCalendar.value : canCreateCalendar.value))
const DEFAULT_EVENT_COLOR = '#2563eb'

const getThemePrimaryColor = () => {
  if (typeof window === 'undefined') return DEFAULT_EVENT_COLOR
  const value = getComputedStyle(document.body).getPropertyValue('--pm-primary').trim()
  return value || DEFAULT_EVENT_COLOR
}

const form = reactive({
  id: null,
  event_no: '',
  added_by_label: '',
  title: '',
  description: '',
  start_date: '',
  start_time: '09:00',
  end_date: '',
  end_time: '10:00',
  all_day: false,
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
  location: '',
  location_map: '',
  color: getThemePrimaryColor(),
  status: 'scheduled',
  visibility: 'private',
  priority: 'medium',
  event_type: 'general',
  required_action: '',
  recurrence_freq: '',
  recurrence_interval: 1,
  recurrence_days: [],
  monthly_day_of_month: 1,
  yearly_month: 1,
  yearly_day_of_month: 1,
  recurrence_mode: 'none',
  recurrence_end_after: 10,
  recurrence_until: '',
  reminder_1_date: '',
  reminder_1_time: '',
  reminder_2_date: '',
  reminder_2_time: '',
  reminder_3_date: '',
  reminder_3_time: '',
  attendees_input: '',
  exceptions_input: '',
  exception_date: '',
  showExceptionPicker: false,
})

const weekDays = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']
const recurrenceChoices = [
  { value: 'daily', label: 'Daily' },
  { value: 'weekly', label: 'Weekly' },
  { value: 'monthly', label: 'Monthly' },
  { value: 'yearly', label: 'Yearly' },
]
const weeklyDayChoices = [
  { value: 'sun', label: 'Sunday' },
  { value: 'mon', label: 'Monday' },
  { value: 'tue', label: 'Tuesday' },
  { value: 'wed', label: 'Wednesday' },
  { value: 'thu', label: 'Thursday' },
  { value: 'fri', label: 'Friday' },
  { value: 'sat', label: 'Saturday' },
]
const yearlyMonthChoices = [
  { value: 1, label: 'January' },
  { value: 2, label: 'February' },
  { value: 3, label: 'March' },
  { value: 4, label: 'April' },
  { value: 5, label: 'May' },
  { value: 6, label: 'June' },
  { value: 7, label: 'July' },
  { value: 8, label: 'August' },
  { value: 9, label: 'September' },
  { value: 10, label: 'October' },
  { value: 11, label: 'November' },
  { value: 12, label: 'December' },
]
const weekdayOrder = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
const weekdayIndexByKey = { sun: 0, mon: 1, tue: 2, wed: 3, thu: 4, fri: 5, sat: 6 }
const weekdayKeyByIndex = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']

const monthLabel = computed(() => {
  const date = currentMonth.value
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

const calendarCells = computed(() => {
  const firstOfMonth = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth(), 1)
  const startOffset = firstOfMonth.getDay()
  const gridStart = new Date(firstOfMonth)
  gridStart.setDate(firstOfMonth.getDate() - startOffset)
  const cells = []
  for (let i = 0; i < 42; i++) {
    const date = new Date(gridStart)
    date.setDate(gridStart.getDate() + i)
    const key = date.toISOString().slice(0, 10)
    cells.push({
      key,
      date,
      isToday: isSameDate(date, new Date()),
      isOutside: date.getMonth() !== currentMonth.value.getMonth(),
      events: eventsByDate(key),
    })
  }
  return cells
})

const upcomingEvents = computed(() => {
  const today = new Date()
  const end = new Date()
  end.setDate(today.getDate() + 7)
  return events.value
    .filter((event) => {
      const start = new Date(event.start_at)
      return start >= today && start <= end
    })
    .sort((a, b) => new Date(a.start_at) - new Date(b.start_at))
    .slice(0, 6)
})
const allEventsSorted = computed(() =>
  [...events.value].sort((a, b) => new Date(a.start_at) - new Date(b.start_at))
)
const detailEventColor = computed(() => detailEvent.value?.color || getThemePrimaryColor())
const eventNoDisplay = computed(() => {
  if (form.event_no) return form.event_no
  if (!form.id) return 'Auto-generated'
  const year = form.start_date ? String(form.start_date).slice(0, 4) : String(new Date().getFullYear())
  return `EVT-${year}-${String(form.id).padStart(4, '0')}`
})
const eventDurationLabel = computed(() => {
  if (form.all_day) return 'All day'
  const start = composeDateTime(form.start_date, form.start_time)
  const end = composeDateTime(form.end_date || form.start_date, form.end_time)
  if (!start || !end) return '10 Min'
  const minutes = Math.max(1, Math.round((end.getTime() - start.getTime()) / 60000))
  return `${minutes} Min`
})
const recurrenceUnitLabel = computed(() => {
  if (form.recurrence_freq === 'daily') return 'day(s)'
  if (form.recurrence_freq === 'weekly') return 'week(s)'
  if (form.recurrence_freq === 'monthly') return 'month(s)'
  if (form.recurrence_freq === 'yearly') return 'year(s)'
  return 'week(s)'
})

const formatCellEventCount = (count) => {
  const label = count === 1 ? 'Event' : 'Events'
  return `${String(count).padStart(2, '0')} ${label}`
}

const calendarCellTone = (count) => {
  if (count >= 6) return 'high'
  if (count >= 3) return 'medium'
  return 'low'
}

const normalizePriority = (value) => {
  const normalized = String(value || '').toLowerCase().trim()
  return ['low', 'medium', 'high', 'critical'].includes(normalized) ? normalized : ''
}

const normalizeRecurrenceDays = (values) => {
  if (!Array.isArray(values)) return []
  const normalized = values
    .map((value) => String(value || '').toLowerCase().trim())
    .filter((value) => weekdayOrder.includes(value))
  return [...new Set(normalized)].sort((a, b) => weekdayIndexByKey[a] - weekdayIndexByKey[b])
}

const defaultWeeklyDayFromStart = () => {
  const baseDate = form.start_date ? new Date(`${form.start_date}T00:00:00`) : selectedDate.value
  const dayIndex = baseDate.getDay()
  return weekdayKeyByIndex[dayIndex] || 'mon'
}

const clamp = (value, min, max, fallback) => {
  const numeric = Number(value)
  if (!Number.isFinite(numeric)) return fallback
  return Math.max(min, Math.min(max, Math.round(numeric)))
}

const syncMonthlyYearlyDefaultsFromStart = (force = false) => {
  const baseDate = form.start_date ? new Date(`${form.start_date}T00:00:00`) : selectedDate.value
  const baseDay = baseDate.getDate()
  const baseMonth = baseDate.getMonth() + 1

  if (force || !form.monthly_day_of_month) {
    form.monthly_day_of_month = baseDay
  }
  if (force || !form.yearly_month) {
    form.yearly_month = baseMonth
  }
  if (force || !form.yearly_day_of_month) {
    form.yearly_day_of_month = baseDay
  }
}

const toggleRecurrenceDay = (dayKey) => {
  const normalized = normalizeRecurrenceDays(form.recurrence_days)
  if (normalized.includes(dayKey)) {
    form.recurrence_days = normalized.filter((value) => value !== dayKey)
    return
  }
  form.recurrence_days = normalizeRecurrenceDays([...normalized, dayKey])
}

const priorityClassForEvent = (event) => {
  const saved = normalizePriority(event?.priority)
  if (saved) return saved

  const start = event?.start_at ? new Date(event.start_at) : null
  const days = start ? (start.getTime() - Date.now()) / (1000 * 60 * 60 * 24) : 99
  if (String(event?.status || '').toLowerCase() === 'cancelled') return 'low'
  if (days <= 1) return 'critical'
  if (days <= 3) return 'high'
  if (days <= 7) return 'medium'
  return 'low'
}

const priorityLabelForEvent = (event) => {
  const key = priorityClassForEvent(event)
  return key.charAt(0).toUpperCase() + key.slice(1)
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleSidebarHidden = () => {
  sidebarHidden.value = !sidebarHidden.value
}

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const goProfile = () => {
  userMenuOpen.value = false
  router.push('/account/profile')
}

const goAccount = () => {
  userMenuOpen.value = false
  router.push('/account/status')
}

const goNotifications = () => {
  router.push('/notifications')
}

const goSettings = () => {
  router.push('/settings/theme')
}

const logout = () => {
  userMenuOpen.value = false
  router.push('/login')
}

const closeUserMenu = (event) => {
  if (!userMenuRef.value) return
  if (!userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

const handleEsc = (event) => {
  if (event.key === 'Escape') {
    userMenuOpen.value = false
    eventDetailOpen.value = false
    dayEventsOpen.value = false
    allEventsOpen.value = false
  }
}

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.trim().split(' ')
  const first = parts[0]?.[0] || 'U'
  const last = parts[1]?.[0] || ''
  return (first + last).toUpperCase()
})

const genericUserLabels = new Set(['user', 'unknown', 'n/a', 'na', '-'])

const normalizeDisplayName = (value) => {
  const label = String(value || '').trim()
  if (!label) return ''
  return genericUserLabels.has(label.toLowerCase()) ? '' : label
}

const resolveUserIdentity = (user, fallbackName = '') => {
  const source = user && typeof user === 'object' ? user : {}
  const safeName =
    normalizeDisplayName(source.name) ||
    normalizeDisplayName(source.username) ||
    normalizeDisplayName(fallbackName)
  const safeEmail = String(source.email || '').trim()
  return { safeName, safeEmail }
}

const formatUserLabel = (user, fallbackName = '') => {
  const { safeName, safeEmail } = resolveUserIdentity(user, fallbackName)
  if (safeName && safeEmail) return `${safeName} (${safeEmail})`
  if (safeName) return safeName
  if (safeEmail) return safeEmail
  return 'Unknown'
}

const syncUserNameFromAuth = () => {
  const { safeName, safeEmail } = resolveUserIdentity(authState.user)
  if (safeName) {
    userName.value = safeName
    return
  }
  if (safeEmail) {
    userName.value = safeEmail
    return
  }
  userName.value = 'User'
}

const currentUserLabel = computed(() => formatUserLabel(authState.user, userName.value))

const creatorLabelForEvent = (event) => {
  if (!event) return currentUserLabel.value
  const labeled = formatUserLabel(event.user || {})
  if (labeled !== 'Unknown') return labeled
  if (event.user_id) return `User #${event.user_id}`
  return currentUserLabel.value
}

watch(
  () => authState.user,
  () => {
    syncUserNameFromAuth()
    if (!form.id || !form.added_by_label || form.added_by_label === 'User' || form.added_by_label === 'Unknown') {
      form.added_by_label = currentUserLabel.value
    }
  },
  { immediate: true, deep: true },
)

const emailPattern = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/

const attendeesList = computed(() =>
  form.attendees_input
    ? form.attendees_input.split(',').map((value) => value.trim()).filter(Boolean)
    : []
)

const invalidAttendees = computed(() =>
  attendeesList.value.filter((item) => !emailPattern.test(item))
)

const sanitizeAttendees = () => {
  const valid = attendeesList.value.filter((item) => emailPattern.test(item))
  form.attendees_input = valid.join(', ')
}

const pad = (value) => String(value).padStart(2, '0')

const formatDate = (date) => {
  const year = date.getFullYear()
  const month = pad(date.getMonth() + 1)
  const day = pad(date.getDate())
  return `${year}-${month}-${day}`
}

const formatShortDate = (date) =>
  date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

const formatTimeInput = (date) => `${pad(date.getHours())}:${pad(date.getMinutes())}`

const formatLongDate = (date) =>
  date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })

const isSameDate = (a, b) =>
  a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()

const formatTimeRange = (event) => {
  if (!event.start_at) return ''
  const start = new Date(event.start_at)
  const end = event.end_at ? new Date(event.end_at) : null
  const startLabel = start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  if (!end) return startLabel
  const endLabel = end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  return `${startLabel} - ${endLabel}`
}

const eventDateTimeLabel = (event) => {
  if (!event?.start_at) return '-'
  const start = new Date(event.start_at)
  const dateLabel = formatShortDate(start)
  return event.all_day ? `${dateLabel} - All day` : `${dateLabel} - ${formatTimeRange(event)}`
}

const eventsByDate = (key) => {
  return events.value.filter((event) => formatDate(new Date(event.start_at)) === key)
}

const selectDate = (date) => {
  selectedDate.value = date
  if (!form.id) {
    form.start_date = formatDate(date)
    if (!form.end_date) form.end_date = formatDate(date)
  }
}

const sortEventsByStart = (items) =>
  [...items].sort((a, b) => new Date(a.start_at) - new Date(b.start_at))

const openDayEvents = (cell) => {
  if (!cell?.events?.length) return
  selectDate(cell.date)
  dayEventsTitle.value = formatLongDate(cell.date)
  dayEventsItems.value = sortEventsByStart(cell.events)
  allEventsOpen.value = false
  dayEventsOpen.value = true
}

const closeDayEvents = () => {
  dayEventsOpen.value = false
}

const openAllEvents = () => {
  dayEventsOpen.value = false
  allEventsOpen.value = true
}

const closeAllEvents = () => {
  allEventsOpen.value = false
}

const prepareNew = () => {
  if (!canCreateCalendar.value) {
    setFlash('You do not have permission to create events.', 'warning', 2500)
    return
  }
  resetForm()
  form.start_date = formatDate(selectedDate.value)
  form.end_date = formatDate(selectedDate.value)
}

const resetForm = () => {
  form.id = null
  form.event_no = ''
  form.added_by_label = currentUserLabel.value
  form.title = ''
  form.description = ''
  form.start_date = formatDate(selectedDate.value)
  form.start_time = '09:00'
  form.end_date = formatDate(selectedDate.value)
  form.end_time = '10:00'
  form.all_day = false
  form.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
  form.location = ''
  form.location_map = ''
  form.color = getThemePrimaryColor()
  form.status = 'scheduled'
  form.visibility = 'private'
  form.priority = 'medium'
  form.event_type = 'general'
  form.required_action = ''
  form.recurrence_freq = ''
  form.recurrence_interval = 1
  form.recurrence_days = []
  form.monthly_day_of_month = Number(form.start_date.slice(-2)) || 1
  form.yearly_month = Number(form.start_date.slice(5, 7)) || 1
  form.yearly_day_of_month = Number(form.start_date.slice(-2)) || 1
  form.recurrence_mode = 'none'
  form.recurrence_end_after = 10
  form.recurrence_until = ''
  form.reminder_1_date = ''
  form.reminder_1_time = ''
  form.reminder_2_date = ''
  form.reminder_2_time = ''
  form.reminder_3_date = ''
  form.reminder_3_time = ''
  form.attendees_input = ''
  form.exceptions_input = ''
  form.exception_date = ''
  form.showExceptionPicker = false
}

const editEvent = (event) => {
  if (!canEditCalendar.value) {
    setFlash('You do not have permission to edit events.', 'warning', 2500)
    return
  }
  if (!event) return
  eventDetailOpen.value = false
  form.id = event.id
  form.event_no = event.event_no || ''
  form.added_by_label = creatorLabelForEvent(event)
  form.title = event.title
  form.description = event.description || ''
  form.all_day = !!event.all_day
  form.location = event.location || ''
  form.location_map = event.location_map || ''
  form.color = event.color || getThemePrimaryColor()
  form.priority = normalizePriority(event.priority) || inferPriorityFromColor(form.color)
  form.status = event.status || 'scheduled'
  form.visibility = event.visibility || 'private'
  form.event_type = event.event_type || 'general'
  form.required_action = event.required_action || ''
  const recurrenceRules = event && typeof event.recurrence_rules_json === 'object' && event.recurrence_rules_json
    ? event.recurrence_rules_json
    : {}
  const storedFreq = event.recurrence_freq || ''
  const storedInterval = Number(event.recurrence_interval || 1)
  if (storedFreq === 'monthly' && storedInterval % 12 === 0 && storedInterval >= 12) {
    form.recurrence_freq = 'yearly'
    form.recurrence_interval = Math.max(1, Math.round(storedInterval / 12))
  } else {
    form.recurrence_freq = storedFreq
    form.recurrence_interval = storedInterval
  }
  form.recurrence_days = normalizeRecurrenceDays(Array.isArray(event.recurrence_days_json) ? event.recurrence_days_json : [])
  form.monthly_day_of_month = clamp(
    recurrenceRules.monthly_day_of_month ?? new Date(event.start_at).getDate(),
    1,
    31,
    new Date(event.start_at).getDate(),
  )
  form.yearly_month = clamp(
    recurrenceRules.yearly_month ?? (new Date(event.start_at).getMonth() + 1),
    1,
    12,
    new Date(event.start_at).getMonth() + 1,
  )
  form.yearly_day_of_month = clamp(
    recurrenceRules.yearly_day_of_month ?? new Date(event.start_at).getDate(),
    1,
    31,
    new Date(event.start_at).getDate(),
  )
  form.recurrence_mode = event.recurrence_mode || (event.recurrence_until ? 'by' : 'none')
  form.recurrence_end_after = Number(event.recurrence_end_after || 10)
  form.recurrence_until = event.recurrence_until ? event.recurrence_until.slice(0, 10) : ''
  setReminderFieldsFromOffsets(new Date(event.start_at), Array.isArray(event.reminders_json) ? event.reminders_json : [])
  form.attendees_input = Array.isArray(event.attendees_json) ? event.attendees_json.join(', ') : ''
  form.exceptions_input = Array.isArray(event.exceptions_json) ? event.exceptions_json.join(', ') : ''
  form.exception_date = ''
  form.showExceptionPicker = false
  const start = new Date(event.start_at)
  form.start_date = formatDate(start)
  form.start_time = formatTimeInput(start)
  if (event.end_at) {
    const end = new Date(event.end_at)
    form.end_date = formatDate(end)
    form.end_time = formatTimeInput(end)
  } else {
    form.end_date = form.start_date
    form.end_time = form.start_time
  }
  if (form.recurrence_freq === 'weekly' && !form.recurrence_days.length) {
    form.recurrence_days = [defaultWeeklyDayFromStart()]
  }
}

const openEventDetail = (event) => {
  dayEventsOpen.value = false
  allEventsOpen.value = false
  detailEvent.value = event
  eventDetailOpen.value = true
}

const closeEventDetail = () => {
  eventDetailOpen.value = false
}

const composeDateTime = (date, time) => {
  if (!date) return null
  const timeValue = time || '00:00'
  return new Date(`${date}T${timeValue}:00`)
}

const priorityColorMap = {
  low: '#64748b',
  high: '#f59e0b',
  critical: '#ef4444',
}

const priorityColorFor = (priority) => {
  const key = normalizePriority(priority)
  if (key === 'medium' || !key) return getThemePrimaryColor()
  return priorityColorMap[key] || getThemePrimaryColor()
}

const inferPriorityFromColor = (color) => {
  const value = String(color || '').toLowerCase()
  if (value.includes('ef4444')) return 'critical'
  if (value.includes('f59e0b')) return 'high'
  if (value.includes('64748b')) return 'low'
  return 'medium'
}

const setReminderFieldsFromOffsets = (startAt, offsets) => {
  form.reminder_1_date = ''
  form.reminder_1_time = ''
  form.reminder_2_date = ''
  form.reminder_2_time = ''
  form.reminder_3_date = ''
  form.reminder_3_time = ''

  const pairs = [
    ['reminder_1_date', 'reminder_1_time'],
    ['reminder_2_date', 'reminder_2_time'],
    ['reminder_3_date', 'reminder_3_time'],
  ]

  offsets
    .map((value) => Number(value))
    .filter((value) => Number.isFinite(value) && value > 0)
    .sort((a, b) => a - b)
    .slice(0, 3)
    .forEach((offset, index) => {
      const reminderAt = new Date(startAt.getTime() - (offset * 60 * 1000))
      const [dateKey, timeKey] = pairs[index]
      form[dateKey] = formatDate(reminderAt)
      form[timeKey] = formatTimeInput(reminderAt)
    })
}

const buildReminderOffsets = (startAt) => {
  const rawPairs = [
    [form.reminder_1_date, form.reminder_1_time],
    [form.reminder_2_date, form.reminder_2_time],
    [form.reminder_3_date, form.reminder_3_time],
  ]

  const offsets = rawPairs
    .map(([dateValue, timeValue]) => {
      if (!dateValue) return null
      const reminderAt = composeDateTime(dateValue, timeValue || '09:00')
      if (!reminderAt) return null
      const minutes = Math.round((startAt.getTime() - reminderAt.getTime()) / (60 * 1000))
      return minutes > 0 ? minutes : null
    })
    .filter((value) => value !== null)

  return [...new Set(offsets)].sort((a, b) => a - b).slice(0, 3)
}

const nextRecurrenceByRules = (cursor, startAt, freq, interval, selectedWeekdays = [], recurrenceRules = {}) => {
  const next = new Date(cursor)

  if (freq === 'daily') {
    next.setDate(next.getDate() + interval)
    return next
  }

  if (freq === 'weekly') {
    const dayIndexes = normalizeRecurrenceDays(selectedWeekdays)
      .map((key) => weekdayIndexByKey[key])
      .filter((value) => Number.isInteger(value))
    if (!dayIndexes.includes(startAt.getDay())) {
      dayIndexes.push(startAt.getDay())
      dayIndexes.sort((a, b) => a - b)
    }

    const baseDay = new Date(startAt.getFullYear(), startAt.getMonth(), startAt.getDate())
    while (true) {
      next.setDate(next.getDate() + 1)
      const cursorDay = new Date(next.getFullYear(), next.getMonth(), next.getDate())
      const daysSinceBase = Math.floor((cursorDay.getTime() - baseDay.getTime()) / (24 * 60 * 60 * 1000))
      const weekIndex = Math.floor(daysSinceBase / 7)
      const isWeekMatch = weekIndex % interval === 0
      const isDayMatch = dayIndexes.includes(next.getDay())
      if (isWeekMatch && isDayMatch) {
        return next
      }
    }
  }

  if (freq === 'monthly') {
    const targetDay = clamp(recurrenceRules.monthly_day_of_month ?? startAt.getDate(), 1, 31, startAt.getDate())
    next.setMonth(next.getMonth() + interval, 1)
    const maxDay = new Date(next.getFullYear(), next.getMonth() + 1, 0).getDate()
    next.setDate(Math.min(targetDay, maxDay))
    return next
  }

  if (freq === 'yearly') {
    const targetMonth = clamp(recurrenceRules.yearly_month ?? (startAt.getMonth() + 1), 1, 12, startAt.getMonth() + 1)
    const targetDay = clamp(recurrenceRules.yearly_day_of_month ?? startAt.getDate(), 1, 31, startAt.getDate())
    const nextYear = next.getFullYear() + interval
    next.setFullYear(nextYear, targetMonth - 1, 1)
    const maxDay = new Date(next.getFullYear(), targetMonth, 0).getDate()
    next.setDate(Math.min(targetDay, maxDay))
    return next
  }

  return null
}

const computeRecurrenceUntilByOccurrences = (
  startAt,
  freq,
  interval,
  occurrences,
  selectedWeekdays = [],
  recurrenceRules = {},
) => {
  const targetCount = Math.max(1, Number(occurrences || 1))
  const cursor = new Date(startAt)

  const steps = targetCount - 1
  for (let i = 0; i < steps; i += 1) {
    const next = nextRecurrenceByRules(cursor, startAt, freq, interval, selectedWeekdays, recurrenceRules)
    if (!next) break
    cursor.setTime(next.getTime())
  }
  return formatDate(cursor)
}

const saveEvent = async () => {
  if (!canManageCurrentEvent.value) {
    setFlash('You do not have permission to save events.', 'warning', 2500)
    return
  }

  saving.value = true
  try {
    sanitizeAttendees()
    const start = form.all_day ? composeDateTime(form.start_date, '00:00') : composeDateTime(form.start_date, form.start_time)
    if (!start) {
      setFlash('Start date is required.', 'warning', 2500)
      return
    }

    const endDate = form.end_date || form.start_date
    let end = form.all_day ? composeDateTime(endDate, '23:59') : composeDateTime(endDate, form.end_time)
    if (!form.all_day && (!end || end < start)) {
      const fallbackEnd = new Date(start.getTime() + 10 * 60 * 1000)
      end = fallbackEnd
      form.end_date = formatDate(fallbackEnd)
      form.end_time = formatTimeInput(fallbackEnd)
    }

    const recurrenceFreq = form.recurrence_freq || ''
    let recurrenceInterval = recurrenceFreq ? Math.max(1, Number(form.recurrence_interval || 1)) : null
    const recurrenceMode = recurrenceFreq ? (form.recurrence_mode || 'none') : 'none'
    const recurrenceEndAfter = recurrenceMode === 'after'
      ? Math.max(1, Number(form.recurrence_end_after || 1))
      : null
    const recurrenceDays = recurrenceFreq === 'weekly'
      ? normalizeRecurrenceDays(
        form.recurrence_days.length ? form.recurrence_days : [defaultWeeklyDayFromStart()],
      )
      : []
    const recurrenceRules = {}
    if (recurrenceFreq === 'monthly') {
      recurrenceRules.monthly_day_of_month = clamp(
        form.monthly_day_of_month,
        1,
        31,
        start.getDate(),
      )
    }
    if (recurrenceFreq === 'yearly') {
      recurrenceRules.yearly_month = clamp(
        form.yearly_month,
        1,
        12,
        start.getMonth() + 1,
      )
      recurrenceRules.yearly_day_of_month = clamp(
        form.yearly_day_of_month,
        1,
        31,
        start.getDate(),
      )
    }
    let recurrenceUntil = null
    if (recurrenceFreq) {
      if (recurrenceMode === 'by' && form.recurrence_until) {
        recurrenceUntil = form.recurrence_until
      } else if (recurrenceMode === 'after') {
        recurrenceUntil = computeRecurrenceUntilByOccurrences(
          start,
          recurrenceFreq,
          recurrenceInterval || 1,
          recurrenceEndAfter,
          recurrenceDays,
          recurrenceRules,
        )
      }
    }

    const persistedFreq = recurrenceFreq || null

    const payload = {
      title: form.title,
      description: form.description,
      start_at: start ? start.toISOString() : null,
      end_at: end ? end.toISOString() : null,
      all_day: form.all_day,
      timezone: form.timezone,
      location: form.location,
      location_map: form.location_map || null,
      color: priorityColorFor(form.priority) || form.color,
      status: form.status,
      visibility: form.visibility,
      priority: normalizePriority(form.priority) || 'medium',
      event_type: form.event_type || 'general',
      required_action: form.required_action || null,
      recurrence_freq: persistedFreq,
      recurrence_interval: persistedFreq ? recurrenceInterval : null,
      recurrence_until: persistedFreq ? recurrenceUntil : null,
      recurrence_days_json: persistedFreq === 'weekly' ? recurrenceDays : [],
      recurrence_rules_json: persistedFreq ? recurrenceRules : {},
      recurrence_mode: persistedFreq ? recurrenceMode : 'none',
      recurrence_end_after: persistedFreq && recurrenceMode === 'after' ? recurrenceEndAfter : null,
      reminders_json: buildReminderOffsets(start),
      attendees_json: form.attendees_input
        ? form.attendees_input.split(',').map((value) => value.trim()).filter(Boolean)
        : [],
      exceptions_json: form.exceptions_input
        ? form.exceptions_input.split(',').map((value) => value.trim()).filter(Boolean)
        : [],
    }

    if (form.id) {
      await client.put(`/calendar-events/${form.id}`, payload)
    } else {
      await client.post('/calendar-events', payload)
    }
    await loadEvents()
    resetForm()
  } finally {
    saving.value = false
  }
}

const deleteEvent = async () => {
  if (!canDeleteCalendar.value) {
    setFlash('You do not have permission to delete events.', 'warning', 2500)
    return
  }
  if (!form.id) return
  await client.delete(`/calendar-events/${form.id}`)
  await loadEvents()
  resetForm()
}

const getGridRange = () => {
  const cells = calendarCells.value
  if (!cells.length) return null
  return {
    start: formatDate(cells[0].date),
    end: formatDate(cells[cells.length - 1].date),
  }
}

const loadEvents = async () => {
  if (!canReadCalendar.value) {
    events.value = []
    return
  }

  const range = getGridRange()
  const params = range ? { start: range.start, end: range.end } : {}
  const { data } = await client.get('/calendar-events', { params })
  events.value = data?.data || []
}

const importInput = ref(null)

const triggerImport = () => {
  if (!canImportCalendar.value) {
    setFlash('You do not have permission to import calendar data.', 'warning', 2500)
    return
  }
  if (importInput.value) importInput.value.click()
}

const handleImport = async (event) => {
  if (!canImportCalendar.value) {
    setFlash('You do not have permission to import calendar data.', 'warning', 2500)
    return
  }
  const file = event.target.files?.[0]
  if (!file) return
  const text = await file.text()
  await client.post('/calendar-events/import', { ics: text })
  await loadEvents()
  event.target.value = ''
}

const exportCalendar = async () => {
  if (!canExportCalendar.value) {
    setFlash('You do not have permission to export calendar data.', 'warning', 2500)
    return
  }
  const response = await client.get('/calendar-events/export', { responseType: 'blob' })
  const blob = new Blob([response.data], { type: 'text/calendar' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = 'calendar.ics'
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}

const shiftMonth = (delta) => {
  const date = new Date(currentMonth.value)
  date.setMonth(date.getMonth() + delta)
  currentMonth.value = date
}

const goToday = () => {
  currentMonth.value = new Date()
  selectedDate.value = new Date()
}

watch(currentMonth, () => {
  loadEvents()
})

watch(() => form.recurrence_freq, (value) => {
  if (!value) {
    form.recurrence_mode = 'none'
    form.recurrence_end_after = 10
    form.recurrence_until = ''
    form.recurrence_days = []
    syncMonthlyYearlyDefaultsFromStart()
    return
  }

  if (value === 'weekly' && !form.recurrence_days.length) {
    form.recurrence_days = [defaultWeeklyDayFromStart()]
  }
  if (value === 'monthly') {
    form.monthly_day_of_month = clamp(
      form.monthly_day_of_month || Number(form.start_date?.slice(-2)),
      1,
      31,
      1,
    )
  }
  if (value === 'yearly') {
    const monthFromStart = Number(form.start_date?.slice(5, 7)) || 1
    const dayFromStart = Number(form.start_date?.slice(-2)) || 1
    form.yearly_month = clamp(form.yearly_month || monthFromStart, 1, 12, monthFromStart)
    form.yearly_day_of_month = clamp(form.yearly_day_of_month || dayFromStart, 1, 31, dayFromStart)
  }
})

watch(() => form.start_date, () => {
  if (form.recurrence_freq === 'weekly' && !form.recurrence_days.length) {
    form.recurrence_days = [defaultWeeklyDayFromStart()]
  }
  if (form.recurrence_freq === 'monthly') {
    form.monthly_day_of_month = clamp(form.monthly_day_of_month, 1, 31, Number(form.start_date?.slice(-2)) || 1)
  }
  if (form.recurrence_freq === 'yearly') {
    form.yearly_month = clamp(form.yearly_month, 1, 12, Number(form.start_date?.slice(5, 7)) || 1)
    form.yearly_day_of_month = clamp(form.yearly_day_of_month, 1, 31, Number(form.start_date?.slice(-2)) || 1)
  }
})

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

onMounted(() => {
  syncUserNameFromAuth()
  resetForm()
  loadEvents()
  window.addEventListener('click', closeUserMenu)
  window.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  window.removeEventListener('click', closeUserMenu)
  window.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-cal-v2-page {
  gap: 20px;
}

.pm-cal-v2-top {
  display: grid;
  grid-template-columns: minmax(0, 2.3fr) minmax(320px, 1fr);
  gap: 16px;
  align-items: start;
}

.pm-cal-v2-board {
  padding: 18px;
  border: 1px solid #dce7f9;
  border-radius: 16px;
  background: #ffffff;
}

.pm-cal-v2-board-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.pm-cal-v2-board-head h3 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: #1e293b;
}

.pm-cal-v2-nav {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.pm-cal-v2-nav .pm-icon-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
}

.pm-cal-v2-nav .pm-icon-btn svg {
  width: 14px;
  height: 14px;
  stroke: var(--pm-primary-strong);
  stroke-width: 2;
  fill: none;
}

.pm-cal-v2-weekdays,
.pm-cal-v2-grid {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 8px;
}

.pm-cal-v2-weekday {
  font-size: 0.72rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  font-weight: 700;
  color: #64748b;
  padding: 0 4px;
}

.pm-cal-v2-cell {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  min-height: 84px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-start;
  transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
}

.pm-cal-v2-cell:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
}

.pm-cal-v2-cell.is-outside {
  opacity: 0.5;
}

.pm-cal-v2-cell.is-selected {
  border-color: var(--pm-primary-soft);
  background: rgba(var(--pm-primary-rgb), 0.1);
}

.pm-cal-v2-cell.is-today {
  border-color: var(--pm-primary);
  box-shadow: inset 0 0 0 1px rgba(var(--pm-primary-rgb), 0.2);
}

.pm-cal-v2-date {
  font-size: 0.84rem;
  font-weight: 700;
  color: #334155;
}

.pm-cal-v2-count {
  font-size: 0.68rem;
  font-weight: 700;
  border-radius: 999px;
  padding: 3px 8px;
  cursor: pointer;
  outline: none;
}

.pm-cal-v2-count:focus-visible {
  box-shadow: 0 0 0 2px rgba(var(--pm-primary-rgb), 0.45);
}

.pm-cal-v2-count.is-low {
  color: #166534;
  background: #dcfce7;
}

.pm-cal-v2-count.is-medium {
  color: #9a3412;
  background: #ffedd5;
}

.pm-cal-v2-count.is-high {
  color: #b91c1c;
  background: #fee2e2;
}

.pm-cal-v2-upcoming {
  border: 1px solid #dce7f9;
  border-radius: 16px;
  overflow: hidden;
  background: #ffffff;
  padding: 0;
}

.pm-cal-v2-upcoming-head {
  background: linear-gradient(120deg, var(--pm-primary-strong) 0%, var(--pm-primary) 100%);
  color: #ffffff;
  padding: 12px 14px;
  font-size: 0.98rem;
  font-weight: 700;
}

.pm-cal-v2-upcoming-list {
  padding: 12px;
  display: grid;
  gap: 10px;
}

.pm-cal-v2-upcoming-item {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #f8fafc;
  padding: 10px;
  text-align: left;
  display: grid;
  gap: 7px;
}

.pm-cal-v2-upcoming-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.pm-cal-v2-upcoming-top strong {
  color: #1e293b;
  font-size: 0.88rem;
  line-height: 1.25;
}

.pm-cal-v2-upcoming-item p {
  margin: 0;
  color: #64748b;
  font-size: 0.76rem;
  line-height: 1.35;
}

.pm-cal-v2-upcoming-meta {
  display: inline-flex;
  gap: 8px;
  flex-wrap: wrap;
  color: #334155;
  font-size: 0.74rem;
  font-weight: 600;
}

.pm-cal-v2-priority {
  font-size: 0.68rem;
  font-weight: 700;
  border-radius: 999px;
  padding: 2px 8px;
}

.pm-cal-v2-priority.is-low {
  color: #475569;
  background: #e2e8f0;
}

.pm-cal-v2-priority.is-medium {
  color: var(--pm-primary-strong);
  background: rgba(var(--pm-primary-rgb), 0.14);
}

.pm-cal-v2-priority.is-high {
  color: #b45309;
  background: #ffedd5;
}

.pm-cal-v2-priority.is-critical {
  color: #b91c1c;
  background: #fee2e2;
}

.pm-cal-v2-empty {
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  background: #f8fafc;
  padding: 12px;
  font-size: 0.85rem;
  color: #64748b;
  text-align: center;
}

.pm-cal-v2-upcoming .btn {
  margin: 0 12px 12px;
}

.pm-cal-v2-form-card {
  overflow: hidden;
  border: 1px solid #dce7f9;
  border-radius: 16px;
  background: #ffffff;
  padding: 0;
}

.pm-cal-v2-form-head {
  background: linear-gradient(120deg, var(--pm-primary-strong) 0%, var(--pm-primary) 100%);
  color: #ffffff;
  font-size: 1rem;
  font-weight: 700;
  padding: 11px 16px;
}

.pm-cal-v2-form {
  padding: 16px;
  display: grid;
  gap: 14px;
}

.pm-cal-v2-grid-2 {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 14px;
}

.pm-cal-v2-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.pm-cal-v2-field {
  display: grid;
  gap: 6px;
}

.pm-cal-v2-field .form-control {
  border-radius: 10px;
  min-height: 38px;
}

.pm-cal-v2-inline-time {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.pm-cal-v2-block {
  border: 1px solid #dce7f9;
  border-radius: 12px;
  padding: 12px;
  display: grid;
  gap: 10px;
  background: #f8fbff;
}

.pm-cal-v2-block h4 {
  margin: 0;
  font-size: 0.92rem;
  font-weight: 700;
  color: #1e293b;
}

.pm-cal-v2-rec-options {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 8px 12px;
}

.pm-cal-v2-rec-repeat {
  display: grid;
  grid-template-columns: 100px 120px auto;
  gap: 10px;
  align-items: center;
}

.pm-cal-v2-rec-repeat .pm-field-label {
  margin: 0;
}

.pm-cal-v2-weekdays-picks {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px 10px;
  max-width: 460px;
}

.pm-cal-v2-radio {
  display: inline-flex;
  gap: 6px;
  align-items: center;
  color: #334155;
  font-size: 0.84rem;
  font-weight: 600;
}

.pm-cal-v2-check {
  display: inline-flex;
  gap: 7px;
  align-items: center;
  padding: 8px 10px;
  border: 1px solid #dbe4f2;
  border-radius: 10px;
  background: #ffffff;
  font-size: 0.82rem;
  color: #334155;
}

.pm-cal-v2-check input {
  width: 16px;
  height: 16px;
  accent-color: var(--pm-primary);
}

.pm-cal-v2-check.is-active {
  border-color: rgba(var(--pm-primary-rgb), 0.45);
  background: rgba(var(--pm-primary-rgb), 0.1);
}

.pm-cal-v2-weekdays-wrap {
  display: grid;
  gap: 6px;
}

.pm-cal-v2-monthly-wrap {
  display: grid;
  gap: 6px;
  max-width: 240px;
}

.pm-cal-v2-yearly-wrap {
  display: grid;
  gap: 6px;
  max-width: 460px;
}

.pm-cal-v2-yearly-row {
  display: grid;
  grid-template-columns: 1fr 140px;
  gap: 10px;
}

.pm-cal-v2-range-options {
  gap: 10px;
}

.pm-cal-v2-range-row {
  display: grid;
  grid-template-columns: 100px minmax(0, 220px) auto;
  gap: 10px;
  align-items: center;
}

.pm-cal-v2-reminder-row {
  display: grid;
  grid-template-columns: 140px 1fr 1fr;
  gap: 10px;
  align-items: center;
}

.pm-cal-v2-reminder-row label {
  margin: 0;
  font-size: 0.8rem;
  color: #334155;
  font-weight: 600;
}

.pm-cal-v2-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 4px;
}

.pm-events-sheet {
  width: min(760px, calc(100vw - 34px));
}

.pm-events-sheet-wide {
  width: min(920px, calc(100vw - 34px));
}

.pm-events-sheet-body {
  max-height: min(62vh, 600px);
  overflow: auto;
  display: grid;
  gap: 10px;
  padding-right: 4px;
}

.pm-event-row {
  border: 1px solid #dbe4f2;
  border-radius: 12px;
  padding: 10px 12px;
  background: #f8fafc;
  text-align: left;
  display: grid;
  gap: 6px;
  transition: border-color 0.16s ease, background 0.16s ease;
}

.pm-event-row:hover {
  border-color: rgba(var(--pm-primary-rgb), 0.45);
  background: rgba(var(--pm-primary-rgb), 0.1);
}

.pm-event-row-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.pm-event-row-top strong {
  font-size: 0.92rem;
  color: #1e293b;
}

.pm-event-row-meta {
  font-size: 0.8rem;
  color: #334155;
  font-weight: 600;
}

.pm-event-row-desc {
  font-size: 0.8rem;
  color: #64748b;
}

@media (max-width: 1220px) {
  .pm-cal-v2-top {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .pm-cal-v2-grid-2,
  .pm-cal-v2-grid-3,
  .pm-cal-v2-inline-time,
  .pm-cal-v2-weekdays-picks,
  .pm-cal-v2-rec-options {
    grid-template-columns: 1fr;
  }

  .pm-cal-v2-rec-repeat,
  .pm-cal-v2-yearly-row,
  .pm-cal-v2-range-row,
  .pm-cal-v2-reminder-row {
    grid-template-columns: 1fr;
  }
}
</style>
