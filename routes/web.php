<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerPageController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/tentang', fn () => redirect()->to(route('home').'#tentang'));
Route::get('/paket', [PublicPageController::class, 'packages'])->name('packages.index');
Route::get('/paket/{package:slug}', [PublicPageController::class, 'packageShow'])->name('packages.show');
Route::get('/destinasi', [PublicPageController::class, 'destinations'])->name('destinations.index');
Route::get('/destinasi/{destination:slug}', [PublicPageController::class, 'destinationShow'])->name('destinations.show');
Route::get('/jadwal', [PublicPageController::class, 'schedules'])->name('schedules.index');
Route::get('/review', [PublicPageController::class, 'reviews'])->name('reviews.index');
Route::get('/galeri', [PublicPageController::class, 'gallery'])->name('gallery.index');
Route::get('/kontak', [PublicPageController::class, 'contact'])->name('contact.index');
Route::post('/kontak', [PublicPageController::class, 'contactStore'])->middleware(['auth', 'role:customer'])->name('contact.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'registerCreate'])->name('register');
    Route::post('/register', [AuthController::class, 'registerStore'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('reservasi')->name('reservations.')->group(function () {
    Route::get('/buat', [PublicPageController::class, 'reservationCreate'])->middleware(['auth', 'role:customer'])->name('create');
    Route::post('/buat', [PublicPageController::class, 'reservationStore'])->middleware(['auth', 'role:customer'])->name('store');
    Route::get('/berhasil', [PublicPageController::class, 'reservationSuccess'])->name('success');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/reservasi', [CustomerPageController::class, 'reservations'])->name('reservations.index');
    Route::get('/reservasi/{reservation:code}', [CustomerPageController::class, 'reservationShow'])->name('reservations.show');
    Route::get('/reservasi/{reservation:code}/edit', [CustomerPageController::class, 'reservationEdit'])->name('reservations.edit');
    Route::put('/reservasi/{reservation:code}', [CustomerPageController::class, 'reservationUpdate'])->name('reservations.update');
    Route::delete('/reservasi/{reservation:code}', [CustomerPageController::class, 'reservationDestroy'])->name('reservations.destroy');
    Route::get('/reservasi/{reservation:code}/pembayaran', [CustomerPageController::class, 'payment'])->name('reservations.payment');
    Route::post('/reservasi/{reservation:code}/pembayaran', [CustomerPageController::class, 'paymentStore'])->name('reservations.payment.store');
    Route::get('/pesan', [CustomerPageController::class, 'messages'])->name('messages.index');
    Route::get('/pesan/{complaint}', [CustomerPageController::class, 'messageShow'])->name('messages.show');
    Route::get('/review', [CustomerPageController::class, 'reviews'])->name('reviews.index');
    Route::get('/review/buat', [CustomerPageController::class, 'reviewCreate'])->name('reviews.create');
    Route::post('/review', [CustomerPageController::class, 'reviewStore'])->name('reviews.store');
    Route::get('/profil', [CustomerPageController::class, 'profile'])->name('profile.index');
    Route::get('/profil/edit', [CustomerPageController::class, 'profileEdit'])->name('profile.edit');
    Route::put('/profil', [CustomerPageController::class, 'profileUpdate'])->name('profile.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/paket', [AdminPageController::class, 'packages'])->name('packages.index');
    Route::get('/paket/create', [AdminPageController::class, 'packageCreate'])->name('packages.create');
    Route::post('/paket', [AdminPageController::class, 'packageStore'])->name('packages.store');
    Route::get('/paket/{package:slug}', [AdminPageController::class, 'packageShow'])->name('packages.show');
    Route::get('/paket/{package:slug}/edit', [AdminPageController::class, 'packageEdit'])->name('packages.edit');
    Route::put('/paket/{package:slug}', [AdminPageController::class, 'packageUpdate'])->name('packages.update');
    Route::delete('/paket/{package:slug}', [AdminPageController::class, 'packageDestroy'])->name('packages.destroy');
    Route::get('/destinasi', [AdminPageController::class, 'destinations'])->name('destinations.index');
    Route::get('/destinasi/create', [AdminPageController::class, 'destinationCreate'])->name('destinations.create');
    Route::post('/destinasi', [AdminPageController::class, 'destinationStore'])->name('destinations.store');
    Route::get('/destinasi/{destination:slug}/edit', [AdminPageController::class, 'destinationEdit'])->name('destinations.edit');
    Route::put('/destinasi/{destination:slug}', [AdminPageController::class, 'destinationUpdate'])->name('destinations.update');
    Route::delete('/destinasi/{destination:slug}', [AdminPageController::class, 'destinationDestroy'])->name('destinations.destroy');
    Route::get('/jadwal', [AdminPageController::class, 'schedules'])->name('schedules.index');
    Route::get('/jadwal/create', [AdminPageController::class, 'scheduleCreate'])->name('schedules.create');
    Route::post('/jadwal', [AdminPageController::class, 'scheduleStore'])->name('schedules.store');
    Route::get('/jadwal/{schedule}/edit', [AdminPageController::class, 'scheduleEdit'])->name('schedules.edit');
    Route::put('/jadwal/{schedule}', [AdminPageController::class, 'scheduleUpdate'])->name('schedules.update');
    Route::delete('/jadwal/{schedule}', [AdminPageController::class, 'scheduleDestroy'])->name('schedules.destroy');
    Route::get('/reservasi', [AdminPageController::class, 'reservations'])->name('reservations.index');
    Route::get('/reservasi/{reservation:code}', [AdminPageController::class, 'reservationShow'])->name('reservations.show');
    Route::get('/pembayaran', [AdminPageController::class, 'payments'])->name('payments.index');
    Route::get('/pembayaran/{payment}', [AdminPageController::class, 'paymentShow'])->name('payments.show');
    Route::prefix('pelanggan')->name('customers.')->group(function () {
        Route::get('/', [AdminCustomerController::class, 'index'])->name('index');
        Route::get('/create', [AdminCustomerController::class, 'create'])->name('create');
        Route::post('/', [AdminCustomerController::class, 'store'])->name('store');
        Route::get('/{user}', [AdminCustomerController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminCustomerController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminCustomerController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminCustomerController::class, 'destroy'])->name('destroy');
    });
    Route::get('/review', [AdminPageController::class, 'reviews'])->name('reviews.index');
    Route::get('/review/{review}', [AdminPageController::class, 'reviewShow'])->name('reviews.show');
    Route::put('/review/{review}', [AdminPageController::class, 'reviewUpdate'])->name('reviews.update');
    Route::delete('/review/{review}', [AdminPageController::class, 'reviewDestroy'])->name('reviews.destroy');
    Route::get('/pesan-masuk', [AdminPageController::class, 'complaints'])->name('complaints.index');
    Route::get('/pesan-masuk/{complaint}', [AdminPageController::class, 'complaintShow'])->name('complaints.show');
    Route::put('/pesan-masuk/{complaint}', [AdminPageController::class, 'complaintUpdate'])->name('complaints.update');
    Route::delete('/pesan-masuk/{complaint}', [AdminPageController::class, 'complaintDestroy'])->name('complaints.destroy');
    Route::get('/faq', [AdminPageController::class, 'faqs'])->name('faqs.index');
    Route::get('/faq/create', [AdminPageController::class, 'faqCreate'])->name('faqs.create');
    Route::post('/faq', [AdminPageController::class, 'faqStore'])->name('faqs.store');
    Route::get('/faq/{faq}/edit', [AdminPageController::class, 'faqEdit'])->name('faqs.edit');
    Route::put('/faq/{faq}', [AdminPageController::class, 'faqUpdate'])->name('faqs.update');
    Route::delete('/faq/{faq}', [AdminPageController::class, 'faqDestroy'])->name('faqs.destroy');
    Route::get('/galeri', [AdminPageController::class, 'gallery'])->name('gallery.index');
    Route::get('/galeri/create', [AdminPageController::class, 'galleryCreate'])->name('gallery.create');
    Route::post('/galeri', [AdminPageController::class, 'galleryStore'])->name('gallery.store');
    Route::get('/galeri/{galleryItem}/edit', [AdminPageController::class, 'galleryEdit'])->name('gallery.edit');
    Route::put('/galeri/{galleryItem}', [AdminPageController::class, 'galleryUpdate'])->name('gallery.update');
    Route::delete('/galeri/{galleryItem}', [AdminPageController::class, 'galleryDestroy'])->name('gallery.destroy');
    Route::put('/reservasi/{reservation:code}/status', [AdminPageController::class, 'reservationUpdate'])->name('reservations.update');
    Route::put('/pembayaran/{payment}/verifikasi', [AdminPageController::class, 'paymentUpdate'])->name('payments.update');
    Route::middleware('master.admin')->prefix('pengguna')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });
});
