<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Account;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Activity;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'contacts'      => Contact::count(),
            'accounts'      => Account::count(),
            'leads'         => Lead::where('is_converted', false)->count(),
            'opportunities' => Opportunity::whereNull('won_at')->whereNull('lost_at')->count(),
            'revenue'       => Opportunity::whereNotNull('won_at')->sum('amount'),
        ];

        $recentActivities = Activity::with(['activitable'])
            ->latest()
            ->limit(10)
            ->get();

        $recentOpportunities = Opportunity::with(['account', 'stage'])
            ->whereNull('won_at')
            ->whereNull('lost_at')
            ->latest()
            ->limit(5)
            ->get();

        $recentContacts = Contact::latest()->limit(5)->get();

        return view('dashboard.index', compact(
            'stats',
            'recentActivities',
            'recentOpportunities',
            'recentContacts'
        ));
    }
}
