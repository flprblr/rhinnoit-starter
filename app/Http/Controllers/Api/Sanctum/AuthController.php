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
     * Emitir un token de acceso Sanctum para autenticación interna
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
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
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
     * Obtener el usuario autenticado actual
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
     * Revocar el token actual del usuario
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revocado exitosamente.',
        ], 200);
    }

    /**
     * Revocar todos los tokens del usuario
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todos los tokens han sido revocados exitosamente.',
        ], 200);
    }

    /**
     * Listar tokens del usuario autenticado
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
     * Revocar un token por ID (del usuario autenticado)
     */
    public function revokeById(Request $request, int $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Token no encontrado o no pertenece al usuario.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token revocado exitosamente.',
        ], 200);
    }

    /**
     * Revocar tokens por nombre (del usuario autenticado)
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
            'message' => $deleted ? 'Tokens revocados exitosamente.' : 'No se encontraron tokens con ese nombre.',
        ], 200);
    }

    /**
     * Revocar todos los tokens excepto el actual
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
            'message' => 'Se han revocado los demás tokens.',
        ], 200);
    }

    /**
     * Revocar tokens expirados del usuario autenticado
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
            'message' => 'Tokens expirados revocados.',
        ], 200);
    }

    /**
     * Verificar si el token actual es válido
     */
    public function verify(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'No se encontró un token válido.',
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
     * Obtener información de un token específico
     */
    public function showToken(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no encontrado o no pertenece al usuario.',
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
     * Actualizar un token existente
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
                'message' => 'Token no encontrado o no pertenece al usuario.',
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
            'message' => 'Token actualizado exitosamente.',
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
     * Obtener cookie CSRF para autenticación SPA
     * 
     * Nota: La cookie CSRF se establece automáticamente por el middleware 'web'
     */
    public function csrfCookie(): JsonResponse
    {
        // La cookie CSRF se establece automáticamente por el middleware web
        // Solo retornamos una respuesta exitosa
        return response()->json([
            'success' => true,
            'message' => 'Cookie CSRF establecida correctamente.',
        ], 200);
    }

    /**
     * Autenticación basada en sesión para SPAs
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
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Autenticar usando sesión web
        auth('web')->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Autenticación exitosa.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Cerrar sesión basada en sesión para SPAs
     */
    public function logout(Request $request): JsonResponse
    {
        auth('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.',
        ], 200);
    }
}
