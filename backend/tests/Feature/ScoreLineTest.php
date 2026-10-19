<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScoreLineTest extends TestCase
{
    private function render(array $scores, array $props = []): string
    {
        $attributes = collect($props)->map(fn ($value, $name) => ":{$name}=\"".var_export($value, true).'"')->implode(' ');

        return (string) $this->blade("<x-score-line :scores=\"\$scores\" {$attributes} />", ['scores' => $scores]);
    }

    private function lines(string $html): array
    {
        preg_match_all('/<line x1="([\d.]+)" y1="([\d.]+)" x2="([\d.]+)" y2="([\d.]+)"/', $html, $matches, PREG_SET_ORDER);

        return array_map(fn ($match) => array_map('floatval', array_slice($match, 1)), $matches);
    }

    public function test_it_connects_scaled_scores_through_the_center_of_their_rows(): void
    {
        $html = $this->render([10, 12, 7]);

        // The SVG must fill a stretched wrapper div: an absolutely positioned <svg> would keep its intrinsic size.
        $this->assertMatchesRegularExpression('/<div data-score-line[^>]*class="pointer-events-none absolute"[^>]*>\s*<svg class="block h-full w-full" viewBox="0 0 300 1900" preserveAspectRatio="none"/', $html);
        $this->assertStringContainsString('vector-effect="non-scaling-stroke"', $html);
        // Column centers are 50, 150, 250; score s sits in row (19 - s), centered: (19 - s + 0.5) * 100.
        $this->assertSame([[50.0, 950.0, 150.0, 750.0], [150.0, 750.0, 250.0, 1250.0]], $this->lines($html));
    }

    public function test_it_connects_standard_scores_on_a_continuous_scale(): void
    {
        $html = $this->render([100, 130, 160], ['min' => 20, 'max' => 160, 'rows' => false]);

        $this->assertStringContainsString('viewBox="0 0 300 1000"', $html);
        // 160 is the top edge and 20 the bottom edge: y = (160 - s) / 140 * 1000.
        $this->assertSame([[50.0, 428.57, 150.0, 214.29], [150.0, 214.29, 250.0, 0.0]], $this->lines($html));
    }

    public function test_a_missing_or_out_of_range_score_breaks_the_line(): void
    {
        $this->assertSame([[250.0, 1250.0, 350.0, 1150.0]], $this->lines($this->render([10, null, 7, 8])));
        $this->assertSame([], $this->lines($this->render([100, 170, 90], ['min' => 20, 'max' => 160, 'rows' => false])));
    }

    public function test_nothing_is_drawn_without_two_neighbouring_scores(): void
    {
        foreach ([[], [null, null], [10], [10, null, 7], [10, 0, 20]] as $scores) {
            $this->assertStringNotContainsString('<svg', $this->render($scores));
        }
    }
}
