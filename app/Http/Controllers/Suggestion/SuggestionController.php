<?php

namespace App\Http\Controllers\Suggestion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Suggestion\StoreSuggestionRequest;
use App\Http\Resources\Suggestion\SuggestionResource;
use App\Models\Suggestion;
use App\Services\Suggestion\SuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SuggestionController extends Controller
{
    public function __construct(private readonly SuggestionService $suggestions) {}

    public function index(): AnonymousResourceCollection
    {
        $suggestions = Suggestion::query()
            ->where('user_id', request()->user()->id)
            ->latest()
            ->paginate(20);

        return SuggestionResource::collection($suggestions);
    }

    public function store(StoreSuggestionRequest $request): JsonResponse
    {
        $suggestion = $this->suggestions->create($request->validated(), $request->user());

        return (new SuggestionResource($suggestion))
            ->response()
            ->setStatusCode(201);
    }
}
