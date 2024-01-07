<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Http\Requests\StoreDispatchRequest;
use App\Http\Requests\UpdateDispatchRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class DispatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dispatches = Dispatch::paginate(5);

        return Inertia::render('Dispatches/index', [
            'dispatches'    => $dispatches,
            'id'            => env('PISO_ID'),
            'apiToken'     => env('APP_KEY'),
        ]);
    }

    public function buscar(Request $request)
    {
        if ($request->startDate != 'null' && $request->startDate != null) {
            $dispatches = Dispatch::whereDate('created_at', $request->startDate)->paginate($request->records);
        } else {
            $dispatches = Dispatch::paginate($request->records);
        }        

        return response([
            'registros' => $dispatches,
            'request'   => $request->all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDispatchRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Dispatch $dispatch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dispatch $dispatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDispatchRequest $request, Dispatch $dispatch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dispatch $dispatch)
    {
        //
    }

    /* API ACCIONS */

    public function newDispatches(Request $request) {
        try {
            DB::beginTransaction();

            foreach ($request->data['dispatches'] as $key => $dispatch) {
                $newDispatch = new Dispatch();
                $newDispatch->prometheus_id = $dispatch['id'];
                $newDispatch->status = 'espera';
                $newDispatch->created_at = $dispatch['created_at'];
                $newDispatch->save();

                foreach ($dispatch['productos'] as $key => $producto) {
                    $newProduct = Product::firstOrNew(
                        [
                            'prometheus_id' => $producto['pivot']['producto_id']
                        ],
                        [
                            'nombre'        => $producto['nombre'],
                            'precio_menor'  => $producto['precio_menor'],
                            'updated_at'    => $producto['updated_at'],
                        ]
                    );

                    $newProduct->nombre = $producto['nombre'];
                    $newProduct->precio_menor = $producto['precio_menor'];
                    $newProduct->updated_at = $producto['updated_at'];

                    $newProduct->save();

                    $newDispatch->products()->attach($newProduct->id, [
                        'cantidad'  => $producto['pivot']['cantidad']
                    ]);

                }                
            }

            DB::commit();

            return response([
                'error'     => false,
                'despachos' => Dispatch::select('id', 'created_at', 'status', 'prometheus_id')->paginate(5),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'error' => true,
                'errorMessage'  => 'Fallo la query',
                'errorData'     => $th->getMessage(),
                'errorLine'     => $th->getLine(),
            ], 500);
        }        
    }

    public function procesar(Request $request) {

        $validator = Validator::make($request->all(), [
            'data.dispatchId'    => 'required|exists:dispatches,prometheus_id',
            'data.status'        => 'required|integer',
        ], 
        [], 
        [
            'data.dispatchId'   => 'ID del despacho',
            'data.status'       => 'Estado',
        ]);

        if ($validator->fails()) {
            return response([
                'error'         => true,
                'errorMessage'  => 'Fallo la validacion de los datos.',
                'errors'        => $validator->errors(),
            ]);
        }

        try {
            DB::beginTransaction();

            $dispatch = Dispatch::with('products')->where('prometheus_id', $request->data['dispatchId'])->first();
            $dispatch->status = ($request->data['status'] > 0) ? 'aceptado' : 'rechazado';
            $dispatch->save();

            if ($request->data['status'] > 0) {
                foreach ($dispatch->products as $key => $producto) {
                    $producto->cantidad = $producto->cantidad+$producto->pivot->cantidad;
                    $producto->save();
                }
            }

            $response = Http::post('http://mipuchito.test/api/despachos/procesar', [
                'data'          => [
                    'apiToken' => env('APP_KEY'),
                    'idBranch' => env('PISO_ID'),
                    'dispatchId'    => $dispatch->prometheus_id,
                    'status'        => $request->data['status'],
                ],                
            ]);

            if ($response->successful()) {
                DB::commit();
                return response([
                    'error'         => false,
                    'data'          => $response->body(),
                    'dispatches'    => Dispatch::paginate(5),
                ]);
            } else {
                DB::rollBack();
                return response([
                    'error'         => true,
                    'errorMessage'  => 'Fallo la conexion con el servidor. Por favor intenta mas tarde.',
                    'data'          => $response->body(),
                ]);
            }

        } catch (\Throwable $th) {
            DB::rollBack();

            return response([
                'error'         => true,
                'errorMessage'  => 'Fallo el sistema. Por favor intenta mas tarde.',
                'errorData'     => $th->getMessage(),
                'errorLine'     => $th->getLine(),
            ]);
        }
    }

    public function maxIdDispatch() {
        $idDispatch = Dispatch::max('prometheus_id');

        return response([
            'idDispatch' => $idDispatch
        ]);
    }
}
