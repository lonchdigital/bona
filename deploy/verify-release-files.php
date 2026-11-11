<?php

// Standalone, read-only checker. Never bootstrap the app or inspect shared data.
$root = realpath($argv[1] ?? '');
$expectedRevision = $argv[2] ?? '';
$manifest = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
if (! $root || ! preg_match('/^[a-f0-9]{40}$/', $expectedRevision) || ! is_array($manifest)) {
    throw new RuntimeException('Invalid release verification input.');
}
$ignored = static fn (string $path): bool => in_array($path, ['.env', 'REVISION', 'public/sitemap.xml'], true)
    || preg_match('#^(storage|vendor|public/storage|public/build|bootstrap/cache)(/|$)#', $path);
$issues = [];
$checked = 0;
if (trim((string) file_get_contents($root.'/REVISION')) !== $expectedRevision) {
    $issues[] = ['kind' => 'revision-changed', 'path' => 'REVISION'];
}
foreach ($manifest as $path => $hash) {
    if (! is_string($path) || preg_match('#(^/|(^|/)\.\.(/|$)|\x00)#', $path)
        || ! is_string($hash) || ! preg_match('/^[a-f0-9]{64}$/', $hash) || $ignored($path)) {
        throw new RuntimeException('Unsafe manifest entry.');
    }
    $actual = realpath($root.'/'.$path);
    if (is_link($root.'/'.$path) || ! $actual || ! str_starts_with($actual, $root.'/') || ! is_file($actual)) {
        $issues[] = ['kind' => 'missing-or-outside-release', 'path' => $path];
    } elseif (hash_file('sha256', $actual) !== $hash) {
        $issues[] = ['kind' => 'modified', 'path' => $path];
    }
    $checked++;
}
$walk = function (string $directory, string $prefix = '') use (&$walk, $ignored, $manifest, &$issues): void {
    foreach (new DirectoryIterator($directory) as $entry) {
        if ($entry->isDot()) {
            continue;
        }
        $path = $prefix.$entry->getFilename();
        if ($ignored($path)) {
            continue;
        }
        if ($entry->isLink()) {
            $issues[] = ['kind' => 'unexpected-symlink', 'path' => $path];
        } elseif ($entry->isDir()) {
            $walk($entry->getPathname(), $path.'/');
        } elseif ($entry->isFile() && ! array_key_exists($path, $manifest)) {
            $issues[] = ['kind' => 'extra-file', 'path' => $path];
        }
    }
};
$walk($root);
echo json_encode(['revision' => $expectedRevision, 'checkedFiles' => $checked, 'issues' => $issues], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($issues === [] ? 0 : 1);
