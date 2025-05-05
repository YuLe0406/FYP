<?php
// Start session at the very beginning to prevent headers already sent warning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <style>
        /* Hero Banner Styles */
        /* Hero Banner 样式 */
        .hero-banner {
            position: relative;
            width: 100vw;
            height: auto;
            overflow: hidden;
        }

        .banner-container {
            width: 100%;
            height: 100%;
        }

        .banner-track {
            display: flex;
            height: 100%;
            transition: transform 0.8s ease;
        }

        .banner-slide {
            width: 100vw;
            height: 100%;
            flex-shrink: 0;
            position: relative;
        }

        .banner-slide img {
            width: 100%;
            height: max-content;
            object-fit: contain; /* 或 contain */
            display: block;
        }

        /* 内容覆盖层 */
        .slide-content {
            position: absolute;
            bottom: 9%;
            left: 14%;
            color: white;
            background-color: rgba(0, 0, 0, 0.2);
            max-width: 500px;
        }
        
        .slide-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }
        
        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .slide-content .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: #A00000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .slide-content .btn:hover {
            background-color: #800000;
        }
        
        .banner-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.7);
            color: #000;
            border: none;
            padding: 1rem;
            cursor: pointer;
            font-size: 1.5rem;
            z-index: 10;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .banner-nav:hover {
            background: rgba(255, 255, 255, 0.9);
        }
        
        .banner-prev {
            left: 2rem;
        }
        
        .banner-next {
            right: 2rem;
        }
        
        .banner-dots {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.8rem;
            z-index: 10;
        }
        
        .banner-dot {
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .banner-dot.active {
            background: white;
            transform: scale(1.2);
        }
        
        /* Featured Categories */
        .featured-categories {
            padding: 5rem 2rem;
            background-color: #f5f5f5;
            text-align: center;
        }
        
        .section-title {
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #222;
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #A00000;
            margin: 1rem auto;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .category-card {
            position: relative;
            height: 300px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .category-card:hover {
            transform: scale(1.03);
        }
        
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .category-card:hover img {
            transform: scale(1.1);
        }
        
        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            padding: 2rem 1rem 1rem;
            color: white;
        }
        
        .category-overlay h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .category-overlay a {
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.5rem;
            font-weight: bold;
            transition: color 0.3s;
        }
        
        .category-overlay a:hover {
            color: #A00000;
        }
        
        /* Values Section */
        .values-section {
            padding: 5rem 2rem;
            background-color: white;
        }
        
        .values-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
        }
        
        .value-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .value-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #222;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .value-card h3:after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: #A00000;
            position: absolute;
            bottom: 0;
            left: 0;
        }
        
        .value-card p {
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .value-card ul {
            padding-left: 1.5rem;
        }
        
        .value-card ul li {
            margin-bottom: 0.8rem;
            line-height: 1.6;
        }
        
        .value-icon {
            font-size: 2rem;
            color: #A00000;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Banner Section -->
    <section class="hero-banner">
        <div class="banner-container">
            <div class="banner-track">
                <!-- Slide 1 -->
                <div class="banner-slide">
                    <img src="images/hpage.png" alt="New Collection">
                    <div class="slide-content">
                        <a href="shop.php" class="btn">Shop Now</a>
                    </div>
                </div>
                
                <!-- Slide 2 -->
                <div class="banner-slide">
                    <img src="images/casualwear.png" alt="Limited Edition">
                    <div class="slide-content">
                        <a href="shop.php" class="btn">View Collection</a>
                    </div>
                </div>
                
                <!-- Slide 3 -->
                <div class="banner-slide">
                    <img src="images/fashion.png" alt="Special Offers">
                    <div class="slide-content">
                        <a href="shop.php" class="btn">Shop Deals</a>
                    </div>
                </div>
            </div>
            
            <button class="banner-nav banner-prev">&#10094;</button>
            <button class="banner-nav banner-next">&#10095;</button>
            
            <div class="banner-dots">
                <div class="banner-dot active"></div>
                <div class="banner-dot"></div>
                <div class="banner-dot"></div>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="featured-categories">
        <h2 class="section-title">Shop Our Collections</h2>
        
        <div class="categories-grid">
            <div class="category-card">
                <img src="images/4Front.png" alt="Oversized Tees">
                <div class="category-overlay">
                    <h3>Oversized Tees</h3>
                    <a href="shop.php?category=oversized-t">Shop Now →</a>
                </div>
            </div>
            
            <div class="category-card">
                <img src="images/4Front.jpeg" alt="Hoodies">
                <div class="category-overlay">
                    <h3>Hoodies</h3>
                    <a href="shop.php?category=hoodie">Shop Now →</a>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <h2 class="section-title">Why Choose CTRL+X</h2>
        
        <div class="values-container">
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-tshirt"></i></div>
                <h3>Premium Quality</h3>
                <p>We use only the finest materials for lasting comfort and durability. Every CTRL+X garment undergoes rigorous quality checks to ensure it meets our high standards.</p>
                <ul>
                    <li>100% premium cotton</li>
                    <li>Reinforced stitching</li>
                    <li>Colorfast fabrics</li>
                </ul>
            </div>
            
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-truck"></i></div>
                <h3>Fast Shipping</h3>
                <p>We know you can't wait to wear your new favorites. That's why we offer lightning-fast shipping options.</p>
                <ul>
                    <li>Same-day dispatch</li>
                    <li>Free shipping over RM250</li>
                    <li>Tracked delivery</li>
                </ul>
            </div>
            
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-heart"></i></div>
                <h3>Customer First</h3>
                <p>Your satisfaction is our top priority. We're committed to providing exceptional service at every step.</p>
                <ul>
                    <li>Easy 30-day returns</li>
                    <li>24/7 customer support</li>
                    <li>Size exchange program</li>
                </ul>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
    const bannerTrack = document.querySelector('.banner-track');
    const slides = document.querySelectorAll('.banner-slide');
    const dots = document.querySelectorAll('.banner-dot');
    const prevBtn = document.querySelector('.banner-prev');
    const nextBtn = document.querySelector('.banner-next');
    
    // 克隆第一张和最后一张图片实现无缝滚动
    const firstSlide = slides[0].cloneNode(true);
    const lastSlide = slides[slides.length - 1].cloneNode(true);
    bannerTrack.appendChild(firstSlide);
    bannerTrack.insertBefore(lastSlide, slides[0]);
    
    let currentIndex = 1; // 从原始第一张开始
    const slideCount = slides.length;
    let slideWidth = slides[0].offsetWidth;
    let isTransitioning = false;
    
    // 初始化轮播
    function initSlider() {
        updateSliderPosition();
        startAutoSlide();
        
        // 窗口大小改变时重新计算
        window.addEventListener('resize', () => {
            slideWidth = slides[0].offsetWidth;
            updateSliderPosition(false); // 无动画
        });
        
        // 事件监听
        prevBtn.addEventListener('click', prevSlide);
        nextBtn.addEventListener('click', nextSlide);
        dots.forEach((dot, index) => dot.addEventListener('click', () => goToSlide(index + 1)));
    }
    
    // 更新轮播位置
    function updateSliderPosition(animate = true) {
        if (animate) {
            bannerTrack.style.transition = 'transform 0.8s ease';
            isTransitioning = true;
        } else {
            bannerTrack.style.transition = 'none';
        }
        
        bannerTrack.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        
        // 更新指示点（排除克隆的幻灯片）
        const activeDotIndex = (currentIndex - 1 + slideCount) % slideCount;
        dots.forEach((dot, i) => dot.classList.toggle('active', i === activeDotIndex));
    }
    
    // 处理过渡结束
    bannerTrack.addEventListener('transitionend', () => {
        if (!isTransitioning) return;
        
        // 无缝跳转（当到达克隆幻灯片时）
        if (currentIndex === 0) {
            currentIndex = slideCount;
            updateSliderPosition(false);
        } else if (currentIndex === slideCount + 1) {
            currentIndex = 1;
            updateSliderPosition(false);
        }
        
        isTransitioning = false;
    });
    
    // 自动轮播
    function startAutoSlide() {
        setInterval(() => {
            if (!isTransitioning) nextSlide();
        }, 5000);
    }
    
    // 下一张
    function nextSlide() {
        if (isTransitioning) return;
        currentIndex++;
        updateSliderPosition();
    }
    
    // 上一张
    function prevSlide() {
        if (isTransitioning) return;
        currentIndex--;
        updateSliderPosition();
    }
    
    // 跳转到指定幻灯片
    function goToSlide(index) {
        if (isTransitioning) return;
        currentIndex = index;
        updateSliderPosition();
    }
    
    initSlider();
});
    </script>
</body>
</html>