{{-- ============================================================
     BALISES PWA — à inclure dans le <head> de chaque page.
     Un seul endroit à modifier si on change quelque chose plus tard.
     ============================================================ --}}

{{-- La "carte d'identité" de l'application (nom, icônes, couleurs) --}}
<link rel="manifest" href="/manifest.json">

{{-- Couleur de la barre d'état du téléphone : le bleu du logo --}}
<meta name="theme-color" content="#0259a0">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Petite icône dans l'onglet du navigateur --}}
<link rel="icon" type="image/png" href="/icons/icon-192.png">

{{-- Réglages spécifiques iPhone / iPad (Safari ignore une partie du manifest) --}}
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="MITSINJO">

{{-- Active le Service Worker (page "hors ligne" quand le réseau est coupé) --}}
<script>
    if ('serviceWorker' in navigator) {
        // On attend que la page soit entièrement chargée pour ne pas la ralentir
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/service-worker.js')
                .catch(function (error) {
                    console.warn('Service Worker non enregistré :', error);
                });
        });
    }

    function vapidKeyToBytes(value) {
        const padding = '='.repeat((4 - value.length % 4) % 4);
        const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = window.atob(base64);
        return Uint8Array.from(raw, character => character.charCodeAt(0));
    }

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-enable-push]');
        if (!button) return;

        const status = document.querySelector('[data-push-status]');
        button.disabled = true;

        try {
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                throw new Error('Les notifications push ne sont pas prises en charge par ce navigateur.');
            }

            const permission = Notification.permission === 'granted'
                ? 'granted'
                : await Notification.requestPermission();

            if (permission !== 'granted') {
                throw new Error('Autorisez les notifications dans les réglages du navigateur.');
            }

            const registration = await navigator.serviceWorker.ready;
            let subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: vapidKeyToBytes(button.dataset.vapidPublicKey),
                });
            }

            const response = await fetch(button.dataset.subscriptionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(subscription.toJSON()),
            });

            if (!response.ok) throw new Error('Impossible d’enregistrer cet appareil. Réessayez.');

            status.textContent = 'Notifications activées sur cet appareil.';
            button.textContent = 'Notifications activées';
        } catch (error) {
            status.textContent = error.message || 'Une erreur a empêché l’activation des notifications.';
            button.disabled = false;
        }
    });
</script>
