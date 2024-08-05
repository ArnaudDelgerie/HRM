
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['template', 'childContainer']
    static values = {index: Number}

    add() {
        const childFragment = this.templateTarget.content.cloneNode(true);
        const div = document.createElement('div');
        div.appendChild(childFragment);
        const child = div.innerHTML.replace(/__name__/g, this.indexValue);

        this.indexValue = this.indexValue + 1;
        this.childContainerTarget.insertAdjacentHTML('beforeend', child);
    }

    remove(e) {
        const container = e.currentTarget.closest(e.params.targetAttribute);
        container.remove();
    }
}
