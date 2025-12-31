<?php
// Reusable modals: delete confirmation + success notification
?>

<!-- Delete confirmation modal -->
<div id="delete-modal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 15px; font-size: 16px; text-align: center;">Xác nhận xóa</h3>
        <p id="delete-modal-message" style="text-align: center; margin-bottom: 20px; font-size: 13px;">Bạn có chắc chắn muốn xóa mục này không?</p>
        <div class="modal-actions" style="justify-content: center;">
            <button id="confirm-delete-btn" class="btn-save btn-danger" style="background-color: #ff3333; margin: 0; width: auto; padding: 10px 30px;">Xóa</button>
            <button id="cancel-delete-btn" class="btn-cancel">Hủy</button>
        </div>
    </div>
</div>

<!-- Generic success modal -->
<div id="success-modal" class="modal">
    <div class="modal-content">
        <h3 id="success-modal-title" style="margin-bottom: 12px; font-size: 16px; text-align: center;">Thành công</h3>
        <p id="success-modal-message" style="text-align: center; margin-bottom: 18px; font-size: 14px;">Thao tác đã thực hiện thành công.</p>
        <div class="modal-actions" style="justify-content: center;">
            <button id="success-ok-btn" class="btn-save" style="margin:0; width:auto; padding:8px 24px;">OK</button>
        </div>
    </div>
</div>

<!-- HR Filter Modal -->
<div id="hr-filter-modal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom:12px; text-align:center;">Lọc nhân sự</h3>
        <div style="display:flex; flex-direction:column; gap:8px;">
            <label>Trạng thái:
                <select id="hr-filter-status">
                    <option value="all">Tất cả</option>
                    <option value="hoạt động">Hoạt động</option>
                    <option value="tạm dừng">Tạm dừng</option>
                    <option value="chờ duyệt">Chờ duyệt</option>
                </select>
            </label>
        </div>
        <div class="modal-actions" style="justify-content:center; margin-top:12px;">
            <button id="apply-hr-filter" class="btn-save">Áp dụng</button>
            <button id="close-hr-filter" class="btn-cancel">Đóng</button>
        </div>
    </div>
</div>

<!-- HR Search Modal -->
<div id="hr-search-modal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom:12px; text-align:center;">Tìm kiếm nhân sự</h3>
        <div style="display:flex; gap:8px; align-items:center;">
            <input id="hr-search-input" type="text" placeholder="Nhập tên, SĐT, mã..." style="flex:1;">
        </div>
        <div class="modal-actions" style="justify-content:center; margin-top:12px;">
            <button id="apply-hr-search" class="btn-save">Tìm</button>
            <button id="close-hr-search" class="btn-cancel">Đóng</button>
        </div>
    </div>
</div>

<!-- Load global scripts (ensure shared JS handlers are available) -->
<script>
    window.BASE_URL = '<?= BASE_URL ?>';
</script>
<!-- script.js should be included once per page in the layout/view head. -->