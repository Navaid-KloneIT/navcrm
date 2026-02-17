@extends('layouts.app')

@section('title', 'Contacts')
@section('page-title', 'Contacts')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Contacts</h1>
    <p class="ncv-page-subtitle">Manage and track all your customer contacts.</p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="exportContacts()">
      <i class="bi bi-download"></i> Export
    </button>
    <a href="{{ route('contacts.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> Add Contact
    </a>
  </div>
</div>

{{-- Stats mini bar --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Total',      'value'=>'1,248', 'change'=>'+24 this month',  'color'=>'var(--ncv-blue-600)'],
    ['label'=>'Customers',  'value'=>'384',   'change'=>'+8 this month',   'color'=>'#10b981'],
    ['label'=>'Prospects',  'value'=>'619',   'change'=>'+16 this month',  'color'=>'#f59e0b'],
    ['label'=>'Archived',   'value'=>'245',   'change'=>'Inactive',        'color'=>'#94a3b8'],
  ] as $stat)
  <div class="col-6 col-md-3">
    <div class="ncv-card" style="padding:.875rem 1.125rem;">
      <div style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.25rem;">{{ $stat['label'] }}</div>
      <div style="font-size:1.5rem;font-weight:800;color:{{ $stat['color'] }};letter-spacing:-.03em;">{{ $stat['value'] }}</div>
      <div style="font-size:.72rem;color:var(--text-muted);margin-top:.125rem;">{{ $stat['change'] }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filters + Search --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <div class="d-flex align-items-center flex-wrap gap-2">

      {{-- Search --}}
      <div style="position:relative; min-width:240px; flex:1;">
        <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
        <input
          type="text"
          id="contactSearch"
          placeholder="Search by name, email, or company…"
          class="ncv-input"
          style="padding-left:2.375rem;"
          oninput="filterContacts()"
        />
      </div>

      {{-- Filter chips --}}
      <div class="d-flex gap-2 flex-wrap">
        <button class="ncv-chip active" onclick="setFilter(this,'all')">All</button>
        <button class="ncv-chip" onclick="setFilter(this,'customer')">Customers</button>
        <button class="ncv-chip" onclick="setFilter(this,'prospect')">Prospects</button>
        <button class="ncv-chip" onclick="setFilter(this,'lead')">Leads</button>
      </div>

      <div class="ms-auto d-flex gap-2 align-items-center">
        {{-- Sort --}}
        <select class="ncv-select" style="width:160px;height:38px;font-size:.8rem;">
          <option>Sort: Name (A-Z)</option>
          <option>Sort: Name (Z-A)</option>
          <option>Sort: Newest</option>
          <option>Sort: Oldest</option>
        </select>

        {{-- View toggle --}}
        <div style="display:flex;border:1.5px solid var(--border-color);border-radius:.625rem;overflow:hidden;">
          <button class="ncv-btn ncv-btn-ghost ncv-btn-icon" id="viewTable"
                  style="border-radius:0;border:none;background:var(--ncv-blue-50);color:var(--ncv-blue-600);"
                  onclick="setView('table')">
            <i class="bi bi-list-ul"></i>
          </button>
          <button class="ncv-btn ncv-btn-ghost ncv-btn-icon" id="viewGrid"
                  style="border-radius:0;border:none;border-left:1.5px solid var(--border-color);"
                  onclick="setView('grid')">
            <i class="bi bi-grid-3x3-gap"></i>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Bulk action bar (hidden by default) --}}
<div id="bulkBar" style="display:none;"
     class="ncv-card mb-3" style="border-color:var(--ncv-blue-400);background:var(--ncv-blue-50);">
  <div class="ncv-card-body" style="padding:.625rem 1.25rem;display:flex;align-items:center;gap:.75rem;">
    <span style="font-size:.825rem;font-weight:600;color:var(--ncv-blue-700);">
      <span id="selectedCount">0</span> selected
    </span>
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-tag"></i> Assign Tag
    </button>
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-envelope"></i> Send Email
    </button>
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm" style="color:#ef4444;border-color:#fca5a5;">
      <i class="bi bi-trash"></i> Delete
    </button>
    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm ms-auto" onclick="clearSelection()">
      Clear
    </button>
  </div>
</div>

{{-- TABLE VIEW --}}
<div id="tableView">
  <div class="ncv-table-wrapper">
    <table class="ncv-table" id="contactsTable">
      <thead>
        <tr>
          <th class="col-check">
            <input type="checkbox" id="selectAll" style="accent-color:#2563eb;cursor:pointer;"
                   onchange="toggleSelectAll(this)" />
          </th>
          <th class="sorted">Name <i class="bi bi-arrow-up ms-1" style="font-size:.65rem;"></i></th>
          <th>Company</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Tags</th>
          <th>Last Activity</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach([
          ['id'=>1,'name'=>'Sarah Johnson',   'initials'=>'SJ','company'=>'Acme Corp',     'email'=>'sarah@acme.com',    'phone'=>'+1 555-0101','tags'=>['Customer','VIP'],        'activity'=>'2h ago',      'status'=>'customer','color'=>'#2563eb'],
          ['id'=>2,'name'=>'Michael Chen',    'initials'=>'MC','company'=>'TechStart Inc',  'email'=>'m.chen@techstart.io','phone'=>'+1 555-0182','tags'=>['Customer'],             'activity'=>'5h ago',      'status'=>'customer','color'=>'#10b981'],
          ['id'=>3,'name'=>'Emma Williams',   'initials'=>'EW','company'=>'Globex Inc',     'email'=>'emma.w@globex.com', 'phone'=>'+1 555-0234','tags'=>['Prospect'],             'activity'=>'Yesterday',   'status'=>'prospect','color'=>'#f59e0b'],
          ['id'=>4,'name'=>'James Rodriguez', 'initials'=>'JR','company'=>'Initech LLC',    'email'=>'james@initech.com', 'phone'=>'+1 555-0317','tags'=>['Lead','Hot'],           'activity'=>'2 days ago',  'status'=>'lead',    'color'=>'#8b5cf6'],
          ['id'=>5,'name'=>'Olivia Brown',    'initials'=>'OB','company'=>'Umbrella Corp',  'email'=>'o.brown@umbrella.co','phone'=>'+1 555-0459','tags'=>['Customer'],            'activity'=>'3 days ago',  'status'=>'customer','color'=>'#06b6d4'],
          ['id'=>6,'name'=>'David Kim',       'initials'=>'DK','company'=>'Stark Industries','email'=>'d.kim@stark.io',   'phone'=>'+1 555-0567','tags'=>['Prospect','Cold'],      'activity'=>'1 week ago',  'status'=>'prospect','color'=>'#ef4444'],
          ['id'=>7,'name'=>'Sophia Martinez', 'initials'=>'SM','company'=>'Wayne Ent.',     'email'=>'sophia@wayne.com',  'phone'=>'+1 555-0628','tags'=>['Lead'],                 'activity'=>'1 week ago',  'status'=>'lead',    'color'=>'#0891b2'],
          ['id'=>8,'name'=>'Noah Wilson',     'initials'=>'NW','company'=>'Cyberdyne Sys.', 'email'=>'noah@cyberdyne.net','phone'=>'+1 555-0744','tags'=>['Customer','Partner'],   'activity'=>'2 weeks ago', 'status'=>'customer','color'=>'#16a34a'],
        ] as $contact)
        <tr data-status="{{ $contact['status'] }}" data-name="{{ strtolower($contact['name']) }}">
          <td class="col-check">
            <input type="checkbox" class="row-check" style="accent-color:#2563eb;cursor:pointer;"
                   onchange="updateBulkBar()" />
          </td>
          <td>
            <div class="ncv-table-name">
              <div class="ncv-table-avatar" style="background:{{ $contact['color'] }}18;color:{{ $contact['color'] }};">
                {{ $contact['initials'] }}
              </div>
              <div>
                <a href="{{ route('contacts.show', $contact['id']) }}" class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;font-size:.875rem;">
                  {{ $contact['name'] }}
                </a>
              </div>
            </div>
          </td>
          <td style="font-size:.82rem;color:var(--text-secondary);">{{ $contact['company'] }}</td>
          <td>
            <a href="mailto:{{ $contact['email'] }}" style="font-size:.82rem;color:var(--ncv-blue-600);text-decoration:none;">
              {{ $contact['email'] }}
            </a>
          </td>
          <td style="font-size:.82rem;color:var(--text-muted);white-space:nowrap;">{{ $contact['phone'] }}</td>
          <td>
            <div class="d-flex gap-1 flex-wrap">
              @foreach($contact['tags'] as $tag)
                <span class="ncv-badge ncv-badge-primary" style="font-size:.65rem;padding:.2rem .5rem;">
                  {{ $tag }}
                </span>
              @endforeach
            </div>
          </td>
          <td style="font-size:.775rem;color:var(--text-muted);">{{ $contact['activity'] }}</td>
          <td>
            @if($contact['status'] === 'customer')
              <span class="ncv-badge ncv-badge-success"><span class="dot"></span>Customer</span>
            @elseif($contact['status'] === 'prospect')
              <span class="ncv-badge ncv-badge-warning"><span class="dot"></span>Prospect</span>
            @else
              <span class="ncv-badge ncv-badge-purple"><span class="dot"></span>Lead</span>
            @endif
          </td>
          <td>
            <div class="d-flex align-items-center gap-1">
              <a href="{{ route('contacts.show', $contact['id']) }}"
                 class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                <i class="bi bi-eye" style="font-size:.8rem;"></i>
              </a>
              <a href="{{ route('contacts.edit', $contact['id']) }}"
                 class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
                <i class="bi bi-pencil" style="font-size:.8rem;"></i>
              </a>
              <div class="ncv-dropdown">
                <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"
                        onclick="toggleDropdown('ctxMenu{{ $contact['id'] }}')" title="More">
                  <i class="bi bi-three-dots" style="font-size:.8rem;"></i>
                </button>
                <div class="ncv-dropdown-menu" id="ctxMenu{{ $contact['id'] }}">
                  <a href="{{ route('contacts.show', $contact['id']) }}" class="ncv-dropdown-item">
                    <i class="bi bi-eye"></i> View Profile
                  </a>
                  <a href="{{ route('contacts.edit', $contact['id']) }}" class="ncv-dropdown-item">
                    <i class="bi bi-pencil"></i> Edit
                  </a>
                  <button class="ncv-dropdown-item">
                    <i class="bi bi-envelope"></i> Send Email
                  </button>
                  <button class="ncv-dropdown-item">
                    <i class="bi bi-telephone"></i> Log Call
                  </button>
                  <button class="ncv-dropdown-item">
                    <i class="bi bi-tag"></i> Manage Tags
                  </button>
                  <div class="ncv-dropdown-divider"></div>
                  <button class="ncv-dropdown-item danger"
                          onclick="deleteContact({{ $contact['id'] }}, '{{ $contact['name'] }}')">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Empty state (shown when no results) --}}
    <div id="emptyState" class="ncv-empty" style="display:none;">
      <div class="ncv-empty-icon">
        <i class="bi bi-person-x" style="font-size:1.75rem;"></i>
      </div>
      <div class="ncv-empty-title">No contacts found</div>
      <div class="ncv-empty-desc">Try adjusting your search or filters.</div>
      <a href="{{ route('contacts.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
        <i class="bi bi-plus-lg"></i> Add First Contact
      </a>
    </div>

  </div>
</div>

{{-- GRID VIEW --}}
<div id="gridView" style="display:none;">
  <div class="row g-3">
    @foreach([
      ['id'=>1,'name'=>'Sarah Johnson',   'initials'=>'SJ','company'=>'Acme Corp',    'email'=>'sarah@acme.com',     'phone'=>'+1 555-0101','status'=>'customer','color'=>'#2563eb'],
      ['id'=>2,'name'=>'Michael Chen',    'initials'=>'MC','company'=>'TechStart Inc', 'email'=>'m.chen@techstart.io','phone'=>'+1 555-0182','status'=>'customer','color'=>'#10b981'],
      ['id'=>3,'name'=>'Emma Williams',   'initials'=>'EW','company'=>'Globex Inc',    'email'=>'emma.w@globex.com',  'phone'=>'+1 555-0234','status'=>'prospect','color'=>'#f59e0b'],
      ['id'=>4,'name'=>'James Rodriguez', 'initials'=>'JR','company'=>'Initech LLC',   'email'=>'james@initech.com',  'phone'=>'+1 555-0317','status'=>'lead',    'color'=>'#8b5cf6'],
      ['id'=>5,'name'=>'Olivia Brown',    'initials'=>'OB','company'=>'Umbrella Corp', 'email'=>'o.brown@umbrella.co','phone'=>'+1 555-0459','status'=>'customer','color'=>'#06b6d4'],
      ['id'=>6,'name'=>'David Kim',       'initials'=>'DK','company'=>'Stark Ind.',    'email'=>'d.kim@stark.io',     'phone'=>'+1 555-0567','status'=>'prospect','color'=>'#ef4444'],
    ] as $contact)
    <div class="col-12 col-sm-6 col-lg-4">
      <div class="ncv-card" style="transition:box-shadow .2s,transform .2s;">
        <div class="ncv-card-body" style="text-align:center;padding:1.5rem 1.25rem;">
          <div style="width:64px;height:64px;border-radius:1rem;background:{{ $contact['color'] }}18;color:{{ $contact['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;margin:0 auto 1rem;">
            {{ $contact['initials'] }}
          </div>
          <h6 style="font-weight:700;font-size:.9375rem;color:var(--text-primary);margin-bottom:.2rem;">
            <a href="{{ route('contacts.show', $contact['id']) }}" style="color:inherit;text-decoration:none;">
              {{ $contact['name'] }}
            </a>
          </h6>
          <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">{{ $contact['company'] }}</p>

          @if($contact['status'] === 'customer')
            <span class="ncv-badge ncv-badge-success mb-3"><span class="dot"></span>Customer</span>
          @elseif($contact['status'] === 'prospect')
            <span class="ncv-badge ncv-badge-warning mb-3"><span class="dot"></span>Prospect</span>
          @else
            <span class="ncv-badge ncv-badge-purple mb-3"><span class="dot"></span>Lead</span>
          @endif

          <div style="display:flex;flex-direction:column;gap:.35rem;font-size:.8rem;color:var(--text-muted);margin-top:.5rem;text-align:left;">
            <div style="display:flex;align-items:center;gap:.5rem;">
              <i class="bi bi-envelope" style="width:16px;flex-shrink:0;"></i>
              <a href="mailto:{{ $contact['email'] }}" style="color:var(--ncv-blue-600);text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $contact['email'] }}
              </a>
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;">
              <i class="bi bi-telephone" style="width:16px;flex-shrink:0;"></i>
              {{ $contact['phone'] }}
            </div>
          </div>
        </div>
        <div style="padding:.625rem 1.25rem;border-top:1px solid var(--card-border);display:flex;justify-content:center;gap:.5rem;">
          <a href="{{ route('contacts.show', $contact['id']) }}"
             class="ncv-btn ncv-btn-outline ncv-btn-sm" style="flex:1;justify-content:center;">
            <i class="bi bi-eye"></i> View
          </a>
          <a href="{{ route('contacts.edit', $contact['id']) }}"
             class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm">
            <i class="bi bi-pencil"></i>
          </a>
        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>

{{-- Pagination --}}
<div class="d-flex align-items-center justify-content-between mt-4 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">
    Showing <strong style="color:var(--text-primary);">1–8</strong> of <strong style="color:var(--text-primary);">1,248</strong> contacts
  </p>
  <nav class="ncv-pagination">
    <a href="#" class="ncv-page-btn" disabled><i class="bi bi-chevron-left" style="font-size:.75rem;"></i></a>
    <a href="#" class="ncv-page-btn active">1</a>
    <a href="#" class="ncv-page-btn">2</a>
    <a href="#" class="ncv-page-btn">3</a>
    <span style="padding:0 .375rem;color:var(--text-muted);font-size:.8rem;">…</span>
    <a href="#" class="ncv-page-btn">156</a>
    <a href="#" class="ncv-page-btn"><i class="bi bi-chevron-right" style="font-size:.75rem;"></i></a>
  </nav>
</div>

{{-- Delete confirmation modal --}}
<div class="ncv-modal-overlay" id="deleteModal" style="display:none;">
  <div class="ncv-modal">
    <div class="ncv-modal-header">
      <h5 class="ncv-modal-title">
        <i class="bi bi-exclamation-triangle-fill me-2" style="color:#ef4444;"></i>
        Delete Contact
      </h5>
      <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="closeDeleteModal()">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="ncv-modal-body">
      <p style="font-size:.9rem;color:var(--text-secondary);margin:0;">
        Are you sure you want to delete <strong id="deleteContactName"></strong>?
        This action cannot be undone and will remove all associated data.
      </p>
    </div>
    <div class="ncv-modal-footer">
      <button class="ncv-btn ncv-btn-outline" onclick="closeDeleteModal()">Cancel</button>
      <form id="deleteForm" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="ncv-btn ncv-btn-danger">
          <i class="bi bi-trash"></i> Delete Contact
        </button>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  // ── View toggle ──────────────────────────────────────────────────────────
  function setView(v) {
    document.getElementById('tableView').style.display = v === 'table' ? 'block' : 'none';
    document.getElementById('gridView').style.display  = v === 'grid'  ? 'block' : 'none';
    document.getElementById('viewTable').style.background = v === 'table' ? 'var(--ncv-blue-50)' : '';
    document.getElementById('viewTable').style.color      = v === 'table' ? 'var(--ncv-blue-600)' : '';
    document.getElementById('viewGrid').style.background  = v === 'grid'  ? 'var(--ncv-blue-50)' : '';
    document.getElementById('viewGrid').style.color       = v === 'grid'  ? 'var(--ncv-blue-600)' : '';
    localStorage.setItem('ncv_contacts_view', v);
  }

  // Restore last view
  const savedView = localStorage.getItem('ncv_contacts_view') || 'table';
  setView(savedView);

  // ── Filter by type chip ──────────────────────────────────────────────────
  function setFilter(el, type) {
    document.querySelectorAll('.ncv-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');

    const rows = document.querySelectorAll('#contactsTable tbody tr');
    rows.forEach(r => {
      const status = r.dataset.status;
      r.style.display = (type === 'all' || status === type) ? '' : 'none';
    });
  }

  // ── Live search ──────────────────────────────────────────────────────────
  function filterContacts() {
    const q = document.getElementById('contactSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#contactsTable tbody tr');
    let visible = 0;
    rows.forEach(r => {
      const name = r.dataset.name || '';
      const text = r.textContent.toLowerCase();
      const show = text.includes(q);
      r.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    document.getElementById('emptyState').style.display = visible === 0 ? 'flex' : 'none';
  }

  // ── Bulk selection ───────────────────────────────────────────────────────
  function toggleSelectAll(master) {
    document.querySelectorAll('.row-check').forEach(c => c.checked = master.checked);
    updateBulkBar();
  }

  function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    const bar = document.getElementById('bulkBar');
    bar.style.display = checked > 0 ? 'block' : 'none';
    document.getElementById('selectedCount').textContent = checked;
  }

  function clearSelection() {
    document.querySelectorAll('.row-check, #selectAll').forEach(c => c.checked = false);
    updateBulkBar();
  }

  // ── Delete modal ─────────────────────────────────────────────────────────
  function deleteContact(id, name) {
    document.getElementById('deleteContactName').textContent = name;
    document.getElementById('deleteForm').action = `/contacts/${id}`;
    document.getElementById('deleteModal').style.display = 'flex';
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
  }

  // Click outside modal to close
  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });

  // ── Export ───────────────────────────────────────────────────────────────
  function exportContacts() {
    window.showToast('Preparing export…', 'CSV export will download shortly.', 'info');
  }
</script>
@endpush
