const CACHE_VERSION = 'build-50f150d24267cb17';
const CACHE_NAME = `pterodactyl-assets-${CACHE_VERSION}`;
const OFFLINE_CACHE_NAME = `pterodactyl-offline-${CACHE_VERSION}`;

let pwaConfig = {
    enabled: false,
    offline_enabled: false,
    offline_page_url: null,
    cache_strategy: 'network-first',
    cache_assets: false,
    cache_api_requests: false,
    precache_assets: false
};

const ASSET_PATTERNS = [
    /\/assets\/.*\.[a-z0-9]{8,}\.(js|css)$/i,
    /\/logo\/.*\.(png|jpg|jpeg|svg|webp|gif)$/i,
    /\/favicons\/.*\.(png|ico|svg)$/i,
    /\/themes\/.*\.(png|jpg|jpeg|svg|webp|gif)$/i,
];

const API_PATTERNS = [
    /\/api\/client/i,
];

const unavailableResponse = () => new Response('', {
    status: 503,
    statusText: 'Service Unavailable',
    headers: { 'Content-Type': 'text/plain; charset=utf-8' },
});

const AUTH_NAVIGATION_PATTERN = /^\/auth(\/|$)/i;
const ADMIN_NAVIGATION_PATTERN = /^\/admin(\/|$)/i;

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'PWA_CONFIG') {
        const config = event.data.config || {};

        pwaConfig = {
            ...pwaConfig,
            ...config,
            cache_strategy: config.cache_strategy ?? config.cacheStrategy ?? pwaConfig.cache_strategy,
            cache_assets: config.cache_assets ?? config.cacheAssets ?? pwaConfig.cache_assets,
            cache_api_requests: config.cache_api_requests ?? config.cacheApi ?? pwaConfig.cache_api_requests,
            offline_enabled: config.offline_enabled ?? config.offlineEnabled ?? pwaConfig.offline_enabled,
            offline_page_url: config.offline_page_url ?? config.offlinePageUrl ?? pwaConfig.offline_page_url,
        };
    }
});

self.addEventListener('install', (event) => {
    self.skipWaiting();

    if (pwaConfig.enabled && pwaConfig.offline_enabled && pwaConfig.offline_page_url) {
        event.waitUntil(
            caches.open(OFFLINE_CACHE_NAME).then((cache) => {
                return cache.add(pwaConfig.offline_page_url).catch(() => undefined);
            })
        );
    }
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => {
                        return name.startsWith('pterodactyl-assets-') ||
                            (name.startsWith('pterodactyl-offline-') && name !== OFFLINE_CACHE_NAME);
                    })
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

const cacheSuccessfulResponse = (request, response) => {
    if (!pwaConfig.enabled) {
        return;
    }

    if (!response || response.status !== 200) {
        return;
    }

    if (request.mode === 'navigate' && response.redirected) {
        return;
    }

    if (request.mode === 'navigate') {
        const pathname = new URL(request.url).pathname;
        if (ADMIN_NAVIGATION_PATTERN.test(pathname)) {
            return;
        }
    }

    const cacheControl = response.headers.get('Cache-Control') || '';
    if (/\bno-store\b|\bprivate\b/i.test(cacheControl)) {
        return;
    }

    const responseToCache = response.clone();
    caches.open(CACHE_NAME).then((cache) => {
        cache.put(request, responseToCache);
    });
};

const handleNavigationRequest = async (request, strategy) => {
    if (strategy === 'cache-first') {
        const cachedNavigation = await caches.match(request);
        if (cachedNavigation) {
            fetch(request)
                .then((networkResponse) => {
                    cacheSuccessfulResponse(request, networkResponse);
                })
                .catch(() => undefined);

            return cachedNavigation;
        }
    }

    try {
        const networkResponse = await fetch(request);
        cacheSuccessfulResponse(request, networkResponse);
        return networkResponse;
    } catch (_error) {
        const cachedNavigation = await caches.match(request);
        if (cachedNavigation) {
            return cachedNavigation;
        }

        if (pwaConfig.offline_enabled && pwaConfig.offline_page_url) {
            return caches.match(pwaConfig.offline_page_url);
        }

        throw new Error('Navigation request failed and no cache is available.');
    }
};

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET') {
        return;
    }

    const strategy = pwaConfig.cache_strategy || 'network-first';

    if (event.request.mode === 'navigate') {
        if (AUTH_NAVIGATION_PATTERN.test(url.pathname)) {
            return;
        }

        if (!pwaConfig.enabled || !pwaConfig.offline_enabled) {
            return;
        }

        const navigationStrategy = 'network-first';

        event.respondWith(handleNavigationRequest(event.request, navigationStrategy));
        return;
    }

    const shouldCache = ASSET_PATTERNS.some(pattern => pattern.test(url.pathname));

    const isApiRequest = API_PATTERNS.some(pattern => pattern.test(url.pathname));

    if (shouldCache && (!pwaConfig.enabled || !pwaConfig.cache_assets)) {
        return;
    }

    if (isApiRequest && !pwaConfig.cache_api_requests) {
        return;
    }

    if (!shouldCache && !isApiRequest) {
        return;
    }

    if (shouldCache || strategy === 'network-first' || isApiRequest) {
        event.respondWith(
            fetch(event.request).then((networkResponse) => {
                cacheSuccessfulResponse(event.request, networkResponse);
                return networkResponse;
            }).catch(() => {
                return caches.match(event.request).then((cachedResponse) => {
                    return cachedResponse || unavailableResponse();
                });
            })
        );
    } else {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }

                return fetch(event.request).then((networkResponse) => {
                    cacheSuccessfulResponse(event.request, networkResponse);
                    return networkResponse;
                }).catch(() => unavailableResponse());
            })
        );
    }
});
