/**
 * Force redirect to checkout
 * Fix for navigation issues
 */
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Get all checkout links
        const checkoutLinks = document.querySelectorAll('a[href*="/checkout"]');
        
        checkoutLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const url = this.getAttribute('href');
                console.log('Redirecting to:', url);
                
                // Force navigation
                window.location.href = url;
                
                return false;
            });
        });
        
        console.log('Pricing redirect script loaded. Found', checkoutLinks.length, 'checkout links.');
    });
})();
