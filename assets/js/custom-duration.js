/**
 * Fix for video duration display
 *
 * Ensures video durations show in minutes as the lowest unit (1m instead of seconds)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Fix for video duration display, ensuring all durations show in minutes
    const fixDurationLabels = function() {
        document.querySelectorAll('.video-duration').forEach(durationEl => {
            const seconds = parseInt(durationEl.getAttribute('data-seconds') || '0', 10);
            const displayText = durationEl.textContent.trim();

            // Don't change the format - PHP already does that
            // Just make sure the element is visible
            durationEl.style.display = 'block';
            durationEl.style.visibility = 'visible';
            durationEl.style.opacity = '1';

            // If there's a seconds indicator or just a numeric value, display it as minutes
            if (displayText.endsWith('s') && seconds > 0) {
                // Extract the numeric part and convert to minutes
                const numericPart = displayText.replace('s', '');
                // Keep the same number but change unit to minutes
                durationEl.textContent = numericPart + 'm';
            } else if (!isNaN(parseInt(displayText)) && displayText.indexOf('m') === -1 && displayText.indexOf('h') === -1) {
                // If it's just a number without unit, add 'm' for minutes
                durationEl.textContent = displayText + 'm';
            }
        });
    };

    // Run duration fix on page load
    fixDurationLabels();

    // Also run after any AJAX content loads
    document.addEventListener('grid_items_added', function() {
        setTimeout(fixDurationLabels, 100);
    });
});