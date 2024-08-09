import { Controller } from '@hotwired/stimulus';
import Quill from 'quill'
import html2pdf from 'html2pdf.js';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {'saveUrl': String}
    static targets = ['editor', 'save', 'edit', 'download'];

    initialize() {
        this.editor = null;
        this.toolbar = null;
    }

    editorTargetConnected(target) {
        const toolbarOptions = [
            [{ 'header': [1, 2, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'color': [] }],
            [{ 'align': [] }],
            ['link'/* , 'image' */],
            ['code-block'],
        ];
        const options = {
            readOnly: true,
            modules: {
                toolbar: toolbarOptions,
            },
            placeholder: '...',
            theme: 'snow'
        };
        this.editor = new Quill(target, options);
        this.editorTarget.style.border = 'none';
        this.toolbar = this.element.querySelector(`[role="toolbar"]`);
        this.element.classList.remove('d-none');
        this.toolbar.classList.add('d-none');
    }

    save() {
        const data = new FormData();
        data.append('summary', this.editor.getSemanticHTML());
        fetch(this['saveUrlValue'], {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
            body: data,
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error);
                }
            })
        this.editor.disable();
        this.toolbar.classList.add('d-none');
        this.saveTarget.classList.add('d-none');
        this.editTarget.classList.remove('d-none');
        this.downloadTarget.classList.remove('d-none');
    }

    edit() {
        this.editor.enable();
        this.toolbar.classList.remove('d-none');
        this.editTarget.classList.add('d-none');
        this.downloadTarget.classList.add('d-none');
        this.saveTarget.classList.remove('d-none');
    }

    download() {
        const options = {
            margin: 2,
            filename: 'file.pdf',
            image: { type: 'png', quality: 0.75 },
            html2canvas: { scale: 1.8},
            jsPDF: {unit: 'px', format: [this.editorTarget.offsetWidth, this.editorTarget.offsetWidth * 1.4], compress: true}
        };
        html2pdf().from(this.editorTarget).set(options).toPdf().save('myfile.pdf');
    }
}

