<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Service;
use App\Models\SaleItem;
use Carbon\Carbon;

class ReportsController extends Controller
{
    protected function parsePeriod(Request $request): array
    {
        $period = $request->get('period', 'today');
        $from = null;
        $to = null;
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $from = $now->copy()->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'this_week':
                $from = $now->copy()->startOfWeek();
                $to = $now->copy()->endOfWeek();
                break;
            case 'this_month':
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $from = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $to = $now->copy()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'custom':
                $from = $request->get('date_from') ? Carbon::parse($request->get('date_from'))->startOfDay() : Carbon::now()->startOfDay();
                $to = $request->get('date_to') ? Carbon::parse($request->get('date_to'))->endOfDay() : Carbon::now()->endOfDay();
                break;
            default:
                $from = $now->copy()->startOfDay();
                $to = $now->copy()->endOfDay();
        }

        return ['from' => $from, 'to' => $to, 'period' => $period];
    }

    public function index(Request $request)
    {
        $reportData = [];
        if ($request->has('report_type')) {
            $apiResponse = $this->apiReports($request);
            $json = json_decode($apiResponse->getContent(), true);
            $reportData = $json['reportData'] ?? [];
        }
        return view('reports', compact('reportData'));
    }

    public function apiReports(Request $request)
    {
        $type = $request->get('report_type', 'sales');
        $period = $this->parsePeriod($request);
        $from = $period['from'];
        $to = $period['to'];

        $reportData = [];
        if ($type === 'sales') {
            $sales = Sale::with('customer', 'saleItems')
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('created_at', 'desc')
                ->get();
            $reportData['total_transactions'] = $sales->count();
            $reportData['total_sales'] = $sales->sum('total_amount');
            $reportData['average_sale'] = $reportData['total_transactions'] ? $reportData['total_sales'] / $reportData['total_transactions'] : 0;
            $reportData['sales'] = $sales->map(function ($s) {
                return [
                    'id' => $s->id,
                    'created_at' => $s->created_at->toDateTimeString(),
                    'customer' => $s->customer ? $s->customer->first_name . ' ' . $s->customer->last_name : 'Walk-in',
                    'items' => $s->saleItems->sum('quantity'),
                    'total_amount' => $s->total_amount,
                    'payment_method' => $s->payment_method,
                ];
            })->toArray();
        } elseif ($type === 'inventory') {
            $products = Product::with('category')->get();
            $reportData['products'] = $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'category' => $p->category ? $p->category->name : null,
                    'stock' => $p->stock,
                    'cost_price' => $p->cost_price,
                    'selling_price' => $p->selling_price,
                    'reorder_level' => $p->reorder_level,
                ];
            })->toArray();
            $reportData['total_stock_value'] = $products->sum(function ($p) {
                return $p->stock * $p->cost_price;
            });
        } elseif ($type === 'customers') {
            $customers = Customer::with('sales')->get();
            $reportData['customers'] = $customers->map(function ($c) {
                $salesCount = $c->sales->count();
                $totalSpent = $c->sales->sum('total_amount');
                $lastPurchase = $c->sales->sortByDesc('created_at')->first();
                return [
                    'id' => $c->id,
                    'first_name' => $c->first_name,
                    'last_name' => $c->last_name,
                    'phone' => $c->phone,
                    'sales_count' => $salesCount,
                    'total_spent' => $totalSpent,
                    'last_purchase' => $lastPurchase ? $lastPurchase->created_at->format('M d, Y') : null,
                ];
            })->toArray();
        } elseif ($type === 'services') {
            $services = Service::all();
            $serviceData = [];
            foreach ($services as $service) {
                $count = SaleItem::where('service_id', $service->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->sum('quantity');
                $total = SaleItem::where('service_id', $service->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->sum('subtotal');
                $serviceData[] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'code' => $service->code,
                    'quantity' => $count,
                    'total' => $total,
                ];
            }
            $reportData['services'] = $serviceData;
        }

        return response()->json(['success' => true, 'reportType' => $type, 'reportData' => $reportData, 'period' => $period['period']]);
    }

    public function pdf(Request $request)
    {
        $type = $request->get('report_type', 'sales');
        $period = $this->parsePeriod($request);
        $from = $period['from'];
        $to = $period['to'];

        // reuse apiReports logic by calling that method
        $apiResponse = $this->apiReports($request);
        $json = json_decode($apiResponse->getContent(), true);
        $reportData = $json['reportData'] ?? [];

        $html = view('reports.pdf', ['reportType' => $type, 'reportData' => $reportData, 'period' => $period['period'], 'from' => $from, 'to' => $to])->render();

        // Try Barryvdharryvdh/laravel-dompdf first (Facade), then fallback to Dompdf
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = app(\Barryvdh\DomPDF\Facade\Pdf::class);
            $pdf->loadHtml($html);
            return $pdf->download('report.pdf');
        }

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();
            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="report.pdf"');
        }

        // If no PDF lib is available, fallback to streaming the HTML with an error header
        return response($html, 200)
            ->header('Content-Type', 'text/html')
            ->header('X-Notice', 'No PDF library installed. Run composer require barryvdh/laravel-dompdf');
    }

    public function print(Request $request)
    {
        // Reuse apiReports logic to build report data
        $apiResponse = $this->apiReports($request);
        $json = json_decode($apiResponse->getContent(), true);
        $reportData = $json['reportData'] ?? [];
        $type = $request->get('report_type', 'sales');
        $period = $this->parsePeriod($request);
        $from = $period['from'];
        $to = $period['to'];
        return view('reports.print', ['reportType' => $type, 'reportData' => $reportData, 'period' => $period['period'], 'from' => $from, 'to' => $to]);
    }
}
