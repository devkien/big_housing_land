<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Báo cáo dẫn khách</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Báo cáo dẫn khách</div>
            <div style="width: 20px;"></div>
        </header>

        <?php require_once __DIR__ . '/../partials/alert.php'; ?>

        <form action="<?= BASE_URL ?>/superadmin/report-list" method="GET" class="search-form-container">
            <div class="search-box-blue-border" style="width: 80%; margin: 15px auto;">
                <input type="text" name="q" placeholder="Tìm theo người gửi, đầu chủ, SĐT..." value="<?= htmlspecialchars($search ?? '') ?>">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>

        <div class="table-wrapper" style="padding-bottom: 80px;">
            <table class="report-list-table">
                <thead>
                    <tr>
                        <th style="padding-left: 10px; padding-right: 5px; text-align: left;">Thời gian gửi</th>
                        <th style="text-align: left; padding-left: 5px; padding-right: 5px;">Đầu Khách</th>
                        <th style="text-align: left; padding-left: 5px; padding-right: 5px;">Đầu chủ</th>
                        <th class="text-center" style="padding-left: 5px; padding-right: 5px;">SĐT</th>
                        <th class="text-center" style="width: 50px;">Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)) : ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px; color:#666;">Không có báo cáo nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r) :
                            $time = !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '';
                            $sender = htmlspecialchars($r['sender_name'] ?? '');
                            $manager = htmlspecialchars($r['manager_name'] ?? '');
                            $phone = htmlspecialchars($r['customer_phone'] ?? '');
                            $customerName = htmlspecialchars($r['customer_name'] ?? '');
                        ?>
                            <tr onclick="window.location.href='<?= BASE_URL ?>/superadmin/report-customer?id=<?= (int)$r['id'] ?>'" style="cursor: pointer;">
                                <td style="padding-left: 10px; padding-right: 5px;"><?= $time ?></td>
                                <td style="padding-left: 5px; padding-right: 5px;"><?= $sender ?></td>
                                <td style="padding-left: 5px; padding-right: 5px;"><?= $manager ?></td>
                                <td class="text-center" style="padding-left: 5px; padding-right: 5px;"><?= $phone ?></td>

                                <td class="text-center">
                                    <button type="button" class="btn-delete-report" data-id="<?= $r['id'] ?>" style="background:none; border:none; color:#dc3545; cursor:pointer; padding: 5px;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">
                            <div class="pagination-container">
                                <?php
                                $queryBase = [];
                                if (!empty($search)) $queryBase['q'] = $search;
                                $prev = max(1, $page - 1);
                                $next = min($totalPages, $page + 1);
                                ?>
                                <a href="<?= BASE_URL ?>/superadmin/report-list?<?= http_build_query(array_merge($queryBase, ['page' => $prev])) ?>" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>
                                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                    <a href="<?= BASE_URL ?>/superadmin/report-list?<?= http_build_query(array_merge($queryBase, ['page' => $i])) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                                <?php endfor; ?>
                                <a href="<?= BASE_URL ?>/superadmin/report-list?<?= http_build_query(array_merge($queryBase, ['page' => $next])) ?>" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

        <!-- Modal for creating a new report -->
        <div id="create-report-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h2>Gửi báo cáo dẫn khách</h2>
                <form id="create-report-form" autocomplete="off">
                    <div class="form-group">
                        <label for="manager_name">Tên đầu chủ:</label>
                        <input type="text" id="manager_name" name="manager_name" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_name">Tên đầu khách:</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">SĐT đầu khách:</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Gửi báo cáo</button>
                </form>
            </div>
        </div>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal handling
            const modal = document.getElementById('create-report-modal');
            const openModalBtn = document.getElementById('open-create-report-modal-btn');
            const closeModalBtn = modal.querySelector('.close-btn');

            if (openModalBtn) {
                openModalBtn.onclick = () => modal.style.display = 'block';
            }
            if (closeModalBtn) {
                closeModalBtn.onclick = () => modal.style.display = 'none';
            }
            window.onclick = (event) => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };

            // AJAX Form Submission
            const form = document.getElementById('create-report-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitButton = form.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.textContent = 'Đang gửi...';

                    const formData = new FormData(this);

                    fetch('<?= BASE_URL ?>/superadmin/report-customer/create', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Backend đã thiết lập session flash message.
                                // Tải lại trang để hiển thị báo cáo mới và thông báo.
                                window.location.reload();
                            } else {
                                // Với các lỗi (ví dụ: validation), hiển thị thông báo động mà không cần tải lại trang.
                                showAlert('error', data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('error', 'Có lỗi kết nối, vui lòng thử lại.');
                        })
                        .finally(() => {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Gửi báo cáo';
                        });
                });
            }

            // Xử lý sự kiện xóa báo cáo
            const deleteBtns = document.querySelectorAll('.btn-delete-report');
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    // Ngăn chặn sự kiện click lan ra thẻ tr (tránh chuyển trang xem chi tiết)
                    e.stopPropagation();

                    if (confirm('Bạn có chắc chắn muốn xóa báo cáo này không?')) {
                        const id = this.getAttribute('data-id');
                        const row = this.closest('tr');

                        // Gửi yêu cầu xóa đến server
                        fetch('<?= BASE_URL ?>/superadmin/report-customer/delete', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'id=' + id
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    row.remove(); // Xóa dòng khỏi bảng
                                    showAlert('success', 'Đã xóa báo cáo thành công.');
                                } else {
                                    showAlert('error', data.message || 'Xóa thất bại.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showAlert('error', 'Có lỗi xảy ra khi xóa.');
                            });
                    }
                });
            });

            function showAlert(type, message) {
                const existingAlert = document.querySelector('.alert-wrapper');
                if (existingAlert) {
                    existingAlert.remove();
                }

                const alertWrapper = document.createElement('div');
                alertWrapper.className = 'alert-wrapper';

                const icons = {
                    'success': 'fa-check-circle',
                    'error': 'fa-exclamation-triangle',
                    'warning': 'fa-exclamation-circle',
                    'info': 'fa-info-circle',
                };
                const iconClass = icons[type] || 'fa-info-circle';

                alertWrapper.innerHTML = `
                    <div class="alert alert--${type}" role="alert" aria-live="polite">
                        <div class="alert-inner">
                            <i class="fa ${iconClass} alert-icon" aria-hidden="true"></i>
                            <div class="alert-message">${message}</div>
                            <button type="button" class="alert-close" aria-label="Đóng thông báo" onclick="this.closest('.alert-wrapper').remove()">&times;</button>
                        </div>
                    </div>
                `;
                const header = document.querySelector('.detail-header');
                header.parentNode.insertBefore(alertWrapper, header.nextSibling);
            }
        });
    </script>
</body>

</html>