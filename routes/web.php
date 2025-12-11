<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ServiceController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
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

// POS
Route::get('/pos', function () {
    return view('pos');
})->name('pos');

// Reports
Route::get('/reports', function () {
    return view('reports');
})->name('reports');

// Settings
Route::get('/settings', function () {
    return view('settings');
})->name('settings');

// API Routes for POS
Route::get('/api/products/search', function(\Illuminate\Http\Request $request) {
    $products = \App\Models\Product::where('name', 'like', '%' . $request->q . '%')
        ->orWhere('sku', 'like', '%' . $request->q . '%')
        ->where('stock', '>', 0)
        ->limit(10)
        ->get();
    return response()->json($products);
});

Route::get('/api/customers/search', function(\Illuminate\Http\Request $request) {
    $customers = \App\Models\Customer::where('first_name', 'like', '%' . $request->q . '%')
        ->orWhere('last_name', 'like', '%' . $request->q . '%')
        ->orWhere('phone', 'like', '%' . $request->q . '%')
        ->limit(10)
        ->get();
    return response()->json($customers);
});

Route::post('/api/sales', function(\Illuminate\Http\Request $request) {
    try {
        \DB::beginTransaction();
        
        $total = collect($request->items)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });
        
        $change = $request->amount_paid - $total;
        
        $sale = \App\Models\Sale::create([
            'customer_id' => $request->customer_id,
            'user_id' => 1, // TODO: Use actual logged-in user
            'total_amount' => $total,
            'discount' => 0,
            'amount_paid' => $request->amount_paid,
            'change_due' => $change,
            'payment_method' => $request->payment_method,
        ]);
        
        foreach ($request->items as $item) {
            \App\Models\SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
            
            // Update stock
            $product = \App\Models\Product::find($item['id']);
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
        
        \DB::commit();
        
        return response()->json(['success' => true, 'sale_id' => $sale->id]);
    } catch (\Exception $e) {
        \DB::rollback();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});