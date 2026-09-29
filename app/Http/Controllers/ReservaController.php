<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('reserva as r')
            ->join('huesped as h', 'r.id_huesped', '=', 'h.id_huesped')
            ->join('detalle_reserva as dr', 'r.id_reserva', '=', 'dr.id_reserva')
            ->join('habitacion as hab', 'dr.id_habitacion', '=', 'hab.id_habitacion')
            ->select(
                'r.id_reserva',
                'h.rut',
                'h.nombre',
                'h.apellido',
                'hab.numero as habitacion',
                'hab.tipo',
                'r.fecha_reserva',
                'r.fecha_entrada',
                'r.fecha_salida',
                'r.estado'
            );

      
        if ($request->filled('fecha_entrada')) {
            $query->whereDate(
                'r.fecha_entrada',
                '>=',
                $request->fecha_entrada
            );
        }

      
        if ($request->filled('fecha_salida')) {
            $query->whereDate(
                'r.fecha_salida',
                '<=',
                $request->fecha_salida
            );
        }

       
        if ($request->filled('rut')) {
            $query->where(
                'h.rut',
                'like',
                '%' . $request->rut . '%'
            );
        }

      
        if ($request->filled('estado')) {
            $query->where(
                'r.estado',
                $request->estado
            );
        }

        $reservas = $query
            ->orderBy('r.fecha_entrada')
            ->get();

        return view('reservas.index', compact('reservas'));
    }

public function create()
    {
        $huespedes = DB::table('huesped')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $habitaciones = DB::table('habitacion')
            ->where('estado', 'Disponible')
            ->orderBy('numero')
            ->get();

        return view(
            'reservas.create',
            compact('huespedes', 'habitaciones')
        );
    }

public function store(Request $request)
{
    
    $request->validate([
        'id_huesped' => 'required|integer|exists:huesped,id_huesped',
        'id_habitacion' => 'required|integer|exists:habitacion,id_habitacion',
        'fecha_entrada' => 'required|date',
        'fecha_salida' => 'required|date|after:fecha_entrada',
    ], [
        'id_huesped.required' => 'Debe seleccionar un huésped.',
        'id_habitacion.required' => 'Debe seleccionar una habitación.',
        'fecha_entrada.required' => 'Debe ingresar la fecha de entrada.',
        'fecha_salida.required' => 'Debe ingresar la fecha de salida.',
        'fecha_salida.after' => 'La fecha de salida debe ser posterior a la fecha de entrada.',
    ]);


   
    $habitacionOcupada = DB::table('detalle_reserva as dr')
        ->join('reserva as r', 'dr.id_reserva', '=', 'r.id_reserva')
        ->where('dr.id_habitacion', $request->id_habitacion)
        ->whereIn('r.estado', ['Pendiente', 'Confirmada'])
        ->where('r.fecha_entrada', '<', $request->fecha_salida)
        ->where('r.fecha_salida', '>', $request->fecha_entrada)
        ->exists();


    if ($habitacionOcupada) {

        return back()
            ->withInput()
            ->withErrors([
                'id_habitacion' =>
                    'La habitación seleccionada ya está reservada para esas fechas.'
            ]);
    }


    
    $habitacion = DB::table('habitacion')
        ->where('id_habitacion', $request->id_habitacion)
        ->first();


    if (!$habitacion || $habitacion->estado === 'Fuera de servicio') {

        return back()
            ->withInput()
            ->withErrors([
                'id_habitacion' =>
                    'La habitación seleccionada no se encuentra disponible.'
            ]);
    }


  
    DB::transaction(function () use ($request, $habitacion) {

        $idReserva = DB::table('reserva')->insertGetId([
            'id_huesped' => $request->id_huesped,

            // Por ahora utilizamos el usuario administrador de prueba.
            // Más adelante será reemplazado por el usuario autenticado.
            'id_usuario' => 1,

            'fecha_reserva' => now()->toDateString(),
            'fecha_entrada' => $request->fecha_entrada,
            'fecha_salida' => $request->fecha_salida,
            'estado' => 'Confirmada'
        ]);


        DB::table('detalle_reserva')->insert([
            'id_reserva' => $idReserva,
            'id_habitacion' => $request->id_habitacion,
            'precio_noche' => $habitacion->precio
        ]);

    });


  
    return redirect()
        ->route('reservas.index')
        ->with('success', 'Reserva creada correctamente.');
}

}