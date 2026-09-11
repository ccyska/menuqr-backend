<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Laporan Penjualan</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .summary {
            margin-bottom: 20px;
        }

        .summary p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 7px;
        }

        th {
            background-color: #eeeeee;
            text-align: left;
        }

        .number {
            text-align: right;
        }
    </style>
</head>

<body>

    <h1>Laporan Penjualan</h1>

    <div class="summary">
        <p><strong>Total Pesanan:</strong> {{ $totalOrders }}</p>
        <p><strong>Total Penjualan:</strong> Rp{{ number_format($totalSales, 0, ',', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Pesanan</th>
                <th>Tanggal</th>
                <th>Restaurant</th>
                <th>Meja</th>
                <th>Nama Pelanggan</th>
                <th>Subtotal</th>
                <th>Diskon</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->order_code }}</td>

                    <td>
                        {{ $order->created_at->format('Y-m-d H:i') }}
                    </td>

                    <td>
                        {{ $order->restaurant?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $order->table?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $order->customer_name ?? '-' }}
                    </td>

                    <td class="number">
                        Rp{{ number_format(($order->total ?? 0) + ($order->discount ?? 0), 0, ',', '.') }}
                    </td>

                    <td class="number">
                        Rp{{ number_format($order->discount ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="number">
                        Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ $order->status }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>