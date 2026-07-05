

import Alpine from 'alpinejs';
import richTextEditor from './rich-text-editor';
import productImagePicker from './product-image-picker';

window.Alpine = Alpine;

Alpine.data('richTextEditor', richTextEditor);
Alpine.data('productImagePicker', productImagePicker);

Alpine.start();
