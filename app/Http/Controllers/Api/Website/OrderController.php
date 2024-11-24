<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Services\TabbyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Str;

class OrderController extends Controller
{
    public function getOrders()
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $orders_in_deatails = [];
                $orders = Order::where('user_id', $user->id)->orderBy('date', 'desc')->get();
                if ($orders->isNotEmpty()) {
                    foreach ($orders as $order) {
                        // get oreder details
                        array_push($orders_in_deatails, [
                            'id' => $order->id,
                            'Code' => $order->code,
                            'amount' => $order->grand_total,
                            'date' => date('Y-m-d', $order->date),
                            'delivery_status' => $order->status,
                            'payment_status' => $order->delivery_status,
                        ]);
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Orders retrieved successfully',
                        'data' => $orders_in_deatails
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No orders found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function orderCount()
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $orderCount = Order::where('user_id', $user->id)->count();
                return response()->json([
                    'status' => true,
                    'message' => 'Order count retrieved successfully',
                    'data' => $orderCount . ' orders'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get order details
    public function getOrderDetails($id)
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $query = 'select orders.*,order_details.*,products.name as product_name,
                    products.unit_price as product_price, products.discount as product_discount from orders 
                    join order_details on orders.id = order_details.order_id 
                    join products on order_details.product_id = products.id 
                    where orders.user_id = :user_id and orders.id = :id';
                $orders_in_deatails = DB::select($query, ['user_id' => $user->id, 'id' => $id]);

                if ($orders_in_deatails) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Order details retrieved successfully',
                        'data' => $orders_in_deatails
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Order not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // delete order
    public function cancelOrder($id)
    {
        try {
            $order = Order::findOrFail($id);
            if ($order) {
                foreach ($order->orderDetails as $orderDetail) {
                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $orderDetail->variation)
                        ->first();

                    if ($product_stock) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }

                    $orderDetail->delete();
                }

                $order->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Order deleted successfully',
                    'data' => $order
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $carts = Cart::where('user_id', Auth::id())->get();
            if ($carts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            $address = Address::where('id', $carts[0]['address_id'])->first();
            $shippingAddress = [];
            if ($address != null) {
                $shippingAddress['name'] = Auth::user()->name;
                $shippingAddress['email'] = Auth::user()->email;
                $shippingAddress['address'] = $address->address;
                $shippingAddress['country'] = @$address->country->name;
                $shippingAddress['state'] = @$address->state->name;
                $shippingAddress['city'] = @$address->city->name;
                $shippingAddress['postal_code'] = @$address->postal_code;
                $shippingAddress['phone'] = $address->phone;
                if ($address->latitude || $address->longitude) {
                    $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
                }
            }

            $combined_order = new CombinedOrder;
            $combined_order->user_id = Auth::id();
            $combined_order->save();

            $combined_order->shipping_address = json_encode($shippingAddress);

            $seller_products = array();
            foreach ($carts as $cartItem) {
                $product_ids = array();
                $product = Product::find($cartItem['product_id']);
                if (isset($seller_products[$product->user_id])) {
                    $product_ids = $seller_products[$product->user_id];
                }
                array_push($product_ids, $cartItem);
                $seller_products[$product->user_id] = $product_ids;
            }

            foreach ($seller_products as $seller_product) {
                $order = new Order;
                $order->combined_order_id = $combined_order->id;
                $order->user_id = Auth::user()->id;
                $order->shipping_address = $combined_order->shipping_address;

                if ($request->payment_option == 'stripe')
                    $order->payment_type = 'Credit Card';
                else
                    $order->payment_type = $request->payment_option;
                $order->delivery_viewed = '0';
                $order->payment_status_viewed = '0';
                $order->code = date('Ymd-His') . rand(10, 99);
                $order->date = strtotime('now');
                $order->save();

                $subtotal = $tax = $shipping = $coupon_discount = 0;

                //Order Details Storing
                foreach ($seller_product as $cartItem) {
                    $product = Product::find($cartItem['product_id']);

                    $subtotal += $cartItem['price'] * $cartItem['quantity'];
                    $tax += $cartItem['tax'] * $cartItem['quantity'];
                    $coupon_discount += $cartItem['discount'];

                    $product_variation = $cartItem['variation'];

                    $product_stock = $product->stocks->where('variant', $product_variation)->first();
                    if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                        $message = "The requested quantity is not available for . $product->name";
                        $order->delete();
                        return response()->json([
                            'status' => false,
                            'message' => $message
                        ], 400);

                    } elseif ($product->digital != 1) {
                        $product_stock->qty -= $cartItem['quantity'];
                        $product_stock->save();
                    }

                    $order_detail = new OrderDetail;
                    $order_detail->order_id = $order->id;
                    $order_detail->seller_id = $product->user_id;
                    $order_detail->product_id = $product->id;
                    $order_detail->variation = $product_variation;
                    $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                    $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                    $order_detail->shipping_type = $cartItem['shipping_type'];
                    $order_detail->product_referral_code = $cartItem['product_referral_code'];
                    $order_detail->shipping_cost = $cartItem['shipping_cost'];
                    $order_detail->shipping_type = $request->payment_option;
                    $shipping += $order_detail->shipping_cost;

                    //End of storing shipping cost

                    $order_detail->quantity = $cartItem['quantity'];
                    $order_detail->save();

                    $product->num_of_sale += $cartItem['quantity'];
                    $product->save();

                    $order->seller_id = $product->user_id;

                    if ($product->added_by == 'seller' && $product->user->seller != null) {
                        $seller = $product->user->seller;
                        $seller->num_of_sale += $cartItem['quantity'];
                        $seller->save();
                    }
                }

                $order->grand_total = $subtotal + $tax + $shipping;

                $combined_order->grand_total += $order->grand_total;

                $order->save();
            }

            $combined_order->save();

            session()->put('combined_order_id', $combined_order->id);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}