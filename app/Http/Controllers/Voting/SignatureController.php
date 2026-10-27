<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voting\StoreSignatureRequest;
use App\Http\Resources\Voting\SignatureResource;
use App\Models\Signature;
use App\Models\VoteTopic;
use App\Services\Voting\SignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SignatureController extends Controller
{
    public function __construct(private readonly SignatureService $signatures) {}

    public function store(StoreSignatureRequest $request, VoteTopic $voteTopic): JsonResponse
    {
        $signature = $this->signatures->sign(
            $voteTopic,
            $request->user(),
            $request->validated('ci'),
        );

        return (new SignatureResource($signature))
            ->response()
            ->setStatusCode(201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $signatures = Signature::query()
            ->where('user_id', $request->user()->id)
            ->with('voteTopic:id,title,target_signatures,signatures_count')
            ->latest()
            ->paginate(20);

        return SignatureResource::collection($signatures);
    }
}
