<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Información importante</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>El proceso de generación automática de horarios puede tomar varios minutos dependiendo de la
                            cantidad de distributivos académicos. El sistema aplicará automáticamente las reglas de
                            validación para evitar conflictos.</p>
                    </div>
                </div>
            </div>
        </div>

        <x-filament-panels::form wire:submit="generar">
            {{ $this->form }}

            <!-- Botón explícito para enviar el formulario -->
            <div class="mt-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.5 4.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 10a5 5 0 10-5-5 5 5 0 005 5zm-5 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    Generar Horarios
                </button>
            </div>
        </x-filament-panels::form>
    </div>
</x-filament-panels::page>
