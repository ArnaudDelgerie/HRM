import { Popover } from 'bootstrap';
import './bootstrap.js';
import './styles/app.scss';
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';
import '@toast-ui/calendar/dist/toastui-calendar.min.css';

bsCustomFileInput.init();
document.addEventListener('turbo:load', function() {
    let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    popoverTriggerList.map(function(popoverTriggerEl) {
        if (popoverTriggerEl.getAttribute('popover-binded') !== "true") {
            popoverTriggerEl.setAttribute('popover-binded', "true");
            return new Popover(popoverTriggerEl)
        }
    });
});

document.addEventListener('turbo:submit-end', function() {
    setTimeout(() => {
        let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        popoverTriggerList.map(function(popoverTriggerEl) {
            if (popoverTriggerEl.getAttribute('popover-binded') !== "true") {
                popoverTriggerEl.setAttribute('popover-binded', "true");
                return new Popover(popoverTriggerEl)
            }
        });
    }, 100)
});

export function getSassVariable(name) {
    return getComputedStyle(document.documentElement).getPropertyValue(`--${name}`).trim();
}
