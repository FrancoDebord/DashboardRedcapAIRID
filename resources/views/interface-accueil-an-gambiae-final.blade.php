@extends('dashboard2')

@section('js')
    <script src="{{ asset('storage/assets/javascript.js') }}"></script>
@endsection

@section('content')
    @php
        $project_id = request()->get('project_id', '');
    @endphp

    <div class="row mb-3">

        <input type="hidden" name="project_id" id="project_id" value="{{ $project_id }}">
        <div class="col d-flex align-items-center">
            <span class="me-2 fw-bold">Projets :</span>
            @foreach ($projects as $project)
                @php
                    $route = 'pullDataFromRedCap';
                    if ($project['id'] == '38') {
                        $route = 'pullDataFromRedCapAnGambiaeFINAL';
                    } elseif ($project['id'] == '40') {
                        $route = 'pullDataFromRedCapAllMosquitoesFINAL';
                    }

                @endphp
                <a href="{{ route($route, ['project_id' => $project['id']]) }}"
                    class="btn btn-outline-primary me-2 {{ $project_id == $project['id'] ? 'active' : '' }}">
                    {{ $project['name'] }}
                </a>
            @endforeach
        </div>
    </div>


    <div class="row mt-2 mb-3">
        <div class="col-12 ">
            <h3 class="text-primary">Projet visualisé : {{ $project_title }}</h3>
        </div>
    </div>


   <div class="row mt-2">
        <div class="col-12 col-sm-4 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Tous les sites</h6>
                    <h3 class="fw-bold">{{ $total_records ?? 0 }} </h3>
                    <small class="text-success">{{ round(($total_records * 100) / 2500, 2) }}% collectés</small>
                    
                    <div class="progress mt-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            aria-valuenow="{{ round(($total_records * 100) / 2500, 2) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ round(($total_records * 100) / 2500, 2) }}%">{{ round(($total_records * 100) / 2500, 2) }}%</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Bassila</h6>
                    <h3 class="fw-bold">{{ $total_records_bassila ?? 0 }}</h3>
                    <small class="text-success">{{ round(($total_records_bassila * 100) / 1250, 2) }}% collectés</small>
                
                     <div class="progress mt-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                            aria-valuenow="{{ round(($total_records_bassila * 100) / 1250, 2) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ round(($total_records_bassila * 100) / 1250, 2) }}%">{{ round(($total_records_bassila * 100) / 1250, 2) }}%</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Zogbodomey</h6>
                    <h3 class="fw-bold">{{ $total_records_zogbodomey ?? 0 }}</h3>
                    <small class="text-success">{{ round(($total_records_zogbodomey * 100) / 1250, 2) }}% collectés</small>
                
                     <div class="progress mt-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar"
                            aria-valuenow="{{ round(($total_records_zogbodomey * 100) / 1250, 2) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ round(($total_records_zogbodomey * 100) / 1250, 2) }}%">{{ round(($total_records_zogbodomey * 100) / 1250, 2) }}%</div>
                    </div>

                </div>
            </div>
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
                                <td>{{ date('d/m/Y', strtotime($date)) }}</td>
                                <td>{{ Str::upper($tablet) }}</td>
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

    <div class="row">
        <div class="col-12 mt-4">
            <h2 class="text-center text-danger2 pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon l'espèce
            </h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Distribution des <strong class="text-danger2">espèces</strong> par commune</h6>
            <canvas id="speciesChart" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Distribution des <strong class="text-danger2">espèces</strong> par commune (Bassila)</h6>
            <canvas id="speciesChartBassila" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Distribution des <strong class="text-danger2">espèces</strong> par commune (Zogbodomey)</h6>
            <canvas id="speciesChartZogbodomey" width="400" height="400"></canvas>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col">
            <h6>Tableau des <strong class="text-danger2">espèces</strong> par commune</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Espèce</th>
                        <th>Nombre d'enregistrements (Bassila)</th>
                        <th>Nombre d'enregistrements (Zogbodomey)</th>
                    </tr>
                </thead>
                <tbody>

                    @php
                        $total_species_bassila = array_sum($species_per_commune_bassila);
                        $total_species_zogbodomey = array_sum($species_per_commune_zogbodomey);
                    @endphp

                    @foreach ($species_per_commune_bassila as $species => $count)
                        @php
                            $total_zogbodomey = $species_per_commune_zogbodomey[$species] ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $species }}</td>
                            <td>{{ $count }}
                                ({{ $total_records != 0 ? round(($count * 100) / $total_records, 2) : 'N/A' }}%)</td>
                            <td>{{ $total_zogbodomey }}
                                ({{ $total_records != 0 ? round(($total_zogbodomey * 100) / $total_records, 2) : 'N/A' }}%)
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong class="text-danger2">Total</strong></td>
                        <td><strong class="text-danger2">{{ $total_species_bassila }}
                                ({{ $total_records != 0 ? round(($total_species_bassila * 100) / $total_records, 2) : 'N/A' }}%)</strong>
                        </td>
                        <td><strong class="text-danger2">{{ $total_species_zogbodomey }}
                                ({{ $total_records != 0 ? round(($total_species_zogbodomey * 100) / $total_records, 2) : 'N/A' }}%)</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-4">
            <h2 class="text-center text-danger2 pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                paramètre "Location"</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Location</strong></h6>
            <canvas id="locationChartAllCommunes" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Location</strong> (Bassila)</h6>
            <canvas id="locationChartBassila" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Location</strong> (Zogbodomey)</h6>
            <canvas id="locationChartZogbodomey" width="400" height="400"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Location</strong></h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>N Moustiques (Bassila)</th>
                        <th>N Moustiques (Zogbodomey)</th>
                    </tr>
                </thead>
                <tbody>


                    @foreach ($location_per_commune_bassila as $location => $count)
                        <tr>
                            <td>{{ $location }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $location_per_commune_zogbodomey[$location] ?? 0 }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td><strong class="text-danger2">Total</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($location_per_commune_bassila) }}</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($location_per_commune_zogbodomey) }}</strong></td>
                    </tr>

                </tbody>
            </table>
        </div>


    </div>

    <div class="row">
        <div class="col-12 mt-4">
            <h2 class="text-center text-danger2 pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                paramètre "Sugar Feeding"</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Sugar Feeding</strong> status</h6>
            <canvas id="sugarFeedingChartAllCommunes" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Sugar Feeding</strong> status (Bassila)</h6>
            <canvas id="sugarFeedingChartBassila" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Sugar Feeding</strong> status (Zogbodomey)</h6>
            <canvas id="sugarFeedingChartZogbodomey" width="400" height="400"></canvas>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col">
            <h6>Tableau de répartition des moustiques par <strong class="text-danger2">Sugar Feeding</strong> status par
                commune</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sugar Feeding Status</th>
                        <th>N Moustiques (Bassila)</th>
                        <th>N Moustiques (Zogbodomey)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sugar_feeding_per_commune_bassila as $site => $count)
                        <tr>
                            <td>{{ $site }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $sugar_feeding_per_commune_zogbodomey[$site] ?? 0 }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td><strong class="text-danger2">Total</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($sugar_feeding_per_commune_bassila) }}</strong>
                        </td>
                        <td><strong class="text-danger2">{{ array_sum($sugar_feeding_per_commune_zogbodomey) }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-4">
            <h2 class="text-center text-danger2 pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                paramètre "Couleur"</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Couleur</strong></h6>
            <canvas id="colourChartAllCommunes" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Couleur</strong> (Bassila)</h6>
            <canvas id="colourChartBassila" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Couleur</strong> (Zogbodomey)</h6>
            <canvas id="colourChartZogbodomey" width="400" height="400"></canvas>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col">
            <h6>Tableau de répartition des moustiques par <strong class="text-danger2">Couleur</strong> par commune</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Couleur</th>
                        <th>N Moustiques (Bassila)</th>
                        <th>N Moustiques (Zogbodomey)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($colour_labels_bassila as $index => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $colour_counts_bassila[$index] ?? 0 }}</td>
                            <td>{{ $colour_counts_zogbodomey[$index] ?? 0 }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td><strong class="text-danger2">Total</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($colour_counts_bassila) }}</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($colour_counts_zogbodomey) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>





    <div class="row">
        <div class="col-12 mt-4">
            <h2 class="text-center text-danger2 pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                paramètre "Feeding Status"</h2>
        </div>
    </div>


    <div class="row mt-4">
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Feeding Status</strong> status</h6>
            <canvas id="feedingStatusChartAllCommunes" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Feeding Status</strong> status (Bassila)</h6>
            <canvas id="feedingStatusChartBassila" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Feeding Status</strong> status (Zogbodomey)</h6>
            <canvas id="feedingStatusChartZogbodomey" width="400" height="400"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Tableau de répartition des moustiques par <strong class="text-danger2">Feeding Status</strong> status
                par commune</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Feeding Status</th>
                        <th>N Moustiques (Bassila)</th>
                        <th>N Moustiques (Zogbodomey)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feeding_status_per_commune_bassila as $site => $count)
                        <tr>
                            <td>{{ $site }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $feeding_status_per_commune_zogbodomey[$site] ?? 0 }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td><strong class="text-danger2">Total</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($feeding_status_per_commune_bassila) }}</strong>
                        </td>
                        <td><strong class="text-danger2">{{ array_sum($feeding_status_per_commune_zogbodomey) }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <div class="row">
        <div class="col-12 mt-4">
            <h2 class="text-center text-danger2 pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                paramètre "Gravid Status"</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Gravid Status</strong> status</h6>
            <canvas id="gravidStatusChartAllCommunes" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Gravid Status</strong> status (Bassila)</h6>
            <canvas id="gravidStatusChartBassila" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Gravid Status</strong> status (Zogbodomey)</h6>
            <canvas id="gravidStatusChartZogbodomey" width="400" height="400"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Tableau de répartition des moustiques par <strong class="text-danger2">Gravid Status</strong> status
                par commune</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Gravid Status</th>
                        <th>N Moustiques (Bassila)</th>
                        <th>N Moustiques (Zogbodomey)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gravid_status_per_commune_bassila as $site => $count)
                        <tr>
                            <td>{{ $site }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $gravid_status_per_commune_zogbodomey[$site] ?? 0 }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td><strong class="text-danger2">Total</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($gravid_status_per_commune_bassila) }}</strong>
                        </td>
                        <td><strong class="text-danger2">{{ array_sum($gravid_status_per_commune_zogbodomey) }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>




    <div class="row">
        <div class="col-12 mt-4">
            <h2 class="text-center text-danger2 pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                paramètre "Living Status"</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Living Status</strong> status</h6>
            <canvas id="livingStatusChartAllCommunes" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Living Status</strong> status (Bassila)</h6>
            <canvas id="livingStatusChartBassila" width="400" height="400"></canvas>
        </div>
        <div class="col">
            <h6>Répartition par <strong class="text-danger2">Living Status</strong> status (Zogbodomey)</h6>
            <canvas id="livingStatusChartZogbodomey" width="400" height="400"></canvas>
        </div>

    </div>

    <div class="row mt-4">
        <div class="col">
            <h6>Tableau de répartition des moustiques par <strong class="text-danger2">Living Status</strong> status
                par commune</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Living Status</th>
                        <th>N Moustiques (Bassila)</th>
                        <th>N Moustiques (Zogbodomey)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($living_status_per_commune_bassila as $site => $count)
                        <tr>
                            <td>{{ $site }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $living_status_per_commune_zogbodomey[$site] ?? 0 }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td><strong class="text-danger2">Total</strong></td>
                        <td><strong class="text-danger2">{{ array_sum($living_status_per_commune_bassila) }}</strong>
                        </td>
                        <td><strong class="text-danger2">{{ array_sum($living_status_per_commune_zogbodomey) }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
@endsection
