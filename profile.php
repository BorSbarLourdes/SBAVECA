<?php
require_once 'db.php';

if (!isset($_SESSION['idCli'])) {
    header("Location: login.php");
    exit();
}

// Obtener info del usuario
$idCli = $_SESSION['idCli'];
$stmt = $pdo->prepare("SELECT u.nombreUsu, u.correoUsu, u.telefonoUsu FROM usuario u JOIN cliente c ON u.idUsu = c.usuarioIdUsu WHERE c.idCli = ?");
$stmt->execute([$idCli]);
$user = $stmt->fetch();
$nombreUsu = $user['nombreUsu'] ?? 'Usuario';
$correoUsu = $user['correoUsu'] ?? 'No disponible';
$telefonoCli = $user['telefonoUsu'] ?? 'No disponible';
?>
<!doctype html>
<html class="no-js" lang="zxx">

<head>
   <meta charset="utf-8">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title>SBAVECA | Pastelería &amp; Rostisería</title>
   <meta name="description" content="Sistema Gastronómico Integral y Tienda Online SBAVECA">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <!-- Place favicon.ico in the root directory -->
   <link rel="shortcut icon" type="image/x-icon" href="assets/img/logo/favicon.png">

   <!-- CSS here -->
   <link rel="stylesheet" href="assets/css/bootstrap.css">
   <link rel="stylesheet" href="assets/css/animate.css">
   <link rel="stylesheet" href="assets/css/swiper-bundle.css">
   <link rel="stylesheet" href="assets/css/slick.css">
   <link rel="stylesheet" href="assets/css/magnific-popup.css">
   <link rel="stylesheet" href="assets/css/font-awesome-pro.css">
   <link rel="stylesheet" href="assets/css/flaticon_shofy.css">
   <link rel="stylesheet" href="assets/css/spacing.css">
   <link rel="stylesheet" href="assets/css/main.css">
   <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Hanken+Grotesk:wght@400;500;600;700&display=swap"
      rel="stylesheet" />
   <style>
      :root {
         --tp-theme-primary: #270b01;
         --tp-theme-secondary: #7e5632;
         --tp-common-black: #1d1b18;
         --tp-text-body: #51443e;
         --tp-ff-body: 'Hanken Grotesk', sans-serif;
         --tp-ff-heading: 'Playfair Display', serif;
         --tp-ff-p: 'Hanken Grotesk', sans-serif;
         --tp-ff-jost: 'Playfair Display', serif;
         --tp-ff-roboto: 'Hanken Grotesk', sans-serif;
         --sbaveca-surface: #fef9f2;
         --sbaveca-primary-container: #401f0e;
         --sbaveca-on-primary-container: #b6846c;
      }

      body {
         font-family: 'Hanken Grotesk', sans-serif !important;
         background-color: var(--sbaveca-surface) !important;
         background-image: url("https://www.transparenttextures.com/patterns/natural-paper.png") !important;
         color: var(--tp-common-black) !important;
         overflow-x: hidden !important;
         max-width: 100vw;
      }

      h1,
      h2,
      h3,
      h4,
      h5,
      h6,
      .tp-section-title,
      .tp-slider-title,
      .tp-product-title,
      .tp-product-title-2 {
         font-family: 'Playfair Display', serif !important;
         color: var(--tp-theme-primary) !important;
      }

      /* Mobile Responsive Fixes */
      @media (max-width: 768px) {
         .tp-slider-title {
            font-size: 36px !important;
            line-height: 1.2 !important;
         }

         .tp-slider-content p {
            font-size: 16px !important;
         }

         .tp-theme-settings-area,
         .tp-theme-settings,
         .settings-btn {
            display: none !important;
         }

         .d-flex.align-items-center img[alt="SBAVECA"] {
            max-height: 45px !important;
         }

         .d-flex.align-items-center span {
            font-size: 18px !important;
         }
      }

      /* Fix for gigantic thumbnails in Product SM Area */
      .tp-product-sm-item .tp-product-thumb {
         width: 100px !important;
         height: 100px !important;
         flex: 0 0 100px !important;
      }

      .tp-product-sm-item .tp-product-thumb img {
         width: 100% !important;
         height: 100% !important;
         object-fit: cover !important;
      }

      /* Fix text contrast over images */
      .tp-product-gadget-categories,
      .tp-banner-item {
         position: relative;
      }

      .tp-product-gadget-categories::before,
      .tp-banner-item::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: linear-gradient(to right, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.3) 100%);
         z-index: 1;
         pointer-events: none;
         border-radius: inherit;
      }

      .tp-product-gadget-categories>*:not(.tp-product-gadget-thumb),
      .tp-banner-content {
         position: relative;
         z-index: 2;
      }

      .tp-product-gadget-categories-list ul li a {
         color: #ffffff !important;
         text-shadow: 1px 1px 2px rgba(0, 0, 0, 1);
      }

      .tp-product-gadget-categories-title {
         color: var(--tp-theme-primary) !important;
         text-shadow: 1px 1px 4px rgba(0, 0, 0, 1);
      }

      .tp-banner-title {
         color: #ffffff !important;
         text-shadow: 1px 1px 4px rgba(0, 0, 0, 1);
      }

      .tp-banner-content span {
         text-shadow: 1px 1px 3px rgba(0, 0, 0, 1);
      }
   </style>
</head>

<body>
   <!--[if lte IE 9]>
      <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
      <![endif]-->


   <!-- pre loader area start -->
   <div id="loading">
      <div id="loading-center">
         <div id="loading-center-absolute">
            <!-- loading content here -->
            <div class="tp-preloader-content">
               <div class="tp-preloader-logo">
                  <div class="tp-preloader-circle">
                     <svg width="190" height="190" viewBox="0 0 380 380" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle stroke="#D9D9D9" cx="190" cy="190" r="180" stroke-width="6" stroke-linecap="round">
                        </circle>
                        <circle stroke="red" cx="190" cy="190" r="180" stroke-width="6" stroke-linecap="round"></circle>
                     </svg>
                  </div>
                  <img src="assets/img/logo/sbavecaLogo.png" alt="" style="max-height: 130px;">
               </div>
               <h3 class="tp-preloader-title"
                  style="color: var(--tp-theme-primary); font-family: 'Playfair Display', serif;">SBAVECA</h3>
               <p class="tp-preloader-subtitle" style="font-family: 'Hanken Grotesk', sans-serif;">Cargando</p>
            </div>
         </div>
      </div>
   </div>
   <!-- pre loader area end -->

   <!-- back to top start -->
   <div class="back-to-top-wrapper">
      <button id="back_to_top" type="button" class="back-to-top-btn">
         <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11 6L6 1L1 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
               stroke-linejoin="round" />
         </svg>
      </button>
   </div>
   <!-- back to top end -->

   <!-- offcanvas area start -->
   <div class="offcanvas__area offcanvas__radius">
      <div class="offcanvas__wrapper">
         <div class="offcanvas__close">
            <button class="offcanvas__close-btn offcanvas-close-btn">
               <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M11 1L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                     stroke-linejoin="round" />
                  <path d="M1 1L11 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                     stroke-linejoin="round" />
               </svg>
            </button>
         </div>
         <div class="offcanvas__content">
            <div class="offcanvas__top mb-70 d-flex justify-content-between align-items-center">
               <div class="offcanvas__logo logo">
                  <a href="index.html" class="d-flex align-items-center">
                     <img src="assets/img/logo/sbavecaLogo.png" alt="SBAVECA" style="max-height: 75px;">
                     <span
                        style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: var(--tp-theme-primary); margin-left: 10px;">SBAVECA</span>
                  </a>
               </div>
            </div>
            <div class="offcanvas__category pb-40">
               <button class="tp-offcanvas-category-toggle">
                  <i class="fa-solid fa-bars"></i>
                  All Categories
               </button>
               <div class="tp-category-mobile-menu">

               </div>
            </div>
            <div class="tp-main-menu-mobile fix d-lg-none mb-40"></div>

            <div class="offcanvas__contact align-items-center d-none">
               <div class="offcanvas__contact-icon mr-20">
                  <span>
                     <img src="assets/img/icon/contact.png" alt="">
                  </span>
               </div>
               <div class="offcanvas__contact-content">
                  <h3 class="offcanvas__contact-title">
                     <a href="tel:3705020691">3705020691</a>
                  </h3>
               </div>
            </div>
            <div class="offcanvas__btn">
               <a href="contact.html" class="tp-btn-2 tp-btn-border-2">Contacto</a>
            </div>
         </div>
         <div class="offcanvas__bottom">
            <div class="offcanvas__footer d-flex align-items-center justify-content-between">
               <div class="offcanvas__currency-wrapper currency">
                  <span class="offcanvas__currency-selected-currency tp-currency-toggle"
                     id="tp-offcanvas-currency-toggle">Currency : USD</span>
                  <ul class="offcanvas__currency-list tp-currency-list">
                     <li>ARS</li>
                     <li>ERU</li>
                     <li>BDT </li>
                     <li>INR</li>
                  </ul>
               </div>
               <div class="offcanvas__select language">
                  <div class="offcanvas__lang d-flex align-items-center justify-content-md-end">
                     <div class="offcanvas__lang-img mr-15">
                        <img src="assets/img/icon/language-flag.png" alt="">
                     </div>
                     <div class="offcanvas__lang-wrapper">
                        <span class="offcanvas__lang-selected-lang tp-lang-toggle"
                           id="tp-offcanvas-lang-toggle">Español</span>
                        <ul class="offcanvas__lang-list tp-lang-list">
                           <li>Spanish</li>
                           <li>Portugese</li>
                           <li>American</li>
                           <li>Canada</li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="body-overlay"></div>
   <!-- offcanvas area end -->

   <!-- mobile menu area start -->
   <div id="tp-bottom-menu-sticky" class="tp-mobile-menu d-lg-none">
      <div class="container">
         <div class="row row-cols-5">
            <div class="col">
               <div class="tp-mobile-item text-center">
                  <a href="shop.php" class="tp-mobile-item-btn">
                     <i class="flaticon-store"></i>
                     <span>Store</span>
                  </a>
               </div>
            </div>
            <div class="col">
               <div class="tp-mobile-item text-center">
                  <button class="tp-mobile-item-btn tp-search-open-btn">
                     <i class="flaticon-search-1"></i>
                     <span>Buscar</span>
                  </button>
               </div>
            </div>
            <div class="col">
               <div class="tp-mobile-item text-center">
                  <a href="wishlist.html" class="tp-mobile-item-btn">
                     <i class="flaticon-love"></i>
                     <span>Wishlist</span>
                  </a>
               </div>
            </div>
            <div class="col">
               <div class="tp-mobile-item text-center">
                  <a href="profile.php" class="tp-mobile-item-btn">
                     <i class="flaticon-user"></i>
                     <span>Account</span>
                  </a>
               </div>
            </div>
            <div class="col">
               <div class="tp-mobile-item text-center">
                  <button class="tp-mobile-item-btn tp-offcanvas-open-btn">
                     <i class="flaticon-menu-1"></i>
                     <span>Menu</span>
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- mobile menu area end -->

   <!-- search area start -->
   <section class="tp-search-area">
      <div class="container">
         <div class="row">
            <div class="col-xl-12">
               <div class="tp-search-form">
                  <div class="tp-search-close text-center mb-20">
                     <button class="tp-search-close-btn tp-search-close-btn"></button>
                  </div>
                  <form action="#">
                     <div class="tp-search-input mb-10">
                        <input type="text" placeholder="Search for product...">
                        <button type="submit"><i class="flaticon-search-1"></i></button>
                     </div>
                     <div class="tp-search-category">
                        <span>Search by : </span>
                        <a href="#">Men, </a>
                        <a href="#">Women, </a>
                        <a href="#">Children, </a>
                        <a href="#">Shirt, </a>
                        <a href="#">Demin</a>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </section>
   <!-- search area end -->

   <!-- cart mini area start -->
   <div class="cartmini__area tp-all-font-roboto">
      <div class="cartmini__wrapper d-flex justify-content-between flex-column">
         <div class="cartmini__top-wrapper">
            <div class="cartmini__top p-relative">
               <div class="cartmini__top-title">
                  <h4>Carrito de Compras</h4>
               </div>
               <div class="cartmini__close">
                  <button type="button" class="cartmini__close-btn cartmini-close-btn"><i
                        class="fal fa-times"></i></button>
               </div>
            </div>
            <div class="cartmini__shipping">
               <p> Envío Gratis en pedidos mayores a <span>$30.000</span></p>
               <div class="progress">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                     data-width="70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
            </div>
            <div class="cartmini__widget">
               <div class="cartmini__widget-item">
                  <div class="cartmini__thumb">
                     <a href="product-details.html">
                        <img src="assets/img/sbaveca/torta_rogel.png" alt="Torta Rogel">
                     </a>
                  </div>
                  <div class="cartmini__content">
                     <h5 class="cartmini__title"><a href="product-details.html">Torta Rogel Suprema</a></h5>
                     <div class="cartmini__price-wrapper">
                        <span class="cartmini__price">$28.500</span>
                        <span class="cartmini__quantity">x2</span>
                     </div>
                  </div>
                  <a href="#" class="cartmini__del"><i class="fa-regular fa-xmark"></i></a>
               </div>
            </div>
            <!-- for wp -->
            <!-- if no item in cart -->
            <div class="cartmini__empty text-center d-none">
               <img src="assets/img/product/cartmini/empty-cart.png" alt="">
               <p>Tu carrito está vacío</p>
               <a href="shop.php" class="tp-btn">Ver el Menú</a>
            </div>
         </div>
         <div class="cartmini__finalizar compra">
            <div class="cartmini__finalizar compra-title mb-30">
               <h4>Subtotal:</h4>
               <span>$57.000</span>
            </div>
            <div class="cartmini__finalizar compra-btn">
               <a href="cart.html" class="tp-btn mb-10 w-100"> ver carrito</a>
               <a href="finalizar compra.html" class="tp-btn tp-btn-border w-100"> finalizar compra</a>
            </div>
         </div>
      </div>
   </div>
   <!-- cart mini area end -->

   <!-- header area start -->
   <header>
      <div class="tp-header-area p-relative z-index-11">
         <!-- header top start  -->
         <div class="tp-header-top black-bg p-relative z-index-1 d-none d-md-block">
            <div class="container">
               <div class="row align-items-center">
                  <div class="col-md-6">
                     <div class="tp-header-welcome d-flex align-items-center">
                        <span>
                           <svg width="22" height="19" viewBox="0 0 22 19" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path d="M14.6364 1H1V12.8182H14.6364V1Z" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" />
                              <path d="M14.6364 5.54545H18.2727L21 8.27273V12.8182H14.6364V5.54545Z"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" />
                              <path
                                 d="M5.0909 17.3636C6.3461 17.3636 7.36363 16.3461 7.36363 15.0909C7.36363 13.8357 6.3461 12.8182 5.0909 12.8182C3.83571 12.8182 2.81818 13.8357 2.81818 15.0909C2.81818 16.3461 3.83571 17.3636 5.0909 17.3636Z"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" />
                              <path
                                 d="M16.9091 17.3636C18.1643 17.3636 19.1818 16.3461 19.1818 15.0909C19.1818 13.8357 18.1643 12.8182 16.9091 12.8182C15.6539 12.8182 14.6364 13.8357 14.6364 15.0909C14.6364 16.3461 15.6539 17.3636 16.9091 17.3636Z"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" />
                           </svg>
                        </span>
                        <p>SBAVECA tu lugar del buen comer</p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="tp-header-top-right d-flex align-items-center justify-content-end">
                        <div class="tp-header-top-menu d-flex align-items-center justify-content-end">

                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- header main start -->
         <div class="tp-header-main tp-header-sticky">
            <div class="container">
               <div class="row align-items-center">
                  <div class="col-xl-2 col-lg-2 col-md-4 col-6">
                     <div class="logo">
                        <a href="index.html" class="d-flex align-items-center">
                           <img src="assets/img/logo/sbavecaLogo.png" alt="SBAVECA" style="max-height: 75px;">
                           <span
                              style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: var(--tp-theme-primary); margin-left: 10px;">SBAVECA</span>
                        </a>
                     </div>
                  </div>
                  <div class="col-xl-6 col-lg-7 d-none d-lg-block">
                     <div class="header-offer-text d-flex align-items-center justify-content-center h-100"
                        style="padding: 10px;">
                        <span
                           style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: var(--tp-theme-primary); background: rgba(234, 106, 18, 0.1); padding: 8px 16px; border-radius: 30px; border: 1px dashed var(--tp-theme-primary);">
                           🎉 ¡Oferta de la semana! 45% o 50% de descuento🎉
                        </span>
                     </div>
                  </div>
                  <div class="col-xl-4 col-lg-3 col-md-8 col-6">
                     <div class="tp-header-main-right d-flex align-items-center justify-content-end">
                        <div class="tp-header-login d-none d-lg-block">
                           <a href="profile.php" class="d-flex align-items-center">
                              <div class="tp-header-login-icon">
                                 <span>
                                    <svg width="17" height="21" viewBox="0 0 17 21" fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                       <circle cx="8.57894" cy="5.77803" r="4.77803" stroke="currentColor"
                                          stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                       <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M1.00002 17.2014C0.998732 16.8655 1.07385 16.5337 1.2197 16.2311C1.67736 15.3158 2.96798 14.8307 4.03892 14.611C4.81128 14.4462 5.59431 14.336 6.38217 14.2815C7.84084 14.1533 9.30793 14.1533 10.7666 14.2815C11.5544 14.3367 12.3374 14.4468 13.1099 14.611C14.1808 14.8307 15.4714 15.27 15.9291 16.2311C16.2224 16.8479 16.2224 17.564 15.9291 18.1808C15.4714 19.1419 14.1808 19.5812 13.1099 19.7918C12.3384 19.9634 11.5551 20.0766 10.7666 20.1304C9.57937 20.2311 8.38659 20.2494 7.19681 20.1854C6.92221 20.1854 6.65677 20.1854 6.38217 20.1304C5.59663 20.0773 4.81632 19.9641 4.04807 19.7918C2.96798 19.5812 1.68652 19.1419 1.2197 18.1808C1.0746 17.8747 0.999552 17.5401 1.00002 17.2014Z"
                                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                          stroke-linejoin="round" />
                                    </svg>
                                 </span>
                              </div>
                              <div class="tp-header-login-content d-none d-xl-block">
                                 <span>Hola, Iniciar Sesión</span>
                                 <h5 class="tp-header-login-title">Tu Cuenta</h5>
                              </div>
                           </a>
                        </div>
                        <div class="tp-header-action d-flex align-items-center ml-50">


                           <div class="tp-header-action-item d-lg-none">
                              <button type="button" class="tp-header-action-btn tp-offcanvas-open-btn">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="30" height="16" viewBox="0 0 30 16">
                                    <rect x="10" width="20" height="2" fill="currentColor" />
                                    <rect x="5" y="7" width="25" height="2" fill="currentColor" />
                                    <rect x="10" y="14" width="20" height="2" fill="currentColor" />
                                 </svg>
                              </button>
                           </div>

                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- header bottom start -->
         <div class="tp-header-bottom tp-header-bottom-border d-none d-lg-block">
            <div class="container">
               <div class="tp-mega-menu-wrapper p-relative">
                  <div class="row align-items-center">
                     <div class="col-xl-3 col-lg-3">
                        <!-- Categorías Verticales Eliminadas -->
                     </div>
                     <div class="col-xl-6 col-lg-6">
                        <div class="main-menu menu-style-1">
                           <nav class="tp-main-menu-content">
                              <style>
                                 .menu-link-animate {
                                    transition: all 0.3s ease;
                                    display: inline-block;
                                 }

                                 .menu-link-animate:hover {
                                    transform: translateY(-3px) scale(1.05);
                                    color: #ff5e14 !important;
                                 }
                              </style>
                              <ul style="display: flex; list-style: none; padding: 0;">
                                 <li><a href="index.html" class="menu-link-animate"
                                       style="font-size: 18px; margin-right: 40px;">Inicio</a></li>
                                 <li><a href="shop.php" class="menu-link-animate"
                                       style="font-size: 18px; margin-right: 40px;">Tienda</a></li>
                                 <li><a href="contact.html" class="menu-link-animate"
                                       style="font-size: 18px;">Contacto</a></li>
                              </ul>
                           </nav>
                        </div>
                     </div>
                     <div class="col-xl-3 col-lg-3">
                        <div class="tp-header-contact d-flex align-items-center justify-content-end">
                           <div class="tp-header-contact-icon">
                              <span>
                                 <svg width="21" height="20" viewBox="0 0 21 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                       d="M1.96977 3.24859C2.26945 2.75144 3.92158 0.946726 5.09889 1.00121C5.45111 1.03137 5.76246 1.24346 6.01544 1.49057H6.01641C6.59631 2.05874 8.26011 4.203 8.35352 4.65442C8.58411 5.76158 7.26378 6.39979 7.66756 7.5157C8.69698 10.0345 10.4707 11.8081 12.9908 12.8365C14.1058 13.2412 14.7441 11.9219 15.8513 12.1515C16.3028 12.2459 18.4482 13.9086 19.0155 14.4894V14.4894C19.2616 14.7414 19.4757 15.0537 19.5049 15.4059C19.5487 16.6463 17.6319 18.3207 17.2583 18.5347C16.3767 19.1661 15.2267 19.1544 13.8246 18.5026C9.91224 16.8749 3.65985 10.7408 2.00188 6.68096C1.3675 5.2868 1.32469 4.12906 1.96977 3.24859Z"
                                       stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                       stroke-linejoin="round" />
                                    <path d="M12.936 1.23685C16.4432 1.62622 19.2124 4.39253 19.6065 7.89874"
                                       stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                       stroke-linejoin="round" />
                                    <path d="M12.936 4.59337C14.6129 4.92021 15.9231 6.23042 16.2499 7.90726"
                                       stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                       stroke-linejoin="round" />
                                 </svg>
                              </span>
                           </div>
                           <div class="tp-header-contact-content">
                              <h5>Atención:</h5>
                              <p><a href="tel:3705020691">3705020691</a></p>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </header>
   <!-- header area end -->

   <div id="header-sticky-2" class="tp-header-sticky-area">
      <div class="container">
         <div class="tp-mega-menu-wrapper p-relative">
            <div class="row align-items-center">
               <div class="col-xl-3 col-lg-3 col-md-3 col-6">
                  <div class="logo">
                     <a href="index.html" class="d-flex align-items-center">
                        <img src="assets/img/logo/sbavecaLogo.png" alt="SBAVECA" style="max-height: 75px;">
                        <span
                           style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: var(--tp-theme-primary); margin-left: 10px;">SBAVECA</span>
                     </a>
                  </div>
               </div>
               <div class="col-xl-6 col-lg-6 col-md-6 d-none d-md-block">
                  <div class="tp-header-sticky-menu main-menu menu-style-1">
                     <nav id="mobile-menu">
                        <ul>
                           <li><a href="index.html">Inicio</a></li>
                           <li>
                              <a href="shop.php">Tienda</a>
                           </li>
                           <li><a href="contact.html">Contacto</a></li>
                        </ul>
                     </nav>
                  </div>
               </div>
               <div class="col-xl-3 col-lg-3 col-md-3 col-6">
                  <div class="tp-header-action d-flex align-items-center justify-content-end ml-50">


                     <div class="tp-header-action-item d-lg-none">
                        <button type="button" class="tp-header-action-btn tp-offcanvas-open-btn">
                           <svg xmlns="http://www.w3.org/2000/svg" width="30" height="16" viewBox="0 0 30 16">
                              <rect x="10" width="20" height="2" fill="currentColor" />
                              <rect x="5" y="7" width="25" height="2" fill="currentColor" />
                              <rect x="10" y="14" width="20" height="2" fill="currentColor" />
                           </svg>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>


   
   <main>
      <!-- profile area start -->
      <section class="profile__area pt-120 pb-120">
         <div class="container">
            <div class="profile__inner p-relative">
               <div class="profile__shape">
                  <img class="profile__shape-1" src="assets/img/login/laptop.png" alt="" >
                  <img class="profile__shape-2" src="assets/img/login/man.png" alt="" >
                  <img class="profile__shape-3" src="assets/img/login/shape-1.png" alt="" >
                  <img class="profile__shape-4" src="assets/img/login/shape-2.png" alt="" >
                  <img class="profile__shape-5" src="assets/img/login/shape-3.png" alt="" >
                  <img class="profile__shape-6" src="assets/img/login/shape-4.png" alt="" >
               </div>
               <div class="row">
                  <div class="col-xxl-4 col-lg-4">
                     <div class="profile__tab mr-40">
                        <nav>
                           <div class="nav nav-tabs tp-tab-menu flex-column" id="profile-tab" role="tablist">
                              <button class="nav-link active" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="true"><span><i class="fa-regular fa-user"></i></span> Perfil</button>
                              <span id="marker-vertical" class="tp-tab-line d-none d-sm-inline-block"></span>
                           </div>
                        </nav>
                     </div>
                  </div>
                  <div class="col-xxl-8 col-lg-8">
                     <div class="profile__tab-content">
                        <div class="tab-content" id="profile-tabContent">
                           <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                              <div class="profile__main">
                                 <div class="profile__main-top pb-40">
                                    <div class="row align-items-center">
                                       <div class="col-md-6">
                                          <div class="profile__main-inner d-flex flex-wrap align-items-center">
                                             <div class="profile__main-thumb">
                                                <img src="assets/img/users/user-10.jpg" alt="Usuario" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                                             </div>
                                             <div class="profile__main-content">
                                                <h4 class="profile__main-title">¡Hola, <?= htmlspecialchars($nombreUsu) ?>!</h4>
                                                <p><?= htmlspecialchars($correoUsu) ?></p>
                                                <p><?= htmlspecialchars($telefonoCli) ?></p>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-6">
                                          <div class="profile__main-logout text-sm-end">
                                             <a href="logout.php" class="tp-logout-btn">Cerrar Sesión</a>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="profile__main-info">
                                    <div class="row gx-3">
                                       <div class="col-md-4 col-sm-6">
                                          <div class="profile__main-info-item text-center p-4 rounded" style="border: 1px solid #eaeaef;">
                                             <div class="profile__main-info-icon mb-15">
                                                <span>
                                                   <i class="fa-light fa-basket-shopping" style="font-size: 32px; color: var(--tp-theme-primary);"></i>
                                                </span>
                                             </div>
                                             <h4 class="profile__main-info-title mb-2">12</h4>
                                             <p class="mb-0" style="font-size: 14px;">Pedidos Totales</p>
                                          </div>
                                       </div>
                                       <div class="col-md-4 col-sm-6">
                                          <div class="profile__main-info-item text-center p-4 rounded" style="border: 1px solid #eaeaef;">
                                             <div class="profile__main-info-icon mb-15">
                                                <span>
                                                   <i class="fa-light fa-heart" style="font-size: 32px; color: var(--tp-theme-primary);"></i>
                                                </span>
                                             </div>
                                             <h4 class="profile__main-info-title mb-2">4</h4>
                                             <p class="mb-0" style="font-size: 14px;">Favoritos</p>
                                          </div>
                                       </div>
                                       <div class="col-md-4 col-sm-6">
                                          <div class="profile__main-info-item text-center p-4 rounded" style="border: 1px solid #eaeaef;">
                                             <div class="profile__main-info-icon mb-15">
                                                <span>
                                                   <i class="fa-light fa-star" style="font-size: 32px; color: var(--tp-theme-primary);"></i>
                                                </span>
                                             </div>
                                             <h4 class="profile__main-info-title mb-2">350</h4>
                                             <p class="mb-0" style="font-size: 14px;">Puntos de Recompensa</p>
                                          </div>
                                       </div>
                                    </div>
                                    
                                    <div class="mt-50">
                                       <h4 class="profile__main-info-title mb-20" style="font-family: 'Playfair Display', serif; color: var(--tp-theme-primary);">Últimos Pedidos</h4>
                                       <div class="table-responsive">
                                          <table class="table table-bordered text-center align-middle">
                                             <thead style="background-color: var(--sbaveca-surface);">
                                                <tr>
                                                   <th scope="col" style="font-weight: 600; color: var(--tp-theme-primary);">N° Pedido</th>
                                                   <th scope="col" style="font-weight: 600; color: var(--tp-theme-primary);">Fecha</th>
                                                   <th scope="col" style="font-weight: 600; color: var(--tp-theme-primary);">Estado</th>
                                                   <th scope="col" style="font-weight: 600; color: var(--tp-theme-primary);">Total</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                                <tr>
                                                   <th scope="row" style="color: var(--tp-theme-primary);">#ORD-0452</th>
                                                   <td>15 Jun, 2026</td>
                                                   <td><span class="badge" style="background-color: #28a745; font-weight: 500;">Entregado</span></td>
                                                   <td style="font-weight: 600;">$12.500</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row" style="color: var(--tp-theme-primary);">#ORD-0321</th>
                                                   <td>02 Jun, 2026</td>
                                                   <td><span class="badge" style="background-color: #28a745; font-weight: 500;">Entregado</span></td>
                                                   <td style="font-weight: 600;">$8.200</td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- profile area end -->
   </main>

   <!-- footer area start -->
   <footer>
      <div class="tp-footer-area" style="background-color: var(--tp-theme-primary); color: #ffffff;">
         <div class="tp-footer-top pt-95 pb-40">
            <div class="container">
               <div class="row">
                  <div class="col-xl-4 col-lg-3 col-md-4 col-sm-6">
                     <div class="tp-footer-widget footer-col-1 mb-50">
                        <div class="tp-footer-widget-content">
                           <div class="tp-footer-logo">
                              <a href="index.html">
                                 <img src="assets/img/logo/sbavecaLogo.png" alt="SBAVECA" style="max-height: 60px;">
                              </a>
                           </div>
                           <p class="tp-footer-desc"
                              style="font-family: 'Poppins', sans-serif; margin-top: 15px; color: #ffffff;">
                              Pastelería artesanal y rotisería premium. Calidad y sabor en cada receta, elaboradas
                              diariamente para deleitar tu paladar.</p>

                        </div>
                     </div>
                  </div>
                  <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
                     <div class="tp-footer-widget footer-col-2 mb-50">
                        <h4 class="tp-footer-widget-title" style="color: #ffffff;">Sobre el Proyecto</h4>
                        <div class="tp-footer-widget-content">
                           <p style="font-size: 14px; color: #ffffff; margin-bottom: 10px;"><b
                                 style="color: #ffffff;">SBAVECA</b>
                              es un Sistema de Gestión Gastronómica diseñado para profesionalizar pequeños y medianos
                              emprendimientos.</p>
                           <ul>
                              <li><a href="#" style="pointer-events: none; color: #ffffff;"><b>Equipo de
                                       Desarrollo:</b></a></li>
                              <li><a href="#" style="pointer-events: none; padding-left: 10px; color: #ffffff;">• Bordon
                                    Sbardella, L.</a></li>
                              <li><a href="#" style="pointer-events: none; padding-left: 10px; color: #ffffff;">• Vega,
                                    Franco A. </a></li>
                              <li><a href="#" style="pointer-events: none; padding-left: 10px; color: #ffffff;">•
                                    Cáceres, Facundo M.</a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                     <div class="tp-footer-widget footer-col-3 mb-50">
                        <h4 class="tp-footer-widget-title" style="color: #ffffff;">Información</h4>
                        <div class="tp-footer-widget-content">
                           <ul>
                              <li><a href="#" style="color: #ffffff;">Nuestra Historia</a></li>
                              <li><a href="#" style="color: #ffffff;">Trabajá con Nosotros</a></li>
                              <li><a href="#" style="color: #ffffff;">Política de Privacidad</a></li>
                              <li><a href="#" style="color: #ffffff;">Términos y Condiciones</a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
                     <div class="tp-footer-widget footer-col-4 mb-50">
                        <h4 class="tp-footer-widget-title" style="color: #ffffff;">Contacto / WhatsApp</h4>
                        <div class="tp-footer-widget-content">
                           <div class="tp-footer-talk mb-20">
                              <span style="color: #ffffff;">¿Tenés preguntas? Llamanos</span>
                              <h4><a href="tel:670-413-90-762" style="color: #ffffff;">[3705020691]</a></h4>
                           </div>
                           <div class="tp-footer-contact">
                              <div class="tp-footer-contact-item d-flex align-items-start">
                                 <div class="tp-footer-contact-icon">
                                    <span style="color: #ffffff;">
                                       <svg width="18" height="16" viewBox="0 0 18 16" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path
                                             d="M1 5C1 2.2 2.6 1 5 1H13C15.4 1 17 2.2 17 5V10.6C17 13.4 15.4 14.6 13 14.6H5"
                                             stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"
                                             stroke-linecap="round" stroke-linejoin="round" />
                                          <path
                                             d="M13 5.40039L10.496 7.40039C9.672 8.05639 8.32 8.05639 7.496 7.40039L5 5.40039"
                                             stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"
                                             stroke-linecap="round" stroke-linejoin="round" />
                                          <path d="M1 11.4004H5.8" stroke="currentColor" stroke-width="1.5"
                                             stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                          <path d="M1 8.19922H3.4" stroke="currentColor" stroke-width="1.5"
                                             stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                       </svg>
                                    </span>
                                 </div>
                                 <div class="tp-footer-contact-content">
                                    <p><a href="mailto:sbaveca@gmail.com" style="color: #ffffff;">sbaveca@gmail.com</a>
                                    </p>
                                 </div>
                              </div>

                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="tp-footer-bottom">
            <div class="container">
               <div class="tp-footer-bottom-wrapper">
                  <div class="row align-items-center">
                     <div class="col-md-6">
                        <div class="tp-footer-copyright">
                           <p style="color: #ffffff;">© 2026 SBAVECA. Todos los derechos reservados.</p>
                        </div>
                     </div>

                  </div>
               </div>
            </div>
         </div>
      </div>
   </footer>
   <!-- footer area end -->



   <!-- JS here -->
   <script src="assets/js/vendor/jquery.js"></script>
   <script src="assets/js/vendor/waypoints.js"></script>
   <script src="assets/js/bootstrap-bundle.js"></script>
   <script src="assets/js/meanmenu.js"></script>
   <script src="assets/js/swiper-bundle.js"></script>
   <script src="assets/js/slick.js"></script>
   <script src="assets/js/range-slider.js"></script>
   <script src="assets/js/magnific-popup.js"></script>
   <script src="assets/js/nice-select.js"></script>
   <script src="assets/js/purecounter.js"></script>
   <script src="assets/js/countdown.js"></script>
   <script src="assets/js/wow.js"></script>
   <script src="assets/js/isotope-pkgd.js"></script>
   <script src="assets/js/imagesloaded-pkgd.js"></script>
   <script src="assets/js/ajax-form.js"></script>
   <script src="assets/js/main.js"></script>
</body>

</html>
