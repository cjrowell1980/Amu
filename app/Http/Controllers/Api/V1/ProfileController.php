<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile', 'roles');

        return response()->json(['data' => new UserResource($user)]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'display_name' => ['sometimes', 'string', 'min:2', 'max:50'],
            'avatar_url'   => ['sometimes', 'nullable', 'url', 'max:500'],
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2'],
            'status'       => ['sometimes', 'nullable', 'string', 'max:100'],
            'preferences'  => ['sometimes', 'nullable', 'array'],
        ]);

        if (isset($validated['display_name']) ||
            isset($validated['avatar_url']) ||
            isset($validated['country_code']) ||
            isset($validated['status']) ||
            isset($validated['preferences'])) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                array_filter($validated, fn ($v) => $v !== null),
            );
        }

        // Update email if provided and different
        if ($request->has('name')) {
            $user->update($request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
            ]));
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data'    => new UserResource($user->fresh('profile', 'roles')),
        ]);
    }
}
