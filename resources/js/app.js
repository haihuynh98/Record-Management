import './bootstrap';

// Xử lý session xem hồ sơ
window.handleViewClick = function(event, profileId) {
    event.preventDefault();
    
    // Gọi API để thiết lập session
    fetch('/api/profile/set-viewing-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            profile_id: profileId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Lưu session ID để sử dụng khi đóng modal
            event.target.closest('[x-data]').__x.$data.sessionId = data.session_id;
            
            // Mở modal xem chi tiết
            // Filament sẽ tự động mở modal sau khi before() return true
        } else {
            // Hiển thị thông báo lỗi
            if (data.is_being_viewed) {
                // Hiển thị thông báo cảnh báo
                Filament.notify('warning', {
                    title: 'Không thể xem hồ sơ',
                    body: data.message
                });
            } else {
                Filament.notify('danger', {
                    title: 'Lỗi',
                    body: data.message
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Filament.notify('danger', {
            title: 'Lỗi',
            body: 'Có lỗi xảy ra khi thiết lập session xem hồ sơ.'
        });
    });
};

// Xử lý khi đóng modal
window.handleModalClose = function(profileId, sessionId) {
    if (sessionId) {
        // Gọi API để xóa session
        fetch('/api/profile/clear-viewing-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                profile_id: profileId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Session cleared successfully');
                // Refresh table để cập nhật icon ổ khóa
                if (window.location.reload) {
                    window.location.reload();
                }
            }
        })
        .catch(error => {
            console.error('Error clearing session:', error);
        });
    }
};

// Lắng nghe sự kiện đóng modal
document.addEventListener('DOMContentLoaded', function() {
    // Lắng nghe sự kiện đóng modal của Filament
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('fi-modal-close') || 
            event.target.closest('.fi-modal-close') ||
            event.target.classList.contains('fi-btn') && event.target.textContent.includes('Đóng')) {
            
            // Session sẽ được xóa tự động bởi Alpine.js trong modal content
            console.log('Modal closed, session will be cleared by Alpine.js');
        }
    });
});
