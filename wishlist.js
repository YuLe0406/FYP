document.addEventListener("DOMContentLoaded", function () {
    loadWishlist();
    document.getElementById("add-selected-to-cart").addEventListener("click", addSelectedToCart);
});

async function loadWishlist() {
    let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
    let wishlistContainer = document.getElementById("wishlist-items");
    wishlistContainer.innerHTML = "";

    if (wishlistItems.length === 0) {
        wishlistContainer.innerHTML = "<p>Your wishlist is empty.</p>";
    } else {
        // 获取所有商品的库存状态
        const stockPromises = wishlistItems.map(async (item, index) => {
            if (!item.variantId) return { index, stock: 1 };
            
            try {
                const response = await fetch(`get_stock.php?pv_id=${item.variantId}`);
                const data = await response.json();
                return { 
                    index, 
                    stock: data.stock || 0,
                    variantId: item.variantId 
                };
            } catch (error) {
                console.error("Error fetching stock:", error);
                return { index, stock: 0, error: true };
            }
        });

        const stockResults = await Promise.all(stockPromises);

        wishlistItems.forEach((item, index) => {
            const stockInfo = stockResults.find(r => r.index === index);
            const isOutOfStock = (stockInfo?.stock || 0) <= 0;

            let div = document.createElement("div");
            div.classList.add("wishlist-item");
            if (isOutOfStock) div.classList.add("out-of-stock");
            
            div.innerHTML = `
                <input type="checkbox" data-index="${index}" ${isOutOfStock ? 'disabled' : ''}>
                <img src="${item.image}" alt="${item.name}">
                <p>${item.name}</p>
                <p>Size: ${item.size}</p>
                <p>Price: RM ${item.price.toFixed(2)}</p>
                ${isOutOfStock ? '<p class="stock-status">Out of Stock</p>' : ''}
                <button class="remove-btn" onclick="removeFromWishlist(${index})">Remove</button>
            `;
            wishlistContainer.appendChild(div);
        });
    }
}

async function addSelectedToCart() {
    let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];

    const selectedCheckboxes = document.querySelectorAll("input[type='checkbox']:checked");

    if (selectedCheckboxes.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'No Selection',
            text: 'Please select at least one item to add to cart.',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    // 检查库存
    let outOfStockItems = [];
    let validItems = [];
    
    for (const checkbox of selectedCheckboxes) {
        const index = checkbox.getAttribute("data-index");
        const item = wishlistItems[index];
        
        // 获取库存
        let stock = 1;
        if (item.variantId) {
            try {
                const response = await fetch(`get_stock.php?pv_id=${item.variantId}`);
                const data = await response.json();
                stock = data.stock || 0;
            } catch (error) {
                console.error("Error fetching stock:", error);
            }
        }
        
        if (stock <= 0) {
            outOfStockItems.push(item.name);
        } else {
            validItems.push({ item, stock });
        }
    }

    // 如果有缺货商品
    if (outOfStockItems.length > 0) {
        await Swal.fire({
            icon: 'error',
            title: 'Out of Stock',
            html: `These items are out of stock: <b>${outOfStockItems.join(', ')}</b>`,
            confirmButtonColor: '#3085d6'
        });
    }

    // 只添加有库存的商品
    validItems.forEach(({ item, stock }) => {
        let existingItem = cartItems.find(cartItem => 
            cartItem.id == item.id && cartItem.size === item.size);
        
        if (existingItem) {
            // 确保不超过库存
            const newQty = existingItem.quantity + 1;
            if (newQty <= stock) {
                existingItem.quantity = newQty;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit',
                    html: `Only ${stock} available for ${item.name}`,
                    confirmButtonColor: '#3085d6'
                });
            }
        } else {
            cartItems.push({ ...item, quantity: 1 });
        }
    });

    localStorage.setItem("cart", JSON.stringify(cartItems));
    
    if (validItems.length > 0) {
        await Swal.fire({
            icon: 'success',
            title: 'Items Added',
            text: `${validItems.length} item(s) added to cart!`,
            confirmButtonColor: '#3085d6'
        });
        loadWishlist();
    }
}

function removeFromWishlist(index) {
    Swal.fire({
        title: 'Remove Item',
        text: 'Are you sure you want to remove this from your wishlist?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
            wishlistItems.splice(index, 1);
            localStorage.setItem("wishlist", JSON.stringify(wishlistItems));
            loadWishlist();
            
            Swal.fire(
                'Removed!',
                'Item has been removed from your wishlist.',
                'success'
            );
        }
    });
}