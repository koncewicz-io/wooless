<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/state', [CartController::class, 'state'])->name('cart.state');
Route::post('/cart/add-item', [CartController::class, 'addItem'])->name('cart.add-item');
Route::post('/cart/remove-item', [CartController::class, 'removeItem'])->name('cart.remove-item');
Route::post('/cart/update-item', [CartController::class, 'updateItem'])->name('cart.update-item');
Route::post('/cart/update-customer', [CartController::class, 'updateCustomer'])->name('cart.update-customer');
Route::post('/cart/select-shipping-rate', [CartController::class, 'selectShippingRate'])->name('cart.select-shipping-rate');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/shipping', [CheckoutController::class, 'shipping'])->name('checkout.shipping');
Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::get('/checkout/order/{order}', [CheckoutController::class, 'order'])->name('checkout.order');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

Route::get('/posts', [PostController::class, 'index'])->name('post.index');

Route::get('/products', [ProductController::class, 'index'])->name('product.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('product.show');

Route::get('/orders', [OrderController::class, 'index'])->name('order.index');

Route::get('/account', [AccountController::class, 'index'])->name('account.index');
Route::get('/account/state', [AccountController::class, 'state'])->name('account.state');

Route::get('/login', [LoginController::class, 'index'])->name('login.index');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

//Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
//Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::post('/logout', [LogoutController::class, 'store'])->name('logout.store');
