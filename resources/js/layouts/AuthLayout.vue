<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>
<style scoped>
.background{
    background-image: url('/images/Background.png');
    background-size: cover;
    background-position: center;
    height: 100vb;
    color: rgb(12, 2, 2);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;

}

</style>
<template>
    <div class="background">
    <AuthLayout :title="title" :description="description" class="mb-4 text-center text-sm font-medium text-green-600 overflow-hidden bg-white/40 backdrop-blur-sm p-4 rounded">
        <slot />
    </AuthLayout>
    </div>
</template>

<script lang="ts">
export default {
    name: 'AuthLayout'
};
</script>
