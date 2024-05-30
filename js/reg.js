// Add smooth scrolling to the form
window.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("registration-form");
    const formTop = form.getBoundingClientRect().top + window.scrollY;
    const startPos = window.pageYOffset;

    window.addEventListener("scroll", function() {
        const currentPos = window.pageYOffset;
        const diff = currentPos - startPos;
        form.style.transform = `translateY(${diff}px)`;
    });

    window.dispatchEvent(new Event("scroll"));
});

// Add hover effects to the form
document.querySelectorAll(".form-group input").forEach(function(input) {
    input.addEventListener("mouseover", function() {
        input.parentNode.classList.add("hover");
    });

    input.addEventListener("mouseout", function() {
        input.parentNode.classList.remove("hover");
    });
});

document.querySelector("button").addEventListener("mouseover", function() {
    this.classList.add("hover");
});

document.querySelector("button").addEventListener("mouseout", function() {
    this.classList.remove("hover");
});