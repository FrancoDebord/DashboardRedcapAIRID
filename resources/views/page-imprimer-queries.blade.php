<div class="container mt-2">
    <div class="row">
        @php
            $tablet_id = request()->get('tablet_id', '');
            $project_id = request()->get('project_id', '');
            $formulaire_name = request()->get('formulaire_name', '');

            switch ($project_id) {
                case 31:
                    $project_title = 'ATSB An. Gambiae Baseline';
                    break;
                case 35:
                    $project_title = 'ATSB Other Species Baseline';
                    break;
                case 38:
                    $project_title = 'ATSB An. Gambiae FINAL';
                    break;
                case 40:
                    $project_title = 'ATSB ALL MOSQUITOES FINAL';
                    break;
                // Add more cases as needed
                default:
                    $project_title = 'Aucun Projet';
                    break;
            }
        @endphp

        <div class="col-12">
            <h2>Liste des queries pour le projet <strong class="text-danger">{{ $project_title }}</strong> </h2>

            <p class="mt-2">Ces queries sont assignées à : <strong class="text-danger">{{ $tablet_id }}</strong></p>
            <p class="mt-2">Exporté ce {{ date('d/m/Y') }} par XXX</p>
        </div>

        @if ($tablet_id != '' && $formulaire_name != '')
            @forelse ($issues as $issue)
                @if ($tablet_id == $issue['tablet_id'] && $formulaire_name == $issue['form'])
                    <div class="col-12 mt-2">
                        <h4>{{ $issue['query_code'] }}</h4>
                        <ul>
                            <li> <strong>Query Code : </strong> {{ $issue['query_date'] }}</li>
                            <li><strong>Mosquito Code : </strong> {{ $issue['mosquito_code'] }}</li>
                            <li> <strong>Tablet ID : </strong> {{ $issue['tablet_id'] }}</li>
                            <li> <strong>Event Name : </strong> {{ $issue['redcap_event_name'] ?? 'NA' }}</li>
                            <li> <strong>Formulaire : </strong> {{ $issue['form'] ?? 'NA' }}</li>
                            <li> <strong>Description : </strong> {!! $issue['description'] ?? 'NA' !!}</li>
                                <li> <strong>Suggestion : </strong> {!!  $issue['suggestion'] ?? 'NA' !!}</li>
                            <li>
                                <strong>Status</strong> : Non résolu
                            </li>
                        </ul>
                    </div>
                @endif
            @empty
            @endforelse
        @else
            @if ($tablet_id != '')
                @forelse ($issues as $issue)
                    @if ($tablet_id == $issue['tablet_id'])
                        <div class="col-12 mt-2">
                            <h4>{{ $issue['query_code'] }}</h4>
                            <ul>
                                <li> <strong>Query Code : </strong> {{ $issue['query_date'] }}</li>
                                <li><strong>Mosquito Code : </strong> {{ $issue['mosquito_code'] }}</li>
                                <li> <strong>Tablet ID : </strong> {{ $issue['tablet_id'] }}</li>
                                <li> <strong>Event Name : </strong> {{ $issue['redcap_event_name'] ?? 'NA' }}</li>
                                <li> <strong>Formulaire : </strong> {{ $issue['form'] ?? 'NA' }}</li>
                                <li> <strong>Description : </strong> {!! $issue['description'] ?? 'NA' !!}</li>
                                <li> <strong>Suggestion : </strong> {!!  $issue['suggestion'] ?? 'NA' !!}</li>
                                <li>
                                    <strong>Status</strong> : Non résolu
                                </li>
                            </ul>
                        </div>
                    @endif
                @empty
                @endforelse
            @else
                @if ($formulaire_name != '')
                    @forelse ($issues as $issue)
                        @if ($formulaire_name == $issue['form'])
                            <div class="col-12 mt-2">
                                <h4>{{ $issue['query_code'] }}</h4>
                                <ul>
                                    <li> <strong>Query Code : </strong> {{ $issue['query_date'] }}</li>
                                    <li><strong>Mosquito Code : </strong> {{ $issue['mosquito_code'] }}</li>
                                    <li> <strong>Tablet ID : </strong> {{ $issue['tablet_id'] }}</li>
                                    <li> <strong>Event Name : </strong> {{ $issue['redcap_event_name'] ?? 'NA' }}</li>
                                    <li> <strong>Formulaire : </strong> {{ $issue['form'] ?? 'NA' }}</li>
                                    <li> <strong>Description : </strong> {!! $issue['description'] ?? 'NA' !!}</li>
                                <li> <strong>Suggestion : </strong> {!!  $issue['suggestion'] ?? 'NA' !!}</li>
                                    <li>
                                        <strong>Status</strong> : Non résolu
                                    </li>
                                </ul>
                            </div>
                        @endif
                    @empty
                    @endforelse
                @else
                    @forelse ($issues as $issue)
                        <div class="col-12 mt-2">
                            <h4>{{ $issue['query_code'] }}</h4>
                            <ul>
                                <li> <strong>Query Code : </strong> {{ $issue['query_date'] }}</li>
                                <li><strong>Mosquito Code : </strong> {{ $issue['mosquito_code'] }}</li>
                                <li> <strong>Tablet ID : </strong> {{ $issue['tablet_id'] }}</li>
                                <li> <strong>Initials : </strong> {{ $issue['initials'] }}</li>
                                <li> <strong>Event Name : </strong> {{ $issue['redcap_event_name'] ?? 'NA' }}</li>
                                <li> <strong>Formulaire : </strong> {{ $issue['form'] ?? 'NA' }}</li>
                                <li> <strong>Description : </strong> {!! $issue['description'] ?? 'NA' !!}</li>
                                <li> <strong>Suggestion : </strong> {!!  $issue['suggestion'] ?? 'NA' !!}</li>
                                <li>
                                    <strong>Status</strong> : Non résolu
                                </li>
                            </ul>
                        </div>
                    @empty
                    @endforelse
                @endif
            @endif
        @endif


    </div>
</div>


<script>
    window.print();
</script>
