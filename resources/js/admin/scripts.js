
const sticky = 131;

function stickyHeaderFunction() {
    const header = document.getElementById("form-header");

    if (!header) {
        return;
    }

    header.classList.toggle("sticky", window.pageYOffset > sticky);
}

window.addEventListener("scroll", stickyHeaderFunction, {passive: true});


