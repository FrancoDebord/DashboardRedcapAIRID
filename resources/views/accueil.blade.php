<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="DashboardProjectRedCap - Accueil" >
    <meta name="keywords" content="RedCap, Dashboard, Projet, Accueil">
    <meta name="author" content="DashboardProjectRedCap Team">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app_url" content="{{ url('/') }}">
{{-- 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/v4-shims.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/brands.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/solid.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/svg-with-js.min.css"> --}}


    <title>Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/Chart.min.css">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="stylesheet" href="{{ asset('storage/assets/style.css') }}">
</head>

<body>
    <header>
        <h1>Bienvenue sur la page d'accueil</h1>

        <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top w-100">

            {{-- <a class="navbar-brand d-flex align-items-center me-3" href="#">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" width="40" height="40"
                    class="d-inline-block align-text-top">
            </a> --}}





            <div class="container-fluid">
                <a class="navbar-brand" href="#">DashboardProjectRedCap</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <form class="d-flex align-items-center me-3 " method="GET" action="{{ route('pullDataFromRedCap') }}"
                    style="margin-bottom: 0; height: 40px;">
                    <div class="input-group input-group-sm" style="height: 32px;">

                        @php
                            $projects = [
                                ['id' => 31, 'name' => 'ATSB An. Gambiae Baseline'],
                                ['id' => 35, 'name' => 'ATSB Other Species Baseline'],
                                ['id' => 38, 'name' => 'ATSB An. Gambiae FINAL'],
                                ['id' => 40, 'name' => 'ATSB ALL MOSQUITOES FINAL'],
                            ];

                            $project_id = request()->get('project_id', '');
                        @endphp
                        <select class="form-select form-control" name="project_id" aria-label="Sélectionner un projet"
                            style="height: 32px; padding-top: 0; padding-bottom: 0;">
                            <option selected disabled>Choisir un projet</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project['id'] }}" {{ $project['id'] == $project_id ? 'selected' : '' }}  >{{ $project['name'] }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-light btn-sm" type="submit"
                            style="height: 32px; padding-top: 0; padding-bottom: 0;">Valider</button>
                    </div>
                </form>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ route("pullDataFromRedCap") }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Queries</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div style="height: 70px;"></div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>

        <p>&copy; {{ date('Y') }} DashboardProjectRedCap</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-labels@1.2.0/dist/chartjs-plugin-labels.min.js"></script>
    <script src="{{ asset('storage/assets/javascript.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
