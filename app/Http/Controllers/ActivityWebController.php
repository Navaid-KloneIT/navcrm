<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with(['user', 'activitable'])->latest();

        if ($type = $request->get('type')) {
            $query->where('activity_type', $type);
        }

        $activities = $query->paginate(30)->withQueryString();

        return view('activities.index', compact('activities'));
    }
}
