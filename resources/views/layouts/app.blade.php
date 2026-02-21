<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Dashboard') — NavCRM</title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

  <!-- Google Fonts — Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

  <!-- NavCRM Theme -->
  <link rel="stylesheet" href="{{ asset('css/navcrm-theme.css') }}" />

  @stack('styles')
</head>
<body>

{{-- Mobile sidebar overlay --}}
<div class="ncv-sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

<div class="ncv-wrapper" id="appWrapper">

  {{-- ====================================================================
       SIDEBAR
  ===================================================================== --}}
  <aside class="ncv-sidebar" id="sidebar">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="ncv-brand">
      <div class="ncv-brand-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
        </svg>
      </div>
      <span class="ncv-brand-text">Nav<span>CRM</span></span>
    </a>

    {{-- Navigation --}}
    <nav class="ncv-nav" id="sidebarNav">

      {{-- Main --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Main</div>

        <a href="{{ route('dashboard') }}"
           class="ncv-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           data-tooltip="Dashboard">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
          </svg>
          <span class="ncv-nav-text">Dashboard</span>
        </a>
      </div>

      {{-- CRM --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">CRM</div>

        <a href="{{ route('contacts.index') }}"
           class="ncv-nav-item {{ request()->routeIs('contacts.*') ? 'active' : '' }}"
           data-tooltip="Contacts">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <span class="ncv-nav-text">Contacts</span>
        </a>

        <a href="{{ route('accounts.index') }}"
           class="ncv-nav-item {{ request()->routeIs('accounts.*') ? 'active' : '' }}"
           data-tooltip="Accounts">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9,22 9,12 15,12 15,22"/>
          </svg>
          <span class="ncv-nav-text">Accounts</span>
        </a>

        <a href="{{ route('leads.index') }}"
           class="ncv-nav-item {{ request()->routeIs('leads.*') ? 'active' : '' }}"
           data-tooltip="Leads">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/>
          </svg>
          <span class="ncv-nav-text">Leads</span>
          <span class="ncv-nav-badge">12</span>
        </a>

        <a href="{{ route('activities.index') }}"
           class="ncv-nav-item {{ request()->routeIs('activities.*') ? 'active' : '' }}"
           data-tooltip="Activities">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
          <span class="ncv-nav-text">Activities</span>
        </a>
      </div>

      {{-- Sales --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Sales</div>

        <a href="{{ route('opportunities.index') }}"
           class="ncv-nav-item {{ request()->routeIs('opportunities.*') ? 'active' : '' }}"
           data-tooltip="Opportunities">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6"  y1="20" x2="6"  y2="14"/>
          </svg>
          <span class="ncv-nav-text">Opportunities</span>
        </a>

        <a href="{{ route('quotes.index') }}"
           class="ncv-nav-item {{ request()->routeIs('quotes.*') ? 'active' : '' }}"
           data-tooltip="Quotes">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10,9 9,9 8,9"/>
          </svg>
          <span class="ncv-nav-text">Quotes</span>
        </a>

        <a href="{{ route('products.index') }}"
           class="ncv-nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}"
           data-tooltip="Products">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
          </svg>
          <span class="ncv-nav-text">Products</span>
        </a>

        <a href="{{ route('forecasts.index') }}"
           class="ncv-nav-item {{ request()->routeIs('forecasts.*') ? 'active' : '' }}"
           data-tooltip="Forecasts">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
          </svg>
          <span class="ncv-nav-text">Forecasts</span>
        </a>
      </div>

      {{-- Marketing --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Marketing</div>

        <a href="{{ route('marketing.campaigns.index') }}"
           class="ncv-nav-item {{ request()->routeIs('marketing.campaigns.*') ? 'active' : '' }}"
           data-tooltip="Campaigns">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
          <span class="ncv-nav-text">Campaigns</span>
        </a>

        <a href="{{ route('marketing.email-campaigns.index') }}"
           class="ncv-nav-item {{ request()->routeIs('marketing.email-campaigns.*') ? 'active' : '' }}"
           data-tooltip="Email Campaigns">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <span class="ncv-nav-text">Email Campaigns</span>
        </a>

        <a href="{{ route('marketing.email-templates.index') }}"
           class="ncv-nav-item {{ request()->routeIs('marketing.email-templates.*') ? 'active' : '' }}"
           data-tooltip="Email Templates">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <path d="M3 9h18M9 21V9"/>
          </svg>
          <span class="ncv-nav-text">Email Templates</span>
        </a>

        <a href="{{ route('marketing.landing-pages.index') }}"
           class="ncv-nav-item {{ request()->routeIs('marketing.landing-pages.*') ? 'active' : '' }}"
           data-tooltip="Landing Pages">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <path d="M8 21h8M12 17v4"/>
          </svg>
          <span class="ncv-nav-text">Landing Pages</span>
        </a>

        <a href="{{ route('marketing.web-forms.index') }}"
           class="ncv-nav-item {{ request()->routeIs('marketing.web-forms.*') ? 'active' : '' }}"
           data-tooltip="Web Forms">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <line x1="10" y1="9" x2="8" y2="9"/>
          </svg>
          <span class="ncv-nav-text">Web Forms</span>
        </a>
      </div>

      {{-- Support --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Support</div>

        <a href="{{ route('support.tickets.index') }}"
           class="ncv-nav-item {{ request()->routeIs('support.tickets.*') ? 'active' : '' }}"
           data-tooltip="Tickets">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
          </svg>
          <span class="ncv-nav-text">Tickets</span>
        </a>

        <a href="{{ route('support.kb-articles.index') }}"
           class="ncv-nav-item {{ request()->routeIs('support.kb-articles.*') ? 'active' : '' }}"
           data-tooltip="Knowledge Base">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
          </svg>
          <span class="ncv-nav-text">Knowledge Base</span>
        </a>
      </div>

      {{-- Customer Success --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Customer Success</div>

        <a href="{{ route('success.dashboard') }}"
           class="ncv-nav-item {{ request()->routeIs('success.dashboard') ? 'active' : '' }}"
           data-tooltip="CS Dashboard">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
          <span class="ncv-nav-text">CS Dashboard</span>
        </a>

        <a href="{{ route('success.onboarding.index') }}"
           class="ncv-nav-item {{ request()->routeIs('success.onboarding.*') ? 'active' : '' }}"
           data-tooltip="Onboarding">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>
          <span class="ncv-nav-text">Onboarding</span>
        </a>

        <a href="{{ route('success.health-scores.index') }}"
           class="ncv-nav-item {{ request()->routeIs('success.health-scores.*') ? 'active' : '' }}"
           data-tooltip="Health Scores">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
          <span class="ncv-nav-text">Health Scores</span>
        </a>

        <a href="{{ route('success.surveys.index') }}"
           class="ncv-nav-item {{ request()->routeIs('success.surveys.*') ? 'active' : '' }}"
           data-tooltip="Surveys">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <span class="ncv-nav-text">Surveys</span>
        </a>
      </div>

      {{-- Activity --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Activity</div>

        <a href="{{ route('activity.tasks.index') }}"
           class="ncv-nav-item {{ request()->routeIs('activity.tasks.*') ? 'active' : '' }}"
           data-tooltip="Tasks">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>
          <span class="ncv-nav-text">Tasks</span>
        </a>

        <a href="{{ route('activity.calendar.index') }}"
           class="ncv-nav-item {{ request()->routeIs('activity.calendar.*') ? 'active' : '' }}"
           data-tooltip="Calendar">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8"  y1="2" x2="8"  y2="6"/>
            <line x1="3"  y1="10" x2="21" y2="10"/>
          </svg>
          <span class="ncv-nav-text">Calendar</span>
        </a>

        <a href="{{ route('activity.calls.index') }}"
           class="ncv-nav-item {{ request()->routeIs('activity.calls.*') ? 'active' : '' }}"
           data-tooltip="Call Logs">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.38 2 2 0 0 1 3.58 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.18 6.18l1.27-.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
          <span class="ncv-nav-text">Call Logs</span>
        </a>

        <a href="{{ route('activity.emails.index') }}"
           class="ncv-nav-item {{ request()->routeIs('activity.emails.*') ? 'active' : '' }}"
           data-tooltip="Email Logs">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <span class="ncv-nav-text">Email Logs</span>
        </a>
      </div>

      {{-- Analytics --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Analytics</div>

        <a href="{{ route('analytics.dashboard') }}"
           class="ncv-nav-item {{ request()->routeIs('analytics.dashboard') ? 'active' : '' }}"
           data-tooltip="Analytics Dashboard">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="12" width="4" height="9" rx="1"/>
            <rect x="10" y="7" width="4" height="14" rx="1"/>
            <rect x="17" y="3" width="4" height="18" rx="1"/>
          </svg>
          <span class="ncv-nav-text">Analytics Dashboard</span>
        </a>

        <a href="{{ route('analytics.reports.sales-activity') }}"
           class="ncv-nav-item {{ request()->routeIs('analytics.reports.sales-activity') ? 'active' : '' }}"
           data-tooltip="Sales Activity">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.38 2 2 0 0 1 3.58 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.18 6.18l1.27-.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
          <span class="ncv-nav-text">Sales Activity</span>
        </a>

        <a href="{{ route('analytics.reports.sales-performance') }}"
           class="ncv-nav-item {{ request()->routeIs('analytics.reports.sales-performance') ? 'active' : '' }}"
           data-tooltip="Sales Performance">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
          <span class="ncv-nav-text">Sales Performance</span>
        </a>

        <a href="{{ route('analytics.reports.funnel') }}"
           class="ncv-nav-item {{ request()->routeIs('analytics.reports.funnel') ? 'active' : '' }}"
           data-tooltip="Funnel Analysis">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 3H2l8 9.46V19l4 2V12.46L22 3z"/>
          </svg>
          <span class="ncv-nav-text">Funnel Analysis</span>
        </a>

        <a href="{{ route('analytics.reports.service') }}"
           class="ncv-nav-item {{ request()->routeIs('analytics.reports.service') ? 'active' : '' }}"
           data-tooltip="Service Reports">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <span class="ncv-nav-text">Service Reports</span>
        </a>
      </div>

      {{-- Finance --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Finance</div>

        <a href="{{ route('finance.invoices.index') }}"
           class="ncv-nav-item {{ request()->routeIs('finance.invoices.*') ? 'active' : '' }}"
           data-tooltip="Invoices">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <span class="ncv-nav-text">Invoices</span>
        </a>

        <a href="{{ route('finance.expenses.index') }}"
           class="ncv-nav-item {{ request()->routeIs('finance.expenses.*') ? 'active' : '' }}"
           data-tooltip="Expenses">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
            <line x1="1" y1="10" x2="23" y2="10"/>
          </svg>
          <span class="ncv-nav-text">Expenses</span>
        </a>
      </div>

      {{-- Projects & Delivery --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Projects</div>

        <a href="{{ route('projects.index') }}"
           class="ncv-nav-item {{ request()->routeIs('projects.*') && !request()->routeIs('projects.') ? 'active' : '' }}"
           data-tooltip="Projects">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          <span class="ncv-nav-text">Projects</span>
        </a>

        <a href="{{ route('timesheets.index') }}"
           class="ncv-nav-item {{ request()->routeIs('timesheets.index') || request()->routeIs('timesheets.show') || request()->routeIs('timesheets.create') || request()->routeIs('timesheets.edit') ? 'active' : '' }}"
           data-tooltip="Timesheets">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          <span class="ncv-nav-text">Timesheets</span>
        </a>

        <a href="{{ route('timesheets.workload') }}"
           class="ncv-nav-item {{ request()->routeIs('timesheets.workload') ? 'active' : '' }}"
           data-tooltip="Workload">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <span class="ncv-nav-text">Workload</span>
        </a>
      </div>

      {{-- Documents & Contracts --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Documents</div>

        <a href="{{ route('documents.index') }}"
           class="ncv-nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}"
           data-tooltip="Documents">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <span class="ncv-nav-text">Documents</span>
        </a>

        <a href="{{ route('document-templates.index') }}"
           class="ncv-nav-item {{ request()->routeIs('document-templates.*') ? 'active' : '' }}"
           data-tooltip="Templates">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <path d="M3 9h18"/>
            <path d="M9 21V9"/>
          </svg>
          <span class="ncv-nav-text">Templates</span>
        </a>
      </div>

      {{-- Automation --}}
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Automation</div>

        <a href="{{ route('workflows.index') }}"
           class="ncv-nav-item {{ request()->routeIs('workflows.*') ? 'active' : '' }}"
           data-tooltip="Workflows">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
          </svg>
          <span class="ncv-nav-text">Workflows</span>
        </a>

        <a href="{{ route('approvals.index') }}"
           class="ncv-nav-item {{ request()->routeIs('approvals.*') ? 'active' : '' }}"
           data-tooltip="Approvals">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <span class="ncv-nav-text">Approvals</span>
        </a>
      </div>

      {{-- Admin (conditional) --}}
      @if(auth()->user()?->hasRole('admin'))
      <div class="ncv-nav-section">
        <div class="ncv-nav-label">Admin</div>

        <a href="{{ route('settings.users.index') }}"
           class="ncv-nav-item {{ request()->routeIs('settings.users.*') ? 'active' : '' }}"
           data-tooltip="Users">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span class="ncv-nav-text">Users</span>
        </a>

        <a href="{{ route('settings.roles.index') }}"
           class="ncv-nav-item {{ request()->routeIs('settings.roles.*') ? 'active' : '' }}"
           data-tooltip="Roles">
          <svg class="ncv-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <span class="ncv-nav-text">Roles & Permissions</span>
        </a>
      </div>
      @endif

    </nav>

    {{-- Footer / User --}}
    <div class="ncv-sidebar-footer">
      <div class="ncv-dropdown w-100" id="userDropdown">
        <div class="ncv-user-card" onclick="toggleDropdown('userDropdownMenu')">
          <div class="ncv-user-avatar">
            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
          </div>
          <div style="overflow:hidden; flex:1;">
            <div class="ncv-user-name">{{ auth()->user()?->name ?? 'User' }}</div>
            <div class="ncv-user-role">{{ auth()->user()?->getRoleNames()->first() ?? 'Member' }}</div>
          </div>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:#7a9bc4; flex-shrink:0;">
            <polyline points="18,15 12,9 6,15"/>
          </svg>
        </div>

        <div class="ncv-dropdown-menu" id="userDropdownMenu" style="bottom: calc(100% + 6px); top: auto;">
          <a href="{{ route('settings.profile') }}" class="ncv-dropdown-item">
            <i class="bi bi-person-circle" style="font-size: .9rem;"></i> My Profile
          </a>
          <a href="{{ route('settings.index') }}" class="ncv-dropdown-item">
            <i class="bi bi-gear" style="font-size: .9rem;"></i> Settings
          </a>
          <div class="ncv-dropdown-divider"></div>
          <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="ncv-dropdown-item danger">
              <i class="bi bi-box-arrow-right" style="font-size: .9rem;"></i> Sign Out
            </button>
          </form>
        </div>
      </div>
    </div>

  </aside>

  {{-- ====================================================================
       MAIN
  ===================================================================== --}}
  <div class="ncv-main" id="mainContent">

    {{-- Topbar --}}
    <header class="ncv-topbar">
      {{-- Sidebar toggle --}}
      <button class="ncv-topbar-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <i class="bi bi-layout-sidebar fs-5"></i>
      </button>

      {{-- Breadcrumb --}}
      <nav class="ncv-breadcrumb">
        <a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: inherit;">
          <i class="bi bi-house-door" style="font-size:.8rem;"></i>
        </a>
        <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
        @yield('breadcrumb-items')
        <span class="ncv-breadcrumb-current">@yield('page-title', 'Dashboard')</span>
      </nav>

      {{-- Global Search --}}
      <div class="ncv-search d-none d-lg-block">
        <svg class="ncv-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" placeholder="Search contacts, leads, deals…" id="globalSearch" />
      </div>

      {{-- Actions --}}
      <div class="ncv-topbar-actions ms-auto">

        {{-- Search toggle (mobile) --}}
        <button class="ncv-topbar-btn d-lg-none" title="Search">
          <i class="bi bi-search fs-6"></i>
        </button>

        {{-- Notifications --}}
        <button class="ncv-topbar-btn" title="Notifications">
          <i class="bi bi-bell fs-6"></i>
          <span class="ncv-topbar-btn-badge"></span>
        </button>

        {{-- Help --}}
        <button class="ncv-topbar-btn" title="Help">
          <i class="bi bi-question-circle fs-6"></i>
        </button>

        {{-- User (topbar) --}}
        <div class="ncv-dropdown" id="topbarUserDropdown">
          <button class="ncv-topbar-user" onclick="toggleDropdown('topbarUserMenu')">
            <div class="ncv-topbar-avatar">
              {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
            </div>
            <span class="ncv-topbar-username d-none d-md-block">
              {{ auth()->user()?->name ?? 'User' }}
            </span>
            <i class="bi bi-chevron-down" style="font-size:.7rem; color: var(--text-muted);"></i>
          </button>

          <div class="ncv-dropdown-menu" id="topbarUserMenu">
            <a href="{{ route('settings.profile') }}" class="ncv-dropdown-item">
              <i class="bi bi-person-circle"></i> Profile
            </a>
            <a href="{{ route('settings.index') }}" class="ncv-dropdown-item">
              <i class="bi bi-gear"></i> Settings
            </a>
            <div class="ncv-dropdown-divider"></div>
            <form method="POST" action="{{ route('auth.logout') }}">
              @csrf
              <button type="submit" class="ncv-dropdown-item danger">
                <i class="bi bi-box-arrow-right"></i> Sign Out
              </button>
            </form>
          </div>
        </div>

        {{-- Mobile sidebar open --}}
        <button class="ncv-topbar-btn d-lg-none" onclick="openMobileSidebar()" title="Menu">
          <i class="bi bi-list fs-5"></i>
        </button>

      </div>
    </header>

    {{-- Page Content --}}
    <main class="ncv-content">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 border-0"
             style="background:#d1fae5; color:#065f46; border-radius:.75rem;"
             role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 border-0"
             style="background:#fee2e2; color:#991b1b; border-radius:.75rem;"
             role="alert">
          <i class="bi bi-exclamation-circle-fill me-2"></i>
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @yield('content')
    </main>

  </div>{{-- end .ncv-main --}}

</div>{{-- end .ncv-wrapper --}}

{{-- Toast container --}}
<div class="ncv-toast-container" id="toastContainer"></div>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmJ3H/jviwjBVfJSYBDiGhCDHMer"
        crossorigin="anonymous"></script>

{{-- NavCRM Theme JS --}}
<script src="{{ asset('js/navcrm-theme.js') }}"></script>

@stack('scripts')
</body>
</html>
