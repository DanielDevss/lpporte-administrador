<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function download(string $folio) {
        $order = Order::where('folio', $folio)->first();
        if(!$order) {
            return http_response_code(404);
        }

        $pdf = Pdf::loadView('orders.order-ticket', compact('order'));
        return $pdf->download('ORDER_' . $folio . '.pdf');
    }
}
