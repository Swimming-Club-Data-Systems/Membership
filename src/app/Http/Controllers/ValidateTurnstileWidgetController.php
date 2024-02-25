<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ValidateTurnstileWidgetController extends Controller
{
    public function __invoke(Request $request)
    {
        $response = Http::post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.cloudflare.turnstile_secret'),
            'response' => $request->input('token'),
        ]);

        $json = $response->json();

        return response()->json([
            'success' => $json['success'],
            'error_codes' => $json['error-codes'],
        ]);
    }
}
