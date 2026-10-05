<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /** Days before expiry at which a subscription is flagged "Expiring Soon". */
    private const EXPIRING_SOON_DAYS = 7;

    /**
     * Admin report: subscribers, licence keys and subscription lifecycle.
     *
     * Supports `?status=` (active|expiring|expired|pending) and `?q=`
     * (matches user name / email / licence key / service title).
     */
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        $query = Payment::query()
            ->with(['user', 'service'])
            ->whereNotNull('service_id')
            ->latest();

        $this->applyStatus($query, $status);
        $this->applySearch($query, $search);

        $subscriptions = $query->paginate(25)->withQueryString();

        // Headline stats (unfiltered, so the cards always describe the estate).
        $approved = Payment::where('status', 'approved')->whereNotNull('service_id');

        $stats = [
            'total_subscriptions' => Payment::whereNotNull('service_id')->count(),
            'active' => (clone $approved)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count(),
            'expiring_soon' => (clone $approved)
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addDays(self::EXPIRING_SOON_DAYS))
                ->count(),
            'expired' => (clone $approved)->where('expires_at', '<=', now())->count(),
            'pending' => Payment::where('status', 'pending')->whereNotNull('service_id')->count(),
            'rejected' => Payment::where('status', 'rejected')->whereNotNull('service_id')->count(),
            'total_customers' => User::count(),
            'customers_with_service' => User::whereHas(
                'payments',
                fn ($q) => $q->where('status', 'approved')->whereNotNull('service_id')
            )->count(),
            'monthly_recurring_syp' => round(
                (float) (clone $approved)->where('expires_at', '>', now())->sum('amount'),
                0
            ),
            'licences_issued' => Payment::where('status', 'approved')
                ->whereNotNull('license_key')->count(),
            'active_services' => Service::where('is_available', true)->count(),
            'by_category' => Service::select('category', DB::raw('count(*) as total'))
                ->groupBy('category')->orderBy('category')->pluck('total', 'category')->all(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'status', 'search'));
    }

    /**
     * Apply the status filter.
     */
    private function applyStatus($query, string $status): void
    {
        switch ($status) {
            case 'active':
                $query->where('status', 'approved')
                    ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
                break;

            case 'expiring':
                $query->where('status', 'approved')
                    ->where('expires_at', '>', now())
                    ->where('expires_at', '<=', now()->addDays(self::EXPIRING_SOON_DAYS));
                break;

            case 'expired':
                $query->where('status', 'approved')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
                break;

            case 'pending':
                $query->where('status', 'pending');
                break;

            case 'rejected':
                $query->where('status', 'rejected');
                break;

            default:
                // 'all' — no filter.
                break;
        }
    }

    /**
     * Free-text search across customer and subscription fields.
     */
    private function applySearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('license_key', 'like', '%'.$search.'%')
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('email', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('service', function ($sq) use ($search) {
                    $sq->where('title', 'like', '%'.$search.'%');
                });
        });
    }
}