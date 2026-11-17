import assert from 'node:assert/strict';
import test from 'node:test';
import { assessAudit } from '../../tools/audit-frontend.mjs';

const now = Date.parse('2026-11-17T00:00:00Z');
const url = 'https://github.com/advisories/GHSA-fc5q-39xc-vjpf';
function fixture() {
    return {
        report: { auditReportVersion: 2, metadata: { vulnerabilities: { high: 2, critical: 0 } }, vulnerabilities: {
            picomatch: { severity: 'high', nodes: ['node_modules/picomatch'], via: [{ name: 'picomatch', url, severity: 'high' }] },
            vite: { severity: 'high', nodes: ['node_modules/vite'], via: ['picomatch'] },
        } },
        lock: { packages: {
            'node_modules/picomatch': { version: '4.0.7', dev: true },
            'node_modules/vite': { version: '8.3.7', dev: true },
        } },
    };
}
test('reviewed advisory accepts only development dependencies and its inherited findings', () => {
    const { report, lock } = fixture();
    assert.deepEqual(assessAudit(report, lock, now), { blocked: [], accepted: ['picomatch', 'vite'] });
});
test('runtime or unknown lock entries fail even for the reviewed advisory', () => {
    for (const value of [false, undefined]) {
        const { report, lock } = fixture();
        lock.packages['node_modules/picomatch'].dev = value;
        assert.deepEqual(assessAudit(report, lock, now).blocked, ['picomatch', 'vite']);
    }
    const { report, lock } = fixture();
    delete lock.packages['node_modules/picomatch'];
    assert.equal(assessAudit(report, lock, now).blocked.length, 2);
});
test('new advisories, new versions and critical severity remain blocking', () => {
    for (const change of [
        ({ report }) => report.vulnerabilities.picomatch.via.push({ name: 'picomatch', url: 'https://github.com/advisories/GHSA-new', severity: 'high' }),
        ({ lock }) => lock.packages['node_modules/picomatch'].version = '4.0.8',
        ({ report }) => { report.vulnerabilities.vite.severity = 'critical'; report.metadata.vulnerabilities = { high: 1, critical: 1 }; },
    ]) {
        const f = fixture(); change(f);
        assert.ok(assessAudit(f.report, f.lock, now).blocked.length);
    }
});
test('exception expires and dependency cycles fail closed', () => {
    const { report, lock } = fixture();
    assert.equal(assessAudit(report, lock, Date.parse('2026-12-17T00:00:00Z')).blocked.length, 2);
    report.vulnerabilities.picomatch.via = ['vite'];
    assert.equal(assessAudit(report, lock, now).blocked.length, 2);
});
test('malformed, failed or inconsistent audit responses cannot pass', () => {
    const { report, lock } = fixture();
    for (const bad of [{}, { ...report, error: { code: 'ENETWORK' } }, { ...report, auditReportVersion: 1 }, { ...report, vulnerabilities: {} }]) {
        assert.throws(() => assessAudit(bad, lock, now));
    }
});
