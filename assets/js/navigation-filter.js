/**
 * Navigation and Filtering System JavaScript
 * 
 * Handles interactivity for the Lustful Clips navigation and filtering interface
 */

(function() {
  'use strict';
  
  // DOM Elements
  const navSide = document.querySelector('.nav-side');
  const navSections = document.querySelectorAll('.nav-section');
  const menuToggle = document.querySelector('.menu-toggle');
  const navOverlay = document.querySelector('.nav-overlay');
  const themeToggle = document.querySelector('.theme-toggle');
  const durationSlider = document.querySelector('.duration-slider');
  const durationValue = document.querySelector('.duration-value');
  const categoryItems = document.querySelectorAll('.nav-category-item');
  const searchInput = document.querySelector('.search-input');
  
  // Initialize
  function init() {
    setupEventListeners();
    setupDurationSlider();
    
    // Check if there's an active filter from URL params
    checkActiveFiltersFromURL();
  }
  
  // Setup Event Listeners
  function setupEventListeners() {
    // Toggle sections
    navSections.forEach(section => {
      const header = section.querySelector('.nav-section-header');
      header.addEventListener('click', () => toggleSection(section));
      
      // Accessibility
      header.setAttribute('role', 'button');
      header.setAttribute('aria-expanded', section.classList.contains('expanded') ? 'true' : 'false');
      header.setAttribute('tabindex', '0');
      
      // Support keyboard navigation
      header.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggleSection(section);
        }
      });
    });
    
    // Mobile menu toggle
    if (menuToggle) {
      menuToggle.addEventListener('click', toggleMobileMenu);
    }
    
    // Overlay close
    if (navOverlay) {
      navOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Theme toggle
    if (themeToggle) {
      themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Category item click
    categoryItems.forEach(item => {
      item.addEventListener('click', (e) => handleCategoryClick(e, item));
    });
    
    // Search input
    if (searchInput) {
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          handleSearch(searchInput.value);
        }
      });
    }
    
    // Handle window resize
    window.addEventListener('resize', handleResize);
  }
  
  // Toggle Section Expanded State
  function toggleSection(section) {
    // Toggle expanded class
    section.classList.toggle('expanded');
    
    // Update ARIA attribute
    const header = section.querySelector('.nav-section-header');
    header.setAttribute('aria-expanded', section.classList.contains('expanded') ? 'true' : 'false');
    
    // Store state in localStorage for persistence
    const sectionId = section.getAttribute('data-section-id');
    if (sectionId) {
      const expandedSections = JSON.parse(localStorage.getItem('expandedSections') || '{}');
      expandedSections[sectionId] = section.classList.contains('expanded');
      localStorage.setItem('expandedSections', JSON.stringify(expandedSections));
    }
  }
  
  // Toggle Mobile Menu
  function toggleMobileMenu() {
    navSide.classList.toggle('active');
    if (navOverlay) {
      navOverlay.classList.toggle('active');
    }
    
    // Prevent body scrolling when menu is open
    document.body.style.overflow = navSide.classList.contains('active') ? 'hidden' : '';
    
    // Update ARIA attributes
    menuToggle.setAttribute('aria-expanded', navSide.classList.contains('active') ? 'true' : 'false');
  }
  
  // Close Mobile Menu
  function closeMobileMenu() {
    navSide.classList.remove('active');
    if (navOverlay) {
      navOverlay.classList.remove('active');
    }
    
    // Re-enable body scrolling
    document.body.style.overflow = '';
    
    // Update ARIA attributes
    if (menuToggle) {
      menuToggle.setAttribute('aria-expanded', 'false');
    }
  }
  
  // Toggle Theme (Dark/Light)
  function toggleTheme() {
    const isDarkMode = document.body.classList.contains('dark-mode');
    document.body.classList.toggle('dark-mode');
    
    // Update the icon
    const themeIcon = themeToggle.querySelector('i');
    if (themeIcon) {
      themeIcon.className = isDarkMode ? 'fas fa-moon' : 'fas fa-sun';
    }
    
    // Store preference
    localStorage.setItem('theme', isDarkMode ? 'light' : 'dark');
    
    // Update ARIA label
    themeToggle.setAttribute('aria-label', isDarkMode ? 'Switch to dark mode' : 'Switch to light mode');
  }
  
  // Setup Duration Slider
  function setupDurationSlider() {
    if (!durationSlider) return;
    
    // Update value display on change
    durationSlider.addEventListener('input', () => {
      updateDurationDisplay();
    });
    
    // Apply filter when slider stops
    durationSlider.addEventListener('change', () => {
      applyDurationFilter();
    });
    
    // Initial display update
    updateDurationDisplay();
  }
  
  // Update Duration Display
  function updateDurationDisplay() {
    if (!durationSlider || !durationValue) return;
    
    const value = durationSlider.value;
    let displayText = '';
    
    // Format the display based on the range value
    if (value == 0) {
      displayText = 'Any length';
    } else if (value <= 5) {
      displayText = 'Under 5m';
    } else if (value <= 10) {
      displayText = 'Under 10m';
    } else if (value <= 20) {
      displayText = 'Under 20m';
    } else {
      displayText = 'Over 20m';
    }
    
    durationValue.textContent = displayText;
  }
  
  // Apply Duration Filter
  function applyDurationFilter() {
    if (!durationSlider) return;
    
    const value = durationSlider.value;
    const maxDuration = value > 0 ? value * 60 : 0; // Convert minutes to seconds
    
    // Show loading state
    showLoading();
    
    // Update URL parameter
    const currentUrl = new URL(window.location);
    if (maxDuration > 0) {
      currentUrl.searchParams.set('duration', value);
    } else {
      currentUrl.searchParams.delete('duration');
    }
    
    // Update URL without refresh
    window.history.pushState({}, '', currentUrl);
    
    // AJAX request to filter videos
    fetchFilteredContent({ duration: maxDuration });
  }
  
  // Handle Category Click
  function handleCategoryClick(e, item) {
    e.preventDefault();
    
    // Remove active state from all items
    categoryItems.forEach(i => i.classList.remove('active'));
    
    // Add active state to clicked item
    item.classList.add('active');
    
    // Get category data
    const categoryId = item.getAttribute('data-category-id');
    const categoryType = item.getAttribute('data-category-type');
    
    // Show loading state
    showLoading();
    
    // Update URL parameter
    const currentUrl = new URL(window.location);
    
    // Remove existing category parameters
    ['genre', 'tag', 'performer', 'duration'].forEach(param => {
      currentUrl.searchParams.delete(param);
    });
    
    // Add new category parameter
    if (categoryId) {
      currentUrl.searchParams.set(categoryType, categoryId);
    }
    
    // Update URL without refresh
    window.history.pushState({}, '', currentUrl);
    
    // AJAX request to filter videos
    const filterData = {};
    filterData[categoryType] = categoryId;
    fetchFilteredContent(filterData);
    
    // Close mobile menu if open
    if (window.innerWidth < 768) {
      closeMobileMenu();
    }
  }
  
  // Handle Search
  function handleSearch(query) {
    if (!query) return;
    
    // Show loading state
    showLoading();
    
    // Update URL parameter
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('s', query);
    
    // Update URL without refresh
    window.history.pushState({}, '', currentUrl);
    
    // AJAX request to search videos
    fetchFilteredContent({ s: query });
  }
  
  // Check Active Filters from URL
  function checkActiveFiltersFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check for category filters
    ['genre', 'tag', 'performer'].forEach(param => {
      const value = urlParams.get(param);
      if (value) {
        const item = document.querySelector(`.nav-category-item[data-category-type="${param}"][data-category-id="${value}"]`);
        if (item) {
          // Add active class
          categoryItems.forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          
          // Expand parent section
          const section = item.closest('.nav-section');
          if (section && !section.classList.contains('expanded')) {
            toggleSection(section);
          }
        }
      }
    });
    
    // Check for duration filter
    const duration = urlParams.get('duration');
    if (duration && durationSlider) {
      durationSlider.value = duration;
      updateDurationDisplay();
    }
    
    // Check for search query
    const searchQuery = urlParams.get('s');
    if (searchQuery && searchInput) {
      searchInput.value = searchQuery;
    }
  }
  
  // Fetch Filtered Content
  function fetchFilteredContent(filters) {
    // AJAX request to get filtered content
    const xhr = new XMLHttpRequest();
    xhr.open('POST', customtube_settings.ajax_url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            // Update the content area
            const contentArea = document.querySelector('.video-grid');
            if (contentArea && response.data.html) {
              contentArea.innerHTML = response.data.html;
            }
            
            // Update counts if provided
            if (response.data.counts) {
              updateCategoryCountDisplay(response.data.counts);
            }
          } else {
            console.error('Error fetching filtered content:', response.data);
          }
        } catch (e) {
          console.error('Error parsing AJAX response:', e);
        }
      } else {
        console.error('AJAX request failed');
      }
      
      // Hide loading state
      hideLoading();
    };
    
    xhr.onerror = function() {
      console.error('Network error during AJAX request');
      hideLoading();
    };
    
    // Prepare data
    const data = new URLSearchParams();
    data.append('action', 'customtube_filter_videos');
    data.append('nonce', customtube_settings.nonce);
    
    // Add all filters
    Object.keys(filters).forEach(key => {
      if (filters[key]) {
        data.append(key, filters[key]);
      }
    });
    
    // Send the request
    xhr.send(data.toString());
  }
  
  // Update Category Count Display
  function updateCategoryCountDisplay(counts) {
    // Update the count badges on categories
    Object.keys(counts).forEach(type => {
      Object.keys(counts[type]).forEach(id => {
        const count = counts[type][id];
        const countElement = document.querySelector(`.nav-category-item[data-category-type="${type}"][data-category-id="${id}"] .nav-category-count`);
        if (countElement) {
          countElement.textContent = count;
        }
      });
    });
  }
  
  // Show Loading State
  function showLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
      overlay.classList.add('active');
    } else {
      // Create overlay if it doesn't exist
      const newOverlay = document.createElement('div');
      newOverlay.className = 'loading-overlay active';
      
      const spinner = document.createElement('div');
      spinner.className = 'loading-spinner';
      newOverlay.appendChild(spinner);
      
      document.body.appendChild(newOverlay);
    }
  }
  
  // Hide Loading State
  function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
      overlay.classList.remove('active');
    }
  }
  
  // Handle Window Resize
  function handleResize() {
    if (window.innerWidth >= 768 && navSide && navSide.classList.contains('active')) {
      closeMobileMenu();
    }
  }
  
  // DOM Ready
  document.addEventListener('DOMContentLoaded', init);
})();