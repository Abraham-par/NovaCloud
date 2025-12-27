// File Upload with Progress
class FileUploader {
    constructor() {
        this.init();
    }

    init() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', this.handleFileSelect.bind(this));
        }

        // Drag and drop
        const dropZone = document.querySelector('.main-content');
        if (dropZone) {
            dropZone.addEventListener('dragover', this.handleDragOver.bind(this));
            dropZone.addEventListener('drop', this.handleDrop.bind(this));
        }
    }

    handleFileSelect(e) {
        const files = e.target.files;
        if (files.length > 0) {
            this.uploadFile(files[0]);
        }
    }

    handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        e.dataTransfer.dropEffect = 'copy';
    }

    handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.uploadFile(files[0]);
        }
    }

    async uploadFile(file) {
        // Check file size
        const maxSize = 100 * 1024 * 1024; // 100MB
        if (file.size > maxSize) {
            alert('File size exceeds 100MB limit');
            return;
        }

        // Check file type
        const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'mp4', 'mp3'];
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExt)) {
            alert('File type not allowed');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch('api/upload.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('File uploaded successfully');
                location.reload();
            } else {
                alert('Upload failed: ' + result.error);
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Upload failed');
        }
    }
}

// Search Functionality
class FileSearch {
    constructor() {
        this.init();
    }

    init() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce(this.handleSearch.bind(this), 300));
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    async handleSearch(e) {
        const query = e.target.value;

        if (query.length < 2) {
            return;
        }

        try {
            const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
            const result = await response.json();

            if (result.success) {
                this.updateFileList(result.files);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    updateFileList(files) {
        const filesGrid = document.querySelector('.files-grid');
        if (!filesGrid) return;

        if (files.length === 0) {
            filesGrid.innerHTML = '<div class="no-files">No files found</div>';
            return;
        }

        // Update files grid with search results
        // This would be implemented based on your file rendering logic
    }
}

// Session Management
class SessionManager {
    constructor() {
        this.checkSession();
        this.setupAutoLogout();
    }

    checkSession() {
        // Check if session is about to expire (5 minutes before)
        const sessionTime = sessionStorage.getItem('session_start');
        if (sessionTime) {
            const elapsed = Date.now() - parseInt(sessionTime);
            const warningTime = 55 * 60 * 1000; // 55 minutes

            if (elapsed > warningTime) {
                this.showSessionWarning();
            }
        }
    }

    showSessionWarning() {
        const warning = document.createElement('div');
        warning.className = 'session-warning';
        warning.innerHTML = `
            <div class="warning-content">
                <p>Your session will expire soon. Click to extend.</p>
                <button onclick="extendSession()">Extend Session</button>
            </div>
        `;

        document.body.appendChild(warning);
    }

    setupAutoLogout() {
        // Auto logout after 1 hour of inactivity
        let timeout;

        const resetTimer = () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                window.location.href = 'logout.php?reason=inactivity';
            }, 60 * 60 * 1000); // 1 hour
        };

        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        events.forEach(event => {
            document.addEventListener(event, resetTimer);
        });

        resetTimer();
    }
}

// Profile Management
class ProfileManager {
    constructor() {
        this.init();
    }

    init() {
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', this.handleProfileUpdate.bind(this));
        }

        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', this.handlePasswordChange.bind(this));
        }
    }

    async handleProfileUpdate(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch('api/update-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                alert('Profile updated successfully');
            } else {
                alert('Update failed: ' + result.error);
            }
        } catch (error) {
            console.error('Profile update error:', error);
            alert('Update failed');
        }
    }

    async handlePasswordChange(e) {
        e.preventDefault();

        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            alert('Passwords do not match');
            return;
        }

        try {
            const response = await fetch('api/change-password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword
                })
            });

            const result = await response.json();

            if (result.success) {
                alert('Password changed successfully');
                e.target.reset();
            } else {
                alert('Password change failed: ' + result.error);
            }
        } catch (error) {
            console.error('Password change error:', error);
            alert('Password change failed');
        }
    }
}

// File Preview System
class FilePreview {
    constructor() {
        this.init();
    }

    init() {
        // Add preview handlers for files
        document.addEventListener('click', this.handlePreviewClick.bind(this));
    }

    handlePreviewClick(e) {
        const fileLink = e.target.closest('.file-preview-link');
        if (fileLink) {
            e.preventDefault();
            const fileId = fileLink.dataset.fileId;
            this.showPreview(fileId);
        }
    }

    async showPreview(fileId) {
        try {
            const response = await fetch(`api/get-file.php?id=${fileId}`);
            const result = await response.json();

            if (result.success) {
                this.displayPreview(result.file);
            }
        } catch (error) {
            console.error('Preview error:', error);
        }
    }

    displayPreview(file) {
        const modal = document.createElement('div');
        modal.className = 'preview-modal';
        modal.innerHTML = `
            <div class="preview-content">
                <button class="close-btn">&times;</button>
                <h3>${file.filename}</h3>
                
                ${this.getPreviewContent(file)}
                
                <div class="preview-actions">
                    <a href="${file.file_path}" download class="btn btn-success">Download</a>
                    <button class="btn btn-secondary close-preview">Close</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelector('.close-btn').addEventListener('click', () => {
            modal.remove();
        });

        modal.querySelector('.close-preview').addEventListener('click', () => {
            modal.remove();
        });
    }

    getPreviewContent(file) {
        const fileType = file.file_type.toLowerCase();

        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
            return `<img src="${file.file_path}" alt="${file.filename}" class="file-preview">`;
        } else if (['mp4', 'webm', 'ogg'].includes(fileType)) {
            return `
                <video controls class="file-preview">
                    <source src="${file.file_path}" type="video/${fileType}">
                    Your browser does not support the video tag.
                </video>
            `;
        } else if (['mp3', 'wav', 'ogg'].includes(fileType)) {
            return `
                <audio controls class="file-preview">
                    <source src="${file.file_path}" type="audio/${fileType}">
                    Your browser does not support the audio tag.
                </audio>
            `;
        } else if (fileType === 'pdf') {
            return `<iframe src="${file.file_path}" class="file-preview"></iframe>`;
        } else {
            return `<p>Preview not available for this file type. Download to view.</p>`;
        }
    }
}

// Sidebar manager removed — restoring original JS behavior

// Initialize all systems when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize file uploader
    window.fileUploader = new FileUploader();

    // Initialize search
    window.fileSearch = new FileSearch();

    // Initialize session manager
    window.sessionManager = new SessionManager();

    // Initialize profile manager
    window.profileManager = new ProfileManager();

    // Initialize file preview
    window.filePreview = new FilePreview();

    // Initialize language switcher
    if (typeof LanguageSwitcher !== 'undefined') {
        window.languageSwitcher = new LanguageSwitcher();
    }

    // SidebarManager removed; no sidebar toggle initialization

    // Add CSRF token to all forms
    document.querySelectorAll('form').forEach(form => {
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(tokenInput);
    });
});

// Utility function to extend session
function extendSession() {
    fetch('api/extend-session.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.session-warning').remove();
                sessionStorage.setItem('session_start', Date.now());
            }
        });
}

// Utility function for file sharing
async function shareFile(fileId, usernames) {
    try {
        const response = await fetch('api/share-file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                file_id: fileId,
                users: usernames.split(',').map(u => u.trim())
            })
        });

        const result = await response.json();

        if (result.success) {
            return { success: true, message: 'File shared successfully' };
        } else {
            return { success: false, message: result.error };
        }
    } catch (error) {
        console.error('Share error:', error);
        return { success: false, message: 'Sharing failed' };
    }
}

// Utility function for file deletion
async function deleteFile(fileId) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }

    try {
        const response = await fetch('api/delete-file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ file_id: fileId })
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert('Delete failed: ' + result.error);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Delete failed');
    }
}