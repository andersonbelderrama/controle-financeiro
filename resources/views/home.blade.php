@extends('layout')

@section('title', 'Dashboard')

@section('css')
    
@endsection

@section('scripts')
<!-- AdminLTE for demo purposes -->
<script src="{{asset('dist/js/demo.js')}}"></script> 
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
{{-- <script src="{{asset('dist/js/pages/dashboard.js')}}"></script> --}}


<script>

var url = "{{ route('home.chart')}}";
var Mes = new Array();
var novoMes = new Array();
var Pagamentos = new Array();
var Recebimentos = new Array();



$(document).ready(function(){
    /* ChartJS
    * -------
    * Here we will create a few charts using ChartJS
    */

    //--------------
    //- AREA CHART -
    //--------------


    $.get(url, function(response){
        response.reverse();
        console.log(response);
    response.forEach(function(data){
        
        if(data.tipo == 'pagamento'){
            Pagamentos.push(data.valor);
        }

        if(data.tipo == 'recebimento'){
            Recebimentos.push(data.valor);
        }

        Mes.push(data.mes_ano);


        $.each(Mes, function(i, el){
            if($.inArray(el, novoMes) === -1) novoMes.push(el);
        });

        
    });

        // Get context with jQuery - using jQuery's .get() method.
		

        var areaChartCanvas = $('#grafico-mensal-canvas').get(0).getContext('2d')

        var areaChartData = {
        labels  : novoMes,
        datasets: [
            {
            label               : 'Recebimentos',
            fill                : false,
            backgroundColor     : '#28a745',
            borderColor         : '#28a745',
            pointRadius         : 3,
            pointHoverRadius    : 6,
            pointColor          : '#28a745',
            pointStrokeColor    : 'rgba(60,141,188,1)',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(60,141,188,1)',
            data                : Recebimentos
            },
            {
            label               : 'Pagamentos',
            fill                : false,
            backgroundColor     : '#dc3545',
            borderColor         : '#dc3545',
            pointRadius         : 3,
            pointHoverRadius    : 6,
            pointColor          : '#dc3545',
            pointStrokeColor    : '#c1c7d1',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(220,220,220,1)',
            data                : Pagamentos
            },
        ]
        }

        var areaChartOptions = {
        maintainAspectRatio : false,
        responsive : true,
        tooltips: {
            mode: 'nearest',
            intersect: false,
        },     
        hover: {
					mode: 'nearest',
					intersect: true
				},
        scales: {
            xAxes: [{
                display:true,
                scaleLabel : {
                    display : true,
                    labelString: 'Meses/Ano'
                }
            }],
            yAxes: [{
                display:true,
                scaleLabel : {
                display : true,
                labelString: 'Valores (R$)'
                }
            }]
        }
        }

        // This will get the first returned node in the jQuery collection.
        var areaChart       = new Chart(areaChartCanvas, { 
        type: 'line',
        data: areaChartData, 
        options: areaChartOptions
        })
    })
})
</script>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
        <div class="inner">
            <h3>R$ {{$v_saldo}}</h3>

            <p>Saldo</p>
        </div>
        <div class="icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <a href="#" class="small-box-footer">Mais infomações <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
        <div class="inner">
            <h3>R$ {{ $v_recebimentos }}</h3>

            <p>Recebimentos/Mês</p>
            
        </div>
        <div class="icon">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <a href="#" class="small-box-footer">Mais infomações <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
        <div class="inner">
            <h3>R$ {{$v_pagamentos_p}}</h3>

            <p>Pendencias de Pagamento</p>
        </div>
        <div class="icon">
            <i class="far fa-clock"></i>
        </div>
        <a href="#" class="small-box-footer">Mais infomações <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
        <div class="inner">
            <h3>R$ {{$v_pagamentos_r}}</h3>

            <p>Pagamentos Realizados/Mês</p>
        </div>
        <div class="icon">
            <i class="fas fa-receipt"></i>
        </div>
        <a href="#" class="small-box-footer">Mais infomações <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-12 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
        <div class="card-header">
            <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Resumo Financeiro
            </h3>
        </div><!-- /.card-header -->
        <div class="card-body">
            <div class="tab-content p-0">
                <!-- Morris chart - Sales -->
                <div class="chart tab-pane active" id="grafico-mensal" style="position: relative; height: 300px;">
                    <canvas id="grafico-mensal-canvas" height="300" style="height: 300px;"></canvas>
                </div>
            </div>
        </div><!-- /.card-body -->
        </div>
        {{-- proximo-card --}}
    </section>
    <!-- right col -->
</div>
<!-- /.row (main row) -->
@endsection