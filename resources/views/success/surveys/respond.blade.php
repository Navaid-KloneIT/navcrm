<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .survey-container {
            width: 100%;
            max-width: 640px;
            padding: 2rem 1rem;
        }
        .survey-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .survey-header {
            background: linear-gradient(135deg, #7c3aed, #4c1d95);
            color: #fff;
            padding: 2rem 2rem 1.75rem;
            text-align: center;
        }
        .survey-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 .5rem;
        }
        .survey-header p {
            margin: 0;
            font-size: .9rem;
            opacity: .85;
        }
        .survey-body {
            padding: 2rem;
        }
        .score-label {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            text-align: center;
        }
        .score-buttons {
            display: flex;
            gap: .5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .score-btn {
            display: none;
        }
        .score-btn-label {
            width: 44px;
            height: 44px;
            border-radius: .5rem;
            border: 2px solid #e2e8f0;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .9rem;
            color: #475569;
            cursor: pointer;
            transition: all .15s ease;
        }
        .score-btn-label:hover {
            border-color: #7c3aed;
            color: #7c3aed;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124,58,237,.15);
        }
        .score-btn:checked + .score-btn-label {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
            color: #fff;
        }
        /* NPS color coding */
        .score-btn.nps-promoter:checked + .score-btn-label {
            background: #10b981;
            border-color: #10b981;
        }
        .score-btn.nps-passive:checked + .score-btn-label {
            background: #f59e0b;
            border-color: #f59e0b;
        }
        .score-btn.nps-detractor:checked + .score-btn-label {
            background: #ef4444;
            border-color: #ef4444;
        }
        /* CSAT color coding */
        .score-btn.csat-high:checked + .score-btn-label {
            background: #10b981;
            border-color: #10b981;
        }
        .score-btn.csat-mid:checked + .score-btn-label {
            background: #f59e0b;
            border-color: #f59e0b;
        }
        .score-btn.csat-low:checked + .score-btn-label {
            background: #ef4444;
            border-color: #ef4444;
        }
        .nps-scale-labels {
            display: flex;
            justify-content: space-between;
            font-size: .75rem;
            color: #94a3b8;
            margin-top: -.75rem;
            margin-bottom: 1.5rem;
            padding: 0 .25rem;
        }
        .thank-you-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>

<div class="survey-container">
  <div class="survey-card">

    @php
      $isSubmitted = isset($submitted) ? $submitted : false;
    @endphp

    @if($isSubmitted)
    {{-- Thank You --}}
    <div class="survey-header">
      <h1>Thank You!</h1>
    </div>
    <div class="survey-body text-center" style="padding:3rem 2rem;">
      <div class="thank-you-icon">
        <i class="bi bi-check-lg"></i>
      </div>
      <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-bottom:.75rem;">Your response has been recorded</h2>
      <p style="color:#64748b;font-size:.95rem;max-width:400px;margin:0 auto;">
        We appreciate you taking the time to share your feedback. Your input helps us improve our service.
      </p>
    </div>

    @else
    {{-- Survey Form --}}
    <div class="survey-header">
      <h1>{{ $survey->name }}</h1>
      @if($survey->description)
        <p>{{ $survey->description }}</p>
      @endif
    </div>
    <div class="survey-body">
      <form method="POST" action="{{ route('survey.respond.store', $survey->token) }}" id="surveyForm">
        @csrf

        {{-- NPS Survey --}}
        @if($survey->type->value === 'nps')
        <div class="score-label">How likely are you to recommend us?</div>
        <div class="score-buttons">
          @for($i = 0; $i <= 10; $i++)
            @php
              if($i >= 9) $cssClass = 'nps-promoter';
              elseif($i >= 7) $cssClass = 'nps-passive';
              else $cssClass = 'nps-detractor';
            @endphp
            <div>
              <input type="radio" name="score" value="{{ $i }}" id="score_{{ $i }}"
                     class="score-btn {{ $cssClass }}" required {{ old('score') == $i ? 'checked' : '' }}>
              <label for="score_{{ $i }}" class="score-btn-label">{{ $i }}</label>
            </div>
          @endfor
        </div>
        <div class="nps-scale-labels">
          <span>Not at all likely</span>
          <span>Extremely likely</span>
        </div>
        @endif

        {{-- CSAT Survey --}}
        @if($survey->type->value === 'csat')
        <div class="score-label">How satisfied were you?</div>
        <div class="score-buttons">
          @for($i = 1; $i <= 10; $i++)
            @php
              if($i >= 9) $cssClass = 'csat-high';
              elseif($i >= 7) $cssClass = 'csat-mid csat-high';
              elseif($i >= 4) $cssClass = 'csat-mid';
              else $cssClass = 'csat-low';
            @endphp
            <div>
              <input type="radio" name="score" value="{{ $i }}" id="score_{{ $i }}"
                     class="score-btn {{ $cssClass }}" required {{ old('score') == $i ? 'checked' : '' }}>
              <label for="score_{{ $i }}" class="score-btn-label">{{ $i }}</label>
            </div>
          @endfor
        </div>
        <div class="nps-scale-labels">
          <span>Very unsatisfied</span>
          <span>Very satisfied</span>
        </div>
        @endif

        @error('score')
          <div class="alert alert-danger py-2" style="font-size:.85rem;">{{ $message }}</div>
        @enderror

        {{-- Comment --}}
        <div class="mb-3">
          <label for="comment" class="form-label" style="font-weight:600;font-size:.9rem;color:#1e293b;">
            Comments <span style="font-weight:400;color:#94a3b8;">(optional)</span>
          </label>
          <textarea class="form-control" id="comment" name="comment" rows="3"
                    placeholder="Tell us more about your experience..." style="border-radius:.5rem;">{{ old('comment') }}</textarea>
        </div>

        {{-- Email --}}
        <div class="mb-4">
          <label for="email" class="form-label" style="font-weight:600;font-size:.9rem;color:#1e293b;">
            Email <span style="font-weight:400;color:#94a3b8;">(optional)</span>
          </label>
          <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                 placeholder="Enter your email so we can follow up" style="border-radius:.5rem;">
          @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn w-100" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;font-weight:600;padding:.75rem;border-radius:.5rem;border:none;font-size:1rem;">
          <i class="bi bi-send-fill me-2"></i>Submit Response
        </button>
      </form>
    </div>
    @endif

  </div>

  {{-- Footer --}}
  <div class="text-center mt-3" style="color:#94a3b8;font-size:.78rem;">
    Powered by NavCRM
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
