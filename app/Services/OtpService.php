<?php

namespace App\Services;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    /**
     * Durée de validité du code OTP en minutes.
     */
    public const VALIDITY_MINUTES = 10;

    /**
     * Nombre maximum de tentatives de saisie autorisées.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * Normalise un numéro de téléphone (retire les espaces, tirets, etc.)
     */
    public function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));
        // S'il n'y a pas de signe +, s'assurer que c'est propre
        return $cleaned;
    }

    /**
     * Génère et expédie un code OTP vers un numéro par WhatsApp ou SMS.
     *
     * @throws ValidationException
     */
    public function generate(string $phone, string $channel = 'whatsapp'): array
    {
        $phone = $this->normalizePhone($phone);
        $throttleKey = 'send-otp:' . $phone;

        // Limiteur de débit : max 3 envois toutes les 5 minutes
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'phone' => "Veuillez patienter {$seconds} secondes avant de demander un nouveau code.",
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        // Invalider les anciens codes non vérifiés pour ce numéro
        Otp::where('phone', $phone)
            ->whereNull('verified_at')
            ->delete();

        // Générer un code cryptographique à 6 chiffres
        $code = (string) random_int(100000, 999999);

        // Sauvegarder dans la base de données
        $otp = Otp::create([
            'phone' => $phone,
            'code' => $code,
            'channel' => in_array($channel, ['whatsapp', 'sms']) ? $channel : 'whatsapp',
            'expires_at' => Carbon::now()->addMinutes(self::VALIDITY_MINUTES),
            'attempts' => 0,
        ]);

        // Envoi effectif du message via la passerelle réelle configurée
        $this->dispatchMessage($phone, $code, $otp->channel);

        $isDemoMode = (bool) config('services.otp.demo_mode', false);

        $response = [
            'success' => true,
            'message' => $otp->channel === 'whatsapp'
                ? 'Code de vérification envoyé sur votre WhatsApp.'
                : 'Code de vérification envoyé par SMS direct.',
            'phone' => $phone,
            'channel' => $otp->channel,
            'expires_at' => $otp->expires_at->toIso8601String(),
        ];

        // N'exposer le code démo QUE si le mode démo est explicitement activé
        if ($isDemoMode) {
            $response['demo_code'] = $code;
        }

        return $response;
    }

    /**
     * Vérifie la validité d'un code OTP.
     */
    public function verify(string $phone, string $code): bool
    {
        $phone = $this->normalizePhone($phone);
        $code = trim($code);

        $otp = Otp::where('phone', $phone)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->delete();
            return false;
        }

        if ($otp->code !== $code) {
            $otp->increment('attempts');
            return false;
        }

        // Marquer le code comme validé
        $otp->update([
            'verified_at' => Carbon::now(),
        ]);

        return true;
    }

    /**
     * Envoie le message via la passerelle configurée (Termii, Twilio ou Webhook).
     */
    protected function dispatchMessage(string $phone, string $code, string $channel): void
    {
        $message = "Votre code de vérification MomoOpti est : {$code}. Valable 10 minutes.";

        // Format du numéro sans signe '+' pour certaines passerelles comme Termii
        $phoneWithoutPlus = ltrim($phone, '+');
        // Format avec '+' pour Twilio
        $phoneWithPlus = str_starts_with($phone, '+') ? $phone : '+' . $phone;

        // Journalisation pour audit
        Log::info("[OTP {$channel}] Déclenchement pour {$phone} : {$code}");

        // 1. PASSERELLE TERMII (Afrique de l'Ouest & International)
        $termiiKey = config('services.termii.api_key');
        if ($termiiKey) {
            try {
                $termiiUrl = rtrim(config('services.termii.url', 'https://api.ng.termii.com'), '/') . '/api/sms/send';
                $senderId = config('services.termii.sender_id', 'MomoOpti');

                $payload = [
                    'to' => $phoneWithoutPlus,
                    'from' => $senderId,
                    'sms' => $message,
                    'type' => 'plain',
                    'channel' => 'generic',
                    'api_key' => $termiiKey,
                ];

                $response = Http::timeout(8)->post($termiiUrl, $payload);
                Log::info("[OTP Termii] Réponse HTTP {$response->status()}", [
                    'body' => $response->json(),
                ]);
            } catch (\Throwable $e) {
                Log::error("[OTP Termii Erreur] " . $e->getMessage());
            }
        }

        // 2. PASSERELLE TWILIO (SMS & WhatsApp)
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');
        if ($twilioSid && $twilioToken) {
            try {
                $from = $channel === 'whatsapp'
                    ? 'whatsapp:' . config('services.twilio.from_whatsapp')
                    : config('services.twilio.from_sms');

                $to = $channel === 'whatsapp'
                    ? 'whatsapp:' . $phoneWithPlus
                    : $phoneWithPlus;

                if ($from) {
                    $twilioUrl = "https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json";
                    $response = Http::withBasicAuth($twilioSid, $twilioToken)
                        ->asForm()
                        ->timeout(8)
                        ->post($twilioUrl, [
                            'From' => $from,
                            'To' => $to,
                            'Body' => $message,
                        ]);

                    Log::info("[OTP Twilio] Réponse HTTP {$response->status()}");
                }
            } catch (\Throwable $e) {
                Log::error("[OTP Twilio Erreur] " . $e->getMessage());
            }
        }

        // 3. WEBHOOK GÉNÉRIQUE PERSONNALISÉ
        $webhookUrl = config('services.otp.webhook_url');
        if ($webhookUrl) {
            try {
                Http::timeout(5)->post($webhookUrl, [
                    'phone' => $phone,
                    'code' => $code,
                    'channel' => $channel,
                    'message' => $message,
                ]);
            } catch (\Throwable $e) {
                Log::error("[OTP Webhook Erreur] " . $e->getMessage());
            }
        }
    }
}
