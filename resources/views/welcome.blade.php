<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
        <style>
            * {
                font-family: 'Franklin Gothic Medium', 'sans-serif';
            }

            body {
                margin: 0px;
                padding: 0px;
                background: rgb(140,97,115);
background: linear-gradient(125deg, rgba(140,97,115,1) 0%, rgba(101,67,80,1) 6%, rgba(87,52,65,1) 42%, rgba(79,40,55,1) 100%);  
                color: rgb(182, 182, 212);
            }

            .flex-box {
                width: 100vw;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .card {
                background: rgb(225,209,216);
                background: linear-gradient(56deg, rgba(128,94,108,1) 0%, rgba(107,74,87,1) 7%, rgba(79,40,55,1) 15%, rgba(79,40,55,1) 84%, rgba(107,74,87,1) 95%, rgba(128,94,108,1) 100%);
                border: 1px solid rgba(128,94,108,1);
                box-shadow: 0px 0px 5px rgba(128,94,108,1) ;
                border-radius: 15px;
                padding-left: 20px;
                padding-right: 20px;
                color: white;
                text-shadow: 0px 0px 3px black;
            }

            .card a {
                color: rgb(231, 206, 226);
                text-decoration: none;
                line-height: 1.4rem;
                font-weight: bold;
            }

            .card a:hover {
                color: white;
                text-decoration: underline;
                text-shadow: 0px 0px 2px white;
            }

            .card ul {
                list-style-type: none;
                margin: 0;
                padding: 0;
            }
        </style>
    </head>
    <body>
        <div class="flex-box">
            @if (Route::has('login'))
                <div>
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="card">
                <div>
                    <h2>APIs Disponibles</h2>

                    <p>
                        <ul>
                            <li><a href="{{ route('openconductores.index') }}">Conductores</a></li>
                            <li><a href="{{ route('openempresas.index') }}">Empresas</a></li>
                            <li><a href="{{ route('openhistorial.index') }}">Historial</a></li>
                            <li><a href="{{ route('opensmartcards.index') }}">Tarjetas</a></li>
                            <li><a href="{{ route('openvehiculos.index') }}">Vehiculos</a></li>
                            <li><a href="{{ route('opendispositivos.index') }}">Dispositivos</a></li>
                            <li><a href="{{ route('openeventos.index') }}">Eventos</a></li>
                            <hr>
                            <li><a href="{{ route('openestadoempresas.index') }}">Estados de Empresas</a></li>
                            <li><a href="{{ route('openestadovehiculos.index') }}">Estados de Vehiculos</a></li>
                            <li><a href="{{ route('opentipovehiculos.index') }}">Tipos de Vehiculos</a></li>
                        </ul>
                    </p>
                </div>
            </div>

            <div>
                <div>
                    &nbsp;
                </div>
            </div>
        </div>
    </body>
</html>
