<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/littlestep-mark.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        /* Custom Scrollbar for the form area */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
        /* Set here, not as a Tailwind bg-[url(...)] class: the URL comes from asset() at runtime,
           so Tailwind never sees the class name and the background would be missing from the build. */
        .auth-backdrop {
            background-image: url('{{ asset('images/background.png') }}');
        }
    </style>
</head>
<body class="h-full w-full flex items-center justify-center overflow-hidden bg-black font-[Inter] antialiased">

    <div class="auth-backdrop absolute inset-0 bg-cover bg-center blur-md scale-110" aria-hidden="true"></div>

    <div class="relative z-10 bg-white w-full max-w-6xl h-full md:h-[min(800px,95vh)] md:max-h-[800px] md:rounded-3xl shadow-2xl flex flex-col md:flex-row border border-gray-200 overflow-hidden">

        <div class="w-full md:w-1/2 overflow-y-auto custom-scrollbar bg-white">
            <div class="min-h-full flex flex-col justify-center p-8 md:p-12 lg:p-16">
                <img src="{{ asset('images/littlestep-logo.png') }}" alt="LittleStep" width="612" height="408"
                     class="mx-auto mb-6 h-auto w-40">
                <x-flash-messages />
                @yield('content')
                <p class="mt-8 text-center text-xs text-gray-400">
                    <a href="{{ route('privacy') }}" class="underline underline-offset-2 hover:text-gray-600">Privacy Policy</a>
                </p>
            </div>
        </div>

        <div class="hidden md:block w-1/2 relative">
            <img src="{{ asset('images/form.png') }}"
                class="absolute inset-0 w-full h-full object-cover" alt="Background">

            <div class="absolute inset-0 flex justify-center items-start z-20">
                <img src="{{ asset('images/logo.png') }}" class="w-48 h-48 mt-16 object-contain drop-shadow-xl">
            </div>

            <div class="absolute inset-0 bg-gradient-to-l from-transparent via-transparent to-white/10"></div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('cspNonce') }}">
        document.addEventListener('click', (event) => {
            event.target.closest('[data-dismiss-alert]')?.closest('[data-flash]')?.remove();
        });

        (() => {
            const unlockForm = (form) => {
                form.dataset.submitting = 'false';
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                    button.disabled = false;
                    if (button.dataset.originalText !== undefined) {
                        button.textContent = button.dataset.originalText;
                    }
                });
            };

            document.querySelectorAll('form[data-lock-submit]').forEach((form) => {
                form.dataset.submitting = 'false';

                form.addEventListener('submit', (event) => {
                    if (form.dataset.submitting === 'true') {
                        event.preventDefault();
                        return;
                    }

                    form.dataset.submitting = 'true';
                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                        button.disabled = true;
                        if (button.dataset.submittingText) {
                            button.dataset.originalText = button.textContent;
                            button.textContent = button.dataset.submittingText;
                        }
                    });
                });
            });

            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    document.querySelectorAll('form[data-lock-submit]').forEach(unlockForm);
                }
            });
        })();
    </script>

</body>
</html>