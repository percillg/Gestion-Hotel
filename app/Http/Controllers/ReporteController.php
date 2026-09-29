<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $reservasQuery = DB::table('reserva as r')
            ->join('huesped as h', 'r.id_huesped', '=', 'h.id_huesped')
            ->join('detalle_reserva as dr', 'r.id_reserva', '=', 'dr.id_reserva')
            ->join('habitacion as hab', 'dr.id_habitacion', '=', 'hab.id_habitacion')
            ->select(
                'r.id_reserva',
                'h.nombre',
                'h.apellido',
                'h.rut',
                'hab.numero as habitacion',
                'hab.tipo',
                'r.fecha_entrada',
                'r.fecha_salida',
                'r.estado'
            );

        if ($fechaInicio) {
            $reservasQuery->whereDate('r.fecha_entrada', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $reservasQuery->whereDate('r.fecha_entrada', '<=', $fechaFin);
        }

        $reservas = $reservasQuery
            ->orderByDesc('r.fecha_entrada')
            ->get();

        $pagosQuery = DB::table('pago')
            ->where('estado', 'Pagado');

        if ($fechaInicio) {
            $pagosQuery->whereDate('fecha_pago', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $pagosQuery->whereDate('fecha_pago', '<=', $fechaFin);
        }

        $ingresos = $pagosQuery->sum('monto');

        $totalReservas = $reservas
            ->pluck('id_reserva')
            ->unique()
            ->count();

        $huespedes = $reservas
            ->pluck('rut')
            ->unique()
            ->count();

        $reservasFinalizadas = $reservas
            ->where('estado', 'Finalizada')
            ->pluck('id_reserva')
            ->unique()
            ->count();

        return view('reportes.index', compact(
            'reservas',
            'ingresos',
            'totalReservas',
            'huespedes',
            'reservasFinalizadas',
            'fechaInicio',
            'fechaFin'
        ));
    }
}