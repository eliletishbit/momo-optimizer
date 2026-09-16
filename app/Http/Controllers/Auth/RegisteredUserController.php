<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use App\Models\Method;
use App\Models\UserMethod;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    /**
     * Handle an incoming registration request without costly OTP fees.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $isPhoneRegistration = $request->input('registration_type', 'phone') === 'phone';

        if ($isPhoneRegistration) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'country_code' => ['required', 'string', 'exists:countries,code'],
                'phone' => ['required', 'string', 'min:8', 'max:25'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            ]);

            // Normalisation du téléphone
            $phone = preg_replace('/[^\d+]/', '', trim($request->phone));
            if (!str_starts_with($phone, '+')) {
                $prefixes = [
                    'BJ' => '+229', 'CI' => '+225', 'TG' => '+228', 'SN' => '+221',
                    'BF' => '+226', 'ML' => '+223', 'NE' => '+227', 'CM' => '+237',
                    'GA' => '+241', 'CD' => '+243', 'CG' => '+242', 'GN' => '+224',
                ];
                $prefix = $prefixes[$request->country_code] ?? '+229';
                $phone = $prefix . ltrim($phone, '0');
            }

            // Vérifier que le numéro est unique
            if (User::where('phone', $phone)->exists()) {
                throw ValidationException::withMessages([
                    'phone' => 'Ce numéro de téléphone est déjà associé à un compte.',
                ]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email ?: null,
                'phone' => $phone,
                'phone_verified_at' => now(),
                'password' => Hash::make($request->password),
                'country_code' => $request->country_code,
                'subscription' => 'free',
                'subscription_expires_at' => now()->addDays(14),
                'trial_used' => 0,
            ]);
        } else {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'country_code' => ['required', 'string', 'exists:countries,code'],
                'phone' => ['nullable', 'string', 'max:25'],
            ]);

            $phone = null;
            if ($request->filled('phone')) {
                $phone = preg_replace('/[^\d+]/', '', trim($request->phone));
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'password' => Hash::make($request->password),
                'country_code' => $request->country_code,
                'subscription' => 'free',
                'subscription_expires_at' => now()->addDays(14),
                'trial_used' => 0,
            ]);
        }

        // Associer automatiquement les méthodes actives du pays pour démarrer immédiatement
        try {
            $activeMethods = Method::where('country_code', $user->country_code)
                ->whereRaw('is_active = true')
                ->get();

            foreach ($activeMethods as $method) {
                UserMethod::firstOrCreate([
                    'user_id' => $user->id,
                    'method_id' => $method->id,
                ], [
                    'type' => 'wallet',
                    'is_active' => true,
                ]);
            }
        } catch (\Throwable $e) {
            // Seeding silencieux
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
