<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdRequest;
use App\Http\Requests\Admin\UpdateAdRequest;
use App\Http\Resources\Ad\AdResource;
use App\Models\Ad;
use App\Services\Ad\AdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AdController extends Controller
{
    public function __construct(private readonly AdService $ads) {}

    public function index(): AnonymousResourceCollection
    {
        $ads = Ad::query()
            ->orderBy('slot')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return AdResource::collection($ads);
    }

    public function store(StoreAdRequest $request): JsonResponse
    {
        $ad = $this->ads->create(
            $request->safe()->except(['image']),
            $request->file('image'),
        );

        return (new AdResource($ad))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAdRequest $request, Ad $ad): AdResource
    {
        $ad = $this->ads->update(
            $ad,
            $request->safe()->except(['image']),
            $request->file('image'),
        );

        return new AdResource($ad);
    }

    public function destroy(Ad $ad): Response
    {
        $this->ads->delete($ad);

        return response()->noContent();
    }
}
