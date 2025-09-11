/***Scroll to top */
if ('scrollRestoration' in history) {
      history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);



/***Notification Modal*/

// function setNotify(event) {
//       event.stopPropagation();
//       var getNotification = document.getElementById("Allnotification-messages");

//       if (getNotification) {
//             getNotification.classList.toggle('notishow');
//       }
//        document.body.classList.add("no-scroll");
// }
// document.addEventListener("click", function () {
//       var getNotification = document.getElementById("Allnotification-messages");
//       getNotification.classList.remove('notishow');
//         document.body.classList.remove("no-scroll");
// });

/****ToolTip */
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      var tooltip = new bootstrap.Tooltip(tooltipTriggerEl);

      // Add click listener to hide tooltip
      tooltipTriggerEl.addEventListener('click', function () {
            tooltip.hide();
      });

      return tooltip;
});



 // Show the button after scrolling 100px
  window.onscroll = function () {
    const btn = document.getElementById("back-to-top-btn");
    btn.style.display = (document.documentElement.scrollTop > 100) ? "block" : "none";
  };

  // Scroll smoothly to the top
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }



  // Start of Custom Toggle Accordion
  document.querySelectorAll('.custom-toggle-accordion').forEach(accordion => {
  accordion.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault(); // Prevent default behavior
      e.stopPropagation();

      const targetId = this.getAttribute('data-bs-target');
      const targetEl = document.querySelector(targetId);

      if (targetEl.classList.contains('show')) {
        const instance = bootstrap.Collapse.getInstance(targetEl);
        if (instance) {
          instance.hide();
        }
      } else {
        // Avoid interfering with Bootstrap's built-in logic
        bootstrap.Collapse.getOrCreateInstance(targetEl).show();
      }
    });
  });
});
// End of Custom Toggle Accordion

// Start of Custom file input button
document.querySelectorAll('.file-upload-block').forEach(block => {
  const fileInput = block.querySelector('.file-upload');
  const fileInfo = block.querySelector('.file-info');
  const fileUploadWrapper = block.querySelector('.file-upload-wrapper');
  const customFileTrigger = block.querySelector('.custom-file-trigger');

  customFileTrigger.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      const fileName = fileInput.files[0].name;
      fileInfo.innerHTML = `
      <div class="d-flex align-item-center gap-1 remove-file">
        <span class="display-file font-size-12">${fileName}</span>
        <i class="bi bi-trash3 text-danger font-size-12 ml-3 " style="cursor:pointer;"></i>
      </div>
      `;
      fileInfo.style.display = 'block';
      //fileUploadWrapper.style.display = 'none';

      fileInfo.querySelector('.remove-file').addEventListener('click', () => {
        fileInput.value = '';
        fileInfo.innerHTML = '';
        fileInfo.style.display = 'none';
        fileUploadWrapper.style.display = 'block';
      });
    }
  });
});
// End of Custom file input button

// Start of Custom file input V2 button
document.querySelectorAll('.file-upload-block-tooltip').forEach(block => {
  const fileInput = block.querySelector('.file-upload');
  const fileInfo = block.querySelector('.file-info');
  const fileUploadWrapper = block.querySelector('.file-upload-wrapper');
  const customFileTrigger = block.querySelector('.custom-file-trigger');

  customFileTrigger.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      const fileName = fileInput.files[0].name;
      const showTooltip = fileName.length > 30;

      fileInfo.innerHTML = `
        <div class="d-flex align-item-center gap-1 remove-file">
          <span class="display-file font-size-12">${fileName}</span>
          ${
            showTooltip
              ? `<button type="button"
                  class="ra-btn ra-btn-link height-inherit text-black font-size-12"
                  data-bs-toggle="tooltip" data-bs-placement="top"
                  title="${fileName}">
                  <span class="bi bi-info-circle-fill font-size-12"></span>
                </button>`
              : ''
          }
          <span class="bi bi-trash3 text-danger font-size-12 ml-3 remove-file-btn" style="cursor:pointer;"></span>
        </div>
      `;

      fileInfo.style.display = 'block';
      
      if (showTooltip) {
        const tooltipTriggerList = [].slice.call(fileInfo.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
      }

      fileInfo.querySelector('.remove-file-btn').addEventListener('click', () => {
        fileInput.value = '';
        fileInfo.innerHTML = '';
        fileInfo.style.display = 'none';
        fileUploadWrapper.style.display = 'block';
      });
    }
  });
});

// End of Custom file input V2 button

// Start of Simple Browse Button
document.querySelectorAll('.simple-file-upload').forEach(wrapper => {
  const fileInput = wrapper.querySelector('.real-file-input');
  const fileDisplayBox = wrapper.querySelector('.file-display-box');
  let tooltip = bootstrap.Tooltip.getInstance(fileDisplayBox);

  const reset = () => {
    fileInput.value = '';
    fileDisplayBox.textContent = 'Upload file';
    fileDisplayBox.removeAttribute('title');
    if (tooltip) { tooltip.dispose(); tooltip = null; }
  };

  // Click box to trigger file input when no file selected
  fileDisplayBox.addEventListener('click', () => {
    if (!fileInput.files.length) fileInput.click();
  });

  // On file select
  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      const fileName = fileInput.files[0].name;
      fileDisplayBox.innerHTML = `<span class="file-name">${fileName}</span><span class="remove-file ms-2 text-danger" style="cursor:pointer;">&times;</span>`;
      fileDisplayBox.setAttribute('title', fileName);

      if (tooltip) { tooltip.dispose(); }
      tooltip = new bootstrap.Tooltip(fileDisplayBox, { title: fileName });

      const removeBtn = fileDisplayBox.querySelector('.remove-file');
      removeBtn.addEventListener('click', e => {
        e.stopPropagation();
        reset();
      });
    } else {
      reset();
    }
  });

  // Ensure initial state
  reset();
});

// End of Simple Browse Button
// Start Tagify on the input
    document.querySelectorAll('.tagify-input').forEach(input => {
      new Tagify(input);
    });
// End of Tagify on the input

// Start of Suggestion Dropdown in Multi Product add
document.querySelectorAll('.toggle-table-wrapper').forEach(wrapper => {
  const toggleBtn = wrapper.querySelector('.toggle-table-button');
  const toggleContent = wrapper.querySelector('.toggle-table-content');
  const toggleIcon = wrapper.querySelector('.toggle-icon');
  if(toggleBtn){
    toggleBtn.addEventListener('click', () => {
      const isVisible = toggleContent.style.display !== 'none';
      toggleContent.style.display = isVisible ? 'none' : 'block';
      
      toggleBtn.className = isVisible 
        ? 'toggle-table-button ra-btn btn-show-hide bg-success' 
        : 'toggle-table-button ra-btn btn-show-hide bg-danger';
        
      toggleIcon.className = isVisible 
        ? 'toggle-icon bi bi-plus-lg' 
        : 'toggle-icon bi bi-dash-lg';
    });
  }
});

// End of Suggestion Dropdown in Multi Product add

// Start of Prevent aria-hidden Warning while close Modal
document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(closeBtn => {
  closeBtn.addEventListener('click', () => {
    // Remove focus from button to prevent aria-hidden warning
    closeBtn.blur();
  });
});
// End of Prevent aria-hidden Warning while close Modal

// Start of Show Hide Icon
document.querySelectorAll('.pw-icon-show-hide').forEach(button => {
    button.addEventListener('click', () => {
      const input = button.parentElement.querySelector('.password-input');
      const icon = button.querySelector('span');

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash-fill');
      }
    });
  });
// End of Show Hide Icon

// Close notification menu
  document.addEventListener('DOMContentLoaded', () => {
  const profileButton = document.getElementById('dropdownProfileButton');
  const profileMenu = document.querySelector('.profile-dropdown-menu');
  const notifyButton = document.getElementById('notifyButton');
  const notificationMenu = document.getElementById('Allnotification-messages');

  // --- Helper functions ---
  function closeProfileMenu() {
    const instance = bootstrap.Dropdown.getOrCreateInstance(profileButton);
    instance.hide();
  }

  function closeNotificationMenu() {
    if (notificationMenu.classList.contains('notishow')) {
      notificationMenu.classList.remove('notishow');
      document.body.classList.remove('no-scroll');
    }
  }

  function isClickInside(el, target) {
    return el && (el === target || el.contains(target));
  }

  // --- Handle notification bell click ---
  notifyButton.addEventListener('click', function (e) {
    e.stopPropagation();
    const isOpen = notificationMenu.classList.contains('notishow');

    // Always close profile menu
    closeProfileMenu();

    // Toggle notification menu
    if (isOpen) {
      closeNotificationMenu();
    } else {
      notificationMenu.classList.add('notishow');
      document.body.classList.add('no-scroll');
    }
  });

  // --- Handle global click to close both menus ---
  document.addEventListener('click', function (e) {
    const target = e.target;

    // Close profile dropdown if click is outside it
    if (!isClickInside(profileButton, target) && !isClickInside(profileMenu, target)) {
      closeProfileMenu();
    }

    // Close notification dropdown if click is outside it
    if (!isClickInside(notifyButton, target) && !isClickInside(notificationMenu, target)) {
      closeNotificationMenu();
    }
  });

  // --- ESC key closes both ---
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeProfileMenu();
      closeNotificationMenu();
    }
  });
});
// End of Logout Dropdown button

