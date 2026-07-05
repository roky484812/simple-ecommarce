import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import richTextEditor from './rich-text-editor';
import productImagePicker from './product-image-picker';
import { ordersByStatusChart, topProductsChart, orderTrendChart } from './admin-charts';

/**
 * Livewire bundles its own Alpine.js. To use custom Alpine.data() components
 * on every page (including ones without a Livewire component, e.g. the
 * storefront product detail page), we manually bundle Livewire + Alpine
 * here via `@livewireScriptConfig` in the layouts, register our components,
 * then start Livewire ourselves. This avoids the "multiple instances of
 * Alpine running" conflict that a separate `import Alpine from 'alpinejs'`
 * + `Alpine.start()` would cause.
 */
Alpine.data('richTextEditor', richTextEditor);
Alpine.data('productImagePicker', productImagePicker);
Alpine.data('ordersByStatusChart', ordersByStatusChart);
Alpine.data('topProductsChart', topProductsChart);
Alpine.data('orderTrendChart', orderTrendChart);

Livewire.start();
