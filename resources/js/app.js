import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import axios from 'axios';
import Swal from 'sweetalert2';
import { usePage } from '@inertiajs/vue3';

const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}
const page = usePage();
const appName =import.meta.env.VITE_APP_NAME || 'Sales Management System';
console.log(page.props);
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#ffffffff',
        delay: 250,
        includeCSS: true,
        showSpinner: true,
    },
});

// Function to show SweetAlert toast for errors
function showErrorToast(status, message) {
    
    let title = 'Error';
    let text = message || 'An unexpected error occurred.';
    let icon = 'error';
    let background = '#fef2f2';
    let color = '#dc2626';
    
    switch (status) {
        case 403:
            title = 'Access Denied';
            text = 'You do not have permission to access this area.';
            break;
        case 401:
            title = 'Unauthorized';
            text = 'You are not authenticated. Please login first.';
                        setTimeout(() => {
                window.location.route('home');
            }, 2000);
            break;            
        case 429:
            title = 'Many Requests';
            text = 'Too many requests. Please slow down.' || message ;
            break;    
        case 404:
            title = 'Page Not Found';
            text = 'The page you are looking for does not exist.';
            break;
        case 409:
            title = 'Email Verification Required';
            text = 'Please verify your email address to continue.';
            // Redirect to email verification
            setTimeout(() => {
                window.location.href = '/verify-email';
            }, 2000);
            break;
        case 419:
            title = 'Page Expired';
            text = 'The page has expired. Please refresh and try again.';
            icon = 'warning';
            background = '#fffbeb';
            color = '#d97706';
            // Auto refresh after showing toast
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            break;
        case 422:
            title = 'Validation Error';
            text ='Please check your input and try again.';
            break;
        case 503:
            title = 'System Maintenance';
            text = 'System under Maintenance. Please try again later.';
            break;    
        case 500:
            title = 'Server Error';
            text = 'Something went wrong on our end. Please try again later.';
            break;
        default:
            title = 'Error';
            text = 'An unexpected error occurred.';
    }
    
    // Show SweetAlert toast
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        background: background,
        color: color
    });
}

axios.interceptors.response.use(
  response => response,
  error => {
    const status = error.response?.status || 500;
    const message = error.response?.data?.message || error.message;

    showErrorToast(status, message);

    return Promise.resolve({ data: null, status }); 
  }
);
