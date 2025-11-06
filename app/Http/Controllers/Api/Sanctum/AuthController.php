<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Http\Controllers\Controller;
use App\Models\UserSanctum;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue a Sanctum access token for internal authentication
     */
    public function issueToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $user = UserSanctum::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $abilities = $validated['abilities'] ?? ['*'];
        $expiresAt = isset($validated['expires_at'])
            ? Carbon::parse($validated['expires_at'])
            : null;

        $token = $user->createToken(
            $validated['device_name'],
            $abilities,
            $expiresAt
        );

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token_info' => [
                'name' => $token->accessToken->name,
                'abilities' => $token->accessToken->abilities,
                'expires_at' => $token->accessToken->expires_at?->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Get the current authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ], 200);
    }

    /**
     * Revoke the current user's token
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully.',
        ], 200);
    }

    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All tokens have been revoked successfully.',
        ], 200);
    }

    /**
     * List tokens of the authenticated user
     */
    public function listTokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at'])
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => optional($token->last_used_at)?->toDateTimeString(),
                    'expires_at' => optional($token->expires_at)?->toDateTimeString(),
                    'created_at' => optional($token->created_at)?->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'tokens' => $tokens,
        ], 200);
    }

    /**
     * Revoke a token by ID (of the authenticated user)
     */
    public function revokeById(Request $request, int $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found or does not belong to the user.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully.',
        ], 200);
    }

    /**
     * Revoke tokens by name (of the authenticated user)
     */
    public function revokeByName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $deleted = $request->user()->tokens()->where('name', $validated['name'])->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => $deleted ? 'Tokens revoked successfully.' : 'No tokens found with that name.',
        ], 200);
    }

    /**
     * Revoke all tokens except the current one
     */
    public function revokeOthers(Request $request): JsonResponse
    {
        $current = $request->user()->currentAccessToken();
        $query = $request->user()->tokens();
        if ($current) {
            $query->where('id', '!=', $current->id);
        }
        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => 'Other tokens have been revoked.',
        ], 200);
    }

    /**
     * Revoke expired tokens of the authenticated user
     */
    public function revokeExpired(Request $request): JsonResponse
    {
        $deleted = $request->user()->tokens()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => 'Expired tokens revoked.',
        ], 200);
    }

    /**
     * Verify if the current token is valid
     */
    public function verify(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'No valid token found.',
            ], 401);
        }

        $isValid = ! $token->expires_at || $token->expires_at > now();

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'expires_at' => $token->expires_at?->toDateTimeString(),
                'last_used_at' => $token->last_used_at?->toDateTimeString(),
                'created_at' => $token->created_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Get information about a specific token
     */
    public function showToken(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found or does not belong to the user.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toDateTimeString(),
                'expires_at' => $token->expires_at?->toDateTimeString(),
                'created_at' => $token->created_at->toDateTimeString(),
                'updated_at' => $token->updated_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Update an existing token
     */
    public function updateToken(Request $request, int $tokenId): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
        ]);

        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found or does not belong to the user.',
            ], 404);
        }

        if (isset($validated['expires_at'])) {
            $validated['expires_at'] = $validated['expires_at']
                ? Carbon::parse($validated['expires_at'])
                : null;
        }

        $token->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Token updated successfully.',
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'expires_at' => $token->expires_at?->toDateTimeString(),
                'last_used_at' => $token->last_used_at?->toDateTimeString(),
                'created_at' => $token->created_at->toDateTimeString(),
                'updated_at' => $token->updated_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Get CSRF cookie for SPA authentication
     *
     * Note: The CSRF cookie is automatically set by the 'web' middleware
     */
    public function csrfCookie(): JsonResponse
    {
        // The CSRF cookie is automatically set by the web middleware
        // We just return a successful response
        return response()->json([
            'success' => true,
            'message' => 'CSRF cookie set successfully.',
        ], 200);
    }

    /**
     * Session-based authentication for SPAs
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = UserSanctum::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Authenticate using web session
        auth('web')->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Logout session-based authentication for SPAs
     */
    public function logout(Request $request): JsonResponse
    {
        auth('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Session closed successfully.',
        ], 200);
    }
}
