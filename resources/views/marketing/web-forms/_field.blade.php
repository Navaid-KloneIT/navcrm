<div class="field-card mb-2" id="field_{{ $index }}">
  <div class="d-flex align-items-start gap-2">
    <span class="drag-handle mt-1"><i class="bi bi-grip-vertical"></i></span>
    <div class="row g-2 flex-grow-1">
      <div class="col-12 col-md-3">
        <label class="ncv-label" style="font-size:.72rem;">Label</label>
        <input type="text" name="fields[{{ $index }}][label]" class="ncv-input"
               value="{{ $field['label'] ?? '' }}" placeholder="Field label" />
      </div>
      <div class="col-12 col-md-3">
        <label class="ncv-label" style="font-size:.72rem;">Field Name (key)</label>
        <input type="text" name="fields[{{ $index }}][name]" class="ncv-input"
               value="{{ $field['name'] ?? '' }}" placeholder="field_name" />
      </div>
      <div class="col-12 col-md-2">
        <label class="ncv-label" style="font-size:.72rem;">Type</label>
        <input type="text" name="fields[{{ $index }}][type]" class="ncv-input"
               value="{{ $field['type'] ?? 'text' }}" readonly />
      </div>
      <div class="col-12 col-md-3">
        <label class="ncv-label" style="font-size:.72rem;">Placeholder</label>
        <input type="text" name="fields[{{ $index }}][placeholder]" class="ncv-input"
               value="{{ $field['placeholder'] ?? '' }}" placeholder="Optional…" />
      </div>
      <div class="col-12 col-md-1 d-flex align-items-end pb-1">
        <label class="d-flex align-items-center gap-1" style="cursor:pointer;">
          <input type="checkbox" name="fields[{{ $index }}][required]" value="1"
                 {{ !empty($field['required']) ? 'checked' : '' }}
                 style="accent-color:var(--ncv-blue-500);" />
          <span style="font-size:.72rem;color:var(--text-muted);">Req.</span>
        </label>
      </div>
    </div>
    <button type="button" onclick="removeField({{ $index }})"
            class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm mt-1" style="color:#ef4444;">
      <i class="bi bi-x-lg" style="font-size:.75rem;"></i>
    </button>
  </div>
</div>
