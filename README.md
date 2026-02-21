# NavCRM

A **multi-tenant CRM** (Customer Relationship Management) application built with **Laravel 12** and **Blade** templates, featuring a RESTful JSON API and a traditional server-rendered web interface.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade templates + Bootstrap 5 |
| Styling | Bootstrap 5 + Bootstrap Icons + custom NavCRM theme |
| Database | MySQL (single-database multi-tenancy) |
| Web Auth | Laravel session-based authentication |
| API Auth | Laravel Sanctum (token-based) |
| Roles | Spatie Laravel Permission |
| PDF | barryvdh/laravel-dompdf |

---

## Quick Start

### Option A — XAMPP (Recommended for local development)

> **Prerequisites:** XAMPP is installed and the project is already inside `htdocs\navcrm`.
> Start **Apache** and **MySQL** in the XAMPP Control Panel before proceeding.

```bash
# 1. Install PHP dependencies (use the XAMPP PHP binary or the one on your PATH)
php composer.phar install
# or if composer is globally available:
composer install

# 2. Install Node dependencies and build assets
npm install && npm run build

# 3. Configure environment
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set the database to match XAMPP MySQL defaults:

```dotenv
APP_URL=http://localhost/navcrm/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=navcrm
DB_USERNAME=root
DB_PASSWORD=
```

> Create the `navcrm` database in phpMyAdmin (`http://localhost/phpmyadmin`) first.

```bash
# 4. Run migrations and seed (all demo data included)
php artisan migrate
php artisan db:seed

# 5. Create storage symlink
php artisan storage:link
```

Then open **http://localhost/navcrm/public** in your browser.

#### Optional: Virtual Host (clean URL without `/public`)

1. Edit `C:\xampp8\apache\conf\extra\httpd-vhosts.conf` and append:

```apache
<VirtualHost *:80>
    ServerName navcrm.test
    DocumentRoot "C:/xampp8/htdocs/navcrm/public"
    <Directory "C:/xampp8/htdocs/navcrm/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. Add to `C:\Windows\System32\drivers\etc\hosts` (run as Administrator):

```
127.0.0.1  navcrm.test
```

3. Restart Apache in XAMPP Control Panel.

4. Update `.env`:

```dotenv
APP_URL=http://navcrm.test
```

Then open **http://navcrm.test** in your browser.

---

### Option B — PHP built-in server (`php artisan serve`)

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies and build assets
npm install && npm run build

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Run migrations and seed (all demo data included)
php artisan migrate
php artisan db:seed

# 5. Start development server
php artisan serve
```

Then open **http://localhost:8000** in your browser.

---

## Architecture

### Two Auth Layers

| Layer | Guard | Routes | Use Case |
|-------|-------|--------|----------|
| **Web** | `web` (session) | `routes/web.php` | Browser users, Blade pages |
| **API** | `sanctum` (token) | `routes/api.php` | Mobile/SPA/API clients |

### Multi-Tenancy

- Single database with `tenant_id` column on all tenant-scoped tables
- `BelongsToTenant` trait adds global query scopes automatically
- `TenantScope` middleware validates active tenant on every API request
- Web routes automatically scope via `auth()->user()->tenant_id`

---

## Features

### Authentication (Web + API)

| Feature | Web Route | API Route |
|---------|-----------|-----------|
| Register | `POST /auth/register` | `POST /api/auth/register` |
| Login | `POST /auth/login` | `POST /api/auth/login` |
| Logout | `POST /auth/logout` | `POST /api/auth/logout` |
| Forgot password | `POST /auth/forgot-password` | `POST /api/auth/forgot-password` |

### CRM Modules (All with full CRUD)

#### Contacts
- Profile: name, email, phone, mobile, job title, department
- Social media links: LinkedIn, Twitter, Facebook
- Address fields (street, city, state, postal code, country)
- Relationship mapping (self-referencing many-to-many with relationship type)
- Activity timeline (polymorphic) and notes
- Tags & segmentation (polymorphic many-to-many)
- Account associations (many-to-many with role and is_primary pivot)
- Table + Grid view toggle

#### Accounts (Companies)
- Company details: industry, website, phone, email, annual revenue, employee count, tax ID
- Parent/child hierarchy (self-referencing)
- Billing and shipping address management (polymorphic)
- Stakeholder contacts with role pivot
- Activity timeline and notes

#### Leads
- Lead capture via manual entry form
- Lead scoring: Hot / Warm / Cold
- Qualification status: New / Contacted / Qualified / Converted / Recycled
- Table and Kanban board views
- One-click conversion to Contact + Account (DB transaction)
- Tag management
- Source tracking

#### Opportunities (Deals)
- Configurable pipeline stages (drag-and-drop Kanban board)
- Deal details: amount, probability, close date, competitor, next steps
- Weighted amount calculation (amount × probability)
- Sales team management with role and commission split percentages
- Won/Lost tracking with timestamps and lost reason
- Linked quotes, activities, notes, tags

#### Products & Price Books
- Full product/service catalog: SKU, unit price, cost price, category, unit
- Price books with per-product pricing and minimum quantities
- Default and active price book flags
- Active/inactive product status

#### Quotes
- Auto-generated quote numbers (QT-XXXXX format)
- Dynamic line item builder with product selection and inline discounts
- Automatic subtotal, discount, tax, and total calculations
- Quote status workflow: Draft → Sent → Accepted / Rejected / Expired
- PDF generation and download (via dompdf)
- Linked to opportunities, accounts, contacts
- Terms and conditions, notes fields

#### Forecasting
- Revenue predictions with weighted pipeline
- Pipeline breakdown by stage (visual chart)
- Sales targets (monthly/quarterly/yearly quotas)
- Target vs actual attainment tracking
- Per-rep and team-level performance data

### Marketing Automation Module

#### Campaigns
- Multi-type campaign management: Email, Webinar, Event, Digital Ads, Direct Mail
- Status workflow: Draft → Active → Paused → Completed
- Budget tracking: planned vs actual budget
- Revenue tracking: target vs actual revenue
- ROI calculation (auto-computed from actual revenue and spend)
- Target lists with contact and lead segmentation
- Linked email campaigns
- Owner assignment and soft deletes

#### Target Lists
- Segment contacts and leads into named target lists per campaign
- Sync contacts and leads via many-to-many pivot tables
- Multiple target lists per campaign

#### Email Campaigns
- Linked to parent campaigns and email templates
- A/B subject line testing (Subject A vs Subject B with winning variant tracking)
- Scheduling: scheduled_at datetime, sent_at timestamp
- Engagement metrics: total sent, opens, clicks, bounces, unsubscribes
- Computed rates: open rate, click rate, bounce rate, unsubscribe rate
- Status workflow: Draft → Scheduled → Sent → Cancelled
- From name and from email configuration

#### Email Templates
- Reusable HTML email templates with subject and body
- Active/inactive status flag
- Linked to multiple email campaigns
- Soft deletes

#### Landing Pages
- Slug-based public URL routing
- SEO fields: meta title, meta description
- Page view counter
- Linked web form (optional embed)
- Active/inactive toggle
- Soft deletes

#### Web Forms
- Drag-and-drop dynamic field builder (JSON-stored field schema)
- Field types: text, email, phone, textarea, select, checkbox, radio, date
- Configurable submit button text and success message
- Post-submit redirect URL support
- Lead/contact auto-assignment to a specific user
- Geography-based assignment flag
- Submission tracking with total count
- Submission-to-lead/contact conversion workflow
- Embeddable on landing pages

### Customer Service & Support (Help Desk)

#### Tickets
- Auto-generated ticket numbers (TK-XXXXX format)
- Priority levels: Low (72h SLA), Medium (24h SLA), High (8h SLA), Critical (4h SLA)
- Status workflow: Open → In Progress → Escalated → Resolved → Closed
- SLA tracking with breach and warning badges
- Channel tracking: Email, Portal, Phone, Manual
- Agent assignment, threaded comments with internal note toggle
- Resolved/Closed timestamps and first response time recording
- Linked to contacts and accounts

#### Knowledge Base
- Searchable article repository with categories
- Internal vs. Public visibility toggle and Published/Draft status
- View count tracking, author attribution, auto-generated slugs
- Soft deletes

#### Customer Self-Service Portal
- Standalone portal at `/portal` with its own layout (no CRM sidebar)
- Session-based portal authentication separate from staff auth
- Contact portal access enabled per-contact via `portal_active` + `portal_password`
- Customers can submit, view and reply to their own tickets
- Non-internal replies visible in thread; closed tickets show read-only view

### Activity & Communication Management

#### Task Management
- Tasks with title, description, due date, due time, priority, and status
- Priority levels: Low / Medium / High / Urgent
- Status workflow: Pending → In Progress → Completed / Cancelled
- Polymorphic association to Contacts, Accounts, and Opportunities
- Recurring tasks: daily, weekly, monthly, quarterly, yearly with configurable interval and end date
- Assignee and creator tracking per task
- Completed timestamp recorded on task completion
- Soft deletes

#### Calendar Events
- Event types: Meeting, Call, Demo, Follow-Up, Webinar, Other
- Status workflow: Scheduled → Completed / Cancelled / No-Show
- Start/end datetime with all-day toggle
- Location and meeting link fields (Zoom, Teams, Google Meet)
- External calendar sync fields: `external_calendar_id` + `external_calendar_source` (Google, Outlook, iCal)
- Calendly-style invite URL for customer self-booking
- Polymorphic association to Contacts, Accounts, and Opportunities
- Organizer assignment and soft deletes

#### Call Logging
- Direction tracking: Inbound / Outbound
- Status: Completed, No Answer, Busy, Voicemail, Failed
- Duration in seconds with automatic formatting
- Phone number, optional VoIP recording URL
- Polymorphic association to Contacts, Leads, and Accounts
- Called-at timestamp and assigned user

#### Email Logging
- Direction tracking: Inbound / Outbound
- Source tracking: Gmail, Outlook, BCC Dropbox, Manual
- Open and click timestamp tracking (`opened_at`)
- CC recipients stored as JSON
- External `message_id` for deduplication with email clients
- Polymorphic association to Contacts, Leads, and Accounts

### Finance & Billing Management

#### Invoicing
- Full invoice lifecycle: Draft → Sent → Partial → Paid → Overdue → Cancelled / Void
- **Quote-to-Invoice conversion** — create invoice pre-filled from any accepted quote
- Invoice number generation in `INV-XXXXX` format (tenant-scoped)
- Line item builder with live JS subtotal/tax/total calculation (mirrors quotes)
- Tax rate support (configurable rates with country/region metadata)
- Discount support: fixed amount or percentage
- PDF download (dompdf) via `/finance/invoices/{id}/pdf`
- Recurring invoices: store recurrence data (monthly/quarterly/yearly) + manual "Generate Next Invoice" button
- Parent/child invoice chain for recurring billing history

#### Payment Tracking (manual)
- Record payments against invoices with method, reference number, and notes
- Supported methods: Bank Transfer, Credit Card, Stripe, PayPal, Razorpay, Cash, Cheque
- Automatic invoice status refresh after each payment: partial → Partial, fully paid → Paid
- Payment amount aggregation: `amount_paid` auto-calculated from completed payments
- Payments embedded in Invoice show page (inline record-payment form)

#### Expense Tracking
- Track business expenses linked to Opportunities or Accounts
- Categories: Travel, Meals, Software, Entertainment, Accommodation, Other
- Approval workflow: Pending → Approved / Rejected (with approver and timestamp)
- Receipt URL storage for expense documentation

### Project & Delivery Management (Post-Sale)

#### Projects
- Auto-generated project numbers (`PRJ-XXXXX` format, tenant-scoped)
- **Deal-to-Project Conversion** — one-click convert from Opportunity show page; guards against duplicates
- Status workflow: Planning → Active → On Hold → Completed / Cancelled
- Linked to Opportunity, Account, and Contact records
- Budget and currency tracking
- Table view + Kanban view (grouped by status) with localStorage view preference
- Milestone progress bar on all project cards

#### Project Milestones
- Add, edit, and delete milestones inline on the Project show page
- Per-milestone status: Pending / In Progress / Completed with auto-set `completed_at`
- **Frappe Gantt chart** (CDN-loaded) on a dedicated Gantt tab — renders milestones as bars with Week/Month view modes
- Overdue milestones highlighted in red

#### Team Members (Resource Allocation)
- Add team members to a project with role and allocated hours (pivot table)
- Members list shown on Project show page with inline remove action

#### Timesheets
- Log hours against specific projects with date, description, billable flag, and billable rate
- Billable vs. Non-Billable hour tracking per project
- Filter timesheets by project, user, date range, and billable flag
- Summary stats: total hours, billable hours, non-billable hours

#### Workload View
- Per-user table showing active projects, allocated hours, hours logged in selected month, and utilization %
- Color-coded utilization bar: green (<80%), yellow (80–100%), red (>100% = overbooked)
- Month selector for historical workload analysis

### Document & Contract Management

#### Document Templates
- Reusable HTML templates with `{{Variable}}` placeholder syntax
- Rich-text editor (Quill.js) with an inline variable cheatsheet
- Template types: NDA, Contract, Proposal, Statement of Work, MSA, Other
- Toggle active/inactive to control availability when creating documents
- Preview rendered template body and see linked documents

#### Documents
- Auto-generated document numbers (`DOC-XXXXX` format, tenant-scoped)
- Link documents to Accounts, Contacts, and Opportunities
- Status workflow: Draft → Sent → Viewed → Signed / Rejected / Expired
- Variable auto-fill when creating from a template (`{{Account.Name}}`, `{{Contact.Name}}`, `{{Opportunity.Value}}`, `{{Today.Date}}`, etc.)
- Rich-text editor (Quill.js) for manual editing of document body
- PDF download via DomPDF (`/documents/{id}/download`)
- Filter by type, status, and account; summary stats (total / sent / signed / expired)

#### E-Signatures (Built-in)
- Send a document to one or more signatories; each gets a unique token-based URL
- Public signing portal at `/sign/{token}` — no login required
- Signature canvas powered by Signature_Pad.js (draw or type)
- Tracks viewed_at, signed_at, IP address, and user_agent per signatory
- Document status auto-updates to `signed` when all signatories have signed
- Copy signing link directly from the document show page

#### Version History
- When a sent/signed document body is edited, a version snapshot is automatically saved
- Version history tab on the document show page with collapsible HTML previews

### Automation & Workflow Engine

#### Trigger-Based Workflows (IFTTT Rules)
- Create automation rules with configurable triggers, conditions, and actions
- **Triggers:** Lead status changed, Opportunity stage changed, Quote discount exceeded, Ticket SLA breached
- **Conditions:** AND-logic filters on any entity field (equals, not-equals, greater than, less than, contains)
- **Actions:** Send email notification, assign user, change status, send webhook (HTTP POST)
- Active/inactive toggle per workflow with one-click enable/disable
- Workflow run audit log with status tracking (pending → running → completed / failed)
- Actions executed via queued jobs (database driver) for non-blocking processing
- Token substitution in email messages using `{field_name}` syntax

#### Approval Processes (Quote Discounts)
- Automatic quote locking when discount exceeds a configurable threshold (default 15%)
- Quote status extended with `Pending Approval` and `Approved` states
- Dedicated approval queue at `/approvals` listing all pending quotes
- One-click approve or reject with mandatory rejection reason
- Approval columns: `approved_by`, `approved_at`, `rejection_reason`, `rejected_at`
- Observer-based detection — discount threshold checked on every quote save

#### Webhooks
- Send JSON payloads to external URLs on any trigger event
- Payload includes: event name, entity type, entity ID, full context data, timestamp
- 10-second timeout with 3 retry attempts on failure
- Webhook URL configured per workflow action

#### SLA Breach Detection
- Scheduled artisan command `workflow:check-sla` runs every 5 minutes
- Detects open tickets past their `sla_due_at` deadline where `sla_breached_at` is null
- Marks tickets as breached and fires the `ticket_sla_breached` automation trigger
- Works with existing SLA badge system (breach/warning indicators)

### Customer Success & Retention

#### Onboarding Pipelines
- Auto-generated pipeline numbers (`OB-XXXXX` format, tenant-scoped)
- Status workflow: Not Started → In Progress → Completed / Cancelled
- Step-by-step checklist with interactive toggle (mark complete/incomplete)
- Add, reorder, and remove steps inline on the pipeline show page
- Computed progress percentage (% steps completed)
- Assignee and due date tracking per pipeline and per step
- Linked to Accounts and Contacts

#### Health Scoring
- Automated account health calculation (0–100 overall score)
- Three sub-scores: Login/Engagement (30%), Ticket Health (35%), Payment Timeliness (35%)
- Score labels: Healthy (70+), At Risk (40–69), Critical (0–39) with color coding
- Historical score tracking (immutable records with `calculated_at` timestamp)
- Recalculate single account or all accounts in bulk
- Factors stored as JSON for drill-down analysis
- CS Dashboard with at-risk accounts widget and KPI summary

#### Surveys & Feedback (NPS/CSAT)
- Two survey types: NPS (Net Promoter Score) and CSAT (Customer Satisfaction)
- Auto-generated survey numbers (`SV-XXXXX` format, tenant-scoped)
- Status workflow: Draft → Active → Closed
- Token-based public response URL (`/survey/{token}`) — no login required
- Clickable 0–10 score selector with optional comment
- NPS score calculation: (% Promoters[9-10] − % Detractors[0-6]) × 100
- CSAT average score and distribution breakdown
- Response tracking linked to Contacts and Accounts
- Optional ticket association for post-resolution CSAT surveys

#### Customer Success Dashboard
- Hero KPI row: Active Onboarding, Avg Health Score, NPS Score, Avg CSAT
- At-Risk Accounts table (health score < 60)
- Active Onboarding Pipelines with progress bars
- Recent Survey Responses feed

### Analytics & Reporting

#### Analytics Dashboard
- Per-user drag-and-drop widget dashboard (12 available widget types)
- Widgets: Pipeline Summary, Leads by Status, Revenue by Month, Top Deals, Deals by Stage, Won vs Lost, Open Tickets, Ticket SLA, Ticket by Priority, Recent Activities, Task Overview, Conversion Funnel
- Widget visibility toggle (show/hide individual widgets per user)
- Layout persistence via AJAX (drag-and-drop order saved to `dashboard_widgets` table)
- Built with SortableJS for drag-and-drop and Chart.js for charts

#### Standard Reports (4 built-in)
- **Sales Activity Report** — activity count per rep (calls, emails, meetings, tasks); activity mix doughnut chart; daily trend line chart; date range filter
- **Sales Performance Report** — win rate, closed revenue, average deal size per rep; won vs open pipeline bar chart; top performers table; date range filter
- **Funnel Analysis Report** — stage-by-stage conversion rates with drop-off visualization; horizontal bar chart; overall win rate KPI; open pipeline value KPI
- **Service Report** — ticket resolution metrics; average first response and resolution times; SLA breach count; charts by status/priority/agent; date range filter

### Dashboard
- KPI cards: Contacts, Accounts, Leads, Open Opportunities, Closed Revenue
- Recent activities feed
- Recent opportunities
- Recent contacts

### Activities
- Polymorphic activity log across all CRM objects
- Filter by activity type (email, call, meeting, task, note)
- Chronological timeline view with pagination

### Settings
- User profile (name, email, phone)
- Password change
- Admin: User management with invite flow and role assignment
- Admin: Roles and permissions matrix view

---

## Project Structure

```
navcrm/
├── app/
│   ├── Enums/
│   │   ├── ActivityType.php
│   │   ├── AddressType.php
│   │   ├── CalendarEventStatus.php      # scheduled, completed, cancelled, no_show
│   │   ├── CalendarEventType.php        # meeting, call, demo, follow_up, webinar, other
│   │   ├── CallDirection.php            # inbound, outbound
│   │   ├── CallStatus.php               # completed, no_answer, busy, voicemail, failed
│   │   ├── CampaignStatus.php           # draft, active, paused, completed
│   │   ├── CampaignType.php             # email, webinar, event, digital_ads, direct_mail
│   │   ├── EmailCampaignStatus.php
│   │   ├── EmailDirection.php           # inbound, outbound
│   │   ├── EmailSource.php              # gmail, outlook, bcc_dropbox, manual
│   │   ├── ExpenseCategory.php          # travel|meals|software|entertainment|accommodation|other
│   │   ├── ExpenseStatus.php            # pending|approved|rejected
│   │   ├── MilestoneStatus.php          # pending|in_progress|completed
│   │   ├── ProjectStatus.php            # planning|active|on_hold|completed|cancelled
│   │   ├── DocumentStatus.php           # draft|sent|viewed|signed|rejected|expired
│   │   ├── DocumentType.php             # nda|contract|proposal|sow|msa|other
│   │   ├── ForecastCategory.php
│   │   ├── InvoiceStatus.php            # draft|sent|partial|paid|overdue|cancelled|void
│   │   ├── LeadScore.php
│   │   ├── PaymentMethod.php            # bank_transfer|credit_card|stripe|paypal|razorpay|cash|cheque
│   │   ├── PaymentStatus.php            # pending|completed|failed|refunded
│   │   ├── LeadStatus.php
│   │   ├── OpportunitySource.php
│   │   ├── QuoteStatus.php              # draft|sent|accepted|rejected|expired|pending_approval|approved
│   │   ├── OnboardingStatus.php          # not_started, in_progress, completed, cancelled
│   │   ├── SurveyType.php               # nps, csat
│   │   ├── SurveyStatus.php             # draft, active, closed
│   │   ├── WorkflowTrigger.php          # lead_status_changed|opportunity_stage_changed|quote_discount_exceeded|ticket_sla_breached
│   │   ├── TaskPriority.php             # low, medium, high, urgent
│   │   ├── TaskRecurrence.php           # daily, weekly, monthly, quarterly, yearly
│   │   ├── TaskStatus.php               # pending, in_progress, completed, cancelled
│   │   ├── TicketChannel.php            # email, portal, phone, manual
│   │   ├── TicketPriority.php           # low, medium, high, critical
│   │   └── TicketStatus.php             # open, in_progress, escalated, resolved, closed
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                         # JSON API controllers (Sanctum auth)
│   │   │   │   ├── AccountController.php
│   │   │   │   ├── ActivityController.php
│   │   │   │   ├── AddressController.php
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── CalendarEventController.php
│   │   │   │   ├── CallLogController.php
│   │   │   │   ├── CampaignController.php
│   │   │   │   ├── ContactController.php
│   │   │   │   ├── EmailCampaignController.php
│   │   │   │   ├── EmailLogController.php
│   │   │   │   ├── EmailTemplateController.php
│   │   │   │   ├── ExpenseController.php
│   │   │   ├── ProjectController.php
│   │   │   └── TimesheetController.php
│   │   │   ├── DocumentController.php
│   │   │   │   ├── WorkflowController.php
│   │   │   │   ├── OnboardingPipelineController.php
│   │   │   │   ├── HealthScoreController.php
│   │   │   │   ├── SurveyController.php
│   │   │   │   ├── SurveyResponseController.php
│   │   │   │   ├── ForecastController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   ├── KbArticleController.php
│   │   │   │   ├── LandingPageController.php
│   │   │   │   ├── LeadController.php
│   │   │   │   ├── NoteController.php
│   │   │   │   ├── OpportunityController.php
│   │   │   │   ├── PipelineStageController.php
│   │   │   │   ├── PriceBookController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── QuoteController.php
│   │   │   │   ├── RolePermissionController.php
│   │   │   │   ├── SalesTargetController.php
│   │   │   │   ├── TagController.php
│   │   │   │   ├── TaskController.php
│   │   │   │   ├── TicketController.php
│   │   │   │   ├── UserController.php
│   │   │   │   └── WebFormController.php
│   │   │   │
│   │   │   ├── AccountWebController.php     # Web Blade controllers (session auth)
│   │   │   ├── ActivityWebController.php
│   │   │   ├── AnalyticsDashboardWebController.php
│   │   │   ├── AnalyticsReportWebController.php
│   │   │   ├── CalendarEventWebController.php
│   │   │   ├── CallLogWebController.php
│   │   │   ├── CampaignWebController.php
│   │   │   ├── ContactWebController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EmailCampaignWebController.php
│   │   │   ├── EmailLogWebController.php
│   │   │   ├── EmailTemplateWebController.php
│   │   │   ├── ExpenseWebController.php
│   │   │   ├── ProjectWebController.php
│   │   │   ├── TimesheetWebController.php
│   │   │   ├── DocumentTemplateWebController.php
│   │   │   ├── DocumentWebController.php
│   │   │   ├── DocumentSigningController.php
│   │   │   ├── WorkflowWebController.php
│   │   │   ├── ApprovalWebController.php
│   │   │   ├── OnboardingPipelineWebController.php
│   │   │   ├── HealthScoreWebController.php
│   │   │   ├── SurveyWebController.php
│   │   │   ├── SurveyPublicController.php
│   │   │   ├── ForecastWebController.php
│   │   │   ├── InvoiceWebController.php
│   │   │   ├── KbArticleWebController.php
│   │   │   ├── LandingPageWebController.php
│   │   │   ├── LeadWebController.php
│   │   │   ├── OpportunityWebController.php
│   │   │   ├── PriceBookWebController.php
│   │   │   ├── ProductWebController.php
│   │   │   ├── QuoteWebController.php
│   │   │   ├── SettingsController.php
│   │   │   ├── TaskWebController.php
│   │   │   ├── TicketWebController.php
│   │   │   ├── WebAuthController.php
│   │   │   └── WebFormWebController.php
│   │   │
│   │   ├── Middleware/
│   │   │   └── TenantScope.php              # Enforces tenant isolation (API)
│   │   │
│   │   ├── Requests/                        # Form validation request classes
│   │   │   ├── Account/
│   │   │   ├── Auth/
│   │   │   ├── CalendarEvent/
│   │   │   ├── CallLog/
│   │   │   ├── Contact/
│   │   │   ├── EmailLog/
│   │   │   ├── ForecastEntry/
│   │   │   ├── Lead/
│   │   │   ├── Opportunity/
│   │   │   ├── PriceBook/
│   │   │   ├── Product/
│   │   │   ├── Quote/
│   │   │   ├── SalesTarget/
│   │   │   └── Task/
│   │   │
│   │   └── Resources/                       # API resource transformers
│   │
│   ├── Models/
│   │   ├── Concerns/
│   │   │   ├── BelongsToTenant.php          # Global scope + auto tenant_id
│   │   │   └── Filterable.php               # Reusable scopeSearch / scopeFilterOwner / scopeFilterDateRange
│   │   ├── Account.php
│   │   ├── Activity.php
│   │   ├── Address.php
│   │   ├── CalendarEvent.php
│   │   ├── CallLog.php
│   │   ├── Campaign.php
│   │   ├── CampaignTargetList.php
│   │   ├── Contact.php
│   │   ├── DashboardWidget.php
│   │   ├── EmailCampaign.php
│   │   ├── EmailLog.php
│   │   ├── Expense.php
│   │   ├── Invoice.php
│   │   ├── InvoiceLineItem.php
│   │   ├── EmailTemplate.php
│   │   ├── ForecastEntry.php
│   │   ├── KbArticle.php
│   │   ├── LandingPage.php
│   │   ├── Lead.php
│   │   ├── Note.php
│   │   ├── Opportunity.php
│   │   ├── Payment.php
│   ├── Project.php
│   ├── ProjectMilestone.php
│   │   ├── PipelineStage.php
│   │   ├── PriceBook.php
│   │   ├── PriceBookEntry.php
│   │   ├── Product.php
│   │   ├── Quote.php
│   │   ├── QuoteLineItem.php
│   │   ├── SalesTarget.php
│   │   ├── Tag.php
│   │   ├── Task.php
│   │   ├── TaxRate.php
│   │   ├── Timesheet.php
│   │   ├── Tenant.php
│   │   ├── Ticket.php
│   │   ├── TicketComment.php
│   │   ├── User.php
│   │   ├── WebForm.php
│   │   ├── WebFormSubmission.php
│   │   ├── Workflow.php
│   │   ├── WorkflowCondition.php
│   │   ├── WorkflowAction.php
│   │   ├── WorkflowRun.php
│   │   ├── OnboardingPipeline.php
│   │   ├── OnboardingStep.php
│   │   ├── HealthScore.php
│   │   ├── Survey.php
│   │   └── SurveyResponse.php
│   │
│   ├── Observers/
│   │   ├── LeadObserver.php               # Fires lead_status_changed trigger
│   │   ├── OpportunityObserver.php        # Fires opportunity_stage_changed trigger
│   │   └── QuoteObserver.php              # Discount threshold check + fires quote_discount_exceeded
│   │
│   ├── Jobs/
│   │   └── ExecuteWorkflowAction.php      # Queued job: send_email, assign_user, change_status, send_webhook
│   │
│   ├── Console/
│   │   └── Commands/
│   │       └── CheckSlaBreaches.php       # Artisan: workflow:check-sla (every 5 min via scheduler)
│   │
│   └── Services/
│       ├── AnalyticsService.php             # KPI data, chart datasets, report data for all 4 analytics reports
│       ├── ForecastService.php
│       ├── InvoicePdfService.php            # dompdf PDF generation for invoices
│       ├── InvoiceService.php               # Invoice number generation, quote conversion, totals, payment status refresh, recurring
│       ├── LeadConversionService.php
│       ├── QuoteCalculationService.php
│       ├── QuotePdfService.php
│       ├── AutomationEngine.php           # Workflow trigger evaluation, condition matching, job dispatch
│       └── HealthScoreService.php        # Automated account health calculation (login, ticket, payment sub-scores)
│
├── database/
│   ├── migrations/                          # 45+ migration files
│   ├── factories/                           # Model factories for testing/seeding
│   └── seeders/
│       ├── DatabaseSeeder.php               # Calls RolePermissionSeeder + DemoDataSeeder
│       ├── RolePermissionSeeder.php         # Roles and permissions setup
│       ├── DemoDataSeeder.php               # Core + SFA demo data
│       ├── MarketingDemoSeeder.php          # Marketing Automation demo data
│       ├── SupportDemoSeeder.php            # Customer Service & Support demo data
│       ├── ActivityDemoSeeder.php           # Activity & Communication demo data
│       ├── FinanceDemoSeeder.php            # Finance & Billing demo data (tax rates, invoices, payments, expenses)
│       ├── ProjectDemoSeeder.php            # Project & Delivery demo data (projects, milestones, members, timesheets)
│       ├── DocumentDemoSeeder.php           # Document & Contract demo data (templates, documents, signatories)
│       ├── WorkflowDemoSeeder.php          # Automation & Workflow demo data (6 workflows, runs, pending approval quote)
│       └── CustomerSuccessDemoSeeder.php  # Customer Success demo data (pipelines, health scores, surveys, responses)
│
├── resources/
│   ├── css/
│   │   └── navcrm-theme.css                 # Custom theme variables & components
│   ├── js/
│   │   ├── app.js
│   │   └── navcrm-theme.js                  # UI helpers (sidebar, dropdowns, toasts)
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php                # Authenticated layout (sidebar + topbar)
│       │   └── auth.blade.php               # Auth pages layout
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   └── forgot-password.blade.php
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── contacts/
│       │   ├── index.blade.php
│       │   ├── create.blade.php             # Shared create/edit form
│       │   └── show.blade.php
│       ├── accounts/
│       │   ├── index.blade.php
│       │   ├── create.blade.php             # Shared create/edit form
│       │   └── show.blade.php
│       ├── leads/
│       │   ├── index.blade.php
│       │   ├── create.blade.php             # Shared create/edit form
│       │   └── show.blade.php
│       ├── activities/
│       │   └── index.blade.php
│       ├── analytics/
│       │   ├── dashboard.blade.php          # Drag-and-drop widget dashboard (SortableJS + Chart.js)
│       │   └── reports/
│       │       ├── sales-activity.blade.php # Activity count per rep + daily trend chart
│       │       ├── sales-performance.blade.php # Win rate, revenue per rep + bar chart
│       │       ├── funnel.blade.php         # Stage conversion funnel + horizontal bar chart
│       │       └── service.blade.php        # Ticket metrics + SLA breach + agent charts
│       ├── opportunities/
│       │   ├── index.blade.php              # Kanban + table view
│       │   ├── create.blade.php             # Shared create/edit form
│       │   └── show.blade.php
│       ├── products/
│       │   ├── index.blade.php
│       │   └── create.blade.php             # Shared create/edit/show form
│       ├── price-books/
│       │   └── index.blade.php
│       ├── quotes/
│       │   ├── index.blade.php
│       │   ├── create.blade.php             # Shared create/edit form
│       │   ├── show.blade.php
│       │   └── pdf.blade.php                # PDF template for dompdf
│       ├── finance/
│       │   ├── invoices/
│       │   │   ├── index.blade.php          # Status chip filters, table with Amount Due column + PDF button
│       │   │   ├── create.blade.php         # Line item builder, tax rate, recurring toggle, quote conversion banner
│       │   │   ├── show.blade.php           # Invoice preview, inline payment recording, recurring schedule card
│       │   │   └── pdf.blade.php            # dompdf PDF template
│       │   └── expenses/
│       │       ├── index.blade.php          # Status chips, filter bar, approve inline action
│       │       ├── create.blade.php         # Category / amount / date / linked opportunity form
│       │       └── show.blade.php           # Detail view with Approve / Reject action buttons
│       ├── forecasts/
│       │   └── index.blade.php
│       ├── marketing/
│       │   ├── campaigns/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php         # Shared create/edit form
│       │   │   └── show.blade.php
│       │   ├── email-campaigns/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php         # Shared create/edit form
│       │   │   └── show.blade.php
│       │   ├── email-templates/
│       │   │   ├── index.blade.php
│       │   │   └── create.blade.php         # Shared create/edit form
│       │   ├── landing-pages/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php         # Shared create/edit form
│       │   │   └── show.blade.php
│       │   └── web-forms/
│       │       ├── index.blade.php
│       │       ├── create.blade.php         # Dynamic field builder
│       │       ├── show.blade.php           # Live preview + submissions list
│       │       └── _field.blade.php         # Reusable drag-and-drop field row
│       ├── activity/
│       │   ├── tasks/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php         # Shared create/edit form
│       │   │   └── show.blade.php
│       │   ├── calendar/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php         # Shared create/edit form
│       │   │   └── show.blade.php
│       │   ├── calls/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php         # Shared create/edit form
│       │   │   └── show.blade.php
│       │   └── emails/
│       │       ├── index.blade.php
│       │       ├── create.blade.php         # Shared create/edit form
│       │       └── show.blade.php
│       ├── projects/
│       │   ├── index.blade.php              # Table + Kanban view toggle (localStorage preference)
│       │   ├── create.blade.php             # Shared create/edit form
│       │   └── show.blade.php               # Milestones tab + Gantt (Frappe Gantt), members panel, timesheets tab
│       ├── timesheets/
│       │   ├── index.blade.php              # Table with filter bar + summary stats (total/billable/non-billable)
│       │   ├── create.blade.php             # Shared create/edit form
│       │   ├── show.blade.php               # Detail view
│       │   └── workload.blade.php           # Per-user workload grid with utilization % bars
│       ├── workflows/
│       │   ├── index.blade.php              # Stats cards, filter bar, toggle active, runs count
│       │   ├── create.blade.php             # Trigger select, dynamic condition/action rows (vanilla JS)
│       │   ├── show.blade.php               # Workflow detail, conditions, actions, paginated run history
│       │   └── _action_config.blade.php     # Action config partial (email/assign/status/webhook panels)
│       ├── approvals/
│       │   └── index.blade.php              # Pending quotes table with approve/reject actions
│       ├── success/
│       │   ├── dashboard.blade.php          # CS overview: KPI hero, at-risk accounts, active pipelines, recent responses
│       │   ├── onboarding/
│       │   │   ├── index.blade.php          # Stats cards, filter toolbar, pipeline table with progress bars
│       │   │   ├── create.blade.php         # Two-column form with repeatable steps builder (vanilla JS)
│       │   │   └── show.blade.php           # Hero header, details sidebar, interactive step checklist
│       │   ├── health-scores/
│       │   │   ├── index.blade.php          # Stats cards, color-coded score table, recalculate buttons
│       │   │   └── show.blade.php           # Account hero, score breakdown cards, Chart.js history chart
│       │   └── surveys/
│       │       ├── index.blade.php          # Stats, filter toolbar, surveys table
│       │       ├── create.blade.php         # Two-column form (type, status, targeting)
│       │       ├── show.blade.php           # NPS gauge / CSAT chart, public link, responses table
│       │       └── respond.blade.php        # Public page (no auth), clickable score buttons, thank-you state
│       ├── settings/
│       │   ├── index.blade.php
│       │   ├── profile.blade.php
│       │   ├── users.blade.php
│       │   └── roles.blade.php
│       └── welcome.blade.php
│
└── routes/
    ├── api.php                              # REST API routes (Sanctum + TenantScope)
    └── web.php                              # Web routes (session auth)
```

---

## Web Routes Summary

```
GET  /                          → redirect (dashboard or login)
GET  /login                     → login form
GET  /register                  → register form
GET  /auth/login                → login form (named: auth.login.form)
GET  /auth/register             → register form
GET  /auth/forgot-password      → forgot password form
POST /auth/login                → authenticate (named: auth.login)
POST /auth/register             → create account + tenant (named: auth.register)
POST /auth/logout               → sign out (named: auth.logout)
POST /auth/forgot-password      → send reset link

GET  /dashboard                 → dashboard

GET|POST          /contacts          → list / create
GET|PUT|DELETE    /contacts/{id}     → show / edit / update / delete
GET|POST          /accounts          → list / create
GET|PUT|DELETE    /accounts/{id}     → show / edit / update / delete
GET|POST          /leads             → list / create
GET|PUT|DELETE    /leads/{id}        → show / edit / update / delete
POST              /leads/{id}/convert → convert to contact+account
GET|POST          /opportunities     → list / create
GET|PUT|DELETE    /opportunities/{id}→ show / edit / update / delete
GET|POST          /products          → list / create
GET|PUT|DELETE    /products/{id}     → show / edit / update / delete
GET|POST          /quotes            → list / create
GET|PUT|DELETE    /quotes/{id}       → show / edit / update / delete
GET               /quotes/{id}/pdf  → download PDF

GET               /price-books       → list + create modal
GET               /activities        → activity timeline

GET               /forecasts         → forecast dashboard

GET|POST          /marketing/campaigns                → list / create
GET|PUT|DELETE    /marketing/campaigns/{id}           → show / edit / update / delete
GET|POST          /marketing/email-campaigns          → list / create
GET|PUT|DELETE    /marketing/email-campaigns/{id}     → show / edit / update / delete
GET|POST          /marketing/email-templates          → list / create
GET|PUT|DELETE    /marketing/email-templates/{id}     → edit / update / delete
GET|POST          /marketing/landing-pages            → list / create
GET|PUT|DELETE    /marketing/landing-pages/{id}       → show / edit / update / delete
GET|POST          /marketing/web-forms                → list / create
GET|PUT|DELETE    /marketing/web-forms/{id}           → show / edit / update / delete
POST              /marketing/web-forms/submissions/{id}/convert → convert to lead/contact

GET|POST          /support/tickets                → list / create
GET|PUT|DELETE    /support/tickets/{id}           → show / edit / update / delete
POST              /support/tickets/{id}/comment   → add reply
POST              /support/tickets/{id}/status    → change status
GET|POST          /support/kb-articles            → list / create
GET|PUT|DELETE    /support/kb-articles/{id}       → show / edit / update / delete

GET               /portal/login                   → portal login form
POST              /portal/login                   → portal authenticate
POST              /portal/logout                  → portal sign out
GET               /portal                         → portal dashboard
GET               /portal/tickets                 → my tickets (portal)
GET|POST          /portal/tickets/create          → submit ticket (portal)
GET               /portal/tickets/{id}            → view ticket (portal)
POST              /portal/tickets/{id}/comment    → reply to ticket (portal)

GET|POST          /activity/tasks                 → list / create
GET|PUT|DELETE    /activity/tasks/{id}            → show / edit / update / delete
GET|POST          /activity/calendar              → list / create
GET|PUT|DELETE    /activity/calendar/{id}         → show / edit / update / delete
GET|POST          /activity/calls                 → list / create
GET|PUT|DELETE    /activity/calls/{id}            → show / edit / update / delete
GET|POST          /activity/emails                → list / create
GET|PUT|DELETE    /activity/emails/{id}           → show / edit / update / delete

GET|POST          /finance/invoices                   → list / create
GET|PUT|DELETE    /finance/invoices/{id}             → show / edit / update / delete
GET               /finance/invoices/{id}/pdf         → download PDF
POST              /finance/invoices/{id}/payments    → record payment
DELETE            /finance/invoices/{id}/payments/{pid} → delete payment
POST              /finance/invoices/{id}/recurring   → generate next recurring invoice

GET|POST          /finance/expenses                  → list / create
GET|PUT|DELETE    /finance/expenses/{id}             → show / edit / update / delete
POST              /finance/expenses/{id}/approve     → approve expense
POST              /finance/expenses/{id}/reject      → reject expense

GET|POST          /projects                           → list / create
GET|PUT|DELETE    /projects/{id}                      → show / edit / update / delete
POST              /projects/{id}/milestones           → add milestone
PUT|DELETE        /projects/{id}/milestones/{mid}     → update / delete milestone
POST              /projects/{id}/members              → add team member
DELETE            /projects/{id}/members/{uid}        → remove team member
POST              /opportunities/{id}/convert-to-project → create project from opportunity

GET|POST          /timesheets                         → list / create
GET|PUT|DELETE    /timesheets/{id}                    → show / edit / update / delete
GET               /timesheets/workload                → resource workload view

GET|POST          /document-templates                 → list / create template
GET|PUT|DELETE    /document-templates/{id}            → show / edit / update / delete
GET|POST          /documents                          → list / create document
GET|PUT|DELETE    /documents/{id}                     → show / edit / update / delete
GET               /documents/{id}/download            → download PDF
GET|POST          /documents/{id}/send                → add signatory / get signing link
GET               /sign/{token}                       → public signing portal (no auth)
POST              /sign/{token}                       → submit signature

GET|POST          /workflows                          → list / create workflow
GET|PUT|DELETE    /workflows/{id}                     → show / edit / update / delete
PATCH             /workflows/{id}/toggle              → toggle active/inactive
GET               /approvals                          → pending quote approvals
POST              /approvals/{id}/approve             → approve quote
POST              /approvals/{id}/reject              → reject quote

GET               /success/dashboard                          → CS dashboard
GET|POST          /success/onboarding                        → list / create pipeline
GET|PUT|DELETE    /success/onboarding/{id}                   → show / edit / update / delete
POST              /success/onboarding/{id}/steps             → add step
POST              /success/onboarding/{id}/steps/{step}/toggle → toggle step completion
DELETE            /success/onboarding/{id}/steps/{step}      → remove step
GET               /success/health-scores                     → all accounts with scores
GET               /success/health-scores/{account}           → account score detail
POST              /success/health-scores/{account}/recalculate → recalculate single
POST              /success/health-scores/recalculate-all     → recalculate all accounts
GET|POST          /success/surveys                           → list / create survey
GET|PUT|DELETE    /success/surveys/{id}                      → show / edit / update / delete
GET               /survey/{token}                            → public survey response form (no auth)
POST              /survey/{token}                            → submit survey response (no auth)

GET               /analytics                          → analytics dashboard
POST              /analytics/dashboard/layout         → save widget layout (AJAX)
POST              /analytics/dashboard/widget/toggle  → toggle widget visibility (AJAX)
GET               /analytics/reports/sales-activity   → sales activity report
GET               /analytics/reports/sales-performance → sales performance report
GET               /analytics/reports/funnel           → funnel analysis report
GET               /analytics/reports/service          → service report

GET               /settings          → settings home
GET|PUT           /settings/profile  → profile
PUT               /settings/password → change password
GET|POST          /settings/users    → user list / invite
PUT|DELETE        /settings/users/{id} → update / delete user
GET               /settings/roles    → roles and permissions view
```

---

## API Routes Summary

All protected routes require `Authorization: Bearer {token}`.

```
GET  /api/health

# Auth (public)
POST /api/auth/register
POST /api/auth/login
POST /api/auth/forgot-password
POST /api/auth/reset-password

# Profile (protected)
GET|PUT /api/profile
PUT     /api/profile/password

# Protected resource routes (Sanctum + active tenant required)
/api/contacts        (CRUD + sync-tags, relationships)
/api/accounts        (CRUD + contacts, children, addresses)
/api/leads           (CRUD + convert, sync-tags)
/api/tags            (CRUD)
/api/activities      (list, create, delete)
/api/notes           (list, create, update, delete)
/api/pipeline-stages (CRUD + reorder)
/api/opportunities   (CRUD + stage, team management)
/api/products        (CRUD)
/api/price-books     (CRUD + entries)
/api/quotes          (CRUD + status update, PDF)
/api/sales-targets   (CRUD)
/api/forecasts       (summary, list)

# Marketing Automation (protected)
/api/campaigns                                        (CRUD + target-lists management)
/api/campaign-target-lists/{id}/sync-contacts         (sync contacts to target list)
/api/campaign-target-lists/{id}/sync-leads            (sync leads to target list)
/api/email-templates                                  (CRUD)
/api/email-campaigns                                  (CRUD + status update)
/api/landing-pages                                    (CRUD)
/api/web-forms                                        (CRUD + submissions list)
/api/web-form-submissions/{id}/convert                (convert submission to lead/contact)

# Customer Service & Support (protected)
/api/tickets         (CRUD + comments, status update)
/api/kb-articles     (CRUD)

# Activity & Communication Management (protected)
/api/tasks           (CRUD)
/api/calendar-events (CRUD)
/api/call-logs       (CRUD)
/api/email-logs      (CRUD)

# Finance & Billing (protected)
/api/invoices                         (CRUD)
PATCH /api/invoices/{id}/status       (update status)
/api/expenses                         (CRUD)
POST  /api/expenses/{id}/approve      (approve expense)
POST  /api/expenses/{id}/reject       (reject expense)

# Project & Delivery Management (protected)
/api/projects        (CRUD)
/api/timesheets      (CRUD)

# Document & Contract Management (protected)
/api/documents       (CRUD)

# Automation & Workflow Engine (protected)
/api/workflows       (CRUD)

# Customer Success & Retention (protected)
/api/onboarding-pipelines                              (CRUD)
PATCH /api/onboarding-pipelines/{id}/steps/{step}/toggle (toggle step)
GET   /api/health-scores                               (list all account scores)
GET   /api/health-scores/{account}                     (score history)
POST  /api/health-scores/{account}/recalculate         (recalculate)
/api/surveys                                           (CRUD)
GET   /api/surveys/{id}/responses                      (list responses)
/api/survey-responses                                  (index, store)

# Admin only (role:admin required)
/api/users           (CRUD + sync-roles)
/api/roles           (CRUD)
/api/permissions     (list)
```

---

## Database Models & Key Relationships

| Model | Table | Key Relationships |
|-------|-------|-------------------|
| Tenant | tenants | hasMany Users |
| User | users | belongsTo Tenant, hasRoles (Spatie) |
| Contact | contacts | belongsToMany Accounts, morphToMany Tags, morphMany Activities/Notes, belongsToMany relatedContacts |
| Account | accounts | belongsTo parent Account, hasMany children, belongsToMany Contacts, morphMany Addresses/Activities/Notes |
| Lead | leads | morphToMany Tags, morphMany Activities/Notes, belongsTo convertedContact/convertedAccount |
| PipelineStage | pipeline_stages | hasMany Opportunities |
| Opportunity | opportunities | belongsTo PipelineStage/Account/Contact, hasMany Quotes, belongsToMany teamMembers (Users) |
| Product | products | hasMany PriceBookEntries, QuoteLineItems |
| PriceBook | price_books | hasMany Entries (PriceBookEntry) |
| Quote | quotes | belongsTo Opportunity/Account/Contact, hasMany LineItems |
| SalesTarget | sales_targets | belongsTo User |
| Tag | tags | morphedByMany Contacts, Leads |
| Activity | activities | morphTo (Contact/Account/Lead/Opportunity) |
| Note | notes | morphTo (Contact/Account/Lead/Opportunity) |
| Address | addresses | morphTo (Account) |
| Campaign | campaigns | hasMany CampaignTargetLists, EmailCampaigns |
| CampaignTargetList | campaign_target_lists | belongsTo Campaign, belongsToMany Contacts/Leads |
| EmailTemplate | email_templates | hasMany EmailCampaigns |
| EmailCampaign | email_campaigns | belongsTo Campaign, EmailTemplate |
| LandingPage | landing_pages | belongsTo WebForm |
| WebForm | web_forms | hasMany WebFormSubmissions, LandingPages |
| WebFormSubmission | web_form_submissions | belongsTo WebForm |
| Ticket | tickets | belongsTo Contact/Account, hasMany TicketComments |
| TicketComment | ticket_comments | belongsTo Ticket, belongsTo User |
| KbArticle | kb_articles | belongsTo User (author) |
| Task | tasks | morphTo taskable (Contact/Account/Opportunity), belongsTo assignee/creator |
| CalendarEvent | calendar_events | morphTo eventable (Contact/Account/Opportunity), belongsTo organizer |
| CallLog | call_logs | morphTo loggable (Contact/Lead/Account), belongsTo User |
| EmailLog | email_logs | morphTo emailable (Contact/Lead/Account), belongsTo User |
| DashboardWidget | dashboard_widgets | belongsTo User; unique(user_id, widget_type); stores widget order + visibility |
| TaxRate | tax_rates | belongsTo Tenant; is_default flag |
| Invoice | invoices | belongsTo Quote/Opportunity/Account/Contact/owner/createdBy/parentInvoice; hasMany LineItems/Payments/childInvoices |
| InvoiceLineItem | invoice_line_items | belongsTo Invoice/Product |
| Payment | payments | belongsTo Invoice/createdBy |
| Expense | expenses | belongsTo Opportunity/Account/user/approvedBy/createdBy |
| Project | projects | belongsTo Opportunity/Account/Contact/manager/createdBy; hasMany Milestones/Timesheets; belongsToMany members (Users via project_members) |
| ProjectMilestone | project_milestones | belongsTo Project/createdBy |
| Timesheet | timesheets | belongsTo Project/User/createdBy |
| DocumentTemplate | document_templates | BelongsToTenant; hasMany Documents; createdBy |
| Document | documents | BelongsToTenant+SoftDeletes; belongsTo Template/Account/Contact/Opportunity/owner/createdBy; hasMany Signatories/Versions |
| DocumentSignatory | document_signatories | belongsTo Document; sign_token (unique UUID) |
| DocumentVersion | document_versions | belongsTo Document/savedBy; version snapshot |
| Workflow | workflows | BelongsToTenant+Filterable; hasMany Conditions/Actions/Runs; trigger_event cast to WorkflowTrigger enum |
| WorkflowCondition | workflow_conditions | belongsTo Workflow; field/operator/value for AND-logic matching |
| WorkflowAction | workflow_actions | belongsTo Workflow; action_config JSON; types: send_email/assign_user/change_status/send_webhook |
| WorkflowRun | workflow_runs | belongsTo Workflow; audit log with context_data, actions_log JSON, status tracking |
| OnboardingPipeline | onboarding_pipelines | BelongsToTenant+Filterable+SoftDeletes; belongsTo Account/Contact/assignee/creator; hasMany Steps; computed `progress` attribute |
| OnboardingStep | onboarding_steps | belongsTo OnboardingPipeline/completedByUser; no tenant trait (inherits via pipeline) |
| HealthScore | health_scores | BelongsToTenant; belongsTo Account; computed `health_label`/`health_color`; immutable history (no SoftDeletes) |
| Survey | surveys | BelongsToTenant+Filterable+SoftDeletes; belongsTo Account/Ticket/creator; hasMany SurveyResponses; computed `average_score`/`nps_score` |
| SurveyResponse | survey_responses | BelongsToTenant; belongsTo Survey/Contact/Account |

---

## Roles & Permissions

| Role | Access |
|------|--------|
| **Admin** | Full access: all CRM modules + user management + settings |
| **Manager** | Full CRM access, view reports, manage team members |
| **Sales** | Create/edit contacts, leads, accounts, opportunities, quotes |
| **Viewer** | Read-only access to all CRM data |

---

## Development

```bash
# Run all tests
php artisan test

# Format PHP code with Pint
./vendor/bin/pint

# Watch assets for changes
npm run dev

# List all web routes
php artisan route:list --path="/"

# List all API routes
php artisan route:list --path=api
```
