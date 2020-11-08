<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescriptionRelease;
use App\Models\PaymentInput;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentInputController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payment_inputs = DB::table('payment_inputs')
        ->join('description_releases', 'payment_inputs.description_id', '=', 'description_releases.id' )
        ->select('payment_inputs.id','description_releases.description','payment_inputs.extra_info', 'payment_inputs.amount', 'payment_inputs.due_date', 'payment_inputs.payment_date')
        ->get();

        $description_releases = DB::table('description_releases')
        ->where('type','=', 1)
        ->get();

        return view('payment_input', compact('payment_inputs','description_releases'));
    
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

        if (!empty($data_inicial && $data_final)) {

            //filter
            $payment_inputs = DB::table('payment_inputs')
            ->join('description_releases', 'payment_inputs.description_id', '=', 'description_releases.id' )
            ->select('payment_inputs.id','description_releases.description', 'payment_inputs.extra_info', 'payment_inputs.amount', 'payment_inputs.due_date', 'payment_inputs.payment_date')
            ->orderBy('payment_inputs.id', 'desc')
            ->whereBetween('payment_inputs.'.$coluna, [$data_inicial, $data_final])
            ->whereNull('deleted_at')
            ->get();

        }else{

            //all registers
            $payment_inputs = DB::table('payment_inputs')
            ->join('description_releases', 'payment_inputs.description_id', '=', 'description_releases.id' )
            ->select('payment_inputs.id','description_releases.description', 'payment_inputs.extra_info', 'payment_inputs.amount', 'payment_inputs.due_date', 'payment_inputs.payment_date')
            ->whereNull('deleted_at')
            ->orderBy('payment_inputs.id', 'desc')
            ->get();
        }

        return Datatables::of($payment_inputs)
            ->addColumn('action', function ($payment_input) {
                //return '<a href="#edit-'.$description->id.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';

                $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$payment_input->id.'" data-original-title="Edit" class="edit btn btn-primary  btn editItem"><i class="fas fa-edit"></i></a>';
   
                $btn = $btn.' <a href="javascript:void(0)" data-token="{{ csrf_token() }}" data-toggle="tooltip"  data-id="'.$payment_input->id.'" data-original-title="Delete" class="btn btn-danger btn deleteItem"><i class="fas fa-times"></i></a>';
                return $btn;

            })
            ->editColumn('amount', function($payment_input){
                
                return 'R$ '.number_format($payment_input->amount, 2, ',', '.');
             
            })
            ->editColumn('due_date', function($payment_input){
                
                return date('d/m/Y', strtotime($payment_input->due_date));
             
            })
            ->editColumn('payment_date', function($payment_input){

                if ($payment_input->payment_date != null) {
                    return date('d/m/Y', strtotime($payment_input->payment_date));
                }else{
                    return "Recebimento Pendente";
                }
                
                
             
            })
            ->editColumn('extra_info', function($payment_input){

                if ($payment_input->extra_info == null) {
                    return "N/A";
                }
                return $payment_input->extra_info;
             
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
            'description_id'    => 'required',
            'amount'            => 'required',
            'due_date'          => 'required',
        ]);

        
        if ($request->payment_date != null) {
            $payment_date = implode('-', array_reverse(explode('/', $request->payment_date)));
        }else{
            $payment_date = $request->payment_date = null;
        }


        if ($validator->passes()) {

            PaymentInput::updateOrCreate(['id' => $request->item_id], [
            'description_id'            => $request->description_id,
            'extra_info'                => $request->extra_info,
            'amount'                    => str_replace(['R$','.',','],['','','.'],$request->amount),
            'due_date'                  => implode('-', array_reverse(explode('/', $request->due_date))),
            'payment_date'              => $payment_date
            ]);  

            return response()->json(['success'=>'Registro inserido com sucesso!']);
        }

        return response()->json(['errors'=>$validator->errors()]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $payment_input = PaymentInput::find($id);

        $payment_input->amount = 'R$ '.number_format($payment_input->amount, 2, ',', '.');
        $payment_input->due_date = date('d/m/Y', strtotime($payment_input->due_date));

        if ($payment_input->payment_date != null) {
            $payment_input->payment_date = date('d/m/Y', strtotime($payment_input->payment_date));
        }else{
            $payment_input->payment_date = "";
        }
        

        return response()->json($payment_input);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = PaymentInput::find($id);

        $result->deleted_at = Carbon::now();

        $result->save();
     
        return response()->json(['success'=>'Registro deletado com sucesso!']);
    }
}
