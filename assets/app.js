import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/main.css';

const locations = document.querySelectorAll('.location');
locations.forEach(location => {
    if (location.textContent ==='Caméléon'){
    location.classList.add('cameleon')
} if (location.textContent==='F'){
    location.classList.add('f')
}if (location.textContent==='Badet'){
    location.classList.add('badet')
}if (location.textContent==='MBA'){
    location.classList.add('mba')
}
});

