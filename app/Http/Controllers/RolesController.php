<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use Validator;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(['permission:modulo de usuarios|listado de roles|ver roles|crear roles|editar roles|eliminar roles']);
    }

    public function index()
    {
        return view('roles.index', [
            'roles'       => Role::all(),
            'permissions' => Permission::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validar = Validator::make(
            $request->all(),
            [
                'name'       => 'required|unique:roles,name,NULL,id,deleted_at,NULL',
                'permission' => 'required',
            ],[
                'required'      => 'Campo requerido',
                'unique'        => 'Ya este nombre esta en uso'
            ]
        );

        if ( count( $validar->errors() ) > 0)
            return response()->json([
                'status' => 500, 
                'errors' => $validar->errors()
            ]);
        
        $newRole = Role::create([ 
            'name' => strtolower( $request->name )
        ]);

        $newRole->syncPermissions( $request->permission );

        \PNotify::success('Rol creado con éxito');

        return Response()->json($newRole);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return $role;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return [
            'fields' => $role,
            'route'  => route( 'roles.update', $id )
        ];
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
        //Validamos que los datos cumplan con los requisitos
        $validar = Validator::make(
            $request->all(),
            [
                'name'       => 'required|unique:roles,name,'.$id.',id,deleted_at,NULL',
                'permission' => 'required',
            ],[
                'required'      => 'Campo requerido',
                'unique'        => 'Ya este nombre esta en uso'
            ]
        );

        if ( count( $validar->errors() ) > 0)
            return response()->json([
                'status' => 500, 
                'errors' => $validar->errors()
            ]);

        if( $id != 1 )
        {

            $role = Role::findOrFail($id);
            $role->name = strtolower( $request->name );
            $role->save();
            $role->syncPermissions( $request->permission );

            \PNotify::success('Rol actualizado con éxito');

            return response()->json($role);
        }
        else
        {
            return response()->json(false);
            \PNotify::error('No tiene permiso para actualizar el rol super-admin');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $rol = Role::findOrFail($id);

        if ( $id != 1 && $rol != null && $rol->users()->count() == 0)
        {
            $rol->delete();
            \PNotify::success('Rol eliminado con éxito');

            return response()->json($rol);
        }

        return response()->json([
            'status' => 500,
            'errors' => 'Este rol tiene usuarios asociados a el o es invalido'
        ]);
    }
}