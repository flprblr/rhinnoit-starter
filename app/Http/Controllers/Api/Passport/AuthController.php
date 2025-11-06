<?php

namespace App\Http\Controllers\Api\Passport;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    /**
     * Issue a Passport access token for external companies
     */
    public function issueToken(ServerRequestInterface $request): JsonResponse
    {
        $accessTokenController = app(\Laravel\Passport\Http\Controllers\AccessTokenController::class);
        $response = $accessTokenController->issueToken($request);

        // If there's an error, return it directly
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Decode the response to add consistent format
        $tokenData = json_decode($response->getContent(), true);

        return response()->json([
            'success' => true,
            'token' => $tokenData['access_token'] ?? null,
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'expires_in' => $tokenData['expires_in'] ?? null,
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'scope' => $tokenData['scope'] ?? null,
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
     * Verify if the current token is valid
     */
    public function verify(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        if (! $token) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'No valid token found.',
            ], 401);
        }

        $isValid = ! $token->revoked && ($token->expires_at === null || $token->expires_at > now());

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'token' => [
                'id' => $token->id,
                'client_id' => $token->client_id,
                'scopes' => $token->scopes,
                'expires_at' => $token->expires_at?->toDateTimeString(),
                'created_at' => $token->created_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * List tokens of the authenticated user
     */
    public function listTokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'client_id', 'scopes', 'revoked', 'expires_at', 'created_at'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'client_id' => $token->client_id,
                    'scopes' => $token->scopes,
                    'revoked' => $token->revoked,
                    'expires_at' => $token->expires_at?->toDateTimeString(),
                    'created_at' => $token->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'tokens' => $tokens,
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
                'client_id' => $token->client_id,
                'scopes' => $token->scopes,
                'revoked' => $token->revoked,
                'expires_at' => $token->expires_at?->toDateTimeString(),
                'created_at' => $token->created_at->toDateTimeString(),
                'updated_at' => $token->updated_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Revoke the current user's token
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $token = $request->user()->token();

        if ($token) {
            $tokenRepository->revokeAccessToken($token->id);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully.',
        ], 200);
    }

    /**
     * Revoke a token by ID (of the authenticated user)
     */
    public function revokeById(Request $request, int $tokenId): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found or does not belong to the user.',
            ], 404);
        }

        $tokenRepository->revokeAccessToken($token->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);

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
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $tokens = $request->user()->tokens;

        foreach ($tokens as $token) {
            $tokenRepository->revokeAccessToken($token->id);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'All tokens have been revoked successfully.',
        ], 200);
    }

    /**
     * Revoke all tokens except the current one
     */
    public function revokeOthers(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $currentToken = $request->user()->token();
        $tokens = $request->user()->tokens;

        $deleted = 0;
        foreach ($tokens as $token) {
            if ($currentToken && $token->id !== $currentToken->id) {
                $tokenRepository->revokeAccessToken($token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
                $deleted++;
            }
        }

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
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $tokens = $request->user()->tokens()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('revoked', false)
            ->get();

        $deleted = 0;
        foreach ($tokens as $token) {
            $tokenRepository->revokeAccessToken($token->id);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => 'Expired tokens revoked.',
        ], 200);
    }

    /**
     * Revoke refresh token
     */
    public function revokeRefreshToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $refreshToken = $refreshTokenRepository->find($validated['refresh_token']);

        if ($refreshToken) {
            $refreshTokenRepository->revokeRefreshToken($validated['refresh_token']);
            $tokenRepository = app(TokenRepository::class);
            $tokenRepository->revokeAccessToken($refreshToken->access_token_id);

            return response()->json([
                'success' => true,
                'message' => 'Refresh token revoked successfully.',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Refresh token not found.',
        ], 404);
    }
}
