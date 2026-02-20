<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Document — {{ $signatory->document->document_number }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, -apple-system, sans-serif; background: #f1f5f9; color: #1e293b; min-height: 100vh; }
    .portal-header { background: #fff; border-bottom: 1px solid #e2e8f0; padding: .875rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    .portal-header-title { font-weight: 700; font-size: .95rem; color: #0f172a; }
    .portal-header-sub { font-size: .75rem; color: #64748b; }
    .container { max-width: 800px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: .75rem; margin-bottom: 1.25rem; overflow: hidden; }
    .card-header { padding: .875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
    .card-title { font-weight: 700; font-size: .9rem; color: #0f172a; display: flex; align-items: center; gap: .5rem; }
    .card-body { padding: 1.25rem; }
    .badge { display: inline-flex; align-items: center; padding: .2rem .6rem; border-radius: 20px; font-size: .72rem; font-weight: 600; }
    .doc-body { font-size: .9rem; line-height: 1.75; color: #334155; }
    .doc-body h1 { font-size: 1.2rem; margin: 1rem 0 .5rem; color: #0f172a; }
    .doc-body h2 { font-size: 1rem; margin: .875rem 0 .4rem; color: #0f172a; }
    .doc-body p { margin-bottom: .75rem; }
    .doc-body ul, .doc-body ol { margin: .5rem 0 .75rem 1.25rem; }
    .sig-canvas-wrap { border: 2px dashed #cbd5e1; border-radius: .5rem; background: #f8fafc; overflow: hidden; }
    canvas { display: block; width: 100%; touch-action: none; }
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .5rem 1.1rem; border-radius: .5rem; font-size: .875rem; font-weight: 600; cursor: pointer; border: none; transition: background .15s; text-decoration: none; }
    .btn-primary { background: #1d4ed8; color: #fff; }
    .btn-primary:hover { background: #1e40af; }
    .btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .btn-outline:hover { background: #f9fafb; }
    .btn-sm { padding: .35rem .75rem; font-size: .8rem; }
    .alert { border-radius: .5rem; padding: .875rem 1rem; font-size: .875rem; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: .625rem; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
    .meta-row { display: flex; gap: .5rem; font-size: .8rem; margin-bottom: .375rem; }
    .meta-label { color: #94a3b8; min-width: 80px; font-weight: 600; text-transform: uppercase; font-size: .7rem; letter-spacing: .04em; }
  </style>
</head>
<body>

<div class="portal-header">
  <div>
    <div class="portal-header-title"><i class="bi bi-file-earmark-text me-1"></i> Document Signing Portal</div>
    <div class="portal-header-sub">Secure, private signing — no account required</div>
  </div>
  <div>
    <span class="badge" style="background:#eff6ff;color:#1d4ed8;">{{ $signatory->document->type->label() }}</span>
  </div>
</div>

<div class="container">

  @if(session('success'))
  <div class="alert alert-success">
    <i class="bi bi-check-circle-fill" style="flex-shrink:0;margin-top:.1rem;"></i>
    <div>
      <strong>Signature recorded!</strong><br>
      <span style="font-size:.82rem;">{{ session('success') }} This page is now read-only.</span>
    </div>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger">
    <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:.1rem;"></i>
    {{ session('error') }}
  </div>
  @endif

  {{-- Signatory info --}}
  <div class="card">
    <div class="card-body" style="padding:.875rem 1.25rem;">
      <div class="d-flex flex-wrap gap-3">
        <div class="meta-row"><span class="meta-label">Document</span><span>{{ $signatory->document->document_number }} — {{ $signatory->document->title }}</span></div>
        <div class="meta-row"><span class="meta-label">For</span><span>{{ $signatory->name }} ({{ $signatory->email }})</span></div>
        <div class="meta-row"><span class="meta-label">Status</span>
          @php
            $badge = match($signatory->status) {
              'signed'   => ['bg' => '#dcfce7', 'color' => '#166534', 'label' => 'Signed'],
              'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Rejected'],
              'viewed'   => ['bg' => '#dbeafe', 'color' => '#1d4ed8',  'label' => 'Viewed'],
              default    => ['bg' => '#f1f5f9', 'color' => '#64748b',  'label' => 'Pending'],
            };
          @endphp
          <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};">{{ $badge['label'] }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Document Content --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="bi bi-file-text"></i> {{ $signatory->document->title }}</div>
    </div>
    <div class="card-body">
      <div class="doc-body">
        {!! $signatory->document->body !!}
      </div>
    </div>
  </div>

  {{-- Signing Panel --}}
  @if($signatory->status === 'signed')
    <div class="card">
      <div class="card-body">
        <div class="alert alert-success" style="margin:0;">
          <i class="bi bi-patch-check-fill" style="font-size:1.2rem;flex-shrink:0;"></i>
          <div>
            <strong>You have already signed this document.</strong><br>
            <span style="font-size:.82rem;">Signed on {{ $signatory->signed_at?->format('F j, Y \a\t H:i') }} UTC</span>
          </div>
        </div>
        @if($signatory->signature_data)
        <div style="margin-top:1rem;">
          <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.5rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Your Signature</div>
          <img src="{{ $signatory->signature_data }}" alt="Your signature" style="max-width:280px;border:1px solid #e2e8f0;border-radius:.5rem;background:#fff;padding:.5rem;">
        </div>
        @endif
      </div>
    </div>

  @elseif($signatory->status === 'rejected')
    <div class="card">
      <div class="card-body">
        <div class="alert alert-danger" style="margin:0;">
          <i class="bi bi-x-circle-fill" style="font-size:1.2rem;flex-shrink:0;"></i>
          <div>
            <strong>This signing request was rejected.</strong><br>
            <span style="font-size:.82rem;">Please contact the sender if this was a mistake.</span>
          </div>
        </div>
      </div>
    </div>

  @else
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="bi bi-pen"></i> Sign Document</div>
      </div>
      <div class="card-body">
        <div class="alert alert-info" style="margin-bottom:1.25rem;">
          <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:.1rem;"></i>
          <span style="font-size:.82rem;">Draw your signature in the box below. By clicking "Submit Signature", you agree that this constitutes your digital signature on this document.</span>
        </div>

        <form method="POST" action="{{ route('signing.sign', $signatory->sign_token) }}" id="sign-form">
          @csrf
          <input type="hidden" name="signature_data" id="sig-data">

          <div style="margin-bottom:1rem;">
            <div style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.5rem;">Draw your signature below:</div>
            <div class="sig-canvas-wrap">
              <canvas id="sig-canvas" height="180"></canvas>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" id="clear-btn" class="btn btn-outline btn-sm"><i class="bi bi-eraser"></i> Clear</button>
            </div>
            <div id="sig-error" style="color:#ef4444;font-size:.78rem;margin-top:.375rem;display:none;">
              <i class="bi bi-exclamation-circle me-1"></i>Please draw your signature before submitting.
            </div>
          </div>

          <div style="display:flex;gap:.75rem;align-items:center;">
            <button type="submit" class="btn btn-primary" id="submit-btn">
              <i class="bi bi-pen"></i> Submit Signature
            </button>
            <span style="font-size:.75rem;color:#94a3b8;">Your IP address and timestamp will be recorded.</span>
          </div>
        </form>
      </div>
    </div>
  @endif

</div>

@if(!in_array($signatory->status, ['signed', 'rejected']))
<script src="https://cdn.jsdelivr.net/npm/signature_pad/dist/signature_pad.umd.min.js"></script>
<script>
  const canvas  = document.getElementById('sig-canvas');
  const sigPad  = new SignaturePad(canvas, { penColor: '#1e293b', backgroundColor: 'rgba(0,0,0,0)' });

  // Resize canvas to fill container
  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width  = canvas.offsetWidth  * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    sigPad.clear();
  }
  window.addEventListener('resize', resizeCanvas);
  resizeCanvas();

  document.getElementById('clear-btn').addEventListener('click', () => sigPad.clear());

  document.getElementById('sign-form').addEventListener('submit', function(e) {
    if (sigPad.isEmpty()) {
      e.preventDefault();
      document.getElementById('sig-error').style.display = 'block';
      return;
    }
    document.getElementById('sig-error').style.display = 'none';
    document.getElementById('sig-data').value = sigPad.toDataURL('image/png');
  });
</script>
@endif

</body>
</html>
