import './bootstrap';

import Alpine from 'alpinejs';
import './modules/clean-form-url';
import registerLandingSignalBoard from './modules/landing-signal-board';

const copyTextValueFromElement = (element) => {
    const value = element?.getAttribute('data-copy-value') ?? '';

    if (!value) {
        return;
    }

    navigator.clipboard?.writeText(value).then(() => {
        const originalTitle = element.getAttribute('title') ?? value;
        element.setAttribute('title', 'Copied');

        window.setTimeout(() => {
            element.setAttribute('title', originalTitle);
        }, 1200);
    }).catch(() => {
        const textArea = document.createElement('textarea');
        textArea.value = value;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    });
};

window.copyTextValue = copyTextValueFromElement;
window.copySessionField = copyTextValueFromElement;
window.copyDeviceValue = copyTextValueFromElement;

window.Alpine = Alpine;

registerLandingSignalBoard(Alpine);

Alpine.start();
