document.addEventListener("DOMContentLoaded", function () {
    const cartTable = document.querySelector("#cart-items tbody");
    const cartTotal = document.querySelector("#cart-total");

    function updateTotal() {
        let total = 0;
        document.querySelectorAll(".item-total").forEach((item) => {
            total += parseFloat(item.textContent.replace("RM ", ""));
        });
        cartTotal.textContent = "RM " + total.toFixed(2);
    }

    cartTable.addEventListener("click", function (event) {
        const row = event.target.closest("tr");
        const productId = row.getAttribute("data-id");
        const size = row.getAttribute("data-size");
        const quantityInput = row.querySelector(".quantity-input");

        if (event.target.classList.contains("plus")) {
            let newQty = parseInt(quantityInput.value) + 1;
            quantityInput.value = newQty;
            updateItem(productId, size, newQty, row);
        } else if (event.target.classList.contains("minus")) {
            let newQty = parseInt(quantityInput.value) - 1;
            if (newQty > 0) {
                quantityInput.value = newQty;
                updateItem(productId, size, newQty, row);
            }
        } else if (event.target.classList.contains("remove-btn")) {
            removeItem(productId, size, row);
        }
    });

    function updateItem(productId, size, newQty, row) {
        fetch("update_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: productId, size: size, quantity: newQty }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.querySelector(".item-total").textContent = "RM " + data.newTotal.toFixed(2);
                updateTotal();
            }
        });
    }

    function removeItem(productId, size, row) {
        fetch("remove_from_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: productId, size: size }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
                updateTotal();
            }
        });
    }
});
