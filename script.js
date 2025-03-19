document.addEventListener("DOMContentLoaded", function () {
    const banner = document.querySelector(".banner");
    const images = document.querySelectorAll(".banner img");
    let index = 0;
    const totalImages = images.length;

    // Clone first image for infinite scroll effect
    const firstClone = images[0].cloneNode(true);
    banner.appendChild(firstClone);

    function slideBanner() {
        index++;
        banner.style.transition = "transform 1s linear";
        banner.style.transform = `translateX(-${index * 100}vw)`;

        // Reset without transition when looping back
        if (index === totalImages) {
            setTimeout(() => {
                banner.style.transition = "none";
                banner.style.transform = "translateX(0)";
                index = 0;
            }, 1000);
        }
    }

    setInterval(slideBanner, 4000); // Slide every 4 seconds
});
