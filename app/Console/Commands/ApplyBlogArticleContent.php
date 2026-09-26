<?php

namespace App\Console\Commands;

use App\Services\BlogArticle\BlogArticleContentImporter;
use Illuminate\Console\Command;

class ApplyBlogArticleContent extends Command
{
    protected $signature = 'blog:apply-content {file : JSON file under database/content/blog}';

    protected $description = 'Fill missing localized blog article copy, FAQ and meta from prepared content';

    public function handle(BlogArticleContentImporter $importer): int
    {
        $path = database_path('content/blog/'.basename((string) $this->argument('file')));
        if (! is_file($path)) {
            $this->error("Content file not found: {$path}");

            return self::FAILURE;
        }

        $result = $importer->importFile($path);
        $this->info("Updated blog articles: {$result['articles']}.");
        if ($result['missing'] !== []) {
            $this->warn('Slugs not found: '.implode(', ', $result['missing']));
        }

        return self::SUCCESS;
    }
}
