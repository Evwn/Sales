import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import axios from 'axios';
import Swal from 'sweetalert2';

const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

const appName = import.meta.env.VITE_APP_NAME || 'Sales Management System';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Function to show SweetAlert toast for errors
function showErrorToast(status, message) {
    console.log('Showing error toast:', status, message);
    
    let title = 'Error';
    let text = message || 'An unexpected error occurred.';
    let icon = 'error';
    let background = '#fef2f2';
    let color = '#dc2626';
    
    switch (status) {
        case 403:
            title = 'Access Denied';
            text = message || 'You do not have permission to access this area.';
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
            text = message || 'Please check your input and try again.';
            break;
        case 500:
            title = 'Server Error';
            text = 'Something went wrong on our end. Please try again later.';
            break;
        default:
            title = 'Error';
            text = message || 'An unexpected error occurred.';
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

// Global axios interceptor to catch HTTP errors
axios.interceptors.response.use(
    response => response,
    error => {
        console.log('Axios error caught:', error);
        if (error.response) {
            const status = error.response.status;
            
            // Try to extract custom error message from Laravel's abort response
            let message = 'Forbidden';
            
            // Check if response is HTML (Laravel's default error page)
            if (typeof error.response.data === 'string' && error.response.data.includes('<!DOCTYPE html>')) {
                // Extract message from HTML content
                const htmlContent = error.response.data;
                
                // Look for the custom message in the HTML
                const messageMatch = htmlContent.match(/Backoffice access only|POS access only|Access denied|Unauthorized/);
                if (messageMatch) {
                    message = messageMatch[0];
                } else {
                    // Fallback: extract from the div containing the message
                    const divMatch = htmlContent.match(/<div[^>]*class="[^"]*text-gray-500[^"]*"[^>]*>([^<]+)<\/div>/);
                    if (divMatch && divMatch[1]) {
                        message = divMatch[1].trim();
                    }
                }
            } else {
                // Handle JSON responses
                if (error.response.data?.message) {
                    message = error.response.data.message;
                } else if (error.response.data?.error) {
                    message = error.response.data.error;
                } else if (error.response.data?.errors && typeof error.response.data.errors === 'string') {
                    message = error.response.data.errors;
                } else if (error.response.data && typeof error.response.data === 'string') {
                    message = error.response.data;
                } else if (error.response.statusText) {
                    message = error.response.statusText;
                }
            }
            
            showErrorToast(status, message);
            
            // For 403 errors, prevent the default error page from showing
            if (status === 403) {
                // Stop the error from propagating
                return Promise.resolve({ data: null, status: 200 });
            }
        }
        return Promise.reject(error);
    }
);

// Inertia router error handler - catch navigation errors
router.on('error', (errors) => {
    console.log('Inertia router error caught:', errors);
    
    // Handle different types of errors
    if (errors.status) {
        showErrorToast(errors.status, errors.message);
        
        // For 403 errors, prevent navigation and stay on current page
        if (errors.status === 403) {
            // Cancel the navigation
            errors.preventDefault();
            return false;
        }
    } else if (errors.message) {
        showErrorToast(500, errors.message);
    } else {
        showErrorToast(500, 'An unexpected error occurred');
    }
});

// Catch unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
    console.log('Unhandled promise rejection:', event);
    
    if (event.reason && event.reason.response) {
        const status = event.reason.response.status;
        const message = event.reason.response.data?.message || event.reason.response.statusText;
        showErrorToast(status, message);
        
        // For 403 errors, prevent default handling
        if (status === 403) {
            event.preventDefault();
            return false;
        }
    } else if (event.reason && event.reason.message) {
        showErrorToast(500, event.reason.message);
    }
    
    // Prevent the default browser error handling
    event.preventDefault();
});

// Catch global errors
window.addEventListener('error', (event) => {
    console.log('Global error caught:', event);
    
    if (event.error && event.error.response) {
        const status = event.error.response.status;
        const message = event.error.response.data?.message || event.error.response.statusText;
        showErrorToast(status, message);
        
        // For 403 errors, prevent default handling
        if (status === 403) {
            event.preventDefault();
            return false;
        }
    }
});

// Prevent default error page for 403 errors
window.addEventListener('beforeunload', (event) => {
    // This is a fallback to prevent navigation on 403 errors
    if (event.target.location && event.target.location.pathname === '/login') {
        // Check if we're trying to navigate to login due to 403
        event.preventDefault();
        return false;
    }
}); 