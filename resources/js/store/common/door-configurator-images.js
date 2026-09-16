// One request/decode per asset, a bounded decoded cache, and at most two background requests.
export function createSceneImageLoader({ base, ImageClass = Image, timeoutMs = 20000, maxEntries = 20, schedule = callback => setTimeout(callback, 120) }) {
    const cache = new Map();
    let queue = [], active = 0, scheduled = false;

    function trim() {
        for (const [file, entry] of cache) {
            if (cache.size <= maxEntries) break;
            if (entry.ready) cache.delete(file);
        }
    }

    function load(file, priority = 'high') {
        if (cache.has(file)) {
            const entry = cache.get(file);
            if (priority === 'high') entry.image.fetchPriority = 'high';
            cache.delete(file); cache.set(file, entry);
            return entry.promise;
        }
        const image = new ImageClass();
        image.decoding = 'async'; image.fetchPriority = priority;
        const entry = { image, ready: false };
        entry.promise = new Promise((resolve, reject) => {
            let settled = false;
            const finish = error => {
                if (settled) return;
                settled = true; clearTimeout(timer);
                image.onload = image.onerror = null;
                if (error) {
                    cache.delete(file);
                    image.src = ''; // Cancel a timed-out transfer; a later selection can retry.
                    reject(error);
                } else {
                    entry.ready = true; trim(); resolve(image);
                }
            };
            const timer = setTimeout(() => finish(new Error('Image load timed out')), timeoutMs);
            image.onload = async () => {
                try {
                    if (typeof image.decode === 'function') await image.decode();
                    finish();
                } catch (error) { finish(error); }
            };
            image.onerror = () => finish(new Error('Image unavailable'));
        });
        cache.set(file, entry);
        image.src = base + file;
        return entry.promise;
    }

    function defer() {
        if (scheduled || !queue.length) return;
        scheduled = true;
        schedule(() => {
            scheduled = false;
            while (active < 2 && queue.length) {
                const file = queue.shift();
                if (cache.has(file)) continue;
                active++;
                load(file, 'low').catch(() => {}).finally(() => { active--; defer(); });
            }
        });
    }

    function preload(files) {
        // Replace stale queued work after a type change. Current selections always load directly.
        queue = [...new Set(files)].filter(file => !cache.has(file)).slice(0, 10);
        defer();
    }

    return { load, preload };
}
