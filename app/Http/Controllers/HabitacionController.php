<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('habitacion');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('numero', 'like', '%' . $buscar . '%')
                    ->orWhere('tipo', 'like', '%' . $buscar . '%');
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $habitaciones = $query
            ->orderBy('numero')
            ->get();

        return view('habitaciones.index', compact('habitaciones'));
    }

    public function create()
    {
        return view('habitaciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|integer|unique:habitacion,numero',
            'tipo' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|in:Disponible,Reservada,Ocupada,En limpieza,Fuera de servicio'
        ]);

        DB::table('habitacion')->insert([
            'numero' => $request->numero,
            'tipo' => $request->tipo,
            'capacidad' => $request->capacidad,
            'precio' => $request->precio,
            'estado' => $request->estado
        ]);

        return redirect()
            ->route('habitaciones.index')
            ->with('success', 'Habitación registrada correctamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Disponible,Reservada,Ocupada,En limpieza,Fuera de servicio'
        ]);

        $habitacion = DB::table('habitacion')
            ->where('id_habitacion', $id)
            ->first();

        if (!$habitacion) {
            return redirect()
                ->route('habitaciones.index')
                ->withErrors('La habitación no existe.');
        }

        DB::table('habitacion')
            ->where('id_habitacion', $id)
            ->update([
                'estado' => $request->estado
            ]);

        return redirect()
            ->route('habitaciones.index')
            ->with('success', 'Estado de la habitación actualizado.');
    }
}