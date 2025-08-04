{{-- resources/views/filament/resources/horario-resource/pages/visualizar-horarios-estudiante.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Formulario de filtros -->
        <x-filament-panels::form wire:submit="consultarHorarios">
            {{ $this->form }}
        </x-filament-panels::form>

        <!-- Cuadrícula de horarios -->
        @if($horarios->isNotEmpty())
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Mis Horarios - {{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        Período: {{ $data['periodo_academico_id'] ? \App\Models\PeriodoAcademico::find($data['periodo_academico_id'])->nombre : '' }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                    Hora
                                </th>
                                @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $dia)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $dia }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $horariosPorDia = $this->getHorariosPorDia();
                                $horariosDisponibles = $this->getHorariosDisponibles();
                                // Si no hay horarios generados, mostrar todos los rangos disponibles
                                if ($horarios->isEmpty()) {
                                    $rangosFiltrados = collect($horariosDisponibles);
                                } else {
                                    $horasOcupadas = $horarios->map(function($h) {
                                        return [
                                            \Carbon\Carbon::parse($h->hora_inicio)->format('H:i'),
                                            \Carbon\Carbon::parse($h->hora_fin)->format('H:i')
                                        ];
                                    });
                                    $minHora = $horasOcupadas->min(fn($h) => $h[0]) ?? null;
                                    $maxHora = $horasOcupadas->max(fn($h) => $h[1]) ?? null;
                                    $rangosFiltrados = collect($horariosDisponibles)->filter(function($rango) use ($minHora, $maxHora) {
                                        if (!$minHora || !$maxHora) return false;
                                        [$inicio, $fin] = explode('-', $rango);
                                        return ($fin > $minHora && $inicio < $maxHora);
                                    });
                                }
                            @endphp

                            @foreach($rangosFiltrados as $rangoHora)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">
                                        <div class="text-center">
                                            {{ $rangoHora }}
                                        </div>
                                    </td>

                                    @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia)
                                        @php
                                            $horariosDelDia = $horariosPorDia[$dia] ?? collect();
                                            $horarioEnRango = $horariosDelDia->first(function ($horario) use ($rangoHora) {
                                                [$inicioRango, $finRango] = explode('-', $rangoHora);
                                                $inicioHorario = \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i');
                                                $finHorario = \Carbon\Carbon::parse($horario->hora_fin)->format('H:i');
                                                return ($inicioHorario >= $inicioRango && $inicioHorario < $finRango) ||
                                                    ($finHorario > $inicioRango && $finHorario <= $finRango) ||
                                                    ($inicioHorario <= $inicioRango && $finHorario >= $finRango);
                                            });
                                        @endphp

                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-900 border-l border-gray-200 relative">
                                            @if($horarioEnRango)
                                                <div class="
                                                    rounded-lg p-2 text-xs h-full min-h-[80px] flex flex-col justify-center
                                                    {{ $horarioEnRango->tipo_clase === 'practica' ? 'bg-green-100 border border-green-300' : 'bg-blue-100 border border-blue-300' }}
                                                ">
                                                    <div class="font-semibold text-gray-800 mb-1">
                                                        {{ Str::limit($horarioEnRango->distributivoAcademico->asignatura->nombre, 20) }}
                                                    </div>
                                                    <div class="text-gray-600 mb-1">
                                                        {{ $horarioEnRango->distributivoAcademico->carrera->codigo }}-{{ $horarioEnRango->distributivoAcademico->semestre }}{{ $horarioEnRango->distributivoAcademico->paralelo }}
                                                    </div>
                                                    <div class="text-gray-500 text-[10px]">
                                                        {{ $horarioEnRango->hora_inicio }} - {{ $horarioEnRango->hora_fin }}
                                                    </div>
                                                    @if($horarioEnRango->aula)
                                                        <div class="text-gray-500 text-[10px]">
                                                            📍 {{ $horarioEnRango->aula }}
                                                        </div>
                                                    @endif
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium
                                                            {{ $horarioEnRango->tipo_clase === 'practica' ? 'bg-green-200 text-green-800' : 'bg-blue-200 text-blue-800' }}
                                                        ">
                                                            {{ ucfirst($horarioEnRango->tipo_clase) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="h-full min-h-[80px] flex items-center justify-center text-gray-300">
                                                    <span class="text-xs">Libre</span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Resumen de horarios -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Total de clases:</span>
                            <span class="ml-1 text-gray-900">{{ $horarios->count() }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Horas teóricas:</span>
                            <span class="ml-1 text-gray-900">{{ $horarios->where('tipo_clase', 'teorica')->count() }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Horas prácticas:</span>
                            <span class="ml-1 text-gray-900">{{ $horarios->where('tipo_clase', 'practica')->count() }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Laboratorios:</span>
                            <span class="ml-1 text-gray-900">{{ $horarios->where('tipo_clase', 'laboratorio')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay horarios</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Seleccione un período académico y haga clic en "Consultar Horarios" para ver los resultados.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
