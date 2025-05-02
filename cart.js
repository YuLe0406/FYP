document.addEventListener("DOMContentLoaded", function() {
    loadCart();
    // 添加 SweetAlert2 初始化（如果需要）
});

async function loadCart() {
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
    let cartContainer = document.getElementById("cart-items");
    let cartTotal = document.getElementById("cart-total");
    cartContainer.innerHTML = "";

    let total = 0;

    if (cartItems.length === 0) {
        cartContainer.innerHTML = "<p>Your cart is empty.</p>";
    } else {
        // 并行获取所有商品的库存
        const stockPromises = cartItems.map(async (item, index) => {
            if (!item.variantId) {
                try {
                    // 如果没有variantId，尝试通过产品ID和尺寸查找
                    const response = await fetch('find_variant_id.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ P_ID: item.id, P_Size: item.size })
                    });
                    const result = await response.json();
                    if (result.PV_ID) {
                        cartItems[index].variantId = result.PV_ID;
                        localStorage.setItem("cart", JSON.stringify(cartItems));
                    }
                } catch (error) {
                    console.error("Error finding variant:", error);
                    return { index, stock: 1, error: true };
                }
            }
            
            try {
                const response = await fetch(`get_stock.php?pv_id=${item.variantId || 0}`);
                const data = await response.json();
                return { 
                    index, 
                    stock: data.stock || 0,
                    variantId: item.variantId,
                    success: data.success
                };
            } catch (error) {
                console.error("Error fetching stock:", error);
                return { index, stock: 0, error: true };
            }
        });

        const stockResults = await Promise.all(stockPromises);

        cartItems.forEach((item, index) => {
            const stockInfo = stockResults.find(r => r.index === index);
            const maxStock = stockInfo?.stock || 0;
            const currentQty = Math.min(item.quantity, maxStock);

            // 自动修正超过库存的数量
            if (item.quantity > maxStock) {
                item.quantity = currentQty;
                localStorage.setItem("cart", JSON.stringify(cartItems));
            }

            let cartItemDiv = document.createElement("div");
            cartItemDiv.classList.add("cart-item");
            cartItemDiv.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="cart-item-info">
                    <p><strong>${item.name}</strong></p>
                    <p>Size: ${item.size}</p>
                    <p>RM ${item.price.toFixed(2)}</p>
                    ${item.quantity > maxStock ? 
                        `<p class="stock-warning">Only ${maxStock} available!</p>` : ''}
                    ${stockInfo?.error ? 
                        `<p class="stock-error">⚠️ Failed to check stock</p>` : ''}
                </div>
                <input type="number" min="1" max="${maxStock}" 
                       value="${currentQty}" 
                       data-index="${index}" 
                       class="cart-qty">
                <button onclick="removeFromCart(${index})">Remove</button>
            `;
            cartContainer.appendChild(cartItemDiv);
            total += item.price * currentQty;
        });
    }

    cartTotal.innerText = `RM ${total.toFixed(2)}`;
    attachQuantityChangeEvents();
}

function attachQuantityChangeEvents() {
    document.querySelectorAll(".cart-qty").forEach(input => {
        input.addEventListener("change", async function() {
            const index = parseInt(this.getAttribute("data-index"));
            let newQty = parseInt(this.value) || 1;
            let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
            const item = cartItems[index];

            // 获取最新库存
            let maxStock = 1;
            if (item.variantId) {
                try {
                    const response = await fetch(`get_stock.php?pv_id=${item.variantId}`);
                    const data = await response.json();
                    maxStock = data.stock || 1;
                } catch (error) {
                    console.error("Error fetching stock:", error);
                }
            }

            // 确保数量在1和maxStock之间
            newQty = Math.max(1, Math.min(newQty, maxStock));

            if (newQty !== parseInt(this.value)) {
                // 如果用户输入的数量超过库存，显示警告并自动调整
                Swal.fire({
                    icon: 'error',
                    title: 'Stock Limit',
                    html: `Only <b>${maxStock}</b> available for ${item.name} (${item.size})`,
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    this.value = newQty;
                    cartItems[index].quantity = newQty;
                    localStorage.setItem("cart", JSON.stringify(cartItems));
                    loadCart();
                });
                return;
            }

            // 更新数量
            cartItems[index].quantity = newQty;
            localStorage.setItem("cart", JSON.stringify(cartItems));
            loadCart();
        });
    });
}

function removeFromCart(index) {
    Swal.fire({
        title: 'Remove Item',
        text: 'Are you sure you want to remove this item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
            cartItems.splice(index, 1);
            localStorage.setItem("cart", JSON.stringify(cartItems));
            loadCart();
            
            Swal.fire(
                'Removed!',
                'Item has been removed from your cart.',
                'success'
            );
        }
    });
}