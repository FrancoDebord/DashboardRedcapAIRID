<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard — ATSB Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <meta name="description" content="DashboardProjectRedCap - Accueil">
    <meta name="keywords" content="RedCap, Dashboard, Projet, Accueil">
    <meta name="author" content="DashboardProjectRedCap Team">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app_url" content="{{ \Request::getSchemeAndHttpHost() }}/" />


    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/Chart.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">

    <link rel="stylesheet" href="{{ asset('storage/assets/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('storage/assets/logo/airid.png') }}" type="image/x-icon">
</head>

<body class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800">Tableau de bord</h1>
            <nav class="flex items-center gap-4">
                <span class="text-sm text-gray-600">Bonjour, {{ Auth::user()->name ?? 'Utilisateur' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">Déconnexion</button>
                </form>
            </nav>
        </div>
    </header>

    <!-- Main -->
    <main class="max-w-7xl mx-auto px-4 py-8 space-y-8">

        @yield('content')
        <!-- KPI Cards -->
        {{-- <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-sm font-medium text-gray-500">Revenus</h2>
                <p class="mt-2 text-3xl font-bold text-gray-800">12 450 €</p>
               
                <p class="mt-1 text-xs text-green-600">+38% ce mois</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-sm font-medium text-gray-500">Nouveaux Clients</h2>
                <p class="mt-2 text-3xl font-bold text-gray-800">120</p>
                <p class="mt-1 text-xs text-green-600">+15% ce mois</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-sm font-medium text-gray-500">Projets en cours</h2>
                <p class="mt-2 text-3xl font-bold text-gray-800">32</p>
                <p class="mt-1 text-xs text-yellow-600">Stable</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-sm font-medium text-gray-500">Tickets Support</h2>
                <p class="mt-2 text-3xl font-bold text-gray-800">7</p>
                <p class="mt-1 text-xs text-red-600">+2 aujourd'hui</p>
            </div>
        </section>

        <!-- Chart Section -->
        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Ventes mensuelles</h2>
            <canvas id="salesChart" height="120"></canvas>
        </section>

        <!-- Table Section -->
        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Dernières activités</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Utilisateur</th>
                            <th class="px-4 py-2 text-left">Action</th>
                            <th class="px-4 py-2 text-left">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t">
                            <td class="px-4 py-2">2025-08-10</td>
                            <td class="px-4 py-2">Jean Dupont</td>
                            <td class="px-4 py-2">Création de projet</td>
                            <td class="px-4 py-2"><span
                                    class="px-2 py-1 rounded bg-green-100 text-green-700">Succès</span></td>
                        </tr>
                        <tr class="border-t">
                            <td class="px-4 py-2">2025-08-09</td>
                            <td class="px-4 py-2">Marie Durant</td>
                            <td class="px-4 py-2">Ajout de client</td>
                            <td class="px-4 py-2"><span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700">En
                                    attente</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section> --}}
    </main>

    <!-- Chart Script -->
    {{-- <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil'],
                datasets: [{
                    label: 'Ventes (€)',
                    data: [1200, 1900, 3000, 2500, 2800, 3200, 4000],
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script> --}}


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-labels@1.2.0/dist/chartjs-plugin-labels.min.js"></script>

    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

    @yield('js')
</body>

</html>
