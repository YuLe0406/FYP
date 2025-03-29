document.addEventListener("DOMContentLoaded", function () {
    const wishlistContainer = document.getElementById("wishlist-items");
    const addToCartBtn = document.getElementById("add-to-cart-btn");

    // Load wishlist from localStorage
    function loadWishlist() {
        let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
        wishlistContainer.innerHTML = "";

        if (wishlist.length === 0) {
            wishlistContainer.innerHTML = "<p>Your wishlist is empty.</p>";
            return;
        }

        wishlist.forEach((item, index) => {
            const itemElement = document.createElement("div");
            itemElement.classList.add("wishlist-item");

            itemElement.innerHTML = `
                <input type="checkbox" class="wishlist-checkbox" data-index="${index}">
                <img src="${item.image}" alt="${item.name}">
                <p>${item.name}</p>
                <p>RM ${item.price}</p>
            `;

            wishlistContainer.appendChild(itemElement);
        });

        updateButtonState();
    }

    // Update button state based on selected items
    function updateButtonState() {
        const checkboxes = document.querySelectorAll(".wishlist-checkbox");
        addToCartBtn.disabled = !Array.from(checkboxes).some(checkbox => checkbox.checked);
    }

    // Event Listener for Checkbox Changes
    wishlistContainer.addEventListener("change", function () {
        updateButtonState();
    });

    // Add selected wishlist items to cart
    addToCartBtn.addEventListener("click", function () {
        let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        const selectedItems = document.querySelectorAll(".wishlist-checkbox:checked");

        selectedItems.forEach((checkbox) => {
            const index = parseInt(checkbox.getAttribute("data-index"));
            cart.push(wishlist[index]);
        });

        // Save updated cart to localStorage
        localStorage.setItem("cart", JSON.stringify(cart));

        // Remove selected items from wishlist
        wishlist = wishlist.filter((_, i) => !Array.from(selectedItems).some(checkbox => parseInt(checkbox.getAttribute("data-index")) === i));
        localStorage.setItem("wishlist", JSON.stringify(wishlist));

        // Refresh wishlist display
        loadWishlist();
        alert("Selected items added to cart!");
    });

    loadWishlist();
});
