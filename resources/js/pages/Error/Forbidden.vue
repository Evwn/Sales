<template>
  <!-- Minimal invisible page - frontend error handling will show the toast -->
  <div style="display: none;"></div>
</template>

<script setup>
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();

onMounted(() => {
  // Show SweetAlert toast with the error message
  const message = page.props.status === 403 ? 
    (page.props.message || 'Access denied') : 
    'You do not have permission to access this page.';
  
  Swal.fire({
    icon: 'error',
    title: 'Access Denied',
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    background: '#fef2f2',
    color: '#dc2626'
  });

  // Redirect based on the context
  setTimeout(() => {
    if (page.props.message?.includes('POS')) {
      window.location.href = '/pos/login';
    } else if (page.props.message?.includes('Backoffice')) {
      window.location.href = '/login';
    } else {
      window.location.href = '/dashboard';
    }
  }, 1000);
});
</script>