<?php

namespace App\Http\Controllers\Api\PaymentGetway;

use App\Http\Controllers\Api\Website\CheckoutController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CombinedOrder;
use App\Models\ProductTax;
use App\Models\Upload;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\User;
use App\Services\TabbyService;
use Illuminate\Support\Facades\Session;
class TabbyController extends Controller
{

    protected $tabbyService;
    protected $data = []; // array to save your payment data
    protected $marchent_code;

    public function __construct() 
    {
        $this->marchent_code = env('MARCHENT_CODE');
        $this->tabbyService = new TabbyService();
    }
    public function getdata(int $combined_order_id, float $amount)
    {
        $cuurency = \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code;
        $order = Order::where('combined_order_id', $combined_order_id)->first();
        $customer = User::findOrFail($order->user_id);
        $orderDetail = OrderDetail::where('order_id', $order->id)->get();
        $products = $orderDetail->map(function ($item) {
            $photoIds = explode(',', $item->product->photos);
            $urls = Upload::whereIn('id', $photoIds)->pluck('file_name')->toArray();
            $product['images'] = array_map(function ($fileName) {
                return asset("/$fileName");
            }, $urls);
            return [
                'title' => $item->product->name,
                'description' => $item->product->description,
                'quantity' => $item->quantity,
                'unit_price' => number_format($item->product->unit_price, 2, '.', ''),
                'discount_amount' => number_format($item->product->discount, 2, '.', ''),
                'discount_type' => $item->product->discount_type,
                'category' => $item->product->category->name,
                // 'reference_id' => strval($item->product_id),
                'image_url' => $product['images'][0],
            ];
        });

        $items = $products->map(function ($item) {
            return [
                "title" => $item['title'],
                "description" => 'description product',
                "quantity" => $item['quantity'],
                "unit_price" => $item['unit_price'],
                "discount_amount" => $item['discount_amount'],
                "category" => $item['category'],
                // "reference_id" => $item['reference_id'],
                "image_url" => $item['image_url'],
            ];
        })->toArray();
        $zip_code = json_decode($order['shipping_address'])->postal_code ? json_decode($order['shipping_address'])->postal_code : "11751";
        $productsIds = $products->pluck('reference_id')->toArray();
        $totalTaxes = ProductTax::whereIn('product_id', $productsIds)->sum('tax');

        $data = [
            "payment" => [
                "amount" => (string) number_format($amount, 2, '.', ''),
                "currency" => "SAR",
                "description" => "Sample order description",
                "buyer" => [
                    "phone" => $customer->phone,
                    "email" => "card.success@tabby.ai",//$customer->email,
                    "name" => $customer->name,
                    "dob" => "1990-01-01"
                ],
                "shipping_address" => [
                    "city" => json_decode($order['shipping_address'])->state,
                    "address" => json_decode($order['shipping_address'])->address,
                    "zip" => $zip_code
                ],
                "order" => [
                    "tax_amount" => (string) number_format($totalTaxes, 2, '.', ''),
                    "shipping_amount" => (string) number_format($orderDetail->pluck('shipping_cost')[0], 2, '.', ''),
                    "discount_amount" => "0.00",
                    "updated_at" => $order->updated_at->toIso8601String(),
                    "reference_id" => $order->code,
                    "items" => $items,
                ],
                "buyer_history" => [
                    "registered_since" => $customer->created_at->toIso8601String(),
                    "loyalty_level" => 0,
                    "wishlist_count" => 0,
                    "is_social_networks_connected" => true,
                    "is_phone_number_verified" => true,
                    "is_email_verified" => true
                ],
                "order_history" => [
                    [
                        "purchased_at" => $order->created_at->toIso8601String(),
                        "amount" => number_format($amount, 2, '.', ''),
                        "payment_method" => "card",
                        "status" => "new",
                        "buyer" => [
                            "phone" => $customer->phone,
                            "email" => "card.success@tabby.ai",//$customer->email,
                            "name" => $customer->name,
                            "dob" => "1990-01-01"
                        ],
                        "shipping_address" => [
                            "city" => json_decode($order['shipping_address'])->state,
                            "address" => json_decode($order['shipping_address'])->address,
                            "zip" => $zip_code
                        ],
                    ]
                ],
                "meta" => (object) [
                    "order_id" => $order->id,
                    "customer" => $customer->id
                ],
                "attachment" => (object) [
                    "body" => "{\"flight_reservation_details\": {\"pnr\": \"TR9088999\",\"itinerary\": [...],\"insurance\": [...],\"passengers\": [...],\"affiliate_name\": \"some affiliate\"}}",
                    "content_type" => "application/vnd.tabby.v1+json"
                ]
            ],
            "lang" => "ar",
            "merchant_code" => $this->marchent_code,
            "merchant_urls" => [
                "success" => url('/tabby/success'),
                "cancel" => url('/tabby/cancel'),
                "failure" => url('/tabby/failure')
            ],
            "token" => null
        ];

        return $data;
    }
    public function initiateCheckoutWithRealData()
    {
        try {
            $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
            $amount = round($combined_order->grand_total );
            $data = $this->getdata($combined_order->id, $amount);
            $response = $this->tabbyService->createCheckoutSession($data);
            // dd($response);
            if (isset($response['id']) && $response['id'] != '') {
                $redirect_url = $response['configuration']['available_products']['installments'][0]['web_url'];
                $payment = $response['payment'];
                Session::put('payment',$payment);
                return redirect($redirect_url);                 // Redirect user to Tabby checkout
            } else {
                flash(translate('Payment is cancelled'))->error();
                return redirect()->route('tabby.cancel');
            }
        } catch (\Exception $e) {
            \Log::error('tabby '.$e->getMessage());
            return redirect()->home();
        }

    }
    public function success()
    {
        try {
            $payment = Session::get('payment');
            // to check if operation done in test mode or live mode
            // if (isset($payment['is_test']) && $payment['is_test']) {
            //         flash(translate('Payment is work with test version'))->error();
            //         return redirect()->route('home');
            // } else {
                $payment_status = [
                    "status" => "Success",
                    "method" => "tabby",
                ];
                Payment::create([
                    'payment_id' => $payment['id'],
                    'payment_method' => 'tabby',
                    'amount' => $payment['amount'],
                    'payment_details' => json_encode($payment),
                ]);
                $checkoutController = new CheckoutController;
                return $checkoutController->checkout_done(session()->get('combined_order_id'), json_encode($payment_status));
            // }
        } catch (\Exception $e) {
            flash(translate('Payment failed'))->error();
            return redirect()->route('home');
        }
    }

    public function cancel()
    {
        flash(translate('Payment is cancelled'))->error();
        return redirect()->route('home');
    }
}
