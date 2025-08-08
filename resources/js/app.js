// Set up CSRF token for AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    // Get the CSRF token from the meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (token) {
        // Set up axios/fetch defaults if using those libraries
        if (window.axios) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }

        // For vanilla fetch or other AJAX requests
        document.addEventListener('fetch', function(e) {
            const options = e.detail?.options || {};
            if (!options.headers) {
                options.headers = {};
            }
            options.headers['X-CSRF-TOKEN'] = token;
            e.detail.options = options;
        });

        // For jQuery if it's being used
        if (window.jQuery) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }
    }
});
