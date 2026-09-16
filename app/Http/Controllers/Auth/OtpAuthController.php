<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtpAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Envoie un code OTP par WhatsApp ou SMS.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:25'],
            'channel' => ['nullable', 'string', 'in:whatsapp,sms'],
        ]);

        $phone = $this->otpService->normalizePhone($request->phone);

        // Vérifier si le numéro est déjà utilisé
        if (User::where('phone', $phone)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce numéro de téléphone est déjà associé à un compte.',
            ], 422);
        }

        try {
            $result = $this->otpService->generate(
                $phone,
                $request->input('channel', 'whatsapp')
            );

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first('phone'),
            ], 429);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'envoyer le code pour le moment. Veuillez réessayer.',
            ], 500);
        }
    }

    /**
     * Vérifie un code OTP sans finaliser l'inscription.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $isValid = $this->otpService->verify($request->phone, $request->code);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Code de vérification incorrect ou expiré.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Numéro vérifié avec succès !',
        ]);
    }
}
