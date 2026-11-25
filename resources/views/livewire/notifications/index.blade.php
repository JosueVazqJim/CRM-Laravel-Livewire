<section class="w-full">

    {{-- HEADER --}}
    <div class="relative mb-4 md:mb-6 w-full">
        <x-mary-header
                title="Notificaciones"
                subtitle="Historial de todas tus notificaciones."
                separator
                progress-indicator
        >
            <x-slot:actions>
                <div class="flex flex-wrap gap-2">
                    {{-- Filtro rápido --}}
                    <x-mary-button
                            label="Todas"
                            wire:click="$set('filterRead', 'all')"
                            class="{{ $filterRead === 'all' ? 'btn-primary' : 'bg-base-300' }} btn-sm"
                    />
                    <x-mary-button
                            label="Sin leer"
                            wire:click="$set('filterRead', 'unread')"
                            :badge="$unreadCount ?: null"
                            badge-classes="font-mono"
                            class="{{ $filterRead === 'unread' ? 'btn-primary' : 'bg-base-300' }} btn-sm"
                    />
                    <x-mary-button
                            label="Leídas"
                            wire:click="$set('filterRead', 'read')"
                            class="{{ $filterRead === 'read' ? 'btn-primary' : 'bg-base-300' }} btn-sm"
                    />

                    @if($unreadCount > 0)
                        <x-mary-button
                                label="Marcar todas como leídas"
                                icon="o-check-circle"
                                wire:click="markAllRead"
                                class="btn-ghost btn-sm"
                        />
                    @endif
                </div>
            </x-slot:actions>
        </x-mary-header>
    </div>

    {{-- LISTA --}}
    <x-mary-card>
        <div class="divide-y divide-base-200">
            @forelse($notifications as $notification)
                <div class="flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-4 px-3 sm:px-4 py-3 sm:py-4 transition-colors
                            {{ is_null($notification->read_at) ? 'bg-primary/5 hover:bg-primary/10' : 'hover:bg-base-200' }}">

                    {{-- Icono - Oculto en móviles muy pequeños, visible desde sm --}}
                    <div class="hidden xs:flex flex-shrink-0 mt-0.5">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-primary/10 flex items-center justify-center">
                            <x-mary-icon name="o-user-plus" class="w-4 h-4 sm:w-5 sm:h-5 text-primary" />
                        </div>
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm text-base-content leading-relaxed">
                            {{ $notification->data['message'] }}
                        </p>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1">
                            <div class="flex items-center gap-1">
                                <x-mary-icon name="o-clock" class="w-3 h-3 text-base-content/40" />
                                <span class="text-xs text-base-content/40">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if(isset($notification->data['granted_by']))
                                <span class="hidden xs:inline text-xs text-base-content/40">·</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-base-content/40">
                                        por {{ $notification->data['granted_by'] }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if(!empty($notification->data['matched_leads']))
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                <span class="text-xs text-base-content/40">Coincide con:</span>
                                @foreach($notification->data['matched_leads'] as $matched)
                                    <a href="{{ route('leads.show', $matched['id']) }}"
                                       wire:navigate
                                       wire:click="markRead('{{ $notification->id }}')"
                                       class="badge badge-outline badge-sm hover:badge-primary cursor-pointer">
                                        {{ $matched['name_company'] ?? ('Lead #' . $matched['id']) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Indicador y acciones - Columna en móvil, fila en desktop --}}
                    <div class="flex items-center justify-between sm:justify-end gap-2 mt-2 sm:mt-0">
                        {{-- Indicador no leído --}}
                        @if(is_null($notification->read_at))
                            <span class="flex-shrink-0">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
                                </span>
                            </span>
                        @endif

                        {{-- Acciones --}}
                        <div class="flex items-center gap-1">
                            @if(isset($notification->data['url']) && $notification->data['url'] !== '#')
                                <x-mary-button
                                        icon="o-arrow-top-right-on-square"
                                        :link="$notification->data['url']"
                                        wire:navigate
                                        wire:click="markRead('{{ $notification->id }}')"
                                        class="btn-ghost btn-xs"
                                        tooltip="Ver lead"
                                />
                            @endif

                            @if(is_null($notification->read_at))
                                <x-mary-button
                                        icon="o-check"
                                        wire:click="markRead('{{ $notification->id }}')"
                                        class="btn-ghost btn-xs"
                                        tooltip="Marcar como leída"
                                />
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12 sm:py-16 text-center px-4">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-base-200 flex items-center justify-center mb-4">
                        <x-mary-icon name="o-bell-slash" class="w-8 h-8 sm:w-10 sm:h-10 text-base-content/30" />
                    </div>
                    <p class="text-sm font-medium text-base-content/60 mb-1">Sin notificaciones</p>
                    <p class="text-xs text-base-content/40">
                        @if($filterRead === 'unread')
                            No tienes notificaciones sin leer
                        @elseif($filterRead === 'read')
                            No tienes notificaciones leídas
                        @else
                            No has recibido notificaciones aún
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- PAGINACIÓN - Scroll horizontal en móvil si es necesario --}}
        @if($notifications->hasPages())
            <div class="mt-4 px-3 sm:px-4 overflow-x-auto">
                <div class="min-w-max sm:min-w-0">
                    {{ $notifications->links() }}
                </div>
            </div>
        @endif
    </x-mary-card>
</section>

{{-- Estilos adicionales para breakpoint extra pequeño --}}
<style>
    @media (min-width: 480px) {
        .xs\:flex {
            display: flex;
        }
        .xs\:inline {
            display: inline;
        }
    }

    @media (max-width: 479px) {
        .xs\:flex {
            display: none;
        }
        .xs\:inline {
            display: none;
        }
    }
</style>