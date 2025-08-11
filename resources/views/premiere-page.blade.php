@extends('accueil')

@section('content')
    <div class="container">
        @php
            // $projects = [
            //     ['id' => 31, 'name' => 'ATSB An. Gambiae Baseline'],
            //     ['id' => 35, 'name' => 'ATSB Other Species Baseline'],
            //     ['id' => 38, 'name' => 'ATSB An. Gambiae FINAL'],
            //     ['id' => 40, 'name' => 'ATSB ALL MOSQUITOES FINAL'],
            // ];

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
    </div>
@endsection
