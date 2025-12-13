<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes (basic)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Forgot/Reset password
Route::get('/password/forgot', [AuthController::class, 'showForgot'])->name('password.request')->middleware('guest');
Route::post('/password/email', [AuthController::class, 'sendForgot'])->name('password.email')->middleware('guest','throttle:5,1');
Route::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/password/reset', [AuthController::class, 'reset'])->name('password.update')->middleware('guest');

// Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Inventory Routes
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

// Customer Routes
Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

// Supplier Routes
Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');
Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

// Service Routes
Route::get('/services', [ServiceController::class, 'index'])->name('services');
Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // POS (allow Cashier+)
    Route::get('/pos', function () {
        return view('pos');
    })->name('pos')->middleware('role:admin|manager|cashier');

    // Transactions listing (transaction history)
    Route::get('/transactions', function () {
    $sales = \App\Models\Sale::with('customer', 'user')
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    return view('transactions.index', compact('sales'));
    })->name('transactions')->middleware('role:admin|manager');

    // Voided Transactions list (Admin and Manager)
    Route::get('/transactions/voided', function () {
        $sales = \App\Models\Sale::with(['customer', 'user', 'voidedBy'])
            ->where('is_void', 1)
            ->orderBy('voided_at', 'desc')
            ->paginate(20);
        return view('transactions.voided', compact('sales'));
    })->name('transactions.voided')->middleware('role:admin|manager');

Route::get('/transactions/{id}', function ($id) {
    $sale = \App\Models\Sale::with(['saleItems.product', 'saleItems.service', 'customer', 'user', 'voidedBy', 'voidRequests', 'voidLogs'])->findOrFail($id);
    return view('transactions.show', compact('sale'));
    })->name('transactions.show')->middleware('role:admin|manager');

    // Reports (admin and manager)
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports')->middleware('role:admin|manager');
// API to fetch reports data in JSON
    // API to fetch reports data in JSON
    Route::get('/api/reports', [ReportsController::class, 'apiReports'])->middleware('role:admin|manager');
// PDF download
    // PDF download
    Route::get('/reports/pdf', [ReportsController::class, 'pdf'])->name('reports.pdf')->middleware('role:admin|manager');
// Printable view (not screenshot) — opens a print-friendly window
    // Printable view (not screenshot) — opens a print-friendly window
    Route::get('/reports/print', [ReportsController::class, 'print'])->name('reports.print')->middleware('role:admin|manager');

    // Settings
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings')->middleware('role:admin');

    // API Routes for POS
    Route::get('/api/products/search', function(\Illuminate\Http\Request $request) {
    $products = \App\Models\Product::where('name', 'like', '%' . $request->q . '%')
        ->orWhere('sku', 'like', '%' . $request->q . '%')
        ->where('stock', '>', 0)
        ->limit(10)
        ->get();
    return response()->json($products);
    })->middleware('role:admin|manager|cashier');

Route::get('/api/customers/search', function(\Illuminate\Http\Request $request) {
    $customers = \App\Models\Customer::where('first_name', 'like', '%' . $request->q . '%')
        ->orWhere('last_name', 'like', '%' . $request->q . '%')
        ->orWhere('phone', 'like', '%' . $request->q . '%')
        ->limit(10)
        ->get();
    return response()->json($customers);
    })->middleware('role:admin|manager|cashier');

Route::get('/api/services/search', function(\Illuminate\Http\Request $request) {
    $services = \App\Models\Service::where('name', 'like', '%' . $request->q . '%')
        ->orWhere('code', 'like', '%' . $request->q . '%')
        ->limit(10)
        ->get();
    return response()->json($services);
    })->middleware('role:admin|manager|cashier');

    Route::post('/api/sales', function(\Illuminate\Http\Request $request) {
    // Validate items and resolve service codes before transaction to avoid partial changes
    $items = $request->items ?? [];
    foreach ($items as $i => &$item) {
        if (isset($item['service_id'])) {
            // If service_id missing, resolve by code
            if (empty($item['service_id']) || !is_numeric($item['service_id']) || !\App\Models\Service::find($item['service_id'])) {
                if (!empty($item['code'])) {
                    $service = \App\Models\Service::where('code', $item['code'])->first();
                    if ($service) {
                        $item['service_id'] = $service->id;
                    }
                }
            }

            if (empty($item['service_id']) || !is_numeric($item['service_id']) || !\App\Models\Service::find($item['service_id'])) {
                return response()->json(['success' => false, 'message' => "Invalid or missing service_id for item index {$i}"], 422);
            }
        } else {
            if (empty($item['id']) || !is_numeric($item['id']) || !\App\Models\Product::find($item['id'])) {
                return response()->json(['success' => false, 'message' => "Invalid or missing product id for item index {$i}"], 422);
            }
        }

        // Validate quantity and price
        if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
            return response()->json(['success' => false, 'message' => "Invalid quantity for item index {$i}"], 422);
        }
        if (!isset($item['price']) || !is_numeric($item['price']) || $item['price'] < 0) {
            return response()->json(['success' => false, 'message' => "Invalid price for item index {$i}"], 422);
        }
    }

    try {
        \DB::beginTransaction();
        
        $total = collect($request->items)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });
        
        $change = $request->amount_paid - $total;
        
        $sale = \App\Models\Sale::create([
            'customer_id' => $request->customer_id,
            'user_id' => \Illuminate\Support\Facades\Auth::id() ?: 1,
            'total_amount' => $total,
            'discount' => 0,
            'amount_paid' => $request->amount_paid,
            'change_due' => $change,
            'payment_method' => $request->payment_method,
        ]);
        
        foreach ($items as $item) {
            // Basic validation: ensure items include either product id or service id
            if (isset($item['service_id'])) {
                // If service_id is empty or invalid, try to resolve by code if provided
                if (empty($item['service_id']) || !is_numeric($item['service_id']) || !\App\Models\Service::find($item['service_id'])) {
                    if (!empty($item['code'])) {
                        $service = \App\Models\Service::where('code', $item['code'])->first();
                        if ($service) {
                            $item['service_id'] = $service->id;
                        }
                    }
                }
                if (empty($item['service_id']) || !is_numeric($item['service_id']) || !\App\Models\Service::find($item['service_id'])) {
                    throw new \Exception('Invalid or missing service_id for one of the items');
                }
            } else {
                if (empty($item['id']) || !is_numeric($item['id']) || !\App\Models\Product::find($item['id'])) {
                    throw new \Exception('Invalid or missing product id for one of the items');
                }
            }
            // If item is a service, it will have service_id
            if (isset($item['service_id'])) {
                \App\Models\SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => null,
                    'service_id' => $item['service_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // No stock update or inventory log for services
            } else {
                // Ensure product exists and has enough stock
                $product = \App\Models\Product::find($item['id']);
                if (!$product) {
                    return response()->json(['success' => false, 'message' => 'Product not found: ' . ($item['id'] ?? 'unknown')], 422);
                }
                if ($product->stock < $item['quantity']) {
                    return response()->json(['success' => false, 'message' => 'Insufficient stock for product: ' . $product->name], 422);
                }
                \App\Models\SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'service_id' => null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
                
                // Update stock
                $product->stock -= $item['quantity'];
                $product->save();
                
                // Log inventory
                \App\Models\InventoryLog::create([
                    'product_id' => $item['id'],
                    'change' => -$item['quantity'],
                    'type' => 'sale',
                    'reference_id' => $sale->id,
                    'reference_type' => 'Sale',
                ]);
            }
        }
        
        \DB::commit();
        \Log::info('Sale created successfully', ['sale_id' => $sale->id]);
        return response()->json(['success' => true, 'sale_id' => $sale->id]);
    } catch (\Exception $e) {
        \DB::rollback();
        \Log::error('Sale creation failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
    })->middleware('role:admin|manager|cashier');

        // Removed admin SMTP test route (removed per user request)

    // User Management - Admin and Manager (manager can only create/edit cashiers)
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index')->middleware('role:admin|manager');
    Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create')->middleware('role:admin|manager');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store')->middleware('role:admin|manager');
    Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit')->middleware('role:admin|manager');
    Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update')->middleware('role:admin|manager');
    Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy')->middleware('role:admin');
    Route::post('/users/{user}/send-reset', [\App\Http\Controllers\UserController::class, 'sendReset'])->name('users.sendReset')->middleware('role:admin|manager','throttle:3,1');
});