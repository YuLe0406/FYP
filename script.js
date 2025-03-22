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


const products = [
    { id: 1, name: "Oversized T-Shirt Black", price: 59.90, image: "1front.png", category: "oversized-t" },
    { id: 2, name: "Oversized T-Shirt White", price: 59.90, image: "2front.png", category: "oversized-t" },
    { id: 3, name: "Oversized T-Shirt White", price: 59.90, image: "3front.png", category: "oversized-t" },
    { id: 4, name: "Oversized T-Shirt White", price: 59.90, image: "4front.png", category: "oversized-t" },
    { id: 5, name: "Oversized T-Shirt White", price: 59.90, image: "5front.png", category: "oversized-t" },
    { id: 6, name: "Oversized T-Shirt White", price: 59.90, image: "6front.png", category: "oversized-t" },
    { id: 7, name: "Oversized T-Shirt White", price: 59.90, image: "7front.png", category: "oversized-t" },
    { id: 8, name: "Hoodie Gray", price: 89.90, image: "1Front.jpeg", category: "hoodie" },
    { id: 9, name: "Hoodie Green", price: 89.90, image: "2Front.jpeg", category: "hoodie" },
    { id: 10, name: "Hoodie Green", price: 89.90, image: "3Front.jpeg", category: "hoodie" },
    { id: 11, name: "Hoodie Green", price: 89.90, image: "4Front.jpeg", category: "hoodie" },
    { id: 12, name: "Hoodie Green", price: 89.90, image: "5Front.jpeg", category: "hoodie" },
    { id: 13, name: "Hoodie Green", price: 89.90, image: "6Front.jpeg", category: "hoodie" },
    { id: 14, name: "Hoodie Green", price: 89.90, image: "7Front.jpeg", category: "hoodie" }
];

// Load products dynamically into shop.html
function loadProducts(filter = "all", sort = "default") {
    let productList = document.getElementById("product-list");
    productList.innerHTML = "";

    let filteredProducts = products.filter(p => filter === "all" || p.category === filter);

    if (sort === "low-to-high") filteredProducts.sort((a, b) => a.price - b.price);
    if (sort === "high-to-low") filteredProducts.sort((a, b) => b.price - a.price);

    filteredProducts.forEach(product => {
        let productCard = document.createElement("div");
        productCard.className = "product-card";
        productCard.innerHTML = `
            <img src="images/${product.image}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p>RM ${product.price.toFixed(2)}</p>
            <button onclick="viewProduct(${product.id})">View Details</button>
        `;
        productList.appendChild(productCard);
    });
}

// Navigate to product details page
function viewProduct(id) {
    window.location.href = `product-details.html?id=${id}`;
}

function loadProductDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get("id");

    if (!productId) {
        document.getElementById("product-details").innerHTML = "<h2>Product Not Found</h2>";
        return;
    }

    const product = products.find(p => p.id == productId);

    if (product) {
        document.getElementById("productImage").src = "images/" + product.image;
        document.getElementById("productName").textContent = product.name;
        document.getElementById("productPrice").textContent = "RM " + product.price.toFixed(2);
    } else {
        document.getElementById("product-details").innerHTML = "<h2>Product Not Found</h2>";
    }
}

// Filtering & Sorting
document.getElementById("categoryFilter").addEventListener("change", (e) => loadProducts(e.target.value));
document.getElementById("sortPrice").addEventListener("change", (e) => loadProducts("all", e.target.value));

window.onload = () => {
    if (document.getElementById("product-list")) {
        loadProducts();
    }
};

// Add to cart function
function addToCart() {
    let name = document.getElementById("productName").textContent;
    let price = document.getElementById("productPrice").textContent;
    let size = document.getElementById("size").value;
    let quantity = document.getElementById("quantity").value;

    alert(`Added to Cart:\n${name}\nSize: ${size}\nQuantity: ${quantity}\n${price}`);
}

// Add to wishlist function
function addToWishlist() {
    let name = document.getElementById("productName").textContent;
    alert(`Added to Wishlist:\n${name}`);
}
