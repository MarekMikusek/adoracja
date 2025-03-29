@extends('layouts.app')

@section('navigation')
    @include('layouts.navigation')
@endsection

@section('style')
    a {
    text-decoration: none;
    }
@endsection

@section('content')
    <div class="container" style="border:solid red 1px;">
        <h1>Admin Dashboard</h1>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="sticky-col">Godziny</th>
                        @foreach ($duties as $date => $duty)
                            <th>{{ $date }}</br>
                                {{ $duty['dayName'] }} </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dayHours as $hour)
                        <tr>
                            <td class="sticky-col text-nowrap">{{ $hour }}-{{ $hour + 1 }}</td>
                            @foreach ($duties as $date => $duty)
                                <td class="editable-cell"
                                    style="background-color:{{ $admins[$duty['timeFrames'][$hour]['admin_id']]->color }};"
                                    title="admin: {{ $admins[$duty['timeFrames'][$hour]['admin_id']]->name }}"
                                    data-href="{{ route('admin.current-duty.edit', ['duty' => $duty['timeFrames'][$hour]['duty_id']]) }}">
                                    {{ $duty['timeFrames'][$hour]['adoracja'] }}
                                    ({{ $duty['timeFrames'][$hour]['gotowość'] }})
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('td').on('dblclick', function() {
                const l = $(this).data('href');
                console.log(l);
                window.location.href = $(this).data('href');
            })
        });
    </script>
@endsection
