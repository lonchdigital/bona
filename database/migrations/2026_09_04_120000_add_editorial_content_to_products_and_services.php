<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('content_blocks')->nullable()->after('meta_tags');
        });

        Schema::table('services_page_sections', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->json('intro')->nullable()->after('description');
            $table->json('content')->nullable()->after('intro');
            $table->json('meta_title')->nullable()->after('button_url');
            $table->json('meta_description')->nullable()->after('meta_title');
            $table->json('meta_keywords')->nullable()->after('meta_description');
            $table->text('meta_tags')->nullable()->after('meta_keywords');
            $table->unsignedInteger('sort_order')->default(0)->after('meta_tags');
        });

        $presets = [
            [
                'slug' => 'konsultatsiia',
                'image' => 'assets/images/services/consultation.webp',
                'title' => ['uk' => 'Консультація', 'ru' => 'Консультация'],
                'description' => [
                    'uk' => 'Персонально підберемо двері, покриття та фурнітуру під ваш інтер’єр і бюджет.',
                    'ru' => 'Персонально подберем двери, покрытия и фурнитуру под ваш интерьер и бюджет.',
                ],
                'button_text' => ['uk' => 'Отримати консультацію', 'ru' => 'Получить консультацию'],
                'button_url' => '#dialog-call-measurer',
                'intro' => [
                    'uk' => 'Допоможемо визначити стиль, конструкцію та комплектацію дверей під ваш інтер’єр, бюджет і щоденні сценарії.',
                    'ru' => 'Поможем определить стиль, конструкцию и комплектацию дверей под ваш интерьер, бюджет и ежедневные сценарии.',
                ],
                'content' => [
                    'uk' => '<h2>Консультація без поспіху</h2><p>У салоні Bona Doors в Одесі менеджер покаже матеріали наживо, пояснить різницю між конструкціями та допоможе зібрати цілісне рішення для всіх приміщень.</p><h3>Що входить</h3><ul><li>підбір моделей, покриттів і фурнітури;</li><li>перевірка сумісності з прорізами та плінтусом;</li><li>попередній розрахунок вартості й термінів;</li><li>план наступних кроків без прихованих умов.</li></ul>',
                    'ru' => '<h2>Консультация без спешки</h2><p>В салоне Bona Doors в Одессе менеджер покажет материалы вживую, объяснит разницу между конструкциями и поможет собрать цельное решение для всех помещений.</p><h3>Что входит</h3><ul><li>подбор моделей, покрытий и фурнитуры;</li><li>проверка совместимости с проемами и плинтусом;</li><li>предварительный расчет стоимости и сроков;</li><li>план следующих шагов без скрытых условий.</li></ul>',
                ],
                'meta_title' => ['uk' => 'Консультація з вибору дверей в Одесі | Bona Doors', 'ru' => 'Консультация по выбору дверей в Одессе | Bona Doors'],
                'meta_description' => ['uk' => 'Професійна консультація з вибору міжкімнатних і вхідних дверей у салонах Bona Doors в Одесі.', 'ru' => 'Профессиональная консультация по выбору межкомнатных и входных дверей в салонах Bona Doors в Одессе.'],
                'meta_keywords' => ['uk' => 'консультація двері Одеса, вибір дверей', 'ru' => 'консультация двери Одесса, выбор дверей'],
            ],
            [
                'slug' => 'vyklyk-maistra',
                'image' => 'assets/images/services/measurement.webp',
                'title' => ['uk' => 'Виклик майстра', 'ru' => 'Вызов мастера'],
                'description' => [
                    'uk' => 'Точно заміряємо прорізи та врахуємо технічні нюанси до оформлення замовлення.',
                    'ru' => 'Точно измерим проемы и учтем технические нюансы до оформления заказа.',
                ],
                'button_text' => ['uk' => 'Викликати майстра', 'ru' => 'Вызвать мастера'],
                'button_url' => '#dialog-call-measurer',
                'intro' => [
                    'uk' => 'Майстер приїде на об’єкт в Одесі, точно перевірить прорізи та зафіксує всі технічні нюанси до замовлення.',
                    'ru' => 'Мастер приедет на объект в Одессе, точно проверит проемы и зафиксирует все технические нюансы до заказа.',
                ],
                'content' => [
                    'uk' => '<h2>Точний замір до виробництва</h2><p>Навіть кілька міліметрів можуть вплинути на монтаж. Майстер перевіряє геометрію прорізу, товщину стін, рівень підлоги та умови примикання.</p><h3>Після виїзду ви отримуєте</h3><ul><li>зафіксовані розміри кожного прорізу;</li><li>рекомендації щодо відкривання й комплектації;</li><li>перелік підготовчих робіт, якщо вони потрібні;</li><li>уточнений кошторис без сюрпризів під час монтажу.</li></ul>',
                    'ru' => '<h2>Точный замер до производства</h2><p>Даже несколько миллиметров могут повлиять на монтаж. Мастер проверяет геометрию проема, толщину стен, уровень пола и условия примыкания.</p><h3>После выезда вы получаете</h3><ul><li>зафиксированные размеры каждого проема;</li><li>рекомендации по открыванию и комплектации;</li><li>перечень подготовительных работ, если они нужны;</li><li>уточненную смету без сюрпризов во время монтажа.</li></ul>',
                ],
                'meta_title' => ['uk' => 'Виклик майстра та замір дверей в Одесі | Bona Doors', 'ru' => 'Вызов мастера и замер дверей в Одессе | Bona Doors'],
                'meta_description' => ['uk' => 'Професійний замір дверних прорізів в Одесі: точні розміри, технічні рекомендації та розрахунок комплектації.', 'ru' => 'Профессиональный замер дверных проемов в Одессе: точные размеры, технические рекомендации и расчет комплектации.'],
                'meta_keywords' => ['uk' => 'замір дверей Одеса, виклик майстра', 'ru' => 'замер дверей Одесса, вызов мастера'],
            ],
            [
                'slug' => 'montazh-dverei',
                'image' => 'assets/images/services/installation.webp',
                'title' => ['uk' => 'Монтаж', 'ru' => 'Монтаж'],
                'description' => [
                    'uk' => 'Професійно встановимо двері, відрегулюємо фурнітуру та перевіримо готовий результат.',
                    'ru' => 'Профессионально установим двери, отрегулируем фурнитуру и проверим готовый результат.',
                ],
                'button_text' => ['uk' => 'Замовити монтаж', 'ru' => 'Заказать монтаж'],
                'button_url' => '#dialog-call-measurer',
                'intro' => [
                    'uk' => 'Акуратно встановимо двері, відрегулюємо фурнітуру та передамо готовий результат із перевіркою кожної деталі.',
                    'ru' => 'Аккуратно установим двери, отрегулируем фурнитуру и передадим готовый результат с проверкой каждой детали.',
                ],
                'content' => [
                    'uk' => '<h2>Монтаж, який завершує інтер’єр</h2><p>Бригада працює з конструкціями, фурнітурою та технологіями виробників, представлених у Bona Doors. Захищаємо поверхні, дотримуємося технологічних зазорів і прибираємо робочу зону.</p><h3>Контроль якості</h3><ul><li>рівна геометрія коробки й полотна;</li><li>точне врізання замків, петель і ручок;</li><li>регулювання плавності відкривання;</li><li>фінальна перевірка разом із клієнтом.</li></ul>',
                    'ru' => '<h2>Монтаж, который завершает интерьер</h2><p>Бригада работает с конструкциями, фурнитурой и технологиями производителей, представленных в Bona Doors. Защищаем поверхности, соблюдаем технологические зазоры и убираем рабочую зону.</p><h3>Контроль качества</h3><ul><li>ровная геометрия коробки и полотна;</li><li>точная врезка замков, петель и ручек;</li><li>регулировка плавности открывания;</li><li>финальная проверка вместе с клиентом.</li></ul>',
                ],
                'meta_title' => ['uk' => 'Професійний монтаж дверей в Одесі | Bona Doors', 'ru' => 'Профессиональный монтаж дверей в Одессе | Bona Doors'],
                'meta_description' => ['uk' => 'Професійне встановлення міжкімнатних і вхідних дверей в Одесі з акуратним монтажем та перевіркою фурнітури.', 'ru' => 'Профессиональная установка межкомнатных и входных дверей в Одессе с аккуратным монтажом и проверкой фурнитуры.'],
                'meta_keywords' => ['uk' => 'монтаж дверей Одеса, встановлення дверей', 'ru' => 'монтаж дверей Одесса, установка дверей'],
            ],
        ];

        $sections = DB::table('services_page_sections')->orderBy('id')->limit(3)->get();
        foreach ($sections as $index => $section) {
            if (! isset($presets[$index])) {
                continue;
            }

            $preset = $presets[$index];
            DB::table('services_page_sections')->where('id', $section->id)->update([
                'slug' => $preset['slug'],
                'title' => json_encode($preset['title'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($preset['description'], JSON_UNESCAPED_UNICODE),
                'button_text' => json_encode($preset['button_text'], JSON_UNESCAPED_UNICODE),
                'button_url' => $preset['button_url'],
                'section_image_path' => $preset['image'],
                'intro' => json_encode($preset['intro'], JSON_UNESCAPED_UNICODE),
                'content' => json_encode($preset['content'], JSON_UNESCAPED_UNICODE),
                'meta_title' => json_encode($preset['meta_title'], JSON_UNESCAPED_UNICODE),
                'meta_description' => json_encode($preset['meta_description'], JSON_UNESCAPED_UNICODE),
                'meta_keywords' => json_encode($preset['meta_keywords'], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
            ]);
        }

        DB::table('services_page_sections')
            ->whereNull('slug')
            ->orderBy('id')
            ->get()
            ->each(function ($section, $index) {
                DB::table('services_page_sections')->where('id', $section->id)->update([
                    'slug' => 'service-'.$section->id,
                    'sort_order' => $index + 3,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('services_page_sections', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'intro',
                'content',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'meta_tags',
                'sort_order',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('content_blocks');
        });
    }
};
