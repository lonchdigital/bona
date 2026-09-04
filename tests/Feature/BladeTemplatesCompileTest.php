<?php

namespace Tests\Feature;

use Illuminate\View\Compilers\BladeCompiler;
use ParseError;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Every Blade template must compile to PHP that actually parses.
 *
 * A template can look perfectly balanced and still compile to broken PHP when
 * a word inside it happens to match a directive the framework has since added.
 * That is how a whole product category went down after the Laravel upgrade: a
 * JSON-LD block carried the key "@context", Laravel 11 had introduced a
 *
 * @context directive, and Blade turned the key into an if() that nothing ever
 * closed. Nothing failed at deploy time -- only the page did, at request time.
 */
class BladeTemplatesCompileTest extends TestCase
{
    public function test_every_template_compiles_to_valid_php(): void
    {
        $compiler = $this->app->make(BladeCompiler::class);
        $root = resource_path('views');

        $templates = Finder::create()->files()->in($root)->name('*.blade.php');

        $broken = [];
        $checked = 0;

        foreach ($templates as $template) {
            $checked++;

            $compiled = $compiler->compileString((string) file_get_contents($template->getRealPath()));

            try {
                token_get_all($compiled, TOKEN_PARSE);
            } catch (ParseError $error) {
                $broken[] = sprintf(
                    '%s — %s',
                    ltrim(str_replace($root, '', $template->getRealPath()), '/'),
                    $error->getMessage(),
                );
            }
        }

        $this->assertGreaterThan(100, $checked, 'Шаблонів знайшлось підозріло мало.');
        $this->assertSame([], $broken, "Ці шаблони компілюються у зламаний PHP:\n".implode("\n", $broken));
    }

    public function test_json_ld_context_keys_cannot_be_compiled_as_blade_context_directives(): void
    {
        $root = resource_path('views');
        $templates = Finder::create()->files()->in($root)->name('*.blade.php');
        $unsafe = [];

        foreach ($templates as $template) {
            if (str_contains((string) file_get_contents($template->getRealPath()), "'@context'")) {
                $unsafe[] = ltrim(str_replace($root, '', $template->getRealPath()), '/');
            }
        }

        $this->assertSame(
            [],
            $unsafe,
            "JSON-LD ключ @context потрібно складати як '@'.'context', інакше Blade перетворює його на директиву:\n".implode("\n", $unsafe),
        );
    }
}
