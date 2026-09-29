<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalHabitaciones = DB::table('habitacion')->count();

        $habitacionesDisponibles = DB::table('habitacion')
            ->where('estado', 'Disponible')
            ->count();

        $habitacionesOcupadas = DB::table('habitacion')
            ->where('estado', 'Ocupada')
            ->count();

        $habitacionesLimpieza = DB::table('habitacion')
            ->where('estado', 'En limpieza')
            ->count();

        $reservasConfirmadas = DB::table('reserva')
            ->where('estado', 'Confirmada')
            ->count();

        $huespedesRegistrados = DB::table('huesped')
            ->count();

        $estadiasActivas = DB::table('estadia')
            ->where('estado', 'Activa')
            ->count();

        $ingresos = DB::table('pago')
            ->where('estado', 'Pagado')
            ->sum('monto');

        $ocupacion = $totalHabitaciones > 0
            ? round(($habitacionesOcupadas / $totalHabitaciones) * 100)
            : 0;

        $proximasReservas = DB::table('reserva as r')
            ->join('huesped as h', 'r.id_huesped', '=', 'h.id_huesped')
            ->join('detalle_reserva as dr', 'r.id_reserva', '=', 'dr.id_reserva')
            ->join('habitacion as hab', 'dr.id_habitacion', '=', 'hab.id_habitacion')
            ->where('r.estado', 'Confirmada')
            ->select(
                'r.id_reserva',
                'h.nombre',
                'h.apellido',
                'hab.numero as habitacion',
                'r.fecha_entrada',
                'r.fecha_salida'
            )
            ->orderBy('r.fecha_entrada')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalHabitaciones',
            'habitacionesDisponibles',
            'habitacionesOcupadas',
            'habitacionesLimpieza',
            'reservasConfirmadas',
            'huespedesRegistrados',
            'estadiasActivas',
            'ingresos',
            'ocupacion',
            'proximasReservas'
        ));
    }
}