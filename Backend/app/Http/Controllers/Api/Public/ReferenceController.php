<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Extra;
use App\Models\PickupLocation;
use Illuminate\Http\JsonResponse;

class ReferenceController extends Controller
{
    public function categories(): JsonResponse
    {
        return response()->json([
            'data' => Category::query()
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function locations(): JsonResponse
    {
        return response()->json([
            'data' => PickupLocation::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function extras(): JsonResponse
    {
        return response()->json([
            'data' => Extra::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
