<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>{{ $document->document_number }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11pt; color: #1e293b; line-height: 1.6; }
    .page { padding: 40px 50px; }
    .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 16px; margin-bottom: 24px; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .doc-number { font-size: 10pt; color: #64748b; margin-bottom: 4px; }
    .doc-title { font-size: 16pt; font-weight: bold; color: #0f172a; }
    .doc-type { display: inline-block; padding: 2px 8px; background: #eff6ff; color: #1d4ed8; border-radius: 4px; font-size: 9pt; margin-top: 6px; }
    .meta-grid { display: table; width: 100%; margin-bottom: 24px; }
    .meta-row { display: table-row; }
    .meta-label { display: table-cell; width: 140px; font-size: 9pt; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; padding: 3px 0; vertical-align: top; }
    .meta-value { display: table-cell; font-size: 10pt; color: #334155; padding: 3px 0; vertical-align: top; }
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }
    .body-content { font-size: 10.5pt; line-height: 1.7; }
    .body-content h1 { font-size: 14pt; margin: 16px 0 8px; color: #0f172a; }
    .body-content h2 { font-size: 12pt; margin: 14px 0 6px; color: #0f172a; }
    .body-content h3 { font-size: 11pt; margin: 12px 0 5px; color: #0f172a; }
    .body-content p { margin-bottom: 10px; }
    .body-content ul, .body-content ol { margin: 8px 0 8px 20px; }
    .body-content li { margin-bottom: 4px; }
    .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 9pt; color: #94a3b8; }
    .signature-section { margin-top: 40px; page-break-inside: avoid; }
    .sig-title { font-size: 11pt; font-weight: bold; margin-bottom: 16px; color: #0f172a; }
    .sig-block { border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px; }
    .sig-name { font-weight: bold; font-size: 10pt; }
    .sig-email { color: #64748b; font-size: 9pt; }
    .sig-status { font-size: 9pt; color: #16a34a; margin-top: 6px; }
    .sig-img { margin-top: 8px; max-width: 200px; border: 1px solid #e2e8f0; border-radius: 4px; background: #fff; }
  </style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="header-top">
      <div>
        <div class="doc-number">{{ $document->document_number }}</div>
        <div class="doc-title">{{ $document->title }}</div>
        <div><span class="doc-type">{{ $document->type->label() }}</span></div>
      </div>
      <div style="text-align:right;font-size:9pt;color:#64748b;">
        <div style="font-weight:bold;font-size:10pt;color:#0f172a;">{{ auth()->user()?->tenant?->name ?? '' }}</div>
        <div>Status: {{ $document->status->label() }}</div>
        <div>Generated: {{ now()->format('M j, Y') }}</div>
      </div>
    </div>
  </div>

  <div class="meta-grid">
    @if($document->account)
    <div class="meta-row">
      <div class="meta-label">Account</div>
      <div class="meta-value">{{ $document->account->name }}</div>
    </div>
    @endif
    @if($document->contact)
    <div class="meta-row">
      <div class="meta-label">Contact</div>
      <div class="meta-value">{{ $document->contact->first_name }} {{ $document->contact->last_name }}</div>
    </div>
    @endif
    @if($document->expires_at)
    <div class="meta-row">
      <div class="meta-label">Expires</div>
      <div class="meta-value">{{ $document->expires_at->format('M j, Y') }}</div>
    </div>
    @endif
  </div>

  <hr class="divider">

  <div class="body-content">
    {!! $document->body !!}
  </div>

  @if($document->signatories->where('status', 'signed')->count() > 0)
  <div class="signature-section">
    <hr class="divider">
    <div class="sig-title">Signatures</div>
    @foreach($document->signatories->where('status', 'signed') as $signatory)
    <div class="sig-block">
      <div class="sig-name">{{ $signatory->name }}</div>
      <div class="sig-email">{{ $signatory->email }}</div>
      <div class="sig-status">✓ Signed {{ $signatory->signed_at?->format('M j, Y H:i') }} UTC</div>
      @if($signatory->signature_data)
      <img src="{{ $signatory->signature_data }}" alt="Signature" class="sig-img">
      @endif
    </div>
    @endforeach
  </div>
  @endif

  <div class="footer">
    <div>{{ $document->document_number }} — {{ $document->title }}</div>
    <div>{{ now()->format('M j, Y') }}</div>
  </div>

</div>
</body>
</html>
