// Banner Auto-Scroll and Manual Controls
const banner = document.querySelector('.banner');
const images = document.querySelectorAll('.banner img');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

let index = 0;
const totalImages = images.length;

// Function to update the banner position
function updateBanner() {
    banner.style.transform = `translateX(-${index * 100}%)`;
}

// Auto-scroll every 3 seconds
let autoScroll = setInterval(() => {
    index = (index + 1) % totalImages;
    updateBanner();
}, 3000);

// Manual navigation
prevBtn.addEventListener('click', () => {
    index = (index - 1 + totalImages) % totalImages;
    updateBanner();
    resetAutoScroll();
});

nextBtn.addEventListener('click', () => {
    index = (index + 1) % totalImages;
    updateBanner();
    resetAutoScroll();
});

// Reset auto-scroll when manually navigating
function resetAutoScroll() {
    clearInterval(autoScroll);
    autoScroll = setInterval(() => {
        index = (index + 1) % totalImages;
        updateBanner();
    }, 3000);
}

// Scroll using mouse wheel
document.querySelector('.banner-container').addEventListener('wheel', (event) => {
    if (event.deltaY > 0) {
        index = (index + 1) % totalImages;
    } else {
        index = (index - 1 + totalImages) % totalImages;
    }
    updateBanner();
    resetAutoScroll();
});

// Search Functionality (Example: Logs the search query)
document.querySelector('.search-bar button').addEventListener('click', () => {
    let query = document.querySelector('.search-bar input').value;
    console.log("Searching for:", query);
    // Future: Implement category search logic here
});
