<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote {{ $quote->quote_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 3px solid #2563eb; padding-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #2563eb; }
        .quote-title { font-size: 28px; font-weight: bold; color: #1f2937; text-align: right; }
        .quote-number { font-size: 14px; color: #6b7280; text-align: right; margin-top: 4px; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-block { width: 48%; }
        .info-block h3 { font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 1px; margin-bottom: 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .info-block p { margin-bottom: 2px; }
        .info-block .label { color: #6b7280; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        thead th { background: #f3f4f6; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #374151; letter-spacing: 0.5px; border-bottom: 2px solid #d1d5db; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; }
        .totals table { margin-bottom: 0; }
        .totals td { padding: 6px 12px; }
        .totals .total-row { font-size: 16px; font-weight: bold; border-top: 2px solid #1f2937; }
        .totals .total-row td { padding-top: 10px; }
        .terms { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .terms h3 { font-size: 13px; font-weight: bold; margin-bottom: 8px; }
        .terms p { font-size: 11px; color: #6b7280; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 60px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <table style="border: none; margin-bottom: 40px; border-bottom: 3px solid #2563eb; padding-bottom: 20px;">
            <tr>
                <td style="border: none; vertical-align: top;">
                    <div class="company-name">NavCRM</div>
                </td>
                <td style="border: none; vertical-align: top; text-align: right;">
                    <div class="quote-title">QUOTE</div>
                    <div class="quote-number">#{{ $quote->quote_number }}</div>
                    <span class="status-badge status-{{ $quote->status->value }}">{{ ucfirst($quote->status->value) }}</span>
                </td>
            </tr>
        </table>

        <table style="border: none; margin-bottom: 30px;">
            <tr>
                <td style="border: none; vertical-align: top; width: 50%;">
                    <h3 style="font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 1px; margin-bottom: 8px;">Bill To</h3>
                    @if($quote->account)
                        <p style="font-weight: bold;">{{ $quote->account->name }}</p>
                    @endif
                    @if($quote->contact)
                        <p>{{ $quote->contact->first_name }} {{ $quote->contact->last_name }}</p>
                        @if($quote->contact->email)
                            <p>{{ $quote->contact->email }}</p>
                        @endif
                    @endif
                </td>
                <td style="border: none; vertical-align: top; width: 50%; text-align: right;">
                    <h3 style="font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 1px; margin-bottom: 8px;">Quote Details</h3>
                    <p><span style="color: #6b7280;">Date:</span> {{ $quote->created_at->format('M d, Y') }}</p>
                    <p><span style="color: #6b7280;">Valid Until:</span> {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'N/A' }}</p>
                    @if($quote->preparedBy)
                        <p><span style="color: #6b7280;">Prepared By:</span> {{ $quote->preparedBy->name }}</p>
                    @endif
                    @if($quote->opportunity)
                        <p><span style="color: #6b7280;">Opportunity:</span> {{ $quote->opportunity->name }}</p>
                    @endif
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Description</th>
                    <th class="text-right" style="width: 12%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 12%;">Discount</th>
                    <th class="text-right" style="width: 16%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->lineItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->discount_percent > 0 ? number_format($item->discount_percent, 1) . '%' : '-' }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">${{ number_format($quote->subtotal, 2) }}</td>
                </tr>
                @if($quote->discount_amount > 0)
                <tr>
                    <td>Discount ({{ $quote->discount_type === 'percentage' ? number_format($quote->discount_value, 1) . '%' : 'Fixed' }})</td>
                    <td class="text-right">-${{ number_format($quote->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($quote->tax_amount > 0)
                <tr>
                    <td>Tax ({{ number_format($quote->tax_rate, 2) }}%)</td>
                    <td class="text-right">${{ number_format($quote->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total</td>
                    <td class="text-right">${{ number_format($quote->total, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($quote->notes)
        <div class="terms">
            <h3>Notes</h3>
            <p>{{ $quote->notes }}</p>
        </div>
        @endif

        @if($quote->terms)
        <div class="terms">
            <h3>Terms & Conditions</h3>
            <p>{{ $quote->terms }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Generated by NavCRM on {{ now()->format('M d, Y \a\t h:i A') }}</p>
        </div>
    </div>
</body>
</html>
