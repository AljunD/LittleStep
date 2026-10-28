@php
    $flashMessages = array_filter([
        'success' => session('success') ?? session('status'),
        'error' => session('error'),
    ], fn ($message) => is_string($message) && $message !== '');

    // A new guardian's temporary password, shown with the success message on the one page after it was created.
    $temporaryPassword = session('temporary_password');

    $flashStyles = [
        'success' => [
            'title' => 'Success',
            'icon' => '✓',
            'role' => 'status',
            'box' => 'bg-green-50 border-green-200 text-green-800',
            'badge' => 'bg-green-100 text-green-700',
            'text' => 'text-green-700',
            'close' => 'text-green-600 hover:text-green-800',
        ],
        'error' => [
            'title' => 'Error',
            'icon' => '✕',
            'role' => 'alert',
            'box' => 'bg-red-50 border-red-200 text-red-800',
            'badge' => 'bg-red-100 text-red-700',
            'text' => 'text-red-700',
            'close' => 'text-red-600 hover:text-red-800',
        ],
    ];
@endphp

@if ($flashMessages)
    <div class="mb-6 space-y-3">
        @foreach ($flashMessages as $type => $message)
            @php($style = $flashStyles[$type])
            <div data-flash="{{ $type }}" role="{{ $style['role'] }}"
                 class="p-4 border rounded-xl shadow-sm flex items-start justify-between gap-3 {{ $style['box'] }}">
                <div class="flex items-start gap-3">
                    <div aria-hidden="true"
                         class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center font-bold text-sm {{ $style['badge'] }}">
                        {{ $style['icon'] }}
                    </div>
                    <div>
                        <p class="font-semibold text-sm">{{ $style['title'] }}</p>
                        <p class="text-sm {{ $style['text'] }}">{{ $message }}</p>
                        @if ($type === 'success' && is_string($temporaryPassword) && $temporaryPassword !== '')
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide {{ $style['text'] }}">Temporary password</p>
                            <p data-temporary-password
                               class="mt-1 inline-block select-all rounded-lg border border-green-200 bg-white px-3 py-1.5 font-mono text-lg font-bold tracking-wider text-gray-900">{{ $temporaryPassword }}</p>
                        @endif
                    </div>
                </div>
                <button type="button" data-dismiss-alert aria-label="Dismiss message"
                        class="text-lg font-bold leading-none px-2 {{ $style['close'] }}">&times;</button>
            </div>
        @endforeach
    </div>
@endif
