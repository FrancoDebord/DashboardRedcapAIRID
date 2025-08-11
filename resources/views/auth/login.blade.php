<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion — Dashboard ATSB Projects</title>
    <!-- Tailwind CDN pour prototype rapide (en prod, compilez dans vos assets) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-indigo-50 flex items-center justify-center">
    <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 gap-8 p-6">
        <!-- Visuel / Illustration -->
        <div style="background: #c10202"
            class="hidden md:flex items-center justify-center bg-gradient-to-b from-indigo-600 to-indigo-500 rounded-2xl shadow-lg p-8 text-white">
            <div class="space-y-6 max-w-md">
                <h1 class="text-3xl font-extrabold">Bienvenue sur <span class="text-yellow-300">DashBoard ATSB
                        Project</span></h1>
                <p class="opacity-90">Connectez-vous pour accéder à votre tableau de bord du projet ATSB</p>
                <ul class="space-y-2 text-sm opacity-90">
                    <li>✅ L'évolution de la collecte sur toutes les bases</li>
                    <li>✅ Les incohérences constatées </li>
                    <li>✅ Quels statistiques descriptives</li>
                </ul>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-white rounded-2xl shadow-md p-8 flex flex-col justify-center">
            <div class="mb-6 text-center">
                <a href="/" class="inline-block">
                    <img src="{{ asset('storage/assets/logo/airid.png') }}" alt="Logo" class="mx-auto "
                        style="width: 234px">
                </a>
                <h2 class="mt-4 text-2xl font-bold text-gray-800">Se connecter</h2>
                <p class="text-sm text-gray-500">Entrez vos identifiants pour continuer</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 px-4 py-3 rounded bg-green-50 text-green-700 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <div class="mt-1 relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required
                            class="block w-full pr-10 rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
                            <template x-if="!show">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M10 3C6 3 2.7 5.1 1 8.5 2.7 11.9 6 14 10 14s7.3-2.1 9-5.5C17.3 5.1 14 3 10 3zM10 12a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </template>
                            <template x-if="show">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M4.03 3.97a.75.75 0 10-1.06 1.06l1.1 1.1A9.9 9.9 0 001 8.5C2.7 11.9 6 14 10 14c1.46 0 2.83-.28 4.06-.77l1.98 1.98a.75.75 0 101.06-1.06L4.03 3.97zM10 12a2 2 0 01-2-2c0-.18.03-.35.08-.51l2.43 2.43c-.16.05-.33.08-.51.08z" />
                                </svg>
                            </template>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" name="remember"
                            class="rounded border-gray-200 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-gray-700">Se souvenir de moi</span>
                    </label>

                    <div>
                        <a href="https://onlinetraining.airid-africa.com/forgot-password?retour={{ url('/') }}"
                            class="text-sm text-indigo-600 hover:underline" target="_blank">Mot de passe oublié ?</a>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold shadow">
                        Se connecter
                    </button>
                </div>

                {{-- <div class="text-center text-sm text-gray-500">ou</div> --}}

                {{-- <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <a href="{{ route('social.redirect', 'github') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 border rounded-md hover:bg-gray-50">
            <img src="https://cdn.jsdelivr.net/npm/simple-icons@v7/icons/github.svg" alt="" class="h-5 w-5 opacity-80">
            Continuer avec GitHub
          </a>

          <a href="{{ route('social.redirect', 'google') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 border rounded-md hover:bg-gray-50">
            <img src="https://cdn.jsdelivr.net/npm/simple-icons@v7/icons/google.svg" alt="" class="h-5 w-5 opacity-80">
            Continuer avec Google
          </a>
        </div> --}}

                {{-- <p class="mt-4 text-center text-sm text-gray-600">Pas encore de compte ? <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">Créer un compte</a></p> --}}
            </form>

            <footer class="mt-6 text-xs text-gray-400 text-center">
                © {{ date('Y') }} Dashboard ATSB Project — Tous droits réservés
            </footer>
        </div>
    </div>
</body>

</html>
