/**
 * Brief Show Page JavaScript
 * ==========================
 * 
 * Handles interactions and functionality for the brief show page.
 * Includes document handling, file type detection, and user interactions.
 */

class BriefShow {
    constructor() {
        this.init();
    }

    /**
     * Initialize the brief show functionality
     */
    init() {
        this.bindEvents();
        this.initTooltips();
        this.handleResponsiveLayout();
        this.initLazyLoading();
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        // PDF download handler
        this.bindPdfDownload();
        
        // Document preview handlers
        this.bindDocumentPreview();
        
        // Image lightbox handlers
        this.bindImageLightbox();
        
        // Copy to clipboard handlers
        this.bindCopyToClipboard();
        
        // Responsive menu handlers
        this.bindResponsiveMenu();
    }

    /**
     * Handle PDF download functionality
     */
    bindPdfDownload() {
        const pdfButtons = document.querySelectorAll('[data-action="download-pdf"]');
        
        pdfButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const url = button.dataset.url;
                
                if (url) {
                    // Open PDF in new tab
                    window.open(url, '_blank');
                    
                    // Track download event if analytics available
                    this.trackEvent('brief_pdf_download', {
                        brief_id: button.dataset.briefId,
                        brief_type: button.dataset.briefType
                    });
                } else {
                    console.error('PDF URL not found');
                    this.showNotification('Ошибка при загрузке PDF', 'error');
                }
            });
        });
    }

    /**
     * Handle document preview functionality
     */
    bindDocumentPreview() {
        const documentLinks = document.querySelectorAll('.document-link');
        
        documentLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const fileType = this.getFileType(link.href);
                
                // Track document view
                this.trackEvent('document_view', {
                    file_type: fileType,
                    file_name: link.dataset.fileName || 'unknown'
                });
            });
        });
    }

    /**
     * Handle image lightbox functionality
     */
    bindImageLightbox() {
        const imageLinks = document.querySelectorAll('.reference-image-link');
        
        imageLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.openImageLightbox(link.href, link.querySelector('img')?.alt || 'Референс');
            });
        });
    }

    /**
     * Handle copy to clipboard functionality
     */
    bindCopyToClipboard() {
        const copyButtons = document.querySelectorAll('[data-action="copy"]');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                const text = button.dataset.text || button.nextElementSibling?.textContent;
                
                try {
                    await navigator.clipboard.writeText(text);
                    this.showNotification('Скопировано в буфер обмена', 'success');
                } catch (err) {
                    console.error('Failed to copy text: ', err);
                    this.showNotification('Ошибка при копировании', 'error');
                }
            });
        });
    }

    /**
     * Handle responsive menu functionality
     */
    bindResponsiveMenu() {
        const menuToggle = document.querySelector('[data-action="toggle-menu"]');
        const menu = document.querySelector('.button-group');
        
        if (menuToggle && menu) {
            menuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                menu.classList.toggle('show');
            });
        }
    }

    /**
     * Initialize tooltips
     */
    initTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    /**
     * Handle responsive layout adjustments
     */
    handleResponsiveLayout() {
        const resizeHandler = () => {
            const windowWidth = window.innerWidth;
            
            // Adjust document grid columns based on screen size
            const documentGrids = document.querySelectorAll('.documents-grid, .references-grid');
            documentGrids.forEach(grid => {
                if (windowWidth <= 576) {
                    grid.style.gridTemplateColumns = '1fr';
                } else if (windowWidth <= 768) {
                    grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(200px, 1fr))';
                } else {
                    grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(250px, 1fr))';
                }
            });
        };

        window.addEventListener('resize', resizeHandler);
        resizeHandler(); // Initial call
    }

    /**
     * Initialize lazy loading for images
     */
    initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });

            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    /**
     * Get file type from URL
     * @param {string} url - File URL
     * @returns {string} - File type
     */
    getFileType(url) {
        const extension = url.split('.').pop().toLowerCase();
        const fileTypes = {
            'pdf': 'pdf',
            'doc': 'document',
            'docx': 'document',
            'xls': 'spreadsheet',
            'xlsx': 'spreadsheet',
            'jpg': 'image',
            'jpeg': 'image',
            'png': 'image',
            'gif': 'image',
            'heic': 'image',
            'mp4': 'video',
            'mov': 'video',
            'avi': 'video',
            'wmv': 'video'
        };
        
        return fileTypes[extension] || 'unknown';
    }

    /**
     * Open image in lightbox
     * @param {string} src - Image source
     * @param {string} alt - Image alt text
     */
    openImageLightbox(src, alt) {
        // Create lightbox overlay
        const overlay = document.createElement('div');
        overlay.className = 'image-lightbox-overlay';
        overlay.innerHTML = `
            <div class="lightbox-content">
                <img src="${src}" alt="${alt}" class="lightbox-image">
                <button class="lightbox-close" aria-label="Закрыть">&times;</button>
                <div class="lightbox-caption">${alt}</div>
            </div>
        `;

        // Add styles if not already present
        if (!document.querySelector('#lightbox-styles')) {
            const styles = document.createElement('style');
            styles.id = 'lightbox-styles';
            styles.textContent = `
                .image-lightbox-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                }
                .lightbox-content {
                    position: relative;
                    max-width: 90%;
                    max-height: 90%;
                }
                .lightbox-image {
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                }
                .lightbox-close {
                    position: absolute;
                    top: -40px;
                    right: 0;
                    background: none;
                    border: none;
                    color: white;
                    font-size: 30px;
                    cursor: pointer;
                    padding: 5px;
                }
                .lightbox-caption {
                    position: absolute;
                    bottom: -40px;
                    left: 0;
                    right: 0;
                    color: white;
                    text-align: center;
                    font-size: 14px;
                }
            `;
            document.head.appendChild(styles);
        }

        // Add to page
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        // Close handlers
        const closeHandler = () => {
            document.body.removeChild(overlay);
            document.body.style.overflow = '';
        };

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeHandler();
        });

        overlay.querySelector('.lightbox-close').addEventListener('click', closeHandler);

        // ESC key handler
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closeHandler();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    /**
     * Show notification to user
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, info)
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                ${message}
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Add styles if not already present
        if (!document.querySelector('#notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    border-radius: 6px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    z-index: 1000;
                    max-width: 300px;
                    animation: slideIn 0.3s ease;
                }
                .notification-success { border-left: 4px solid #28a745; }
                .notification-error { border-left: 4px solid #dc3545; }
                .notification-info { border-left: 4px solid #17a2b8; }
                .notification-content {
                    padding: 15px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    color: #999;
                    margin-left: 10px;
                }
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(styles);
        }

        // Add to page
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);

        // Close button handler
        notification.querySelector('.notification-close').addEventListener('click', () => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        });
    }

    /**
     * Track event for analytics
     * @param {string} eventName - Event name
     * @param {object} properties - Event properties
     */
    trackEvent(eventName, properties = {}) {
        // Google Analytics tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, properties);
        }

        // Console log for development
        if (process.env.NODE_ENV === 'development') {
            console.log('Event tracked:', eventName, properties);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BriefShow();
});

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BriefShow;
}
