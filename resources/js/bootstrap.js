import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Get the CSRF token from the meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Initialize Laravel Echo for real-time communication
try {
    window.Pusher = Pusher;

    // For debugging - show what env variables are available
    console.log('Pusher Key:', import.meta.env.VITE_PUSHER_APP_KEY);
    console.log('Pusher Cluster:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '39575501d1d95209b931', // Hardcoded for immediate fix
        cluster: 'eu',
        wsHost: window.location.hostname,
        wsPort: 6001,
        wssPort: 6001,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    });

    console.log('WebSocket connection initialized');
} catch (e) {
    console.warn('Error initializing WebSocket connection:', e);
    console.error(e);
}

// Remove this import if echo.js doesn't exist
// import './echo';
