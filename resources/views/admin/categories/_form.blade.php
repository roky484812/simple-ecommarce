@props([
    'category' => null,
    'parentOptions' => [],
])

<x-ui.input
    label="Name"
    name="name"
    value="{{ old('name', $category?->name) }}"
    required
    autofocus
/>

<x-ui.input
    label="Slug"
    name="slug"
    value="{{ old('slug', $category?->slug) }}"
    hint="Leave blank to auto-generate from the name."
/>

<fieldset class="fieldset">
    <legend class="fieldset-legend">Parent Category</legend>
    <select name="parent_id" id="parent_id" class="select w-full @error('parent_id') select-error @enderror">
        <option value="">— None (top-level category) —</option>
        @foreach ($parentOptions as $id => $name)
            <option value="{{ $id }}" @selected((int) old('parent_id', $category?->parent_id) === $id)>
                {{ $name }}
            </option>
        @endforeach
    </select>
    @error('parent_id')
        <p class="label text-error">{{ $message }}</p>
    @enderror
</fieldset>

<fieldset class="fieldset">
    <label class="label cursor-pointer justify-start gap-3">
        <input type="hidden" name="is_active" value="0" />
        <input
            type="checkbox"
            name="is_active"
            value="1"
            class="checkbox"
            {{ old('is_active', $category?->is_active ?? true) ? 'checked' : '' }}
        />
        <span class="fieldset-legend">Active</span>
    </label>
</fieldset>
