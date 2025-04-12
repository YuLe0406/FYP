document.addEventListener("DOMContentLoaded", function () {
    const userId = sessionStorage.getItem("user_id"); // From login process

    if (!userId) {
        console.log("User not logged in. Skipping sync.");
        return;
    }

    // --- Sync Wishlist ---
    const wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    if (wishlist.length > 0) {
        fetch("sync_wishlist.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ user_id: userId, wishlist })
        })
        .then(res => res.text())
        .then(data => {
            console.log("Wishlist synced:", data);
            // Clear local wishlist (optional)
            // localStorage.removeItem("wishlist");
        })
        .catch(err => console.error("Wishlist sync error:", err));
    }

    // --- Sync Cart ---
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length > 0) {
        fetch("sync_cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ user_id: userId, cart })
        })
        .then(res => res.text())
        .then(data => {
            console.log("Cart synced:", data);
            // Clear local cart (optional)
            // localStorage.removeItem("cart");
        })
        .catch(err => console.error("Cart sync error:", err));
    }
});
