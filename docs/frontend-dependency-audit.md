# Frontend dependency audit

Updated 2026-11-21. CI runs the standard **`npm audit --audit-level=high`**, with no advisory exceptions.

On November 17, [GHSA-fc5q-39xc-vjpf](https://github.com/advisories/GHSA-fc5q-39xc-vjpf) temporarily blocked the release. A narrowly scoped, expiring exception was added for the reviewed development-only picomatch dependency tree, which is not shipped in the production runtime. On November 18, the advisory was withdrawn because it was not considered a security issue.

The November 21 audit reports **zero vulnerabilities**. The temporary exception, custom runner and its tests have been removed, and both CI workflows again use npm's standard high/critical gate. No dependency versions were downgraded or changed for this advisory.
