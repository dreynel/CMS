# ISAT U Coffee Shop POS & Online Reservation System
## PHP Laravel & MySQL Architecture Blueprint

This document outlines the seamless migration roadmap for transforming the current HTML5 / TailwindCSS / Vanilla JS frontend into a full **PHP Laravel 11/12** enterprise web application backed by **MySQL**.

---

## 1. Directory Structure Mapping

When creating the Laravel project (e.g. `composer create-project laravel/laravel isatu-pos`), map the existing files as follows:

```
laravel-root/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── POSController.php            # Handles POS terminal, cart charging & receipts
│   │   │   ├── ReservationController.php    # Handles customer table bookings & QR token generation
│   │   │   ├── QRScannerController.php      # Validates scanned QR passes & check-in
│   │   │   ├── KitchenController.php        # KDS live order queue & status transitions
│   │   │   ├── AdminDashboardController.php # Sales analytics, inventory adjustments
│   │   │   └── Api/
│   │   │       ├── OrderApiController.php   # REST API for mobile POS / offline sync
│   │   │       └── TableApiController.php   # Real-time table occupancy API
│   │   └── Requests/
│   │       ├── StoreReservationRequest.php
│   │       └── CheckoutOrderRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── ProductSize.php
│   │   ├── DiningTable.php
│   │   ├── Reservation.php
│   │   ├── ReservationItem.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── Payment.php
│   └── Events/
│       ├── OrderCreatedEvent.php            # Broadcasts to Kitchen KDS via Laravel Reverb
│       └── ReservationCheckedInEvent.php    # Updates table status in real-time
├── database/
│   ├── migrations/                          # Migrations generated from database/schema.sql
│   └── seeders/                             # Seeders generated from database/seeder.sql
├── public/
│   ├── assets/
│   │   ├── css/style.css
│   │   ├── js/ (pos.js, reservation.js, scanner.js, kitchen.js, admin.js)
│   │   └── images/isatu.png
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php                # Master layout with ISAT U theme & Tailwind
│       │   ├── pos.blade.php                # POS full-screen layout
│       │   └── kds.blade.php                # Dark mode KDS barista screen layout
│       ├── customer/
│       │   ├── index.blade.php              # From index.html
│       │   └── qr-ticket.blade.php          # QR ticket pass view
│       ├── pos/
│       │   ├── terminal.blade.php           # From pos.html
│       │   └── receipt-print.blade.php      # Printable thermal receipt
│       ├── scanner/
│       │   └── validator.blade.php          # From scanner.html
│       ├── kitchen/
│       │   └── board.blade.php              # From kitchen.html
│       └── admin/
│           ├── dashboard.blade.php          # From admin.html
│           ├── reservations.blade.php
│           └── inventory.blade.php
└── routes/
    ├── web.php
    ├── api.php
    └── channels.php
```

---

## 2. Eloquent Model Relationships

### `Reservation.php`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_number', 'qr_token', 'user_id', 'table_id',
        'customer_name', 'customer_type', 'id_number', 'phone',
        'email', 'reservation_date', 'time_slot', 'guest_count',
        'special_notes', 'total_amount', 'payment_status', 'status',
        'checked_in_at'
    ];

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function items()
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
```

### `Order.php`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'reservation_id', 'cashier_id', 'order_type',
        'table_number', 'customer_name', 'subtotal', 'discount_type',
        'discount_amount', 'vat_amount', 'net_total', 'payment_method',
        'payment_reference', 'status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
```

---

## 3. Laravel Routes (`routes/web.php`)

```php
use App\Http\Controllers\POSController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\QRScannerController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

// Customer Portal & Reservation
Route::get('/', [ReservationController::class, 'index'])->name('portal.home');
Route::post('/reserve', [ReservationController::class, 'store'])->name('portal.reserve');
Route::get('/pass/{token}', [ReservationController::class, 'showPass'])->name('portal.pass');

// Staff POS Terminal
Route::prefix('pos')->group(function () {
    Route::get('/', [POSController::class, 'index'])->name('pos.terminal');
    Route::post('/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::get('/receipt/{orderId}', [POSController::class, 'receipt'])->name('pos.receipt');
});

// Staff QR Scanner & Check-in
Route::prefix('scanner')->group(function () {
    Route::get('/', [QRScannerController::class, 'index'])->name('scanner.view');
    Route::post('/verify', [QRScannerController::class, 'verifyToken'])->name('scanner.verify');
    Route::post('/checkin/{id}', [QRScannerController::class, 'checkIn'])->name('scanner.checkin');
});

// Kitchen Display System (KDS)
Route::prefix('kitchen')->group(function () {
    Route::get('/', [KitchenController::class, 'index'])->name('kitchen.kds');
    Route::patch('/orders/{id}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.status');
});

// Admin & Manager Dashboard
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('/products/{id}/stock', [AdminDashboardController::class, 'updateStock'])->name('admin.stock');
    Route::get('/reservations', [AdminDashboardController::class, 'reservationsList'])->name('admin.reservations');
});
```

---

## 4. Real-time Kitchen Dispatch via Laravel Reverb

When a guest checks in or a cashier charges an order, dispatch an event:

```php
event(new OrderCreatedEvent($order));
```

In `KitchenController.php` / Blade views, listen via Laravel Echo:

```js
window.Echo.channel('kitchen-orders')
    .listen('OrderCreatedEvent', (e) => {
        // Play chime sound and insert new ticket into the Pending column
        renderNewTicket(e.order);
        playAudioChime();
    });
```
