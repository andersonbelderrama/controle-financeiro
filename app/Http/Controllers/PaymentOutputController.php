<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescriptionRelease;
use App\Models\PaymentOutput;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentOutputController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payment_outputs = DB::table('payment_outputs')
        ->join('description_releases', 'payment_outputs.description_id', '=', 'description_releases.id' )
        ->select('payment_outputs.id','description_releases.description', 'payment_outputs.amount', 'payment_outputs.due_date', 'payment_outputs.payment_date')
        ->get();

        $description_releases = DB::table('description_releases')
        ->where('type','=', 2)
        ->get();

        return view('payment_output', compact('payment_outputs','description_releases'));
    }

    public function loadData(Request $request)
    {
        $coluna = $request->get('coluna');
        $data_inicial = implode('-', array_reverse(explode('/', $request->get('inicial'))));
        $data_final = implode('-', array_reverse(explode('/', $request->get('final'))));


        if ($coluna == 1) {
            $coluna = 'due_date';
        }else{
            $coluna = 'payment_date';
        }

        if (!empty($data_inicial)) {

            //filter
            $payment_outputs = DB::table('payment_outputs')
            ->join('description_releases', 'payment_outputs.description_id', '=', 'description_releases.id' )
            ->select('payment_outputs.id','description_releases.description', 'payment_outputs.amount', 'payment_outputs.due_date', 'payment_outputs.payment_date')
            ->orderBy('payment_outputs.id', 'desc')
            ->whereBetween('payment_outputs.'.$coluna, [$data_inicial, $data_final])
            //->where('payment_outputs.'.$coluna, '=', $data_inicial) //**FAST TEST**
            ->get();

        }else{

            //all registers
            $payment_outputs = DB::table('payment_outputs')
            ->join('description_releases', 'payment_outputs.description_id', '=', 'description_releases.id' )
            ->select('payment_outputs.id','description_releases.description', 'payment_outputs.amount', 'payment_outputs.due_date', 'payment_outputs.payment_date')
            ->orderBy('payment_outputs.id', 'desc')
            ->get();
        }

        return Datatables::of($payment_outputs)
            ->addColumn('action', function ($payment_output) {
                //return '<a href="#edit-'.$description->id.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';

                $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$payment_output->id.'" data-original-title="Edit" class="edit btn btn-primary  btn editItem"><i class="fas fa-edit"></i></a>';
   
                $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$payment_output->id.'" data-original-title="Delete" class="btn btn-danger btn deleteItem"><i class="fas fa-times"></i></a>';
                return $btn;

            })
            ->editColumn('amount', function($payment_output){
                
                return 'R$ '.number_format($payment_output->amount, 2, ',', '.');
             
            })
            ->editColumn('due_date', function($payment_output){
                
                return date('d/m/Y', strtotime($payment_output->due_date));
             
            })
            ->editColumn('payment_date', function($payment_output){
                
                if ($payment_output->payment_date != null) {
                    return date('d/m/Y', strtotime($payment_output->payment_date));
                }else{
                    return "Pagamento Pendente";
                }
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

         $validator = Validator::make($request->all(), [
             'description_id' => 'required',
             'amount' => 'required',
             'due_date' => 'required',
             //'payment_date' => 'required'
         ]);


         if ($validator->passes()) {


            if ($request->payment_date != null) {
                PaymentOutput::updateOrCreate(['id' => $request->item_id],
                ['description_id' => $request->description_id,
                'amount' => str_replace(['R$','.',','],['','','.'],$request->amount),
                'due_date' => implode('-', array_reverse(explode('/', $request->due_date))),
                'payment_date' => implode('-', array_reverse(explode('/', $request->payment_date)))
            ]);  
            }else{
                PaymentOutput::updateOrCreate(['id' => $request->item_id],
                ['description_id' => $request->description_id,
                'amount' => str_replace(['R$','.',','],['','','.'],$request->amount),
                'due_date' => implode('-', array_reverse(explode('/', $request->due_date))),
                'payment_date' => $request->payment_date = null
            ]);  
            }

            
            return response()->json(['success'=>'Registro inserido com sucesso!']);
			
         }

         return response()->json(['error'=>$validator->errors()]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $payment_output = PaymentOutput::find($id);

        $payment_output->amount = 'R$ '.number_format($payment_output->amount, 2, ',', '.');
        $payment_output->due_date = date('d/m/Y', strtotime($payment_output->due_date));
        

        if ($payment_output->payment_date != null) {
            $payment_output->payment_date = date('d/m/Y', strtotime($payment_output->payment_date));
        }else{
            $payment_output->payment_date = "";
        }

        //dd($payment_output);

        return response()->json($payment_output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        PaymentOutput::find($id)->delete();
     
        return response()->json(['success'=>'Registro deletado com sucesso!']);
    }
}