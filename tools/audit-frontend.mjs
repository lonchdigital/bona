import { spawnSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';

const advisory = 'https://github.com/advisories/GHSA-fc5q-39xc-vjpf';
const expires = Date.parse('2026-12-17T00:00:00Z');
const reviewedVersions = new Set(['2.3.2', '4.0.7']);

// See docs/frontend-dependency-audit.md. Only a specific, unpatched build-tool
// advisory is eligible; runtime dependencies and other advisories still fail.
export function assessAudit(report, lock, now = Date.now()) {
    if (report?.auditReportVersion !== 2 || report.error || !report.vulnerabilities
        || !report.metadata?.vulnerabilities || !lock?.packages) {
        throw new Error('Invalid npm audit response or package lock');
    }

    const vulnerabilities = report.vulnerabilities;
    function reviewed(name, chain = new Set()) {
        const entry = vulnerabilities[name];
        if (!entry || chain.has(name) || !entry.nodes?.length || !entry.via?.length || now >= expires) return false;
        if (!entry.nodes.every(path => lock.packages[path]?.dev === true)) return false;
        const next = new Set([...chain, name]);
        return entry.via.every(cause => {
            if (typeof cause === 'string') return reviewed(cause, next);
            return name === 'picomatch' && cause.name === 'picomatch'
                && cause.url === advisory && cause.severity === 'high'
                && entry.nodes.every(path => reviewedVersions.has(lock.packages[path].version));
        });
    }

    const blocked = [], accepted = [];
    for (const [name, entry] of Object.entries(vulnerabilities)) {
        if (!['info', 'low', 'moderate', 'high', 'critical'].includes(entry.severity)) {
            throw new Error(`Unrecognized severity for ${name}`);
        }
        if (!['high', 'critical'].includes(entry.severity)) continue;
        (entry.severity === 'high' && reviewed(name) ? accepted : blocked).push(name);
    }
    const expected = (report.metadata.vulnerabilities.high ?? 0) + (report.metadata.vulnerabilities.critical ?? 0);
    if (blocked.length + accepted.length !== expected) throw new Error('Inconsistent npm audit totals');
    return { blocked, accepted };
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
    try {
        const result = spawnSync('npm', ['audit', '--json'], { encoding: 'utf8', timeout: 120000, maxBuffer: 8 * 1024 * 1024 });
        if (result.error || ![0, 1].includes(result.status)) throw new Error('npm audit did not complete');
        const report = JSON.parse(result.stdout);
        const { blocked, accepted } = assessAudit(report, JSON.parse(readFileSync('package-lock.json', 'utf8')));
        if (accepted.length) {
            console.warn(`Reviewed build-only advisory ${advisory}; exception expires 2026-12-17. Packages: ${accepted.join(', ')}`);
        }
        if (blocked.length) throw new Error(`High/critical dependency vulnerabilities: ${blocked.join(', ')}`);
        console.log('Frontend audit passed: no unreviewed high/critical vulnerabilities.');
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
}
