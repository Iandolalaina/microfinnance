<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => [
                'required',
                'url',
                'max:2048',
                function (string $attribute, string $value, Closure $fail): void {
                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    $host = trim((string) parse_url($value, PHP_URL_HOST), '[]');
                    $ipAddress = filter_var($host, FILTER_VALIDATE_IP);

                    if ($scheme !== 'https'
                        || $host === ''
                        || in_array(mb_strtolower($host), ['localhost'], true)
                        || str_ends_with(mb_strtolower($host), '.localhost')
                        || str_ends_with(mb_strtolower($host), '.local')
                        || ($ipAddress !== false && filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)) {
                        $fail('Le point de notification doit être une adresse HTTPS publique.');
                    }
                },
            ],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'in:aes128gcm,aesgcm'],
        ]);

        $endpointHash = hash('sha256', $validated['endpoint']);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id' => $request->user()->id,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
            ],
        );

        return response()->json(['message' => 'Notifications activées sur cet appareil.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
        ]);

        $request->user()->pushSubscriptions()
            ->where('endpoint_hash', hash('sha256', $validated['endpoint']))
            ->delete();

        return response()->json(['message' => 'Notifications désactivées sur cet appareil.']);
    }
}