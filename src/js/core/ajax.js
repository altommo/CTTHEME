/**
 * CustomTube AJAX Handler
 * Centralized AJAX functionality for all API calls
 */

export class AjaxHandler {
    constructor(config = {}) {
        this.config = {
            ajaxUrl: config.ajaxUrl || (window.customtubeData?.ajaxurl || '/wp-admin/admin-ajax.php'),
            nonce: config.nonce || (window.customtubeData?.nonce || ''),
            debug: config.debug || (window.customtubeData?.debug || false),
            ...config
        };
    }

    /**
     * Base AJAX request method
     * @param {Object} options - Request options
     * @returns {Promise} Promise resolving to response data
     */
    async request(options = {}) {
        const defaultOptions = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin'
        };

        const config = { ...defaultOptions, ...options };
        
        // Add nonce to data if not present
        if (config.body instanceof URLSearchParams && !config.body.has('nonce')) {
            config.body.append('nonce', this.config.nonce);
        }

        try {
            if (this.config.debug) {
                console.log(`AJAX Request: Action=${options.action}, URL=${this.config.ajaxUrl}, Body=${config.body.toString()}`);
            }

            const response = await fetch(this.config.ajaxUrl, config);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error(`AJAX HTTP error! Status: ${response.status}, Response: ${errorText}`);
                throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
            }
            
            const data = await response.json();
            
            if (this.config.debug) {
                console.log('AJAX Response:', options.action, data);
            }
            
            return data;
        } catch (error) {
            console.error('AJAX request failed:', error);
            throw error;
        }
    }

    /**
     * Create URLSearchParams from object
     * @param {Object} data - Data object
     * @returns {URLSearchParams} URL search params
     */
    createParams(data) {
        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(data)) {
            // Handle arrays by appending multiple times with '[]' for PHP
            if (Array.isArray(value)) {
                value.forEach(item => params.append(`${key}[]`, item));
            } else {
                params.append(key, value);
            }
        }
        return params;
    }

    /**
     * Update video view count
     * @param {number} postId - Post ID
     * @returns {Promise} Promise resolving to response data
     */
    async updateViewCount(postId) {
        const data = this.createParams({
            action: 'customtube_update_view_count',
            post_id: postId,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'updateViewCount'
        });
    }

    /**
     * Toggle video like status
     * @param {number} postId - Post ID
     * @returns {Promise} Promise resolving to response data
     */
    async toggleLike(postId) {
        const data = this.createParams({
            action: 'customtube_toggle_like',
            post_id: postId,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'toggleLike'
        });
    }

    /**
     * Get filtered videos
     * @param {Object} filters - Filter parameters
     * @returns {Promise} Promise resolving to response data
     */
    async getFilteredVideos(filters = {}) {
        const data = this.createParams({
            action: 'customtube_filter_videos',
            ...filters,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'getFilteredVideos'
        });
    }

    /**
     * Load more videos for pagination
     * @param {number} page - Page number
     * @param {Object} filters - Additional filters
     * @returns {Promise} Promise resolving to response data
     */
    async loadMoreVideos(page, filters = {}) {
        const data = this.createParams({
            action: 'customtube_load_more_videos',
            page: page,
            ...filters,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'loadMoreVideos'
        });
    }

    /**
     * Get trending videos
     * @param {Object} params - Parameters (page, period, etc.)
     * @returns {Promise} Promise resolving to response data
     */
    async getTrendingVideos(params = {}) {
        const data = this.createParams({
            action: 'get_trending_videos',
            ...params,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'getTrendingVideos'
        });
    }

    /**
     * Get liked videos for current user
     * @param {Object} params - Parameters (page, sort, etc.)
     * @returns {Promise} Promise resolving to response data
     */
    async getLikedVideos(params = {}) {
        const data = this.createParams({
            action: 'get_liked_videos',
            ...params,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'getLikedVideos'
        });
    }

    /**
     * Get short videos
     * @param {Object} params - Parameters (page, sort, duration, category)
     * @returns {Promise} Promise resolving to response data
     */
    async getShortVideos(params = {}) {
        const data = this.createParams({
            action: 'get_short_videos',
            ...params,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'getShortVideos'
        });
    }

    /**
     * User login
     * @param {string} username - Username
     * @param {string} password - Password
     * @param {boolean} remember - Remember me checkbox
     * @returns {Promise} Promise resolving to response data
     */
    async userLogin(username, password, remember = false) {
        const data = this.createParams({
            action: 'custom_user_login',
            log: username,
            pwd: password,
            rememberme: remember ? '1' : '0',
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'userLogin'
        });
    }

    /**
     * User registration
     * @param {Object} userData - User registration data
     * @returns {Promise} Promise resolving to response data
     */
    async userRegister(userData) {
        const data = this.createParams({
            action: 'custom_user_register',
            ...userData,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'userRegister'
        });
    }

    /**
     * Search videos/content
     * @param {string} query - Search query
     * @param {Object} params - Additional search parameters
     * @returns {Promise} Promise resolving to response data
     */
    async search(query, params = {}) {
        const data = this.createParams({
            action: 'customtube_search',
            query: query,
            ...params,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'search'
        });
    }

    /**
     * Get video recommendations
     * @param {number} videoId - Current video ID
     * @param {number} count - Number of recommendations
     * @returns {Promise} Promise resolving to response data
     */
    async getRecommendations(videoId, count = 8) {
        const data = this.createParams({
            action: 'get_video_recommendations',
            video_id: videoId,
            count: count,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'getRecommendations'
        });
    }

    /**
     * Get personalized video recommendations from the backend.
     * @param {Array} watchedVideoIds - Array of video IDs the user has watched.
     * @param {number} limit - Number of recommendations to fetch.
     * @param {string} type - Type of recommendation (e.g., 'hero', 'because_you_watched').
     * @returns {Promise} Promise resolving to an array of recommended video data.
     */
    async getPersonalizedRecommendations(watchedVideoIds = [], limit = 5, type = 'hero') {
        const data = this.createParams({
            action: 'customtube_ajax_get_personalized_recommendations',
            watched_video_ids: watchedVideoIds,
            limit: limit,
            type: type,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'getPersonalizedRecommendations'
        });
    }

    /**
     * Track user behavior (e.g., video clicks/views).
     * @param {number} videoId - The ID of the video being interacted with.
     * @param {string} eventType - The type of event ('click', 'view', 'watch_complete').
     * @returns {Promise} Promise resolving to success/failure.
     */
    async trackUserBehavior(videoId, eventType) {
        const data = this.createParams({
            action: 'customtube_track_user_behavior', // This would be a new AJAX action in PHP
            video_id: videoId,
            event_type: eventType,
            nonce: this.config.nonce
        });

        return this.request({
            body: data,
            action: 'trackUserBehavior'
        });
    }

    /**
     * Generic WordPress AJAX helper
     * @param {string} action - WordPress action hook
     * @param {Object} data - Data to send
     * @returns {Promise} Promise resolving to response data
     */
    async wp(action, data = {}) {
        const params = this.createParams({
            action: action,
            ...data,
            nonce: this.config.nonce
        });

        return this.request({
            body: params,
            action: action
        });
    }
}

// Export singleton instance
export const ajax = new AjaxHandler();
