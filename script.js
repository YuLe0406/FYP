document.addEventListener("DOMContentLoaded", function () {
    const images = document.querySelectorAll(".banner img");
    const sizeDropdown = document.getElementById("size-select");
    const quantityInput = document.getElementById("quantity");

    if (sizeDropdown) {
        sizeDropdown.addEventListener("change", function () {
            const selectedOption = sizeDropdown.options[sizeDropdown.selectedIndex];
            const stock = parseInt(selectedOption.getAttribute("data-stock")) || 1;

            quantityInput.max = stock;
            quantityInput.value = 1;

            if (stock <= 0) {
                quantityInput.disabled = true;
            } else {
                quantityInput.disabled = false;
            }
        });
    }
});



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


/*Add to Cart with Stock Validation (SweetAlert2)*/

async function addToCart() {
    // 1. Get DOM elements
    const sizeDropdown = document.getElementById("size-select");
    const quantityInput = document.getElementById("quantity");
    
    // 2. Basic validation
    if (!sizeDropdown || !quantityInput) {
        await showErrorAlert("System Error", "Could not find size or quantity inputs");
        return;
    }

    const selectedSize = sizeDropdown.value;
    const selectedQuantity = parseInt(quantityInput.value) || 1;
    const selectedOption = sizeDropdown.options[sizeDropdown.selectedIndex];
    const availableStock = parseInt(selectedOption.getAttribute("data-stock")) || 0;
    const variantId = selectedOption.getAttribute("data-variant-id");

    // 3. Validate size selection
    if (!selectedSize) {
        await showErrorAlert("Size Required", "Please select a size first");
        return;
    }

    // 4. Validate stock
    if (selectedQuantity > availableStock) {
        await showStockAlert(availableStock, selectedSize);
        quantityInput.value = availableStock;
        return;
    }

    // 5. Get current cart
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const productId = product.id;
    const productData = {
        id: productId,
        name: product.name,
        price: product.price,
        image: product.image,
        size: selectedSize,
        quantity: selectedQuantity,
        variantId: variantId
    };

    // 6. Check for existing item
    const existingItemIndex = cart.findIndex(item => 
        item.id === productId && item.size === selectedSize
    );

    // 7. Update or add item
    if (existingItemIndex >= 0) {
        const newQuantity = cart[existingItemIndex].quantity + selectedQuantity;
        
        if (newQuantity > availableStock) {
            await showStockAlert(availableStock, selectedSize);
            cart[existingItemIndex].quantity = availableStock;
        } else {
            cart[existingItemIndex].quantity = newQuantity;
            await showSuccessAlert("Cart Updated", `${product.name} quantity increased`);
        }
    } else {
        cart.push(productData);
        await showSuccessAlert("Added to Cart", `${product.name} was added to your cart`);
    }

    // 8. Save and update UI
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCounter();
}

/**
 * Add to Wishlist with Stock Validation (SweetAlert2)
 */
async function addToWishlist(id, name, image, price) {
    // 1. Get DOM elements
    const sizeDropdown = document.getElementById("size-select");
    
    // 2. Basic validation
    if (!sizeDropdown) {
        await showErrorAlert("System Error", "Could not find size selection");
        return;
    }

    const selectedSize = sizeDropdown.value;
    const selectedOption = sizeDropdown.options[sizeDropdown.selectedIndex];
    const availableStock = parseInt(selectedOption.getAttribute("data-stock")) || 0;
    const variantId = selectedOption.getAttribute("data-variant-id");

    // 3. Validate size selection
    if (!selectedSize) {
        await showErrorAlert("Size Required", "Please select a size first");
        return;
    }

    // 4. Validate stock
    if (availableStock <= 0) {
        await showErrorAlert("Out of Stock", "This item is currently unavailable");
        return;
    }

    // 5. Get current wishlist
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    const productData = {
        id,
        name,
        image,
        price,
        size: selectedSize,
        variantId
    };

    // 6. Check for existing item
    const exists = wishlist.some(item => 
        item.id === id && item.size === selectedSize
    );

    // 7. Add or notify
    if (exists) {
        await showInfoAlert("Already in Wishlist", `${name} (${selectedSize}) is already in your wishlist`);
    } else {
        wishlist.push(productData);
        localStorage.setItem("wishlist", JSON.stringify(wishlist));
        await showSuccessAlert("Added to Wishlist", `${name} was added to your wishlist`);
        updateWishlistCounter();
    }
}

/************************
 * Helper Functions (SweetAlert2)
 ************************/

async function showErrorAlert(title, text) {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonColor: '#3085d6',
        background: '#fff',
        backdrop: `rgba(255, 0, 0, 0.1)`
    });
}

async function showSuccessAlert(title, text) {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        confirmButtonColor: '#4CAF50',
        background: '#fff',
        timer: 1500,
        showConfirmButton: false
    });
}

async function showInfoAlert(title, text) {
    return Swal.fire({
        icon: 'info',
        title: title,
        text: text,
        confirmButtonColor: '#2196F3',
        background: '#fff'
    });
}

async function showStockAlert(stock, size) {
    return Swal.fire({
        icon: 'warning',
        title: 'Insufficient Stock',
        html: `Only <b style="color:#d32f2f">${stock}</b> available for size <b>${size}</b>`,
        confirmButtonColor: '#FF9800',
        background: '#fff',
        width: '400px'
    });
}

function updateCartCounter() {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const counter = document.getElementById("cart-counter");
    if (counter) {
        counter.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
    }
}

function updateWishlistCounter() {
    const wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    const counter = document.getElementById("wishlist-counter");
    if (counter) {
        counter.textContent = wishlist.length;
    }
}