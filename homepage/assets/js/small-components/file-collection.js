/**
 * Handle file collection behavior.
 * In particular file upload forms: intercept form to asynchronously send it to
 * the backend, manage asynchronous upload, progress bar and success/error
 * message.
 */
(function() {
    const fileCollections = AppGlobals.elements.fileCollections;

    fileCollections.forEach(collection => {
        setupFileUpload(collection);
    });

    function setupFileUpload(collection) {
        const uploadForm = collection.querySelector('.upload-form');
        if (uploadForm == null) return;

        const button = uploadForm.querySelector('button[type="submit"]');
        const progress = uploadForm.querySelector('.upload-progress');
        const progress_perc = uploadForm.querySelector('.upload-progress-percent');
        const status = uploadForm.querySelector('.upload-status');

        uploadForm.addEventListener('submit', e => {
            e.preventDefault();

            button.disabled = true;

            const xhr = new XMLHttpRequest();
            const data = new FormData(uploadForm);

            progress.hidden = false;
            progress.value = 0;
            status.textContent = 'Uploading...';

            xhr.upload.addEventListener('progress', e => {
                if (!e.lengthComputable) return;
                progress.value = e.loaded / e.total * 100;
                progress_perc.textContent = Math.round(progress.value) + '%';
            });

            xhr.addEventListener('load', () => {
                if (xhr.status < 200 || xhr.status >= 300) {
                    status.textContent = 'Upload failed.';
                    button.disabled = false;
                    return;
                }

                const result = JSON.parse(xhr.responseText);

                if (!result.success) {
                    status.textContent = 'Upload failed.';
                    if (result.error != null)
                        status.textContent += ' ' + result.error;
                    button.disabled = false;
                    return;
                }

                status.textContent = 'Upload complete.';
                progress.value = 100;
                progress_perc.textContent = Math.round(progress.value) + '%';
                button.disabled = false;

                console.log(result.file);
            });

            xhr.addEventListener('error', () => {
                status.textContent = 'Upload failed.';
                button.disabled = false;
            });

            xhr.open('POST', uploadForm.action);
            xhr.send(data);
        });
    }
})();
