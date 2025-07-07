import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';


// document.addEventListener('DOMContentLoaded', () => {
// 	const tabs = document.querySelectorAll('.tab');
// 	const tabContents = document.querySelectorAll('.tab-content');
// 	const currentTab = (new URLSearchParams(window.location.search)).get('tab');
// 	// document.querySelector('.tab.family-tab').classList.add('active');
// 	// document.querySelector('.tab-content.family-tab').classList.add('active');
// 	// if (currentTab && currentTab === 'book') {
// 	// 	document.querySelector('.tab.family-tab').classList.remove('active');
// 	// 	document.querySelector('.tab-content.family-tab').classList.remove('active');
// 	// 	document.querySelector('.tab-content.book-tab').classList.add('active');
// 	// }	

// 	tabs.forEach(tab => {
// 		tab.addEventListener('click', (event)=> {
			
// 			// tabs.forEach(tab => tab.classList.remove('active'));
// 			// tabContents.forEach(content => content.classList.remove('active'));
// 			// event.target.classList.add('active');
// 			// if (event.target.classList.contains('book-tab')) {
// 			// 	document.querySelector('.tab-content.book-tab').classList.add('active');
// 			// } else if (event.target.classList.contains('family-tab')) {
// 			// 	document.querySelector('.tab-content.family-tab').classList.add('active');
// 			// }
// 		});
// 	});
// });
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

