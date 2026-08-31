<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PruneDeploymentArtifactsTest extends TestCase
{
    public function test_it_removes_only_stale_inactive_deployment_artifacts(): void
    {
        $deployPath = sys_get_temp_dir().'/bona-deploy-prune-'.Str::uuid();
        $releasesPath = $deployPath.'/releases';
        $incomingPath = $deployPath.'/incoming';
        $currentRelease = $releasesPath.'/current-release';
        $staleRelease = $releasesPath.'/stale-release';
        $recentRelease = $releasesPath.'/recent-release';

        File::ensureDirectoryExists($currentRelease);
        File::ensureDirectoryExists($staleRelease);
        File::ensureDirectoryExists($recentRelease);
        File::ensureDirectoryExists($incomingPath);
        symlink($currentRelease, $deployPath.'/current');

        $staleArchive = $incomingPath.'/bona-stale.tar.gz';
        $recentArchive = $incomingPath.'/bona-recent.tar.gz';
        $deployScript = $incomingPath.'/release.sh';

        File::put($staleArchive, 'stale');
        File::put($recentArchive, 'recent');
        File::put($deployScript, 'keep');
        touch($staleRelease, now()->subHours(7)->getTimestamp());
        touch($staleArchive, now()->subHours(7)->getTimestamp());

        try {
            $this->artisan('deploy:prune', ['--path' => $deployPath])
                ->expectsOutputToContain('1 release(s), 1 archive(s), 0 link(s) deleted')
                ->assertSuccessful();

            $this->assertDirectoryExists($currentRelease);
            $this->assertDirectoryDoesNotExist($staleRelease);
            $this->assertDirectoryExists($recentRelease);
            $this->assertFileDoesNotExist($staleArchive);
            $this->assertFileExists($recentArchive);
            $this->assertFileExists($deployScript);
        } finally {
            File::deleteDirectory($deployPath);
        }
    }

    public function test_it_refuses_a_directory_without_a_valid_current_release_link(): void
    {
        $deployPath = sys_get_temp_dir().'/bona-invalid-deploy-'.Str::uuid();
        File::ensureDirectoryExists($deployPath.'/releases');

        try {
            $this->artisan('deploy:prune', ['--path' => $deployPath])
                ->expectsOutput('The deployment path is not a valid atomic deployment.')
                ->assertFailed();
        } finally {
            File::deleteDirectory($deployPath);
        }
    }
}
