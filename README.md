# NavCRM

A multi-tenant CRM (Customer Relationship Management) application built with Laravel and Blade templates.

## Tech Stack

- **Laravel 12** (PHP 8.2+)
- **Blade** (server-side templating)
- **Vite** (asset bundler)
- **MySQL** (single database, `tenant_id` column multi-tenancy)
- **Laravel Sanctum** (token-based API authentication)
- **Spatie Laravel Permission** (roles & permissions)
- **barryvdh/laravel-dompdf** (PDF generation)

## Features

### Authentication
- User registration with company/tenant creation
- Login / Logout with Sanctum tokens
- Forgot password / Reset password
- Route guards and 401 redirect

### Multi-Tenancy
- Single database with `tenant_id` scoping via `BelongsToTenant` trait
- Global query scopes automatically filter all data by tenant
- `TenantScope` middleware validates active tenant on every request

### Roles & Permissions
- 4 default roles: **Admin**, **Manager**, **Sales**, **Viewer**
- 51 granular permissions (12 modules x 4 actions + 3 special)
- Admin-only user and role management

### CRM Modules

#### Contacts
- Full CRUD with search, filtering, sorting, pagination
- Tag management (polymorphic many-to-many)
- Contact relationships (self-referencing)
- Activity timeline and notes
- Account associations

#### Accounts (Companies)
- Full CRUD with search and filtering
- Parent/child hierarchy (self-referencing)
- Stakeholder management (contact pivot with role)
- Address management (polymorphic: billing/shipping/other)

#### Leads
- Full CRUD with search and filtering
- Lead scoring (Hot / Warm / Cold)
- Qualification status (New / Contacted / Qualified / Converted / Recycled)
- Table and Kanban board views
- Lead conversion to Contact + Account (transactional)
- Tag management

### Sales Force Automation (SFA)

#### Opportunities (Deals)
- Full CRUD with search, filtering, sorting, pagination
- Configurable pipeline stages with drag-and-drop Kanban board
- Table and Kanban board views (toggle)
- Deal amount, probability, weighted amount calculation
- Sales team management with role and commission split percentages
- Linked quotes, activities, and notes
- Won/Lost tracking with timestamps and lost reason
- Source tracking (web, referral, partner, outbound, inbound)

#### Products
- Full CRUD product/service catalog
- SKU, unit price, cost price, margin calculation
- Category and unit type management
- Active/inactive status
- Price books with per-product pricing and minimum quantities

#### Quotes
- Full CRUD with auto-generated quote numbers (Q-XXXXX)
- Line item builder with product selection, quantity, discounts
- Automatic subtotal, discount, tax, and total calculations
- Quote status workflow (Draft → Sent → Accepted/Rejected/Expired)
- PDF generation and download
- Linked to opportunities, accounts, and contacts
- Terms and conditions, notes

#### Forecasting
- Pipeline value and weighted forecast summary
- Pipeline breakdown by stage (visual bar chart)
- Sales targets (monthly/quarterly/yearly quotas)
- Target vs actual attainment tracking with progress indicators
- Per-rep and team-level targets
- Period-based filtering (quarterly navigation)

### Dashboard
- KPI stats cards (Total Contacts, Accounts, Leads, Converted Leads)
- Recent activity feed

### Settings
- User profile management
- Password change
- Admin: User management with role assignment
- Admin: Role and permission management

## Project Structure

```
navcrm/
├── app/
│   ├── Enums/                          # PHP 8.2 enums for status/score values
│   │   ├── ActivityType.php
│   │   ├── AddressType.php
│   │   ├── ForecastCategory.php
│   │   ├── LeadScore.php
│   │   ├── LeadStatus.php
│   │   ├── OpportunitySource.php
│   │   └── QuoteStatus.php
│   │
│   ├── Http/
│   │   ├── Controllers/Api/            # 17 RESTful API controllers
│   │   │   ├── AccountController.php
│   │   │   ├── ActivityController.php
│   │   │   ├── AddressController.php
│   │   │   ├── AuthController.php
│   │   │   ├── ContactController.php
│   │   │   ├── ForecastController.php
│   │   │   ├── LeadController.php
│   │   │   ├── NoteController.php
│   │   │   ├── OpportunityController.php
│   │   │   ├── PipelineStageController.php
│   │   │   ├── PriceBookController.php
│   │   │   ├── ProductController.php
│   │   │   ├── QuoteController.php
│   │   │   ├── RolePermissionController.php
│   │   │   ├── SalesTargetController.php
│   │   │   ├── TagController.php
│   │   │   └── UserController.php
│   │   ├── Middleware/
│   │   │   └── TenantScope.php         # Enforces tenant isolation per request
│   │   ├── Requests/                   # Form request validation classes
│   │   │   ├── Account/
│   │   │   ├── Auth/
│   │   │   ├── Contact/
│   │   │   ├── ForecastEntry/
│   │   │   ├── Lead/
│   │   │   ├── Opportunity/
│   │   │   ├── PriceBook/
│   │   │   ├── Product/
│   │   │   ├── Quote/
│   │   │   └── SalesTarget/
│   │   └── Resources/                  # API resource transformers (16 resources)
│   │       ├── AccountResource.php
│   │       ├── ActivityResource.php
│   │       ├── AddressResource.php
│   │       ├── ContactResource.php
│   │       ├── LeadResource.php
│   │       ├── NoteResource.php
│   │       ├── OpportunityResource.php
│   │       ├── PipelineStageResource.php
│   │       ├── PriceBookEntryResource.php
│   │       ├── PriceBookResource.php
│   │       ├── ProductResource.php
│   │       ├── QuoteLineItemResource.php
│   │       ├── QuoteResource.php
│   │       ├── SalesTargetResource.php
│   │       ├── TagResource.php
│   │       └── UserResource.php
│   │
│   ├── Models/                         # Eloquent models with BelongsToTenant trait
│   │   ├── Concerns/
│   │   │   └── BelongsToTenant.php     # Global scope trait for tenant isolation
│   │   ├── Account.php
│   │   ├── Activity.php
│   │   ├── Address.php
│   │   ├── Contact.php
│   │   ├── ForecastEntry.php
│   │   ├── Lead.php
│   │   ├── Note.php
│   │   ├── Opportunity.php
│   │   ├── PipelineStage.php
│   │   ├── PriceBook.php
│   │   ├── PriceBookEntry.php
│   │   ├── Product.php
│   │   ├── Quote.php
│   │   ├── QuoteLineItem.php
│   │   ├── SalesTarget.php
│   │   ├── Tag.php
│   │   ├── Tenant.php
│   │   └── User.php
│   │
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   │
│   └── Services/                       # Business logic services
│       ├── ForecastService.php
│       ├── LeadConversionService.php
│       ├── QuoteCalculationService.php
│       └── QuotePdfService.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/                             # Laravel configuration files
│   ├── app.php
│   ├── auth.php
│   ├── cors.php
│   ├── database.php
│   ├── mail.php
│   ├── permission.php
│   └── ...
│
├── database/
│   ├── factories/                      # Model factories for testing
│   │   ├── AccountFactory.php
│   │   ├── ContactFactory.php
│   │   ├── LeadFactory.php
│   │   ├── TenantFactory.php
│   │   └── UserFactory.php
│   ├── migrations/                     # 27 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── DemoDataSeeder.php
│       └── RolePermissionSeeder.php
│
├── public/
│   ├── index.php                       # Application entry point
│   ├── css/
│   │   └── navcrm-theme.css
│   └── js/
│       └── navcrm-theme.js
│
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/                          # Blade templates
│       ├── welcome.blade.php
│       ├── layouts/
│       │   ├── app.blade.php           # Main authenticated layout
│       │   └── auth.blade.php          # Auth layout
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── accounts/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── show.blade.php
│       ├── contacts/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── show.blade.php
│       ├── leads/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── show.blade.php
│       ├── opportunities/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── show.blade.php
│       ├── quotes/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── show.blade.php
│       │   └── pdf.blade.php           # Quote PDF template (DomPDF)
│       ├── price-books/
│       │   └── index.blade.php
│       ├── products/
│       │   ├── index.blade.php
│       │   └── create.blade.php
│       └── forecasts/
│           └── index.blade.php
│
├── routes/
│   ├── api.php                         # All API routes
│   ├── web.php                         # Web routes
│   └── console.php
│
├── tests/
│   ├── Feature/
│   │   ├── Account/
│   │   │   └── AccountCrudTest.php
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   └── RegisterTest.php
│   │   ├── Contact/
│   │   │   └── ContactCrudTest.php
│   │   ├── Lead/
│   │   │   └── LeadCrudTest.php
│   │   └── TenantIsolationTest.php
│   ├── Unit/
│   │   └── Services/
│   │       └── LeadConversionServiceTest.php
│   └── TestCase.php
│
├── composer.json
├── package.json
├── vite.config.js
├── phpunit.xml
└── README.md
```

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8.0+
- XAMPP (or equivalent local server)

## Setup

### 1. Clone the repository

```bash
git clone <repository-url>
cd navcrm
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment

```bash
cp .env.example .env

# Edit .env: set DB_DATABASE=navcrm, DB_USERNAME, DB_PASSWORD
php artisan key:generate
```

### 4. Set up the database

```bash
# Create database
mysql -u root -e "CREATE DATABASE navcrm"

# Run migrations
php artisan migrate

# Seed roles, permissions, and demo data
php artisan db:seed
```

### 5. Build assets

```bash
npm run build
# or for development with hot reload:
npm run dev
```

### 6. Start the server

```bash
php artisan serve
```

The app will be available at `http://localhost:8000`.

## Demo Accounts

After running `php artisan db:seed`, you can log in with these demo accounts:

| Email | Password | Role |
|-------|----------|------|
| `admin@acme.test` | `password` | Admin |
| `manager@acme.test` | `password` | Manager |
| `sales1@acme.test` | `password` | Sales |
| `viewer@acme.test` | `password` | Viewer |

## API Routes

### Public
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new tenant + user |
| POST | `/api/auth/login` | Login and get token |
| POST | `/api/auth/forgot-password` | Send password reset email |
| POST | `/api/auth/reset-password` | Reset password with token |

### Protected (requires Bearer token)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/logout` | Revoke current token |
| GET | `/api/auth/me` | Get authenticated user |
| GET/PUT | `/api/profile` | Get/update profile |
| PUT | `/api/profile/password` | Change password |
| CRUD | `/api/contacts` | Contact management |
| CRUD | `/api/accounts` | Account management |
| CRUD | `/api/leads` | Lead management |
| CRUD | `/api/tags` | Tag management |
| GET/POST/DELETE | `/api/activities` | Activity management |
| GET/POST/PUT/DELETE | `/api/notes` | Note management |
| CRUD | `/api/pipeline-stages` | Pipeline stage management |
| POST | `/api/pipeline-stages/reorder` | Reorder pipeline stages |
| CRUD | `/api/opportunities` | Opportunity management |
| PUT | `/api/opportunities/{id}/stage` | Update opportunity stage (kanban) |
| GET/POST/PUT/DELETE | `/api/opportunities/{id}/team` | Sales team management |
| CRUD | `/api/products` | Product catalog |
| CRUD | `/api/price-books` | Price book management |
| POST/PUT/DELETE | `/api/price-books/{id}/entries` | Price book entries |
| CRUD | `/api/quotes` | Quote management |
| PUT | `/api/quotes/{id}/status` | Update quote status |
| GET | `/api/quotes/{id}/pdf` | Download quote PDF |
| CRUD | `/api/sales-targets` | Sales target management |
| GET | `/api/forecasts` | Forecast data |
| GET | `/api/forecasts/summary` | Forecast summary |

### Admin Only (requires `admin` role)
| Method | Endpoint | Description |
|--------|----------|-------------|
| CRUD | `/api/users` | User management |
| POST | `/api/users/{id}/sync-roles` | Assign roles to user |
| CRUD | `/api/roles` | Role management |
| GET | `/api/permissions` | List all permissions |

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=AuthTest
php artisan test --filter=ContactCrudTest
php artisan test --filter=TenantIsolationTest
```

Current test suite: **33 tests, 146 assertions** covering auth, CRUD operations, tenant isolation, and lead conversion.

## Database Schema

### Core Tables
- `tenants` - Organizations/companies
- `users` - Users with `tenant_id` scoping
- `roles` / `permissions` - Spatie permission tables

### CRM Tables
- `contacts` - Contact records with address and social fields
- `accounts` - Company records with parent/child hierarchy
- `leads` - Lead records with status and score enums
- `account_contact` - Pivot with role and `is_primary`
- `contact_relationships` - Self-referencing with relationship type

### SFA Tables
- `pipeline_stages` - Configurable stages per tenant (position, probability, color, is_won/is_lost)
- `opportunities` - Deals with amount, probability, close date, stage, owner
- `opportunity_team` - Sales team pivot with role and split percentage
- `products` - Product/service catalog with SKU, pricing, category
- `price_books` - Named pricing lists (default/active flags)
- `price_book_entries` - Per-product prices within a price book
- `quotes` - Quote headers with auto-numbered quotes, discount/tax calculations
- `quote_line_items` - Line items with product, quantity, unit price, discount
- `sales_targets` - Monthly/quarterly/yearly quota targets per user or team
- `forecast_entries` - Manual forecast overrides by category

### Polymorphic Tables
- `tags` / `taggables` - Tagging system for contacts and leads
- `activities` - Activity log for contacts, accounts, leads, opportunities
- `notes` - Notes for contacts, accounts, leads, opportunities
- `addresses` - Billing/shipping addresses for accounts

## Architecture Decisions

1. **Single DB multi-tenancy** - `tenant_id` column with global scopes for simplicity
2. **Token-based auth** - Sanctum tokens for API authentication
3. **Polymorphic tables** - Activities, notes, addresses, tags shared across modules
4. **PHP 8.2 enums** - Type-safe status and score values
5. **Blade templates** - Server-side rendering with Vite-bundled assets
6. **Quote PDF generation** - Server-side PDF rendering with barryvdh/laravel-dompdf
7. **Configurable pipelines** - Tenant-specific pipeline stages with position ordering and color coding
8. **Forecast calculations** - Service-based weighted pipeline and target vs actual computations

## License

MIT
