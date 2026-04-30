document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-image-preview-input]').forEach((input) => {
        const previewTargetId = input.getAttribute('data-image-preview-target');
        const previewNameId = input.getAttribute('data-image-preview-name');
        const wrapper = previewTargetId ? document.getElementById(previewTargetId) : null;
        const image = wrapper?.querySelector('[data-image-preview-image]') ?? null;
        const filename = previewNameId ? document.getElementById(previewNameId) : wrapper?.querySelector('[data-image-preview-filename]') ?? null;
        let objectUrl = null;

        if (!wrapper || !image) {
            return;
        }

        const resetPreview = () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            image.removeAttribute('src');
            wrapper.hidden = true;

            if (filename) {
                filename.textContent = '';
            }
        };

        input.addEventListener('change', () => {
            const [file] = input.files || [];

            if (!file || !file.type.startsWith('image/')) {
                resetPreview();
                return;
            }

            objectUrl = URL.createObjectURL(file);
            image.src = objectUrl;
            wrapper.hidden = false;

            if (filename) {
                filename.textContent = file.name;
            }
        });

        resetPreview();
    });
});
