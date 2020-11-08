<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescriptionRelease;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DescriptionReleaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('description_release');
    }

    public function loadData()
    {
        $descriptions = DB::table('description_releases')
        ->orderBy('description_releases.id', 'desc')
        ->get();

        return Datatables::of($descriptions)
            ->addColumn('action', function ($description) {

                $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$description->id.'" data-original-title="Edit" class="edit btn btn-primary btn editItem"><i class="fas fa-edit"></i></a>';
   
                $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$description->id.'" data-original-title="Delete" class="btn btn-danger btn deleteItem"><i class="fas fa-times"></i></a>';
                return $btn;

            })
            ->rawColumns(['action'])
            ->editColumn('created_at', function($description){
                return Carbon::parse($description->created_at)->format('d/m/Y H:m:s');

            })
            ->editColumn('type', function($description){
                if($description->type == 1){
                    return 'Recebimento';
                }else{
                    return 'Pagamento';
                }
            })
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
            'type' => 'required',
            'description' => 'required'
        ]);

        if ($validator->passes()) {

            DescriptionRelease::updateOrCreate(['id' => $request->item_id],[
                'type'          => $request->type, 
                'description'   => $request->description
            ]);  
            return response()->json(['success'=>'Registro inserido com sucesso!']);
			
        }

        return response()->json(['error'=>$validator->errors()]);

        //DescriptionRelease::updateOrCreate(['id' => $request->item_id],
        //['type' => $request->type, 'description' => $request->description]);        

        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $description = DescriptionRelease::find($id);
        return response()->json($description);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DescriptionRelease::find($id)->delete();

     
        return response()->json(['success'=>'Registro deletado com sucesso!']);
    }
}