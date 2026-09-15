import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const installAppBtn = document.getElementById('install-app-btn');
let deferredPrompt = null;

if (installAppBtn) {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        installAppBtn.classList.remove('hidden');
    });

    installAppBtn.addEventListener('click', async () => {
        if (!deferredPrompt) {
            return;
        }

        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        installAppBtn.classList.add('hidden');
    });

    window.addEventListener('appinstalled', () => {
        installAppBtn.classList.add('hidden');
    });
}

