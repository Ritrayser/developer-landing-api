<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\JsonResponse;

class MetricsController extends Controller
{
    public function stats(): JsonResponse
    {
        $total = Contact::count();
        $byCategory = Contact::groupBy('category')
            ->selectRaw('category, count(*) as count')
            ->pluck('count', 'category')
            ->toArray();

        if (isset($byCategory[''])) {
            $byCategory['unknown'] = $byCategory[''];
            unset($byCategory['']);
        }

        return response()->json([
            'total_contacts' => $total,
            'categories'     => $byCategory,
        ]);
    }
}