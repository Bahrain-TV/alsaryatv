<div>
    <!-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Marie Curie -->
    <button {{ $attributes->merge(['class' => 'voicu-button']) }}>
    {{ $slot }}
    </button>

    @push('styles')
        <style>
            .voicu-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                background-color: #3498db;
                color: white;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: background-color 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            .voicu-button:hover {
                background-color: #2980b9;
            }

            .voicu-button:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.5);
            }
        </style>
    @endpush
</div>
