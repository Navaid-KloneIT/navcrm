@extends('layouts.app')

@section('title', $contact->name ?? 'Contact Profile')
@section('page-title', $contact->name ?? 'Contact Profile')

@section('breadcrumb-items')
  <a href="{{ route('contacts.index') }}" style="color:inherit;text-decoration:none;">Contacts</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .contact-hero {
    background: linear-gradient(135deg, #0d1f4e 0%, #1a3a8f 60%, #2563eb 100%);
    border-radius: var(--card-radius);
    padding: 2rem 1.75rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
  }
  .contact-hero::before {
    content: '';
    position: absolute;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
    top: -80px; right: -60px;
  }
  .contact-hero::after {
    content: '';
    position: absolute;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    bottom: -50px; right: 100px;
  }
  .contact-avatar-lg {
    width: 80px; height: 80px;
    border-radius: 1.25rem;
    background: rgba(255,255,255,.15);
    border: 2px solid rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
  }
  .contact-hero-name {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.03em;
    margin-bottom: .25rem;
  }
  .contact-hero-meta {
    font-size: .875rem;
    color: rgba(255,255,255,.7);
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: .5rem;
  }
  .contact-hero-meta span { display: flex; align-items: center; gap: .35rem; }

  .info-row {
    display: flex;
    align-items: flex-start;
    gap: .625rem;
    padding: .625rem 0;
    border-bottom: 1px solid var(--border-color);
    font-size: .875rem;
  }
  .info-row:last-child { border-bottom: none; }
  .info-row-label {
    font-size: .75rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    min-width: 110px;
    padding-top: 2px;
  }
  .info-row-value { color: var(--text-secondary); flex: 1; }
  .info-row-value a { color: var(--ncv-blue-600); text-decoration: none; }
  .info-row-value a:hover { text-decoration: underline; }
</style>
@endpush

@section('content')

{{-- Hero Banner --}}
<div class="contact-hero">
  <div class="d-flex align-items-start gap-3 position-relative" style="z-index:1;">
    <div class="contact-avatar-lg">{{ strtoupper(substr($contact->name ?? 'C', 0, 2)) }}</div>
    <div class="flex-1">
      <div class="contact-hero-name">{{ $contact->name ?? 'Sarah Johnson' }}</div>
      <div style="font-size:.9rem; color:rgba(255,255,255,.85);">
        {{ $contact->title ?? 'VP of Sales' }} at
        <strong>{{ $contact->account->name ?? 'Acme Corporation' }}</strong>
      </div>
      <div class="contact-hero-meta">
        <span><i class="bi bi-envelope"></i> {{ $contact->email ?? 'sarah@acme.com' }}</span>
        <span><i class="bi bi-telephone"></i> {{ $contact->phone ?? '+1 555-0101' }}</span>
        <span><i class="bi bi-geo-alt"></i> {{ $contact->city ?? 'New York, NY' }}</span>
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.875rem;">
        <span class="ncv-badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;">
          <span style="width:6px;height:6px;border-radius:50%;background:#34d399;display:inline-block;"></span>
          Customer
        </span>
        <span class="ncv-badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;">VIP</span>
        <span class="ncv-badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;">Decision Maker</span>
      </div>
    </div>
    <div class="ms-auto d-flex gap-2 flex-wrap" style="position:relative;z-index:1;">
      <button class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
        <i class="bi bi-telephone"></i> Log Call
      </button>
      <button class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
        <i class="bi bi-envelope"></i> Send Email
      </button>
      <a href="{{ route('contacts.edit', $contact->id ?? 1) }}"
         class="ncv-btn ncv-btn-sm" style="background:#fff;color:#1d4ed8;border:none;font-weight:700;">
        <i class="bi bi-pencil"></i> Edit
      </a>
    </div>
  </div>
</div>

<div class="row g-3">

  {{-- LEFT COLUMN: Profile Info --}}
  <div class="col-12 col-lg-4">

    {{-- Contact Details --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-person-lines-fill me-2" style="color:var(--ncv-blue-500);"></i>Contact Details</h6>
        <a href="{{ route('contacts.edit', $contact->id ?? 1) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-pencil"></i></a>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['label'=>'Full Name',   'value'=>$contact->name ?? 'Sarah Johnson'],
          ['label'=>'Title',       'value'=>$contact->title ?? 'VP of Sales'],
          ['label'=>'Department',  'value'=>$contact->department ?? 'Sales'],
          ['label'=>'Email',       'value'=>$contact->email ?? 'sarah@acme.com', 'type'=>'email'],
          ['label'=>'Mobile',      'value'=>$contact->mobile ?? '+1 555-0101'],
          ['label'=>'Work Phone',  'value'=>$contact->phone ?? '+1 555-0189'],
          ['label'=>'LinkedIn',    'value'=>$contact->linkedin ?? 'linkedin.com/in/sarahjohnson', 'type'=>'link'],
          ['label'=>'Twitter',     'value'=>$contact->twitter ?? '@sarah_j', 'type'=>'link'],
          ['label'=>'Created',     'value'=>$contact->created_at?->format('M d, Y') ?? 'Jan 15, 2026'],
        ] as $row)
        <div class="info-row">
          <span class="info-row-label">{{ $row['label'] }}</span>
          <span class="info-row-value">
            @if(isset($row['type']) && $row['type'] === 'email')
              <a href="mailto:{{ $row['value'] }}">{{ $row['value'] }}</a>
            @elseif(isset($row['type']) && $row['type'] === 'link')
              <a href="#" target="_blank" rel="noopener">{{ $row['value'] }} <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;"></i></a>
            @else
              {{ $row['value'] }}
            @endif
          </span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Address --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-geo-alt me-2" style="color:var(--ncv-blue-500);"></i>Address</h6>
      </div>
      <div class="ncv-card-body">
        <p style="font-size:.875rem;color:var(--text-secondary);margin:0;line-height:1.7;">
          {{ $contact->street ?? '1600 Amphitheatre Pkwy' }}<br />
          {{ $contact->city ?? 'New York' }}, {{ $contact->state ?? 'NY' }} {{ $contact->zip ?? '10001' }}<br />
          {{ $contact->country ?? 'United States' }}
        </p>
        <a href="https://maps.google.com" target="_blank"
           class="ncv-btn ncv-btn-ghost ncv-btn-sm mt-2" style="padding-left:0;">
          <i class="bi bi-map"></i> View on Map
        </a>
      </div>
    </div>

    {{-- Tags --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-tags me-2" style="color:var(--ncv-blue-500);"></i>Tags</h6>
        <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="openTagEditor()"><i class="bi bi-plus-lg"></i></button>
      </div>
      <div class="ncv-card-body">
        <div style="display:flex;gap:.4rem;flex-wrap:wrap;" id="tagContainer">
          @foreach(['Customer','VIP','Decision Maker','Q1 Focus','Renewal 2026'] as $tag)
          <span class="ncv-badge ncv-badge-primary" style="cursor:default;">
            {{ $tag }}
            <button onclick="removeTag(this)" style="background:none;border:none;padding:0;margin-left:3px;color:inherit;opacity:.6;cursor:pointer;font-size:.8rem;line-height:1;">&times;</button>
          </span>
          @endforeach
        </div>
        <div id="tagEditor" style="display:none;margin-top:.75rem;">
          <div style="display:flex;gap:.5rem;">
            <input type="text" class="ncv-input" id="newTagInput" placeholder="Type a tag and press Enter" style="height:36px;font-size:.8rem;" />
            <button class="ncv-btn ncv-btn-primary ncv-btn-sm" onclick="addTag()">Add</button>
          </div>
        </div>
      </div>
    </div>

    {{-- Related Account --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-building me-2" style="color:var(--ncv-blue-500);"></i>Account</h6>
      </div>
      <div class="ncv-card-body">
        <div style="display:flex;align-items:center;gap:.75rem;">
          <div class="ncv-table-avatar" style="width:40px;height:40px;background:#dbeafe;color:#1d4ed8;font-size:.85rem;border-radius:.625rem;">AC</div>
          <div>
            <a href="{{ route('accounts.show', 1) }}" style="font-weight:700;color:var(--text-primary);text-decoration:none;font-size:.9rem;">Acme Corporation</a>
            <div style="font-size:.775rem;color:var(--text-muted);">Technology · 500–1000 employees</div>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- RIGHT COLUMN: Tabs --}}
  <div class="col-12 col-lg-8">

    {{-- Tab Navigation --}}
    <div class="ncv-tabs mb-3" id="profileTabs">
      <a class="ncv-tab active" onclick="switchTab('timeline')" href="#">Activity Timeline</a>
      <a class="ncv-tab" onclick="switchTab('relationships')" href="#">Relationships</a>
      <a class="ncv-tab" onclick="switchTab('notes')" href="#">Notes <span class="ncv-badge ncv-badge-primary ms-1" style="padding:.15rem .45rem;font-size:.65rem;">3</span></a>
      <a class="ncv-tab" onclick="switchTab('opportunities')" href="#">Opportunities</a>
    </div>

    {{-- TIMELINE TAB --}}
    <div id="tab-timeline">
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Activity Timeline</h6>
          <div class="d-flex gap-2">
            <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="openLogActivity()">
              <i class="bi bi-plus-lg"></i> Log Activity
            </button>
            <select class="ncv-select" style="width:130px;height:34px;font-size:.78rem;">
              <option>All Activities</option>
              <option>Calls</option>
              <option>Emails</option>
              <option>Meetings</option>
              <option>Notes</option>
            </select>
          </div>
        </div>
        <div class="ncv-card-body">

          {{-- Log Activity Form (hidden) --}}
          <div id="logActivityForm" style="display:none;background:var(--ncv-blue-50);border-radius:.75rem;padding:1rem;margin-bottom:1.25rem;border:1.5px dashed var(--ncv-blue-300);">
            <div class="row g-2 mb-2">
              <div class="col-4">
                <select class="ncv-select" style="height:38px;font-size:.82rem;">
                  <option>📞 Call</option>
                  <option>📧 Email</option>
                  <option>📅 Meeting</option>
                  <option>📝 Note</option>
                  <option>✅ Task</option>
                </select>
              </div>
              <div class="col-4">
                <input type="date" class="ncv-input" style="height:38px;" value="{{ date('Y-m-d') }}" />
              </div>
              <div class="col-4">
                <input type="text" class="ncv-input" placeholder="Subject" style="height:38px;font-size:.82rem;" />
              </div>
            </div>
            <textarea class="ncv-textarea" rows="2" placeholder="Add notes about this activity…" style="font-size:.82rem;"></textarea>
            <div class="d-flex gap-2 mt-2">
              <button class="ncv-btn ncv-btn-primary ncv-btn-sm">Save Activity</button>
              <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="closeLogActivity()">Cancel</button>
            </div>
          </div>

          <ul class="ncv-timeline">
            @foreach([
              ['type'=>'Call',    'icon'=>'bi-telephone-fill', 'bg'=>'#dbeafe','color'=>'#2563eb',  'title'=>'Discovery call — 28 min',           'time'=>'Today, 2:30 PM',   'user'=>'You',             'desc'=>'Discussed renewal options. Sarah is open to expanding to 200 seats. Follow up with pricing sheet.'],
              ['type'=>'Email',   'icon'=>'bi-envelope-fill',  'bg'=>'#d1fae5','color'=>'#059669',  'title'=>'Sent: Q1 Renewal Proposal',         'time'=>'Today, 10:15 AM',  'user'=>'You',             'desc'=>'Emailed the updated proposal with enterprise discount applied.'],
              ['type'=>'Meeting', 'icon'=>'bi-calendar-check', 'bg'=>'#fef3c7','color'=>'#d97706',  'title'=>'Intro meeting — Acme HQ',           'time'=>'Yesterday, 3:00 PM','user'=>'John Smith',     'desc'=>'In-person meeting at client office. Demo of new reporting features was well received.'],
              ['type'=>'Note',    'icon'=>'bi-sticky-fill',    'bg'=>'#ede9fe','color'=>'#7c3aed',  'title'=>'Internal note',                     'time'=>'Feb 15, 4:12 PM',  'user'=>'You',             'desc'=>'Sarah mentioned competitor pricing concern — Salesforce quoted $85/user/month. We need to counter.'],
              ['type'=>'Email',   'icon'=>'bi-envelope-fill',  'bg'=>'#d1fae5','color'=>'#059669',  'title'=>'Received: Question on API limits',  'time'=>'Feb 14, 9:45 AM',  'user'=>'Sarah Johnson',   'desc'=>'Sarah asked about API rate limits for their custom integration.'],
              ['type'=>'Call',    'icon'=>'bi-telephone-fill', 'bg'=>'#dbeafe','color'=>'#2563eb',  'title'=>'Follow-up call — 12 min',           'time'=>'Feb 10, 11:00 AM', 'user'=>'You',             'desc'=>'Confirmed trial setup and addressed billing questions.'],
            ] as $activity)
            <li class="ncv-timeline-item">
              <div class="ncv-timeline-icon" style="background:{{ $activity['bg'] }};color:{{ $activity['color'] }};">
                <i class="bi {{ $activity['icon'] }}" style="font-size:.8rem;"></i>
              </div>
              <div class="ncv-timeline-body">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                  <div class="ncv-timeline-title">{{ $activity['title'] }}</div>
                  <span class="ncv-badge ncv-badge-muted" style="font-size:.65rem;white-space:nowrap;">{{ $activity['type'] }}</span>
                </div>
                <div class="ncv-timeline-time">
                  <i class="bi bi-clock"></i> {{ $activity['time'] }}
                  &nbsp;·&nbsp;
                  <i class="bi bi-person"></i> {{ $activity['user'] }}
                </div>
                <div class="ncv-timeline-desc">{{ $activity['desc'] }}</div>
              </div>
            </li>
            @endforeach
          </ul>

          <div class="text-center mt-2">
            <button class="ncv-btn ncv-btn-ghost ncv-btn-sm">
              <i class="bi bi-arrow-down-circle"></i> Load more activities
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- RELATIONSHIPS TAB --}}
    <div id="tab-relationships" style="display:none;">
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Contact Relationships</h6>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> Add Relationship
          </button>
        </div>
        <div class="ncv-card-body p-0">
          <table class="ncv-table">
            <thead>
              <tr>
                <th>Contact</th>
                <th>Company</th>
                <th>Relationship</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach([
                ['name'=>'Robert Acme','initials'=>'RA','company'=>'Acme Corporation','role'=>'Reports to (CEO)','color'=>'#2563eb'],
                ['name'=>'Linda Chen', 'initials'=>'LC','company'=>'Acme Corporation','role'=>'Colleague (Finance VP)','color'=>'#10b981'],
                ['name'=>'Mike Torres','initials'=>'MT','company'=>'TechStart Inc',    'role'=>'Former colleague','color'=>'#f59e0b'],
              ] as $rel)
              <tr>
                <td>
                  <div class="ncv-table-name">
                    <div class="ncv-table-avatar" style="background:{{ $rel['color'] }}18;color:{{ $rel['color'] }};">{{ $rel['initials'] }}</div>
                    <div class="ncv-table-cell-primary">{{ $rel['name'] }}</div>
                  </div>
                </td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $rel['company'] }}</td>
                <td><span class="ncv-badge ncv-badge-primary" style="font-size:.7rem;">{{ $rel['role'] }}</span></td>
                <td>
                  <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Remove">
                    <i class="bi bi-x-lg" style="font-size:.75rem;"></i>
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- NOTES TAB --}}
    <div id="tab-notes" style="display:none;">
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Notes</h6>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="document.getElementById('newNoteBox').style.display='block'">
            <i class="bi bi-plus-lg"></i> Add Note
          </button>
        </div>
        <div class="ncv-card-body">
          <div id="newNoteBox" style="display:none;background:var(--ncv-blue-50);border-radius:.75rem;padding:1rem;margin-bottom:1.25rem;border:1.5px dashed var(--ncv-blue-300);">
            <textarea class="ncv-textarea" rows="3" placeholder="Write a note about this contact…" style="font-size:.875rem;"></textarea>
            <div class="d-flex gap-2 mt-2">
              <button class="ncv-btn ncv-btn-primary ncv-btn-sm">Save Note</button>
              <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="this.closest('#newNoteBox').style.display='none'">Cancel</button>
            </div>
          </div>
          @foreach([
            ['text'=>'Sarah is key decision-maker for the enterprise renewal. Competitor: Salesforce at $85/user. Budget is confirmed at $45k. Best time to call: mornings before 10am EST.', 'user'=>'You', 'date'=>'Feb 15, 2026'],
            ['text'=>'Prefers detailed documentation before any demos. Has final approval authority but checks with CFO for deals >$50k.', 'user'=>'John Smith', 'date'=>'Feb 10, 2026'],
            ['text'=>'Met at SaaS Summit 2025. Warm introduction via David Kim. Very interested in our AI forecasting module.', 'user'=>'You', 'date'=>'Nov 12, 2025'],
          ] as $note)
          <div style="padding:.875rem;background:#f8faff;border-radius:.75rem;margin-bottom:.625rem;border:1px solid var(--border-color);">
            <p style="font-size:.875rem;color:var(--text-secondary);margin:0 0 .5rem;">{{ $note['text'] }}</p>
            <div style="font-size:.725rem;color:var(--text-muted);display:flex;align-items:center;gap:.75rem;">
              <span><i class="bi bi-person"></i> {{ $note['user'] }}</span>
              <span><i class="bi bi-calendar"></i> {{ $note['date'] }}</span>
              <button class="ncv-btn ncv-btn-ghost ncv-btn-sm ms-auto" style="padding:.15rem .5rem;font-size:.7rem;color:#ef4444;">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- OPPORTUNITIES TAB --}}
    <div id="tab-opportunities" style="display:none;">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Linked Opportunities</h6>
          <a href="{{ route('opportunities.create') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> New Deal
          </a>
        </div>
        <div class="ncv-card-body p-0">
          <table class="ncv-table">
            <thead>
              <tr><th>Opportunity</th><th>Stage</th><th>Value</th><th>Close Date</th></tr>
            </thead>
            <tbody>
              @foreach([
                ['name'=>'Acme Enterprise Renewal', 'stage'=>'Negotiation','stage_c'=>'success','value'=>'$85,000','close'=>'Jan 31, 2026'],
                ['name'=>'Acme Add-On Seats (50)',   'stage'=>'Prospecting','stage_c'=>'muted',  'value'=>'$12,500','close'=>'Mar 15, 2026'],
              ] as $opp)
              <tr>
                <td><a href="{{ route('opportunities.show', 1) }}" style="font-weight:600;color:var(--text-primary);text-decoration:none;font-size:.875rem;">{{ $opp['name'] }}</a></td>
                <td><span class="ncv-badge ncv-badge-{{ $opp['stage_c'] }}"><span class="dot"></span>{{ $opp['stage'] }}</span></td>
                <td style="font-weight:700;font-size:.875rem;">{{ $opp['value'] }}</td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $opp['close'] }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>{{-- end right column --}}
</div>

@endsection

@push('scripts')
<script>
  // Tab switching
  function switchTab(tabName) {
    ['timeline','relationships','notes','opportunities'].forEach(t => {
      document.getElementById('tab-' + t).style.display = t === tabName ? 'block' : 'none';
    });
    document.querySelectorAll('#profileTabs .ncv-tab').forEach(function(tab, i) {
      const tabs = ['timeline','relationships','notes','opportunities'];
      tab.classList.toggle('active', tabs[i] === tabName);
    });
    return false;
  }

  // Log activity form toggle
  function openLogActivity()  { document.getElementById('logActivityForm').style.display = 'block'; }
  function closeLogActivity() { document.getElementById('logActivityForm').style.display = 'none';  }

  // Tag management
  function openTagEditor() {
    const ed = document.getElementById('tagEditor');
    ed.style.display = ed.style.display === 'none' ? 'block' : 'none';
    if (ed.style.display === 'block') document.getElementById('newTagInput').focus();
  }
  function addTag() {
    const input = document.getElementById('newTagInput');
    const val = input.value.trim();
    if (!val) return;
    const span = document.createElement('span');
    span.className = 'ncv-badge ncv-badge-primary';
    span.style.cursor = 'default';
    span.innerHTML = val + ' <button onclick="removeTag(this)" style="background:none;border:none;padding:0;margin-left:3px;color:inherit;opacity:.6;cursor:pointer;font-size:.8rem;line-height:1;">&times;</button>';
    document.getElementById('tagContainer').appendChild(span);
    input.value = '';
    window.showToast('Tag added', val + ' tag has been added.', 'success', 2500);
  }
  function removeTag(btn) {
    const badge = btn.closest('.ncv-badge');
    const name  = badge.textContent.trim().slice(0,-1);
    badge.remove();
    window.showToast('Tag removed', name + ' has been removed.', 'info', 2500);
  }
  document.getElementById('newTagInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); addTag(); }
  });
</script>
@endpush
