<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Test Report')</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 11px;
      line-height: 1.5;
      color: #1a1a1a;
      padding: 20px;
    }

    /* Header */
    .report-header {
      border-bottom: 3px solid #344767;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }
    .report-header h1 {
      font-size: 20px;
      color: #344767;
      margin-bottom: 4px;
    }
    .report-header .subtitle {
      font-size: 12px;
      color: #6c757d;
    }
    .report-meta {
      display: flex;
      gap: 30px;
      margin-top: 10px;
      flex-wrap: wrap;
    }
    .report-meta .meta-item {
      font-size: 11px;
    }
    .report-meta .meta-label {
      color: #6c757d;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 9px;
      letter-spacing: 0.5px;
    }
    .report-meta .meta-value {
      font-weight: 700;
      color: #344767;
    }

    /* Summary */
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      gap: 10px;
      margin-bottom: 20px;
    }
    .summary-box {
      border: 1px solid #e9ecef;
      border-radius: 6px;
      padding: 10px;
      text-align: center;
    }
    .summary-box .value {
      font-size: 22px;
      font-weight: 700;
    }
    .summary-box .label {
      font-size: 9px;
      text-transform: uppercase;
      color: #6c757d;
      letter-spacing: 0.5px;
    }
    .summary-box.passed .value { color: #2dce89; }
    .summary-box.failed .value { color: #f5365c; }
    .summary-box.skipped .value { color: #6c757d; }

    /* Progress bar */
    .progress-bar-container {
      height: 12px;
      background: #e9ecef;
      border-radius: 6px;
      overflow: hidden;
      margin-bottom: 20px;
      display: flex;
    }
    .progress-bar-container .bar-passed { background: #2dce89; }
    .progress-bar-container .bar-failed { background: #f5365c; }
    .progress-bar-container .bar-skipped { background: #adb5bd; }

    /* Tables */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
      font-size: 10px;
    }
    table th {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      padding: 6px 8px;
      text-align: left;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 9px;
      color: #495057;
      letter-spacing: 0.3px;
    }
    table td {
      border: 1px solid #dee2e6;
      padding: 5px 8px;
      vertical-align: top;
    }
    table tr.row-passed { background: #d4edda; }
    table tr.row-failed { background: #f8d7da; }
    table tr.row-blocked { background: #fff3cd; }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 9px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .badge-passed { background: #d4edda; color: #155724; }
    .badge-failed { background: #f8d7da; color: #721c24; }
    .badge-error { background: #fff3cd; color: #856404; }
    .badge-skipped { background: #e2e3e5; color: #383d41; }
    .badge-blocked { background: #fff3cd; color: #856404; }
    .badge-get { background: #d4edda; color: #155724; }
    .badge-post { background: #cce5ff; color: #004085; }
    .badge-put { background: #fff3cd; color: #856404; }
    .badge-patch { background: #d1ecf1; color: #0c5460; }
    .badge-delete { background: #f8d7da; color: #721c24; }
    .badge-critical { background: #f8d7da; color: #721c24; }
    .badge-high { background: #fff3cd; color: #856404; }
    .badge-medium { background: #d1ecf1; color: #0c5460; }
    .badge-low { background: #e2e3e5; color: #383d41; }

    /* Sections */
    .section-title {
      font-size: 14px;
      font-weight: 700;
      color: #344767;
      margin: 20px 0 10px;
      padding-bottom: 5px;
      border-bottom: 1px solid #dee2e6;
    }

    .scenario-block {
      margin-bottom: 15px;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      overflow: hidden;
    }
    .scenario-header {
      background: #f8f9fa;
      padding: 8px 12px;
      font-weight: 700;
      font-size: 11px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .scenario-body {
      padding: 0;
    }
    .scenario-notes {
      padding: 6px 12px;
      background: #f1f3f5;
      font-size: 10px;
      color: #495057;
    }

    /* Footer */
    .report-footer {
      margin-top: 30px;
      padding-top: 10px;
      border-top: 1px solid #dee2e6;
      font-size: 9px;
      color: #6c757d;
      display: flex;
      justify-content: space-between;
    }

    /* Print styles */
    @media print {
      body { padding: 0; }
      .no-print { display: none !important; }
      .scenario-block { break-inside: avoid; }
      table { break-inside: auto; }
      tr { break-inside: avoid; }
    }

    /* Print button */
    .print-actions {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      display: flex;
      gap: 8px;
    }
    .btn-print {
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-print-primary {
      background: #5e72e4;
      color: white;
    }
    .btn-print-secondary {
      background: #6c757d;
      color: white;
    }
    .btn-print:hover { opacity: 0.9; }
  </style>
</head>
<body>
  <div class="print-actions no-print">
    <button class="btn-print btn-print-primary" onclick="window.print()">
      🖨️ Print / Save PDF
    </button>
    <button class="btn-print btn-print-secondary" onclick="window.history.back()">
      ← Kembali
    </button>
  </div>

  @yield('content')

  <div class="report-footer">
    <span>Generated: {{ now()->format('d M Y H:i:s') }}</span>
    <span>{{ $siteSetting?->project_name ?? config('app.name') }} — Testing Report</span>
  </div>
</body>
</html>
