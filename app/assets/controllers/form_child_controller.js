import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['template', 'childContainer', 'clonedInput']
    static values = {index: Number}

    add(e) {
        const childFragment = this.templateTarget.content.cloneNode(true);
        const div = document.createElement('div');
        div.appendChild(childFragment);
        const child = div.innerHTML.replace(/__name__/g, this.indexValue);

        this.indexValue = this.indexValue + 1;
        this.childContainerTarget.insertAdjacentHTML('beforeend', child);

        if(this.clonedInputTargets.length > 1) {
            if (e.params.compute === 'addDay') {
                const dateToCompute = new Date(this.clonedInputTargets[this.clonedInputTargets.length - 2].value);
                dateToCompute.setDate(dateToCompute.getDate() + 1);
                this.clonedInputTargets[this.clonedInputTargets.length - 1].value = dateToCompute.toISOString().split('T')[0];
            } else {
                this.clonedInputTargets[this.clonedInputTargets.length - 1].value = this.clonedInputTargets[this.clonedInputTargets.length - 2].value;
            }
        }
    }

    remove(e) {
        const container = e.currentTarget.closest(e.params.targetAttribute);
        container.remove();
    }
}
