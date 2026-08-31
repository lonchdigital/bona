
const sticky = 131;

function stickyHeaderFunction() {
    const header = document.getElementById("form-header");

    if (!header) {
        return;
    }

    header.classList.toggle("sticky", window.pageYOffset > sticky);
}

window.addEventListener("scroll", stickyHeaderFunction, {passive: true});


// handle sidebar menu
document.addEventListener("DOMContentLoaded", function () {
    const adminCloseSideMenu = document.getElementById("adminCloseSideMenu");
    const sidebar = document.getElementById("leftSidebar");
    const toggleButton = document.getElementById("admin-side-menu-toggle");
    const toggleButtons = document.querySelectorAll("#admin-side-menu-toggle, #adminCloseSideMenu");

    function openSidebar() {
        sidebar.style.width = "256px";
    }

    function closeSidebar() {
        sidebar.style.width = "";
    }

    toggleButtons.forEach(button => {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            if (sidebar.style.width === "256px") {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    });

    /*toggleButton.addEventListener("click", function (event) {
        event.preventDefault();
        if (sidebar.style.width === "256px") {
            closeSidebar();
        } else {
            openSidebar();
        }
    });*/

    document.addEventListener("click", function (event) {
        if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
            closeSidebar();
        }
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });
});
