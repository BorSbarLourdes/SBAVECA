<?php
require_once 'db.php';

// Inicializar carrito básico
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (isset($_GET['add_cart']) && is_numeric($_GET['add_cart'])) {
    $prod_id = $_GET['add_cart'];
    if (isset($_SESSION['cart'][$prod_id])) {
        $_SESSION['cart'][$prod_id]++;
    } else {
        $_SESSION['cart'][$prod_id] = 1;
    }
    // Evitar que la recarga vuelva a agregar
    header("Location: shop.php");
    exit();
}

// Obtener todas las categorías activas
$stmtCat = $pdo->query("SELECT idCat, nombreCat FROM categoria WHERE estadoCat = 1");
$categorias = $stmtCat->fetchAll();

// Filtros
$where = "WHERE p.estadoProd = 'Activo'";
$params = [];

// Filtro Categoría
if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
    $where .= " AND s.idCat = ?";
    $params[] = $_GET['cat'];
}

// Filtro Búsqueda
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $where .= " AND p.nombreProd LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

// Filtro Precio
if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $where .= " AND p.precioVentaProd >= ?";
    $params[] = $_GET['min_price'];
}
if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $where .= " AND p.precioVentaProd <= ?";
    $params[] = $_GET['max_price'];
}

// Filtro Estado (Oferta / Stock)
if (isset($_GET['on_sale'])) {
    $where .= " AND p.enOfertaProd = 1";
}
if (isset($_GET['in_stock'])) {
    $where .= " AND p.stockActualProd > 0";
}

// Ordenamiento
$order = "ORDER BY p.idProducto DESC";
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'price_asc') {
        $order = "ORDER BY p.precioVentaProd ASC";
    } elseif ($_GET['sort'] == 'price_desc') {
        $order = "ORDER BY p.precioVentaProd DESC";
    } elseif ($_GET['sort'] == 'name_asc') {
        $order = "ORDER BY p.nombreProd ASC";
    }
}

// Obtener productos activos
$sqlCount = "SELECT COUNT(p.idProducto) 
        FROM producto p 
        LEFT JOIN subcategoria s ON p.IdSubCat = s.idSubCat
        LEFT JOIN categoria c ON s.idCat = c.idCat
        $where";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();

$limit = 12; // 12 productos por página
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$totalPages = ceil($totalRows / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
$offset = ($page - 1) * $limit;

$sql = "SELECT p.*, c.nombreCat 
        FROM producto p 
        LEFT JOIN subcategoria s ON p.IdSubCat = s.idSubCat
        LEFT JOIN categoria c ON s.idCat = c.idCat
        $where 
        $order
        LIMIT $limit OFFSET $offset";
$stmtProd = $pdo->prepare($sql);
$stmtProd->execute($params);
$productos = $stmtProd->fetchAll();
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
                  <form action="shop.php" method="GET">
                     <div class="tp-search-input mb-10">
                        <input type="text" name="q" placeholder="Buscar productos..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit"><i class="flaticon-search-1"></i></button>
                     </div>
                     <div class="tp-search-category">
                        <span>Search by : </span>
                        <a href="#">Men, </a>
                        <a href="#">Women, </a>
                        <a href="#">Children, </a>
                        <a href="#">Shirt, </a>
                        <a href="#">Postres</a>
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

         <!-- breadcrumb area start -->
         <section class="breadcrumb__area include-bg pt-100 pb-50">
            <div class="container">
               <div class="row">
                  <div class="col-xxl-12">
                     <div class="breadcrumb__content p-relative z-index-1">
                        <h3 class="breadcrumb__title">Tienda</h3>
                        <div class="breadcrumb__list">
                           <span><a href="#">Inicio</a></span>
                           <span>Tienda</span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- breadcrumb area end -->

         <!-- shop area start -->
         <section class="tp-shop-area pb-120">
            <div class="container">
               <div class="row">
                  <div class="col-xl-3 col-lg-4">
                     <form method="GET" action="shop.php" id="shop-filters-form"><div class="tp-shop-sidebar mr-10">
<!-- Conservar búsqueda si existe -->
<?php if(isset($_GET["q"])): ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET["q"]); ?>"><?php endif; ?>
                        <!-- filter -->
                        <div class="tp-shop-widget mb-35">
                           <h3 class="tp-shop-widget-title no-border">Filtrar por Precio</h3>

                           <div class="tp-shop-widget-content">
                              <div class="tp-shop-widget-filter">
                                 <div id="slider-range" class="mb-10"></div>
                                 <div class="tp-shop-widget-filter-info d-flex align-items-center justify-content-between">
                                    <span class="input-range">
                                       <input type="text" id="amount" readonly>
                                    </span>
                                    <input type="hidden" name="min_price" id="min_price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : 5000; ?>">
                                    <input type="hidden" name="max_price" id="max_price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : 500000; ?>">
                                    <button class="tp-shop-widget-filter-btn" type="submit">Filtrar</button>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <!-- status -->
                        <div class="tp-shop-widget mb-50">
                           <h3 class="tp-shop-widget-title">Estado del Producto</h3>

                           <div class="tp-shop-widget-content">
                              <div class="tp-shop-widget-checkbox">
                                 <ul class="filter-items filter-checkbox">
                                    <li class="filter-item checkbox">
                                       <input id="on_sale" type="checkbox" name="on_sale" value="1" onchange="document.getElementById('shop-filters-form').submit();" <?php echo isset($_GET["on_sale"]) ? "checked" : ""; ?>>
                                       <label for="on_sale">En Oferta</label>
                                    </li>
                                 </ul><!-- .filter-items -->
                              </div>
                           </div>
                        </div>
                        <!-- categories -->
                        <div class="tp-shop-widget mb-50">
                           <h3 class="tp-shop-widget-title">Categorías</h3>

                           <div class="tp-shop-widget-content">
                              <div class="tp-shop-widget-categories">
                                 <ul>
                                    <?php foreach ($categorias as $cat): ?>
                                       <li>
                                          <?php $qs = $_GET; $qs["cat"] = $cat["idCat"]; ?><a href="shop.php?<?php echo http_build_query($qs); ?>" class="<?php echo (isset($_GET["cat"]) && $_GET["cat"] == $cat["idCat"]) ? "active text-danger" : ""; ?>">
                                             <?php echo htmlspecialchars($cat['nombreCat']); ?> 
                                          </a>
                                       </li>
                                    <?php endforeach; ?>
                                 </ul>
                              </div>
                           </div>
                        </div>

                        <!-- product rating -->
                        <div class="tp-shop-widget mb-50">
                           <h3 class="tp-shop-widget-title">Productos Mejor Valorados</h3>

                           <div class="tp-shop-widget-content">
                              <div class="tp-shop-widget-product">
                                 <div class="tp-shop-widget-product-item d-flex align-items-center">
                                    <div class="tp-shop-widget-product-thumb">
                                       <a href="product-details.html">
                                          <img src="assets/img/sbaveca/torta_rogel.png" alt="Torta Rogel Suprema con Dulce de Leche">
                                       </a>
                                    </div>
                                    <div class="tp-shop-widget-product-content">
                                       <div class="tp-shop-widget-product-rating-wrapper d-flex align-items-center">
                                          <div class="tp-shop-widget-product-rating">
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                          </div>
                                          <div class="tp-shop-widget-product-rating-number">
                                             <span>(4.2)</span>
                                          </div>
                                       </div>
                                       <h4 class="tp-shop-widget-product-title">
                                          <a href="product-details.html">Torta Rogel Suprema con Dulce de Leche</a>
                                       </h4>
                                       <div class="tp-shop-widget-product-price-wrapper">
                                          <span class="tp-shop-widget-product-price">$150.00</span>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="tp-shop-widget-product-item d-flex align-items-center">
                                    <div class="tp-shop-widget-product-thumb">
                                       <a href="product-details.html">
                                          <img src="assets/img/sbaveca/milanesa_napolitana.png" alt="Milanesa Napolitana Gigante con Fritas">
                                       </a>
                                    </div>
                                    <div class="tp-shop-widget-product-content">
                                       <div class="tp-shop-widget-product-rating-wrapper d-flex align-items-center">
                                          <div class="tp-shop-widget-product-rating">
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                          </div>
                                          <div class="tp-shop-widget-product-rating-number">
                                             <span>(4.5)</span>
                                          </div>
                                       </div>
                                       <h4 class="tp-shop-widget-product-title">
                                          <a href="product-details.html">Milanesa Napolitana Gigante con Fritas</a>
                                       </h4>
                                       <div class="tp-shop-widget-product-price-wrapper">
                                          <span class="tp-shop-widget-product-price">$120.00</span>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="tp-shop-widget-product-item d-flex align-items-center">
                                    <div class="tp-shop-widget-product-thumb">
                                       <a href="product-details.html">
                                          <img src="assets/img/sbaveca/pollo_asado.png" alt="Pizza Fugazzeta Rellena con Queso">
                                       </a>
                                    </div>
                                    <div class="tp-shop-widget-product-content">
                                       <div class="tp-shop-widget-product-rating-wrapper d-flex align-items-center">
                                          <div class="tp-shop-widget-product-rating">
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                          </div>
                                          <div class="tp-shop-widget-product-rating-number">
                                             <span>(3.5)</span>
                                          </div>
                                       </div>
                                       <h4 class="tp-shop-widget-product-title">
                                          <a href="product-details.html">Pizza Fugazzeta Rellena con Queso</a>
                                       </h4>
                                       <div class="tp-shop-widget-product-price-wrapper">
                                          <span class="tp-shop-widget-product-price">$99.00</span>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="tp-shop-widget-product-item d-flex align-items-center">
                                    <div class="tp-shop-widget-product-thumb">
                                       <a href="product-details.html">
                                          <img src="assets/img/sbaveca/torta_rogel.png" alt="Docena de Empanadas de Pollo y Jamón">
                                       </a>
                                    </div>
                                    <div class="tp-shop-widget-product-content">
                                       <div class="tp-shop-widget-product-rating-wrapper d-flex align-items-center">
                                          <div class="tp-shop-widget-product-rating">
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                             <span>
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M6 0L7.854 3.756L12 4.362L9 7.284L9.708 11.412L6 9.462L2.292 11.412L3 7.284L0 4.362L4.146 3.756L6 0Z" fill="currentColor"/>
                                                </svg>
                                             </span>
                                          </div>
                                          <div class="tp-shop-widget-product-rating-number">
                                             <span>(4.8)</span>
                                          </div>
                                       </div>
                                       <h4 class="tp-shop-widget-product-title">
                                          <a href="product-details.html">Docena de Empanadas de Pollo y Jamón</a>
                                       </h4>
                                       <div class="tp-shop-widget-product-price-wrapper">
                                          <span class="tp-shop-widget-product-price">$165.00</span>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>

                     </div>
                  </div>
                  <div class="col-xl-9 col-lg-8">
                     </form>
<div class="tp-shop-main-wrapper">
                        

                        <div class="tp-shop-items-wrapper tp-shop-item-primary">
                           <div class="tab-content" id="productTabContent">
                              <div class="tab-pane fade show active" id="grid-tab-pane" role="tabpanel" aria-labelledby="grid-tab" tabindex="0">
                                 <div class="row infinite-container">
                                    <?php if(empty($productos)): ?>
                                        <div class="col-12"><p>No se encontraron productos.</p></div>
                                    <?php else: ?>
                                        <?php foreach($productos as $prod): 
                                            $imagen = "assets/img/sbaveca/torta_rogel.png";
                                            if (!empty($prod['imagenNombreProd'])) {
                                                $imagen = "assets/img/sbaveca/" . $prod['imagenNombreProd'];
                                            } elseif (!empty($prod['imagenBlobProd'])) {
                                                $imagen = "data:image/jpeg;base64," . base64_encode($prod['imagenBlobProd']);
                                            }
                                        ?>
                                        <div class="col-xl-4 col-md-6 col-sm-6 infinite-item">
                                           <div class="tp-product-item-2 mb-40">
                                              <div class="tp-product-thumb-2 p-relative z-index-1 fix w-img">
                                                 <a href="#">
                                                    <img src="<?php echo $imagen; ?>" alt="<?php echo htmlspecialchars($prod['nombreProd']); ?>" style="height:250px; object-fit:cover;">
                                                 </a>
                                              </div>
                                              <div class="tp-product-content-2 pt-15">
                                                 <div class="tp-product-tag-2">
                                                    <a href="#"><?php echo htmlspecialchars($prod['nombreCat']); ?></a>
                                                 </div>
                                                 <h3 class="tp-product-title-2">
                                                    <a href="#"><?php echo htmlspecialchars($prod['nombreProd']); ?></a>
                                                 </h3>
                                                 <div class="tp-product-price-wrapper-2">
                                                    <?php if($prod['enOfertaProd'] && $prod['precioOfertaProd']): ?>
                                                        <span class="tp-product-price-2 new-price">$<?php echo number_format($prod['precioOfertaProd'], 2, ',', '.'); ?></span>
                                                        <span class="tp-product-price-2 old-price">$<?php echo number_format($prod['precioVentaProd'], 2, ',', '.'); ?></span>
                                                    <?php else: ?>
                                                        <span class="tp-product-price-2 new-price">$<?php echo number_format($prod['precioVentaProd'], 2, ',', '.'); ?></span>
                                                    <?php endif; ?>
                                                 </div>
                                              </div>
                                           </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                 </div>
                              </div>
<div class="tp-shop-pagination mt-20">
                           <div class="tp-pagination">
                              <nav>
                                 <ul>
                                    <?php if ($totalPages > 1): ?>
                                       <?php
                                       $queryString = $_GET;
                                       unset($queryString['page']);
                                       $baseUrl = 'shop.php?' . http_build_query($queryString) . (!empty($queryString) ? '&' : '') . 'page=';
                                       ?>
                                       <?php if ($page > 1): ?>
                                          <li>
                                             <a href="<?php echo $baseUrl . ($page - 1); ?>" class="tp-pagination-prev prev page-numbers">
                                                <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M1.00017 6.77879L14 6.77879" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                   <path d="M6.24316 11.9999L0.999899 6.77922L6.24316 1.55762" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                             </a>
                                          </li>
                                       <?php endif; ?>

                                       <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                          <li>
                                             <?php if ($i == $page): ?>
                                                <span class="current"><?php echo $i; ?></span>
                                             <?php else: ?>
                                                <a href="<?php echo $baseUrl . $i; ?>"><?php echo $i; ?></a>
                                             <?php endif; ?>
                                          </li>
                                       <?php endfor; ?>

                                       <?php if ($page < $totalPages): ?>
                                          <li>
                                             <a href="<?php echo $baseUrl . ($page + 1); ?>" class="next page-numbers">
                                                <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                   <path d="M13.9998 6.77883L1 6.77883" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                   <path d="M8.75684 1.55767L14.0001 6.7784L8.75684 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>                                     
                                             </a>
                                          </li>
                                       <?php endif; ?>
                                    <?php endif; ?>
                                 </ul>
                               </nav>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- shop area end -->

         <div class="modal fade tp-product-modal" id="producQuickViewModal" tabindex="-1" aria-labelledby="producQuickViewModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content">
                  <div class="tp-product-modal-content d-lg-flex align-items-start">
                     <button type="button" class="tp-product-modal-close-btn" data-bs-toggle="modal" data-bs-target="#producQuickViewModal"><i class="fa-regular fa-xmark"></i></button>
                     <div class="tp-product-details-thumb-wrapper tp-tab d-sm-flex">
                        <nav>
                           <div class="nav nav-tabs flex-sm-column " id="productDetailsNavThumb" role="tablist">
                              <button class="nav-link active" id="nav-1-tab" data-bs-toggle="tab" data-bs-target="#nav-1" type="button" role="tab" aria-controls="nav-1" aria-selected="true">
                                 <img src="assets/img/sbaveca/torta_rogel.png" alt="">
                              </button>
                              <button class="nav-link" id="nav-2-tab" data-bs-toggle="tab" data-bs-target="#nav-2" type="button" role="tab" aria-controls="nav-2" aria-selected="false">
                                 <img src="assets/img/sbaveca/lemon_pie.png" alt="">
                              </button>
                              <button class="nav-link" id="nav-3-tab" data-bs-toggle="tab" data-bs-target="#nav-3" type="button" role="tab" aria-controls="nav-3" aria-selected="false">
                                 <img src="assets/img/sbaveca/empanadas_carne.png" alt="">
                              </button>
                              <button class="nav-link" id="nav-4-tab" data-bs-toggle="tab" data-bs-target="#nav-4" type="button" role="tab" aria-controls="nav-4" aria-selected="false">
                                 <img src="assets/img/sbaveca/pizza_muzzarella.png" alt="">
                              </button>
                           </div>
                        </nav>
                        <div class="tab-content m-img" id="productDetailsNavContent">
                           <div class="tab-pane fade show active" id="nav-1" role="tabpanel" aria-labelledby="nav-1-tab" tabindex="0">
                              <div class="tp-product-details-nav-main-thumb">
                                 <img src="assets/img/sbaveca/torta_rogel.png" alt="">
                              </div>
                           </div>
                           <div class="tab-pane fade" id="nav-2" role="tabpanel" aria-labelledby="nav-2-tab" tabindex="0">
                              <div class="tp-product-details-nav-main-thumb">
                                 <img src="assets/img/sbaveca/lemon_pie.png" alt="">
                              </div>
                           </div>
                           <div class="tab-pane fade" id="nav-3" role="tabpanel" aria-labelledby="nav-3-tab" tabindex="0">
                              <div class="tp-product-details-nav-main-thumb">
                                 <img src="assets/img/sbaveca/empanadas_carne.png" alt="">
                              </div>
                           </div>
                           <div class="tab-pane fade" id="nav-4" role="tabpanel" aria-labelledby="nav-4-tab" tabindex="0">
                              <div class="tp-product-details-nav-main-thumb">
                                 <img src="assets/img/sbaveca/pizza_muzzarella.png" alt="">
                              </div>
                           </div>
                         </div>
                     </div>
                     <div class="tp-product-details-wrapper">
                        <div class="tp-product-details-category">
                           <span>Rostisería &amp; Pastelería</span>
                        </div>
                        <h3 class="tp-product-details-title">Samsung galaxy A8 tablet</h3>
   
                        <!-- inventory details -->
                        <div class="tp-product-details-inventory d-flex align-items-center mb-10">
                           <div class="tp-product-details-stock mb-10">
                              <span>En Stock</span>
                           </div>
                           <div class="tp-product-details-rating-wrapper d-flex align-items-center mb-10">
                              <div class="tp-product-details-rating">
                                 <span><i class="fa-solid fa-star"></i></span>
                                 <span><i class="fa-solid fa-star"></i></span>
                                 <span><i class="fa-solid fa-star"></i></span>
                                 <span><i class="fa-solid fa-star"></i></span>
                                 <span><i class="fa-solid fa-star"></i></span>
                              </div>
                              <div class="tp-product-details-reviews">
                                 <span>(36 Reviews)</span>
                              </div>
                           </div>
                        </div>
                        <p>Una delicia artesanal que a todos les encanta: Nuestra Torta Rogel Suprema cuenta con capas crocantes súper finas, rellenas del más rico dulce de leche y decorada con merengue italiano suave... <span>Ver más</span></p>
   
                        <!-- price -->
                        <div class="tp-product-details-price-wrapper mb-20">
                           <span class="tp-product-details-price old-price">$320.00</span>
                           <span class="tp-product-details-price new-price">$236.00</span>
                        </div>
   
                        <!-- variations -->
                        <div class="tp-product-details-variation">
                           <!-- single item -->
                           <div class="tp-product-details-variation-item">
                              <h4 class="tp-product-details-variation-title">Variación :</h4>
                              <div class="tp-product-details-variation-list">
                                 <button type="button" class="color tp-color-variation-btn" >
                                    <span data-bg-color="#F8B655"></span>
                                    <span class="tp-color-variation-tootltip">Amarillo</span>
                                 </button>
                                 <button type="button" class="color tp-color-variation-btn active" >
                                    <span data-bg-color="#CBCBCB"></span>
                                    <span class="tp-color-variation-tootltip">Gray</span>
                                 </button>
                                 <button type="button" class="color tp-color-variation-btn" >
                                    <span data-bg-color="#494E52"></span>
                                    <span class="tp-color-variation-tootltip">Black</span>
                                 </button>
                                 <button type="button" class="color tp-color-variation-btn" >
                                    <span data-bg-color="#B4505A"></span>
                                    <span class="tp-color-variation-tootltip">Brown</span>
                                 </button>
                              </div>
                           </div>
                        </div>
   
                        <!-- actions -->
                        <div class="tp-product-details-action-wrapper">
                           <h3 class="tp-product-details-action-title">Cantidad</h3>
                           <div class="tp-product-details-action-item-wrapper d-flex align-items-center">
                              <div class="tp-product-details-quantity">
                                 <div class="tp-product-quantity mb-15 mr-15">
                                    <span class="tp-cart-minus">
                                       <svg width="11" height="2" viewBox="0 0 11 2" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M1 1H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                       </svg>                                                            
                                    </span>
                                    <input class="tp-cart-input" type="text" value="1">
                                    <span class="tp-cart-plus">
                                       <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M1 6H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                          <path d="M5.5 10.5V1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                       </svg>
                                    </span>
                                 </div>
                              </div>
                              <div class="tp-product-details-add-to-cart mb-15 w-100">
                                 <button class="tp-product-details-add-to-cart-btn w-100">Agregar al Carrito</button>
                              </div>
                           </div>
                           <button class="tp-product-details-buy-now-btn w-100">Comprar Ahora</button>
                        </div>
                        <div class="tp-product-details-action-sm">
                           <button type="button" class="tp-product-details-action-sm-btn">
                              <svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M1 3.16431H10.8622C12.0451 3.16431 12.9999 4.08839 12.9999 5.23315V7.52268" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                 <path d="M3.25177 0.985168L1 3.16433L3.25177 5.34354" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                 <path d="M12.9999 12.5983H3.13775C1.95486 12.5983 1 11.6742 1 10.5295V8.23993" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                 <path d="M10.748 14.7774L12.9998 12.5983L10.748 10.4191" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                              Compare
                           </button>
                           <button type="button" class="tp-product-details-action-sm-btn">
                              <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                 <path fill-rule="evenodd" clip-rule="evenodd" d="M2.33541 7.54172C3.36263 10.6766 7.42094 13.2113 8.49945 13.8387C9.58162 13.2048 13.6692 10.6421 14.6635 7.5446C15.3163 5.54239 14.7104 3.00621 12.3028 2.24514C11.1364 1.8779 9.77578 2.1014 8.83648 2.81432C8.64012 2.96237 8.36757 2.96524 8.16974 2.81863C7.17476 2.08487 5.87499 1.86999 4.69024 2.24514C2.28632 3.00549 1.68259 5.54167 2.33541 7.54172ZM8.50115 15C8.4103 15 8.32018 14.9784 8.23812 14.9346C8.00879 14.8117 2.60674 11.891 1.29011 7.87081C1.28938 7.87081 1.28938 7.8701 1.28938 7.8701C0.462913 5.33895 1.38316 2.15812 4.35418 1.21882C5.7492 0.776121 7.26952 0.97088 8.49895 1.73195C9.69029 0.993159 11.2729 0.789057 12.6401 1.21882C15.614 2.15956 16.5372 5.33966 15.7115 7.8701C14.4373 11.8443 8.99571 14.8088 8.76492 14.9332C8.68286 14.9777 8.592 15 8.50115 15Z" fill="currentColor"/>
                                 <path d="M8.49945 13.8387L8.42402 13.9683L8.49971 14.0124L8.57526 13.9681L8.49945 13.8387ZM14.6635 7.5446L14.5209 7.4981L14.5207 7.49875L14.6635 7.5446ZM12.3028 2.24514L12.348 2.10211L12.3478 2.10206L12.3028 2.24514ZM8.83648 2.81432L8.92678 2.93409L8.92717 2.9338L8.83648 2.81432ZM8.16974 2.81863L8.25906 2.69812L8.25877 2.69791L8.16974 2.81863ZM4.69024 2.24514L4.73548 2.38815L4.73552 2.38814L4.69024 2.24514ZM8.23812 14.9346L8.16727 15.0668L8.16744 15.0669L8.23812 14.9346ZM1.29011 7.87081L1.43266 7.82413L1.39882 7.72081H1.29011V7.87081ZM1.28938 7.8701L1.43938 7.87009L1.43938 7.84623L1.43197 7.82354L1.28938 7.8701ZM4.35418 1.21882L4.3994 1.36184L4.39955 1.36179L4.35418 1.21882ZM8.49895 1.73195L8.42 1.85949L8.49902 1.90841L8.57801 1.85943L8.49895 1.73195ZM12.6401 1.21882L12.6853 1.0758L12.685 1.07572L12.6401 1.21882ZM15.7115 7.8701L15.5689 7.82356L15.5686 7.8243L15.7115 7.8701ZM8.76492 14.9332L8.69378 14.8011L8.69334 14.8013L8.76492 14.9332ZM2.19287 7.58843C2.71935 9.19514 4.01596 10.6345 5.30013 11.744C6.58766 12.8564 7.88057 13.6522 8.42402 13.9683L8.57487 13.709C8.03982 13.3978 6.76432 12.6125 5.49626 11.517C4.22484 10.4185 2.97868 9.02313 2.47795 7.49501L2.19287 7.58843ZM8.57526 13.9681C9.12037 13.6488 10.4214 12.8444 11.7125 11.729C12.9999 10.6167 14.2963 9.17932 14.8063 7.59044L14.5207 7.49875C14.0364 9.00733 12.7919 10.4 11.5164 11.502C10.2446 12.6008 8.9607 13.3947 8.42364 13.7093L8.57526 13.9681ZM14.8061 7.59109C15.1419 6.5613 15.1554 5.39131 14.7711 4.37633C14.3853 3.35729 13.5989 2.49754 12.348 2.10211L12.2576 2.38816C13.4143 2.75381 14.1347 3.54267 14.4905 4.48255C14.8479 5.42648 14.8379 6.52568 14.5209 7.4981L14.8061 7.59109ZM12.3478 2.10206C11.137 1.72085 9.72549 1.95125 8.7458 2.69484L8.92717 2.9338C9.82606 2.25155 11.1357 2.03494 12.2577 2.38821L12.3478 2.10206ZM8.74618 2.69455C8.60221 2.8031 8.40275 2.80462 8.25906 2.69812L8.08043 2.93915C8.33238 3.12587 8.67804 3.12163 8.92678 2.93409L8.74618 2.69455ZM8.25877 2.69791C7.225 1.93554 5.87527 1.71256 4.64496 2.10213L4.73552 2.38814C5.87471 2.02742 7.12452 2.2342 8.08071 2.93936L8.25877 2.69791ZM4.64501 2.10212C3.39586 2.49722 2.61099 3.35688 2.22622 4.37554C1.84299 5.39014 1.85704 6.55957 2.19281 7.58826L2.478 7.49518C2.16095 6.52382 2.15046 5.42513 2.50687 4.48154C2.86175 3.542 3.58071 2.7534 4.73548 2.38815L4.64501 2.10212ZM8.50115 14.85C8.43415 14.85 8.36841 14.8341 8.3088 14.8023L8.16744 15.0669C8.27195 15.1227 8.38645 15.15 8.50115 15.15V14.85ZM8.30897 14.8024C8.19831 14.7431 6.7996 13.9873 5.26616 12.7476C3.72872 11.5046 2.07716 9.79208 1.43266 7.82413L1.14756 7.9175C1.81968 9.96978 3.52747 11.7277 5.07755 12.9809C6.63162 14.2373 8.0486 15.0032 8.16727 15.0668L8.30897 14.8024ZM1.29011 7.72081C1.31557 7.72081 1.34468 7.72745 1.37175 7.74514C1.39802 7.76231 1.41394 7.78437 1.42309 7.8023C1.43191 7.81958 1.43557 7.8351 1.43727 7.84507C1.43817 7.8504 1.43869 7.85518 1.43898 7.85922C1.43913 7.86127 1.43923 7.8632 1.43929 7.865C1.43932 7.86591 1.43934 7.86678 1.43936 7.86763C1.43936 7.86805 1.43937 7.86847 1.43937 7.86888C1.43937 7.86909 1.43937 7.86929 1.43938 7.86949C1.43938 7.86959 1.43938 7.86969 1.43938 7.86979C1.43938 7.86984 1.43938 7.86992 1.43938 7.86994C1.43938 7.87002 1.43938 7.87009 1.28938 7.8701C1.13938 7.8701 1.13938 7.87017 1.13938 7.87025C1.13938 7.87027 1.13938 7.87035 1.13938 7.8704C1.13938 7.8705 1.13938 7.8706 1.13938 7.8707C1.13938 7.8709 1.13938 7.87111 1.13938 7.87131C1.13939 7.87173 1.13939 7.87214 1.1394 7.87257C1.13941 7.87342 1.13943 7.8743 1.13946 7.8752C1.13953 7.87701 1.13962 7.87896 1.13978 7.88103C1.14007 7.88512 1.14059 7.88995 1.14151 7.89535C1.14323 7.90545 1.14694 7.92115 1.15585 7.93861C1.16508 7.95672 1.18114 7.97896 1.20762 7.99626C1.2349 8.01409 1.26428 8.02081 1.29011 8.02081V7.72081ZM1.43197 7.82354C0.623164 5.34647 1.53102 2.26869 4.3994 1.36184L4.30896 1.0758C1.23531 2.04755 0.302663 5.33142 1.14679 7.91665L1.43197 7.82354ZM4.39955 1.36179C5.7527 0.932384 7.22762 1.12136 8.42 1.85949L8.57791 1.60441C7.31141 0.820401 5.74571 0.619858 4.30881 1.07585L4.39955 1.36179ZM8.57801 1.85943C9.73213 1.14371 11.2694 0.945205 12.5951 1.36192L12.685 1.07572C11.2763 0.632908 9.64845 0.842602 8.4199 1.60447L8.57801 1.85943ZM12.5948 1.36184C15.4664 2.27018 16.3769 5.34745 15.5689 7.82356L15.8541 7.91663C16.6975 5.33188 15.7617 2.04893 12.6853 1.07581L12.5948 1.36184ZM15.5686 7.8243C14.9453 9.76841 13.2952 11.4801 11.7526 12.7288C10.2142 13.974 8.80513 14.7411 8.69378 14.8011L8.83606 15.0652C8.9555 15.0009 10.3826 14.2236 11.9413 12.9619C13.4957 11.7037 15.2034 9.94602 15.8543 7.91589L15.5686 7.8243ZM8.69334 14.8013C8.6337 14.8337 8.56752 14.85 8.50115 14.85V15.15C8.61648 15.15 8.73201 15.1217 8.83649 15.065L8.69334 14.8013Z" fill="currentColor"/>
                                 <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8384 6.93209C12.5548 6.93209 12.3145 6.71865 12.2911 6.43693C12.2427 5.84618 11.8397 5.34743 11.266 5.1656C10.9766 5.07361 10.8184 4.76962 10.9114 4.48718C11.0059 4.20402 11.3129 4.05023 11.6031 4.13934C12.6017 4.45628 13.3014 5.32371 13.3872 6.34925C13.4113 6.64606 13.1864 6.90622 12.8838 6.92993C12.8684 6.93137 12.8538 6.93209 12.8384 6.93209Z" fill="currentColor"/>
                                 <path d="M12.8384 6.93209C12.5548 6.93209 12.3145 6.71865 12.2911 6.43693C12.2427 5.84618 11.8397 5.34743 11.266 5.1656C10.9766 5.07361 10.8184 4.76962 10.9114 4.48718C11.0059 4.20402 11.3129 4.05023 11.6031 4.13934C12.6017 4.45628 13.3014 5.32371 13.3872 6.34925C13.4113 6.64606 13.1864 6.90622 12.8838 6.92993C12.8684 6.93137 12.8538 6.93209 12.8384 6.93209" stroke="currentColor" stroke-width="0.3"/>
                              </svg>
                              Add Wishlist
                           </button>
                           <button type="button" class="tp-product-details-action-sm-btn">
                              <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M8.575 12.6927C8.775 12.6927 8.94375 12.6249 9.08125 12.4895C9.21875 12.354 9.2875 12.1878 9.2875 11.9907C9.2875 11.7937 9.21875 11.6275 9.08125 11.492C8.94375 11.3565 8.775 11.2888 8.575 11.2888C8.375 11.2888 8.20625 11.3565 8.06875 11.492C7.93125 11.6275 7.8625 11.7937 7.8625 11.9907C7.8625 12.1878 7.93125 12.354 8.06875 12.4895C8.20625 12.6249 8.375 12.6927 8.575 12.6927ZM8.55625 5.0638C8.98125 5.0638 9.325 5.17771 9.5875 5.40553C9.85 5.63335 9.98125 5.92582 9.98125 6.28294C9.98125 6.52924 9.90625 6.77245 9.75625 7.01258C9.60625 7.25272 9.3625 7.5144 9.025 7.79763C8.7 8.08087 8.44063 8.3795 8.24688 8.69352C8.05313 9.00754 7.95625 9.29385 7.95625 9.55246C7.95625 9.68792 8.00938 9.79567 8.11563 9.87572C8.22188 9.95576 8.34375 9.99578 8.48125 9.99578C8.63125 9.99578 8.75625 9.94653 8.85625 9.84801C8.95625 9.74949 9.01875 9.62635 9.04375 9.47857C9.08125 9.23228 9.16562 9.0137 9.29688 8.82282C9.42813 8.63195 9.63125 8.42568 9.90625 8.20402C10.2812 7.89615 10.5531 7.58829 10.7219 7.28042C10.8906 6.97256 10.975 6.62775 10.975 6.246C10.975 5.59333 10.7594 5.06996 10.3281 4.67589C9.89688 4.28183 9.325 4.0848 8.6125 4.0848C8.1375 4.0848 7.7 4.17716 7.3 4.36187C6.9 4.54659 6.56875 4.81751 6.30625 5.17463C6.20625 5.31009 6.16563 5.44863 6.18438 5.59025C6.20313 5.73187 6.2625 5.83962 6.3625 5.91351C6.5 6.01202 6.64688 6.04281 6.80313 6.00587C6.95937 5.96892 7.0875 5.88272 7.1875 5.74726C7.35 5.5256 7.54688 5.35627 7.77813 5.23929C8.00938 5.1223 8.26875 5.0638 8.55625 5.0638ZM8.5 15.7775C7.45 15.7775 6.46875 15.5897 5.55625 15.2141C4.64375 14.8385 3.85 14.3182 3.175 13.6532C2.5 12.9882 1.96875 12.2062 1.58125 11.3073C1.19375 10.4083 1 9.43547 1 8.38873C1 7.35431 1.19375 6.38762 1.58125 5.48866C1.96875 4.58969 2.5 3.80772 3.175 3.14273C3.85 2.47775 4.64375 1.95438 5.55625 1.57263C6.46875 1.19088 7.45 1 8.5 1C9.5375 1 10.5125 1.19088 11.425 1.57263C12.3375 1.95438 13.1313 2.47775 13.8063 3.14273C14.4813 3.80772 15.0156 4.58969 15.4094 5.48866C15.8031 6.38762 16 7.35431 16 8.38873C16 9.43547 15.8031 10.4083 15.4094 11.3073C15.0156 12.2062 14.4813 12.9882 13.8063 13.6532C13.1313 14.3182 12.3375 14.8385 11.425 15.2141C10.5125 15.5897 9.5375 15.7775 8.5 15.7775ZM8.5 14.6692C10.2625 14.6692 11.7656 14.0534 13.0094 12.822C14.2531 11.5905 14.875 10.1128 14.875 8.38873C14.875 6.6647 14.2531 5.18695 13.0094 3.95549C11.7656 2.72404 10.2625 2.10831 8.5 2.10831C6.7125 2.10831 5.20312 2.72404 3.97188 3.95549C2.74063 5.18695 2.125 6.6647 2.125 8.38873C2.125 10.1128 2.74063 11.5905 3.97188 12.822C5.20312 14.0534 6.7125 14.6692 8.5 14.6692Z" fill="currentColor" stroke="currentColor" stroke-width="0.3"/>
                              </svg>
                              Ask a question
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
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
   <script src="assets/js/main.js?v=2"></script>
   <script>
   // Re-inicializar el slider después de que main.js lo haya creado
   $(window).on('load', function() {
       var minP = parseInt($("#min_price").val()) || 5000;
       var maxP = parseInt($("#max_price").val()) || 500000;
       
       if ($("#slider-range").length) {
           // Destruir el viejo e inicializar uno nuevo para evitar conflictos con main.js
           $("#slider-range").slider("destroy");
           $("#slider-range").slider({
               range: true,
               min: 5000,
               max: 500000,
               step: 1000,
               values: [minP, maxP],
               slide: function (event, ui) {
                   $("#amount").val("$" + ui.values[0] + " - $" + ui.values[1]);
                   $("#min_price").val(ui.values[0]);
                   $("#max_price").val(ui.values[1]);
               }
           });
           $("#amount").val("$" + minP + " - $" + maxP);
       }
   });
   </script>
</body>

</html>
