@extends('layouts.app')

@section('title', isset($expense) ? 'Edit Expense' : 'New Expense')
@section('page-title', isset($expense) ? 'Edit Expense' : 'New Expense')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('finance.expenses.index') }}" class="ncv-breadcrumb-item">Expenses</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<form method="POST"
      action="{{ isset($expense) ? route('finance.expenses.update', $expense) : route('finance.expenses.store') }}">
  @csrf
  @if(isset($expense)) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="ncv-card mb-3">
        <div class="ncv-card-header"><span class="ncv-card-title">Expense Details</span></div>
        <div class="ncv-card-body">
          <div class="row g-3">

            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Description <span class="text-danger">*</span></label>
              <input type="text" name="description"
                     value="{{ old('description', $expense?->description) }}"
                     placeholder="e.g. Flight to New York — client meeting"
                     class="ncv-input @error('description') is-invalid @enderror" required>
              @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Category <span class="text-danger">*</span></label>
              <select name="category" class="ncv-select @error('category') is-invalid @enderror" required>
                <option value="">— Select category —</option>
                @foreach(\App\Enums\ExpenseCategory::cases() as $c)
                  <option value="{{ $c->value }}" {{ old('category', $expense?->category->value) === $c->value ? 'selected' : '' }}>
                    {{ $c->label() }}
                  </option>
                @endforeach
              </select>
              @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Amount <span class="text-danger">*</span></label>
              <div style="position:relative;">
                <span style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--text-muted);">$</span>
                <input type="number" name="amount"
                       value="{{ old('amount', $expense?->amount) }}"
                       min="0.01" step="0.01"
                       placeholder="0.00"
                       class="ncv-input @error('amount') is-invalid @enderror"
                       style="padding-left:1.5rem;" required>
              </div>
              @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Currency</label>
              <select name="currency" class="ncv-select">
                @foreach(['USD','EUR','GBP','INR','AUD','CAD','SGD'] as $cur)
                  <option value="{{ $cur }}" {{ old('currency', $expense?->currency ?? 'USD') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Date <span class="text-danger">*</span></label>
              <input type="date" name="expense_date"
                     value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                     class="ncv-input @error('expense_date') is-invalid @enderror" required>
              @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Linked Opportunity</label>
              <select name="opportunity_id" class="ncv-select">
                <option value="">— None —</option>
                @foreach($opportunities as $opp)
                  <option value="{{ $opp->id }}" {{ old('opportunity_id', $expense?->opportunity_id) == $opp->id ? 'selected' : '' }}>
                    {{ $opp->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Linked Account</label>
              <select name="account_id" class="ncv-select">
                <option value="">— None —</option>
                @foreach($accounts as $acc)
                  <option value="{{ $acc->id }}" {{ old('account_id', $expense?->account_id) == $acc->id ? 'selected' : '' }}>
                    {{ $acc->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Receipt URL</label>
              <input type="url" name="receipt_url"
                     value="{{ old('receipt_url', $expense?->receipt_url) }}"
                     placeholder="https://…"
                     class="ncv-input @error('receipt_url') is-invalid @enderror">
              @error('receipt_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Notes</label>
              <textarea name="notes" rows="3" class="ncv-input" style="height:auto;">{{ old('notes', $expense?->notes) }}</textarea>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="ncv-card">
        <div class="ncv-card-body">
          <button type="submit" class="ncv-btn ncv-btn-primary w-100 mb-2">
            <i class="bi bi-check-lg me-1"></i>
            {{ isset($expense) ? 'Update Expense' : 'Submit Expense' }}
          </button>
          <a href="{{ route('finance.expenses.index') }}" class="ncv-btn ncv-btn-outline w-100">Cancel</a>
          @if(isset($expense))
          <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
            <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}"
                  onsubmit="return confirm('Delete this expense?')">
              @csrf @method('DELETE')
              <button type="submit" class="ncv-btn ncv-btn-ghost w-100 text-danger">
                <i class="bi bi-trash me-1"></i>Delete
              </button>
            </form>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</form>

@endsection
