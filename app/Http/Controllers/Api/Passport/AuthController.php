<?php

namespace App\Http\Controllers\Api\Passport;

use App\Http\Controllers\Controller;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    public function issueToken(ServerRequestInterface $request)
    {
        // Usa el AccessTokenController de Passport
        return app(AccessTokenController::class)->issueToken($request);
    }
}
