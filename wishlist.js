document.addEventListener("DOMContentLoaded", function () {
    loadWishlist();
    document.getElementById("add-selected-to-cart").addEventListener("click", addSelectedToCart);
});

function loadWishlist() {
    let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
    let wishlistContainer = document.getElementById("wishlist-items");
    wishlistContainer.innerHTML = "";

    if (wishlistItems.length === 0) {
        wishlistContainer.innerHTML = "<p>Your wishlist is empty.</p>";
    } else {
        wishlistItems.forEach((item, index) => {
            let div = document.createElement("div");
            div.classList.add("wishlist-item");
            div.innerHTML = `
                <input type="checkbox" data-index="${index}">
                <img src="images/${item.image}" alt="${item.name}">
                <p><strong>${item.name}</strong></p>
                <p>Price: RM ${item.price.toFixed(2)}</p>
                <button class="remove-btn" onclick="removeFromWishlist(${index})">Remove</button>
            `;
            wishlistContainer.appendChild(div);
        });
    }
}

function removeFromWishlist(index) {
    let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
    wishlistItems.splice(index, 1);
    localStorage.setItem("wishlist", JSON.stringify(wishlistItems));
    loadWishlist();
}

function addSelectedToCart() {
    let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];

    document.querySelectorAll("input[type='checkbox']:checked").forEach((checkbox) => {
        let index = checkbox.getAttribute("data-index");
        let item = wishlistItems[index];

        let existingCartItem = cartItems.find(cartItem => cartItem.id == item.id);
        if (existingCartItem) {
            existingCartItem.quantity += 1;
        } else {
            cartItems.push({ ...item, quantity: 1 });
        }
    });

    localStorage.setItem("cart", JSON.stringify(cartItems));
    alert("Selected items added to cart!");
    loadWishlist();
}
