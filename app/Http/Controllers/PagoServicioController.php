<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoServicioController extends Controller
{
    public function index(Request $request)
    {
        $reservas = DB::table('reserva as r')
            ->join('huesped as h', 'r.id_huesped', '=', 'h.id_huesped')
            ->select(
                'r.id_reserva',
                'h.nombre',
                'h.apellido',
                'h.rut',
                'r.fecha_entrada',
                'r.fecha_salida',
                'r.estado'
            )
            ->orderByDesc('r.id_reserva')
            ->get();

        $reservaSeleccionada = null;
        $estadia = null;
        $cargos = collect();
        $pagos = collect();

        $servicios = DB::table('servicio')
            ->where('estado', 'Activo')
            ->orderBy('nombre')
            ->get();

        $totalHabitacion = 0;
        $totalServicios = 0;
        $totalCuenta = 0;
        $totalPagado = 0;
        $saldo = 0;

        if ($request->filled('reserva')) {
            $idReserva = $request->reserva;

            $reservaSeleccionada = DB::table('reserva as r')
                ->join('huesped as h', 'r.id_huesped', '=', 'h.id_huesped')
                ->where('r.id_reserva', $idReserva)
                ->select(
                    'r.*',
                    'h.nombre',
                    'h.apellido',
                    'h.rut'
                )
                ->first();

            if ($reservaSeleccionada) {
                $detalles = DB::table('detalle_reserva as dr')
                    ->join('habitacion as hab', 'dr.id_habitacion', '=', 'hab.id_habitacion')
                    ->where('dr.id_reserva', $idReserva)
                    ->select(
                        'dr.precio_noche',
                        'hab.numero',
                        'hab.tipo'
                    )
                    ->get();

                $entrada = \Carbon\Carbon::parse($reservaSeleccionada->fecha_entrada);
                $salida = \Carbon\Carbon::parse($reservaSeleccionada->fecha_salida);
                $noches = $entrada->diffInDays($salida);

                $totalHabitacion = $detalles->sum(function ($detalle) use ($noches) {
                    return $detalle->precio_noche * $noches;
                });

                $estadia = DB::table('estadia')
                    ->where('id_reserva', $idReserva)
                    ->first();

                if ($estadia) {
                    $cargos = DB::table('cargo as c')
                        ->join('servicio as s', 'c.id_servicio', '=', 's.id_servicio')
                        ->where('c.id_estadia', $estadia->id_estadia)
                        ->select(
                            'c.id_cargo',
                            's.nombre as servicio',
                            'c.cantidad',
                            'c.precio',
                            'c.subtotal',
                            'c.fecha'
                        )
                        ->orderByDesc('c.fecha')
                        ->get();

                    $totalServicios = $cargos->sum('subtotal');
                }

                $pagos = DB::table('pago')
                    ->where('id_reserva', $idReserva)
                    ->orderByDesc('fecha_pago')
                    ->get();

                $totalPagado = $pagos
                    ->where('estado', 'Pagado')
                    ->sum('monto');

                $totalCuenta = $totalHabitacion + $totalServicios;
                $saldo = $totalCuenta - $totalPagado;
            }
        }

        return view('pagos.index', compact(
            'reservas',
            'reservaSeleccionada',
            'estadia',
            'cargos',
            'pagos',
            'servicios',
            'totalHabitacion',
            'totalServicios',
            'totalCuenta',
            'totalPagado',
            'saldo'
        ));
    }

    public function agregarServicio(Request $request)
    {
        $request->validate([
            'id_reserva' => 'required|integer|exists:reserva,id_reserva',
            'id_servicio' => 'required|integer|exists:servicio,id_servicio',
            'cantidad' => 'required|integer|min:1'
        ]);

        $estadia = DB::table('estadia')
            ->where('id_reserva', $request->id_reserva)
            ->where('estado', 'Activa')
            ->first();

        if (!$estadia) {
            return redirect()
                ->route('pagos.index', ['reserva' => $request->id_reserva])
                ->withErrors('La reserva debe tener una estadía activa para agregar servicios.');
        }

        $servicio = DB::table('servicio')
            ->where('id_servicio', $request->id_servicio)
            ->where('estado', 'Activo')
            ->first();

        if (!$servicio) {
            return redirect()
                ->route('pagos.index', ['reserva' => $request->id_reserva])
                ->withErrors('El servicio seleccionado no está disponible.');
        }

        $subtotal = $servicio->precio * $request->cantidad;

        DB::table('cargo')->insert([
            'id_estadia' => $estadia->id_estadia,
            'id_servicio' => $servicio->id_servicio,
            'cantidad' => $request->cantidad,
            'precio' => $servicio->precio,
            'subtotal' => $subtotal,
            'fecha' => now()
        ]);

        return redirect()
            ->route('pagos.index', ['reserva' => $request->id_reserva])
            ->with('success', 'Servicio agregado correctamente.');
    }

    public function registrarPago(Request $request)
    {
        $request->validate([
            'id_reserva' => 'required|integer|exists:reserva,id_reserva',
            'monto' => 'required|numeric|min:1',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Transferencia'
        ]);

        $reserva = DB::table('reserva')
            ->where('id_reserva', $request->id_reserva)
            ->first();

        $detalles = DB::table('detalle_reserva')
            ->where('id_reserva', $request->id_reserva)
            ->get();

        $entrada = \Carbon\Carbon::parse($reserva->fecha_entrada);
        $salida = \Carbon\Carbon::parse($reserva->fecha_salida);
        $noches = $entrada->diffInDays($salida);

        $totalHabitacion = $detalles->sum(function ($detalle) use ($noches) {
            return $detalle->precio_noche * $noches;
        });

        $estadia = DB::table('estadia')
            ->where('id_reserva', $request->id_reserva)
            ->first();

        $totalServicios = 0;

        if ($estadia) {
            $totalServicios = DB::table('cargo')
                ->where('id_estadia', $estadia->id_estadia)
                ->sum('subtotal');
        }

        $totalPagado = DB::table('pago')
            ->where('id_reserva', $request->id_reserva)
            ->where('estado', 'Pagado')
            ->sum('monto');

        $saldo = ($totalHabitacion + $totalServicios) - $totalPagado;

        if ($saldo <= 0) {
            return redirect()
                ->route('pagos.index', ['reserva' => $request->id_reserva])
                ->withErrors('La reserva no tiene saldo pendiente.');
        }

        if ($request->monto > $saldo) {
            return redirect()
                ->route('pagos.index', ['reserva' => $request->id_reserva])
                ->withErrors('El monto ingresado supera el saldo pendiente.');
        }

        DB::table('pago')->insert([
            'id_reserva' => $request->id_reserva,
            'fecha_pago' => now(),
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'estado' => 'Pagado'
        ]);

        return redirect()
            ->route('pagos.index', ['reserva' => $request->id_reserva])
            ->with('success', 'Pago registrado correctamente.');
    }
}