import './menu.js';
import './theme.js';
import './scroll.js';
import './contact.js';

import { loadPortfolio } from './portfolio.js';
import { loadExperiences } from './experience.js';

async function init() {
    // Wait for all content to load from API
    await Promise.allSettled([
        loadPortfolio(),
        loadExperiences()
    ]);

    // Initialize WOW.js after dynamic content is in the DOM
    if (typeof WOW !== 'undefined') {
        new WOW().init();
    }
}

document.addEventListener('DOMContentLoaded', init);