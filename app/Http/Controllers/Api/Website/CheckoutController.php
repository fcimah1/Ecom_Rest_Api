<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\DeliveryBoy;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Utility\NotificationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    protected $address;
    public function __construct()
    {
        $this->address = new AddressController;
    }
    public function getShippingCart()
    {
        try {
            if (Auth::check()) {
                $carts = Cart::where('user_id', Auth::id())->get();
                $address = $this->address->address(Auth::id());
                if ($carts && count($carts) > 0) {
                    return response()->json([
                        'status' => true,
                        'address' => $address,
                        'carts' => $carts,
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Your cart is empty',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'user not authenticated',
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error("error message" . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addShippingAddressToCart(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->id == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please login to add shipping address',
                ], 401);
            }
            if ($request->address_id == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please add shipping address',
                ], 404);
            } else {
                $this->address->setDefaultAddress($request->address_id);
                $carts = Cart::where('user_id', Auth::id())->get();
                foreach ($carts as $key => $cartItem) {
                    $cartItem->address_id = $request->address_id;
                    $cartItem->save();
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Shipping address added to cart',
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message" . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeDeliveryInfo()
    {
        try {
            $home_delivery = 'Home Delivery';
            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please login to add shipping info',
                ], 401);
            }

            $carts = Cart::where('user_id', Auth::id())->get();

            if (!$carts ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your cart is empty',
                ], 404);
            }

            $shipping_info = $this->address->address(Auth::id());

            if ($shipping_info == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please add shipping address',
                ], 404);
            } else {
                if (count($shipping_info) > 1) {
                    foreach ($shipping_info as $key => $address) {
                        if ($address->set_default == 1) {
                            $shipping_info = $address;
                            break;
                        }
                    }
                }
            }
            $total = $tax = $shipping = $subtotal = 0;

            if ($carts && count($carts) > 0) {
                foreach ($carts as $key => $cartItem) {
                    // $product = Product::find($cartItem['product_id']);
                    $tax += $cartItem['tax'] * $cartItem['quantity'];
                    $subtotal += $cartItem['price'] * $cartItem['quantity'];

                    if ($cartItem['shipping_type'] == 'home_delivery') {
                        $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                    }

                    if (isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {

                        foreach (json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                            if ($shipping_info['city'] == $shipping_region) {
                                $cartItem['shipping_cost'] = (double) $val;
                                break;
                            } else {
                                $cartItem['shipping_cost'] = 0;
                            }
                        }
                    } else {
                        $cartItem['shipping_cost'] = 0;
                    }
                    $shipping += $cartItem['shipping_cost'];
                    $cartItem->save();

                }
                $total = $subtotal + $tax + $shipping;
                return response()->json([
                    'status' => true,
                    'message' => 'Shipping info added to cart',
                    'data' => [
                        'total' => $total,
                        'tax' => $tax,
                        'shipping' => $shipping,
                        'subtotal' => $subtotal,
                        'shipping_info' => $shipping_info,
                        'cart' => $carts,
                        'home_delivery' => $home_delivery
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Your cart is empty',
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message" . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function storePaymentInfo(Request $request)
    {
        try {
            if ($request->payment_option != null) {
                (new OrderController)->store($request);
                session()->put('payment_type', 'cart_payment');

                if (session()->get('combined_order_id') != null) {
                    if ($request->payment_option == 'cash_on_delivery') {
                        return $this->order_confirmed();
                    } elseif ($request->payment_option == 'wallet') {
                        $user = Auth::user();
                        $combined_order = CombinedOrder::findOrFail(session()->get('combined_order_id'));
                        if ($user->balance >= $combined_order->grand_total) {
                            $user->balance -= $combined_order->grand_total;
                            $user->save();
                            return $this->checkout_done(session()->get('combined_order_id'), null);
                        }
                    } else {
                        $combined_order = CombinedOrder::findOrFail(session()->get('combined_order_id'));
                        foreach ($combined_order->orders as $order) {
                            $order->manual_payment = 1;
                            $order->save();
                        }
                        return $this->order_confirmed();
                    }
                }
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong',
                ], 500);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please select a payment method',
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message" . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //redirects to this method after a successfull checkout
    public function checkout_done($combined_order_id, $payment)
    {
        try {
            $combined_order = CombinedOrder::findOrFail($combined_order_id);

            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'paid';
                $order->payment_details = $payment;
                $order->save();

                // calculateCommissionAffilationClubPoint($order);
            }

            Session::put('combined_order_id', $combined_order_id);
            return $this->order_confirmed();
        } catch (\Exception $e) {
            Log::error("error message" . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function order_confirmed()
    {
        try {
            $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

            Cart::where('user_id', $combined_order->user_id)->delete();

            foreach ($combined_order->orders as $order) {
                NotificationUtility::sendOrderPlacedNotification($order);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order confirmed',
                'data' => [
                    'combined_order' => $combined_order,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message" . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
