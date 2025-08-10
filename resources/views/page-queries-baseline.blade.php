@extends('accueil')

@section('content')
    <div class="container mt-5">
        <h2>Page Queries Baseline</h2>
        <p>Bienvenue sur la page de requêtes de base.</p>

        <div class="row">
            <div class="col-md-12">
                <p>Cette page est dédiée à la gestion des requêtes de base pour les projets sélectionnés.</p>
            </div>
        </div>

        {{-- Add your content here --}}

        {{-- display queries issues in a table --}}
        <div class="table-responsive row">
            <table class="table table-striped col">
                <thead>
                    <tr>
                        <th>Issue</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($issues as $issue)
                        <tr>
                            <td>{{ $issue }}</td>
                            <td>NOT OKAY</td>
                            <td>{{ date('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    @endsection
