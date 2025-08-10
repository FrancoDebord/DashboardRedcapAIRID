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

            <input type="hidden" name="project_id" id="project_id" value="{{ $project_id }}">
            
            <div class="col d-flex align-items-center">
                <span class="me-2 fw-bold">Projets :</span>
                @foreach ($projects as $project)

                @php

                    $route = "pullDataFromRedCap";
                    if ($project['id'] == "38" ){
                        $route = 'pullDataFromRedCapAnGambiaeFINAL';
                    } elseif ($project['id'] == "40") {
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

        <div class="row">
            <div class="col-12 mt-2">
                <h2 class="text-center text-danger">Tableau de bord des données collectées</h2>
                <p class="text-center">Sélectionnez un projet pour visualiser les données collectées.</p>
            </div>
        </div>

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

                        @php
                            $total_records_per_date_tablet = 0;
                        @endphp
                        @foreach ($records_per_date_tablet as $date => $tablets)
                            @foreach ($tablets as $tablet => $count)
                                <tr>
                                    <td>{{ date("d/m/Y",strtotime($date)) }}</td>
                                    <td>{{ Str::upper($tablet) }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2"><strong class="text-danger">Total pour {{ date('d/m/Y', strtotime($date)) }}</strong></td>
                                <td><strong class="text-danger">{{ array_sum( array_column($tablets, 'count')) }}</strong></td>
                            </tr>
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
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon l'espèce
                </h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Distribution des <strong class="text-danger">espèces</strong> par commune</h6>
                <canvas id="speciesChart" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Distribution des <strong class="text-danger">espèces</strong> par commune (Bassila)</h6>
                <canvas id="speciesChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Distribution des <strong class="text-danger">espèces</strong> par commune (Zogbodomey)</h6>
                <canvas id="speciesChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <h6>Tableau des <strong class="text-danger">espèces</strong> par commune</h6>
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
                            <tr>
                                <td>{{ $species }}</td>
                                <td>{{ $count }}</td>
                                <td>{{ $species_per_commune_zogbodomey[$species] ?? 0 }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ $total_species_bassila }}</strong></td>
                            <td><strong class="text-danger">{{ $total_species_zogbodomey }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mt-4">
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                    paramètre "Location"</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Location</strong></h6>
                <canvas id="locationChartAllCommunes" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Location</strong> (Bassila)</h6>
                <canvas id="locationChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Location</strong> (Zogbodomey)</h6>
                <canvas id="locationChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Location</strong></h6>
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
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ array_sum($location_per_commune_bassila) }}</strong></td>
                            <td><strong class="text-danger">{{ array_sum($location_per_commune_zogbodomey) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>


        </div>

        <div class="row">
            <div class="col-12 mt-4">
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                    paramètre "Sugar Feeding"</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Sugar Feeding</strong> status</h6>
                <canvas id="sugarFeedingChartAllCommunes" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Sugar Feeding</strong> status (Bassila)</h6>
                <canvas id="sugarFeedingChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Sugar Feeding</strong> status (Zogbodomey)</h6>
                <canvas id="sugarFeedingChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <h6>Tableau de répartition des moustiques par <strong class="text-danger">Sugar Feeding</strong> status par
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
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ array_sum($sugar_feeding_per_commune_bassila) }}</strong>
                            </td>
                            <td><strong class="text-danger">{{ array_sum($sugar_feeding_per_commune_zogbodomey) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mt-4">
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                    paramètre "Couleur"</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Couleur</strong></h6>
                <canvas id="colourChartAllCommunes" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Couleur</strong> (Bassila)</h6>
                <canvas id="colourChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Couleur</strong> (Zogbodomey)</h6>
                <canvas id="colourChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <h6>Tableau de répartition des moustiques par <strong class="text-danger">Couleur</strong> par commune</h6>
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
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ array_sum($colour_counts_bassila) }}</strong></td>
                            <td><strong class="text-danger">{{ array_sum($colour_counts_zogbodomey) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>


        <div class="row">
            <div class="col-12 mt-4">
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                    paramètre "Sexe"</h2>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Sexe</strong></h6>
                <canvas id="sexChartAllCommunes" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Sexe</strong> (Bassila)</h6>
                <canvas id="sexChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Sexe</strong> (Zogbodomey)</h6>
                <canvas id="sexChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Tableau de répartition des moustiques par <strong class="text-danger">Sexe</strong> par commune</h6>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Sexe</th>
                            <th>N Moustiques (Bassila)</th>
                            <th>N Moustiques (Zogbodomey)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sex_per_commune_bassila as $sex => $count)
                            <tr>
                                <td>{{ $sex }}</td>
                                <td>{{ $count }}</td>
                                <td>{{ $sex_per_commune_zogbodomey[$sex] ?? 0 }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ array_sum($sex_per_commune_bassila) }}</strong></td>
                            <td><strong class="text-danger">{{ array_sum($sex_per_commune_zogbodomey) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>





        <div class="row">
            <div class="col-12 mt-4">
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                    paramètre "Feeding Status"</h2>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Feeding Status</strong> status</h6>
                <canvas id="feedingStatusChartAllCommunes" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Feeding Status</strong> status (Bassila)</h6>
                <canvas id="feedingStatusChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Feeding Status</strong> status (Zogbodomey)</h6>
                <canvas id="feedingStatusChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Tableau de répartition des moustiques par <strong class="text-danger">Feeding Status</strong> status
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
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ array_sum($feeding_status_per_commune_bassila) }}</strong>
                            </td>
                            <td><strong class="text-danger">{{ array_sum($feeding_status_per_commune_zogbodomey) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <div class="row">
            <div class="col-12 mt-4">
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                    paramètre "Gravid Status"</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Gravid Status</strong> status</h6>
                <canvas id="gravidStatusChartAllCommunes" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Gravid Status</strong> status (Bassila)</h6>
                <canvas id="gravidStatusChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Gravid Status</strong> status (Zogbodomey)</h6>
                <canvas id="gravidStatusChartZogbodomey" width="400" height="400"></canvas>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Tableau de répartition des moustiques par <strong class="text-danger">Gravid Status</strong> status
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
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ array_sum($gravid_status_per_commune_bassila) }}</strong>
                            </td>
                            <td><strong class="text-danger">{{ array_sum($gravid_status_per_commune_zogbodomey) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>




        <div class="row">
            <div class="col-12 mt-4">
                <h2 class="text-center text-danger pb-2" style="border-bottom: 3px dashed #ccc">Distribution selon le
                    paramètre "Living Status"</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Living Status</strong> status</h6>
                <canvas id="livingStatusChartAllCommunes" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Living Status</strong> status (Bassila)</h6>
                <canvas id="livingStatusChartBassila" width="400" height="400"></canvas>
            </div>
            <div class="col">
                <h6>Répartition par <strong class="text-danger">Living Status</strong> status (Zogbodomey)</h6>
                <canvas id="livingStatusChartZogbodomey" width="400" height="400"></canvas>
            </div>

        </div>

        <div class="row mt-4">
            <div class="col">
                <h6>Tableau de répartition des moustiques par <strong class="text-danger">Living Status</strong> status
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
                            <td><strong class="text-danger">Total</strong></td>
                            <td><strong class="text-danger">{{ array_sum($living_status_per_commune_bassila) }}</strong>
                            </td>
                            <td><strong class="text-danger">{{ array_sum($living_status_per_commune_zogbodomey) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>


        </div>
@endsection
