<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>The Brain – Premium Luxury Brand Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('The-Brain-Logo.png') }}" width="32" height="32">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-soft: #eff4ff;
            --dark: #111827;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top left, #e0f2fe, #f9fafb 40%);
            color: var(--dark);
        }

        /* Navbar */
        .navbar {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.88) !important;
            border-bottom: 1px solid rgba(148, 163, 184, .25);
        }

        .navbar-brand img {
            height: 34px;
        }

        .nav-link {
            font-weight: 500;
            color: #4b5563 !important;
        }

        .nav-link.active {
            color: var(--primary) !important;
        }

        /* Search */
        .search-wrapper .form-control {
            border-left: 0;
            box-shadow: none;
        }

        .search-wrapper .input-group-text {
            border-right: 0;
            background: transparent;
        }

        /* Hero */
        /* .page-wrapper {
            max-width: 1200px;
        } */

        .hero-wrapper {
            margin-top: 88px;
        }

        .banner-card {
            border-radius: 24px;
            overflow: hidden;
            background: #0f172a;
            position: relative;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .35);
        }

        .banner-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .banner-badge {
            position: absolute;
            left: 16px;
            top: 16px;
            padding: .25rem .7rem;
            border-radius: 999px;
            font-size: .75rem;
            background: rgba(15, 23, 42, .72);
            color: #e5e7eb;
        }

        /* Sub banners */
        .small-banner {
            border-radius: 18px;
            overflow: hidden;
            background: #111827;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .3);
        }

        .small-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Category cards */
        .category-section-title {
            font-size: 1.05rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }

        .category-card {
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            transition: all .18s ease;
            cursor: pointer;
            min-width: 150px;
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
            border-color: var(--primary-soft);
        }

        .category-card img {
            max-height: 80px;
            object-fit: contain;
        }

        .category-name {
            font-weight: 600;
            font-size: .95rem;
        }

        /* NEW: horizontal strip – all 8 in one line */

        /* Category grid layout */
        .category-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr)); /* mobile: 2 columns */
            gap: 0.75rem;
        }

        .category-item {
            width: 100%;
        }

        /* Small devices and up (≥576px): 4 columns */
        @media (min-width: 576px) {
            .category-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        /* Large devices and up (≥992px): 8 columns in one row */
        @media (min-width: 992px) {
            .category-row {
                grid-template-columns: repeat(8, minmax(0, 1fr));
            }
        }

        /* .category-row {
            display: flex;
            align-items: stretch;
            flex-wrap: nowrap;         
            gap: 1rem;
            overflow-x: auto;
            padding: .5rem 0 1rem;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
        }

        .category-row::-webkit-scrollbar {
            display: none;
        }

        
        .category-item {
            flex: 0 0 auto;
            width: 150px;
            scroll-snap-align: start;
        }

        
        @media (min-width: 992px) {
            .category-row {
                overflow-x: visible;
                justify-content: space-between;
                scroll-snap-type: none;
            }
        } */


        /* Text section */
        .section-title {
            font-weight: 700;
            font-size: 1.9rem;
        }

        .section-subtitle {
            max-width: 750px;
        }

        .pill-chip {
            display: inline-flex;
            align-items: center;
            padding: .2rem .75rem;
            border-radius: 999px;
            font-size: .78rem;
            background: var(--primary-soft);
            color: #1d4ed8;
            font-weight: 600;
        }

        .pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #22c55e;
            margin-right: .35rem;
        }

        /* ===== Regular Pentanik TV Product Section ===== */
        .product-section {
            padding-top: 0rem !important;
            padding-bottom: 2rem;
        }

        .product-section .section-title {
            font-weight: 700;
            font-size: 2rem;
        }

        .product-section .section-subtitle {
            max-width: 760px;
        }

        /* Product card */
        .product-card {
            position: relative;
            border-radius: 22px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 1.2rem 1.0rem 1.4rem;
            transition: all .18s ease;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 38px rgba(15, 23, 42, .10);
            border-color: var(--primary-soft);
        }

        .product-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            background: #111827;
            color: #f9fafb;
            padding: .25rem .7rem;
            font-size: .75rem;
            border-radius: 999px;
            font-weight: 500;
        }

        .product-image-wrapper {
            text-align: center;
            padding: 1.5rem 0 .25rem;
            min-height: 170px;

        }

        .product-image-wrapper img {
            max-height: 360px;
            border-radius: 5%;
            width: 100%;
            object-fit: cover;
        }

        .product-title {
            font-size: .95rem;
            font-weight: 600;
            color: #1f2937;
            min-height: 2.7em;
            /* keep titles same height in grid */
        }

        .product-divider {
            height: 1px;
            background: #e5e7eb;
            margin: .9rem 0 1rem;
        }

        .product-price {
            font-size: .98rem;
        }

        .price-current {
            color: #dc2626;
            font-weight: 700;
            margin-right: .35rem;
        }

        .price-old {
            color: #9ca3af;
            text-decoration: line-through;
            font-size: .9rem;
        }

        .btn-product {
            background: var(--primary-soft);
            border: none;
            font-weight: 600;
            color: #1d4ed8;
            border-radius: 10px;
            padding: .55rem 1rem;
        }

        .btn-product:hover {
            background: var(--primary);
            color: #ffffff;
        }

        /* Small tweaks for very small screens */
        @media (max-width: 575.98px) {
            .product-image-wrapper {
                min-height: 140px;
                padding-top: 1.1rem;
            }
        }

        /* Cart dropdown */
        .cart-dropdown-menu {
            width: min(340px, 100vw - 1.5rem);
            border-radius: 16px;
            border: 1px solid #e5e7eb;
        }

        .cart-items-list {
            max-height: 260px;
            overflow-y: auto;
        }

        .cart-item-thumb {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 8px;
            background: #f9fafb;
        }


        footer {
            border-top: 1px solid rgba(148, 163, 184, .4);
        }







    </style>
    @stack('styles')
</head>

<body>


   

    <x-frontend.navbar />
  

    <main class="container hero-wrapper">





        @yield('pages')






    </main>


  <style>
    .whatsapp-container{position:fixed;right:10px;bottom:12px;z-index:999}
    .whatsapp-button{background-color:rgba(35, 160, 77, 1);color:#fff;display:flex;align-items:center;border-radius:50px;text-decoration:none;font-family:sans-serif;font-size:16px;font-weight:bold;box-shadow:0 4px 8px rgba(0,0,0,.2);transition:all .3s ease}
    .whatsapp-button:hover{background-color:rgba(8, 116, 44, 1); color:#fff}.whatsapp-button i{font-size:28px;margin-right:10px}
  </style>
  <div class="whatsapp-container">
    <a href="https://wa.link/v2rkod" class="whatsapp-button px-3 py-1" target="_blank" rel="noopener">
      <i class="bi bi-whatsapp"></i><span>Whatsapp</span>
    </a>
  </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
