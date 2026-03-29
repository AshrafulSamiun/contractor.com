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
          <button class="pm-icon-btn" type="button" aria-label="Notifications">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 1 1 10 0v6Z" />
            </svg>
            <span class="pm-topbar-badge"></span>
          </button>
          <button class="pm-icon-btn" type="button" aria-label="Settings">
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
        <div class="container pm-ops-page pm-announcement-premium-page">
          <section class="pm-announcement-hero">
            <div>
              <div class="pm-announcement-kicker">Communication Module</div>
              <h2 class="pm-announcement-title">Announcement Center</h2>
              <div class="pm-page-subtitle">Broadcast updates, alerts, and operational notices.</div>
            </div>
            <div class="pm-announcement-hero-actions">
              <span class="pm-announcement-chip">{{ currentViewLabel }}</span>
              <div class="pm-announcement-view-controls">
                <button v-if="canCreateAnnouncements" class="btn btn-primary" type="button" @click="switchToForm(true)">New Announcement</button>
                <button class="btn btn-outline-primary" type="button" @click="switchToList">Announcement List</button>
                <button class="btn btn-outline-secondary" type="button" @click="loadAnnouncements">Refresh</button>
              </div>
              <div class="pm-announcement-edit-hint">To edit, open Announcement List and click the Edit button.</div>
            </div>
          </section>

          <div class="pm-announcement-stats">
            <div class="pm-stat-card">
              <div class="pm-stat-label">Published</div>
              <div class="pm-stat-value">{{ publishedCount }}</div>
              <div class="pm-stat-meta">Live updates</div>
            </div>
            <div class="pm-stat-card">
              <div class="pm-stat-label">Drafts</div>
              <div class="pm-stat-value">{{ draftCount }}</div>
              <div class="pm-stat-meta">Pending edits</div>
            </div>
            <div class="pm-stat-card">
              <div class="pm-stat-label">Urgent</div>
              <div class="pm-stat-value">{{ urgentCount }}</div>
              <div class="pm-stat-meta">High priority</div>
            </div>
            <div class="pm-stat-card">
              <div class="pm-stat-label">Pinned</div>
              <div class="pm-stat-value">{{ pinnedCount }}</div>
              <div class="pm-stat-meta">Pinned notices</div>
            </div>
          </div>

          <div v-show="viewMode === 'form'" ref="formCardRef" class="pm-card pm-ops-card pm-announcement-form">
          <div class="pm-card-header">
            <div>
              <h3>{{ form.id ? 'Edit Announcement' : 'Create Announcement' }}</h3>
              <p>Keep teams aligned with scheduled updates and alerts.</p>
            </div>
            <div class="pm-card-actions">
              <button class="btn btn-outline-secondary btn-sm" type="button" @click="downloadCurrentAnnouncement" :disabled="!form.id">Download</button>
              <button class="btn btn-outline-secondary btn-sm" type="button" @click="switchToForm(true)">New</button>
              <button
                v-if="canDeleteAnnouncements"
                class="btn btn-outline-danger btn-sm"
                type="button"
                :disabled="!form.id"
                @click="deleteAnnouncement"
              >
                Delete
              </button>
            </div>
          </div>
          <form class="pm-form-grid" @submit.prevent="saveAnnouncement">
            <div class="pm-form-field">
              <label class="pm-field-label">No</label>
              <input v-model="form.announcement_no" class="form-control" type="text" placeholder="Auto-generated on save" readonly />
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Date-Time</label>
              <input v-model="form.occurred_at" class="form-control" type="datetime-local" />
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Facilities / Property</label>
              <select v-model="form.facility_id" class="form-control">
                <option value="">Select facility/property</option>
                <option v-for="facility in facilities" :key="facility.id" :value="String(facility.id)">
                  {{ facility.facility_name }}
                </option>
              </select>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Recipients <span class="text-danger">*</span></label>
              <select v-model="form.recipient_mode" class="form-control">
                <option value="all">All recipients</option>
                <option value="one">One recipient</option>
                <option value="multiple">Multiple recipients</option>
              </select>
              <div class="pm-help-text">Choose who will receive this announcement.</div>
            </div>
            <div class="pm-form-field" v-if="form.recipient_mode === 'one'">
              <label class="pm-field-label">Select Recipient <span class="text-danger">*</span></label>
              <select v-model="form.recipient_single_id" class="form-control">
                <option value="">{{ filteredRecipients.length ? 'Select recipient' : 'No recipient available' }}</option>
                <option v-for="recipient in filteredRecipients" :key="recipient.id" :value="String(recipient.id)">
                  {{ recipient.recipient_name }}
                </option>
              </select>
              <div class="pm-help-text">{{ recipientFilterHint }}</div>
            </div>
            <div class="pm-form-field full" v-else-if="form.recipient_mode === 'multiple'">
              <label class="pm-field-label">Select Recipients <span class="text-danger">*</span></label>
              <select v-model="form.recipient_ids" class="form-control" multiple size="4">
                <option v-for="recipient in filteredRecipients" :key="recipient.id" :value="String(recipient.id)">
                  {{ recipient.recipient_name }}
                </option>
              </select>
              <div class="pm-help-text">
                {{ filteredRecipients.length ? `Selected ${selectedRecipientCount} recipient(s). Hold Ctrl/Cmd to select multiple recipients.` : 'No recipient available for the current facility filter.' }}
              </div>
            </div>
            <div class="pm-form-field full" v-else>
              <label class="pm-field-label">Recipients</label>
              <input class="form-control" type="text" :value="recipientAllLabel" readonly />
              <div class="pm-help-text">{{ recipientFilterHint }}</div>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Subject *</label>
              <input v-model="form.title" class="form-control" type="text" placeholder="Holiday schedule update" required />
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Priority</label>
              <select v-model="form.priority" class="form-control">
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Audience</label>
              <select v-model="form.audience" class="form-control">
                <option value="all">All Users</option>
                <option value="staff">Staff</option>
                <option value="admins">Admins</option>
                <option value="roles">Role Based</option>
              </select>
            </div>
            <div class="pm-form-field" v-if="form.audience === 'roles'">
              <label class="pm-field-label">Target Roles (comma)</label>
              <input v-model="form.target_roles_input" class="form-control" type="text" placeholder="admin, staff" />
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Status</label>
              <select v-model="form.status" class="form-control">
                <option value="draft">Draft</option>
                <option v-if="canPublishAnnouncements || form.status === 'published'" value="published">Published</option>
                <option value="archived">Archived</option>
              </select>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Requires Approval</label>
              <select v-model="form.requires_approval" class="form-control">
                <option :value="false">No</option>
                <option :value="true">Yes</option>
              </select>
            </div>
            <div class="pm-form-field" v-if="form.requires_approval">
              <label class="pm-field-label">Approval Status</label>
              <select v-model="form.approval_status" class="form-control">
                <option value="">Pending</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Publish At</label>
              <input v-model="form.publish_at" class="form-control" type="datetime-local" />
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Expires At</label>
              <input v-model="form.expires_at" class="form-control" type="datetime-local" />
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Pinned</label>
              <select v-model="form.pinned" class="form-control">
                <option :value="true">Yes</option>
                <option :value="false">No</option>
              </select>
            </div>
            <div class="pm-form-field full">
              <label class="pm-field-label">Detail *</label>
              <div class="pm-editor-toolbar">
                <button class="pm-editor-btn" type="button" @click="wrapSelection('<strong>', '</strong>')">B</button>
                <button class="pm-editor-btn" type="button" @click="wrapSelection('<em>', '</em>')">I</button>
                <button class="pm-editor-btn" type="button" @click="wrapSelection('<u>', '</u>')">U</button>
                <button class="pm-editor-btn" type="button" @click="wrapSelection('<ul><li>', '</li></ul>')">List</button>
                <button class="pm-editor-btn" type="button" @click="wrapSelection('<ol><li>', '</li></ol>')">1. List</button>
                <button class="pm-editor-btn" type="button" @click="wrapSelection('<blockquote>', '</blockquote>')">Quote</button>
                <button class="pm-editor-btn" type="button" @click="togglePreview">
                  {{ showPreview ? 'Edit' : 'Preview' }}
                </button>
              </div>
              <textarea
                v-if="!showPreview"
                ref="bodyRef"
                v-model="form.body"
                class="form-control"
                rows="5"
                placeholder="Write your announcement..."
                required
              ></textarea>
              <div v-else class="pm-editor-preview"><pre class="mb-0">{{ form.body || 'No content' }}</pre></div>
            </div>
            <div class="pm-form-field full">
              <label class="pm-field-label">Required Follow-up Task(s)</label>
              <div class="pm-required-actions-wrap">
                <table class="table pm-table mb-0">
                  <thead>
                    <tr>
                      <th style="width: 60%;">Task Description</th>
                      <th style="width: 35%;">Due Date-Time</th>
                      <th style="width: 5%;"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(actionRow, index) in form.required_actions" :key="index">
                      <td>
                        <input
                          v-model="actionRow.action"
                          class="form-control"
                          type="text"
                          :placeholder="`Task ${index + 1}`"
                        />
                      </td>
                      <td>
                        <input v-model="actionRow.due_at" class="form-control" type="datetime-local" />
                      </td>
                      <td>
                        <button class="pm-icon-btn" type="button" @click="removeRequiredAction(index)" title="Remove row">x</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div class="pm-required-actions-controls">
                  <button class="btn btn-outline-primary btn-sm" type="button" @click="addRequiredAction">+ Add Task</button>
                </div>
              </div>
            </div>
            <div class="pm-form-field full">
              <label class="pm-field-label">Attachments</label>
              <div
                class="pm-dropzone"
                :class="{ 'is-dragging': isDragging }"
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
                @drop.prevent="handleDrop"
              >
                <input class="form-control" type="file" multiple :disabled="!canManageCurrentAnnouncement || saving" @change="handleAttachments" />
                <div class="pm-dropzone-text">Drag & drop files here or click to upload</div>
              </div>
              <div class="pm-help-text">Allowed: PDF, PNG, JPG, DOCX, XLSX (max 5MB each).</div>
              <div class="pm-attachment-list" v-if="attachments.length">
                <div v-for="file in attachments" :key="file.id || file.name" class="pm-attachment-item">
                  <span>{{ file.original_name || file.name }}</span>
                  <div class="pm-attachment-actions">
                    <button
                      v-if="file.id"
                      class="pm-icon-btn"
                      type="button"
                      @click="downloadAttachment(file)"
                      title="Download"
                    >
                      Download
                    </button>
                    <button
                      v-if="file.id"
                      class="pm-icon-btn"
                      type="button"
                      @click="previewAttachment(file)"
                      title="Preview"
                    >
                      Preview
                    </button>
                    <button
                      v-if="!file.id ? canManageCurrentAnnouncement : canDeleteAnnouncements"
                      class="pm-icon-btn"
                      type="button"
                      @click="removeAttachment(file)"
                      title="Remove"
                    >
                      x
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="pm-form-actions">
              <button class="btn btn-primary" type="submit" :disabled="saving || !canManageCurrentAnnouncement">
                {{ saving ? 'Saving...' : form.id ? 'Save Changes' : 'Save Announcement' }}
              </button>
            </div>
          </form>
        </div>

          <div v-show="viewMode === 'list'" ref="listCardRef" class="pm-card pm-announcement-list-shell">
          <div class="pm-card-header">
            <div>
              <h3>Announcement Library</h3>
              <p>Track engagement and manage publication states.</p>
            </div>
            <div class="pm-card-actions">
              <button class="btn btn-outline-primary btn-sm" type="button" @click="loadAnnouncements">Refresh</button>
              <button v-if="canCreateAnnouncements" class="btn btn-primary btn-sm" type="button" @click="switchToForm(true)">New</button>
            </div>
          </div>
          <div class="pm-filter-grid">
            <div class="pm-form-field">
              <label class="pm-field-label">Search</label>
              <input v-model="filters.q" class="form-control" type="text" placeholder="Search announcements..." />
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Status</label>
              <select v-model="filters.status" class="form-control">
                <option value="">All</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
              </select>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Priority</label>
              <select v-model="filters.priority" class="form-control">
                <option value="">All</option>
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Audience</label>
              <select v-model="filters.audience" class="form-control">
                <option value="">All</option>
                <option value="all">All Users</option>
                <option value="staff">Staff</option>
                <option value="admins">Admins</option>
                <option value="roles">Role Based</option>
              </select>
            </div>
            <div class="pm-form-field">
              <label class="pm-field-label">Active Only</label>
              <select v-model="filters.active" class="form-control">
                <option :value="false">No</option>
                <option :value="true">Yes</option>
              </select>
            </div>
            <div class="pm-form-actions pm-filter-actions">
              <button class="btn btn-outline-secondary btn-sm" type="button" @click="resetFilters">Reset</button>
            </div>
          </div>

          <div class="pm-table-wrap">
            <table class="table pm-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Date-Time</th>
                  <th>Facilities / Property</th>
                  <th>Recipient(s)</th>
                  <th>Subject</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Required Follow-up Task(s)</th>
                  <th>Read</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in announcements" :key="item.id">
                  <td>{{ item.announcement_no || '-' }}</td>
                  <td>{{ formatDateTime(item.occurred_at || item.publish_at) }}</td>
                  <td>{{ item.facility_name || '-' }}</td>
                  <td>{{ recipientSummary(item) }}</td>
                  <td>
                    <div class="pm-title-cell">
                      <span class="pm-announcement-pin" v-if="item.pinned" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                          <path d="m12 3 2.6 5.3 5.9.9-4.3 4.2 1 5.9-5.2-2.7-5.2 2.7 1-5.9-4.3-4.2 5.9-.9L12 3Z" />
                        </svg>
                      </span>
                      {{ item.title }}
                    </div>
                  </td>
                  <td>
                    <span class="pm-status-pill" :class="priorityClass(item.priority)">
                      {{ item.priority }}
                    </span>
                  </td>
                  <td>
                    <span class="pm-status-pill" :class="statusClass(item.status)">
                      {{ item.status }}
                    </span>
                  </td>
                  <td>{{ requiredActionsCount(item) }}</td>
                  <td>
                    <span class="pm-status-pill" :class="item.is_read ? 'active' : 'warning'">
                      {{ item.is_read ? 'Read' : 'Unread' }} ({{ item.read_count || 0 }})
                    </span>
                  </td>
                  <td class="pm-table-actions">
                    <button
                      v-if="canEditAnnouncements"
                      class="btn btn-sm btn-outline-primary pm-edit-action-btn"
                      type="button"
                      @click="editAnnouncement(item)"
                      title="Edit"
                    >
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 20h4l10-10-4-4L4 16v4Z" />
                        <path d="m14 6 4 4 2-2-4-4-2 2Z" />
                      </svg>
                      <span>Edit</span>
                    </button>
                    <button class="pm-icon-btn" type="button" @click="markRead(item)" title="Mark read">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                    <button v-if="canPublishAnnouncements" class="pm-icon-btn" type="button" @click="publishAnnouncement(item)" title="Publish">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3v12" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                        <path d="m7 8 5-5 5 5" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 21h14" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                      </svg>
                    </button>
                    <button v-if="canPublishAnnouncements" class="pm-icon-btn" type="button" @click="archiveAnnouncement(item)" title="Archive">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7h16" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                        <path d="M5 7v12h14V7" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                        <path d="M9 11h6" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                      </svg>
                    </button>
                    <button v-if="canEditAnnouncements" class="pm-icon-btn" type="button" @click="submitForApproval(item)" title="Submit">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3v12" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                        <path d="m7 10 5-5 5 5" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                    <button v-if="canApproveAnnouncements" class="pm-icon-btn" type="button" @click="approveAnnouncement(item)" title="Approve">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                    <button v-if="canApproveAnnouncements" class="pm-icon-btn" type="button" @click="rejectAnnouncement(item)" title="Reject">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="announcements.length === 0" class="pm-empty-state">
              No announcements found for the selected filters.
            </div>
          </div>
          </div>
        </div>
      </section>
    </div>

    <div v-if="sidebarOpen" class="pm-sidebar-overlay" @click="toggleSidebar"></div>

    <div v-if="previewOpen" class="pm-modal-backdrop" @click="closePreview">
      <div class="pm-modal-card" @click.stop>
        <div class="pm-modal-header">
          <div class="pm-modal-title">{{ previewName }}</div>
          <div class="pm-modal-actions" v-if="previewType.startsWith('image/')">
            <button class="btn btn-outline-secondary btn-sm" type="button" @click="zoomOut">-</button>
            <button class="btn btn-outline-secondary btn-sm" type="button" @click="resetZoom">Reset</button>
            <button class="btn btn-outline-secondary btn-sm" type="button" @click="zoomIn">+</button>
          </div>
          <button class="pm-icon-btn" type="button" @click="closePreview">x</button>
        </div>
        <div class="pm-modal-body">
          <div v-if="previewType.startsWith('image/')" class="pm-zoom-wrap">
            <img :src="previewUrl" alt="Attachment preview" :style="{ transform: `scale(${zoom})` }" />
          </div>
          <iframe v-else-if="previewType === 'application/pdf'" :src="previewUrl"></iframe>
          <div v-else class="pm-empty-state">No preview available. Please download.</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch, nextTick } from 'vue'
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
const saving = ref(false)
const showPreview = ref(false)
const bodyRef = ref(null)
const formCardRef = ref(null)
const listCardRef = ref(null)
const attachments = ref([])
const previewOpen = ref(false)
const previewUrl = ref('')
const previewType = ref('')
const previewName = ref('')
const zoom = ref(1)
const isDragging = ref(false)
const viewMode = ref('form')

const announcements = ref([])
const facilities = ref([])
const recipients = ref([])
const filters = reactive({
  q: '',
  status: '',
  priority: '',
  audience: '',
  active: true,
})

const publishedCount = computed(() => announcements.value.filter((item) => item.status === 'published').length)
const draftCount = computed(() => announcements.value.filter((item) => item.status === 'draft').length)
const urgentCount = computed(() => announcements.value.filter((item) => item.priority === 'urgent').length)
const pinnedCount = computed(() => announcements.value.filter((item) => item.pinned).length)
const selectedRecipientCount = computed(() => (Array.isArray(form.recipient_ids) ? form.recipient_ids.length : 0))

const selectedFacilityName = computed(() => {
  const id = Number(form.facility_id)
  if (!Number.isFinite(id) || id <= 0) return ''
  const facility = facilities.value.find((item) => Number(item.id) === id)
  return String(facility?.facility_name || '').trim().toLowerCase()
})

const filteredRecipients = computed(() => {
  const source = Array.isArray(recipients.value) ? recipients.value : []
  if (!selectedFacilityName.value) return source

  return source.filter((recipient) => {
    const recipientFacilityName = String(recipient?.facility_name || '').trim().toLowerCase()
    return recipientFacilityName !== '' && recipientFacilityName === selectedFacilityName.value
  })
})

const filteredRecipientIdSet = computed(() =>
  new Set(filteredRecipients.value.map((recipient) => String(recipient.id)))
)

const recipientFilterHint = computed(() =>
  selectedFacilityName.value
    ? 'Recipients are filtered by the selected facility.'
    : 'Recipients from all facilities are available.'
)

const recipientAllLabel = computed(() => `All active recipients (${filteredRecipients.value.length})`)

const emptyRequiredActions = () =>
  Array.from({ length: 4 }, () => ({
    action: '',
    due_at: '',
  }))

const form = reactive({
  id: null,
  announcement_no: '',
  occurred_at: '',
  facility_id: '',
  recipient_mode: 'all',
  recipient_single_id: '',
  recipient_ids: [],
  required_actions: emptyRequiredActions(),
  title: '',
  body: '',
  priority: 'normal',
  status: 'draft',
  audience: 'all',
  target_roles_input: '',
  requires_approval: false,
  approval_status: '',
  publish_at: '',
  expires_at: '',
  pinned: false,
})
const currentViewLabel = computed(() => {
  if (viewMode.value === 'list') return 'List View'
  return form.id ? 'Editing' : 'Create View'
})
const canReadAnnouncements = computed(() => hasUserPermission(authState.user, 'announcements', 'read'))
const canCreateAnnouncements = computed(() => hasUserPermission(authState.user, 'announcements', 'create'))
const canEditAnnouncements = computed(() => hasUserPermission(authState.user, 'announcements', 'edit'))
const canDeleteAnnouncements = computed(() => hasUserPermission(authState.user, 'announcements', 'delete'))
const canPublishAnnouncements = computed(() => hasUserPermission(authState.user, 'announcements', 'publish'))
const canApproveAnnouncements = computed(() => hasUserPermission(authState.user, 'announcements', 'approve'))
const canManageCurrentAnnouncement = computed(() => (form.id ? canEditAnnouncements.value : canCreateAnnouncements.value))

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
    previewOpen.value = false
  }
}

const userInitials = computed(() => {
  if (!userName.value) return 'U'
  const parts = userName.value.trim().split(' ')
  const first = parts[0]?.[0] || 'U'
  const last = parts[1]?.[0] || ''
  return (first + last).toUpperCase()
})

const formatDateTime = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  return date.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const pad = (value) => String(value).padStart(2, '0')

const toIsoOrNull = (value) => {
  if (!value) return null
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return null
  return date.toISOString()
}

const toLocalDateTimeInput = (value) => {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

const nowLocalDateTimeInput = () => toLocalDateTimeInput(new Date().toISOString())

const parseRoleList = (value) =>
  String(value || '')
    .split(',')
    .map((item) => item.trim().toLowerCase())
    .filter(Boolean)

const statusClass = (status) => {
  if (status === 'published') return 'active'
  if (status === 'archived') return 'warning'
  return 'neutral'
}

const priorityClass = (priority) => {
  if (priority === 'urgent') return 'danger'
  if (priority === 'high') return 'warning'
  if (priority === 'low') return 'neutral'
  return 'active'
}

const sanitizeRecipientSelection = () => {
  const allowed = filteredRecipientIdSet.value

  if (form.recipient_single_id && !allowed.has(String(form.recipient_single_id))) {
    form.recipient_single_id = ''
  }

  if (Array.isArray(form.recipient_ids)) {
    form.recipient_ids = form.recipient_ids
      .map((id) => String(id))
      .filter((id) => allowed.has(id))
  }
}

const resetForm = () => {
  form.id = null
  form.announcement_no = ''
  form.occurred_at = nowLocalDateTimeInput()
  form.facility_id = ''
  form.recipient_mode = 'all'
  form.recipient_single_id = ''
  form.recipient_ids = []
  form.required_actions = emptyRequiredActions()
  form.title = ''
  form.body = ''
  form.priority = 'normal'
  form.status = 'draft'
  form.audience = 'all'
  form.target_roles_input = ''
  form.requires_approval = false
  form.approval_status = ''
  form.publish_at = ''
  form.expires_at = ''
  form.pinned = false
  showPreview.value = false
  attachments.value = []
}

const scrollToSection = (sectionRef) => {
  nextTick(() => {
    if (sectionRef?.value?.scrollIntoView) {
      sectionRef.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  })
}

const switchToForm = (createNew = false) => {
  if (createNew) {
    resetForm()
  }
  viewMode.value = 'form'
  scrollToSection(formCardRef)
}

const switchToList = () => {
  viewMode.value = 'list'
  loadAnnouncements()
  scrollToSection(listCardRef)
}

const editAnnouncement = (item) => {
  if (!canEditAnnouncements.value) {
    setFlash('You do not have permission to edit announcements.', 'warning', 2500)
    return
  }
  form.id = item.id
  form.announcement_no = item.announcement_no || ''
  form.occurred_at = toLocalDateTimeInput(item.occurred_at || item.publish_at || item.created_at)
  form.facility_id = item.facility_id ? String(item.facility_id) : ''
  form.recipient_mode = item.recipient_mode || 'all'
  form.recipient_ids = Array.isArray(item.recipient_ids) ? item.recipient_ids.map((id) => String(id)) : []
  form.recipient_single_id = form.recipient_ids[0] || ''
  form.required_actions = Array.isArray(item.required_actions) && item.required_actions.length
    ? item.required_actions.map((row) => ({
      action: row?.action || '',
      due_at: toLocalDateTimeInput(row?.due_at || ''),
    }))
    : emptyRequiredActions()
  form.title = item.title
  form.body = item.body
  form.priority = item.priority || 'normal'
  form.status = item.status || 'draft'
  form.audience = item.audience || 'all'
  form.target_roles_input = Array.isArray(item.target_roles) ? item.target_roles.join(', ') : ''
  form.requires_approval = !!item.requires_approval
  form.approval_status = item.approval_status || ''
  form.publish_at = toLocalDateTimeInput(item.publish_at)
  form.expires_at = toLocalDateTimeInput(item.expires_at)
  form.pinned = !!item.pinned
  showPreview.value = false
  sanitizeRecipientSelection()
  viewMode.value = 'form'
  scrollToSection(formCardRef)
  setFlash('Edit mode ready. Update fields and click Save Changes.', 'success', 2200)
  loadAttachments(item.id)
}

const buildPayload = () => {
  const recipientMode = form.recipient_mode || 'all'
  const multipleRecipientIds = Array.isArray(form.recipient_ids)
    ? form.recipient_ids.map((id) => Number(id)).filter((id) => Number.isFinite(id) && id > 0)
    : []

  const recipientIds = recipientMode === 'all'
    ? []
    : recipientMode === 'one'
      ? (form.recipient_single_id ? [Number(form.recipient_single_id)] : [])
      : multipleRecipientIds

  const requiredActions = Array.isArray(form.required_actions)
    ? form.required_actions
      .map((row) => ({
        action: String(row?.action || '').trim(),
        due_at: toIsoOrNull(row?.due_at || ''),
      }))
      .filter((row) => row.action || row.due_at)
    : []

  return {
    announcement_no: form.announcement_no || null,
    occurred_at: toIsoOrNull(form.occurred_at),
    facility_id: form.facility_id ? Number(form.facility_id) : null,
    recipient_mode: recipientMode,
    recipient_ids: recipientIds,
    required_actions: requiredActions,
    title: form.title,
    body: form.body,
    priority: form.priority,
    status: form.status,
    audience: form.audience,
    target_roles: form.audience === 'roles' ? parseRoleList(form.target_roles_input) : [],
    requires_approval: form.requires_approval,
    approval_status: form.approval_status || null,
    publish_at: toIsoOrNull(form.publish_at),
    expires_at: toIsoOrNull(form.expires_at),
    pinned: form.pinned,
  }
}

const addRequiredAction = () => {
  form.required_actions.push({
    action: '',
    due_at: '',
  })
}

const removeRequiredAction = (index) => {
  form.required_actions.splice(index, 1)
  if (!form.required_actions.length) {
    form.required_actions = emptyRequiredActions()
  }
}

const recipientSummary = (item) => {
  const mode = item?.recipient_mode || 'all'
  if (mode === 'all') return 'All'

  const labels = Array.isArray(item?.recipient_labels) ? item.recipient_labels.filter(Boolean) : []
  if (mode === 'one') {
    return labels[0] || 'One recipient'
  }

  if (!labels.length) {
    const count = Array.isArray(item?.recipient_ids) ? item.recipient_ids.length : 0
    return count > 0 ? `${count} selected` : '-'
  }

  return labels.join(', ')
}

const requiredActionsCount = (item) => {
  const rows = Array.isArray(item?.required_actions) ? item.required_actions : []
  return rows.filter((row) => String(row?.action || '').trim() !== '').length
}

const loadAnnouncementMeta = async () => {
  if (!canReadAnnouncements.value) {
    facilities.value = []
    recipients.value = []
    return
  }

  try {
    const { data } = await client.get('/announcements/meta')
    facilities.value = data?.data?.facilities || []
    recipients.value = data?.data?.recipients || []
    sanitizeRecipientSelection()
  } catch {
    facilities.value = []
    recipients.value = []
    sanitizeRecipientSelection()
  }
}

const downloadCurrentAnnouncement = async () => {
  if (!form.id) {
    setFlash('Select an announcement first to download.', 'warning', 2500)
    return
  }

  const requiredActions = Array.isArray(form.required_actions)
    ? form.required_actions
      .map((row, index) => {
        const action = String(row?.action || '').trim()
        const dueAt = String(row?.due_at || '').trim()
        if (!action && !dueAt) return null
        return `${index + 1}. ${action || '-'} | Due: ${dueAt || '-'}`
      })
      .filter(Boolean)
    : []

  const lines = [
    `No: ${form.announcement_no || '-'}`,
    `Date-Time: ${form.occurred_at || '-'}`,
    `Facilities / Property: ${resolveFacilityName(form.facility_id) || '-'}`,
    `Recipient(s): ${resolveSelectedRecipientLabel()}`,
    `Subject: ${form.title || '-'}`,
    '',
    'Detail:',
    form.body || '-',
    '',
    'Required Follow-up Task(s):',
    ...(requiredActions.length ? requiredActions : ['-']),
  ]

  const blob = new Blob([lines.join('\n')], { type: 'text/plain;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `${form.announcement_no || `announcement-${form.id}`}.txt`
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}

const resolveFacilityName = (facilityId) => {
  const id = Number(facilityId)
  if (!Number.isFinite(id) || id <= 0) return ''
  const selected = facilities.value.find((facility) => Number(facility.id) === id)
  return selected?.facility_name || ''
}

const resolveSelectedRecipientLabel = () => {
  if (form.recipient_mode === 'all') return recipientAllLabel.value

  if (form.recipient_mode === 'one') {
    const oneId = Number(form.recipient_single_id)
    if (!Number.isFinite(oneId) || oneId <= 0) return '-'
    const selected = recipients.value.find((recipient) => Number(recipient.id) === oneId)
    return selected?.recipient_name || 'One recipient'
  }

  const ids = Array.isArray(form.recipient_ids)
    ? form.recipient_ids.map((id) => Number(id)).filter((id) => Number.isFinite(id) && id > 0)
    : []

  const labels = recipients.value
    .filter((recipient) => ids.includes(Number(recipient.id)))
    .map((recipient) => recipient.recipient_name)

  return labels.length ? labels.join(', ') : (ids.length ? `${ids.length} selected` : '-')
}

const wrapSelection = (before, after) => {
  if (!bodyRef.value) return
  const textarea = bodyRef.value
  const start = textarea.selectionStart || 0
  const end = textarea.selectionEnd || 0
  const value = form.body || ''
  form.body = value.slice(0, start) + before + value.slice(start, end) + after + value.slice(end)
  nextTick(() => {
    textarea.focus()
    textarea.selectionStart = start + before.length
    textarea.selectionEnd = end + before.length
  })
}

const togglePreview = () => {
  showPreview.value = !showPreview.value
}

const loadAttachments = async (id) => {
  if (!id) return
  const { data } = await client.get(`/announcements/${id}/attachments`)
  attachments.value = data?.data || []
}

const handleAttachments = async (event) => {
  if (!canManageCurrentAnnouncement.value) {
    setFlash('You do not have permission to edit attachments.', 'warning', 2500)
    return
  }
  const files = Array.from(event.target.files || [])
  if (!files.length) return
  if (!form.id) {
    attachments.value = [...attachments.value, ...files]
    return
  }
  const formData = new FormData()
  files.forEach((file) => formData.append('files[]', file))
  await client.post(`/announcements/${form.id}/attachments`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  await loadAttachments(form.id)
  event.target.value = ''
}

const handleDrop = async (event) => {
  isDragging.value = false
  if (!canManageCurrentAnnouncement.value) {
    setFlash('You do not have permission to edit attachments.', 'warning', 2500)
    return
  }
  const files = Array.from(event.dataTransfer?.files || [])
  if (!files.length) return
  if (!form.id) {
    attachments.value = [...attachments.value, ...files]
    return
  }
  const formData = new FormData()
  files.forEach((file) => formData.append('files[]', file))
  await client.post(`/announcements/${form.id}/attachments`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  await loadAttachments(form.id)
}

const handleDragOver = (event) => {
  event.preventDefault()
  isDragging.value = true
}

const handleDragLeave = () => {
  isDragging.value = false
}

const removeAttachment = async (file) => {
  if (!file.id) {
    if (!canManageCurrentAnnouncement.value) {
      setFlash('You do not have permission to edit attachments.', 'warning', 2500)
      return
    }
    attachments.value = attachments.value.filter((item) => item !== file)
    return
  }
  if (!canDeleteAnnouncements.value) {
    setFlash('You do not have permission to delete attachments.', 'warning', 2500)
    return
  }
  await client.delete(`/announcements/${form.id}/attachments/${file.id}`)
  await loadAttachments(form.id)
}

const previewAttachment = async (file) => {
  if (!file.id) return
  const response = await client.get(`/announcements/${form.id}/attachments/${file.id}`, { responseType: 'blob' })
  const blob = new Blob([response.data], { type: file.mime || 'application/octet-stream' })
  const url = URL.createObjectURL(blob)
  previewUrl.value = url
  previewType.value = file.mime || ''
  previewName.value = file.original_name || 'attachment'
  zoom.value = 1
  previewOpen.value = true
}

const closePreview = () => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  previewUrl.value = ''
  previewType.value = ''
  previewName.value = ''
  zoom.value = 1
  previewOpen.value = false
}

const zoomIn = () => {
  zoom.value = Math.min(zoom.value + 0.2, 3)
}

const zoomOut = () => {
  zoom.value = Math.max(zoom.value - 0.2, 0.6)
}

const resetZoom = () => {
  zoom.value = 1
}

const downloadAttachment = async (file) => {
  if (!file.id) return
  const response = await client.get(`/announcements/${form.id}/attachments/${file.id}`, { responseType: 'blob' })
  const blob = new Blob([response.data], { type: file.mime || 'application/octet-stream' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = file.original_name || 'attachment'
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}

const saveAnnouncement = async () => {
  if (!canManageCurrentAnnouncement.value) {
    setFlash('You do not have permission to save announcements.', 'warning', 2500)
    return
  }

  const selectedTargetRoles = parseRoleList(form.target_roles_input)
  if (form.audience === 'roles' && selectedTargetRoles.length === 0) {
    setFlash('For role-based audience, add at least one target role.', 'warning', 3000)
    return
  }

  if (form.recipient_mode === 'one' && !form.recipient_single_id) {
    setFlash('Recipient mode "One" requires exactly one recipient.', 'warning', 3000)
    return
  }

  if (form.recipient_mode === 'multiple' && (!Array.isArray(form.recipient_ids) || form.recipient_ids.length < 1)) {
    setFlash('Recipient mode "Multiple" requires at least one recipient.', 'warning', 3000)
    return
  }

  const hasInvalidRequiredAction = Array.isArray(form.required_actions) && form.required_actions.some((row) => {
    const action = String(row?.action || '').trim()
    const dueAt = String(row?.due_at || '').trim()
    return (action && !dueAt) || (!action && dueAt)
  })
  if (hasInvalidRequiredAction) {
    setFlash('Each follow-up task row must include both task description and due date-time.', 'warning', 3000)
    return
  }

  if (form.status === 'published' && !canPublishAnnouncements.value) {
    setFlash('You do not have permission to publish announcements.', 'warning', 2500)
    return
  }

  if (form.requires_approval && form.status === 'published' && form.approval_status !== 'approved') {
    setFlash('Approval is required before publishing this announcement.', 'warning', 3000)
    return
  }

  const wasEdit = !!form.id
  saving.value = true
  try {
    if (form.id) {
      await client.put(`/announcements/${form.id}`, buildPayload())
    } else {
      const { data } = await client.post('/announcements', buildPayload())
      form.id = data?.data?.id || null
    }
    if (form.id && attachments.value.some((f) => !f.id)) {
      const formData = new FormData()
      attachments.value.filter((f) => !f.id).forEach((file) => formData.append('files[]', file))
      await client.post(`/announcements/${form.id}/attachments`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    await loadAnnouncements()
    if (form.id) {
      await loadAttachments(form.id)
    }
    setFlash(wasEdit ? 'Announcement updated successfully.' : 'Announcement created successfully.', 'success', 2200)
    resetForm()
    switchToList()
  } finally {
    saving.value = false
  }
}

const deleteAnnouncement = async () => {
  if (!canDeleteAnnouncements.value) {
    setFlash('You do not have permission to delete announcements.', 'warning', 2500)
    return
  }
  if (!form.id) return
  await client.delete(`/announcements/${form.id}`)
  await loadAnnouncements()
  setFlash('Announcement deleted successfully.', 'success', 2200)
  resetForm()
  switchToList()
}

const markRead = async (item) => {
  if (!canReadAnnouncements.value) return
  await client.post(`/announcements/${item.id}/read`)
  await loadAnnouncements()
}

const publishAnnouncement = async (item) => {
  if (!canPublishAnnouncements.value) {
    setFlash('You do not have permission to publish announcements.', 'warning', 2500)
    return
  }
  await client.post(`/announcements/${item.id}/publish`)
  await loadAnnouncements()
}

const archiveAnnouncement = async (item) => {
  if (!canPublishAnnouncements.value) {
    setFlash('You do not have permission to archive announcements.', 'warning', 2500)
    return
  }
  await client.post(`/announcements/${item.id}/archive`)
  await loadAnnouncements()
}

const submitForApproval = async (item) => {
  if (!canEditAnnouncements.value) {
    setFlash('You do not have permission to submit announcements.', 'warning', 2500)
    return
  }
  await client.post(`/announcements/${item.id}/submit`)
  await loadAnnouncements()
}

const approveAnnouncement = async (item) => {
  if (!canApproveAnnouncements.value) {
    setFlash('You do not have permission to approve announcements.', 'warning', 2500)
    return
  }
  await client.post(`/announcements/${item.id}/approve`)
  await loadAnnouncements()
}

const rejectAnnouncement = async (item) => {
  if (!canApproveAnnouncements.value) {
    setFlash('You do not have permission to reject announcements.', 'warning', 2500)
    return
  }
  await client.post(`/announcements/${item.id}/reject`)
  await loadAnnouncements()
}

const loadAnnouncements = async () => {
  if (!canReadAnnouncements.value) {
    announcements.value = []
    return
  }

  const params = {
    q: filters.q || undefined,
    status: filters.status || undefined,
    priority: filters.priority || undefined,
    audience: filters.audience || undefined,
    active: filters.active ? '1' : undefined,
  }
  try {
    const { data } = await client.get('/announcements', { params })
    announcements.value = data?.data || []
  } catch {
    announcements.value = []
  }
}

const resetFilters = () => {
  filters.q = ''
  filters.status = ''
  filters.priority = ''
  filters.audience = ''
  filters.active = true
  loadAnnouncements()
}

watch(
  () => form.audience,
  (value) => {
    if (value !== 'roles') {
      form.target_roles_input = ''
    }
  }
)

watch(
  () => form.requires_approval,
  (required) => {
    if (!required) {
      form.approval_status = ''
    }
  }
)

watch(
  () => form.recipient_mode,
  (mode) => {
    if (mode === 'all') {
      form.recipient_single_id = ''
      form.recipient_ids = []
      return
    }

    if (mode === 'one') {
      sanitizeRecipientSelection()
      const one = form.recipient_single_id || (Array.isArray(form.recipient_ids) ? form.recipient_ids[0] : '')
      const normalizedOne = one ? String(one) : ''
      form.recipient_single_id = filteredRecipientIdSet.value.has(normalizedOne) ? normalizedOne : ''
      form.recipient_ids = form.recipient_single_id ? [String(form.recipient_single_id)] : []
      return
    }

    if (mode === 'multiple') {
      sanitizeRecipientSelection()
      if (form.recipient_single_id) {
        const value = String(form.recipient_single_id)
        const current = Array.isArray(form.recipient_ids) ? [...form.recipient_ids] : []
        if (!current.includes(value) && filteredRecipientIdSet.value.has(value)) {
          current.push(value)
        }
        form.recipient_ids = current
      }
      form.recipient_single_id = ''
    }
  }
)

watch(
  () => form.recipient_single_id,
  (value) => {
    if (form.recipient_mode !== 'one') return
    const normalized = value ? String(value) : ''
    form.recipient_ids = normalized && filteredRecipientIdSet.value.has(normalized) ? [normalized] : []
  }
)

watch(
  () => form.facility_id,
  () => {
    sanitizeRecipientSelection()
  }
)

watch(sidebarOpen, (value) => {
  document.body.classList.toggle('pm-no-scroll', value)
})

watch(
  () => ({ ...filters }),
  () => loadAnnouncements(),
  { deep: true }
)

onMounted(() => {
  userName.value = authState.user?.name || authState.user?.username || authState.user?.email || 'User'
  resetForm()
  loadAnnouncementMeta()
  loadAnnouncements()
  window.addEventListener('click', closeUserMenu)
  window.addEventListener('keydown', handleEsc)
})

onUnmounted(() => {
  window.removeEventListener('click', closeUserMenu)
  window.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.pm-announcement-premium-page {
  display: grid;
  gap: 16px;
  padding-bottom: 24px;
}

.pm-announcement-hero {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 14px;
  align-items: start;
  border: 1px solid #d9e3ff;
  border-radius: 18px;
  padding: 16px 18px;
  background:
    radial-gradient(circle at 92% 14%, rgba(37, 99, 235, 0.16), transparent 52%),
    linear-gradient(180deg, #f8fbff 0%, #edf4ff 100%);
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
  animation: pmAnnFadeUp 0.32s ease both;
}

.pm-announcement-kicker {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #1d4ed8;
  font-weight: 700;
  margin-bottom: 4px;
}

.pm-announcement-title {
  margin: 0;
  font-size: 1.52rem;
  color: #0f172a;
}

.pm-announcement-hero-actions {
  display: grid;
  justify-items: end;
  gap: 10px;
}

.pm-announcement-chip {
  border-radius: 999px;
  padding: 7px 12px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #fff;
  background: linear-gradient(120deg, #2563eb 0%, #0ea5e9 100%);
  box-shadow: 0 10px 20px rgba(37, 99, 235, 0.26);
}

.pm-announcement-view-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
}

.pm-announcement-edit-hint {
  font-size: 0.8rem;
  color: #475569;
}

.pm-announcement-form,
.pm-announcement-list-shell {
  border: 1px solid #dbe5f6;
  border-radius: 16px;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
  animation: pmAnnFadeUp 0.32s ease both;
}

.pm-announcement-list-shell .pm-table-wrap {
  border: 1px solid #dbe5f6;
  border-radius: 14px;
  overflow: auto;
  max-height: calc(100vh - 310px);
}

.pm-announcement-list-shell .pm-table-wrap thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  background: #f8fbff;
}

.pm-edit-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-right: 4px;
}

.pm-edit-action-btn svg {
  width: 14px;
  height: 14px;
  fill: currentColor;
}

@media (max-width: 992px) {
  .pm-announcement-hero {
    grid-template-columns: 1fr;
  }

  .pm-announcement-hero-actions,
  .pm-announcement-view-controls {
    justify-items: start;
    justify-content: flex-start;
  }

  .pm-announcement-list-shell .pm-table-wrap {
    max-height: none;
  }
}

@keyframes pmAnnFadeUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
