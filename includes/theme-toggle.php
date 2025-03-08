<!-- includes/theme-toggle.php -->
<style>
    /* CSS cho nút chuyển đổi giao diện */
    .theme-toggle-btn {
        position: fixed;
        bottom: 20px;
        right: 20px; /* Chuyển từ left sang right để nút nằm ở góc dưới bên phải */
        z-index: 1000;
        background-color: #007bff;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        transition: background-color 0.3s;
    }
    .theme-toggle-btn:hover {
        background-color: #0056b3;
    }

    /* CSS cho giao diện sáng/tối - chỉ áp dụng cho nội dung chính */
    .content-light {
        background-color: #ffffff;
        color: #000000;
    }
    .content-dark {
        background-color: #1a1a1a;
        color: #ffffff;
    }
    .container.content-light {
        background-color: #ffffff;
    }
    .container.content-dark {
        background-color: #1a1a1a;
    }
    .manga-card.content-light {
        background-color: #f8f9fa;
    }
    .manga-card.content-dark {
        background-color: #333333;
    }

    /* Điều chỉnh màu chữ trong chế độ tối */
    .content-dark .manga-title {
        color: #ffffff;
    }
    .content-dark .text-muted {
        color: #b0b0b0;
    }
    .content-dark .section-title {
        color: #ffffff;
    }
    .content-dark .card-body .text-muted span {
        color: #ffffff;
    }
    .content-dark .card-body .text-muted i {
        color: #ffffff;
    }
    .content-dark .card-body .text-muted {
        color: #ffffff !important;
    }

    /* Cố định giao diện header và các phần tử con */
    .header {
        background: linear-gradient(135deg, #1e3a8a, #3b82f6) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
        position: fixed !important;
        width: 100% !important;
        top: 0 !important;
        z-index: 1000 !important;
    }
    .header .header-brand {
        color: #fef08a !important;
    }
    .header .hamburger-btn {
        color: #fef08a !important;
    }
    .header .search-bar input {
        background: #fff !important;
        color: #1e3a8a !important;
    }
    .header .search-bar button {
        background: #ef4444 !important;
        color: #fff !important;
    }
    .header .user-actions .login-btn,
    .header .user-actions .register-btn {
        background: #fef08a !important;
        color: #1e3a8a !important;
    }
    .header .user-actions .dropdown-toggle {
        color: #fff !important;
    }
    .header .nav-container {
        background: transparent !important;
    }
    .header .mobile-nav .nav-link {
        background: #3b82f6 !important;
        color: #fff !important;
    }
    .header .dropdown-menu {
        background: #fff !important;
        color: #1e3a8a !important;
    }
    .header .dropdown-item {
        color: #1e3a8a !important;
    }

    /* Cố định giao diện genre-nav */
    .genre-nav {
        background: #fff !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
        margin-top: 70px !important;
    }
    .genre-nav .nav-link {
        color: #1e3a8a !important;
        background: #e0f2fe !important;
    }
    .genre-nav .dropdown-menu {
        background: #fff !important;
    }
    .genre-nav .dropdown-item {
        color: #1e3a8a !important;
    }
    .genre-nav marquee {
        background: #fef08a !important;
        color: #1e3a8a !important;
    }

    /* Cố định giao diện footer */
    footer.site-footer {
        background-color: #212529 !important;
        color: #ffffff !important;
    }
    footer.site-footer a {
        color: #ffffff !important;
    }
</style>

<!-- Nút chuyển đổi giao diện -->
<button id="themeToggleBtn" class="theme-toggle-btn">
    <i class="fas fa-moon"></i>
</button>

<script>
    // Xử lý chuyển đổi giao diện
    function toggleTheme() {
        const body = document.body;
        const contentContainers = document.querySelectorAll('.container:not(.header .container):not(.genre-nav .container):not(footer .container)');
        const mangaCards = document.querySelectorAll('.manga-card');
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const isDarkMode = body.classList.contains('content-dark');

        if (isDarkMode) {
            body.classList.remove('content-dark');
            body.classList.add('content-light');
            contentContainers.forEach(container => {
                container.classList.remove('content-dark');
                container.classList.add('content-light');
            });
            mangaCards.forEach(card => {
                card.classList.remove('content-dark');
                card.classList.add('content-light');
            });
            themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
            localStorage.setItem('theme', 'light');
        } else {
            body.classList.remove('content-light');
            body.classList.add('content-dark');
            contentContainers.forEach(container => {
                container.classList.remove('content-light');
                container.classList.add('content-dark');
            });
            mangaCards.forEach(card => {
                card.classList.remove('content-light');
                card.classList.add('content-dark');
            });
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
            localStorage.setItem('theme', 'dark');
        }
    }

    // Khôi phục giao diện từ localStorage khi tải trang
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme');
        const body = document.body;
        const contentContainers = document.querySelectorAll('.container:not(.header .container):not(.genre-nav .container):not(footer .container)');
        const mangaCards = document.querySelectorAll('.manga-card');
        const themeToggleBtn = document.getElementById('themeToggleBtn');

        if (savedTheme === 'light') {
            body.classList.remove('content-dark');
            body.classList.add('content-light');
            contentContainers.forEach(container => {
                container.classList.remove('content-dark');
                container.classList.add('content-light');
            });
            mangaCards.forEach(card => {
                card.classList.remove('content-dark');
                card.classList.add('content-light');
            });
            themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            body.classList.add('content-dark');
            contentContainers.forEach(container => {
                container.classList.add('content-dark');
            });
            mangaCards.forEach(card => {
                card.classList.add('content-dark');
            });
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
    });

    // Gán sự kiện click cho nút
    document.getElementById('themeToggleBtn').addEventListener('click', toggleTheme);
</script>