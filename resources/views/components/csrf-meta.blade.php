@section('head')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Verify CSRF token is available and log it
        document.addEventListener('DOMContentLoaded', function() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) {
                console.error('CSRF Token not found in meta tag!');
            } else {
                console.log('CSRF Token loaded successfully');
            }
            
            // Verify session cookie exists
            const cookieString = document.cookie;
            if (!cookieString.includes('XSRF-TOKEN') && !cookieString.includes('_token')) {
                console.warn('Session cookie may not be properly set');
            }
        });

        // Add CSRF token to all AJAX requests automatically
        window.addEventListener('beforeunload', function() {
            document.querySelectorAll('form').forEach(form => {
                if (form.method.toUpperCase() === 'POST') {
                    const token = document.querySelector('meta[name="csrf-token"]');
                    if (token && !form.querySelector('input[name="_token"]')) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_token';
                        input.value = token.content;
                        form.appendChild(input);
                    }
                }
            });
        });
    </script>
@endsection
