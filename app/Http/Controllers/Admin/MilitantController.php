<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\MilitantRegistry;
use App\Enums\MilitantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMilitantRequest;
use App\Http\Resources\Admin\AdminMilitantResource;
use App\Models\Militant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MilitantController extends Controller
{
    public function __construct(private readonly MilitantRegistry $registry) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = addcslashes($request->string('search')->toString(), '%_\\');
        $status = $request->string('status')->toString();

        $militants = Militant::query()
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($inner) use ($search): void {
                    $inner->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('ci', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })
            )
            ->when(
                in_array($status, ['pending', 'active', 'rejected'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return AdminMilitantResource::collection($militants);
    }

    public function update(UpdateMilitantRequest $request, Militant $militant): AdminMilitantResource
    {
        $status = MilitantStatus::from($request->validated('status'));
        $updated = $this->registry->updateStatus($militant, $status);

        return new AdminMilitantResource($updated);
    }
}
