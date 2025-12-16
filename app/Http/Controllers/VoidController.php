<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\VoidRequest;
use App\Models\VoidLog;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;

class VoidController extends Controller
{
    // Cashier requests a void
    public function request(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $sale = Sale::with('saleItems')->findOrFail($id);
        if ($sale->is_void) {
            return back()->withErrors(['void' => 'This transaction is already voided.']);
        }

        // If requester is cashier, ensure they own the sale
        if (Auth::user() && Auth::user()->isCashier() && $sale->user_id !== Auth::id()) {
            return back()->withErrors(['void' => 'You may only request voids for transactions you handled.']);
        }

        // Prevent duplicate pending requests
        $existing = $sale->voidRequests()->where('status', 'pending')->first();
        if ($existing) {
            return back()->withErrors(['void' => 'There is already a pending void request for this transaction.']);
        }

        $vr = VoidRequest::create([
            'sale_id' => $sale->id,
            'requested_by' => Auth::id(),
            'requested_at' => now(),
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        // log
        VoidLog::create([
            'sale_id' => $sale->id,
            'action' => 'requested',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
            'note' => $request->input('reason'),
        ]);

        return back()->with('success', 'Void request submitted for approval.');
    }

    // Manager/Admin approves the request and performs void
    public function approve(Request $request, $id)
    {
        $request->validate(['void_reason' => 'required|string|max:255']);

        $sale = Sale::with('saleItems')->findOrFail($id);
        if ($sale->is_void) {
            return back()->withErrors(['void' => 'This transaction is already voided.']);
        }

        $vr = $sale->voidRequests()->where('status', 'pending')->latest()->first();
        if (!$vr) {
            // allow manager/admin to directly void even without a pending request
            $vr = VoidRequest::create([
                'sale_id' => $sale->id,
                'requested_by' => Auth::id(),
                'requested_at' => now(),
                'reason' => $request->input('void_reason') ?? 'Manager initiated void',
                'status' => 'pending',
            ]);
        }

        $vr->status = 'approved';
        $vr->approved_by = Auth::id();
        $vr->approved_at = now();
        $vr->save();

        // perform void: restore stock for product items, mark sale as void
        foreach ($sale->saleItems as $item) {
            if ($item->product_id) {
                $product = $item->product;
                if ($product) {
                    $product->stock = $product->stock + $item->quantity;
                    $product->save();

                    InventoryLog::create([
                        'product_id' => $product->id,
                        'change' => $item->quantity,
                        'type' => 'void',
                        'reference_id' => $sale->id,
                        'reference_type' => 'Sale',
                    ]);
                }
            }
        }

        $sale->is_void = true;
        $sale->voided_by = Auth::id();
        $sale->voided_at = now();
        $sale->void_reason = $request->input('void_reason') ?? ('Manager approved: ' . ($vr->reason ?? 'No reason provided'));
        $sale->save();

        VoidLog::create([
            'sale_id' => $sale->id,
            'action' => 'approved',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
            'note' => $sale->void_reason,
        ]);

        return back()->with('success', 'Void request approved and transaction voided.');
    }

    // Manager/Admin rejects the void request
    public function reject(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:1000']);

        $sale = Sale::findOrFail($id);
        $vr = $sale->voidRequests()->where('status', 'pending')->latest()->first();
        if (!$vr) {
            return back()->withErrors(['void' => 'No pending void request found for this transaction.']);
        }

        $vr->status = 'rejected';
        $vr->approved_by = Auth::id();
        $vr->approved_at = now();
        $vr->save();

        VoidLog::create([
            'sale_id' => $sale->id,
            'action' => 'rejected',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
            'note' => $request->input('note'),
        ]);

        return back()->with('success', 'Void request rejected.');
    }

    // Restore a previously voided transaction (Admin/Manager only)
    public function restore(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:255']);

        $sale = Sale::with('saleItems')->findOrFail($id);
        if (!$sale->is_void) {
            return back()->withErrors(['void' => 'This transaction is not voided.']);
        }

        // Check inventory availability to re-apply the sale
        $insufficient = [];
        foreach ($sale->saleItems as $item) {
            if ($item->product_id) {
                $product = $item->product;
                if ($product && $product->stock < $item->quantity) {
                    $insufficient[] = $product->name . ' (need ' . $item->quantity . ', have ' . $product->stock . ')';
                }
            }
        }

        if (count($insufficient) > 0) {
            return back()->withErrors(['void' => 'Cannot restore transaction. Insufficient stock: ' . implode(', ', $insufficient)]);
        }

        // Deduct stock back because we're restoring the sale
        foreach ($sale->saleItems as $item) {
            if ($item->product_id) {
                $product = $item->product;
                if ($product) {
                    $product->stock = max(0, $product->stock - $item->quantity);
                    $product->save();

                    InventoryLog::create([
                        'product_id' => $product->id,
                        'change' => -$item->quantity,
                        'type' => 'restore',
                        'reference_id' => $sale->id,
                        'reference_type' => 'Sale',
                    ]);
                }
            }
        }

        $sale->is_void = false;
        $sale->voided_by = null;
        $sale->voided_at = null;
        $sale->void_reason = null;
        $sale->save();

        VoidLog::create([
            'sale_id' => $sale->id,
            'action' => 'restored',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
            'note' => $request->input('note'),
        ]);

        return back()->with('success', 'Voided transaction restored successfully.');
    }
}
