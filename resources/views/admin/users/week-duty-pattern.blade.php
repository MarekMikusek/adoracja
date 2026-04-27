@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Statystyki stałych posług</h2>

    @foreach(['adoracja' => 'Adoracja Stała', 'rezerwa' => 'Rezerwa Stała'] as $type => $label)
        <div class="card mb-5">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">{{ $label }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Godzina</th>
                                @foreach($days as $day)
                                    <th>{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hours as $hour)
                                <tr>
                                    <td class="fw-bold">{{ $hour }}:00</td>
                                    @foreach($days as $day)
                                        @php
                                            $cell = $stats[$type][$hour][$day];
                                            $namesList = implode(', ', $cell['names']);
                                        @endphp
                                        <td
                                            class="{{ $cell['count'] > 0 ? 'table-success' : '' }}"
                                            title="{{ $namesList ?: 'Brak zapisanych osób' }}"
                                            style="cursor: help;"
                                        >
                                            {{ $cell['count'] ?: '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
