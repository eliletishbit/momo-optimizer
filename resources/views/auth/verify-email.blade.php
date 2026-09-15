@extends('layouts.guest')

@section('title', 'Vérification Email')

@section('content')
    <div class="mb-10 text-center lg:text-left">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-50 text-indigo-600 rounded-3xl mb-6 lg:mb-8 animate-bounce">
            <i class="far fa-envelope-open fa-2xl"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Vérifiez vos emails</h2>
        <p class="text-slate-500 mt-3 text-base leading-relaxed">
            Merci de rejoindre MomoOpti ! Un lien de confirmation a été envoyé à votre adresse. Merci de cliquer dessus pour activer votre compte.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-center">
            <p class="text-sm font-bold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i> Un nouveau lien a été envoyé !
            </p>
        </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-indigo-100 transition-all active:scale-[0.98]">
                Renvoyer l'email <i class="fas fa-sync ml-2 text-indigo-300"></i>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-sm font-bold text-slate-400 hover:text-red-500 transition-colors py-2">
                Se déconnecter
            </button>
        </form>
    </div>
@endsection
