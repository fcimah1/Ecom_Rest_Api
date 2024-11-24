<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class InvoiceReportController extends Controller
{
    public function invoiceDownload($id)
    {
        try {
            $font_family = "'Roboto','sans-serif'";
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';

            $order = Order::findOrFail($id);

            return Pdf::loadView('backend.invoices.invoice', [
                'order' => $order,
                'font_family' => $font_family,
                'direction' => $direction,
                'text_align' => $text_align,
                'not_text_align' => $not_text_align

            ],[])->download('order-' . $order->code . '.pdf');
    
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}