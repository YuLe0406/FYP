document.addEventListener("DOMContentLoaded", function () {
    const banner = document.querySelector(".banner");
    const images = document.querySelectorAll(".banner img");
    const totalImages = images.length;
    const dotsContainer = document.querySelector(".dots-container");
    const leftBtn = document.querySelector(".left-btn");
    const rightBtn = document.querySelector(".right-btn");

    let index = 1; // Start at cloned first image
    let autoSlide;

    // Clone first & last images
    const firstClone = images[0].cloneNode(true);
    const lastClone = images[totalImages - 1].cloneNode(true);

    // Append clones
    banner.appendChild(firstClone);
    banner.insertBefore(lastClone, banner.firstChild);

    const updatedImages = document.querySelectorAll(".banner img");
    const newTotalImages = updatedImages.length;

    // Adjust banner width
    banner.style.width = `calc(100vw * ${newTotalImages})`;

    // Move to cloned first image
    banner.style.transform = `translateX(-100vw)`;

    function slideBanner() {
        index++;
        updateSlide();
    }

    function updateSlide() {
        banner.style.transition = "transform 1s linear";
        banner.style.transform = `translateX(-${index * 100}vw)`;

        // If at last cloned image, reset to real first image
        if (index === newTotalImages - 1) {
            setTimeout(() => {
                banner.style.transition = "none";
                index = 1;
                banner.style.transform = `translateX(-100vw)`;
            }, 1000);
        }

        // If at first cloned image, reset to real last image
        if (index === 0) {
            setTimeout(() => {
                banner.style.transition = "none";
                index = newTotalImages - 2;
                banner.style.transform = `translateX(-${index * 100}vw)`;
            }, 1000);
        }

        updateDots();
    }

    function moveToSlide(slideIndex) {
        index = slideIndex + 1;
        updateSlide();
        restartAutoSlide();
    }

    // **FIXED DOT INDICATORS**
    dotsContainer.innerHTML = ""; // Clear old dots
    for (let i = 0; i < totalImages; i++) {
        const dot = document.createElement("div");
        dot.classList.add("dot");
        if (i === 0) dot.classList.add("active");
        dot.addEventListener("click", () => moveToSlide(i));
        dotsContainer.appendChild(dot);
    }

    const dots = document.querySelectorAll(".dot");

    function updateDots() {
        let dotIndex = index - 1;

        // Fix dot index when reset happens
        if (index === newTotalImages - 1) dotIndex = 0; // When looping back to start
        if (index === 0) dotIndex = totalImages - 1; // When looping back to end

        dots.forEach((dot, i) => {
            dot.classList.toggle("active", i === dotIndex);
        });
    }

    // Left & Right button controls
    leftBtn.addEventListener("click", function () {
        index--;
        updateSlide();
        restartAutoSlide();
    });

    rightBtn.addEventListener("click", function () {
        index++;
        updateSlide();
        restartAutoSlide();
    });

    function restartAutoSlide() {
        clearInterval(autoSlide);
        autoSlide = setInterval(slideBanner, 4000);
    }

    restartAutoSlide();
});
