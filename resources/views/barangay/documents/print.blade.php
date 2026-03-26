<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentData['document_title'] }} - {{ $documentData['document_number'] }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
        }

        .page-shell {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding: 16px 20px;
            border: 1px solid #d1d5db;
            border-radius: 18px;
            background: #ffffff;
        }

        .action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #111827;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .button.primary {
            background: #111827;
            border-color: #111827;
            color: #ffffff;
        }

        .document-sheet {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 24px;
            padding: 56px 64px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .document-header {
            text-align: center;
        }

        .document-header .small {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        .document-header .medium {
            font-size: 15px;
            margin-top: 6px;
        }

        .document-title {
            margin-top: 24px;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .document-meta {
            margin-top: 14px;
            font-size: 14px;
            color: #374151;
        }

        .content-section {
            margin-top: 36px;
            font-size: 16px;
        }

        .content-section p {
            margin: 0 0 18px 0;
            text-align: justify;
        }

        .content-section p.emphasis {
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.03em;
        }

        .footer-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: 1fr 1fr;
            margin-top: 48px;
        }

        .signature-box {
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .signature-line {
            border-top: 1px solid #111827;
            padding-top: 8px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14px;
        }

        .signature-sub {
            font-size: 12px;
            color: #4b5563;
        }

        .processing-box {
            margin-top: 36px;
            border: 1px solid #d1d5db;
            border-radius: 18px;
            padding: 18px 20px;
            background: #f8fafc;
            font-size: 13px;
        }

        .processing-box p {
            margin: 6px 0;
        }

        .footer-note {
            margin-top: 22px;
            font-size: 12px;
            color: #4b5563;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page-shell {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .action-bar {
                display: none;
            }

            .document-sheet {
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 40px 48px;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="action-bar">
            <div>
                <strong>{{ $documentData['document_title'] }}</strong><br>
                <span style="font-size: 13px; color: #475569;">{{ $serviceRequest->reference_number }} · {{ $documentData['document_number'] }}</span>
            </div>

            <div class="action-group">
                <a href="{{ route('barangay.documents.show', $serviceRequest) }}" class="button">Back to Request</a>
                <button type="button" class="button primary" onclick="window.print()">Print Document</button>
            </div>
        </div>

        <div class="document-sheet">
            <header class="document-header">
                <div class="small">Republic of the Philippines</div>
                <div class="medium">Province of {{ $documentData['province_name'] }}</div>
                <div class="medium">{{ $documentData['city_name'] }}</div>
                <div class="medium">Barangay {{ $documentData['barangay_name'] }}</div>

                <div class="document-title">{{ $documentData['document_title'] }}</div>
                <div class="document-meta">
                    Document No.: {{ $documentData['document_number'] }}<br>
                    Date Issued: {{ $documentData['issue_date'] }}
                </div>
            </header>

            <section class="content-section">
                @foreach ($documentData['paragraphs'] as $paragraph)
                    @if (str_contains($paragraph, $documentData['resident_name']) && count($documentData['paragraphs']) > 2)
                        <p class="emphasis">{{ $documentData['resident_name'] }}</p>
                    @endif

                    <p>{{ $paragraph }}</p>
                @endforeach
            </section>

            <div class="footer-grid">
                <div class="signature-box">
                    <div class="signature-line">{{ strtoupper($documentData['prepared_by']) }}</div>
                    <div class="signature-sub">Prepared by / Processing Staff</div>
                </div>

                <div class="signature-box">
                    <div class="signature-line">BARANGAY AUTHORIZED SIGNATORY</div>
                    <div class="signature-sub">Barangay Official Signatory</div>
                </div>
            </div>

            <div class="processing-box">
                <p><strong>Reference Number:</strong> {{ $serviceRequest->reference_number }}</p>
                <p><strong>Service:</strong> {{ $serviceRequest->serviceType?->name }}</p>
                <p><strong>Resident:</strong> {{ $documentData['resident_name'] }}</p>
                <p><strong>Printed By:</strong> {{ $documentData['printed_by'] }}</p>
                <p><strong>Printed At:</strong> {{ $documentData['printed_at'] ?: 'Not yet logged' }}</p>
                <p><strong>Current Status:</strong> {{ str($serviceRequest->current_status)->replace('_', ' ')->title() }}</p>
            </div>

            <div class="footer-note">
                {{ $documentData['footer_note'] }}
            </div>
        </div>
    </div>
</body>
</html>
