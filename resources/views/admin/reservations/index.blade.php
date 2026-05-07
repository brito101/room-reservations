@extends('adminlte::page')

@section('title', '- Reservas de Salas')
@section('plugins.select2', true)
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-fw fa-door-open"></i> Reservas de Salas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Reservas de Salas</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            @include('components.alert')

            {{-- Nova Reserva --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Nova Reserva</h3>
                </div>
                <form method="POST" action="{{ route('admin.reservations.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="col-12 col-md-6 form-group px-0 pr-md-2">
                                <label for="room_id">Sala</label>
                                <x-adminlte-select2 name="room_id" id="room_id">
                                    <option value="" disabled selected>Selecione uma sala…</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}"
                                            {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }} (cap. {{ $room->capacity }})
                                        </option>
                                    @endforeach
                                </x-adminlte-select2>
                            </div>
                            <div class="col-12 col-md-6 form-group px-0 pl-md-2">
                                <label for="title">Título / Motivo</label>
                                <input type="text" class="form-control" id="title" name="title"
                                    placeholder="Ex: Reunião de planejamento" value="{{ old('title') }}" required>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="col-12 col-md-4 form-group px-0 pr-md-2">
                                <label for="date">Data</label>
                                <input type="date" class="form-control" id="date" name="date"
                                    value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-4 form-group px-0 px-md-2">
                                <label for="start_time">Início</label>
                                <input type="time" class="form-control" id="start_time" name="start_time"
                                    value="{{ old('start_time', '08:00') }}" required>
                            </div>
                            <div class="col-12 col-md-4 form-group px-0 pl-md-2">
                                <label for="end_time">Fim</label>
                                <input type="time" class="form-control" id="end_time" name="end_time"
                                    value="{{ old('end_time', '09:00') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check mr-1"></i> Reservar Sala
                        </button>
                    </div>
                </form>
            </div>

            {{-- Lista de Reservas --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> Reservas Existentes</h3>
                </div>

                @php
                    $isAdmin = Auth::user()->hasRole('Programador|Administrador');
                    $heads = [
                        ['label' => '#', 'width' => 5],
                        'Sala',
                        'Título',
                        'Data',
                        'Horário',
                        'Usuário',
                        'Status',
                        ['label' => 'Ações', 'no-export' => true, 'width' => 15],
                    ];
                    $config = [
                        'language' => ['url' => asset('vendor/datatables/js/pt-BR.json')],
                        'responsive' => true,
                        'dom' => '<"d-flex flex-wrap col-12 justify-content-between"Bf>rtip',
                        'order' => [[3, 'desc']],
                        'buttons' => [
                            ['extend' => 'pageLength', 'className' => 'btn-default'],
                            [
                                'extend' => 'copy',
                                'className' => 'btn-default',
                                'text' => '<i class="fas fa-fw fa-lg fa-copy text-secondary"></i>',
                                'titleAttr' => 'Copiar',
                                'exportOptions' => ['columns' => ':not([dt-no-export])'],
                            ],
                            [
                                'extend' => 'print',
                                'className' => 'btn-default',
                                'text' => '<i class="fas fa-fw fa-lg fa-print text-info"></i>',
                                'titleAttr' => 'Imprimir',
                                'exportOptions' => ['columns' => ':not([dt-no-export])'],
                            ],
                            [
                                'extend' => 'csv',
                                'className' => 'btn-default',
                                'text' => '<i class="fas fa-fw fa-lg fa-file-csv text-primary"></i>',
                                'titleAttr' => 'Exportar para CSV',
                                'exportOptions' => ['columns' => ':not([dt-no-export])'],
                            ],
                            [
                                'extend' => 'excel',
                                'className' => 'btn-default',
                                'text' => '<i class="fas fa-fw fa-lg fa-file-excel text-success"></i>',
                                'titleAttr' => 'Exportar para Excel',
                                'exportOptions' => ['columns' => ':not([dt-no-export])'],
                            ],
                            [
                                'extend' => 'pdf',
                                'className' => 'btn-default',
                                'text' => '<i class="fas fa-fw fa-lg fa-file-pdf text-danger"></i>',
                                'titleAttr' => 'Exportar para PDF',
                                'exportOptions' => ['columns' => ':not([dt-no-export])'],
                            ],
                        ],
                    ];
                @endphp

                <div class="card-body">
                    <x-adminlte-datatable id="table-reservations" :heads="$heads" :config="$config"
                        striped hoverable beautify theme="dark">
                        @forelse ($reservations as $reservation)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $reservation->room->name ?? '-' }}</td>
                                <td>{{ $reservation->title }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</td>
                                <td>
                                    {{ \Illuminate\Support\Str::substr($reservation->start_time, 0, 5) }}
                                    – {{ \Illuminate\Support\Str::substr($reservation->end_time, 0, 5) }}
                                </td>
                                <td>{{ $reservation->user->name ?? '-' }}</td>
                                <td>
                                    @if ($reservation->status === 'ativa')
                                        <span class="badge badge-success">Ativa</span>
                                    @else
                                        <span class="badge badge-danger">Cancelada</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($reservation->status !== 'cancelada' && ($isAdmin || $reservation->user_id === Auth::id()))
                                        <a class="btn btn-xs btn-primary mx-1 shadow" title="Editar"
                                            href="{{ route('admin.reservations.edit', $reservation->id) }}">
                                            <i class="fa fa-lg fa-fw fa-pen"></i>
                                        </a>
                                    @endif
                                    @if ($reservation->status === 'ativa' && ($isAdmin || $reservation->user_id === Auth::id()))
                                        <form method="POST" class="d-inline"
                                            action="{{ route('admin.reservations.cancel', $reservation->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-warning mx-1 shadow"
                                                title="Cancelar Reserva"
                                                onclick="return confirm('Confirma o cancelamento desta reserva?')">
                                                <i class="fas fa-lg fa-fw fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($isAdmin || $reservation->user_id === Auth::id())
                                        <form method="POST" class="d-inline"
                                            action="{{ route('admin.reservations.destroy', $reservation->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger mx-1 shadow"
                                                title="Excluir"
                                                onclick="return confirm('Confirma a exclusão permanente desta reserva?')">
                                                <i class="fa fa-lg fa-fw fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Nenhuma reserva cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </x-adminlte-datatable>
                </div>
            </div>

        </div>
    </section>

@endsection
