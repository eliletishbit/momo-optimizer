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

class RegisteredUserController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

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
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $isPhoneRegistration = $request->input('registration_type') === 'phone';

        if ($isPhoneRegistration) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'country_code' => ['required', 'string', 'exists:countries,code'],
                'phone' => ['required', 'string', 'min:8', 'max:25'],
                'otp_code' => ['required', 'string', 'size:6'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            ]);

            $phone = $this->otpService->normalizePhone($request->phone);

            // Vérifier que le numéro est unique
            if (User::where('phone', $phone)->exists()) {
                throw ValidationException::withMessages([
                    'phone' => 'Ce numéro de téléphone est déjà associé à un compte existant.',
                ]);
            }

            // Vérifier le code OTP
            $isValidOtp = $this->otpService->verify($phone, $request->otp_code);
            if (!$isValidOtp) {
                throw ValidationException::withMessages([
                    'otp_code' => 'Code OTP incorrect ou expiré. Veuillez redemander un nouveau code.',
                ]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email ?: null,
                'phone' => $phone,
                'phone_verified_at' => now(),
                'whatsapp_enabled' => $request->boolean('whatsapp_enabled', true),
                'password' => Hash::make($request->password),
                'country_code' => $request->country_code,
            ]);
        } else {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'country_code' => ['required', 'string', 'exists:countries,code'],
                'phone' => ['nullable', 'string', 'max:25', 'unique:'.User::class],
            ]);

            $phone = $request->filled('phone') ? $this->otpService->normalizePhone($request->phone) : null;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'password' => Hash::make($request->password),
                'country_code' => $request->country_code,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
