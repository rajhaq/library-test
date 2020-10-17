<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AssignBook;
use Auth;
use DB;
use Illuminate\Support\Facades\Validator;
class AssignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response=array();
        $response['status']=false;
        $response['data'] ='';
        $validator = Validator::make(
            $request->all(), [
            'book_id' => 'required',
            'user_id' => 'required',

        ]);
        if ($validator->fails()) {
            $response['data'] =$validator->errors();
            return response()->json($response,200);
        }
        DB::beginTransaction();

        try {
            $admin_id = Auth::id();
            $response['data']=AssignBook::create(
                [
                    'admin_id' => $admin_id,
                    'user_id' => $request->user_id,
                    'book_id' => $request->book_id,
                ]);           
            DB::commit();
            $response['status'] = true;
        } catch (\Exception $e) {
            $response['data']=$e->getMessage()."line".$e->getLine();
            DB::rollback();
        }

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data=AssignBook::orderBy('id','ASC');    
        $data= $data->where('status',1)
        ->with('book')
        ->with('user')
        ->get();
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $response=array();
        $response['status']=false;
        $response['data'] ='';
        $validator = Validator::make(
            $request->all(), [
            'book_id' => 'required',
            'user_id' => 'required',

        ]);
        if ($validator->fails()) {
            $response['data'] =$validator->errors();
            return response()->json($response,200);
        }
        DB::beginTransaction();

        try {
            $admin_id = Auth::id();
            $response['data']=AssignBook::create(
                [
                    'admin_id' => $admin_id,
                    'user_id' => $request->user_id,
                    'book_id' => $request->book_id,
                ]);           
            DB::commit();
            $response['status'] = true;
        } catch (\Exception $e) {
            $response['data']=$e->getMessage()."line".$e->getLine();
            DB::rollback();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response=array();
        $response['status']=false;
        $response['data'] ='';
        DB::beginTransaction();

        try {
            $response['data']=AssignBook::where('id',$id)
            ->update(
                [
                    'status' => 0
                ]);           
            DB::commit();
            $response['status'] = true;
        } catch (\Exception $e) {
            $response['data']=$e->getMessage()."line".$e->getLine();
            DB::rollback();
        }

        return response()->json($response);
    }
}
