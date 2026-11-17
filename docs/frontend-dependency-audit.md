# Frontend dependency audit

Reviewed 2026-11-17. Re-review expires **2026-12-17 UTC**.

GitHub updated [GHSA-fc5q-39xc-vjpf](https://github.com/advisories/GHSA-fc5q-39xc-vjpf) on November 17. All released picomatch versions through 4.0.7 are affected; no patched release is available. The reported attack requires an attacker-controlled repeated-extglob pattern. `npm audit fix --force` proposes a Vite downgrade that does not fix picomatch; it must not be used as remediation.

In this repository every installed picomatch instance (2.3.2 or 4.0.7) is development-only, used by Vite, reload/glob tools, and the optional Sass filesystem watcher. The build reads repository-owned paths and configuration, not storefront requests or uploaded customer files. Production uses Laravel/PHP and compiled assets; `deploy/package-release.sh` packages a Git archive plus PHP vendor files and `public/build`. It does not copy `node_modules` or start a Vite/Node server. This is an exposure assessment, **not a claim that the upstream flaw is fixed**.

`tools/audit-frontend.mjs` runs the full npm audit and temporarily accepts only this exact advisory and its inherited dependency findings, only if every affected lockfile path is marked `dev: true` and the picomatch version is one reviewed above. Other high/critical findings, runtime occurrences, new versions, malformed reports and audit failures still fail CI. The exception is printed in logs and expires automatically; it does not suppress alerts or change the lockfile.

When a patched release becomes available, update the dependency tree and remove this exception. Reassess immediately if Node is introduced into production, frontend build inputs become customer-controlled, or the dependency graph changes. Tests cover runtime promotion, unrelated advisories, expiry and fail-closed handling.
