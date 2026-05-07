@extends('adminlte::page')

@section('title', '- Editar Reserva')
@section('plugins.select2', true)

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-fw fa-door-open"></i> Editar Reserva</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reservations.index') }}">Reservas de Salas</a></li>
                        <li class="breadcrumb-item active">Editar Reserva</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    @include('components.alert')

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dados da Reserva</h3>
                        </div>

                        <form method="POST" action="{{ route('admin.reservations.update', $reservation->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="card-body">

                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-12 col-md-6 form-group px-0 pr-md-2">
                                        <label for="room_id">Sala</label>
                                        <x-adminlte-select2 name="room_id" id="room_id">
                                            <option value="" disabled>Selecione uma sala…</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->id }}"
                                                    {{ (old('room_id') ?? $reservation->room_id) == $room->id ? 'selected' : '' }}>
                                                    {{ $room->name }} (cap. {{ $room->capacity }})
                                                </option>
                                            @endforeach
                                        </x-adminlte-select2>
                                    </div>
                                    <div class="col-12 col-md-6 form-group px-0 pl-md-2">
                                        <label for="title">Título / Motivo</label>
                                        <input type="text" class="form-control" id="title" name="title"
                                            placeholder="Ex: Reunião de planejamento"
                                            value="{{ old('title') ?? $reservation->title }}" required>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-12 col-md-3 form-group px-0 pr-md-2">
                                        <label for="date">Data</label>
                                        <input type="date" class="form-control" id="date" name="date"
                                            value="{{ old('date') ?? $reservation->date }}" required>
                                    </div>
                                    <div class="col-12 col-md-3 form-group px-0 px-md-2">
                                        <label for="start_time">Início</label>
                                        <input type="time" class="form-control" id="start_time" name="start_time"
                                            value="{{ old('start_time') ?? \Illuminate\Support\Str::substr($reservation->start_time, 0, 5) }}"
                                            required>
                                    </div>
                                    <div class="col-12 col-md-3 form-group px-0 px-md-2">
                                        <label for="end_time">Fim</label>
                                        <input type="time" class="form-control" id="end_time" name="end_time"
                                            value="{{ old('end_time') ?? \Illuminate\Support\Str::substr($reservation->end_time, 0, 5) }}"
                                            required>
                                    </div>
                                    <div class="col-12 col-md-3 form-group px-0 pl-md-2">
                                        @if ($isAdmin)
                                            <label for="status">Status</label>
                                            <x-adminlte-select2 name="status" id="status">
                                                <option value="ativa"     {{ (old('status') ?? $reservation->status) === 'ativa'     ? 'selected' : '' }}>Ativa</option>
                                                <option value="cancelada" {{ (old('status') ?? $reservation->status) === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                            </x-adminlte-select2>
                                        @endif
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Salvar Alterações
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
