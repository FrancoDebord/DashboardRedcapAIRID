@extends('accueil')

<style>
    td.details-control {
        background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }

    tr.shown td.details-control {
        background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
    }
</style>
@section('content')
    <div class="container mt-2 mb-2">
        <h2 class="text-info">Pages des différentes erreurs constatées dans les données</h2>


        <div class="row mb-3 mt-3">

            @php
                $project_id = $project_id_courant ?? request()->get('project_id', '');
            @endphp
            <input type="hidden" name="project_id" id="project_id" value="{{ $project_id }}">

            <div class="col d-flex align-items-center">
                <span class="me-2 fw-bold">Projets :</span>

                @foreach ($projects as $project)
                    @php

                        $route = route('pullQueriesDataREDCapBaseline', ['project_id' => $project['id']]);
                        if ($project['id'] == '38') {
                            $route = route('pullQueriesDataREDCapAnGambiaeFINAL');
                        } elseif ($project['id'] == '40') {
                            $route = route('pullQueriesDataREDCapALlMosquitoesFINAL');
                        }

                    @endphp


                    <a href="{{ $route }}"
                        class="btn btn-outline-primary me-2 {{ $project_id == $project['id'] ? 'active' : '' }}">
                        {{ $project['name'] }}
                    </a>
                @endforeach
            </div>
        </div>


        <div class="row mt-2 mb-3">
            <div class="col">
                <form action="#" class="p-2" style="border : 1px dashed #ddd">
                    @csrf

                    <div class="row">
                        <div class="form-group col">
                            <label for="tablet_id">
                                <strong>Sélectionner une tablette</strong>
                            </label>

                            <select name="tablet_id" id="tablet_id" class="form-control">
                                <option value="">Sélectionner</option>
                                @forelse ($tablets as $tablet)
                                    <option value="{{ $tablet }}">{{ $tablet }}</option>
                                @empty
                                @endforelse
                            </select>

                        </div>

                        <div class="form-group col">
                            <label for="formulaire_name">
                                <strong>Sélectionner un formulaire </strong>
                            </label>

                            <select name="formulaire_name" id="formulaire_name" class="form-control">
                                <option value="">Sélectionner</option>
                                @forelse ($formulaires as $formulaire)
                                    <option value="{{ $formulaire }}">{{ $formulaire }}</option>
                                @empty
                                @endforelse
                            </select>

                        </div>

                        <div class="col form-group mt-4">
                            <input type="hidden" name="route_export" id="route_exporter_queries"
                                value="{{ route('exporterQueries', ['project_id' => $project_id]) }}" />
                            <a href="{{ route('exporterQueries', ['project_id' => $project_id]) }}" target="_blank"
                                class="btn btn-danger" id="expoter_queries">Exporter les queries</a>

                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- display queries issues in a table --}}
        <div class="table-responsive row mt-4">
            <table class="table table-striped col table-bordered" id="table-queries">
                <thead>
                    <tr>
                        <th></th>
                        {{-- <th><input type="checkbox" id="select-all"></th> <!-- Checkbox globale --> --}}
                        <th>Query Code</th>
                        <th>Query Date</th>
                        <th>Mosquito Code</th>
                        <th>Tablet ID</th>
                        <th>Initials</th>
                        <th>Event</th>
                        <th>Form</th>
                        <th>Instance</th>
                        {{-- A cacher --}}
                        <th>Description</th>
                        <th>Suggestion</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($issues as $issue)
                        <tr>
                            {{-- <td></td> --}}
                            <td class="details-control"></td>
                            <td>{{ $issue['query_code'] }}</td>
                            <td>{{ $issue['query_date'] }}</td>
                            <td>{{ $issue['mosquito_code'] }}</td>
                            <td>{{ $issue['tablet_id'] }}</td>
                            <td>{{ $issue['initials'] }}</td>
                            <td>{{ $issue['redcap_event_name'] }}</td>
                            <td>{{ $issue['form'] }}</td>
                            <td>{{ $issue['instance'] }}</td>
                            <td>{!! $issue['description'] !!}</td>
                            <td>{!! $issue['suggestion'] !!}</td>
                            <td>
                                <strong class="text-danger">Non résolu</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    @endsection

    @section('js')
        <script>
            $(function() {


                var app_url = $('meta[name="app_url"]').attr('content');



                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });


                let table = null;

                function getQueries() {


                    // Bar chart for data sent each day
                    $.ajax({
                        url: app_url + "pull-queries-ajax",
                        method: 'GET',
                        dataType: 'json',

                        success: function(response) {


                            table = $('#table-queries').DataTable({

                                pageLength: 100, // nombre de lignes par page
                                "columns": [{
                                        "className": 'details-control',
                                        "orderable": false,
                                        "data": null,
                                        "defaultContent": ''
                                    },
                                    {
                                        "data": "query_code"
                                    },
                                    {
                                        "data": "query_date"
                                    },
                                    {
                                        "data": "mosquito_code"
                                    },
                                    {
                                        "data": "tablet_id"
                                    },
                                    {
                                        "data": "initials"
                                    },
                                    {
                                        "data": "redcap_event_name"
                                    },
                                    {
                                        "data": "form"
                                    },
                                    {
                                        "data": "instance"
                                    },
                                    // Colonnes masquées
                                    {
                                        "data": "description",
                                        "visible": false
                                    },
                                    {
                                        "data": "suggestion",
                                        "visible": false
                                    },
                                    {
                                        "data": "status",
                                        "visible": false
                                    }
                                ],
                                // columnDefs: [{
                                //     targets: 0, // première colonne
                                //     render: function(data, type, row) {
                                //         return '<input type="checkbox" class="row-checkbox " value="' +
                                //             row.id +
                                //             '">';
                                //     }
                                // }]
                            });
                            table.clear();
                            table.rows.add(response.issues).draw();

                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', error);
                        }
                    });

                }


                // Gestion du clic sur le bouton détail
                $('#table-queries tbody').on('click', 'td.details-control', function() {

                    var tr = $(this).closest('tr');
                    var row = table.row(tr);

                    if (row.child.isShown()) {

                        // Fermer la ligne
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        // Ouvrir la ligne et afficher les détails
                        row.child(format(row.data())).show();
                        tr.addClass('shown');
                    }
                });

                // // Fonction pour formater les détails à afficher
                function format(d) {
                    return '<div class="details-content" style="padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">' +
                        '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
                        '<tr>' +
                        '<td><strong>Description:</strong></td>' +
                        '<td>' + d.description + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td><strong>Suggestion:</strong></td>' +
                        '<td>' + d["suggestion"] + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td><strong>Statut:</strong></td>' +
                        '<td>Non Résolu</td>' +
                        '</tr>' +
                        '</table>' +
                        '</div>';
                }


                $("#tablet_id").on("change", function(e) {

                    var tablet_id = $(this).val();
                    var formulaire_name = $("#formulaire_name").val();

                    var route = $("#route_exporter_queries").val();
                    var search_value = "";

                    if (tablet_id != "" && formulaire_name != "") {
                        $("#expoter_queries").attr("href", route + "&tablet_id=" + tablet_id +
                            "&formulaire_name=" + formulaire_name);

                        table.column(4).search(tablet_id).draw();
                        table.column(7).search(formulaire_name).draw();
                    } else {
                        if (tablet_id != "") {
                            $("#expoter_queries").attr("href", route + "&tablet_id=" + tablet_id);
                            table.column(4).search(tablet_id).draw();
                        } else {
                            $("#expoter_queries").attr("href", route + "&formulaire_name=" + formulaire_name);
                            table.column(7).search(formulaire_name).draw();

                        }
                    }


                });

                $("#formulaire_name").on("change", function(e) {

                    var tablet_id = $("#tablet_id").val();
                    var formulaire_name = $(this).val();
                    var route = $("#route_exporter_queries").val();
                    var search_value = "";


                    if (tablet_id != "" && formulaire_name != "") {
                        $("#expoter_queries").attr("href", route + "&tablet_id=" + tablet_id +
                            "&formulaire_name=" + formulaire_name);

                        table.column(4).search(tablet_id).draw();
                        table.column(7).search(formulaire_name).draw();
                    } else {
                        if (tablet_id != "") {
                            $("#expoter_queries").attr("href", route + "&tablet_id=" + tablet_id);

                            table.column(4).search(tablet_id).draw();
                        } else {
                            $("#expoter_queries").attr("href", route + "&formulaire_name=" + formulaire_name);
                            table.column(7).search(formulaire_name).draw();
                        }
                    }

                });

                getQueries();
            })
        </script>
    @endsection
