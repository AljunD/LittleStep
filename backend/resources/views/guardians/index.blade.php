@extends('components.app')

@section('title', 'Guardians')

@section('content')
<div class="space-y-6">
    <x-page-header title="Guardians" description="Manage and view all registered child guardians.">
        <x-button :href="route('guardians.create')" icon="plus">Add Guardian</x-button>
    </x-page-header>

    <x-search-bar placeholder="Search by name, email, contact number, or relationship" />

    <x-table :columns="['Guardian', 'Contact', 'Relationship', 'Linked Children']" :paginator="$guardians"
             :empty="request('search') ? 'No guardians match your search.' : 'No guardians registered yet.'">
        @foreach($guardians as $guardian)
            <tr>
                <td class="whitespace-nowrap">
                    <x-person :name="$guardian->first_name.' '.$guardian->last_name" :subtitle="$guardian->user?->email" />
                </td>
                <td class="whitespace-nowrap">{{ $guardian->contact_number }}</td>
                <td class="whitespace-nowrap">
                    <x-badge color="violet">{{ $guardian->relationship_to_child }}</x-badge>
                </td>
                <td>
                    @if($guardian->children->isEmpty())
                        <span class="text-xs italic text-gray-400">No linked children</span>
                    @else
                        <div class="space-y-0.5">
                            @foreach($guardian->children as $child)
                                <div class="text-gray-800">{{ $child->first_name }} {{ $child->last_name }}</div>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="whitespace-nowrap text-right">
                    <x-action-menu label="Actions for {{ $guardian->first_name }} {{ $guardian->last_name }}">
                        <x-action-menu.item :href="route('guardians.show', $guardian->id)" icon="view">View</x-action-menu.item>
                        <x-action-menu.item :href="route('guardians.edit', $guardian->id)" icon="edit">Edit</x-action-menu.item>
                        <x-action-menu.item :href="route('guardians.create-child', $guardian->id)" icon="link">Link Child</x-action-menu.item>
                        <x-action-menu.item :href="route('guardians.archive-child', $guardian->id)" icon="unlink">Unlink Child</x-action-menu.item>
                        @if($guardian->user && ! $guardian->user->hasVerifiedEmail())
                            <form action="{{ route('guardians.resend-verification', $guardian->id) }}" method="POST">
                                @csrf
                                <x-action-menu.item icon="mail">Resend Verification Email</x-action-menu.item>
                            </form>
                        @endif
                        <form action="{{ route('guardians.destroy', $guardian->id) }}" method="POST"
                              data-confirm="Are you sure you want to archive {{ $guardian->first_name }} {{ $guardian->last_name }} and all linked children records?">
                            @csrf
                            @method('DELETE')
                            <x-action-menu.item icon="archive" variant="danger">Archive</x-action-menu.item>
                        </form>
                    </x-action-menu>
                </td>
            </tr>
        @endforeach
    </x-table>
</div>
@endsection
