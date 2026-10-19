@extends('components.app')

@section('title', 'Children')

@section('content')
<div class="space-y-6">
    <x-page-header title="Children" description="Manage and view all registered child profiles." />

    <x-search-bar placeholder="Search by child or guardian name">
        <x-filter-field name="sex" label="Sex" placeholder="All sexes"
                        :options="array_combine(\App\Support\FormRules::SEXES, \App\Support\FormRules::SEXES)" />
    </x-search-bar>

    <x-table :columns="['Child', 'Sex', 'Guardian']" :paginator="$children"
             :empty="request()->anyFilled(['search', 'sex']) ? 'No children match your filters.' : 'No children registered yet.'">
        @foreach($children as $child)
            <tr>
                <td class="whitespace-nowrap">
                    <x-person :name="$child->first_name.' '.$child->middle_name.' '.$child->last_name"
                              :subtitle="$child->date_of_birth ? 'Born '.\Carbon\Carbon::parse($child->date_of_birth)->format('M d, Y') : null" />
                </td>
                <td class="whitespace-nowrap">{{ $child->sex }}</td>
                <td class="whitespace-nowrap">
                    @if($child->guardian)
                        {{ $child->guardian->first_name }} {{ $child->guardian->last_name }}
                    @else
                        <span class="text-xs italic text-gray-400">No guardian linked</span>
                    @endif
                </td>
                <td class="whitespace-nowrap text-right">
                    <x-action-menu label="Actions for {{ $child->first_name }} {{ $child->last_name }}">
                        <x-action-menu.item :href="route('children.show', $child->id)" icon="view">View</x-action-menu.item>
                        <x-action-menu.item :href="route('children.edit', $child->id)" icon="edit">Edit</x-action-menu.item>
                    </x-action-menu>
                </td>
            </tr>
        @endforeach
    </x-table>
</div>
@endsection
