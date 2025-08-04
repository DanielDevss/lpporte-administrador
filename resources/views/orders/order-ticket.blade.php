@php
    use App\Enums\PaymentIntentStatusEnum;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Compra - {{ $order->folio }}</title>

    <style>
        *, * * {
            margin: 0px;
            padding: 0px;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: white;
            color: #333;
            line-height: 1.4;
        }

        .receipt-container {
            max-width: 800px;
            margin-left: 0;
            margin-top: 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .receipt-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .receipt-header .subtitle {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 300;
        }

        .receipt-content {
            padding: 25px;
        }

        .order-info {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .info-row {
            display: table-row;
        }

        .info-group {
            display: table-cell;
            padding: 12px;
            border-right: 1px solid #ddd;
            vertical-align: top;
            width: 25%;
        }

        .info-group:last-child {
            border-right: none;
        }

        .info-label {
            font-size: 10px;
            font-weight: 600;
            color: #667eea;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .customer-info {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #17a2b8;
        }

        .customer-info h3 {
            color: #17a2b8;
            font-size: 16px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .customer-details {
            display: table;
            width: 100%;
        }

        .customer-row {
            display: table-row;
        }

        .customer-group {
            display: table-cell;
            padding: 8px 12px;
            vertical-align: top;
            width: 25%;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }

        .products-table, .suscriptions-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .products-table th, .suscriptions-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .products-table td, .suscriptions-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .product-name, .suscription-name {
            font-weight: 600;
            color: #333;
        }

        .product-plan {
            background: #667eea;
            color: white;
            padding: 3px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .price {
            font-weight: 600;
            color: #28a745;
        }

        .quantity {
            background: #f8f9fa;
            padding: 3px 6px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            min-width: 25px;
        }

        .total-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .total-row.final {
            font-size: 20px;
            font-weight: 700;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            padding-top: 12px;
            margin-top: 12px;
        }

        .receipt-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .footer-text {
            color: #666;
            font-size: 12px;
            line-height: 1.5;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .no-items {
            text-align: center;
            color: #666;
            padding: 20px;
            /* font-style: italic; */
        }

    </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif;">
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1>Comprobante de Compra</h1>
            <div class="subtitle">Gracias por tu compra</div>
        </div>

        <!-- Content -->
        <div class="receipt-content">
            <!-- Order Information -->
            <div class="order-info">
                <div class="info-row">
                    <div class="info-group">
                        <div class="info-label">Número de Folio</div>
                        <div class="info-value">{{ $order->folio }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Fecha de Compra</div>
                        <div class="info-value">{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : 'N/A' }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <span>
                                {{ PaymentIntentStatusEnum::from($order->status)->label() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="customer-info">
                <h3>Información del Cliente</h3>
                <div class="customer-details">
                    <div class="customer-row">
                        <div class="customer-group">
                            <div class="info-label">Nombre</div>
                            <div class="info-value">{{ $order->customer->user->name ?? 'N/A' }}</div>
                        </div>
                        <div class="customer-group">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $order->customer->user->email ?? 'N/A' }}</div>
                        </div>
                        <div class="customer-group">
                            <div class="info-label">Plan Actual</div>
                            <div class="info-value">{{ ucfirst($order->customer->currentPlan()) }}</div>
                        </div>
                        <div class="customer-group">
                            <div class="info-label">Código de Referencia</div>
                            <div class="info-value">{{ $order->customer->reference_code ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            @if($order->products->count() > 0)
            <div class="products-section">
                <h3 class="section-title">Productos Comprados</h3>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Plan</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->products as $product)
                            <tr>
                                <td>
                                    <div class="product-name">{{ $product->title }}</div>
                                    <small style="color: #666;">{{ $product?->brand?->name ?? 'Sin marca' }}</small>
                                </td>
                                <td>
                                    <span class="product-plan">{{ ucfirst($product->pivot->plan) }}</span>
                                </td>
                                <td>
                                    <span class="quantity">{{ $product->pivot->quantity }}</span>
                                </td>
                                <td class="price">${{ number_format($product->pivot->amount / 100, 2) }}</td>
                                <td class="price">${{ number_format(($product->pivot->amount * $product->pivot->quantity) / 100, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Suscriptions Section -->
            @if($order->suscriptions->count() > 0)
            <div class="suscriptions-section">
                <h3 class="section-title">Suscripciones</h3>
                <table class="suscriptions-table">
                    <thead>
                        <tr>
                            <th>Suscripción</th>
                            <th>Descripción</th>
                            <th>Precio Anual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->suscriptions as $suscription)
                            <tr>
                                <td>
                                    <div class="suscription-name">{{ $suscription->name }}</div>
                                </td>
                                <td>
                                    <small style="color: #666;">
                                        @if($suscription->benefits)
                                            {{ implode(', ', $suscription->benefits) }}
                                        @else
                                            Sin descripción disponible
                                        @endif
                                    </small>
                                </td>
                                <td class="price">${{ number_format($suscription->amount / 100, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Total Section -->
            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format(($order->amount - $order->tax) / 100, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>IVA (16%):</span>
                    <span>${{ number_format($order->tax / 100, 2) }}</span>
                </div>
                <div class="total-row final">
                    <span>Total:</span>
                    <span>${{ number_format($order->amount / 100, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="footer-text">
                <p><strong>¡Gracias por tu compra!</strong></p>
                <p>Este comprobante es válido como factura fiscal.</p>
                <p>Para cualquier consulta, contacta a nuestro servicio al cliente.</p>
                <p style="margin-top: 10px; font-size: 10px; color: #999;">
                    Generado el {{ now()->format('d/m/Y H:i:s') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>