@extends('layouts.app')

@section('title', isset($invoice) ? 'Edit Invoice' : 'New Invoice')
@section('page-title', isset($invoice) ? 'Edit Invoice' : 'New Invoice')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('finance.invoices.index') }}" class="ncv-breadcrumb-item">Invoices</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .line-grid {
    display: grid;
    grid-template-columns: 2fr 80px 100px 70px 100px 36px;
    gap: .5rem;
    align-items: center;
  }
  .totals-table td { padding: .3rem .5rem; font-size:.88rem; }
  .totals-table .total-row td { font-size:1rem; font-weight:800; }
</style>
@endpush

@section('content')

@if($quote)
<div class="ncv-card mb-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
  <div class="ncv-card-body py-2 px-3" style="font-size:.85rem;">
    <i class="bi bi-info-circle me-1" style="color:#2563eb;"></i>
    Converting from Quote <strong>{{ $quote->quote_number }}</strong>. Line items and totals have been pre-filled.
  </div>
</div>
@endif

<form method="POST"
      action="{{ isset($invoice) ? route('finance.invoices.update', $invoice) : route('finance.invoices.store') }}"
      id="invoiceForm">
  @csrf
  @if(isset($invoice)) @method('PUT') @endif

  <div class="row g-3">

    {{-- Left column --}}
    <div class="col-lg-8">

      {{-- Invoice Details --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <span class="ncv-card-title">Invoice Details</span>
        </div>
        <div class="ncv-card-body">
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Account</label>
              <select name="account_id" class="ncv-select">
                <option value="">— No Account —</option>
                @foreach($accounts as $acc)
                  <option value="{{ $acc->id }}" {{ old('account_id', $invoice?->account_id ?? $quote?->account_id) == $acc->id ? 'selected' : '' }}>
                    {{ $acc->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Contact</label>
              <select name="contact_id" class="ncv-select">
                <option value="">— No Contact —</option>
                @foreach($contacts as $c)
                  <option value="{{ $c->id }}" {{ old('contact_id', $invoice?->contact_id ?? $quote?->contact_id) == $c->id ? 'selected' : '' }}>
                    {{ $c->first_name }} {{ $c->last_name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Opportunity</label>
              <select name="opportunity_id" class="ncv-select">
                <option value="">— No Opportunity —</option>
                @foreach($opportunities as $opp)
                  <option value="{{ $opp->id }}" {{ old('opportunity_id', $invoice?->opportunity_id ?? $quote?->opportunity_id) == $opp->id ? 'selected' : '' }}>
                    {{ $opp->name }}
                  </option>
                @endforeach
              </select>
            </div>

            @if($quote)
              <input type="hidden" name="quote_id" value="{{ $quote->id }}">
            @endif

            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Issue Date <span class="text-danger">*</span></label>
              <input type="date" name="issue_date"
                     value="{{ old('issue_date', $invoice?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                     class="ncv-input" required>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Due Date</label>
              <input type="date" name="due_date"
                     value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d')) }}"
                     class="ncv-input">
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Currency</label>
              <select name="currency" class="ncv-select">
                @foreach(['USD','EUR','GBP','INR','AUD','CAD','SGD'] as $cur)
                  <option value="{{ $cur }}" {{ old('currency', $invoice?->currency ?? 'USD') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Status</label>
              <select name="status" class="ncv-select">
                @foreach(\App\Enums\InvoiceStatus::cases() as $s)
                  <option value="{{ $s->value }}" {{ old('status', $invoice?->status->value ?? 'draft') === $s->value ? 'selected' : '' }}>
                    {{ $s->label() }}
                  </option>
                @endforeach
              </select>
            </div>

          </div>
        </div>
      </div>

      {{-- Line Items --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header d-flex align-items-center justify-content-between">
          <span class="ncv-card-title">Line Items</span>
          <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="addLineItem()">
            <i class="bi bi-plus-lg"></i> Add Line
          </button>
        </div>
        <div class="ncv-card-body">

          {{-- Header --}}
          <div class="line-grid mb-1" style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">
            <span>Product / Description</span>
            <span>Qty</span>
            <span>Unit Price</span>
            <span>Disc%</span>
            <span style="text-align:right;">Line Total</span>
            <span></span>
          </div>

          <div id="lineItemsContainer">
            @php
              $existingItems = $invoice?->lineItems ?? ($quote?->lineItems ?? collect());
            @endphp
            @forelse($existingItems as $i => $item)
            <div class="line-grid mb-2 line-item-row" data-idx="{{ $i }}">
              <div>
                <select class="ncv-select" style="font-size:.8rem;margin-bottom:.25rem;"
                        onchange="fillFromProduct({{ $i }}, this.value)">
                  <option value="">— Custom / manual entry —</option>
                  @foreach($products as $p)
                    <option value="{{ $p->id }}"
                            data-price="{{ $p->unit_price }}"
                            data-name="{{ $p->name }}"
                            {{ $item->product_id == $p->id ? 'selected' : '' }}>
                      {{ $p->name }}
                    </option>
                  @endforeach
                </select>
                <input type="hidden" name="line_items[{{ $i }}][product_id]" value="{{ $item->product_id }}" id="pid_{{ $i }}">
                <input type="text" name="line_items[{{ $i }}][description]"
                       value="{{ $item->description }}"
                       placeholder="Description…"
                       class="ncv-input" style="font-size:.8rem;" required>
              </div>
              <input type="number" name="line_items[{{ $i }}][quantity]" value="{{ $item->quantity }}"
                     min="0.01" step="0.01" class="ncv-input" style="font-size:.8rem;"
                     oninput="recalcLine({{ $i }})" id="qty_{{ $i }}">
              <input type="number" name="line_items[{{ $i }}][unit_price]" value="{{ $item->unit_price }}"
                     min="0" step="0.01" class="ncv-input" style="font-size:.8rem;"
                     oninput="recalcLine({{ $i }})" id="price_{{ $i }}">
              <input type="number" name="line_items[{{ $i }}][discount_percent]" value="{{ $item->discount_percent }}"
                     min="0" max="100" step="0.01" class="ncv-input" style="font-size:.8rem;"
                     oninput="recalcLine({{ $i }})" id="disc_{{ $i }}">
              <div style="text-align:right;font-weight:700;font-size:.88rem;padding-top:.5rem;" id="linetotal_{{ $i }}">
                ${{ number_format($item->subtotal, 2) }}
              </div>
              <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm text-danger"
                      onclick="removeLine({{ $i }})">
                <i class="bi bi-trash" style="font-size:.8rem;"></i>
              </button>
            </div>
            @empty
            @endforelse
          </div>

          <p id="noLinesMsg" class="text-center text-muted py-3" style="font-size:.82rem;{{ $existingItems->count() ? 'display:none;' : '' }}">
            No line items yet. Click "Add Line" to start.
          </p>

        </div>
      </div>

      {{-- Totals --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header"><span class="ncv-card-title">Totals</span></div>
        <div class="ncv-card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Discount Type</label>
              <select name="discount_type" class="ncv-select" id="discountType" onchange="recalcTotals()">
                <option value="">None</option>
                <option value="percentage" {{ old('discount_type', $invoice?->discount_type ?? $quote?->discount_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                <option value="fixed" {{ old('discount_type', $invoice?->discount_type ?? $quote?->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed ($)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Discount Value</label>
              <input type="number" name="discount_value" id="discountValue"
                     value="{{ old('discount_value', $invoice?->discount_value ?? $quote?->discount_value ?? 0) }}"
                     min="0" step="0.01" class="ncv-input" oninput="recalcTotals()">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Tax Rate (%)</label>
              <select name="tax_rate" class="ncv-select" id="taxRate" onchange="recalcTotals()">
                <option value="0">0%</option>
                @foreach($taxRates as $tr)
                  <option value="{{ $tr->rate }}"
                    {{ old('tax_rate', $invoice?->tax_rate ?? $quote?->tax_rate) == $tr->rate ? 'selected' : '' }}>
                    {{ $tr->name }} ({{ $tr->rate }}%)
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <table class="totals-table ms-auto" style="width:300px;">
            <tr>
              <td style="color:var(--text-muted);">Subtotal</td>
              <td style="text-align:right;" id="displaySubtotal">$0.00</td>
            </tr>
            <tr id="discountRow" style="display:none;">
              <td style="color:var(--text-muted);">Discount</td>
              <td style="text-align:right;color:#ef4444;" id="displayDiscount">−$0.00</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Tax (<span id="taxRateLabel">0</span>%)</td>
              <td style="text-align:right;" id="displayTax">$0.00</td>
            </tr>
            <tr class="total-row" style="border-top:2px solid var(--border-color);">
              <td>Total</td>
              <td style="text-align:right;" id="displayTotal">$0.00</td>
            </tr>
          </table>
        </div>
      </div>

      {{-- Notes & Terms --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header"><span class="ncv-card-title">Notes & Terms</span></div>
        <div class="ncv-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Notes</label>
              <textarea name="notes" rows="3" class="ncv-input" style="height:auto;">{{ old('notes', $invoice?->notes ?? $quote?->notes) }}</textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Payment Terms</label>
              <textarea name="terms" rows="3" class="ncv-input" style="height:auto;">{{ old('terms', $invoice?->terms ?? $quote?->terms) }}</textarea>
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- Right column --}}
    <div class="col-lg-4">

      {{-- Actions --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-body">
          <button type="submit" class="ncv-btn ncv-btn-primary w-100 mb-2">
            <i class="bi bi-check-lg me-1"></i>
            {{ isset($invoice) ? 'Update Invoice' : 'Create Invoice' }}
          </button>
          <a href="{{ route('finance.invoices.index') }}" class="ncv-btn ncv-btn-outline w-100">Cancel</a>
        </div>
      </div>

      {{-- Recurring --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header"><span class="ncv-card-title">Recurring Billing</span></div>
        <div class="ncv-card-body">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_recurring" value="1"
                   id="isRecurring"
                   {{ old('is_recurring', $invoice?->is_recurring) ? 'checked' : '' }}
                   onchange="toggleRecurring(this.checked)">
            <label class="form-check-label" for="isRecurring" style="font-size:.85rem;">Enable recurring</label>
          </div>
          <div id="recurringFields" style="{{ old('is_recurring', $invoice?->is_recurring) ? '' : 'display:none;' }}">
            <div class="mb-2">
              <label class="form-label" style="font-size:.8rem;">Interval</label>
              <select name="recurrence" class="ncv-select">
                <option value="monthly" {{ old('recurrence', $invoice?->recurrence) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="quarterly" {{ old('recurrence', $invoice?->recurrence) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="yearly" {{ old('recurrence', $invoice?->recurrence) === 'yearly' ? 'selected' : '' }}>Yearly</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label" style="font-size:.8rem;">Next Invoice Date</label>
              <input type="date" name="next_invoice_date"
                     value="{{ old('next_invoice_date', $invoice?->next_invoice_date?->format('Y-m-d')) }}"
                     class="ncv-input">
            </div>
            <div>
              <label class="form-label" style="font-size:.8rem;">End Date (optional)</label>
              <input type="date" name="recurrence_end_date"
                     value="{{ old('recurrence_end_date', $invoice?->recurrence_end_date?->format('Y-m-d')) }}"
                     class="ncv-input">
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</form>

@endsection

@push('scripts')
<script>
let lineIdx = {{ ($existingItems ?? collect())->count() }};

function addLineItem(productId = '', productName = '', unitPrice = 0) {
  const idx = lineIdx++;
  const container = document.getElementById('lineItemsContainer');
  const row = document.createElement('div');
  row.className = 'line-grid mb-2 line-item-row';
  row.dataset.idx = idx;

  const productOptions = `<option value="">— Custom / manual entry —</option>` +
    @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'unit_price' => $p->unit_price]))
    .map(p => `<option value="${p.id}" data-price="${p.unit_price}" data-name="${p.name}" ${p.id == productId ? 'selected' : ''}>${p.name}</option>`)
    .join('');

  row.innerHTML = `
    <div>
      <select class="ncv-select" style="font-size:.8rem;margin-bottom:.25rem;" onchange="fillFromProduct(${idx}, this.value)">
        ${productOptions}
      </select>
      <input type="hidden" name="line_items[${idx}][product_id]" value="${productId}" id="pid_${idx}">
      <input type="text" name="line_items[${idx}][description]" value="${productName}"
             placeholder="Description…" class="ncv-input" style="font-size:.8rem;" required>
    </div>
    <input type="number" name="line_items[${idx}][quantity]" value="1" min="0.01" step="0.01"
           class="ncv-input" style="font-size:.8rem;" oninput="recalcLine(${idx})" id="qty_${idx}">
    <input type="number" name="line_items[${idx}][unit_price]" value="${unitPrice}" min="0" step="0.01"
           class="ncv-input" style="font-size:.8rem;" oninput="recalcLine(${idx})" id="price_${idx}">
    <input type="number" name="line_items[${idx}][discount_percent]" value="0" min="0" max="100" step="0.01"
           class="ncv-input" style="font-size:.8rem;" oninput="recalcLine(${idx})" id="disc_${idx}">
    <div style="text-align:right;font-weight:700;font-size:.88rem;padding-top:.5rem;" id="linetotal_${idx}">$0.00</div>
    <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm text-danger" onclick="removeLine(${idx})">
      <i class="bi bi-trash" style="font-size:.8rem;"></i>
    </button>
  `;
  container.appendChild(row);
  document.getElementById('noLinesMsg').style.display = 'none';
  recalcLine(idx);
}

function fillFromProduct(idx, productId) {
  const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'unit_price' => $p->unit_price]));
  const p = products.find(x => x.id == productId);
  if (p) {
    document.getElementById(`pid_${idx}`).value = p.id;
    document.querySelector(`[data-idx="${idx}"] input[name*="[description]"]`).value = p.name;
    document.getElementById(`price_${idx}`).value = p.unit_price;
    recalcLine(idx);
  } else {
    document.getElementById(`pid_${idx}`).value = '';
  }
}

function recalcLine(idx) {
  const qty   = parseFloat(document.getElementById(`qty_${idx}`)?.value) || 0;
  const price = parseFloat(document.getElementById(`price_${idx}`)?.value) || 0;
  const disc  = parseFloat(document.getElementById(`disc_${idx}`)?.value) || 0;
  let total = qty * price;
  if (disc > 0) total -= total * (disc / 100);
  total = Math.round(total * 100) / 100;
  const el = document.getElementById(`linetotal_${idx}`);
  if (el) el.textContent = '$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  recalcTotals();
}

function recalcTotals() {
  let subtotal = 0;
  document.querySelectorAll('.line-item-row').forEach(row => {
    const idx   = row.dataset.idx;
    const qty   = parseFloat(document.getElementById(`qty_${idx}`)?.value) || 0;
    const price = parseFloat(document.getElementById(`price_${idx}`)?.value) || 0;
    const disc  = parseFloat(document.getElementById(`disc_${idx}`)?.value) || 0;
    let t = qty * price;
    if (disc > 0) t -= t * (disc / 100);
    subtotal += Math.round(t * 100) / 100;
  });

  const discType  = document.getElementById('discountType').value;
  const discVal   = parseFloat(document.getElementById('discountValue').value) || 0;
  const taxRate   = parseFloat(document.getElementById('taxRate').value) || 0;

  let discountAmount = 0;
  if (discVal > 0) {
    discountAmount = discType === 'percentage' ? Math.round(subtotal * (discVal / 100) * 100) / 100 : discVal;
  }
  const afterDisc = subtotal - discountAmount;
  const taxAmount = Math.round(afterDisc * (taxRate / 100) * 100) / 100;
  const total = afterDisc + taxAmount;

  const fmt = v => '$' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  document.getElementById('displaySubtotal').textContent = fmt(subtotal);
  document.getElementById('displayTax').textContent = fmt(taxAmount);
  document.getElementById('displayTotal').textContent = fmt(total);
  document.getElementById('taxRateLabel').textContent = taxRate;
  const discRow = document.getElementById('discountRow');
  if (discountAmount > 0) {
    discRow.style.display = '';
    document.getElementById('displayDiscount').textContent = '−' + fmt(discountAmount);
  } else {
    discRow.style.display = 'none';
  }
}

function removeLine(idx) {
  const row = document.querySelector(`.line-item-row[data-idx="${idx}"]`);
  if (row) { row.remove(); recalcTotals(); }
  if (!document.querySelectorAll('.line-item-row').length) {
    document.getElementById('noLinesMsg').style.display = '';
  }
}

function toggleRecurring(on) {
  document.getElementById('recurringFields').style.display = on ? '' : 'none';
}

// Init
recalcTotals();
</script>
@endpush
