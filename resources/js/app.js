import Alpine from 'alpinejs';
import htmx from 'htmx.org';

window.Alpine = Alpine;
window.htmx = htmx;

console.log('HTMX loaded:', typeof htmx);

Alpine.start();
htmx.process(document.body);
