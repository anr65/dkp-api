<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $baseUrl = 'https://gatewayapi.telegram.org';
    private string $token;

    public function __construct()
    {
        $this->token = config('services.telegram.gateway_token');
    }

    /**
     * Send verification message to phone number
     *
     * @param string $phoneNumber Phone in E.164 format
     * @param int $codeLength Length of verification code (4-8)
     * @return array|null Returns request_id on success
     */
    public function sendVerificationMessage(string $phoneNumber, int $codeLength = 6): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->post($this->baseUrl . '/sendVerificationMessage', [
                'phone_number' => $phoneNumber,
                'code_length' => $codeLength,
            ]);

            $data = $response->json();

            if ($data['ok'] ?? false) {
                return $data['result'];
            }

            Log::error('Telegram Gateway API error', ['error' => $data['error'] ?? 'Unknown error']);
            return null;
        } catch (\Exception $e) {
            Log::error('Telegram verification send failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check verification status
     *
     * @param string $requestId Request ID from sendVerificationMessage
     * @param string|null $code Optional verification code to check
     * @return array|null Returns verification status
     */
    public function checkVerificationStatus(string $requestId, ?string $code = null): ?array
    {
        try {
            $params = ['request_id' => $requestId];

            if ($code !== null) {
                $params['code'] = $code;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->post($this->baseUrl . '/checkVerificationStatus', $params);

            $data = $response->json();

            if ($data['ok'] ?? false) {
                return $data['result'];
            }

            Log::error('Telegram Gateway API error', ['error' => $data['error'] ?? 'Unknown error']);
            return null;
        } catch (\Exception $e) {
            Log::error('Telegram verification check failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Verify signature for callback
     *
     * @param string $signature Signature from X-Request-Signature header
     * @param string $payload Request body
     * @return bool
     */
    public function verifySignature(string $signature, string $payload): bool
    {
        $tokenHash = hash('sha256', $this->token, true);
        $expectedSignature = hash_hmac('sha256', $payload, $tokenHash);

        return hash_equals($expectedSignature, $signature);
    }
}
