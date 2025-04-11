function syncLocalData() {
    // Sync Wishlist
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    fetch('sync_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(wishlist)
    });

    // Sync Cart
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    fetch('sync_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cart)
    });
    
    // Optional: Clear localStorage after sync
    localStorage.removeItem("wishlist");
    localStorage.removeItem("cart");
}
