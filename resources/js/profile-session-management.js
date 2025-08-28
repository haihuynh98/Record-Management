// Profile Session Management JavaScript
window.handleViewClick = function(event, profileId) {
    event.preventDefault();
    
    const user = window.currentUser; // Sẽ được set từ backend
    if (!user) return;
    
    // Kiểm tra xem user có quyền duyệt/từ chối không
    const hasApprovalPermission = user.permissions && (
        user.permissions.includes('approve_profile') || 
        user.permissions.includes('reject_profile')
    );
    
    if (!hasApprovalPermission) {
        // Nếu không có quyền, cho phép xem bình thường
        return;
    }
    
    // Gọi API để bắt đầu session
    fetch('/api/profile-sessions/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            profile_id: profileId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Session bắt đầu thành công, mở modal
            openProfileModal(profileId);
        } else {
            // Có người đang xem
            if (data.is_being_viewed) {
                // Hiển thị notification
                if (window.Filament && window.Filament.notifications) {
                    window.Filament.notifications.danger({
                        title: 'Không thể xem hồ sơ',
                        body: data.message
                    });
                } else {
                    alert(data.message);
                }
            }
        }
    })
    .catch(error => {
        console.error('Error starting session:', error);
    });
};

window.openProfileModal = function(profileId) {
    // Lưu session ID để tracking
    window.currentProfileSessionId = profileId;
    
    // Bắt đầu interval để update activity
    window.activityInterval = setInterval(() => {
        updateSessionActivity(profileId);
    }, 30000); // Update mỗi 30 giây
    
    // Thêm event listener cho modal close
    document.addEventListener('modal-closed', function() {
        endProfileSession(profileId);
    });
    
    // Thêm event listener cho page unload
    window.addEventListener('beforeunload', function() {
        endProfileSession(profileId);
    });
};

window.updateSessionActivity = function(profileId) {
    fetch('/api/profile-sessions/update-activity', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            profile_id: profileId
        })
    })
    .catch(error => {
        console.error('Error updating activity:', error);
    });
};

window.endProfileSession = function(profileId) {
    // Clear interval
    if (window.activityInterval) {
        clearInterval(window.activityInterval);
        window.activityInterval = null;
    }
    
    // Gọi API để kết thúc session
    fetch('/api/profile-sessions/end', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            profile_id: profileId
        })
    })
    .catch(error => {
        console.error('Error ending session:', error);
    });
    
    // Xóa session ID
    window.currentProfileSessionId = null;
};

// Thêm event listener cho modal close (Filament specific)
document.addEventListener('DOMContentLoaded', function() {
    // Listen for Filament modal close events
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.removedNodes.forEach(function(node) {
                    if (node.classList && node.classList.contains('fi-modal')) {
                        if (window.currentProfileSessionId) {
                            endProfileSession(window.currentProfileSessionId);
                        }
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
