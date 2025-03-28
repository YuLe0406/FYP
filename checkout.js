document.addEventListener("DOMContentLoaded", function () {
    const paymentMethod = document.getElementById("payment-method");
    const cardDetails = document.getElementById("card-details");
    const phoneInput = document.getElementById("phone");
    const checkoutForm = document.getElementById("checkout-form");
    const placeOrderBtn = document.getElementById("place-order-btn");

    // Show/Hide Card Details Based on Payment Method
    paymentMethod.addEventListener("change", function () {
        if (this.value === "credit_card") {
            cardDetails.style.display = "block";
        } else {
            cardDetails.style.display = "none";
        }
    });

    // Malaysia Phone Number Validation (Format: 012-345 6789)
    phoneInput.addEventListener("input", function () {
        let formattedPhone = this.value.replace(/\D/g, ""); // Remove non-numeric characters
        if (formattedPhone.length >= 3) {
            formattedPhone = formattedPhone.substring(0, 3) + "-" + formattedPhone.substring(3);
        }
        if (formattedPhone.length >= 7) {
            formattedPhone = formattedPhone.substring(0, 7) + " " + formattedPhone.substring(7);
        }
        this.value = formattedPhone;
    });

    // Form Validation Before Submission
    placeOrderBtn.addEventListener("click", function (event) {
        if (paymentMethod.value === "credit_card") {
            const cardNumber = document.getElementById("card-number").value.trim();
            const cardName = document.getElementById("card-name").value.trim();
            const expiryDate = document.getElementById("expiry-date").value.trim();
            const cvv = document.getElementById("cvv").value.trim();

            if (cardNumber === "" || cardName === "" || expiryDate === "" || cvv === "") {
                alert("Please enter all credit card details.");
                event.preventDefault();
                return;
            }
        }

        // Validate Address Fields
        const address1 = document.getElementById("address1").value.trim();
        if (address1 === "") {
            alert("Please enter Address Line 1.");
            event.preventDefault();
            return;
        }

        alert("Order placed successfully!");
    });

    // Load Cart Items into Order Summary
    function loadCartItems() {
        const cartItemsContainer = document.getElementById("cart-items");
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        let total = 0;

        cartItemsContainer.innerHTML = "";
        cart.forEach((item) => {
            const itemElement = document.createElement("div");
            itemElement.classList.add("cart-item");

            itemElement.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <p>${item.name}</p>
                <p>RM ${item.price}</p>
            `;

            cartItemsContainer.appendChild(itemElement);
            total += parseFloat(item.price);
        });

        document.getElementById("cart-total").innerText = `RM ${total.toFixed(2)}`;
    }

    loadCartItems();
});
