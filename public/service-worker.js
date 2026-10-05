// ============================================================
// SERVICE WORKER — MITSINJO
//
// Rôle : afficher une page claire quand le réseau est coupé.
// Règle de sécurité : on ne met JAMAIS en cache les pages de
// l'application (soldes, échéances, paiements) : une donnée
// financière périmée serait trompeuse pour le client.
// ============================================================

// Si vous modifiez ce fichier plus tard, changez ce numéro (v1 → v2) :
// le navigateur supprimera alors l'ancien cache automatiquement.
const CACHE_NAME = 'mitsinjo-v2';

// Les seuls fichiers gardés en cache : la page "hors ligne" et les icônes.
const FILES_TO_CACHE = [
    '/offline.html',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// ------------------------------------------------------------
// 1. INSTALLATION : on télécharge et range les fichiers de secours
// ------------------------------------------------------------
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(FILES_TO_CACHE))
    );
    // Active la nouvelle version tout de suite, sans attendre la fermeture des onglets
    self.skipWaiting();
});

// ------------------------------------------------------------
// 2. ACTIVATION : on supprime les anciens caches devenus inutiles
// ------------------------------------------------------------
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((names) =>
            Promise.all(
                names
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            )
        )
    );
    self.clients.claim();
});

// ------------------------------------------------------------
// 3. INTERCEPTION DES REQUÊTES
// ------------------------------------------------------------
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // On ne touche qu'aux lectures (GET). Les envois de formulaires,
    // les paiements et les actions Livewire (POST) passent directement
    // au serveur, sans aucune interférence.
    if (request.method !== 'GET') {
        return;
    }

    // Cas A : l'utilisateur ouvre une PAGE (navigation).
    // On essaie TOUJOURS le réseau d'abord (données à jour).
    // Si le réseau est coupé, on affiche la page "hors ligne".
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Cas B : les icônes. Elles ne changent presque jamais,
    // on les sert depuis le cache pour aller plus vite.
    const url = new URL(request.url);
    if (url.origin === self.location.origin && url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request))
        );
    }

    // Tout le reste (CSS, JS, données) : on ne fait rien,
    // le navigateur fonctionne normalement.
});

self.addEventListener('push', (event) => {
    let payload = {
        title: 'MITSINJO',
        body: 'Vous avez reçu une nouvelle information.',
        url: '/client/dashboard',
    };

    if (event.data) {
        try {
            payload = { ...payload, ...event.data.json() };
        } catch {
            payload.body = event.data.text();
        }
    }

    event.waitUntil(self.registration.showNotification(payload.title, {
        body: payload.body,
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        tag: payload.tag || 'mitsinjo-announcement',
        data: { url: payload.url || '/client/dashboard' },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const target = new URL(event.notification.data?.url || '/client/dashboard', self.location.origin);
    if (target.origin !== self.location.origin) return;

    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
        for (const client of clients) {
            if ('focus' in client) {
                if (client.url !== target.href && 'navigate' in client) client.navigate(target.href);
                return client.focus();
            }
        }

        return self.clients.openWindow(target.href);
    }));
});
