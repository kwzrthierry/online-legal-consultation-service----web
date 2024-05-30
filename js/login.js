// Add scroll animations
document.addEventListener('scroll', function() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    elements.forEach(element => {
        if (isInViewport(element)) {
            element.classList.add('animated');
        } else {
            element.classList.remove('animated');
        }
    });
});

function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Hide scroll pane when not scrolling
let timeout;
document.addEventListener('scroll', function() {
    clearTimeout(timeout);
    document.body.classList.add('scrolling');
    timeout = setTimeout(() => {
        document.body.classList.remove('scrolling');
    }, 1000);
});

// Go back button function
function goBack() {
    window.location.href = '../index.html'; // Update this to the correct path to your homepage
}
