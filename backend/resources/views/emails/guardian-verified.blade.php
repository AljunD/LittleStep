<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome to LittleStep</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-teal-950 via-teal-800 to-emerald-700 px-4 py-10 text-slate-800">
    <main class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-3xl items-center justify-center">
        <section class="w-full overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="relative overflow-hidden bg-gradient-to-r from-teal-600 to-emerald-500 px-8 py-10 text-center text-white sm:px-14 sm:py-14">
                <div class="absolute -right-12 -top-16 h-48 w-48 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-24 -left-10 h-56 w-56 rounded-full bg-white/10"></div>

                <div class="relative mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-white text-emerald-600 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </div>

                <p class="relative mb-2 text-sm font-bold uppercase tracking-[0.2em] text-emerald-50">LittleStep Guardian</p>
                <h1 class="relative text-3xl font-extrabold sm:text-4xl">Welcome to LittleStep!</h1>
                <p class="relative mx-auto mt-4 max-w-lg text-base leading-relaxed text-teal-50 sm:text-lg">
                    Your email is verified and your guardian account is ready.
                </p>
            </div>

            <div class="px-7 py-8 sm:px-14 sm:py-10">
                <div class="rounded-2xl border border-teal-100 bg-teal-50 p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-teal-950">You’re all set</h2>
                    <p class="mt-2 leading-relaxed text-slate-600">
                        You can now sign in to the LittleStep mobile app with the email address you verified. Your account is ready to help you follow your child’s learning progress.
                    </p>
                </div>

                <div class="mt-7 flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="6" y="2" width="12" height="20" rx="2" />
                            <path stroke-linecap="round" d="M11 18h2" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-900">Next step</h2>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                            @if ($appLoginUrl)
                                On the phone where LittleStep is installed, tap the button below to open the app and log in with your verified account.
                            @else
                                Open the LittleStep mobile app and log in with your verified account.
                            @endif
                            You can close this page and return to the app whenever you’re ready.
                        </p>
                    </div>
                </div>

                @if ($appLoginUrl)
                    <div class="mt-7 text-center">
                        <a id="open-guardian-app" href="{{ $appLoginUrl }}" rel="noopener"
                           class="inline-flex h-12 w-full max-w-sm items-center justify-center gap-2 rounded-xl bg-teal-600 px-6 text-base font-semibold text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="6" y="2" width="12" height="20" rx="2" />
                                <path stroke-linecap="round" d="M11 18h2" />
                            </svg>
                            Open LittleStep App
                        </a>
                        <p class="mt-3 text-xs text-slate-500">
                            Nothing happens? Make sure the LittleStep app is installed on this phone, then open it and log in.
                        </p>
                    </div>
                @endif

                <div class="mt-8 border-t border-slate-100 pt-6 text-center">
                    <p class="text-sm text-slate-500">Thank you for being part of your child’s learning journey.</p>
                    <a href="{{ url('/') }}" class="mt-4 inline-flex font-semibold text-teal-700 underline decoration-teal-300 underline-offset-4 hover:text-teal-900">
                        Visit LittleStep
                    </a>
                </div>
            </div>
        </section>
    </main>

    @if ($appLoginUrl && $openAppAutomatically)
        <script nonce="{{ request()->attributes->get('cspNonce') }}">
            (() => {
                const link = document.getElementById('open-guardian-app');
                if (link && /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                    window.setTimeout(() => { window.location.href = link.href; }, 600);
                }
            })();
        </script>
    @endif
</body>
</html>
