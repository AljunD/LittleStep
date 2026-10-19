@extends('components.app')

@section('title', 'Edit Checklist Item')

@section('content')
<div class="space-y-6">
    <x-page-header title="Edit Checklist Item" description="{{ $domainLabel }} · Item {{ $item->item_number }}" />

    <form id="editChecklistItemForm" method="POST" action="{{ route('checklist.update', $item) }}"
          class="max-w-3xl overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
        @csrf
        @method('PUT')

        <div class="space-y-6 p-6">
            <x-validation-errors class="mb-0" />

            <div class="flex gap-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-800" role="note">
                <i class="fas fa-circle-info mt-0.5" aria-hidden="true"></i>
                <p>
                    Changes apply to every teacher and appear on evaluation forms, progress records and the guardian app right away.
                    Only fix the wording: the domain, item number and item list are official and are used to compute the scores.
                </p>
            </div>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Domain</dt>
                    <dd class="mt-1"><x-badge color="blue">{{ $domainLabel }}</x-badge></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Item Number</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-800">{{ $item->item_number }}</dd>
                </div>
            </dl>

            <x-settings-field name="activity" label="Activity" :value="old('activity', $item->activity)" maxlength="255" required />
            <x-settings-field type="textarea" name="materials_and_procedure" label="Materials and Procedure (optional)"
                              :value="old('materials_and_procedure', $item->materials_and_procedure)" rows="6"
                              :maxlength="\App\Http\Requests\UpdateChecklistItemRequest::MATERIALS_MAX_LENGTH"
                              hint="Leave empty when a parent's report is enough. Line breaks are kept." />
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4">
            <x-button variant="secondary" :href="route('checklist.index', ['domain' => $item->domain])">Cancel</x-button>
            <x-button type="submit" icon="save">Save Changes</x-button>
        </div>
    </form>
</div>
@endsection
