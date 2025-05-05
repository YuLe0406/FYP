<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

// 获取所有产品
$products_query = "SELECT * FROM PRODUCT";
$products_result = mysqli_query($conn, $products_query);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);

// 获取分类
$categories_query = "SELECT * FROM CATEGORIES";
$categories_result = mysqli_query($conn, $categories_query);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

// 图片基础URL
$baseUrl = 'http://localhost/FYP/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | CTRL+X</title>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <style>
        /* Shop Page Styles */
        .shop-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .shop-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .shop-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .shop-controls {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
            padding:0 2rem;
        }

        .filter-section {
            flex: 1;
            min-width: 300px;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e0e0e0;
            background: white;
            color: #333;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-btn:hover {
            border-color: #333;
            background: #f8f8f8;
        }

        .filter-btn.active {
            background: #333;
            color: white;
            border-color: #333;
        }

        .sort-section {
            min-width: 200px;
        }

        .sort-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            background: white;
            cursor: pointer;
        }

        .sort-select:focus {
            outline: none;
            border-color: #333;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
            padding:0 2rem;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
        }

        .product-card img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-bottom: 1px solid #f0f0f0;
            transition: transform 0.5s ease;
        }

        .product-card:hover img {
            transform: scale(1.03);
        }

        .quick-view-btn {
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: bottom 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .product-card:hover .quick-view-btn {
            bottom: 0;
        }

        .product-card h3 {
            padding: 1rem 1rem 0.5rem;
            font-size: 1.1rem;
            color: #333;
            margin: 0;
        }

        .product-card p {
            padding: 0 1rem;
            font-size: 1.2rem;
            font-weight: bold;
            color: #222;
            margin: 0.5rem 0;
        }

        .product-card button {
            display: block;
            width: calc(100% - 2rem);
            margin: 1rem;
            padding: 0.75rem;
            background: #333;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .product-card button:hover {
            background: #555;
        }

        .shop-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .load-more-btn {
            padding: 0.75rem 2rem;
            background: white;
            border: 2px solid #333;
            color: #333;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .load-more-btn:hover {
            background: #333;
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .shop-controls {
                flex-direction: column;
            }
            
            .filter-section, .sort-section {
                width: 100%;
            }
            
            .category-filters {
                justify-content: center;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="shop-container">
    <div class="shop-header">
        <h1>Shop Our Collection</h1>
        <div class="shop-controls">
            <div class="filter-section">
                <div class="category-filters">
                    <button class="filter-btn active" data-category="all">All Products</button>
                    <?php foreach ($categories as $category): ?>
                        <button class="filter-btn" data-category="<?= $category['C_ID'] ?>">
                            <i class="fas <?= $category['C_ID'] == 1 ? 'fa-tshirt' : 'fa-hoodie' ?>"></i>
                            <?= htmlspecialchars($category['C_Name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="sort-section">
                <select id="sortPrice" class="sort-select">
                    <option value="default">Sort By</option>
                    <option value="low-to-high">Price: Low to High</option>
                    <option value="high-to-low">Price: High to Low</option>
                    <option value="newest">Newest Arrivals</option>
                </select>
            </div>
        </div>
    </div>
    
    <div id="product-list" class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image-container">
                    <img src="<?= $baseUrl . htmlspecialchars($product['P_Picture']) ?>" 
                         alt="<?= htmlspecialchars($product['P_Name']) ?>" 
                         loading="lazy">
                </div>
                <h3><?= htmlspecialchars($product['P_Name']) ?></h3>
                <p>RM <?= number_format($product['P_Price'], 2) ?></p>
                <button class="view-details-btn" onclick="viewProduct(<?= $product['P_ID'] ?>)">
                    View Details
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 当前查看的产品ID
    let currentProductId = 0;
    let currentProductName = '';
    let currentProductPrice = 0;


    // 查看产品详情
    function viewProduct(id) {
        window.location.href = `product-details.php?id=${id}`;
    }


    // AJAX过滤和排序
    function loadProducts(filter = "all", sort = "default") {
        fetch('filter_products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `filter=${filter}&sort=${sort}`
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('product-list').innerHTML = html;
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Failed to load products. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        });
    }


    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const sortValue = document.getElementById('sortPrice').value;
                loadProducts(this.dataset.category, sortValue);
            });
        });

        document.getElementById('sortPrice').addEventListener('change', (e) => {
            const activeFilter = document.querySelector('.filter-btn.active');
            const filterValue = activeFilter ? activeFilter.dataset.category : 'all';
            loadProducts(filterValue, e.target.value);
        });
    });
</script>

<?php include 'footer.php'; ?>

</body>
</html>