@extends('adminlte::page')

@section('title', '- Dashboard')

@section('plugins.Chartjs', true)
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fa fa-fw fa-digital-tachograph"></i> Dashboard</h1>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Usuários</span>
                            <span class="info-box-number">{{ $users }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-door-open"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Salas Cadastradas</span>
                            <span class="info-box-number">{{ $totalRooms }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reservas Ativas</span>
                            <span class="info-box-number">{{ $activeReservations }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-calendar-day"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reservas Hoje</span>
                            <span class="info-box-number">{{ $todayReservations }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-calendar-week"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reservas nos Próximos 7 Dias</span>
                            <span class="info-box-number">{{ $weekReservations }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-ban"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reservas Canceladas</span>
                            <span class="info-box-number">{{ $cancelledReservations }}</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Gráfico de reservas por mês — visível a todos os perfis --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i> Reservas por Mês
                                <small class="text-muted ml-2">(últimos 12 meses)</small>
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="reservations-chart" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @if (Auth::user()->hasRole('Programador|Administrador'))
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between col-12 align-content-center">
                            <h3 class="card-title align-self-center">Acessos Diário</h3>
                        </div>
                    </div>

                    @php
                        $heads = [
                            ['label' => 'Hora', 'width' => 10],
                            'Página',
                            'IP',
                            'User-Agent',
                            'Plataforma',
                            'Navegador',
                            'Usuário',
                            'Método',
                        ];
                        $config = [
                            'ajax' => url('/admin'),
                            'columns' => [
                                ['data' => 'time', 'name' => 'time'],
                                ['data' => 'url', 'name' => 'url'],
                                ['data' => 'ip', 'name' => 'ip'],
                                ['data' => 'useragent', 'name' => 'useragent'],
                                ['data' => 'platform', 'name' => 'platform'],
                                ['data' => 'browser', 'name' => 'browser'],
                                ['data' => 'name', 'name' => 'name'],
                                ['data' => 'method', 'name' => 'method'],
                            ],
                            'language' => ['url' => asset('vendor/datatables/js/pt-BR.json')],
                            'order' => [0, 'desc'],
                            'destroy' => true,
                            'autoFill' => true,
                            'processing' => true,
                            'serverSide' => true,
                            'responsive' => true,
                            'lengthMenu' => [[10, 50, 100, 500, 1000, -1], [10, 50, 100, 500, 1000, 'Tudo']],
                            'dom' => '<"d-flex flex-wrap col-12 justify-content-between"Bf>rtip',
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
                        <x-adminlte-datatable id="table1" :heads="$heads" :heads="$heads" :config="$config" striped
                            hoverable beautify theme="dark" />
                    </div>
                </div>

                <div class="row px-0">

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0">
                                <div class="d-flex justify-content-between">
                                    <h3 class="card-title">Usuários Online: <span
                                            id="onlineusers">{{ $onlineUsers }}</span></h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex">
                                    <p class="d-flex flex-column">
                                        <span class="text-bold text-lg" id="accessdaily">{{ $access }}</span>
                                        <span>Acessos Diários</span>
                                    </p>
                                    <p class="ml-auto d-flex flex-column text-right">
                                        <span id="percentclass" class="{{ $percent > 0 ? 'text-success' : 'text-danger' }}">
                                            <i id="percenticon"
                                                class="fas {{ $percent > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}  mr-1"></i><span
                                                id="percentvalue">{{ $percent }}</span>%
                                        </span>
                                        <span class="text-muted">em relação ao dia anterior</span>
                                    </p>
                                </div>

                                <div class="position-relative mb-4">
                                    <div class="chartjs-size-monitor">
                                        <div class="chartjs-size-monitor-expand">
                                            <div class=""></div>
                                        </div>
                                        <div class="chartjs-size-monitor-shrink">
                                            <div class=""></div>
                                        </div>
                                    </div>
                                    <canvas id="visitors-chart" style="display: block; width: 489px; height: 200px;"
                                        class="chartjs-render-monitor" width="489" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

        </div>
    </section>
@endsection

@section('custom_js')
    @if (Auth::user()->hasRole('Programador|Administrador'))
        <script>
            const ctx = document.getElementById('visitors-chart');
            if (ctx) {
                ctx.getContext('2d');
                const myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ({!! json_encode($chart->labels) !!}),
                        datasets: [{
                            label: 'Acessos por horário',
                            data: {!! json_encode($chart->dataset) !!},
                            borderWidth: 1,
                            borderColor: '#007bff',
                            backgroundColor: 'transparent'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        legend: {
                            labels: {
                                boxWidth: 0,
                            }
                        },
                    },
                });

                let getData = function() {

                    $.ajax({
                        url: "{{ route('admin.home.chart') }}",
                        type: "GET",
                        success: function(data) {
                            myChart.data.labels = data.chart.labels;
                            myChart.data.datasets[0].data = data.chart.dataset;
                            myChart.update();
                            $("#onlineusers").text(data.onlineUsers);
                            $("#accessdaily").text(data.access);
                            $("#percentvalue").text(data.percent);
                            const percentclass = $("#percentclass");
                            const percenticon = $("#percenticon");
                            percentclass.removeClass('text-success');
                            percentclass.removeClass('text-danger');
                            percenticon.removeClass('fa-arrow-up');
                            percenticon.removeClass('fa-arrow-down');
                            if (parseInt(data.percent) > 0) {
                                percentclass.addClass('text-success');
                                percenticon.addClass('fa-arrow-up');
                            } else {
                                percentclass.addClass('text-danger');
                                percenticon.addClass('fa-arrow-down');
                            }
                        }
                    });
                };
                setInterval(getData, 10000);
            }
        </script>
    @endif

    <script>
        const ctxRes = document.getElementById('reservations-chart');
        if (ctxRes) {
            new Chart(ctxRes, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($reservationChart->labels) !!},
                    datasets: [{
                            label: 'Ativas',
                            data: {!! json_encode($reservationChart->ativas) !!},
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Canceladas',
                            data: {!! json_encode($reservationChart->canceladas) !!},
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            },
                        }
                    }
                }
            });
        }
    </script>
@endsection
