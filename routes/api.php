<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Auth\PasswordResetRequestController;
use App\Http\Controllers\Api\PaymentGetway\TamaraController;
use App\Http\Controllers\Api\Website\AboutController;
use App\Http\Controllers\Api\Website\AddressController;
use App\Http\Controllers\Api\Website\BlogController;
use App\Http\Controllers\Api\Website\BrandController;
use App\Http\Controllers\Api\Website\CartController;
use App\Http\Controllers\Api\Website\CategoryController;
use App\Http\Controllers\Api\Website\CheckoutController;
use App\Http\Controllers\Api\Website\ContactController;
use App\Http\Controllers\Api\Website\CustomPageController;
use App\Http\Controllers\Api\Website\FooterController;
use App\Http\Controllers\Api\Website\HeaderController;
use App\Http\Controllers\Api\Website\HomeController;
use App\Http\Controllers\Api\Website\InvoiceReportController;
use App\Http\Controllers\Api\Website\OrderController;
use App\Http\Controllers\Api\Website\ProductController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\PaymentGetway\TabbyController;
use App\Http\Controllers\Api\Website\AdController;
use App\Http\Controllers\Api\Website\UserController;
use App\Http\Controllers\Api\Website\WishlistController;
use Illuminate\Support\Facades\Route;


Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);


Route::get('tamara-payment-types', [TamaraController::class,'payment_types'])->name('tamara.payment_types');
Route::post('tamara-payment-ckeck', [TamaraController::class,'checkoutavailablity'])->name('tamara.payment_types');
Route::post('tamara-chechout', [TamaraController::class,'createCheckoutSession'])->name('tamara.chechout');
Route::post('tamara-order-details', [TamaraController::class,'orderDetails'])->name('tamara.chechout');

//tabby 
Route::post('tabby/checkout', [TabbyController::class, 'initiateCheckout'])->name('tabby.checkout');
Route::get('tabby/checkout/{id}', [TabbyController::class, 'getCheckoutSession'])->name('tabby.checkout.show');
Route::get('tabby/payments', [TabbyController::class, 'getAllPayments'])->name('tabby.getAll-payments');
Route::post('tabby/payments/{payment_id}/captures', [TabbyController::class, 'capturePayment'])->name('tabby.capture-payment');
Route::get('tabby/payments/{payment_id}', [TabbyController::class, 'retrievePayment'])->name('tabby.retrieve-payment');
Route::put('tabby/payments/{payment_id}', [TabbyController::class, 'updatePayment'])->name('tabby.update-payment');
Route::post('tabby/payments/{payment_id}/refunds', [TabbyController::class, 'refundPayment'])->name('tabby.refund-payment');
Route::post('tabby/payments/{payment_id}/close', [TabbyController::class, 'closePayment'])->name('tabby.close-payment');
Route::get('/tabby/failure', [TabbyController::class, 'cancel'])->name('tabby.failure');
Route::get('/tabby/success', [TabbyController::class,'success'])->name('tabby.success');
Route::get('/tabby/cancel', [TabbyController::class,'cancel'])->name('tabby.cancel');


Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login-by-email', [AuthController::class,'login'])->name('loginByEmail');
    Route::post('login-by-phone', [AuthController::class,'login'])->name('loginByPhone');
    // Route::post('login-by-social', [AuthController::class,'loginSocial']);
    Route::post('register', [AuthController::class,'register'])->name('register');
    // Route::post('verify', [AuthController::class,'verify'])->name('verify');
    // Route::post('resend-verify', [AuthController::class, 'resendVerify'])->name('resendVerify');
    Route::post('logout', [AuthController::class,'logout'])->name('logout');
    Route::post('refresh', [AuthController::class,'refresh'])->name('refresh');
    Route::post('userProfile', [AuthController::class,'userProfile'])->name('userProfile');
    // Route::post('sendPasswordResetLink', [PasswordResetRequestController::class,'sendEmail']);
    // Route::post('resetPassword', [ChangePasswordController::class, 'passwordResetProcess']);
});

Route::get('auth/{provider}', [SocialAuthController::class,'redirectToProvider']);
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);


//user Dashboard
Route::group(['middleware' => 'api', 'prefix' => 'user'], function ($router) {
    Route::get('get-profile', [UserController::class,'userProfile'])->name('getProfile');
    Route::put('update-profile', [UserController::class,'updateProfile'])->name('updateProfile');
    Route::put('verfy-email', [UserController::class,'verfyMail'])->name('verfyEmail');
    Route::put('update-email', [UserController::class,'updateMail'])->name('updateEmail');
    Route::post('add-address', [AddressController::class,'addAddress'])->name('addAddress');
    Route::get('get-address', [AddressController::class,'getAddress'])->name('getAddress');
    Route::put('update-address/{id}', [AddressController::class,'updateAddress'])->name('updateAddress');
    Route::delete('delete-address/{id}', [AddressController::class,'deleteAddress'])->name('deleteAddress');
    Route::get('get-default-address', [AddressController::class,'getDefaultAddress'])->name('getDefaultAddress');
    Route::put('default-address/{id}', [AddressController::class,'setDefaultAddress'])->name('setDefaultAddress');
    Route::get('get-countries', [AddressController::class, 'getCountries'])->name('getCountries');
    Route::post('get-states', [AddressController::class, 'getStates'])->name('getStates');
    Route::post('get-cities', [AddressController::class, 'getCities'])->name('getCities');
    Route::get('cart-count', [CartController::class,'cartCount'])->name('cartCount');
    Route::get('wishlist-count', [WishlistController::class,'wishlistCount'])->name('wishlistCount');
    Route::get('order-count', [OrderController::class,'orderCount'])->name('orderCount');
    Route::get('get-orders', [OrderController::class,'getOrders'])->name('getOrders');
    Route::get('cancel-order/{id}', [OrderController::class,'cancelOrder'])->name('cancelOrder');
    Route::get('store-order', [OrderController::class,'store'])->name('storeOrder');
    Route::get('get-order-details/{id}', [OrderController::class,'getOrderDetails'])->name('getOrderDetails');
    Route::get('invoice-download/{id}', [InvoiceReportController::class,'invoiceDownload'])->name('invoiceDownload');
});

//website
Route::group([],function(){
    Route::get('home', [HomeController::class,'index'])->name('home');
    Route::get('about-us', [AboutController::class,'about'])->name('about');
    Route::get('contact', [ContactController::class,'contact'])->name('contact');
    Route::get('blog', [BlogController::class,'blog'])->name('blog');
    Route::get('custom-page', [CustomPageController::class,'customPage'])->name('customPage');
    Route::get('footer', [FooterController::class,'footer'])->name('footer');
});

//header
Route::group([],function(){
    Route::post('cart-count', [CartController::class,'cartCount'])->name('cartCount');
    Route::post('wishlist-count', [WishlistController::class,'wishlistCount'])->name('wishlistCount');
    Route::get('four-categories', [CategoryController::class,'LimitCategories'])->name('LimitCategories');
    Route::get('four-brands', [BrandController::class,'limitBrand'])->name('limitBrand');
    Route::get('products/search', [ProductController::class, 'searchProducts'])->name('searchProducts');
});

//announce bar
Route::group([],function(){
    Route::get('language', [HeaderController::class,'language'])->name('language');
    Route::get('currency', [HeaderController::class,'currency'])->name('currency');
    Route::get('country', [HeaderController::class, 'country'])->name('country');
    Route::get('announce', [ProductController::class,'announcement'])->name('announcement');
});

// Shop
Route::group([], function () {
    Route::get('products', [ProductController::class, 'index'])->name('products');
    Route::get('categories', [CategoryController::class, 'allCategories'])->name('categories');
    Route::get('featured-categories', [CategoryController::class, 'getFeaturedCategories'])->name('featuredCategories');
    Route::get('brands', [BrandController::class, 'allBrands'])->name('brands');
    Route::get('featured-brands', [BrandController::class, 'getFeaturedBrands'])->name('featuredBrands');
    Route::get('products/product-count/category', [ProductController::class, 'countProductsByCategory'])->name('countProductsByCategory');
    Route::get('products/product-count/brand', [ProductController::class, 'countProductsByBrand'])->name('countProductsByBrand');
    Route::get('products/popular/{category_id}', [ProductController::class, 'productPopular'])->name('productPopular');
    Route::get('products/category/{category}', [ProductController::class, 'getProductsByCategory'])->name('getProductsByCategory');
    Route::get('products/brand/{brand}', [ProductController::class, 'getProductsByBrand'])->name('getProductsByBrand');
    Route::get('products/search', [ProductController::class, 'searchProducts'])->name('searchProducts');
    Route::get('products/sort', [ProductController::class, 'sortBy'])->name('sortBy');
    Route::get('products/product-details/{product}', [ProductController::class, 'getProductByName'])->name('getProductByName');
    // Route::get('products/product-details/{id}', [ProductController::class, 'getProductById'])->name('getProductById');
});

//Blogs
Route::group(['prefix' => 'blogs'], function () {
    Route::get('/', [BlogController::class, 'index'])->name('blogs');
    Route::get('newest', [BlogController::class, 'getNewestBlogs'])->name('getNewestBlogs');
    Route::post('search', [BlogController::class, 'getBlogsBySearch'])->name('getBlogsBySearch');
    Route::get('{slug}', [BlogController::class, 'getBlog'])->name('getBlog');
    Route::get('category/{category}', [BlogController::class, 'getBlogsByCategory'])->name('getBlogsByCategory');
    Route::get('author/{author}', [BlogController::class, 'getBlogsByAuthor'])->name('getBlogsByAuthor');
    Route::get('date/{date}', [BlogController::class, 'getBlogsByDate'])->name('getBlogsByDate');
    Route::get('tag/{tag}', [BlogController::class, 'getBlogsByTag'])->name('getBlogsByTag');
    Route::get('Categories', [BlogController::class, 'getAllBlogsCategories'])->name('getAllBlogsCategories');
    Route::get('count/category', [BlogController::class, 'countBlogsOfCategory'])->name('countBlogsOfCategory');
    // blog details
    Route::get('blog-details/{slug}', [BlogController::class, 'getBlogDetails'])->name('getBlogDetails');
});

//Cart
Route::group([],function(){
    Route::post('cart/add', [CartController::class, 'addToCart'])->name('addToCart');
    Route::get('cart/aaa', [CartController::class, 'regenerateSession'])->name('aaa');
    Route::post('carts', [CartController::class, 'getCart'])->name('getCart');
    Route::delete('cart/remove', [CartController::class, 'removeFromCart'])->name('removeFromCart');
    Route::put('cart/update', [CartController::class, 'updateProductInCart'])->name('updateProductInCart');
});

//Index
Route::group([], function () {
    Route::get('limit-categories', [CategoryController::class, 'LimitCategories_3'])->name('LimitCategories_3');
    Route::get('limit-brands', [BrandController::class, 'limitBrand_3'])->name('limitBrand_3');
    Route::get('newest-products', [ProductController::class, 'newestProduct'])->name('newestProduct');
    Route::get('limit-sliders', [HomeController::class, 'limitSliders'])->name('limitSliders');
    Route::get('hot-offer-products', [ProductController::class, 'getHotOfferProducts'])->name('HotOfferProducts');
    Route::get('latest-products', [ProductController::class, 'getLatestProducts'])->name('latestProducts');
    Route::get('top-rated-products', [ProductController::class, 'topRatedProducts'])->name('topRatedProducts');
    Route::get('best-selling-products', [ProductController::class, 'getBestSellingProducts'])->name('bestSellingProducts');
    Route::get('featured-products', [ProductController::class, 'getFeaturedProducts'])->name('getFeaturedProducts');
});

// banners
Route::group([], function () {
    Route::get('banners', [AdController::class, 'index'])->name('getBanners');
    Route::get('banners/{type}', [AdController::class, 'getAdByType'])->name('getAdByType');
});

//Contact
Route::group([], function () {
    Route::get('contact-info', [ContactController::class, 'contactInfo'])->name('contactInfo');
    Route::get('location', [ContactController::class, 'googleMap'])->name('googleMap');
    Route::post('contact-form', [ContactController::class, 'contactForm'])->name('contactForm');
});

// checkout
Route::group(['middleware' => 'api', 'prefix' => 'checkout'], function ($router) {
    Route::get('/shipping-info', [CheckoutController::class, 'getShippingCart'])->name('getShippingCart');
    Route::post('delivery-info', [CheckoutController::class, 'addShippingAddressToCart'])->name('addShippingAddressToCart');
    Route::post('payment-select', [CheckoutController::class, 'storeDeliveryInfo'])->name('storeDeliveryInfo');
    Route::post('payment', [CheckoutController::class, 'storePaymentInfo'])->name('storePaymentInfo');
    Route::get('order-confirmed', [CheckoutController::class, 'orderConfirmed'])->name('orderConfirmed');
    Route::post('payment-verify', [CheckoutController::class, 'paymentVerify'])->name('paymentVerify');
});

//address
Route::group(['middleware' => 'api', 'prefix' => 'address'], function ($router){
    Route::get('get-address', [AddressController::class, 'getAddress'])->name('getAddress');
    Route::post('add-address', [AddressController::class, 'addAddress'])->name('addAddress');
    Route::put('update-address/{id}', [AddressController::class, 'updateAddress'])->name('updateAddress');
    Route::get('get-countries', [AddressController::class, 'getCountries'])->name('getCountries');
    Route::post('get-states', [AddressController::class, 'getStates'])->name('getStates');
    Route::post('get-cities', [AddressController::class, 'getCities'])->name('getCities');
});

//wishlist
Route::group([], function () {
    Route::post('get-wishlist', [WishlistController::class, 'getWishlist'])->name('getWishlist');
    Route::post('add-wishlist', [WishlistController::class, 'addWishlist'])->name('addWishlist');
    Route::delete('remove-wishlist/{id}', [WishlistController::class, 'removeWishlist'])->name('removeWishlist');
});

// faq
Route::group([], function () {
    Route::get('faq', [HomeController::class, 'faq']);
});

//footer
Route::group(['prefix' => 'footer'], function () {
    Route::get('policy-links', [FooterController::class, 'policyLinks'])->name('policyLinks');
    Route::get('social-links', [FooterController::class, 'socialLinks'])->name('socialLinks');
    Route::get('profile-links', [FooterController::class, 'profileLinks'])->name('profileLinks');
    Route::get('contact-info', [FooterController::class, 'contactInfo'])->name('contactInfo');
    Route::post('subscribe', [FooterController::class, 'subscribe'])->name('subscribe');
});


// Custom Page
Route::group([], function () {
    Route::get('/{slug}', [CustomPageController::class, 'getCustomPage'])->name('getCustomPage');
});


Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'Invalid Route'
    ], 404);
})->name('api.fallback');