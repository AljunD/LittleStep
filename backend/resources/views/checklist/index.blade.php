@extends('components.app')

@section('title', 'ECCD Checklist')

@section('content')
<div class="space-y-6">
    <x-page-header title="ECCD Checklist"
                   description="The official checklist items used in every evaluation. You can correct an item's wording; the item list and numbering stay official." />

    <x-search-bar placeholder="Search activities or materials and procedure">
        <x-filter-field name="domain" label="Domain" placeholder="All domains" :options="$domainLabels" />
    </x-search-bar>

    <x-table :columns="['Domain', 'No.', 'Activity', 'Materials and Procedure']" :paginator="$items"
             :empty="request()->anyFilled(['search', 'domain']) ? 'No checklist items match your filters.' : 'The ECCD checklist has not been loaded yet. Run the ECCDChecklistSeeder.'">
        @foreach($items as $item)
            <tr>
                <td class="whitespace-nowrap"><x-badge color="blue">{{ $domainLabels[$item->domain] ?? $item->domain }}</x-badge></td>
                <td class="whitespace-nowrap font-bold text-gray-800">{{ $item->item_number }}</td>
                <td class="min-w-56 font-semibold text-gray-900">{{ $item->activity }}</td>
                <td class="min-w-72 max-w-xl">
                    @if(filled($item->materials_and_procedure))
                        <p class="line-clamp-2" title="{{ $item->materials_and_procedure }}">{{ $item->materials_and_procedure }}</p>
                    @else
                        <span class="text-xs italic text-gray-400">Parental report will suffice.</span>
                    @endif
                </td>
                <td class="whitespace-nowrap text-right">
                    <x-action-menu label="Actions for {{ $domainLabels[$item->domain] ?? $item->domain }} item {{ $item->item_number }}">
                        <x-action-menu.item :href="route('checklist.edit', $item)" icon="edit">Edit Wording</x-action-menu.item>
                    </x-action-menu>
                </td>
            </tr>
        @endforeach
    </x-table>
</div>
@endsection
