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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-16">
        <div class="content-wrapper max-w-6xl mx-auto">
            <h1 class="text-center py-4 bg-gradient-to-r from-blue-500 to-blue-300 text-white rounded-lg shadow-lg mb-5 text-2xl font-bold uppercase">
                <i class="fa fa-history"></i> LỊCH SỬ ĐỌC
            </h1>

            <div id="historyContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mt-6">
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Lấy lịch sử đọc từ localStorage
        let readHistory = JSON.parse(localStorage.getItem('readHistory')) || [];
        function removeChapterFromHistory(filename) {
            readHistory = readHistory.filter(chapter => chapter.filename !== filename);
            localStorage.setItem('readHistory', JSON.stringify(readHistory));
            renderHistory();
        }
        function renderHistory() {
            const historyContainer = document.getElementById('historyContainer');
            historyContainer.innerHTML = ''; 
            if (readHistory.length > 0) {
                readHistory.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
                readHistory.forEach(chapter => {
                    const historyItem = `
                        <div class="relative group">
                            <a href="${chapter.chapter_link}">
                                <img src="${chapter.chapter_image}" alt="${chapter.chapter_story_name}" 
                                     class="w-full h-48 object-cover rounded-lg shadow-md hover:opacity-90 transition">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 flex items-center justify-center rounded-lg transition">
                                    <span class="text-white text-sm font-semibold hidden group-hover:block">${chapter.chapter_story_name}</span>
                                </div>
                            </a>
                            <div class="mt-2 text-center">
                                <p class="text-gray-400 text-xs">${chapter.chapter_name}</p>
                            </div>
                            <button class="absolute top-2 right-2 bg-red-600 text-white w-6 h-6 rounded-full hover:bg-red-700 transition flex items-center justify-center"
                                    onclick="removeChapterFromHistory('${chapter.filename}')">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    `;
                    historyContainer.insertAdjacentHTML('beforeend', historyItem);
                });
            } else {
                historyContainer.innerHTML = `
                    <div class="col-span-full text-center bg-yellow-500 text-white p-4 rounded-lg">
                        Bạn chưa đọc chương truyện nào.
                    </div>
                `;
            }
        }
        renderHistory();
    </script>
</body>
</html>