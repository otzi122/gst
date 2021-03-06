<?php

namespace App\Http\Controllers;

use App\Setting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use Validator;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:configuraciones:listado']);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('settings.index',[
            'settings' => Setting::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, $id )
    {
        $setting = Setting::find($id);

        if ( isset( $setting->field['validate'] ) )
        {

            if(is_array( $setting->field['validate']  ) )
                foreach ( $setting->field['validate'] as $value )
                {
                    $patron = "/^new /";
                    if( preg_match( $patron, $value ) )
                    {
                        $object = 'App\Rules\\' . preg_replace( $patron, '', $value);
                        $validate[] = new $object();
                    }
                    else
                        $validate[] = $value;
                    
                }
                
            else
                $validate = $setting->field['validate'];
            
        }

        $data = $request->validate([ 'value' => $validate ?? 'required']);

        $setting->value = strtolower( $request->value );
        $setting->save();

        return response()->json(true);
    }
}
