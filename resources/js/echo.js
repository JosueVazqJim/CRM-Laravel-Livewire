// import Echo from 'laravel-echo';
//
// import Pusher from 'pusher-js';
// window.Pusher = Pusher;
//
// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     wsHost: import.meta.env.VITE_REVERB_HOST,
//     wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
//     wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Activar/desactivar Reverb según entorno
const reverbEnabled = import.meta.env.VITE_REVERB_ENABLED === 'true';

if (reverbEnabled) {

    window.Echo = new Echo({
        broadcaster: 'reverb',

        key: import.meta.env.VITE_REVERB_APP_KEY,

        wsHost: import.meta.env.VITE_REVERB_HOST,

        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,

        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,

        forceTLS:
            (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',

        enabledTransports: ['ws', 'wss'],
    });

    console.log('[Reverb] WebSocket habilitado');

} else {

    console.log('[Reverb] WebSocket deshabilitado');

}