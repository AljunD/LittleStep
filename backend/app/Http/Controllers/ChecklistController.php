<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistIndexRequest;
use App\Http\Requests\UpdateChecklistItemRequest;
use App\Models\EccdChecklistItem;
use App\Services\EccdExpertSystem;
use App\Support\Search;

class ChecklistController extends Controller
{
    public function index(ChecklistIndexRequest $request)
    {
        $items = Search::apply(EccdChecklistItem::query(), $request->search(), ['activity', 'materials_and_procedure'])
            ->when($request->validated('domain'), fn ($query, $domain) => $query->where('domain', $domain))
            ->inOfficialOrder()
            ->paginate(15)
            ->withQueryString();

        return view('checklist.index', [
            'items' => $items,
            'domainLabels' => EccdExpertSystem::DOMAIN_LABELS,
        ]);
    }

    public function edit(EccdChecklistItem $checklistItem)
    {
        return view('checklist.edit', [
            'item' => $checklistItem,
            'domainLabel' => EccdExpertSystem::DOMAIN_LABELS[$checklistItem->domain] ?? $checklistItem->domain,
        ]);
    }

    public function update(UpdateChecklistItemRequest $request, EccdChecklistItem $checklistItem)
    {
        $checklistItem->fill($request->validated());
        $backToList = redirect()->route('checklist.index', ['domain' => $checklistItem->domain]);

        if (! $checklistItem->isDirty()) {
            return $backToList->with('status', 'No changes to save.');
        }

        $changedFields = $this->changedFieldsList($checklistItem);
        $checklistItem->save();

        recordLog('updated', 'ChecklistItem', $checklistItem->id, sprintf(
            'Checklist item updated: %s #%d (%s).',
            EccdExpertSystem::DOMAIN_LABELS[$checklistItem->domain] ?? $checklistItem->domain,
            $checklistItem->item_number,
            $changedFields
        ));

        return $backToList->with('success', 'Checklist item updated.');
    }
}
