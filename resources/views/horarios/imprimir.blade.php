<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 15px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }

        .header h2 {
            font-size: 12px;
            margin: 0;
            color: #666;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .info-item {
            font-size: 9px;
        }

        .info-label {
            font-weight: bold;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 9px;
        }

        .hora-columna {
            width: 80px;
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .clase-teorica {
            background-color: #e3f2fd;
            border-left: 3px solid #2196f3;
        }

        .clase-practica {
            background-color: #e8f5e8;
            border-left: 3px solid #4caf50;
        }

        .clase-laboratorio {
            background-color: #fff3e0;
            border-left: 3px solid #ff9800;
        }

        .clase-info {
            font-size: 8px;
            line-height: 1.2;
        }

        .asignatura {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .docente,
        .curso {
            color: #666;
            margin-bottom: 1px;
        }

        .horario {
            color: #888;
            font-size: 7px;
        }

        .aula {
            color: #888;
            font-size: 7px;
        }

        .tipo-clase {
            background-color: #333;
            color: white;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6px;
            margin-top: 2px;
            display: inline-block;
        }

        .clase-practica .tipo-clase {
            background-color: #4caf50;
        }

        .clase-laboratorio .tipo-clase {
            background-color: #ff9800;
        }

        .receso-hora {
            background-color: #fff8e1;
            border-left: 3px solid #ffc107;
            color: #f57f17;
            font-weight: bold;
        }

        .receso-celda {
            background-color: #fff8e1;
            border-left: 3px solid #ffc107;
            color: #f57f17;
            text-align: center;
            font-weight: bold;
        }

        .receso-info {
            font-size: 8px;
            line-height: 1.2;
        }

        .multiple-clases {
            border: 2px dashed #666;
        }

        .separador-clases {
            margin: 2px 0;
            border: none;
            border-top: 1px dashed #999;
        }

        .resumen {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            font-size: 9px;
        }

        .resumen-item {
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .resumen-numero {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .resumen-label {
            color: #666;
            font-size: 8px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .celda-libre {
            color: #ccc;
            font-style: italic;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <h2>{{ $subtitulo }}</h2>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Tipo de Vista:</span> {{ ucfirst($tipoVista) }}
        </div>
        <div class="info-item">
            <span class="info-label">Fecha de Generación:</span> {{ $fechaGeneracion }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="hora-columna">Hora</th>
                <th>Lunes</th>
                <th>Martes</th>
                <th>Miércoles</th>
                <th>Jueves</th>
                <th>Viernes</th>
                <th>Sábado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rangosFiltrados as $rangoHora)
                <tr>
                    <td class="hora-columna @if(str_starts_with($rangoHora, 'RECESO:')) receso-hora @endif">
                        @if(str_starts_with($rangoHora, 'RECESO:'))
                            🍽️ {{ substr($rangoHora, 7) }}
                        @else
                            {{ $rangoHora }}
                        @endif
                    </td>

                    @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia)
                        @if(str_starts_with($rangoHora, 'RECESO:'))
                            <td class="receso-celda">
                                <div class="receso-info">
                                    🍽️ RECESO ACADÉMICO<br>
                                    <small>{{ substr($rangoHora, 7) }}</small>
                                </div>
                            </td>
                        @else
                            @php
                                $horariosDelDia = $horariosPorDia[$dia] ?? collect();
                                // Para docentes con múltiples semestres, mostrar todos los horarios que coincidan
                                $horariosEnRango = $horariosDelDia->filter(function ($horario) use ($rangoHora) {
                                    [$inicioRango, $finRango] = explode('-', $rangoHora);
                                    $inicioHorario = \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i');
                                    $finHorario = \Carbon\Carbon::parse($horario->hora_fin)->format('H:i');
                                    return ($inicioHorario >= $inicioRango && $inicioHorario < $finRango) ||
                                        ($finHorario > $inicioRango && $finHorario <= $finRango) ||
                                        ($inicioHorario <= $inicioRango && $finHorario >= $finRango);
                                });
                            @endphp

                            <td class="
                                @if($horariosEnRango->isNotEmpty())
                                    @php
                                        $primerHorario = $horariosEnRango->first();
                                    @endphp
                                    clase-{{ $primerHorario->tipo_clase }}
                                    @if($horariosEnRango->count() > 1) multiple-clases @endif
                                @endif
                            ">
                                @if($horariosEnRango->isNotEmpty())
                                    @foreach($horariosEnRango as $index => $horarioEnRango)
                                        @if($index > 0)
                                            <hr class="separador-clases">
                                        @endif
                                        <div class="clase-info">
                                            <div class="asignatura">
                                                {{ Str::limit($horarioEnRango->distributivoAcademico->asignatura->nombre, 25) }}
                                            </div>

                                            @if($tipoVista === 'carrera')
                                                <div class="docente">
                                                    {{ Str::limit($horarioEnRango->distributivoAcademico->docente->user->nombre_completo, 20) }}
                                                </div>
                                            @else
                                                <div class="curso">
                                                    {{ $horarioEnRango->distributivoAcademico->carrera->codigo }}-{{ $horarioEnRango->distributivoAcademico->semestre }}{{ $horarioEnRango->distributivoAcademico->paralelo }}
                                                </div>
                                            @endif

                                            <div class="horario">
                                                {{ $horarioEnRango->hora_inicio }} - {{ $horarioEnRango->hora_fin }}
                                            </div>

                                            @if($horarioEnRango->aula)
                                                <div class="aula">
                                                    📍 {{ $horarioEnRango->aula }}
                                                </div>
                                            @endif

                                            <span class="tipo-clase">
                                                {{ ucfirst($horarioEnRango->tipo_clase) }}
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="celda-libre">Libre</div>
                                @endif
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumen de horarios -->
    <div class="resumen">
        <div class="resumen-item">
            <div class="resumen-numero">{{ $horarios->count() }}</div>
            <div class="resumen-label">Total de Clases</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero">{{ $horarios->where('tipo_clase', 'teorica')->count() }}</div>
            <div class="resumen-label">Horas Teóricas</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero">{{ $horarios->where('tipo_clase', 'practica')->count() }}</div>
            <div class="resumen-label">Horas Prácticas</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero">{{ $horarios->where('tipo_clase', 'laboratorio')->count() }}</div>
            <div class="resumen-label">Laboratorios</div>
        </div>
    </div>

    <div class="footer">
        <p>Horario generado automáticamente - {{ $fechaGeneracion }}</p>
        <p>Sistema de Gestión Académica</p>
    </div>
</body>

</html>
