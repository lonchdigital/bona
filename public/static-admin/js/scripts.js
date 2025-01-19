
// Get the header
var header = document.getElementById("form-header");
var sticky = 131;

function stickyHeaderFunction() {
    if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
}

window.onscroll = function() {stickyHeaderFunction()};


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

