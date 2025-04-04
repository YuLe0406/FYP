document.addEventListener("DOMContentLoaded", function () {
    const addToCartBtn = document.getElementById("add-to-cart-btn");

    // Update button state based on selected items
    function updateButtonState() {
        const checkboxes = document.querySelectorAll(".wishlist-checkbox");
        addToCartBtn.disabled = !Array.from(checkboxes).some(checkbox => checkbox.checked);
    }

    // Event Listener for Checkbox Changes
    document.getElementById("wishlist-items").addEventListener("change", function () {
        updateButtonState();
    });

    // Add selected wishlist items to cart
    addToCartBtn.addEventListener("click", function () {
        const selectedItems = Array.from(document.querySelectorAll(".wishlist-checkbox:checked"))
            .map(checkbox => checkbox.getAttribute("data-id"));

        if (selectedItems.length > 0) {
            fetch("add_to_cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ items: selectedItems })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Added to cart successfully!");
                    location.reload();
                } else {
                    alert("Error adding to cart.");
                }
            });
        }
    });
});
