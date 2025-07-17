

import './bootstrap.js';
import './styles/main.css';

/*
 * Welcome to your app's main JavaScript file!
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

document.addEventListener('DOMContentLoaded', () => {  // important pour que le style css charge correctement
    const locations = document.querySelectorAll('.location');

    locations.forEach(location => {
        const text = location.textContent.trim(); // trim enlève les espaces
        console.log(text);

        if (text === 'Caméléon') {
            location.classList.add('cameleon');
            
        } else if (text === 'F') {
            location.classList.add('f');
            
        } else if (text === 'Badet') {
            location.classList.add('badet');
            
        } else if (text === 'MBA') {
            location.classList.add('mba');
            
        }
    });
});

