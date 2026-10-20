<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            background: #ffffff;
            padding: 30px 40px;
        }

        /* Header / Store Name */
        .store-header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .store-name {
            font-size: 20pt;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: 1px;
        }
        .store-subtitle {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Invoice Title & Meta */
        .invoice-meta {
            display: table;
            width: 100%;
            margin-bottom: 18px;
        }
        .invoice-meta-left {
            display: table-cell;
            vertical-align: top;
        }
        .invoice-meta-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
        }
        .invoice-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 6px;
        }
        .meta-row {
            font-size: 9.5pt;
            color: #4b5563;
            margin-bottom: 3px;
        }
        .meta-label {
            display: inline-block;
            font-weight: bold;
            min-width: 110px;
            color: #374151;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .items-table thead tr {
            background-color: #4f46e5;
            color: #ffffff;
        }
        .items-table thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 9.5pt;
            font-weight: bold;
        }
        .items-table thead th.text-right {
            text-align: right;
        }
        .items-table thead th.text-center {
            text-align: center;
        }
        .items-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .items-table tbody td {
            padding: 7px 10px;
            font-size: 10pt;
            color: #1f2937;
            vertical-align: top;
        }
        .items-table tbody td.text-right {
            text-align: right;
        }
        .items-table tbody td.text-center {
            text-align: center;
        }
        .items-table tfoot tr {
            background-color: #f3f4f6;
        }
        .items-table tfoot td {
            padding: 8px 10px;
            font-size: 10.5pt;
        }
        .items-table tfoot .total-label {
            text-align: right;
            font-weight: bold;
            color: #374151;
        }
        .items-table tfoot .total-value {
            text-align: right;
            font-weight: bold;
            font-size: 12pt;
            color: #4f46e5;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 16px 0;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 16px;
            border-top: 1px dashed #d1d5db;
        }
        .footer-text {
            font-size: 10.5pt;
            color: #6b7280;
            font-style: italic;
        }
        .footer-brand {
            font-size: 9pt;
            color: #9ca3af;
            margin-top: 6px;
        }
    </style>
</head>
<body>

    {{-- ===== Store Header ===== --}}
    <div class="store-header invoice-header">
        <div class="store-name invoice-brand">Buana Store</div>
        <div class="store-subtitle">Gerai Seluler &amp; Aksesoris</div>
    </div>

    {{-- ===== Invoice Meta ===== --}}
    <div class="invoice-meta">
        <div class="invoice-meta-left">
            <div class="invoice-title">FAKTUR PENJUALAN</div>
            <div class="meta-row">
                <span class="meta-label">No. Faktur</span>
                <span>: {{ $sale->invoice_number }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Tanggal</span>
                <span>: {{ $sale->sold_at->format('d/m/Y') }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Waktu</span>
                <span>: {{ $sale->sold_at->format('H:i') }} WIB</span>
            </div>
        </div>
        <div class="invoice-meta-right">
            <div class="meta-row">
                <span class="meta-label" style="min-width: auto;">Kasir</span>
                <span>: {{ $sale->user->name ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- ===== Items Table ===== --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No.</th>
                <th>Nama Produk</th>
                <th class="text-right" style="width: 90px;">Harga Satuan</th>
                <th class="text-center" style="width: 50px;">Qty</th>
                <th class="text-right" style="width: 100px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    {{ $item->product->name ?? '-' }}
                    @if ($item->product && $item->product->sku)
                        <br><span style="font-size: 8.5pt; color: #9ca3af;">SKU: {{ $item->product->sku }}</span>
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total-label">TOTAL PEMBAYARAN</td>
                <td class="total-value">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ===== Footer ===== --}}
    <div class="footer">
        <div class="footer-text">Terima kasih atas pembelian Anda!</div>
        <div class="footer-brand">Buana Store &mdash; Gerai Seluler &amp; Aksesoris</div>
    </div>

</body>
</html>
