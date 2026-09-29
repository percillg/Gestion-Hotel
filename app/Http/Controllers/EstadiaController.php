<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadiaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('reserva as r')
            ->join('huesped as h', 'r.id_huesped', '=', 'h.id_huesped')
            ->join('detalle_reserva as dr', 'r.id_reserva', '=', 'dr.id_reserva')
            ->join('habitacion as hab', 'dr.id_habitacion', '=', 'hab.id_habitacion')
            ->leftJoin('estadia as e', 'r.id_reserva', '=', 'e.id_reserva')
            ->select(
                'r.id_reserva',
                'h.nombre',
                'h.apellido',
                'h.rut',
                'hab.id_habitacion',
                'hab.numero as habitacion',
                'hab.tipo',
                'r.fecha_entrada',
                'r.fecha_salida',
                'r.estado as estado_reserva',
                'e.id_estadia',
                'e.fecha_checkin',
                'e.fecha_checkout',
                'e.estado as estado_estadia'
            );

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('h.nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('h.apellido', 'like', '%' . $buscar . '%')
                    ->orWhere('h.rut', 'like', '%' . $buscar . '%')
                    ->orWhere('r.id_reserva', 'like', '%' . $buscar . '%')
                    ->orWhere('hab.numero', 'like', '%' . $buscar . '%');
            });
        }

        $reservas = $query
            ->where(function ($q) {
                $q->where('r.estado', 'Confirmada')
                    ->orWhere('e.estado', 'Activa');
            })
            ->orderBy('r.fecha_entrada')
            ->get();

        return view('estadias.index', compact('reservas'));
    }

    public function checkin($id)
    {
        $reserva = DB::table('reserva')
            ->where('id_reserva', $id)
            ->first();

        if (!$reserva || $reserva->estado !== 'Confirmada') {
            return redirect()
                ->route('estadias.index')
                ->withErrors('La reserva no está disponible para realizar check-in.');
        }

        $estadiaExistente = DB::table('estadia')
            ->where('id_reserva', $id)
            ->exists();

        if ($estadiaExistente) {
            return redirect()
                ->route('estadias.index')
                ->withErrors('Esta reserva ya tiene una estadía registrada.');
        }

        DB::transaction(function () use ($id) {
            DB::table('estadia')->insert([
                'id_reserva' => $id,
                'fecha_checkin' => now(),
                'fecha_checkout' => null,
                'estado' => 'Activa'
            ]);

            $habitaciones = DB::table('detalle_reserva')
                ->where('id_reserva', $id)
                ->pluck('id_habitacion');

            DB::table('habitacion')
                ->whereIn('id_habitacion', $habitaciones)
                ->update([
                    'estado' => 'Ocupada'
                ]);
        });

        return redirect()
            ->route('estadias.index')
            ->with('success', 'Check-in realizado correctamente.');
    }

    public function checkout($id)
    {
        $estadia = DB::table('estadia')
            ->where('id_reserva', $id)
            ->where('estado', 'Activa')
            ->first();

        if (!$estadia) {
            return redirect()
                ->route('estadias.index')
                ->withErrors('No existe una estadía activa para esta reserva.');
        }

        DB::transaction(function () use ($id, $estadia) {
            DB::table('estadia')
                ->where('id_estadia', $estadia->id_estadia)
                ->update([
                    'fecha_checkout' => now(),
                    'estado' => 'Finalizada'
                ]);

            DB::table('reserva')
                ->where('id_reserva', $id)
                ->update([
                    'estado' => 'Finalizada'
                ]);

            $habitaciones = DB::table('detalle_reserva')
                ->where('id_reserva', $id)
                ->pluck('id_habitacion');

            DB::table('habitacion')
                ->whereIn('id_habitacion', $habitaciones)
                ->update([
                    'estado' => 'En limpieza'
                ]);
        });

        return redirect()
            ->route('estadias.index')
            ->with('success', 'Check-out realizado correctamente.');
    }
}