<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescriptionRelease;
use App\Models\PaymentInput;
use App\Models\PaymentOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
use Illuminate\Support\Str;


class HomeController extends Controller
{
    public function index()
    {
        
        //Calculo Saldo
            //Recebimento Total
            $query_inputs = DB::table('payment_inputs')
            ->whereNotNull('payment_date')
            ->sum('amount');

            //Pagamento Total
            $query_outputs = DB::table('payment_outputs')
            ->whereNotNull('payment_date')
            ->sum('amount');

        $saldo = $query_inputs - $query_outputs;
        $v_saldo = number_format($saldo, 2, ',', '.');
        

        //Recebimento Mês Atual
        $recebimentos = DB::table('payment_inputs')
        ->whereNotNull('payment_date')
        ->whereMonth('payment_date', Carbon::now()->month)
        ->whereYear('payment_date', Carbon::now()->year)
        ->sum('amount');
        $v_recebimentos= number_format($recebimentos, 2, ',', '.');

        //Pagamentos Pendentes
        $pagamentos_p = DB::table('payment_outputs')
        ->whereNull('payment_date')
        ->sum('amount');
        $v_pagamentos_p = number_format($pagamentos_p, 2, ',', '.');
        
        //Pagamentos Realizados
        $pagamentos_r = DB::table('payment_outputs')
        ->whereNotNull('payment_date')
        ->whereMonth('payment_date', Carbon::now()->month)
        ->whereYear('payment_date', Carbon::now()->year)
        ->sum('amount');
        $v_pagamentos_r = number_format($pagamentos_r, 2, ',', '.');

        
        

        return View::make('home')
        ->with(compact('v_saldo'))
        ->with(compact('v_recebimentos'))
        ->with(compact('v_pagamentos_p'))
        ->with(compact('v_pagamentos_r'));


    }

    public function chart(){
        //  $result = [
        //      [
        //          'mes'           => 'Outubro',
        //          'recebimentos'  => '2550',
        //          'pagamentos'    => '1880',
        //      ],
        //      [
        //          'mes'           => 'Novembro',
        //          'recebimentos'  => '3300',
        //          'pagamentos'    => '500',
        //      ]
        //  ];
        
            //$teste = Carbon::parse('2020-02-02')->monthName;
            //

        $result = DB::select(DB::Raw("
        
            SELECT a.tipo AS tipo,
            CONCAT((MONTHNAME(a.mes)),'/',YEAR(a.mes)) AS mes_ano,
            a.valor AS valor

            FROM	

            (

            SELECT
            'recebimento' AS tipo,
            payment_date AS mes,
            SUM(amount) AS valor

            FROM payment_inputs

            WHERE payment_date IS NOT NULL

            GROUP BY YEAR(payment_date), MONTH(payment_date)


            UNION ALL	


            SELECT
            'pagamento' AS tipo,
            payment_date AS mes,
            SUM(amount) AS valor

            FROM payment_outputs	

            WHERE payment_date IS NOT NULL

            GROUP BY YEAR(payment_date), MONTH(payment_date)

            )a 
            
            ORDER BY a.mes DESC

            LIMIT 24
        
        "));


        //dd($result); 

        //foreach($result as $r){
            //$r->valor = 'R$ '.$r->valor;
        //}
    
        

        return response()->json($result);     
       
    }
}
