<?php

namespace App\Http\Controllers\Debate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Debate\StoreDebateRequest;
use App\Http\Requests\Debate\UpdateDebateRequest;
use App\Http\Resources\Debate\DebateResource;
use App\Models\Debate;
use App\Services\Debate\DebateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DebateController extends Controller
{
    public function __construct(private readonly DebateService $debates) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = addcslashes($request->string('search')->toString(), '%_\\');

        $debates = Debate::query()
            ->with('author:id,nick')
            ->withCount('messages')
            ->when(
                $request->filled('category'),
                fn ($query) => $query->where('category', $request->string('category')->toString())
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where('title', 'like', '%'.$search.'%')
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return DebateResource::collection($debates);
    }

    public function store(StoreDebateRequest $request): JsonResponse
    {
        $debate = $this->debates->create(
            $request->safe()->except(['image', 'video']),
            $request->user()->id,
            $request->file('image'),
            $request->file('video'),
        );

        return (new DebateResource($debate->loadCount('messages')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Debate $debate): DebateResource
    {
        $debate->load('author:id,nick')->loadCount('messages');

        return new DebateResource($debate);
    }

    public function update(UpdateDebateRequest $request, Debate $debate): DebateResource
    {
        $debate = $this->debates->update(
            $debate,
            $request->safe()->except(['image', 'video']),
            $request->file('image'),
            $request->file('video'),
        );

        return new DebateResource($debate->loadCount('messages'));
    }

    public function destroy(Debate $debate): Response
    {
        $this->authorize('delete', $debate);
        $this->debates->delete($debate);

        return response()->noContent();
    }
}
