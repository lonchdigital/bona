import hashlib
import json
import subprocess
import tempfile
import unittest
from pathlib import Path

CHECKER = Path(__file__).resolve().parents[2] / 'deploy/verify-release-files.php'
REVISION = 'a' * 40


class ReleaseIntegrityTest(unittest.TestCase):
    def test_detects_drift_and_ignores_only_runtime_files(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            (root / 'REVISION').write_text(REVISION)
            (root / 'app').mkdir()
            source = root / 'app/test.php'
            source.write_text('original')
            (root / '.env').write_text('private placeholder must never appear in output')
            (root / 'bootstrap/cache').mkdir(parents=True)
            (root / 'bootstrap/cache/config.php').write_text('runtime placeholder')
            manifest = {'app/test.php': hashlib.sha256(b'original').hexdigest()}

            def check():
                result = subprocess.run(['php', str(CHECKER), tmp, REVISION],
                                        input=json.dumps(manifest), text=True, capture_output=True)
                self.assertNotIn('private placeholder', result.stdout + result.stderr)
                return result.returncode, json.loads(result.stdout)

            self.assertEqual(check()[0], 0)
            source.write_text('server hotfix')
            code, report = check()
            self.assertEqual(code, 1)
            self.assertIn({'kind': 'modified', 'path': 'app/test.php'}, report['issues'])
            source.unlink()
            self.assertIn({'kind': 'missing-or-outside-release', 'path': 'app/test.php'}, check()[1]['issues'])
            source.symlink_to(root / '.env')
            self.assertIn({'kind': 'unexpected-symlink', 'path': 'app/test.php'}, check()[1]['issues'])
            (root / 'app/manual.php').write_text('manual patch')
            self.assertIn({'kind': 'extra-file', 'path': 'app/manual.php'}, check()[1]['issues'])
            (root / 'REVISION').write_text('b' * 40)
            self.assertIn({'kind': 'revision-changed', 'path': 'REVISION'}, check()[1]['issues'])


if __name__ == '__main__':
    unittest.main()
