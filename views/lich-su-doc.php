<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Lịch Sử Đọc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        #historyContainer {
            margin-top: 20px;
        }

        /* Đảm bảo hình ảnh không vượt quá kích thước container */
        .chapter-image {
            max-width: 50px;
            height: auto;
        }

        /* Ẩn một số cột trên màn hình nhỏ */
        @media (max-width: 768px) {
            .hide-on-mobile {
                display: none;
            }

            .btn-smaller {
                font-size: 0.8rem;
                padding: 5px 10px;
            }
        }

        /* Đảm bảo các nút hành động không bị dính sát nhau */
        .action-buttons {
            display: flex;
            gap: 5px;
        }

        /* Tùy chỉnh bảng */
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container mt-5">
        <h1 class="text-center mb-4">Lịch Sử Đọc</h1>
        <div id="historyContainer">
        </div>
        <br>
    </main>
    <?php include '../includes/footer.php'; ?>

    <script>
        // Lấy lịch sử đọc từ localStorage
        let readHistory = JSON.parse(localStorage.getItem('readHistory')) || [];

        // Hàm xóa chương khỏi lịch sử
        function removeChapterFromHistory(filename) {
            readHistory = readHistory.filter(chapter => chapter.filename !== filename);
            localStorage.setItem('readHistory', JSON.stringify(readHistory));
            renderHistory();  
        }

        // Hàm hiển thị lịch sử đọc
        function renderHistory() {
            const historyContainer = document.getElementById('historyContainer');
            if (readHistory.length > 0) {
                // Sắp xếp lịch sử theo thứ tự truyện mới nhất
                readHistory.sort((a, b) => b.filename.localeCompare(a.filename));  
                let historyHtml = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Chapter</th>
                                    <th scope="col" class="hide-on-mobile">Tiêu Đề</th>
                                    <th scope="col" class="hide-on-mobile">Hình Ảnh</th>
                                    <th scope="col">Tên Truyện</th>
                                    <th scope="col">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                readHistory.forEach(chapter => {
                    historyHtml += `
                        <tr>
                            <td>${chapter.chapter_name}</td>
                            <td class="hide-on-mobile">${chapter.chapter_title}</td>
                            <td class="hide-on-mobile"><img src="${chapter.chapter_image}" alt="Hình ảnh truyện" class="chapter-image"></td>
                            <td>${chapter.chapter_story_name}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="${chapter.chapter_link}" target="_blank" class="btn btn-primary btn-smaller">Xem Truyện</a>
                                    <button class="btn btn-danger btn-smaller" onclick="removeChapterFromHistory('${chapter.filename}')">Xóa</button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                historyHtml += `</tbody></table></div>`;
                historyContainer.innerHTML = historyHtml;
            } else {
                historyContainer.innerHTML = `
                    <div class="alert alert-warning text-center" role="alert">
                        Bạn chưa đọc chương truyện nào.
                    </div>
                `;
            }
        }

        // Gọi hàm render
        renderHistory();
    </script>
</body>
</html>