@extends('accueil')

@section('content')
    <div class="container">

        @php
            $projects = [
                ['id' => 31, 'name' => 'ATSB An. Gambiae Baseline'],
                ['id' => 35, 'name' => 'ATSB Other Species Baseline'],
                ['id' => 38, 'name' => 'ATSB An. Gambiae FINAL'],
                ['id' => 40, 'name' => 'ATSB ALL MOSQUITOES FINAL'],
            ];

            $project_id = request()->get('project_id', '');
        @endphp

        <div class="row mb-3">
            <div class="col d-flex align-items-center">
                <span class="me-2 fw-bold">Projets :</span>
                @foreach ($projects as $project)
                    <a href="{{ route('pullDataFromRedCap', ['project_id' => $project['id']]) }}"
                       class="btn btn-outline-primary me-2 {{ $project_id == $project['id'] ? 'active' : '' }}">
                        {{ $project['name'] }}
                    </a>
                @endforeach
            </div>
        </div>


        <div class="row mt-2 mb-3">
            <div class="col-12 ">
                <h3 class="text-success">Projet visualisé : {{ $project_title }}</h3>
            </div>
        </div>

        <style>
            .custom-bg-all {
                background-color: #e3f2fd;
                /* Light blue */
            }

            .custom-bg-bassila {
                background-color: #c8e6c9;
                /* Light green */
            }

            .custom-bg-zogbodomey {
                background-color: #fff9c4;
                /* Light yellow */
            }

            .custom-box-padding {
                padding: 15px;
            }
        </style>
        <div class="row mt-2">
            <div class="col border custom-bg-all custom-box-padding" style="height: 150px;">
                <h3>Tous les sites</h3>
                <p>Total des enregistrements : <strong class="text-danger">{{ $total_records }}</strong> </p>
            </div>
            <div class="col border custom-bg-bassila custom-box-padding     " style="height: 150px;">
                <h3>Bassila</h3>
                <p>Total des enregistrements à Bassila : <strong class="text-danger">{{ $total_records_bassila }}</strong>
                </p>
            </div>
            <div class="col border custom-bg-zogbodomey custom-box-padding  " style="height: 150px;">
                <h3>Zogbodomey</h3>
                <p>Total des enregistrements à Zogbodomey : <strong
                        class="text-danger">{{ $total_records_zogbodomey }}</strong></p>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col">
                <h3>Données envoyées par date et par tablette</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date de Collecte</th>
                            <th>Tablet ID</th>
                            <th>Nombre de données</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records_per_date_tablet as $date => $tablets)
                            @foreach ($tablets as $tablet => $count)
                                <tr>
                                    <td>{{ $date }}</td>
                                    <td>{{ $tablet }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="col">
                <h3>Graphiques</h3>
                <p>Graphiques et visualisations des données seront ajoutés ici.</p>
                <!-- Placeholder for future charts -->
                <canvas id="myBarChart" width="400" height="400"></canvas>

            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h3>Distribution des espèces par commune</h3>
                <canvas id="speciesChart" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h3>Distribution des espèces par commune (Bassila)</h3>
                <canvas id="speciesChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h3>Distribution des espèces par commune (Zogbodomey)</h3>
                <canvas id="speciesChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <h3>Tableau des espèces par commune</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Espèce</th>
                            <th>Nombre d'enregistrements (Bassila)</th>
                            <th>Nombre d'enregistrements (Zogbodomey)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($species_per_commune_bassila as $species => $count)
                            <tr>
                                <td>{{ $species }}</td>
                                <td>{{ $count }}</td>
                                <td>{{ $species_per_commune_zogbodomey[$species] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
