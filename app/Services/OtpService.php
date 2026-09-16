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

        // Envoi effectif du message
        $this->dispatchMessage($phone, $code, $otp->channel);

        return [
            'success' => true,
            'message' => $otp->channel === 'whatsapp'
                ? 'Code de vérification envoyé sur votre WhatsApp.'
                : 'Code de vérification envoyé par SMS.',
            'phone' => $phone,
            'channel' => $otp->channel,
            'expires_at' => $otp->expires_at->toIso8601String(),
            // Exposé pour tests immédiats en environnement de démonstration
            'demo_code' => $code,
        ];
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
     * Envoie le message via le fournisseur configuré ou par journalisation.
     */
    protected function dispatchMessage(string $phone, string $code, string $channel): void
    {
        $message = "Votre code de vérification Momo Optimizer est : {$code}. Il est valable pendant " . self::VALIDITY_MINUTES . " minutes.";

        // Toujours journaliser pour debug et traçabilité
        Log::info("[OTP {$channel}] Envoi vers {$phone} : {$code}");

        // Si une URL de webhook ou une API WhatsApp/SMS est définie dans .env
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
                Log::error("Erreur lors de l'envoi OTP externe : " . $e->getMessage());
            }
        }
    }
}
