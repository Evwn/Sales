<template>
  <div class="barcode-scanner">
    <div class="scanner-container">
      <video ref="videoRef" class="scanner-video" autoplay playsinline></video>
      <div class="scanner-overlay"></div>
    </div>
    <div class="scanner-controls">
      <button @click="startScanner" class="btn btn-primary" :disabled="isScanning">
        Start Scanner
      </button>
      <button @click="stopScanner" class="btn btn-secondary" :disabled="!isScanning">
        Stop Scanner
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { BrowserMultiFormatReader } from '@zxing/library';

const emit = defineEmits<{
  (e: 'barcode-detected', barcode: string): void;
}>();

const videoRef = ref<HTMLVideoElement | null>(null);
const isScanning = ref(false);
let codeReader: BrowserMultiFormatReader | null = null;

const startScanner = async () => {
  if (!videoRef.value) return;
  
  try {
    codeReader = new BrowserMultiFormatReader();
    isScanning.value = true;
    
    const videoInputDevices = await BrowserMultiFormatReader.listVideoInputDevices();
    const selectedDeviceId = videoInputDevices[0]?.deviceId;
    
    if (selectedDeviceId) {
      await codeReader.decodeFromVideoDevice(
        selectedDeviceId,
        videoRef.value,
        (result) => {
          if (result) {
            emit('barcode-detected', result.getText());
            stopScanner();
          }
        }
      );
    } else {
      throw new Error('No camera found');
    }
  } catch (error) {
    console.error('Error starting scanner:', error);
    isScanning.value = false;
  }
};

const stopScanner = () => {
  if (codeReader) {
    codeReader.reset();
    isScanning.value = false;
  }
};

onUnmounted(() => {
  stopScanner();
});
</script>

<style scoped>
.barcode-scanner {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1rem;
  background: #fff;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.scanner-container {
  position: relative;
  width: 100%;
  max-width: 640px;
  margin: 0 auto;
  aspect-ratio: 4/3;
  overflow: hidden;
  border-radius: 0.25rem;
  background: #000;
}

.scanner-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.scanner-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border: 2px solid rgba(255, 255, 255, 0.5);
  border-radius: 0.25rem;
}

.scanner-controls {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.25rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #4f46e5;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #4338ca;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover:not(:disabled) {
  background: #d1d5db;
}
</style> 