# NavCRM

A multi-tenant CRM (Customer Relationship Management) application built with Laravel 12 and Next.js 15.

## Tech Stack

### Backend
- **Laravel 12** (PHP 8.2+)
- **MySQL** (single database, `tenant_id` column multi-tenancy)
- **Laravel Sanctum** (token-based API authentication)
- **Spatie Laravel Permission** (roles & permissions)

### Frontend
- **Next.js 15** (App Router, TypeScript)
- **Tailwind CSS v4**
- **Zustand** (state management with persist)
- **React Hook Form + Zod** (form validation)
- **Axios** (HTTP client)
- **Lucide React** (icons)

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
- 27 granular permissions (6 modules x 4 actions + 3 special)
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
├── backend/                    # Laravel 12 API
│   ├── app/
│   │   ├── Enums/              # LeadStatus, LeadScore, ActivityType, AddressType
│   │   ├── Http/
│   │   │   ├── Controllers/Api/ # 10 API controllers
│   │   │   ├── Middleware/      # TenantScope
│   │   │   ├── Requests/       # Form request validation
│   │   │   └── Resources/      # API resource transformers
│   │   ├── Models/             # Eloquent models with BelongsToTenant trait
│   │   └── Services/          # LeadConversionService
│   ├── database/
│   │   ├── factories/          # Model factories for testing
│   │   ├── migrations/         # 17 migration files
│   │   └── seeders/           # RolePermission + DemoData seeders
│   ├── routes/api.php          # All API routes
│   └── tests/                  # PHPUnit feature & unit tests
│
├── frontend/                   # Next.js 15 App
│   └── src/
│       ├── app/
│       │   ├── (auth)/         # Login, Register, Forgot Password
│       │   └── (dashboard)/    # Dashboard, Contacts, Accounts, Leads, Settings
│       ├── components/
│       │   ├── ui/             # Button, Input, Card, Dialog, Table, Toast, etc.
│       │   ├── layout/         # Sidebar, Header, Breadcrumbs, Mobile Nav
│       │   ├── shared/         # DataTable, SearchInput, ConfirmDialog, StatsCard
│       │   ├── contacts/       # Contact form, table, filters, timeline
│       │   ├── accounts/       # Account form, table, hierarchy, stakeholders
│       │   ├── leads/          # Lead form, table, kanban, conversion dialog
│       │   ├── activities/     # Activity timeline, activity form
│       │   └── tags/           # Tag input, tag badge
│       ├── lib/
│       │   ├── api/            # Axios client + API modules
│       │   ├── stores/         # Zustand auth + UI stores
│       │   ├── utils/          # cn(), formatDate(), constants
│       │   └── validations/    # Zod schemas
│       ├── providers/          # AuthProvider, ToastProvider
│       └── types/              # TypeScript interfaces
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

### 2. Backend setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file and configure
cp .env.example .env
# Edit .env: set DB_DATABASE=navcrm, DB_USERNAME, DB_PASSWORD

# Generate app key
php artisan key:generate

# Create database
mysql -u root -e "CREATE DATABASE navcrm"

# Run migrations
php artisan migrate

# Seed roles, permissions, and demo data
php artisan db:seed

# Start the server
php artisan serve
```

The API will be available at `http://localhost:8000/api`.

### 3. Frontend setup

```bash
cd frontend

# Install dependencies
npm install

# Create environment file
echo "NEXT_PUBLIC_API_URL=http://localhost:8000/api" > .env.local

# Start development server
npm run dev
```

The app will be available at `http://localhost:3000`.

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

### Admin Only (requires `admin` role)
| Method | Endpoint | Description |
|--------|----------|-------------|
| CRUD | `/api/users` | User management |
| POST | `/api/users/{id}/sync-roles` | Assign roles to user |
| CRUD | `/api/roles` | Role management |
| GET | `/api/permissions` | List all permissions |

## Testing

```bash
cd backend

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

### Polymorphic Tables
- `tags` / `taggables` - Tagging system for contacts and leads
- `activities` - Activity log for contacts, accounts, leads
- `notes` - Notes for contacts, accounts, leads
- `addresses` - Billing/shipping addresses for accounts

## Architecture Decisions

1. **Single DB multi-tenancy** - `tenant_id` column with global scopes for simplicity
2. **Token-based auth** - Sanctum tokens (not SPA cookies) since frontend/backend run on different ports
3. **Polymorphic tables** - Activities, notes, addresses, tags shared across modules
4. **PHP 8.2 enums** - Type-safe status and score values
5. **Client-side rendering** - Dashboard pages use `'use client'` since auth tokens are in localStorage

## License

MIT
