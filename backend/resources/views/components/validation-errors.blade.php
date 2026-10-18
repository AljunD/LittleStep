{{--
    Summary of form validation errors, styled like the error banner of the flash-messages component.
    Field-level @error messages under inputs can be shown alongside it.
--}}
@props(['title' => 'Please correct the following errors:'])

@if ($errors->any())
    <div data-validation-errors role="alert"
         {{ $attributes->merge(['class' => 'p-4 mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm']) }}>
        <div class="flex items-start gap-3">
            <div aria-hidden="true"
                 class="w-8 h-8 shrink-0 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold text-sm">
                ✕
            </div>
            <div class="text-left">
                <p class="font-semibold text-sm">{{ $title }}</p>
                <ul class="mt-1 list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
