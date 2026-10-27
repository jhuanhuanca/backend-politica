<?php

namespace App\Http\Controllers\Militant;

use App\Contracts\MilitantRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Militant\RegisterMilitantRequest;
use Illuminate\Http\JsonResponse;

class MilitantController extends Controller
{
    public function __construct(private readonly MilitantRegistry $registry) {}

    public function store(RegisterMilitantRequest $request): JsonResponse
    {
        $this->registry->register(
            [
                ...$request->safe()->only([
                    'full_name',
                    'ci',
                    'email',
                    'phone',
                    'city',
                    'department',
                    'birth_year',
                ]),
                'consent' => true,
            ],
            auth('sanctum')->user()?->id,
        );

        return response()->json([
            'message' => 'Gracias por su registro. Nos pondremos en contacto.',
        ], 201);
    }
}
