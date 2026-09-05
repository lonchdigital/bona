<?php

namespace Tests\Unit;

use App\Services\SerpAgent\SerpAgentHtmlService;
use Tests\TestCase;

class SerpAgentHtmlServiceTest extends TestCase
{
    public function test_it_decorates_legacy_table_advice_and_bold_qa_markup(): void
    {
        $html = '<table><tr><td>Дані</td></tr></table>'
            .'<p><strong>Порада:</strong> Перевірте умови.</p>'
            .'<p><strong>Q: Скільки діє право на обмін?</strong><br><strong>А:</strong> Чотирнадцять днів.</p>';

        $result = app(SerpAgentHtmlService::class)->decorateForDisplay($html, 'uk');

        $this->assertStringContainsString('class="article-table" tabindex="0"', $result);
        $this->assertStringContainsString('class="article-advice"', $result);
        $this->assertStringContainsString('class="article-qa"', $result);
        $this->assertStringContainsString('Питання', $result);
        $this->assertStringContainsString('Відповідь', $result);
        $this->assertStringNotContainsString('Q:', $result);
    }

    public function test_display_decoration_is_idempotent(): void
    {
        $service = app(SerpAgentHtmlService::class);
        $once = $service->decorateForDisplay(
            '<p><strong>Порада:</strong> Перевірте умови.</p><table><tr><td>Дані</td></tr></table>',
            'uk',
        );

        $twice = $service->decorateForDisplay($once, 'uk');

        $this->assertSame($once, $twice);
        $this->assertSame(1, substr_count($twice, 'article-advice'));
        $this->assertSame(1, substr_count($twice, 'article-table'));
    }

    public function test_it_makes_legacy_faq_triggers_keyboard_accessible(): void
    {
        $result = app(SerpAgentHtmlService::class)->decorateForDisplay(
            '<section class="article-faq"><h3 class="accordion active">Питання</h3><div class="art-panel">Відповідь</div></section>',
            'uk',
        );

        $this->assertStringContainsString('role="button"', $result);
        $this->assertStringContainsString('tabindex="0"', $result);
        $this->assertStringContainsString('aria-expanded="true"', $result);
    }
}
