<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminSignatureResource;
use App\Models\VoteTopic;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SignatureController extends Controller
{
    public function index(VoteTopic $voteTopic): AnonymousResourceCollection
    {
        $signatures = $voteTopic->signatures()
            ->with('user:id,nick,email')
            ->latest()
            ->paginate(30);

        return AdminSignatureResource::collection($signatures);
    }
}
