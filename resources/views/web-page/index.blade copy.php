<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Mini Web Page Management</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Quill Editor CSS -->
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

        <style>
            body {
                background-color: #f9f9f9;
            }

            .form-section {
                max-width: 1000px;
                margin: 40px auto;
                background-color: #fff;
                padding: 30px 40px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            }

            #editor {
                height: 200px;
            }

            .file-hint {
                font-size: 12px;
                color: #e26b0a;
                white-space: nowrap;
            }

            .btn-cancel {
                border: 1px solid #dc3545;
                color: #dc3545;
                background-color: transparent;
            }

            .char-count {
                font-size: 12px;
                color: #666;
                margin-top: 5px;
            }

            .file-preview {
                margin-top: 8px;
            }

            .file-preview a {
                font-size: 14px;
            }

            .file-preview .btn {
                padding: 0.15rem 0.5rem;
                font-size: 12px;
            }
        </style>
    </head>

    <body>

        <div class="form-section">
            <h5>Mini Web Page Management</h5>

            <form id="miniWebForm" enctype="multipart/form-data">
                <!-- About Us -->
                <div class="row mb-3">
                    <label class="col-4 col-form-label">About Us</label>
                    <div class="col-8">
                        <div id="editor" class="form-control"></div>
                        <div class="char-count" id="charCount">Characters: 0/10000</div>
                    </div>
                </div>

                <!-- Catalogue Upload -->
                <div class="row mb-3">
                    <label class="col-4 col-form-label">Catalogue</label>
                    <div class="col-8">
                        <div class="d-flex align-items-center">
                            <input type="file" class="form-control file-input" id="catalogueFile"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="file-hint ms-2">(PDF/Image/Document)</div>
                        </div>
                        <div class="file-preview" id="preview-catalogueFile"></div>
                    </div>
                </div>

                <!-- Other Credentials Upload -->
                <div class="row mb-3">
                    <label class="col-4 col-form-label">Other Credentials</label>
                    <div class="col-8">
                        <div class="d-flex align-items-center">
                            <input type="file" class="form-control file-input" id="credentialsFile"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="file-hint ms-2">(PDF/Image/Document)</div>
                        </div>
                        <div class="file-preview" id="preview-credentialsFile"></div>
                    </div>
                </div>

                <!-- Home Banner Left -->
                <div class="row mb-3">
                    <label class="col-4 col-form-label">Home Banner Left</label>
                    <div class="col-8">
                        <div class="d-flex align-items-center">
                            <input type="file" class="form-control file-input" id="bannerLeft"
                                accept=".jpg,.jpeg,.png,.gif">
                            <div class="file-hint ms-2">(JPEG/JPG/PNG/GIF)</div>
                        </div>
                        <div class="file-preview" id="preview-bannerLeft"></div>
                    </div>
                </div>

                <!-- Home Banner Right -->
                <div class="row mb-3">
                    <label class="col-4 col-form-label">Home Banner Right</label>
                    <div class="col-8">
                        <div class="d-flex align-items-center">
                            <input type="file" class="form-control file-input" id="bannerRight"
                                accept=".jpg,.jpeg,.png,.gif">
                            <div class="file-hint ms-2">(JPEG/JPG/PNG/GIF)</div>
                        </div>
                        <div class="file-preview" id="preview-bannerRight"></div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="row mt-4">
                    <div class="col-4"></div>
                    <div class="col-8 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">SUBMIT</button>
                        <button type="button" class="btn btn-cancel">CANCEL</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- QuillJS -->
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

        <script>
            const quill = new Quill('#editor', { theme: 'snow' });
  const charLimit = 10000;
  const charCount = document.getElementById('charCount');

  // Character Limit for About Us
  quill.on('text-change', function () {
    let text = quill.getText().trim();
    if (text.length > charLimit) {
      quill.deleteText(charLimit, text.length);
      text = quill.getText().trim();
    }
    charCount.textContent = `Characters: ${text.length}/${charLimit}`;
  });

  // Allowed file types per field
  const allowedTypes = {
    'catalogueFile': ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'credentialsFile': ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'bannerLeft': ['image/jpeg', 'image/png', 'image/gif'],
    'bannerRight': ['image/jpeg', 'image/png', 'image/gif']
  };

  // File preview + validation
  document.querySelectorAll('.file-input').forEach(input => {
    input.addEventListener('change', function () {
      const file = this.files[0];
      const id = this.id;
      const preview = document.getElementById(`preview-${id}`);

      if (file) {
        if (!allowedTypes[id].includes(file.type)) {
          alert("Invalid file type.");
          this.value = "";
          preview.innerHTML = "";
          return;
        }

        const blobUrl = URL.createObjectURL(file);
        preview.innerHTML = `
          <div class="d-flex align-items-center gap-2 mt-2">
            <a href="${blobUrl}" download="${file.name}" target="_blank">${file.name}</a>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeFile('${id}')">Delete</button>
          </div>
        `;
      }
    });
  });

  // Remove file
  function removeFile(id) {
    document.getElementById(id).value = '';
    document.getElementById(`preview-${id}`).innerHTML = '';
  }

  // Final validation on submit
  document.getElementById('miniWebForm').addEventListener('submit', function (e) {
    for (const fieldId in allowedTypes) {
      const input = document.getElementById(fieldId);
      const file = input.files[0];
      if (file && !allowedTypes[fieldId].includes(file.type)) {
        alert(`Invalid file type in ${fieldId}`);
        e.preventDefault();
        return;
      }
    }
  });
        </script>

    </body>

</html>