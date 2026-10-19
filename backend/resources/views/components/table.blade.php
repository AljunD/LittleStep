@props(['columns' => [], 'actions' => true, 'paginator' => null, 'empty' => 'Nothing to show yet.'])
{{-- Shared index table: pass column labels, rows as the slot, and an optional paginator for the footer. --}}
<div {{ $attributes->class('overflow-hidden rounded-2xl border border-gray-200/70 bg-white') }}>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">{{ $column }}</th>
                    @endforeach
                    @if ($actions)
                        <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-600 [&>tr]:transition-colors [&>tr:hover]:bg-gray-50/70 [&_td]:px-6 [&_td]:py-4">
                @if ($slot->isEmpty())
                    <tr>
                        <td colspan="{{ count($columns) + ($actions ? 1 : 0) }}" class="text-center">
                            <div class="flex flex-col items-center gap-2 py-8 text-gray-400">
                                <i class="fas fa-inbox text-2xl text-gray-300" aria-hidden="true"></i>
                                <p class="text-sm">{{ $empty }}</p>
                            </div>
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    @if ($paginator && $paginator->total() > 0)
        <div class="border-t border-gray-100 px-6 py-4">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
