<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\User;
use App\Support\Bolivia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = addcslashes($request->string('search')->toString(), '%_\\');
        $department = $request->string('department')->toString();

        $users = User::query()
            ->withCount(['messages', 'signatures', 'debates', 'suggestions'])
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($inner) use ($search): void {
                    $inner->where('nick', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })
            )
            ->when(
                in_array($department, Bolivia::DEPARTMENTS, true),
                fn ($query) => $query->where('department', $department)
            )
            ->when(
                $department === 'none',
                fn ($query) => $query->whereNull('department')
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return AdminUserResource::collection($users);
    }
}
