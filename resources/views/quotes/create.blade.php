@extends('layouts.app')

@section('title', isset($quote) ? 'Edit Quote' : 'New Quote')
@section('page-title', isset($quote) ? 'Edit Quote' : 'Quote Builder')

@section('breadcrumb-items')
  <a href="{{ route('quotes.index') }}" style="color:inherit;text-decoration:none;">Quotes</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .line-items-header {
    display: grid;
    grid-template-columns: 2fr 1fr 80px 80px 80px 100px 36px;
    gap: .5rem;
    padding: .625rem .875rem;
    background: #f8faff;
    border-radius: .625rem .625rem 0 0;
    border: 1.5px solid var(--border-color);
    font-size: .72rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .06em;
  }
  .line-item-row {
    display: grid;
    grid-template-columns: 2fr 1fr 80px 80px 80px 100px 36px;
    gap: .5rem;
    padding: .625rem .875rem;
    border: 1.5px solid var(--border-color);
    border-top: none;
    align-items: center;
    transition: background .12s;
  }
  .line-item-row:hover { background: var(--ncv-blue-50); }
  .line-item-row:last-child { border-radius: 0 0 .625rem .625rem; }
  .line-item-row .ncv-input {
    height: 36px;
    font-size: .82rem;
    background: transparent;
    border-color: transparent;
    padding: 0 .5rem;
  }
  .line-item-row .ncv-input:focus {
    background: #fff;
    border-color: var(--ncv-blue-400);
  }
  .line-total { font-weight: 700; color: var(--text-primary); font-size: .875rem; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="ncv-page-title">{{ isset($quote) ? 'Edit Quote' : 'Quote Builder' }}</h1>
    <p class="ncv-page-subtitle">Build a professional quote with line items and auto-calculated totals.</p>
  </div>
  <a href="{{ route('quotes.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<form method="POST"
      action="{{ isset($quote) ? route('quotes.update', $quote->id) : route('quotes.store') }}"
      id="quoteForm">
  @csrf
  @if(isset($quote)) @method('PUT') @endif

  <div class="row g-3">

    {{-- Quote Header --}}
    <div class="col-12">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-file-earmark-text me-2" style="color:var(--ncv-blue-500);"></i>Quote Details</h6>
        </div>
        <div class="ncv-card-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="ncv-label" for="account_id">Account <span class="required">*</span></label>
              <select class="ncv-select" id="account_id" name="account_id" required>
                <option value="">— Select Account —</option>
                <option value="1" {{ old('account_id') == 1 ? 'selected' : '' }}>Acme Corporation</option>
                <option value="2" {{ old('account_id') == 2 ? 'selected' : '' }}>TechStart Inc</option>
                <option value="3" {{ old('account_id') == 3 ? 'selected' : '' }}>Globex Inc</option>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="ncv-label" for="contact_id">Primary Contact</label>
              <select class="ncv-select" id="contact_id" name="contact_id">
                <option value="">— Select Contact —</option>
                <option value="1">Sarah Johnson</option>
                <option value="2">Michael Chen</option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="ncv-label" for="opportunity_id">Linked Opportunity</label>
              <select class="ncv-select" id="opportunity_id" name="opportunity_id">
                <option value="">— None —</option>
                <option value="1">Acme Enterprise Renewal</option>
                <option value="2">TechStart Expansion</option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="ncv-label" for="valid_until">Valid Until <span class="required">*</span></label>
              <input type="date" class="ncv-input" id="valid_until" name="valid_until"
                     value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" required />
            </div>
            <div class="col-12 col-md-4">
              <label class="ncv-label" for="price_book_id">Price Book</label>
              <select class="ncv-select" id="price_book_id" name="price_book_id">
                <option value="1" selected>Standard</option>
                <option value="2">Enterprise</option>
                <option value="3">Partner</option>
              </select>
            </div>
            <div class="col-12">
              <label class="ncv-label" for="notes">Quote Notes / Terms</label>
              <textarea class="ncv-textarea" id="notes" name="notes" rows="2"
                        placeholder="Payment terms, special conditions, validity notes…">{{ old('notes', 'Payment due within 30 days of acceptance. Prices are valid for 30 days. Volume discounts available for multi-year contracts.') }}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Line Items --}}
    <div class="col-12">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-list-ol me-2" style="color:var(--ncv-blue-500);"></i>Line Items</h6>
          <div class="d-flex gap-2">
            <button type="button" class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="addFromCatalog()">
              <i class="bi bi-box-seam"></i> Add from Catalog
            </button>
            <button type="button" class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="addLineItem()">
              <i class="bi bi-plus-lg"></i> Add Line
            </button>
          </div>
        </div>
        <div class="ncv-card-body">

          {{-- Column Headers --}}
          <div class="line-items-header">
            <div>Product / Description</div>
            <div>Unit</div>
            <div style="text-align:right;">Qty</div>
            <div style="text-align:right;">Unit Price</div>
            <div style="text-align:right;">Disc %</div>
            <div style="text-align:right;">Line Total</div>
            <div></div>
          </div>

          {{-- Line Items --}}
          <div id="lineItems">
            @php
            $initLines = [
              ['product'=>'NavCRM Enterprise License','unit'=>'Month','qty'=>12,'price'=>2500.00,'disc'=>20],
              ['product'=>'API Access Module',        'unit'=>'Month','qty'=>12,'price'=>199.00, 'disc'=>20],
              ['product'=>'Onboarding Service',       'unit'=>'Each', 'qty'=>1, 'price'=>4500.00,'disc'=>0],
            ];
            @endphp
            @foreach($initLines as $i => $line)
            <div class="line-item-row" id="line{{ $i }}">
              <div>
                <input type="text" class="ncv-input" name="lines[{{ $i }}][product]"
                       value="{{ $line['product'] }}" placeholder="Product name or description" />
              </div>
              <div>
                <select class="ncv-select" name="lines[{{ $i }}][unit]" style="height:36px;font-size:.82rem;">
                  @foreach(['Each','Month','Year','Hour','License','Seat'] as $u)
                  <option value="{{ $u }}" {{ $line['unit'] === $u ? 'selected' : '' }}>{{ $u }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <input type="number" class="ncv-input" name="lines[{{ $i }}][qty]"
                       value="{{ $line['qty'] }}" min="1" step="1"
                       oninput="recalcLine({{ $i }})" style="text-align:right;" />
              </div>
              <div>
                <input type="number" class="ncv-input" name="lines[{{ $i }}][price]"
                       value="{{ $line['price'] }}" min="0" step="0.01"
                       oninput="recalcLine({{ $i }})" style="text-align:right;" />
              </div>
              <div>
                <input type="number" class="ncv-input" name="lines[{{ $i }}][discount]"
                       value="{{ $line['disc'] }}" min="0" max="100" step="1"
                       oninput="recalcLine({{ $i }})" style="text-align:right;" />
              </div>
              <div class="line-total" id="lineTotal{{ $i }}" style="text-align:right;">
                ${{ number_format($line['qty'] * $line['price'] * (1 - $line['disc']/100), 2) }}
              </div>
              <div style="text-align:center;">
                <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"
                        onclick="removeLine({{ $i }})" style="color:#ef4444;">
                  <i class="bi bi-x-lg" style="font-size:.8rem;"></i>
                </button>
              </div>
            </div>
            @endforeach
          </div>

        </div>
      </div>
    </div>

    {{-- Totals + Tax --}}
    <div class="col-12">
      <div class="row g-3">
        <div class="col-12 col-md-5 offset-md-7">
          <div class="ncv-card">
            <div class="ncv-card-body">
              <table style="width:100%;font-size:.875rem;">
                <tr>
                  <td style="padding:.4rem 0;color:var(--text-muted);">Subtotal</td>
                  <td style="text-align:right;font-weight:600;" id="summarySubtotal">$26,412.00</td>
                </tr>
                <tr>
                  <td style="padding:.4rem 0;color:var(--text-muted);">
                    Discount (<span id="summaryDiscPct">—</span>)
                  </td>
                  <td style="text-align:right;color:#10b981;font-weight:600;" id="summaryDiscount">-$6,603.00</td>
                </tr>
                <tr>
                  <td style="padding:.4rem 0;color:var(--text-muted);">
                    Tax Rate
                    <select id="taxRate" name="tax_rate" onchange="recalcTotals()"
                            style="border:none;background:transparent;font-size:.8rem;color:var(--text-muted);cursor:pointer;">
                      <option value="0">0%</option>
                      <option value="5">5%</option>
                      <option value="9" selected>9%</option>
                      <option value="10">10%</option>
                      <option value="20">20%</option>
                    </select>
                  </td>
                  <td style="text-align:right;font-weight:600;" id="summaryTax">$2,377.08</td>
                </tr>
                <tr>
                  <td colspan="2"><hr style="border-top:2px solid var(--border-color);margin:.5rem 0;" /></td>
                </tr>
                <tr>
                  <td style="font-size:1rem;font-weight:800;color:var(--text-primary);">Total</td>
                  <td style="text-align:right;font-size:1.25rem;font-weight:800;color:var(--ncv-blue-700);" id="summaryTotal">$28,789.08</td>
                </tr>
              </table>
              <input type="hidden" name="subtotal" id="inputSubtotal" value="26412" />
              <input type="hidden" name="total"    id="inputTotal"    value="28789.08" />
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="col-12">
      <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('quotes.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
        <button type="submit" name="status" value="draft" class="ncv-btn ncv-btn-outline">
          <i class="bi bi-floppy"></i> Save as Draft
        </button>
        <button type="submit" name="status" value="sent" class="ncv-btn ncv-btn-primary">
          <i class="bi bi-send"></i> {{ isset($quote) ? 'Update & Send' : 'Save & Send Quote' }}
        </button>
      </div>
    </div>

  </div>
</form>

{{-- Catalog Modal --}}
<div class="ncv-modal-overlay" id="catalogModal" style="display:none;">
  <div class="ncv-modal" style="max-width:600px;">
    <div class="ncv-modal-header">
      <h5 class="ncv-modal-title"><i class="bi bi-box-seam me-2" style="color:var(--ncv-blue-500);"></i>Add from Product Catalog</h5>
      <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="closeCatalog()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="ncv-modal-body" style="padding:1rem 1.5rem;">
      <input type="text" class="ncv-input mb-3" placeholder="Search products…" style="height:38px;" oninput="filterCatalog(this.value)" />
      <div id="catalogList">
        @foreach([
          ['id'=>1,'name'=>'NavCRM Enterprise','sku'=>'NCR-ENT-001','price'=>2500.00,'unit'=>'Month'],
          ['id'=>2,'name'=>'NavCRM Pro',        'sku'=>'NCR-PRO-001','price'=>499.00, 'unit'=>'Month'],
          ['id'=>3,'name'=>'NavCRM Starter',    'sku'=>'NCR-STR-001','price'=>99.00,  'unit'=>'Month'],
          ['id'=>4,'name'=>'API Access Module', 'sku'=>'NCR-API-001','price'=>199.00, 'unit'=>'Month'],
          ['id'=>5,'name'=>'AI Forecasting',    'sku'=>'NCR-AI-001', 'price'=>349.00, 'unit'=>'Month'],
          ['id'=>6,'name'=>'Onboarding Service','sku'=>'SVC-ONB-001','price'=>4500.00,'unit'=>'Each'],
          ['id'=>7,'name'=>'Training Package',  'sku'=>'SVC-TRN-001','price'=>1500.00,'unit'=>'Each'],
        ] as $p)
        <div class="catalog-item" style="display:flex;align-items:center;justify-content:space-between;padding:.625rem .875rem;border:1px solid var(--border-color);border-radius:.625rem;margin-bottom:.375rem;cursor:pointer;transition:background .12s;" data-name="{{ strtolower($p['name']) }}"
             onmouseenter="this.style.background='var(--ncv-blue-50)'"
             onmouseleave="this.style.background=''"
             onclick="addCatalogItem('{{ $p['name'] }}','{{ $p['unit'] }}',{{ $p['price'] }})">
          <div>
            <div style="font-size:.875rem;font-weight:600;color:var(--text-primary);">{{ $p['name'] }}</div>
            <div style="font-size:.72rem;color:var(--text-muted);font-family:monospace;">{{ $p['sku'] }}</div>
          </div>
          <div style="text-align:right;">
            <div style="font-weight:800;color:var(--text-primary);">${{ number_format($p['price'], 2) }}</div>
            <div style="font-size:.72rem;color:var(--text-muted);">per {{ $p['unit'] }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    <div class="ncv-modal-footer">
      <button class="ncv-btn ncv-btn-outline" onclick="closeCatalog()">Close</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  let lineCount = 3;

  // Recalculate a single line
  function recalcLine(idx) {
    const qty   = parseFloat(document.querySelector(`[name="lines[${idx}][qty]"]`)?.value)      || 0;
    const price = parseFloat(document.querySelector(`[name="lines[${idx}][price]"]`)?.value)    || 0;
    const disc  = parseFloat(document.querySelector(`[name="lines[${idx}][discount]"]`)?.value) || 0;
    const total = qty * price * (1 - disc / 100);
    const el = document.getElementById('lineTotal' + idx);
    if (el) el.textContent = '$' + total.toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });
    recalcTotals();
  }

  // Recalculate grand totals
  function recalcTotals() {
    let subtotal = 0, grossSaved = 0;
    document.querySelectorAll('.line-item-row').forEach((row, i) => {
      const idx   = row.id.replace('line', '');
      const qty   = parseFloat(row.querySelector(`[name^="lines["][name$="[qty]"]`)?.value)    || 0;
      const price = parseFloat(row.querySelector(`[name^="lines["][name$="[price]"]`)?.value)  || 0;
      const disc  = parseFloat(row.querySelector(`[name^="lines["][name$="[discount]"]`)?.value) || 0;
      const net = qty * price * (1 - disc / 100);
      const gross = qty * price;
      subtotal   += net;
      grossSaved += (gross - net);
    });

    const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
    const tax   = subtotal * taxRate / 100;
    const total = subtotal + tax;

    document.getElementById('summarySubtotal').textContent = '$' + subtotal.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('summaryDiscount').textContent = '-$' + grossSaved.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('summaryTax').textContent      = '$' + tax.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('summaryTotal').textContent    = '$' + total.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('inputSubtotal').value = subtotal.toFixed(2);
    document.getElementById('inputTotal').value    = total.toFixed(2);
  }

  // Add blank line
  function addLineItem(product = '', unit = 'Each', price = 0) {
    const idx = lineCount++;
    const row = document.createElement('div');
    row.className = 'line-item-row';
    row.id = 'line' + idx;
    row.innerHTML = `
      <div><input type="text" class="ncv-input" name="lines[${idx}][product]" value="${product}" placeholder="Product name or description" /></div>
      <div>
        <select class="ncv-select" name="lines[${idx}][unit]" style="height:36px;font-size:.82rem;">
          ${['Each','Month','Year','Hour','License','Seat'].map(u => `<option${u===unit?' selected':''}>${u}</option>`).join('')}
        </select>
      </div>
      <div><input type="number" class="ncv-input" name="lines[${idx}][qty]" value="1" min="1" step="1" oninput="recalcLine(${idx})" style="text-align:right;" /></div>
      <div><input type="number" class="ncv-input" name="lines[${idx}][price]" value="${price}" min="0" step="0.01" oninput="recalcLine(${idx})" style="text-align:right;" /></div>
      <div><input type="number" class="ncv-input" name="lines[${idx}][discount]" value="0" min="0" max="100" step="1" oninput="recalcLine(${idx})" style="text-align:right;" /></div>
      <div class="line-total" id="lineTotal${idx}" style="text-align:right;">$${price.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}</div>
      <div style="text-align:center;"><button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="removeLine(${idx})" style="color:#ef4444;"><i class="bi bi-x-lg" style="font-size:.8rem;"></i></button></div>`;
    document.getElementById('lineItems').appendChild(row);
    recalcTotals();
  }

  function removeLine(idx) {
    const el = document.getElementById('line' + idx);
    if (el) el.remove();
    recalcTotals();
  }

  // Catalog
  function addFromCatalog() { document.getElementById('catalogModal').style.display = 'flex'; }
  function closeCatalog()    { document.getElementById('catalogModal').style.display = 'none'; }

  function addCatalogItem(name, unit, price) {
    addLineItem(name, unit, price);
    closeCatalog();
    window.showToast('Product Added', name + ' added to quote.', 'success', 2500);
  }

  function filterCatalog(q) {
    document.querySelectorAll('.catalog-item').forEach(item => {
      item.style.display = item.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
    });
  }

  document.getElementById('catalogModal').addEventListener('click', function(e) {
    if (e.target === this) closeCatalog();
  });

  // Init
  recalcTotals();
</script>
@endpush
