<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    maxWidth?: string;
    closeable?: boolean;
    backdrop?: boolean;
}>();

const emit = defineEmits(['close']);

watch(() => props.show, () => {
    if (props.show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = '';
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth || '2xl'];
});
</script>

<template>
    <teleport to="body">
        <div v-show="show" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div
                v-if="props.backdrop !== false"
                class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity z-50"
                @click="close"
            ></div>
            <!-- Modal Content -->
            <div
                class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg z-60"
                :class="maxWidthClass"
            >
                <slot />
            </div>
        </div>
    </teleport>
</template> 
