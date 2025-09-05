document.addEventListener("DOMContentLoaded", function () {
  /*** Sticky Header ***/
  // const header = document.getElementById("project_header");
  // const stickyPoint = header.offsetTop;
  // const placeholder = document.createElement("div");

  // placeholder.style.display = "none";
  // placeholder.style.height = `${header.offsetHeight}px`;
  // header.after(placeholder);

  // window.addEventListener("scroll", () => {
  //   const isSticky = window.pageYOffset > stickyPoint;
  //   header.classList.toggle("sticky", isSticky);
  //   placeholder.style.display = isSticky ? "block" : "none";
  // });

  /*** Scroll Restoration ***/
  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }
  window.scrollTo(0, 0);

  /*** Logout Dropdown Toggle ***/
  const logout = document.getElementById("user_logout");
  const notifications = document.getElementById("Allnotification_messages");
  const notifyButton = document.getElementById("notifyButton");

  // Initialize: hide logout on page load
  if (logout) logout.style.display = "none";

  window.setLogout = function (event) {
    event.stopPropagation();

    // Hide notifications if open
    if (notifications && notifications.classList.contains("notishow")) {
      notifications.classList.remove("notishow");
    }

    // Toggle logout
    if (logout) {
      logout.style.display = logout.style.display === "block" ? "none" : "block";
    }
  };

  window.setNotify = function (event) {
    event.stopPropagation();

    // Hide logout if open
    if (logout && logout.style.display === "block") {
      logout.style.display = "none";
    }

    // Toggle notifications
    if (notifications) {
      notifications.classList.toggle("notishow");
    }
  };

  // Close all dropdowns on outside click
  document.addEventListener("click", () => {
    if (logout) logout.style.display = "none";
    if (notifications) notifications.classList.remove("notishow");
  });

  // Close all dropdowns on Esc key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      if (logout) logout.style.display = "none";
      if (notifications) notifications.classList.remove("notishow");
    }
  });

  /*** Sidebar Toggle ***/
  window.openNav = function () {
    const sidebar = document.getElementById("mySidebar");
    if (sidebar) sidebar.style.transform = "translateX(0)";
  };

  window.closeNav = function () {
    const sidebar = document.getElementById("mySidebar");
    if (sidebar) sidebar.style.transform = "translateX(-115%)";
  };

  /*** Bootstrap Tooltips ***/
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach((el) => {
    const tooltip = new bootstrap.Tooltip(el);
    el.addEventListener("click", () => tooltip.hide());
  });
});
