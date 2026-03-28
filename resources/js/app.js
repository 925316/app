import './bootstrap';

import Alpine from 'alpinejs';
import './modules/clean-form-url';
import registerLandingSignalBoard from './modules/landing-signal-board';

window.Alpine = Alpine;

registerLandingSignalBoard(Alpine);

Alpine.start();
