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
            <button type="button" @click="remove(toast.id)" class="btn btn-ghost btn-xs btn-circle" aria-label="Dismiss">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
            </button>
        </div>
    </template>
</div>
