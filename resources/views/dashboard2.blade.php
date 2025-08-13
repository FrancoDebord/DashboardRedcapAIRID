<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — ATSB Project</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}

    <meta name="description" content="DashboardProjectRedCap - Accueil">
    <meta name="keywords" content="RedCap, Dashboard, Projet, Accueil">
    <meta name="author" content="DashboardProjectRedCap Team">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app_url" content="{{ \Request::getSchemeAndHttpHost() }}/" />


    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/Chart.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">

 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">


    <link rel="stylesheet" href="{{ asset('storage/assets/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('storage/assets/logo/airid.png') }}" type="image/x-icon">
</head>
<body class="bg-light">
  <!-- Header -->
  {{-- <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-primary" href="#"> Dashboard ATSB Project</a>
      <div class="d-flex align-items-center">
        <span class="me-3 small text-muted">👋 Bonjour, {{ Auth::user()->prenom ?? 'Utilisateur' }}</span>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-sm btn-danger">Déconnexion</button>
        </form>
      </div>
    </div>
  </nav> --}}

   <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-primary" href="{{ url("/") }}">Dashboard ATSB Project</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ route("pullDataFromRedCapAnGambiaeFINAL",["project_id"=>38]) }}">Dashboard des projets ATSB</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route("pullQueriesDataREDCapAnGambiaeFINAL",["project_id"=>38]) }}">Incohérences à corriger</a>
          </li>
          {{-- <li class="nav-item">
            <a class="nav-link" href="#">Projets</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Utilisateurs</a>
          </li> --}}
        </ul>
        <div class="d-flex align-items-center">
          <span class="me-3 small text-muted">👋 Bonjour, {{ Auth::user()->prenom ?? 'Utilisateur' }}</span>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">Déconnexion</button>
          </form>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main -->
  <main class="container py-4">

    @yield('content')
    <!-- KPI Cards -->
    {{-- <div class="row g-4 mb-4">
      @php
        $stats = [
          ['label' => 'Revenus', 'value' => '12 450 €', 'change' => '+8%', 'color' => 'success'],
          ['label' => 'Nouveaux Clients', 'value' => '120', 'change' => '+15%', 'color' => 'success'],
          ['label' => 'Projets en cours', 'value' => '32', 'change' => 'Stable', 'color' => 'warning'],
          ['label' => 'Tickets Support', 'value' => '7', 'change' => '+2 aujourd\'hui', 'color' => 'danger']
        ];
      @endphp
      @foreach($stats as $stat)
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h6 class="text-muted">{{ $stat['label'] }}</h6>
              <h3 class="fw-bold">{{ $stat['value'] }}</h3>
              <small class="text-{{ $stat['color'] }}">{{ $stat['change'] }} ce mois</small>
            </div>
          </div>
        </div>
      @endforeach
    </div> --}}

    {{-- <!-- Chart and Table -->
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">📈 Ventes mensuelles</h5>
            <canvas id="salesChart" height="120"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title mb-3">🕒 Dernières activités</h5>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Statut</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>2025-08-10</td>
                    <td>Jean Dupont</td>
                    <td>Création de projet</td>
                    <td><span class="badge bg-success">Succès</span></td>
                  </tr>
                  <tr>
                    <td>2025-08-09</td>
                    <td>Marie Durant</td>
                    <td>Ajout de client</td>
                    <td><span class="badge bg-warning text-dark">En attente</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
  </main>

  <!-- Chart Script -->
  <script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil'],
        datasets: [{
          label: 'Ventes (€)',
          data: [1200, 1900, 3000, 2500, 2800, 3200, 4000],
          backgroundColor: 'rgba(13, 110, 253, 0.2)',
          borderColor: 'rgba(13, 110, 253, 1)',
          borderWidth: 2,
          tension: 0.3,
          fill: true,
          pointBackgroundColor: 'rgba(13, 110, 253, 1)'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-labels@1.2.0/dist/chartjs-plugin-labels.min.js"></script>

    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>


    @yield('js')
</body>
</html>
