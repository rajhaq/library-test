<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AssignBook;
use App\Book;
use Auth;
use DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $auth_id = Auth::id();
        $data=Book::orderBy('id','ASC');    
        if(isset($request->search) && !empty($request->search))
        {
            $search='%'.$request->search.'%';

            $data=$data->where('id','like',$search)
            ->orWhere('name',  'like', $search)
            ->orWhere('author',  'like', $search)
            ->orWhere('category',  'like', $search);
    
        }
        $data= $data->with(['assign' => function($q) use($auth_id){
            $q->where('user_id', $auth_id)
            ->where('status',1);
        }])->paginate(10);
        return $data;
    }
    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $response=array();
        $response['status']=false;
        $response['data'] ='';
        $validator = Validator::make(
            $request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            $response['data'] =$validator->errors();
            return response()->json($response,200);
        }
        DB::beginTransaction();

        try {
            $user_id = Auth::id();
            $request['user_id']=$user_id;    
            $response['data']=Book::create($request->all());           
            DB::commit();
            $response['status'] = true;
        } catch (\Exception $e) {
            $response['data']=$e->getMessage()."line".$e->getLine();
            DB::rollback();
        }

        return response()->json($response);
    }

    public function show($id)
    {
        $user_id = Auth::id();
        $data=AssignBook::orderBy('id','ASC');    
        $data= $data->where('user_id',$user_id)
        ->where('status',1)
        ->with('book')
        ->get();
        return $data;
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $response=array();
        $response['status']=false;
        $response['data'] ='';
        $validator = Validator::make(
            $request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            $response['data'] =$validator->errors();
            return response()->json($response,200);
        }
        DB::beginTransaction();
        try {
            $response['data']=Book::where('id',$id)
            ->update(
                [
                    'name' => $request->name,
                    'category' => $request->category,
                    'author' => $request->author,
                    'publish_date' => $request->publish_date,
                    'stock' => $request->stock,
                    'status' => $request->status,
                ]
            );           
            DB::commit();
            $response['status'] = true;
        } catch (\Exception $e) {
            $response['data']=$e->getMessage();
            DB::rollback();
        }

        return response()->json($response);
    }
    public function destroy($id)
    {
        $response=array();
        $response['status']=false;
        $response['data'] = Book::find($id);
        if($response['data'])
        {
                $response['data']=$response['data']->delete();
                $response['status']=true;
        }
        else
        {
            $response['data']="Not Exist";  
        }
        return response()->json($response);

    }
}
