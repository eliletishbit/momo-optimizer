import './bootstrap';

// Fonction universelle de copie avec feedback visuel ("Copié !")
window.copyToClipboard = function (text, btnElement) {
    if (!text) return;
    const originalHtml = btnElement ? btnElement.innerHTML : null;

    const showSuccess = () => {
        if (btnElement) {
            btnElement.innerHTML = `
                <svg class="w-4 h-4 text-emerald-600 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-emerald-700 font-bold">Copié !</span>
            `;
            btnElement.classList.add('bg-emerald-50', 'border-emerald-300');
            setTimeout(() => {
                if (originalHtml) btnElement.innerHTML = originalHtml;
                btnElement.classList.remove('bg-emerald-50', 'border-emerald-300');
            }, 2500);
        }
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text)
            .then(showSuccess)
            .catch(() => fallbackCopy(text, showSuccess));
    } else {
        fallbackCopy(text, showSuccess);
    }
};

function fallbackCopy(text, callback) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        if (callback) callback();
    } catch (err) {
        console.error('Erreur fallback copie:', err);
    }
    textArea.remove();
}

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

