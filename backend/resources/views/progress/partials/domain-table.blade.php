{{-- resources/views/progress/partials/domain-table.blade.php --}}
<div class="bg-white border-2 border-black overflow-hidden rounded-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse">
            <thead>
                <tr class="bg-black text-white">
                    <th colspan="2" class="border border-gray-800 px-4 py-3 text-xs font-bold uppercase text-left tracking-wider">
                        {{ $domainName }}
                    </th>
                    <th class="border border-gray-800 px-4 py-3 text-xs font-bold uppercase text-left w-1/3">
                        Material / Procedure
                    </th>
                    <th class="border border-gray-800 px-2 py-3 text-xs font-bold uppercase text-center">
                        Present
                    </th>
                    <th class="border border-gray-800 px-4 py-3 text-xs font-bold uppercase text-left">
                        Comments
                    </th>
                </tr>
                <tr class="bg-gray-100 text-[10px] font-bold uppercase text-gray-600">
                    <th colspan="3" class="border-r border-black"></th>
                    <th class="border-r border-black p-0">
                        <div class="flex text-center border-b border-gray-300">
                            <div class="w-20 border-r border-gray-300 py-1">1st Eval</div>
                            <div class="w-20 border-r border-gray-300 py-1">2nd Eval</div>
                            <div class="w-20 py-1">3rd Eval</div>
                        </div>
                    </th>
                    <th class="bg-gray-50 border-b border-black"></th>
                </tr>
            </thead>

            <tbody class="text-sm divide-y divide-gray-300">
                @php
                    $total1 = 0;
                    $total2 = 0;
                    $total3 = 0;
                @endphp

                @foreach($items as $item)
                    @php
                        $raw1 = $item['val'][0] ?? null;
                        $raw2 = $item['val'][1] ?? null;
                        $raw3 = $item['val'][2] ?? null;

                        $isCheck1 = in_array($raw1, ['check', true, 1, '1'], true);
                        $isCheck2 = in_array($raw2, ['check', true, 1, '1'], true);
                        $isCheck3 = in_array($raw3, ['check', true, 1, '1'], true);
                        $isHyphen1 = in_array($raw1, ['hyphen', '—', false, 0, '0'], true);
                        $isHyphen2 = in_array($raw2, ['hyphen', '—', false, 0, '0'], true);
                        $isHyphen3 = in_array($raw3, ['hyphen', '—', false, 0, '0'], true);

                        $total1 += $isCheck1 ? 1 : 0;
                        $total2 += $isCheck2 ? 1 : 0;
                        $total3 += $isCheck3 ? 1 : 0;

                        $comment = $item['comments'] ?? '';
                    @endphp

                    <tr>
                        <td class="px-2 py-4 text-center font-bold w-10 border-r border-gray-300">
                            {{ $item['id'] }}.
                        </td>

                        <td class="px-4 py-4 font-medium text-gray-800 border-r border-gray-300">
                            {{ $item['task'] }}
                        </td>

                        <td class="px-4 py-4 text-xs text-gray-600 border-r border-gray-300 italic">
                            {!! nl2br(e($item['proc'] ?? '')) !!}
                        </td>

                        <td class="p-0 border-r border-gray-300">
                            <div class="flex h-full min-h-[60px] text-center items-center justify-center">
                                <div class="w-20 border-r border-gray-100 font-bold text-lg {{ $isCheck1 ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ $isCheck1 ? '✓' : ($isHyphen1 ? '—' : '') }}
                                </div>
                                <div class="w-20 border-r border-gray-100 font-bold text-lg {{ $isCheck2 ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ $isCheck2 ? '✓' : ($isHyphen2 ? '—' : '') }}
                                </div>
                                <div class="w-20 font-bold text-lg {{ $isCheck3 ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ $isCheck3 ? '✓' : ($isHyphen3 ? '—' : '') }}
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-4 text-xs italic text-gray-500">
                            {{ $comment !== '' ? $comment : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr class="bg-gray-100 font-bold">
                    <td colspan="3" class="px-4 py-3 text-right border-t-2 border-black">
                        TOTAL SCORE
                    </td>
                    <td class="p-0 border-t-2 border-black">
                        <div class="flex text-center font-bold text-lg">
                            <div class="w-20 border-r border-gray-300 py-3">{{ $total1 }}</div>
                            <div class="w-20 border-r border-gray-300 py-3">{{ $total2 }}</div>
                            <div class="w-20 py-3">{{ $total3 }}</div>
                        </div>
                    </td>
                    <td class="border-t-2 border-black"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>