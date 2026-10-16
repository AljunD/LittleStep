<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ECCD Checklist Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            color: #111;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.25;
        }

        h1, h2, p {
            margin: 0;
        }

        h1 {
            color: #001f5b;
            font-size: 17px;
            margin-bottom: 3px;
            text-align: center;
            text-transform: uppercase;
        }

        h2 {
            color: #001f5b;
            font-size: 13px;
            margin-bottom: 7px;
            text-align: center;
            text-transform: uppercase;
        }

        .subtitle {
            margin-bottom: 12px;
            text-align: center;
        }

        .page-break {
            page-break-after: always;
            break-after: page;
            page-break-inside: avoid;
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        th, td {
            border: 1px solid #333;
            padding: 4px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #e9eef5;
            color: #001f5b;
            font-weight: bold;
            text-align: left;
        }

        .section-heading {
            background: #001f5b;
            color: #fff;
            font-size: 11px;
            text-align: left;
        }

        .label {
            background: #f2f4f7;
            font-weight: bold;
            width: 18%;
        }

        .domain-table {
            font-size: 5.5px;
            line-height: 1;
            page-break-inside: avoid;
        }

        .domain-table th,
        .domain-table td {
            padding: 0.5px;
        }

        .domain-table thead {
            display: table-header-group;
        }

        .domain-table tr {
            page-break-inside: avoid;
        }

        .center {
            text-align: center;
            vertical-align: middle;
        }

        .mark {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        .comment {
            border-top: 1px dotted #aaa;
            color: #444;
            font-size: 4.5px;
            margin-top: 0;
            padding-top: 0;
        }

        .muted {
            color: #666;
        }

        .score-table {
            font-size: 8px;
        }

        .score-table th,
        .score-table td {
            text-align: center;
        }

        .score-table .domain-name {
            text-align: left;
        }

        .total-row td {
            background: #f2f4f7;
            font-weight: bold;
        }

        .notes-table td {
            height: 75px;
            vertical-align: top;
        }

        .notes-label {
            background: #f2f4f7;
            font-weight: bold;
            width: 25%;
        }

        .page-number {
            color: #777;
            font-size: 8px;
            margin-top: 6px;
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $childName = trim(implode(' ', array_filter([
            $child?->first_name,
            $child?->middle_name,
            $child?->last_name,
        ])));
        $guardianName = trim(implode(' ', array_filter([
            $guardian?->first_name,
            $guardian?->middle_name,
            $guardian?->last_name,
        ])));
        $teacherName = trim(implode(' ', array_filter([
            $teacher?->first_name,
            $teacher?->middle_name,
            $teacher?->last_name,
        ])));
        $formatDate = static fn ($date) => $date
            ? \Illuminate\Support\Carbon::parse($date)->format('F j, Y')
            : '—';
        $valueOrDash = static fn ($value) => filled($value) ? $value : '—';
        $roundLabels = [1 => '1st Assessment', 2 => '2nd Assessment', 3 => '3rd Assessment'];
    @endphp

    <div class="page-break">
        <h1>Early Childhood Care and Development Checklist</h1>
        <p class="subtitle">Child's Record 2 | Progress Record #{{ $progressRecord->id }}</p>

        <table>
            <tbody>
                <tr><th class="section-heading" colspan="4">Child Sociodemographic Information</th></tr>
                <tr>
                    <td class="label">Child's Name</td><td>{{ $valueOrDash($childName) }}</td>
                    <td class="label">Sex</td><td>{{ $valueOrDash($child?->sex) }}</td>
                </tr>
                <tr>
                    <td class="label">Date of Birth</td><td>{{ $formatDate($child?->date_of_birth) }}</td>
                    <td class="label">Handedness</td><td>{{ $valueOrDash($child?->handedness) }}</td>
                </tr>
                <tr>
                    <td class="label">Address</td><td>{{ $valueOrDash($child?->address) }}</td>
                    <td class="label">Currently Studying</td><td>{{ $child?->is_studying ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td class="label">School Name</td><td>{{ $valueOrDash($child?->school_name) }}</td>
                    <td class="label">Guardian</td><td>{{ $valueOrDash($guardianName) }}</td>
                </tr>
                <tr>
                    <td class="label">Guardian Relationship</td><td>{{ $valueOrDash($guardian?->relationship_to_child) }}</td>
                    <td class="label">Guardian Contact</td><td>{{ $valueOrDash($guardian?->contact_number) }}</td>
                </tr>
                <tr>
                    <td class="label">Father's Name / Age</td>
                    <td>{{ $valueOrDash($child?->fathers_name) }} / {{ $valueOrDash($child?->fathers_age) }}</td>
                    <td class="label">Father's Occupation</td><td>{{ $valueOrDash($child?->fathers_occupation) }}</td>
                </tr>
                <tr>
                    <td class="label">Father's Education</td><td>{{ $valueOrDash($child?->fathers_education) }}</td>
                    <td class="label">Mother's Name / Age</td>
                    <td>{{ $valueOrDash($child?->mothers_name) }} / {{ $valueOrDash($child?->mothers_age) }}</td>
                </tr>
                <tr>
                    <td class="label">Mother's Occupation</td><td>{{ $valueOrDash($child?->mothers_occupation) }}</td>
                    <td class="label">Mother's Education</td><td>{{ $valueOrDash($child?->mothers_education) }}</td>
                </tr>
                <tr>
                    <td class="label">Siblings / Birth Order</td>
                    <td>{{ $valueOrDash($child?->number_of_siblings) }} / {{ $valueOrDash($child?->birth_order) }}</td>
                    <td class="label">Examiner</td><td>{{ $valueOrDash($teacherName) }}</td>
                </tr>

                <tr><th class="section-heading" colspan="4">Computation of Age (30-Day Month Rule)</th></tr>
                <tr>
                    <th>Assessment</th>
                    <th>Date Tested</th>
                    <th>Child's Date of Birth</th>
                    <th>Computed Age (Years / Months / Days)</th>
                </tr>
                @foreach ([1, 2, 3] as $round)
                    @php
                        $age = $computedByRound[$round]['age'] ?? null;
                        $ageText = $age
                            ? "{$age['years']} years, {$age['months']} months, {$age['days']} days"
                            : '—';
                    @endphp
                    <tr>
                        <td>{{ $roundLabels[$round] }}</td>
                        <td>{{ $formatDate($assessmentDates[$round] ?? null) }}</td>
                        <td>{{ $formatDate($child?->date_of_birth) }}</td>
                        <td>{{ $ageText }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="page-number">Page 1 of 10</p>
    </div>

    @foreach ($preparedDomains as $domain)
        <div class="page-break">
            <h2>{{ $domain['label'] }}</h2>
            <table class="domain-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 22%;">Checklist Activity</th>
                        <th style="width: 31%;">Materials / Procedure</th>
                        <th class="center" style="width: 14%;">1st Eval<br>Mark / Comment</th>
                        <th class="center" style="width: 14%;">2nd Eval<br>Mark / Comment</th>
                        <th class="center" style="width: 14%;">3rd Eval<br>Mark / Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($domain['items'] as $item)
                        <tr>
                            <td class="center">{{ $item['number'] }}</td>
                            <td>{{ $item['activity'] }}</td>
                            <td>{{ $item['procedure'] }}</td>
                            @foreach ([1, 2, 3] as $round)
                                <td>
                                    <div class="mark">{{ $item['rounds'][$round]['mark'] ?: ' ' }}</div>
                                    <div class="comment">
                                        {{ $item['rounds'][$round]['comment'] !== '' ? $item['rounds'][$round]['comment'] : ' ' }}
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Raw Total Score</th>
                        @foreach ([1, 2, 3] as $round)
                            <th class="center">
                                {{ $computedByRound[$round]['raw_scores'][$domain['key']] ?? '—' }}
                            </th>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
            <p class="page-number">Page {{ $loop->iteration + 1 }} of 10</p>
        </div>
    @endforeach

    <div class="page-break">
        <h2>Qualitative Domain Observation Notes</h2>
        <table class="notes-table">
            <tbody>
                <tr><th class="section-heading" colspan="2">Observation Context</th></tr>
                <tr>
                    <td class="notes-label">Place Administered</td>
                    <td>{{ $valueOrDash($observation?->place_administered) }}</td>
                </tr>
                <tr><th class="section-heading" colspan="2">Qualitative Observations</th></tr>
                <tr>
                    <td class="notes-label">Child Background</td>
                    <td>{{ $valueOrDash($observation?->background_observations) }}</td>
                </tr>
                <tr>
                    <td class="notes-label">Family Environment</td>
                    <td>{{ $valueOrDash($observation?->family_environment_notes) }}</td>
                </tr>
                <tr>
                    <td class="notes-label">Stimulating Activities</td>
                    <td>{{ $valueOrDash($observation?->stimulating_activities_notes) }}</td>
                </tr>
                <tr>
                    <td class="notes-label">Home Environment</td>
                    <td>{{ $valueOrDash($observation?->home_environment_notes) }}</td>
                </tr>
                <tr>
                    <td class="notes-label">Others</td>
                    <td>{{ $valueOrDash($observation?->other_notes) }}</td>
                </tr>
            </tbody>
        </table>
        <p class="page-number">Page 9 of 10</p>
    </div>

    <div>
        <h2>Summary of Scores</h2>
        <table class="score-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 19%;">Domain / Summary</th>
                    @foreach ([1, 2, 3] as $round)
                        <th colspan="2">{{ $roundLabels[$round] }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ([1, 2, 3] as $round)
                        <th>Date / Age</th>
                        <th>Raw / Scaled</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($domainLabels as $domainKey => $domainLabel)
                    <tr>
                        <td class="domain-name">{{ $domainLabel }}</td>
                        @foreach ([1, 2, 3] as $round)
                            @php
                                $roundResult = $computedByRound[$round];
                                $age = $roundResult['age'] ?? null;
                            @endphp
                            <td>
                                {{ $formatDate($assessmentDates[$round] ?? null) }}<br>
                                <span class="muted">{{ $age['label'] ?? 'Age —' }}</span>
                            </td>
                            <td>
                                Raw: {{ $roundResult['raw_scores'][$domainKey] ?? '—' }}<br>
                                Scaled: {{ $roundResult['scaled_scores'][$domainKey] ?? '—' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="domain-name">Sum of Scaled Scores</td>
                    @foreach ([1, 2, 3] as $round)
                        <td colspan="2">{{ $computedByRound[$round]['sum_scaled_score'] ?? '—' }}</td>
                    @endforeach
                </tr>
                <tr class="total-row">
                    <td class="domain-name">Standard Score</td>
                    @foreach ([1, 2, 3] as $round)
                        <td colspan="2">{{ $computedByRound[$round]['standard_score'] ?? '—' }}</td>
                    @endforeach
                </tr>
                <tr class="total-row">
                    <td class="domain-name">Interpretation</td>
                    @foreach ([1, 2, 3] as $round)
                        <td colspan="2">{{ $computedByRound[$round]['interpretation']['overall'] ?? 'Insufficient data' }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        <p class="page-number">Page 10 of 10</p>
    </div>
</body>
</html>
