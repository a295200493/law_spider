<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Permission;
use Illuminate\Http\Request;

use App\Http\Middleware;
use App\Transformers\RoleTransformer;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Repositories\Contracts\RoleRepository;
use Log;
use Auth;


class RoleController extends BaseController
{
    protected $fields = [
        'name' => '',
        'description' => '',
        'permissions' => [],
    ];
/*
   public function __construct(RoleRepository $roleRepository)
    {
        $this->$roleRepository = $roleRepository;
    }*/
    public function index()
    {
        $role = DB::table('admin_permissions')->get();
        return response()->json($role);

    }
    public function create()
    {
        $data = [];
        foreach ($this->fields as $field => $default) {
            $data[$field] = old($field, $default);
        }
        $arr = Permission::all()->toArray();
        foreach ($arr as $v) {
            $data['permissionAll'][$v['cid']][] = $v;
        }
        return view('admin.role.create', $data);
    }

    public function show($id)
    {
        //
    }
    public function edit(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'id' =>'required',
            'label' => 'required|unique:admin_permissions|max:255',
            'cid' =>'required',
            'name'=>'required',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $label = $request->get('label');
        $cid = $request->get('cid');
        $name = $request->get('name');
        $id = $request ->get('id');
        $role = [
            'label' => $label,
            'cid' =>$cid,
            'name'=>$name,
        ];
        if(DB::table('admin_permissions')->where('id',$id)->update($role)){
            return response()->json($role);
        }else{
            return response()->json('修改权限失败');
        }
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'label' => 'required|unique:admin_permissions|max:255',
            'cid' =>'required',
            'name'=>'required',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $label = $request->get('label');
        $cid = $request->get('cid');
        $name = $request->get('name');
        $role = [
            'label' => $label,
            'cid' =>$cid,
            'name'=>$name,
        ];
        if( DB::table('admin_permissions')->insert($role)){
            return response()->json($role);
        }else{
            return response()->json('添加权限失败');
        }
    }
    public function keep(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'user_id' => 'required',
            'role_id' =>'required',
            'role_id' =>'required',
            'role_id' =>'required',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $user_id = $request->get('user_id');
        $role_id = $request->get('role_id');
        $role = [
            'user_id' => $user_id,
            'role_id' =>$role_id,
        ];
        if( DB::table('admin_role_user')->insert($role)){
            return response()->json($role);
        }else{
            return response()->json('添加权限失败');
        }
    }

    public function delete(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'id' =>'required',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $id = $request ->get('id');

        if(DB::table('admin_permissions')->where('id',$id)->delete()){
            return response()->json($id);
        }else{
            return response()->json('修改权限失败');
        }
    }
}
