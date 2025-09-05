function initFileUpload() {
    document.querySelectorAll('.file-upload-block').forEach(block => {
        const fileInput = block.querySelector('.file-upload');
        const fileInfo = block.querySelector('.file-info');
        const fileUploadWrapper = block.querySelector('.file-upload-wrapper');
        const customFileTrigger = block.querySelector('.custom-file-trigger');
        const errorMessage = block.closest('.upload-file').querySelector('.text-danger-orange');

        // Allowed extensions
        const allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'docx', 'doc', 'xls', 'csv'];

        if (!fileInput || !customFileTrigger) return;

        // Click event for custom button
        customFileTrigger.onclick = () => fileInput.click();

        // File selection change
        fileInput.onchange = () => {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                const fileExt = fileName.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExt)) {
                    // Invalid extension
                    errorMessage.style.display = 'block';
                    fileInput.value = ''; // clear invalid file
                    fileInfo.style.display = 'none';
                    fileUploadWrapper.style.display = 'block';
                    return;
                }

                // Hide error when valid file is selected
                errorMessage.style.display = 'none';

                // Show file info
                fileInfo.innerHTML = `
                    <div class="d-flex align-item-center gap-1 remove-file">
                        <span class="display-file font-size-12">${fileName}</span>
                        <i class="bi bi-trash3 text-danger font-size-12 ml-3" style="cursor:pointer;"></i>
                    </div>
                `;
                fileInfo.style.display = 'block';
                fileUploadWrapper.style.display = 'none';

                // Remove file event
                fileInfo.querySelector('.remove-file').onclick = () => {
                    fileInput.value = '';
                    fileInfo.innerHTML = '';
                    fileInfo.style.display = 'none';
                    fileUploadWrapper.style.display = 'block';
                };
            }
        };
    });
}



// End of Custom file input button
let ckEditorInstance = null;

function initCkEditor(initialData = '') {
    const editorElement = document.querySelector('#msg');

    if (!editorElement) {
        console.warn('CKEditor target #msg not found');
        return;
    }

    if (ckEditorInstance) {
        ckEditorInstance.destroy()
            .then(() => createCkEditor(editorElement, initialData))
            .catch(error => {
                console.error('Error destroying previous CKEditor:', error);
                createCkEditor(editorElement, initialData);
            });
    } else {
        createCkEditor(editorElement, initialData);
    }
}

function createCkEditor(editorElement, initialData) {
    ClassicEditor
        .create(editorElement, {
            toolbar: [
                'heading', '|', 'bold', 'italic', 'link',
                'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'
            ],
        })
        .then(editor => {
            ckEditorInstance = editor;
            editor.setData(initialData || '');
            editor.ui.view.editable.element.style.height = "180px";
        })
        .catch(error => console.error('CKEditor error:', error));
}

// Destroy CKEditor when modal closes
document.getElementById('dynamicMessageModal').addEventListener('hidden.bs.modal', function () {
    if (ckEditorInstance) {
        ckEditorInstance.destroy();
        ckEditorInstance = null;
    }
});

// === AJAX Function ===
function messageModal(url, senderId, receiverId, subject, message = '', productId = '') {
    console.log('url', url);

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        type: "POST",
        url,
        dataType: 'json',
        data: { senderId, receiverId, subject, message, productId },
        success: function (response) {
            if (response.status == false) {
                toastr.error(response.message);
            } else {
                // Inject HTML
                $('#dynamicMessageModal .message_html').html(response.message_html);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('dynamicMessageModal'));
                modal.show();

                // Wait until modal transition completes
                document.getElementById('dynamicMessageModal')
                    .addEventListener('shown.bs.modal', function initOnce() {
                        initCkEditor(response.messageText);
                        initFileUpload();
                        // Remove listener so it won't duplicate
                        document.getElementById('dynamicMessageModal')
                            .removeEventListener('shown.bs.modal', initOnce);
                    });
            }
        },
        error: function () {
            toastr.error('Something Went Wrong..');
        }
    });
}

function submitModalForm(url) {
    const modal = document.getElementById('dynamicMessageModal');
    const fileInput = modal.querySelector('.file-upload');
    const errorMessage = modal.querySelector('.text-danger-orange');

    errorMessage.style.display = 'none';

    // === File Validation ===
    const allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'docx', 'doc', 'xls', 'csv'];
    if (fileInput.files.length > 0) {
        const fileName = fileInput.files[0].name;
        const fileExt = fileName.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExt)) {
            errorMessage.style.display = 'block';
            return; // stop submission
        }
    }

    // === Collect Form Data ===
    const formData = new FormData();
    formData.append('senderId', modal.querySelector('input[name="senderId"]').value);
    formData.append('receiverId', modal.querySelector('input[name="receiverId"]').value);
    formData.append('productId', modal.querySelector('input[name="productId"]')?.value || '');
    formData.append('subject', modal.querySelector('input[placeholder="Subject"]').value);
    formData.append('message', ckEditorInstance.getData());
    if (fileInput.files.length > 0) {
        formData.append('attachment', fileInput.files[0]);
    }

    // === AJAX Call ===
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                toastr.success(data.message || 'Message sent successfully');
                bootstrap.Modal.getInstance(modal).hide();
            } else {
                toastr.error(data.message || 'Failed to send message');
            }
        })
        .catch(() => toastr.error('Something went wrong'));
}

document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'sendMessageBtn') {
        const url = e.target.dataset.url;
        submitModalForm(url);
    }
});

