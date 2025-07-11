/**
 * Admin JavaScript Module
 * Handles WordPress admin-specific functionality
 */

// Simple admin module for now - will be expanded as needed
class CustomTubeAdmin {
    constructor() {
        this.isInitialized = false;
    }

    init() {
        if (this.isInitialized) {
            return;
        }

        console.log('CustomTube Admin: Initializing...');

        // Auto-detect functionality if it exists
        this.initAutoDetect();

        // Other admin features can be added here

        this.isInitialized = true;
        console.log('CustomTube Admin: Initialization complete');
    }

    /**
     * Initialize auto-detect functionality from existing admin files
     */
    initAutoDetect() {
        // Check if auto-detect elements exist (from existing auto-detect.js)
        const autoDetectButton = document.querySelector('#auto-detect-btn');
        const progressDiv = document.querySelector('#auto-detect-progress');

        if (autoDetectButton && progressDiv) {
            // This functionality exists in assets/js/admin/auto-detect.js
            // For now, we'll leave the original file intact and reference it
            console.log('CustomTube Admin: Auto-detect functionality detected');
        }
    }

    cleanup() {
        this.isInitialized = false;
        console.log('CustomTube Admin: Cleaned up');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.CustomTubeAdmin = new CustomTubeAdmin();
    window.CustomTubeAdmin.init();
});

export default CustomTubeAdmin;
