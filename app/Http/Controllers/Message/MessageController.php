<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\Message\MessageResource;
use App\Models\Debate;
use App\Services\Debate\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    public function __construct(private readonly MessageService $messages) {}

    public function index(Debate $debate): AnonymousResourceCollection
    {
        $messages = $debate->messages()
            ->with('user:id,nick')
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(50);

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request, Debate $debate): JsonResponse
    {
        $message = $this->messages->create(
            $debate,
            $request->user(),
            $request->validated('content'),
            $request->validated('parent_id'),
        );

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }
}
