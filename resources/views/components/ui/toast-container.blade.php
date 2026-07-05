@props([
    'variant' => 'success',
])

<div
    x-data="{
        toasts: [],
        add(message, variant = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, variant });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
    }"
    x-on:toast.window="add($event.detail.message, $event.detail.variant)"
    class="toast toast-top toast-end z-100"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            class="alert shadow-lg"
            :class="{
                'alert-success': toast.variant === 'success',
                'alert-error': toast.variant === 'error',
                'alert-info': toast.variant === 'info',
                'alert-warning': toast.variant === 'warning',
            }"
            x-transition
        >
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>
