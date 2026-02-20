@php $config = $act['action_config'] ?? []; $type = $act['action_type'] ?? ''; @endphp

@if($type === 'send_email')
<div class="row g-2">
  <div class="col-12 col-md-6">
    <label class="ncv-label" style="font-size:.78rem;">To Email (leave blank for owner)</label>
    <input type="email" name="actions[{{ $i }}][action_config][to_email]" class="ncv-input ncv-input-sm"
           value="{{ $config['to_email'] ?? '' }}" placeholder="recipient@example.com">
  </div>
  <div class="col-12 col-md-6">
    <label class="ncv-label" style="font-size:.78rem;">Subject</label>
    <input type="text" name="actions[{{ $i }}][action_config][subject]" class="ncv-input ncv-input-sm"
           value="{{ $config['subject'] ?? '' }}" placeholder="Workflow Notification">
  </div>
  <div class="col-12">
    <label class="ncv-label" style="font-size:.78rem;">Message</label>
    <textarea name="actions[{{ $i }}][action_config][message]" class="ncv-input ncv-input-sm" rows="3">{{ $config['message'] ?? '' }}</textarea>
  </div>
</div>

@elseif($type === 'assign_user')
<div>
  <label class="ncv-label" style="font-size:.78rem;">Assign To User</label>
  <select name="actions[{{ $i }}][action_config][user_id]" class="ncv-select ncv-select-sm">
    <option value="">Select user…</option>
    @foreach($users as $u)
      <option value="{{ $u->id }}" {{ ($config['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
    @endforeach
  </select>
</div>

@elseif($type === 'change_status')
<div>
  <label class="ncv-label" style="font-size:.78rem;">New Status Value</label>
  <input type="text" name="actions[{{ $i }}][action_config][status]" class="ncv-input ncv-input-sm"
         value="{{ $config['status'] ?? '' }}" placeholder="e.g. qualified, closed_won">
</div>

@elseif($type === 'send_webhook')
<div>
  <label class="ncv-label" style="font-size:.78rem;">Webhook URL</label>
  <input type="url" name="actions[{{ $i }}][action_config][url]" class="ncv-input ncv-input-sm"
         value="{{ $config['url'] ?? '' }}" placeholder="https://hooks.example.com/crm">
</div>
@endif
