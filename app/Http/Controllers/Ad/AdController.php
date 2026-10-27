<?php

namespace App\Http\Controllers\Ad;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ad\AdResource;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $ads = Ad::query()
            ->active()
            ->when(
                $request->filled('slot'),
                fn ($query) => $query->where('slot', $request->string('slot')->toString())
            )
            ->orderBy('slot')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return AdResource::collection($ads);
    }
}
