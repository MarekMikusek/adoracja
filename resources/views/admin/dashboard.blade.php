@extends('layouts.app')

@section('style')
    <style>
        a {
            text-decoration: none;
        }

        .table-container {
            max-height: 500px;
            /* Set max height to enable scrolling */
            overflow: auto;
            position: relative;
        }

        /* Sticky header */
        .table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            /* Ensure the header is above the content */
        }

        /* Sticky first column */
        .table td:first-child,
        .table th:first-child {
            position: sticky;
            left: 0;
            background-color: #f8f9fa;

            z-index: 2;
            /* Ensure the first column is above other cells */
        }

        /* Optional: Add a border to separate the first column */
        .table td:first-child,
        .table th:first-child {
            border-right: 2px solid #ddd;
        }

        .no-wrap {
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="container" style="border:solid red 1px;">
        <h1>Admin Dashboard</h1>
        <div class="table-responsive">
            <div class="table-container">
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
                                <td class="sticky-col text-nowrap no-wrap">{{ $hour }}-{{ $hour + 1 }}</td>
                                @foreach ($duties as $date => $duty)
                                    <td class="editable-cell"
                                        style="background-color:{{ $admins[$duty['timeFrames'][$hour]['admin_id']]->color }};"
                                        title="admin: {{ $admins[$duty['timeFrames'][$hour]['admin_id']]->name }}"
                                        data-href="{{ route('admin.current-duty.edit', ['duty' => $duty['timeFrames'][$hour]['duty_id']]) }}">
                                        {{ $duty['timeFrames'][$hour]['adoracja'] }}
                                        ({{ $duty['timeFrames'][$hour]['rezerwa'] }})
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
