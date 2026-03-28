<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>YouTube Course Scraper - أداة جمع الدورات التعليمية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e63946;
            --primary-dark: #d62839;
            --primary-light: rgba(230,57,70,0.1);
            --bg-light: #f5f6fa;
            --bg-white: #ffffff;
            --text-dark: #1d1d2c;
            --text-body: #4a4a5a;
            --text-muted: #8b8b9e;
            --border: #e8e8ef;
            --card-shadow: 0 2px 12px rgba(0,0,0,0.06);
            --card-hover-shadow: 0 8px 30px rgba(0,0,0,0.12);
            --gradient-hero: linear-gradient(135deg, #0f0c29 0%, #1a1a3e 40%, #24243e 100%);
        }

        * {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-body);
            -webkit-font-smoothing: antialiased;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #aaa; }

        /* Navbar */
        .navbar-custom {
            background: var(--bg-white);
            border-bottom: 1px solid var(--border);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .navbar-custom .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar-custom .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .navbar-custom .brand-logo {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
        }
        .navbar-custom .brand-text {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.95rem;
        }
        .navbar-custom .brand-text span {
            color: var(--primary);
        }
        .nav-stats {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.82rem;
            color: var(--text-muted);
        }
        .nav-stats .stat-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-stats .stat-item i {
            color: var(--primary);
        }

        /* Hero Section */
        .hero-section {
            background: var(--gradient-hero);
            color: #fff;
            padding: 60px 0 70px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(230,57,70,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-section .container {
            position: relative;
            z-index: 1;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        .hero-badge i {
            color: var(--primary);
        }
        .hero-section h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .hero-section p {
            color: rgba(255,255,255,0.6);
            font-size: 0.95rem;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Input Section */
        .input-section {
            background: var(--bg-white);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-top: -40px;
            position: relative;
            z-index: 2;
            border: 1px solid var(--border);
        }
        .input-section label {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.82rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .input-section label i {
            color: var(--primary);
            font-size: 0.7rem;
        }
        .input-section textarea {
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            font-size: 0.92rem;
            resize: vertical;
            direction: rtl;
            text-align: right;
            transition: all 0.3s;
            background: var(--bg-light);
            width: 100%;
            min-height: 120px;
        }
        .input-section textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
            background: var(--bg-white);
        }
        .input-section textarea::placeholder {
            color: #bbb;
        }

        /* Buttons */
        .btn-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
        }
        .btn-fetch {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 13px 32px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .btn-fetch:hover {
            background: var(--primary-dark);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230,57,70,0.35);
        }
        .btn-fetch:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
            cursor: not-allowed;
        }
        .btn-stop {
            background: var(--bg-white);
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 12px;
            padding: 11px 24px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .btn-stop:hover {
            background: var(--primary);
            color: #fff;
        }

        /* Progress */
        .progress-section {
            display: none;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .progress-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .progress-header .progress-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .progress-header .progress-percent {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--primary);
        }
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background: #eee;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #ff6b6b);
            border-radius: 4px;
            transition: width 0.5s ease;
            position: relative;
        }
        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .progress-text {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .progress-text i {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .progress-log {
            max-height: 160px;
            overflow-y: auto;
            background: var(--bg-light);
            border-radius: 10px;
            padding: 14px;
            margin-top: 12px;
            font-size: 0.78rem;
            direction: rtl;
            border: 1px solid var(--border);
        }
        .progress-log .log-entry {
            padding: 5px 8px;
            margin-bottom: 4px;
            border-radius: 6px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.6;
        }
        .progress-log .log-entry::before {
            content: '\f111';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.35rem;
            color: #ccc;
            flex-shrink: 0;
        }
        .progress-log .log-entry.success {
            color: #059669;
            background: rgba(5,150,105,0.06);
        }
        .progress-log .log-entry.success::before {
            content: '\f00c';
            font-size: 0.65rem;
            color: #059669;
        }
        .progress-log .log-entry.error {
            color: var(--primary);
            background: var(--primary-light);
        }
        .progress-log .log-entry.error::before {
            content: '\f00d';
            font-size: 0.65rem;
            color: var(--primary);
        }
        .progress-log .log-entry.info {
            color: #2563eb;
            background: rgba(37,99,235,0.06);
        }
        .progress-log .log-entry.info::before {
            content: '\f129';
            font-size: 0.65rem;
            color: #2563eb;
        }

        /* Courses Section */
        .courses-section {
            padding: 50px 0 60px;
        }
        .courses-header {
            text-align: right;
            margin-bottom: 28px;
        }
        .courses-header h2 {
            font-weight: 800;
            font-size: 1.6rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
        .courses-header h2 .count-badge {
            background: var(--primary);
            color: #fff;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 700;
        }
        .courses-header .subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 4px;
        }

        /* Category Tabs */
        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 28px;
            justify-content: flex-end;
        }
        .category-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s;
            border: 1px solid var(--border);
            color: var(--text-body);
            background: var(--bg-white);
        }
        .category-tab:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }
        .category-tab.active {
            background: var(--text-dark);
            color: #fff;
            border-color: var(--text-dark);
        }
        .category-tab .tab-count {
            background: rgba(0,0,0,0.08);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
        }
        .category-tab.active .tab-count {
            background: rgba(255,255,255,0.2);
        }

        /* Course Card */
        .course-card {
            background: var(--bg-white);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border);
        }
        .course-card:hover {
            box-shadow: var(--card-hover-shadow);
            transform: translateY(-5px);
            border-color: transparent;
        }
        .course-card .card-img-wrapper {
            position: relative;
            overflow: hidden;
            aspect-ratio: 16/9;
        }
        .course-card .card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .course-card:hover .card-img-wrapper img {
            transform: scale(1.05);
        }
        .course-card .card-img-wrapper .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .course-card:hover .card-img-wrapper .overlay {
            opacity: 1;
        }
        .course-card .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1rem;
            opacity: 0;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .course-card:hover .play-btn {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        .course-card .video-count {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: #fff;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            backdrop-filter: blur(4px);
        }
        .course-card .duration-badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: #fff;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            backdrop-filter: blur(4px);
        }
        .course-card .card-body {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .course-card .card-title {
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1.6;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-decoration: none;
            color: var(--text-dark);
            transition: color 0.2s;
        }
        .course-card .card-title:hover {
            color: var(--primary);
        }
        .course-card .channel-name {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .course-card .channel-name i {
            font-size: 0.7rem;
        }
        .course-card .card-footer-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }
        .course-card .view-count {
            font-size: 0.72rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .course-card .category-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        .pagination .page-item .page-link {
            border-radius: 10px;
            margin: 0 3px;
            color: var(--text-dark);
            border: 1px solid var(--border);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 10px 16px;
            transition: all 0.2s;
        }
        .pagination .page-item .page-link:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }
        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-muted);
        }
        .empty-state .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-light);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .empty-state .empty-icon i {
            font-size: 2rem;
            color: var(--primary);
        }
        .empty-state h4 {
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        .empty-state p {
            max-width: 350px;
            margin: 0 auto;
            font-size: 0.9rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        /* Footer */
        .site-footer {
            text-align: center;
            padding: 24px 0;
            color: var(--text-muted);
            font-size: 0.8rem;
            border-top: 1px solid var(--border);
            background: var(--bg-white);
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 { font-size: 1.5rem; }
            .hero-section { padding: 40px 0 55px; }
            .input-section { padding: 20px; margin-top: -30px; }
            .nav-stats { display: none; }
            .category-tabs { justify-content: center; }
            .courses-header { text-align: center; }
            .courses-header h2 { justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="container">
            <div class="nav-inner">
                <div class="nav-stats">
                    @hasSection('nav-stats')
                        @yield('nav-stats')
                    @endif
                </div>
                <a href="{{ url('/') }}" class="brand">
                    <span class="brand-text">
                        أداة جمع الدورات التعليمية
                        <span>| YouTube Scraper</span>
                    </span>
                    <div class="brand-logo">
                        <i class="fab fa-youtube"></i>
                    </div>
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="site-footer">
        <div class="container">
            YouTube Course Scraper &copy; {{ date('Y') }}
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
