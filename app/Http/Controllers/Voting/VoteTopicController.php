<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voting\StoreVoteTopicRequest;
use App\Http\Requests\Voting\UpdateVoteTopicRequest;
use App\Http\Resources\Voting\VoteTopicResource;
use App\Models\VoteTopic;
use App\Services\Voting\VoteTopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class VoteTopicController extends Controller
{
    public function __construct(private readonly VoteTopicService $topics) {}

    public function index(): AnonymousResourceCollection
    {
        $topics = VoteTopic::query()
            ->with('author:id,nick')
            ->latest()
            ->paginate(20);

        return VoteTopicResource::collection($topics);
    }

    public function store(StoreVoteTopicRequest $request): JsonResponse
    {
        $topic = $this->topics->create($request->validated(), $request->user());

        return (new VoteTopicResource($topic))
            ->response()
            ->setStatusCode(201);
    }

    public function show(VoteTopic $voteTopic): VoteTopicResource
    {
        $voteTopic->load('author:id,nick');

        return new VoteTopicResource($voteTopic);
    }

    public function update(UpdateVoteTopicRequest $request, VoteTopic $voteTopic): VoteTopicResource
    {
        $topic = $this->topics->update($voteTopic, $request->validated());

        return new VoteTopicResource($topic);
    }

    public function destroy(VoteTopic $voteTopic): Response
    {
        $this->authorize('delete', $voteTopic);
        $this->topics->delete($voteTopic);

        return response()->noContent();
    }
}
