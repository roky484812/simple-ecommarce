import Quill from 'quill';
import 'quill/dist/quill.snow.css';

/**
 * Alpine.js component that wires a Quill rich-text editor to a hidden
 * textarea/input so its HTML content is submitted with the surrounding form.
 *
 * Usage: <div x-data="richTextEditor('description', @js($initialHtml))">
 */
export default function richTextEditor(fieldName, initialHtml = '') {
    return {
        quill: null,

        init() {
            const editorEl = this.$refs.editor;
            const hiddenInput = this.$refs.input;

            this.quill = new Quill(editorEl, {
                theme: 'snow',
                placeholder: 'Describe the product…',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'blockquote'],
                        ['clean'],
                    ],
                },
            });

            if (initialHtml) {
                this.quill.clipboard.dangerouslyPasteHTML(initialHtml);
            }

            hiddenInput.value = initialHtml;

            this.quill.on('text-change', () => {
                const html = this.quill.getText().trim() === '' ? '' : this.quill.root.innerHTML;
                hiddenInput.value = html;
            });
        },
    };
}
