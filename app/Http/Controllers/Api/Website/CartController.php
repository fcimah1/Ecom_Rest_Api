<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\TempGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    
    public function getCart(Request $request)
    {
        try {
            if (Auth::check()) {
                $user_id = Auth::id();
                if ($request->temp_user_id) {
                    Cart::where('temp_user_id', $request->temp_user_id)
                        ->update(
                            [
                                'user_id' => $user_id,
                                'temp_user_id' => null
                            ]
                        );
                    TempGuest::where('temp_user_id', $request->temp_user_id)->delete();
                }
                $carts = Cart::where('user_id', $user_id)->get();
            } else {

                $carts = ($request->temp_user_id != null) ? Cart::where('temp_user_id', $request->temp_user_id)->get() : [];
            }
            if (count($carts) > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'carts' => $carts
                ])->setStatusCode(200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty'
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    // method for add to cart
    public function addToCart(Request $request)
    {
        try {
            $product = Product::find($request->id);
            $temp_user_id = null;
            $tempExists = $temp_user_id != null ?  $temp_user_id:  null;
            $carts = [];
            $data = [];
            // check user is logged in or not
            if (Auth::check()) {
                $user_id = Auth::user()->id;
                $data['user_id'] = $user_id;
                $carts = Cart::where('user_id', $user_id)->get();
            } else {
                // Handle guest user and temp_user_id
                if ($request->temp_user_id) {
                    $temp_user = TempGuest::where('temp_user_id', $request->temp_user_id)->first();
                    if ($temp_user) {
                        // Retrieve the existing temp_user_id from database
                        $temp_user_id = $temp_user->temp_user_id;
                    } 
                } else {
                    // Generate a new temp_user_id and store it in the session
                    $temp_user_id = bin2hex(random_bytes(10));
                    TempGuest::create([
                        'temp_user_id' => $temp_user_id,
                        'expires_at' => now()->addDay()->toDateTimeString(),
                    ]);
                }
    
                // Use temp_user_id for guest users
                $data['temp_user_id'] = $temp_user_id;
                $carts = Cart::where('temp_user_id', $temp_user_id)->get();
            }

            $data['product_id'] = $product->id;
            $data['owner_id'] = $product->user_id;

            $str = '';
            $tax = 0;
            // check product is auction product or not
            if ($product->auction_product == 0) {
                if ($product->digital != 1 && $request->quantity < $product->min_qty) {
                    return response()->json([
                        'status' => false,
                        'message' => "Minimum quantity is $product->min_qty",
                    ]);
                }

                //check the color is available or not
                if ($request->has('color')) {
                    $str = $request['color'];
                }

                if ($product->digital != 1) {
                    //Gets all the choice values of customer and generate a string like Black-S-Cotton
                    foreach (json_decode(Product::find($request->id)->choice_options) as $key => $choice) {
                        if ($str != null) {
                            $str .= '-' . str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                        } else {
                            $str .= str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                        }
                    }
                }

                $data['variation'] = $str;

                //check for stock of product
                $product_stock = $product->stocks->where('variant', $str)->first();

                $price = @$product_stock->price;

                if ($product->wholesale_product) {
                    $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                    if ($wholesalePrice) {
                        $price = $wholesalePrice->price;
                    }
                }

                $quantity = @$product_stock->qty;

                if ($quantity < $request['quantity']) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Out of stock',
                    ])->setStatusCode(400);
                }

                // calculate discount for products
                $discount_applicable = false;

                if ($product->discount_start_date == null) {
                    $discount_applicable = true;
                } elseif (
                    strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                    strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
                ) {
                    $discount_applicable = true;
                }

                if ($discount_applicable) {
                    if ($product->discount_type == 'percent') {
                        $price -= ($price * $product->discount) / 100;
                    } elseif ($product->discount_type == 'amount') {
                        $price -= $product->discount;
                    }
                }

                // calculate taxes for products
                foreach ($product->taxes as $product_tax) {
                    if ($product_tax->tax_type == 'percent') {
                        $tax += ($price * $product_tax->tax) / 100;
                    } elseif ($product_tax->tax_type == 'amount') {
                        $tax += $product_tax->tax;
                    }
                }

                $data['quantity'] = $request['quantity'];
                $data['price'] = $price;
                $data['tax'] = $tax;
                $data['shipping_cost'] = 0;
                $data['product_referral_code'] = null;
                $data['cash_on_delivery'] = $product->cash_on_delivery;
                $data['digital'] = $product->digital;

                if ($request['quantity'] == null) {
                    $data['quantity'] = 1;
                }
                // check if already exists in cart
                if (Cookie::has('referred_product_id') && Cookie::get('referred_product_id') == $product->id) {
                    $data['product_referral_code'] = Cookie::get('product_referral_code');
                }

                if ($carts && count($carts) > 0) {
                    $foundInCart = false;

                    foreach ($carts as $key => $cartItem) {
                        $product = Product::where('id', $cartItem['product_id'])->first();
                        if ($product->auction_product == 1) {
                            return response()->json([
                                'status' => false,
                                'cart_count' => count($carts),
                                'message' => 'This product is in auction. You can\'t add it to cart',
                            ])->setStatusCode(400);
                        }

                        if ($cartItem['product_id'] == $request->id) {
                            $product_stock = $product->stocks->where('variant', $str)->first();
                            $quantity = $product_stock->qty;
                            if ($quantity < $cartItem['quantity'] + $request['quantity']) {
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Only ' . $quantity . ' quantity available in stock',
                                ]);
                            }
                            if (($str != null && $cartItem['variation'] == $str) || $str == null) {
                                $foundInCart = true;

                                $cartItem['quantity'] += $request['quantity'];

                                if ($product->wholesale_product) {
                                    $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                                    if ($wholesalePrice) {
                                        $price = $wholesalePrice->price;
                                    }
                                }

                                $cartItem['price'] = $price;

                                $cartItem->save();
                            }
                        }
                    }
                    if (!$foundInCart) {
                        Cart::create($data);
                    }
                } else {
                    Cart::create($data);
                }

                if (Auth::user() != null) {
                    $user_id = Auth::user()->id;
                    $carts = Cart::where('user_id', $user_id)->get();
                } else {
                    $carts = Cart::where('temp_user_id', $temp_user_id)->get();
                }
                $product = Product::find($data['product_id']);
                return response()->json([
                    'status' => true,
                    'product name' => $product->name,
                    'cart_count' => count($carts),
                    'temp_user_id' => $tempExists,
                    'message' => 'Product added to cart successfully',
                ])->setStatusCode(200);
            } else {
                $price = $product->bids->max('amount');

                foreach ($product->taxes as $product_tax) {
                    if ($product_tax->tax_type == 'percent') {
                        $tax += ($price * $product_tax->tax) / 100;
                    } elseif ($product_tax->tax_type == 'amount') {
                        $tax += $product_tax->tax;
                    }
                }

                $data['quantity'] = 1;
                $data['price'] = $price;
                $data['tax'] = $tax;
                $data['shipping_cost'] = 0;
                $data['product_referral_code'] = null;
                $data['cash_on_delivery'] = $product->cash_on_delivery;
                $data['digital'] = $product->digital;

                if (count($carts) == 0) {
                    Cart::create($data);
                }
                if (Auth::user() != null) {
                    $user_id = Auth::user()->id;
                    $carts = Cart::where('user_id', $user_id)->get();
                } else {
                    $carts = Cart::where('temp_user_id', $temp_user_id)->get();
                }

                return response()->json([
                    'status' => true,
                    'product name' => $product->name,
                    'cart_count' => count($carts),
                    'temp_user_id' => $tempExists,
                    'message' => 'Product added to cart successfully',
                ])->setStatusCode(200);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        try {
            Cart::destroy($request->id);
            if (Auth::user() != null) {
                $user_id = Auth::user()->id;
                $carts = Cart::where('user_id', $user_id)->get();
            } else {
                $carts = ($request->temp_user_id != null) ? Cart::where('temp_user_id', $request->temp_user_id)->get() : null;
            }
            return response()->json([
                'status' => true,
                'cart_count' => count($carts),
                'message' => 'Product removed from cart successfully',
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    //clears the cart
    public function clearCart(Request $request)
    {
        try {
            if (Auth::user() != null) {
                $user_id = Auth::user()->id;
                Cart::where('user_id', $user_id)->delete();
            } else {
                ($request->temp_user_id != null) ? Cart::where('temp_user_id', $request->temp_user_id)->delete() : null;
            }
            return response()->json([
                'status' => true,
                'message' => 'Cart cleared successfully',
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    //updated the quantity for a cart item
    public function updateProductInCart(Request $request)
    {
        try {
            $cartItem = Cart::findOrFail($request->id);
            if (!$cartItem)
                return response()->json([
                    'status' => false,
                    'message' => 'Cart item not found',
                ])->setStatusCode(404);

            if ($cartItem['id'] == $request->id) {
                $product = Product::find($cartItem['product_id']);
                $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();
                $quantity = $product_stock->qty;
                $price = $product_stock->price;

                if ($quantity >= $request->quantity) {
                    if ($request->quantity >= $product->min_qty) {
                        $cartItem['quantity'] = $request->quantity;
                    }
                }

                if ($product->wholesale_product) {
                    $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                    if ($wholesalePrice) {
                        $price = $wholesalePrice->price;
                    }
                }

                $cartItem->save();
            }

            if (Auth::user() != null) {
                $user_id = Auth::user()->id;
                $carts = Cart::where('user_id', $user_id)->get();
            } else {
                ($request->temp_user_id != null) ? $carts = Cart::where('temp_user_id', $request->temp_user_id)->get() : null;
            }
            if(count($carts) > 0)
            return response()->json([
                'status' => true,
                'cart_count' => count($carts),
                'message' => 'Cart updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function cartCount(Request $request)
    {
        try {
            $carts = null;
            if (Auth::check()) {
                $user_id = Auth::id();
                $carts = Cart::where('user_id', $user_id)->get();
            }else{
                ($request->temp_user_id != null) ? $carts = Cart::where('temp_user_id', $request->temp_user_id)->get() : null;
            }
            if($carts && count($carts) > 0) {
                return response()->json([
                    'status' => true,
                    'cart_count' => count($carts),
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No products in cart',
                ], 401);
            }

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

}
