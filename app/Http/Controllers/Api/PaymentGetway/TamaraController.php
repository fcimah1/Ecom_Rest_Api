<?php

namespace App\Http\Controllers\Api\PaymentGetway;

use App\Http\Controllers\Api\Website\CheckoutController;
use App\Http\Controllers\Controller;
use App\Models\CombinedOrder;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\ProductTax;
use App\Services\TamaraService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class TamaraController extends Controller
{
    protected $tamaraService;
    protected $currency;
    protected $countryCode;
    protected $appName;
    protected $tamaraMode;

    public function __construct()
    {
        $tamaraService = new TamaraService;
        $this->tamaraService = $tamaraService;
        $this->appName = config('app.name');
        $this->tamaraMode = config(key: 'service.tamara.mode');
    }

    public function getOrderData(int $combined_order_id, float $amount)
    {
        $order = Order::where('combined_order_id', $combined_order_id)->first();
        $consumer = $order->user;
        $shipping_address = json_decode($order->shipping_address);
        $first_name = explode(' ', $shipping_address->name)[0];
        $last_name = explode(' ', $shipping_address->name)[1] ?? " ";
        $line_address = $shipping_address->address;
        $country_code = Country::where('name', $shipping_address->country)->first()->code;
        $city = $shipping_address->city;
        $phone = $shipping_address->phone;
        // $this->currency = Currency::where('c');
        // $this->countryCode = $order->country_code;
        $orderDetail = OrderDetail::where('order_id', $order->id)->get();
        $products = $orderDetail->map(function ($item) {
            // $photoIds = explode(',', $item->product->photos);
            // $urls = Upload::whereIn('id', $photoIds)->pluck('file_name')->toArray();
            // $product['images'] = array_map(function ($fileName) {
            //     return asset("/$fileName");
            // }, $urls);
            return [
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'reference_id' => $item->product->id,
                'type' => $item->product->category->name,
                'sku' => $item->product->barcode,
            ];
        });

        $items = $products->map(function ($item,float $amount) {
            return [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'reference_id' => $item['reference_id'],
                'type' => $item['type'],
                'sku' => $item['sku'],
                'total_amount' =>
                    [
                        'amount' => $amount,
                        'currency' => "SAR",
                    ]
            ];
        })->toArray();
        $productsIds = $products->pluck('reference_id')->toArray();
        $totalTaxes = ProductTax::whereIn('product_id', $productsIds)->sum('tax');
        $data = [
            'total_amount' =>
                [
                    'amount' => $amount,
                    'currency' => "SAR",
                ],
            'shipping_amount' =>
                [
                    'amount' => (string) number_format($orderDetail->pluck('shipping_cost')[0], 2, '.', ''),
                    'currency' => "SAR",
                ],
            'tax_amount' =>
                [
                    'amount' => (string) number_format($totalTaxes, 2, '.', ''),
                    'currency' => "SAR",
                ],
            'order_reference_id' =>$order->code,
            'order_number' =>$order->code,
            'discount' =>
                [
                    'name' => $order->discount_name ?? "",
                    'amount' =>
                        [
                            'amount' => ($order->discount_end_date >= strtotime(Carbon::now()) )? $order->discount : 0,
                            'currency' => "SAR",
                        ],
                ],
            'consumer' =>
                [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone_number' => $consumer->phone,
                    'email' => $consumer->email,
                ],
                'country_code' => "SA",
            'description' => 'Order #' . $order->code . ' from ' . $this->appName,
            'merchant_url' =>
                [
                    'success' => url('/tamara/success'),
                    'failure' => url('/tamara/failure'),
                    'cancel' => url('/tamara/cancel'),
                    'notification' => url('/tamara/notification'),
                ],
            'payment_type' => 'PAY_BY_INSTALMENTS',
            'instalments' => 3,
            // 'billing_address' =>
            //     [
            //         'first_name' => $billing_address['first_name'],
            //         'last_name' => $billing_address['last_name'],
            //         'line1' => $billing_address['line1'],
            //         'city' => $billing_address['city'],
            //         'country_code' => "SA",
            //         'phone_number' => $billing_address['phone'],
            //     ],
            'shipping_address' =>
                [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'line1' => $line_address,
                    'city' => $city,
                    'country_code' => $country_code,
                    'phone_number' => $phone,
                ],
                'locale' => 'en_US',
                'items' => $items,
        ];
        return $data;
    }

    public function getStaticOrderData()
    {
        $order = ['order_num' => '123', 'total' => 500, 'notes' => 'notes ', 'discount_name' => 'discount coupon', 'discount_amount' => 50, 'vat_amount' => 50, 'shipping_amount' => 20];
        $products[0] = ['id' => '123', 'type' => 'mobiles', 'name' => 'iphone', 'sku' => 'SA-12436', 'image_url' => 'https://example.com/image.png', 'quantity' => 1, 'unit_price' => 50, 'discount_amount' => 5, 'tax_amount' => 10, 'total' => 70];
        $products[1] = ['id' => '345', 'type' => 'labtops', 'name' => 'macbook air', 'sku' => 'SA-789', 'image_url' => 'https://example.com/image.png', 'quantity' => 1, 'unit_price' => 200, 'discount_amount' => 50, 'tax_amount' => 100, 'total' => 300];
        $consumer = ['first_name' => 'mohamed', 'last_name' => 'maree', 'phone' => '01234567890', 'email' => 'm7mdmaree26@gmail.com'];
        $billing_address = ['first_name' => 'mohamed', 'last_name' => 'maree', 'line1' => 'mehalla', 'city' => 'mehalla', 'phone' => '01234567890'];
        $shipping_address = ['first_name' => 'mohamed', 'last_name' => 'maree', 'line1' => 'mehalla', 'city' => 'mehalla', 'phone' => '01234567890'];
        $urls = ['success' => 'http://yoursite/success', 'failure' => 'http://yoursite/failure', 'cancel' => 'http://yoursite/cancel', 'notification' => 'http://yoursite/notification'];

        return [
            'order' => $order,
            'products' => $products,
            'consumer' => $consumer,
            'billing_address' => $billing_address,
            'shipping_address' => $shipping_address,
            'urls' => $urls,
        ];
    }

    public function createCheckoutSession()
    {
        try {
            $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
            $amount = round($combined_order->grand_total);
            $data = $this->getOrderData($combined_order->id, $amount);

            $response = $this->tamaraService->createCheckoutSession($data);
            if (isset($response['checkout_id']) && $response['checkout_id'] != '') {
                $redirect_url = $response['checkout_url'];
                $payment = $response;

                Session::put(['payment'=> $payment,'amount'=> $amount]);
                return redirect($redirect_url);                 // Redirect user to Tabby checkout
            } else {
                flash(translate('Payment is cancelled'))->error();
                return redirect()->route('tamara.cancel');
            }
        } catch (\Exception $e) {
            \Log::error('tabby ' . $e->getMessage());
            return redirect()->home();
        }
    }

    public function success()
    {
        try {
            $payment = Session::get('payment');
            $amount = Session::get('amount');
            // to check if operation done in test mode or live mode
            // if (isset($tamaraMode) && $tamaraMode != 'live') {
            //         flash(translate('Payment is work with test version'))->error();
            //         return redirect()->route('home');
            // } else {
            $payment_status = [
                "status" => "Success",
                "method" => "tamara",
            ];
            Payment::create([
                'payment_id' => $payment['checkout_id'],
                'payment_method' => 'tabby',
                'amount' => $amount,
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
