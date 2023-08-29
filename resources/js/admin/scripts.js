
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



