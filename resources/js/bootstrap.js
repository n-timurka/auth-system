import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Ensure CSRF token is sent with every request
let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    // Fallback to cookie if meta tag is missing (Inertia apps often rely on cookie)
    // Axios usually handles X-XSRF-TOKEN automatically from cookie, but we can be explicit if needed.
    // However, Laravel's standard setup with Axios works out of the box with Cookies.
    // Let's rely on standard Axios behavior for Cookies first, but maybe just log if it's missing?
    // Actually, let's leave it as standard axios behavior unless testing shows issues.
    // But I will add the meta tag check as it's a common pattern in Laravel Blade apps, though Inertia relies on Cookies.
    // Since this is Inertia, we rely on the XSRF-TOKEN cookie.
}
