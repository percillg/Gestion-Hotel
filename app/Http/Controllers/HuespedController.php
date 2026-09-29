<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HuespedController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('huesped');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('apellido', 'like', '%' . $buscar . '%')
                    ->orWhere('rut', 'like', '%' . $buscar . '%')
                    ->orWhere('email', 'like', '%' . $buscar . '%');
            });
        }

        $huespedes = $query
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return view('huespedes.index', compact('huespedes'));
    }

    public function create()
    {
        return view('huespedes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido' => 'required|string|max:50',
            'rut' => 'required|string|max:20|unique:huesped,rut',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:150'
        ]);

        DB::table('huesped')->insert([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'rut' => $request->rut,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'direccion' => $request->direccion
        ]);

        return redirect()
            ->route('huespedes.index')
            ->with('success', 'Huésped registrado correctamente.');
    }
}