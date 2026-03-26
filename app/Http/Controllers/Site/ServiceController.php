<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = ServiceType::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('site.services.index', [
            'services' => $services,
        ]);
    }
}
