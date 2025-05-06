<?php
include 'db.php';  // Database connection
include 'header.php';      // Header file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist | CTRL+X</title>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Main Layout */
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .wishlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .wishlist-header h1 {
            font-size: 2rem;
            color:  #4a6baf;
        }

        .back-to-shop {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #4a6baf;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border: 1px solid #4a6baf;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .back-to-shop:hover {
            background-color: #4a6baf;
            color: white;
        }

        /* Empty Wishlist */
        .empty-wishlist {
            text-align: center;
            padding: 4rem 0;
        }

        .empty-wishlist i {
            font-size: 5rem;
            color: #e0e0e0;
            margin-bottom: 1rem;
        }

        .empty-wishlist p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 1.5rem;
        }

        .shop-now-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .shop-now-btn:hover {
            background-color: #555;
        }

        /* Wishlist Items */
        #wishlist-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .wishlist-item {
            display: flex;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .wishlist-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .wishlist-item.out-of-stock {
            opacity: 0.7;
        }

        .wishlist-item-img {
            width: 120px;
            height: 150px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .wishlist-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wishlist-item-details {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .wishlist-item-details h3 {
            font-size: 1rem;
            margin: 0 0 0.5rem 0;
            color: #333;
        }

        .wishlist-item-details .price {
            font-weight: bold;
            color: #222;
            margin: 0.3rem 0;
        }

        .wishlist-item-details .size {
            color: #666;
            margin: 0.3rem 0;
            font-size: 0.9rem;
        }

        .stock-status {
            margin: 0.3rem 0;
            font-size: 0.9rem;
        }

        .stock-status.in-stock {
            color: #28a745;
        }

        .stock-status.out-of-stock {
            color: #dc3545;
        }

        .wishlist-actions {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
        }

        .add-to-cart-btn {
            flex-grow: 1;
            padding: 0.5rem;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 0.8rem;
        }

        .add-to-cart-btn:hover:not(:disabled) {
            background-color: #555;
        }

        .add-to-cart-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .remove-btn {
            padding: 0.5rem;
            background-color: #f8f9fa;
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .remove-btn:hover {
            background-color: #dc3545;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .wishlist-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .wishlist-item {
                flex-direction: column;
            }
            
            .wishlist-item-img {
                width: 100%;
                height: 200px;
            }
        }

        .footer {
            background-color: #f5f5f5;
            color: #333;
            padding: 40px 0 20px;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<main>
    <div class="wishlist-header">
        <h1><i class="fas fa-heart"></i>  Your Wishlist</h1>
        <a href="shop.php" class="back-to-shop">← Continue Shopping</a>     
    </div>

    <section id="wishlist-container">
        <div id="wishlist-items">
            <!-- Items will be loaded here by JavaScript -->
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>  <!-- Include Footer -->

<script>
document.addEventListener("DOMContentLoaded", function () {
    loadWishlist();
});

async function loadWishlist() {
    let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
    let wishlistContainer = document.getElementById("wishlist-items");
    wishlistContainer.innerHTML = "";

    if (wishlistItems.length === 0) {
        wishlistContainer.innerHTML = `
            <div class="empty-wishlist">
                <i class="far fa-heart"></i>
                <p>Your wishlist is empty</p>
                <a href="shop.php" class="shop-now-btn">Shop Now</a>
            </div>
        `;
    } else {
        // Get stock status for all items
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
            const stockStatus = isOutOfStock ? 'Out of Stock' : 'In Stock';
            const stockClass = isOutOfStock ? 'out-of-stock' : 'in-stock';

            let div = document.createElement("div");
            div.classList.add("wishlist-item");
            if (isOutOfStock) div.classList.add("out-of-stock");
            
            div.innerHTML = `
                <div class="wishlist-item-img">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="wishlist-item-details">
                    <h3>${item.name}</h3>
                    <p class="price">RM ${item.price.toFixed(2)}</p>
                    <p class="size">Size: ${item.size}</p>
                    <p class="stock-status ${stockClass}">
                        <i class="fas ${isOutOfStock ? 'fa-times-circle' : 'fa-check-circle'}"></i>
                        ${stockStatus}
                    </p>
                    <div class="wishlist-actions">
                        <button class="add-to-cart-btn" ${isOutOfStock ? 'disabled' : ''} onclick="addToCartFromWishlist(${index})">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="remove-btn" onclick="removeFromWishlist(${index})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            wishlistContainer.appendChild(div);
        });
    }
}

async function addToCartFromWishlist(index) {
    let wishlistItems = JSON.parse(localStorage.getItem("wishlist")) || [];
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
    const item = wishlistItems[index];

    // Check stock
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
        Swal.fire({
            icon: 'error',
            title: 'Out of Stock',
            text: 'This item is currently out of stock',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    let existingItem = cartItems.find(cartItem => 
        cartItem.id == item.id && cartItem.size === item.size);
    
    if (existingItem) {
        // Ensure not exceeding stock
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
            return;
        }
    } else {
        cartItems.push({ ...item, quantity: 1 });
    }

    localStorage.setItem("cart", JSON.stringify(cartItems));
    
    Swal.fire({
        icon: 'success',
        title: 'Item Added',
        text: `${item.name} has been added to your cart!`,
        confirmButtonColor: '#3085d6'
    });
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
            const removedItem = wishlistItems[index];
            wishlistItems.splice(index, 1);
            localStorage.setItem("wishlist", JSON.stringify(wishlistItems));
            loadWishlist();
            
            Swal.fire(
                'Removed!',
                `${removedItem.name} has been removed from your wishlist.`,
                'success'
            );
        }
    });
}
</script>

</body>
</html>