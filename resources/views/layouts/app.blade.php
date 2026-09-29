<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Gestión Hotel')</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f6f8fb;
            color: #172033;
        }

        /* =========================
           SIDEBAR
        ========================== */

        .sidebar {
            width: 220px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;

            background: #10264f;
            color: white;

            display: flex;
            flex-direction: column;

            padding: 18px 12px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-bottom: 25px;
            padding: 0 8px;
        }

        .logo-icon {
            width: 34px;
            height: 34px;

            background: #2563eb;
            border-radius: 7px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 18px;
        }

        .logo-text h2 {
            margin: 0;
            font-size: 17px;
        }

        .logo-text span {
            color: #9eacc5;
            font-size: 11px;
        }

        /* =========================
           MENÚ
        ========================== */

        .menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .menu-item {
            display: flex;
            align-items: center;

            gap: 12px;

            padding: 11px 12px;

            color: #aab7ce;
            text-decoration: none;

            border-radius: 7px;

            font-size: 14px;

            transition: 0.2s;
        }

        .menu-item i {
            width: 20px;
            font-size: 17px;
        }

        .menu-item:hover {
            background: #1d3156;
            color: white;
        }

        .menu-item.active {
            background: #1e3154;
            color: white;
            font-weight: 600;
        }

        .logout {
            margin-top: auto;
        }

        /* =========================
           CONTENIDO
        ========================== */

        .main {
            margin-left: 220px;
            min-height: 100vh;
        }

        /* =========================
           HEADER
        ========================== */

        .header {
            height: 60px;

            background: white;

            border-bottom: 1px solid #dde4ee;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 24px;
        }

        .header h1 {
            font-size: 21px;
            margin: 0;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;

            color: #66758d;
            font-size: 14px;
        }

        .avatar {
            width: 35px;
            height: 35px;

            background: #2563eb;
            color: white;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 12px;
        }

        /* =========================
           CONTENIDO DE CADA PÁGINA
        ========================== */

        .content {
            padding: 25px;
        }

        /* =========================
           ELEMENTOS GENERALES
        ========================== */

        .card {
            background: white;

            border: 1px solid #dbe3ee;
            border-radius: 10px;

            padding: 20px;

            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        }

        .btn-primary {
            background: #2563eb;
            color: white;

            border: none;
            border-radius: 7px;

            padding: 10px 18px;

            cursor: pointer;

            font-weight: 600;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        input,
        select,
        textarea {
            font-family: inherit;
        }

        /* =========================
           RESPONSIVE BÁSICO
        ========================== */

        @media (max-width: 900px) {

            .sidebar {
                width: 180px;
            }

            .main {
                margin-left: 180px;
            }

            .logo-text h2 {
                font-size: 15px;
            }

            .menu-item {
                font-size: 13px;
            }
        }

    </style>

    @stack('styles')

</head>

<body>

    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar">

        <div class="logo">

            <div class="logo-icon">
                <i class="bi bi-building"></i>
            </div>

            <div class="logo-text">
                <h2>Gestión Hotel</h2>
                <span>Sistema de administración</span>
            </div>

        </div>


        <nav class="menu">

            <a href="{{ route('dashboard') }}"
               class="menu-item @yield('menu-dashboard')">

                <i class="bi bi-speedometer2"></i>
                Dashboard

            </a>


            <a href="{{ route('habitaciones.index') }}"
               class="menu-item @yield('menu-habitaciones')">

                <i class="bi bi-building"></i>
                Habitaciones

            </a>


            <a href="{{ route('reservas.index') }}"
               class="menu-item @yield('menu-reservas')">

                <i class="bi bi-calendar-check"></i>
                Reservas

            </a>


            <a href="{{ route('huespedes.index') }}"
               class="menu-item @yield('menu-huespedes')">

                <i class="bi bi-people"></i>
                Huéspedes

            </a>


            <a href="{{ route('estadias.index') }}"
               class="menu-item @yield('menu-checkin')">

                <i class="bi bi-arrow-left-right"></i>
                Check-in/Check-out

            </a>


            <a href="{{ route('pagos.index') }}"
               class="menu-item @yield('menu-pagos')">

                <i class="bi bi-credit-card"></i>
                Pagos y Servicios

            </a>


            <a href="{{ route('reportes.index') }}"
               class="menu-item @yield('menu-reportes')">

                <i class="bi bi-bar-chart"></i>
                Reportes

            </a>


            <a href="#"
               class="menu-item @yield('menu-usuarios')">

                <i class="bi bi-person-gear"></i>
                Usuarios

            </a>


            <a href="{{ route('configuracion.index') }}"
               class="menu-item">

                <i class="bi bi-gear"></i>
                Configuración

            </a>

        </nav>


        <a href="#" class="menu-item logout">

            <i class="bi bi-box-arrow-left"></i>
            Cerrar sesión

        </a>

    </aside>


    <!-- =========================
         ÁREA PRINCIPAL
    ========================== -->

    <main class="main">

        <header class="header">

            <h1>
                @yield('page-title', 'Dashboard')
            </h1>

            <div class="user-area">

                <span>Bienvenido/a, Recepcionista</span>

                <div class="avatar">
                    RE
                </div>

            </div>

        </header>


        <section class="content">

            @yield('content')

        </section>

    </main>


    @stack('scripts')

</body>
</html>