<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class PruneDeploymentArtifacts extends Command
{
    protected $signature = 'deploy:prune
        {--path= : Override the auto-detected atomic deployment path}
        {--grace-hours=6 : Keep deployment artifacts modified within this many hours}';

    protected $description = 'Delete stale atomic releases and uploaded deployment artifacts';

    public function handle(Filesystem $files): int
    {
        $graceHours = filter_var(
            $this->option('grace-hours'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($graceHours === false) {
            $this->error('The grace period must be a positive integer.');

            return self::FAILURE;
        }

        $deployPath = $this->resolveDeployPath();

        if ($deployPath === null) {
            $this->info('No atomic deployment path was detected; nothing to prune.');

            return self::SUCCESS;
        }

        $releasesPath = $deployPath.'/releases';
        $currentLink = $deployPath.'/current';
        $currentRelease = realpath($currentLink);

        if (
            $deployPath === DIRECTORY_SEPARATOR ||
            ! $files->isDirectory($releasesPath) ||
            ! is_link($currentLink) ||
            $currentRelease === false ||
            ! str_starts_with($currentRelease, $releasesPath.DIRECTORY_SEPARATOR)
        ) {
            $this->error('The deployment path is not a valid atomic deployment.');

            return self::FAILURE;
        }

        $cutoff = now()->subHours($graceHours)->getTimestamp();
        $deletedReleases = $this->pruneReleases($files, $releasesPath, $currentRelease, $cutoff);
        $deletedArchives = $this->pruneIncomingArchives($files, $deployPath.'/incoming', $cutoff);
        $deletedLinks = $this->pruneDeploymentLinks($files, $deployPath, $cutoff);

        $this->info(sprintf(
            'Deployment maintenance complete: %d release(s), %d archive(s), %d link(s) deleted.',
            $deletedReleases,
            $deletedArchives,
            $deletedLinks
        ));

        return self::SUCCESS;
    }

    private function resolveDeployPath(): ?string
    {
        if ($this->option('path') !== null) {
            return realpath((string) $this->option('path')) ?: null;
        }

        $releasePath = realpath(base_path());

        if ($releasePath === false || basename(dirname($releasePath)) !== 'releases') {
            return null;
        }

        return dirname($releasePath, 2);
    }

    private function pruneReleases(
        Filesystem $files,
        string $releasesPath,
        string $currentRelease,
        int $cutoff
    ): int {
        $deleted = 0;

        foreach ($files->directories($releasesPath) as $releasePath) {
            if (
                realpath($releasePath) === $currentRelease ||
                is_link($releasePath) ||
                filemtime($releasePath) >= $cutoff
            ) {
                continue;
            }

            if ($files->deleteDirectory($releasePath)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function pruneIncomingArchives(Filesystem $files, string $incomingPath, int $cutoff): int
    {
        if (! $files->isDirectory($incomingPath)) {
            return 0;
        }

        $deleted = 0;

        foreach ($files->files($incomingPath) as $file) {
            if (! $this->isDeploymentArchive($file) || $file->getMTime() >= $cutoff) {
                continue;
            }

            if ($files->delete($file->getPathname())) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function pruneDeploymentLinks(Filesystem $files, string $deployPath, int $cutoff): int
    {
        $deleted = 0;

        foreach (new \FilesystemIterator($deployPath, \FilesystemIterator::SKIP_DOTS) as $file) {
            $linkMetadata = $file->isLink() ? lstat($file->getPathname()) : false;

            if (
                ! $file->isLink() ||
                ! preg_match('/^\.(?:current|rollback)-[A-Za-z0-9._-]+$/', $file->getFilename()) ||
                $linkMetadata === false ||
                $linkMetadata['mtime'] >= $cutoff
            ) {
                continue;
            }

            if ($files->delete($file->getPathname())) {
                $deleted++;
            }
        }

        $previousLink = $deployPath.'/previous';

        if (is_link($previousLink) && ! file_exists($previousLink) && $files->delete($previousLink)) {
            $deleted++;
        }

        return $deleted;
    }

    private function isDeploymentArchive(SplFileInfo $file): bool
    {
        return (bool) preg_match(
            '/^bona-[A-Za-z0-9._-]+\.tar\.gz(?:\.sha256)?$/',
            $file->getFilename()
        );
    }
}
