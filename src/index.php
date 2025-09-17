<?php
// index.php - Updated with cart functionality
session_start();
ob_start();

include "koneksi.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$products = [];
$hasError = false;
$errorMessage = '';

try {
    $sql = "SELECT * FROM Menu ORDER BY MenuID ASC";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $hasError = true;
    $errorMessage = "Database error: " . $e->getMessage();
}

ob_end_clean();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--=============== FAVICON ===============-->
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">

    <!--=============== REMIXICONS ===============-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">

    <!--=============== SWIPER CSS ===============-->
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">

    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="assets/css/styles.css">

    <title>Responsive coffee website - Bedimcode</title>
    
    <!-- Cart Styles -->
    <style>
        .nav__actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .nav__cart {
            position: relative;
            color: var(--white-color);
            font-size: 1.5rem;
            transition: color .4s;
        }
        
        .nav__cart:hover {
            color: var(--first-color);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--first-color);
            color: var(--white-color);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: var(--font-semi-bold);
            min-width: 20px;
        }
        
        .cart-count:empty {
            display: none;
        }
        
        .products__button {
            transition: all .3s ease;
        }
        
        .products__button:hover {
            transform: scale(1.1);
            background-color: var(--first-color-alt);
        }
        
        .products__button.adding {
            animation: pulse 0.6s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .cart-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: var(--first-color);
            color: var(--white-color);
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .cart-notification.show {
            transform: translateX(0);
        }
        
        @media screen and (max-width: 1150px) {
            .nav__actions {
                position: absolute;
                right: 4rem;
            }
        }
    </style>
</head>
<body>
    <!--==================== HEADER ====================-->
    <header class="header" id="header">
        <nav class="nav container">
            <a href="#" class="nav__logo">ORDOCOFFEE</a>

            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li>
                        <a href="#home" class="nav__link">HOME</a>
                    </li>
                    <li>
                        <a href="#popular" class="nav__link">POPULAR</a>
                    </li>
                    <li>
                        <a href="#about" class="nav__link">ABOUT US</a>
                    </li>
                    <li>
                        <a href="#products" class="nav__link">PRODUCTS</a>
                    </li>
                    <li>
                        <a href="#contact" class="nav__link">CONTACT</a>
                    </li>
                </ul>
                <!-- Close button -->
                <div class="nav__close" id="nav-close">
                    <i class="ri-close-large-line"></i>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="nav__actions">
                <a href="cart.php" class="nav__cart">
                    <i class="ri-shopping-cart-line"></i>
                    <span class="cart-count" id="cart-count"><?= array_sum($_SESSION['cart']) > 0 ? array_sum($_SESSION['cart']) : '' ?></span>
                </a>
                
                <!-- Toggle button -->
                <div class="nav__toggle" id="nav-toggle">
                    <i class="ri-apps-2-fill"></i>
                </div>
            </div>
        </nav>
    </header>

    <!-- Cart Notification -->
    <div class="cart-notification" id="cart-notification">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="ri-check-line"></i>
            <span>Added to cart!</span>
        </div>
    </div>

    <!--==================== MAIN ====================-->
    <main class="main">
        <!--==================== HOME ====================-->
        <section class="home section" id="home">
            <div class="home__container container grid">
                <h1 class="home__title">COLD COFFEE</h1>

                <div class="home__images">
                    <div class="home__shape"></div>
                    <img src="assets/img/home-splash.png" alt="image" class="home__splash">
                    <img src="assets/img/bean-img.png" alt="image" class="home__bean-2">
                    <img src="assets/img/home-coffee.png" alt="image" class="home__coffee">
                    <img src="assets/img/bean-img.png" alt="image" class="home__bean-1">
                    <img src="assets/img/ice-img.png" alt="image" class="home__ice-1">
                    <img src="assets/img/ice-img.png" alt="image" class="home__ice-2">
                    <img src="assets/img/leaf-img.png" alt="image" class="home__leaf">
                </div>

                <img src="assets/img/home-sticker.svg" alt="image" class="home__sticker">

                <div class="home__data">
                    <p class="home__description">
                        Find delecious hot and cold coffees with the best varieties, calm the pleasure and enjoy a good coffee, order now.
                    </p>

                    <a href="#about" class="button">Learn More</a>
                </div>
            </div>
        </section>

        <!--==================== POPULAR ====================-->
        <section class="popular section" id="popular">
            <div class="popular__container container">
                <h2 class="section__title">POPULAR <br>CREATIONS<br></h2>

                <div class="popular__swiper swiper">
                    <div class="swiper-wrapper">
                        <article class="popular__card swiper-slide">
                            <div class="popular__images">
                                <div class="popular__shape"></div>
                                <img src="assets/img/bean-img.png" alt="image" class="popular__bean-2">
                                <img src="assets/img/popular-coffee-3.png" alt="image" class="popular__coffee">
                            </div>

                            <div class="popular__data">
                                <h2 class="popular__name">MOCHA COFFEE</h2>

                                <p class="popular__description">
                                    Indulge in the simplicity of our delicicous cold brew coffee.
                                </p>

                                <a href="#contact" class="button button-dark">Order now: $19.00</a>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!--==================== ABOUT ====================-->
        <section class="about section" id="about">
            <div class="about__container container grid">
                <div class="about__data">
                    <h2 class="section__title">LEARN MORE <br> ABOUT US</h2>

                    <p class="about__description">
                        Welcome to StarCoffee, where coffee is pure passion. 
                        From bean to cup, we are dedicated to delivering 
                        excellence in every sip. Join us on a journey of 
                        flavor and quality, crafted with love to create the 
                        ultimate coffee experience.
                    </p>

                    <a href="#popular" class="button">The Best Coffees</a>
                </div>

                <div class="about__images">
                    <div class="about__shape"></div>
                    <img src="assets/img/leaf-img.png" alt="image" class="about__leaf-1">
                    <img src="assets/img/leaf-img.png" alt="image" class="about__leaf-2">
                    <img src="assets/img/about-coffee.png" alt="image" class="about__coffee">
                </div>
            </div>
        </section>

        <!--==================== PRODUCTS ====================-->
        <section class="products section" id="products">
            <h2 class="section__title">THE MOST <br> REQUESTED</h2>

            <div class="products__container container grid">
                <?php if ($hasError): ?>
                    <div class="error-message">
                        <p>Unable to load products: <?= htmlspecialchars($errorMessage) ?></p>
                    </div>
                <?php elseif (empty($products)): ?>
                    <div class="no-products">
                        <p>No products available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php
                    $productCounter = 1;
                    foreach ($products as $product):
                        $ice_image = "assets/img/ice-img.png";
                    ?>
                        <article class="products__card">
                            <div class="products__images">
                                <div class="products__shape"></div>
                                <img src="<?= htmlspecialchars($ice_image) ?>" alt="image" class="products__ice-1">
                                <img src="<?= htmlspecialchars($ice_image) ?>" alt="image" class="products__ice-2">
                                <img src="assets/img/products-coffee-<?= $productCounter ?>.png" alt="<?= htmlspecialchars($product['NamaMenu']) ?>" class="products__coffee">
                            </div>

                            <div class="products__data">
                                <h3 class="products__name"><?= htmlspecialchars($product['NamaMenu']) ?></h3>
                                <span class="products__price">$<?= htmlspecialchars($product['Harga']) ?></span>

                                <button class="products__button" onclick="addToCart(<?= $product['MenuID'] ?>, '<?= htmlspecialchars($product['NamaMenu']) ?>')">
                                    <i class="ri-shopping-bag-3-fill"></i>
                                </button>
                            </div>
                        </article>
                    <?php
                        $productCounter++;
                        if ($productCounter > 12) $productCounter = 1;
                    endforeach;
                    ?>
                <?php endif; ?>
            </div>
        </section>

        <!--==================== CONTACT ====================-->
        <section class="contact section" id="contact">
            <h2 class="section__title">CONTACT US</h2>

            <div class="contact__container container grid">
                <div class="contact__info grid">
                    <div>
                        <h3 class="contact__title">Write Us</h3>

                        <div class="contact__social">
                            <a href="https://api.whatsapp.com/send?phone=51123456789&text=Hello" target="_blank" class="contact__social-link">
                                <i class="ri-whatsapp-fill"></i>
                            </a>

                            <a href="https://m.me/bedimcode" target="_blank" class="contact__social-link">
                                <i class="ri-messenger-fill"></i>
                            </a>

                            <a href="https://t.me/telegram" target="_blank" class="contact__social-link">
                                <i class="ri-telegram-2-fill"></i>
                            </a>
                        </div>
                    </div>

                    <div>
                        <h3 class="contact__title">Location</h3>

                        <address class="contact__address">
                            Lima - Sun City - Peru <br>
                            Av. Moon #4321
                        </address>

                        <a href="https://maps.app.goo.gl/MAmMDxUBFXBSUzLH7" class="contact__map">
                            <i class="ri-map-pin-fill"></i>
                            <span>View On Map</span>
                        </a>
                    </div>
                </div>

                <div class="contact__info grid">
                    <div>
                        <h3 class="contact__title">Delivery</h3>

                        <address class="contact__address">
                            +00-987-7654-321 <br>
                            +11-012345
                        </address>
                    </div>
                    
                    <div>
                        <h3 class="contact__title">Attention</h3>

                        <address class="contact__address">
                            Monday - Saturday <br> 
                            9AM - 10PM
                        </address>
                    </div>
                </div>

                <div class="contact__images">
                    <div class="contact__shape"></div>
                    <img src="assets/img/contact-delivery.png" alt="image" class="contact__delivery">
                </div>
            </div>
        </section>
    </main>

    <!--==================== FOOTER ====================-->
    <footer class="footer">
        <div class="footer__container container grid">
            <div>
                <h3 class="footer__title">Social</h3>

                <div class="footer__social">
                    <a href="https://www.facebook.com/" target="blank" class="footer__social-link">
                        <i class="ri-facebook-circle-fill"></i>
                    </a>

                    <a href="https://www.instagram.com/" target="blank" class="footer__social-link">
                        <i class="ri-instagram-fill"></i>
                    </a>

                    <a href="https://twitter.com/" target="blank" class="footer__social-link">
                        <i class="ri-twitter-fill"></i>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="footer__title">Payment Methods</h3>

                <div class="footer__pay">
                    <img src="assets/img/footer-card-1.png" alt="image" class="footer__pay-card">
                    <img src="assets/img/footer-card-2.png" alt="image" class="footer__pay-card">
                    <img src="assets/img/footer-card-3.png" alt="image" class="footer__pay-card">
                    <img src="assets/img/footer-card-4.png" alt="image" class="footer__pay-card">
                </div>
            </div>

            <div>
                <h3 class="footer__title">Subscribe For Discounts</h3>

                <form action="" class="footer__form">
                    <input type="email" placeholder="email" class="footer__input">
                    <button type="submit" class="footer__button button">Subscribe</button>
                </form>
            </div>
        </div>

        <span class="footer__copy">
            &#169; All Rights Reserved By Laurensius
        </span>
    </footer>

    <!--========== SCROLL UP ==========-->
    <a href="#" class="scrollup" id="scroll-up">
        <i class="ri-arrow-up-line"></i>
    </a>

    <!--=============== SCROLLREVEAL ===============-->
    <script src="assets/js/scrollreveal.min.js"></script>

    <!--=============== SWIPER JS ===============-->
    <script src="assets/js/swiper-bundle.min.js"></script>

    <!--=============== MAIN JS ===============-->
    <script src="assets/js/main.js"></script>
    
    <!--=============== CART JS ===============-->
    <script>
        // Add to cart function
        async function addToCart(menuId, menuName) {
            try {
                const button = event.target.closest('.products__button');
                button.classList.add('adding');
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&menu_id=${menuId}&quantity=1`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update cart count
                    const cartCount = document.getElementById('cart-count');
                    cartCount.textContent = result.cart_count;
                    cartCount.style.display = result.cart_count > 0 ? 'flex' : 'none';
                    
                    // Show notification
                    showCartNotification();
                }
                
                setTimeout(() => {
                    button.classList.remove('adding');
                }, 600);
                
            } catch (error) {
                console.error('Error adding to cart:', error);
            }
        }
        
        // Show cart notification
        function showCartNotification() {
            const notification = document.getElementById('cart-notification');
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cartCount = document.getElementById('cart-count');
            if (cartCount.textContent === '0' || cartCount.textContent === '') {
                cartCount.style.display = 'none';
            }
        });
    </script>
</body>
</html>" alt="image" class="popular__bean-1">
                                <img src="assets/img/bean-img.png" alt="image" class="popular__bean-2">
                                <img src="assets/img/popular-coffee-1.png" alt="image" class="popular__coffee">
                            </div>

                            <div class="popular__data">
                                <h2 class="popular__name">VANILLA LATTE</h2>

                                <p class="popular__description">
                                    Indulge in the simplicity of our delicicous cold brew coffee.
                                </p>

                                <a href="#contact" class="button button-dark">Order now: $19.00</a>
                            </div>
                        </article>

                        <article class="popular__card swiper-slide">
                            <div class="popular__images">
                                <div class="popular__shape"></div>
                                <img src="assets/img/bean-img.png" alt="image" class="popular__bean-1">
                                <img src="assets/img/bean-img.png" alt="image" class="popular__bean-2">
                                <img src="assets/img/popular-coffee-2.png" alt="image" class="popular__coffee">
                            </div>

                            <div class="popular__data">
                                <h2 class="popular__name">CLASSIC COFFEE</h2>

                                <p class="popular__description">
                                    Indulge in the simplicity of our delicicous cold brew coffee.
                                </p>

                                <a href="#contact" class="button button-dark">Order now: $19.00</a>
                            </div>
                        </article>

                        <article class="popular__card swiper-slide">
                            <div class="popular__images">
                                <div class="popular__shape"></div>
                                <img src="assets/img/bean-img.png" alt="image" class="popular__bean-1">
                                <img src="assets/img/bean-img.png