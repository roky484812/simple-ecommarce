let nextFileId = 1;

/**
 * Alpine.js component backing the "Product Images" picker. Manages a
 * client-side list of newly-selected (not yet uploaded) files so each can be
 * previewed with its own remove button before the form is submitted.
 *
 * Newly picked files are prepended to the existing pending list (closest to
 * the "Add More" tile), and the underlying <input type="file"> is kept in
 * sync via a DataTransfer so all accumulated files submit together.
 */
export default function productImagePicker() {
    return {
        pendingFiles: [],

        filesPicked(event) {
            const newFiles = Array.from(event.target.files).map((file) => ({
                file,
                __id: nextFileId++,
                __previewUrl: URL.createObjectURL(file),
            }));

            this.pendingFiles = [...newFiles, ...this.pendingFiles];
            this.syncInput();
        },

        removePendingFile(index) {
            const [removed] = this.pendingFiles.splice(index, 1);

            if (removed) {
                URL.revokeObjectURL(removed.__previewUrl);
            }

            this.syncInput();
        },

        /**
         * Rebuilds the real file input's FileList from the current
         * `pendingFiles` array, since FileList itself is read-only.
         */
        syncInput() {
            const dataTransfer = new DataTransfer();

            this.pendingFiles.forEach((entry) => dataTransfer.items.add(entry.file));

            this.$refs.input.files = dataTransfer.files;
        },
    };
}
