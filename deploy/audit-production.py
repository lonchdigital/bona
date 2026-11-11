"""Compare immutable production source files to their exact Git revision.

Only hashes and relative filenames are returned. Shared uploads, environment,
runtime caches, installed dependencies and compiled assets are excluded.
"""
import hashlib
import io
import json
import os
import re
import shlex
import subprocess
import tarfile
from pathlib import Path


def release_manifest(revision):
    archive = subprocess.check_output(['git', 'archive', '--format=tar', revision])
    manifest = {}
    with tarfile.open(fileobj=io.BytesIO(archive)) as tar:
        for member in tar:
            path = member.name
            if (not member.isfile() or path in ['.env', 'REVISION']
                    or re.match(r'^(storage|vendor|public/storage|public/build|bootstrap/cache)(/|$)', path)):
                continue
            manifest[path] = hashlib.sha256(tar.extractfile(member).read()).hexdigest()
    return manifest


def main():
    target = os.environ['SSH_USER'] + '@' + os.environ['SSH_HOST']
    deploy_path = os.environ['DEPLOY_PATH'].rstrip('/')
    if not deploy_path.startswith('/') or deploy_path == '':
        raise ValueError('Invalid production path')
    current = deploy_path + '/current'
    php = os.environ.get('PHP_BIN') or '/usr/bin/php8.4'
    ssh = ['ssh', '-o', 'BatchMode=yes', '-o', 'StrictHostKeyChecking=yes', target]
    revision = subprocess.check_output(
        ssh + ['cat -- ' + shlex.quote(current + '/REVISION')], text=True
    ).strip()
    if not re.fullmatch(r'[a-f0-9]{40}', revision):
        raise ValueError('Invalid active revision')
    subprocess.run(['git', 'merge-base', '--is-ancestor', revision, 'HEAD'], check=True)
    manifest = release_manifest(revision)
    code = Path('deploy/verify-release-files.php').read_text().removeprefix('<?php')
    print(f'Checking {len(manifest)} source files at revision {revision}', flush=True)
    command = shlex.join([php, '-d', 'display_errors=stderr', '-d', 'log_errors=0', '-r', code, current, revision])
    result = subprocess.run(ssh + [command], input=json.dumps(manifest), text=True, capture_output=True)
    print(result.stdout, end='')
    if result.stderr:
        print(result.stderr, end='')
    if result.returncode:
        raise SystemExit(result.returncode)
    print('Expected repository changes since the active release:')
    subprocess.run(['git', 'diff', '--stat', revision, 'HEAD'], check=True)


if __name__ == '__main__':
    main()
