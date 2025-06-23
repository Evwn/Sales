<template>
  <div class="barcode-display">
    <div class="barcode-container">
      <img v-if="barcodeUrl && barcodeUrl !== '/products' && barcodeUrl !== ''" :src="barcodeUrl" :alt="barcode" class="barcode-image" />
    </div>
    <div class="barcode-text">{{ barcode }}</div>
    <div class="barcode-actions">
      <button @click="copyBarcode" class="btn btn-secondary">
        Copy Barcode
      </button>
      <button @click="downloadBarcode" class="btn btn-primary">
        Download
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import JsBarcode from 'jsbarcode';

const props = defineProps<{
    value: string;
    format?: string;
    width?: number;
    height?: number;
    displayValue?: boolean;
    fontSize?: number;
    fontOptions?: string;
    font?: string;
    textAlign?: string;
    textPosition?: string;
    textMargin?: number;
    background?: string;
    lineColor?: string;
    margin?: number;
}>();

const barcodeRef = ref<HTMLElement | null>(null);

onMounted(() => {
    if (barcodeRef.value) {
        JsBarcode(barcodeRef.value, props.value, {
            format: props.format || 'CODE128',
            width: props.width || 2,
            height: props.height || 100,
            displayValue: props.displayValue !== false,
            fontSize: props.fontSize || 20,
            fontOptions: props.fontOptions || 'bold',
            font: props.font || 'monospace',
            textAlign: props.textAlign || 'center',
            textPosition: props.textPosition || 'bottom',
            textMargin: props.textMargin || 2,
            background: props.background || '#ffffff',
            lineColor: props.lineColor || '#000000',
            margin: props.margin || 10
        });
    }
});

const barcodeUrl = ref('');

const copyBarcode = () => {
  navigator.clipboard.writeText(props.value);
};

const downloadBarcode = () => {
  const link = document.createElement('a');
  link.download = `barcode-${props.value}.png`;
  link.href = barcodeUrl.value;
  link.click();
};

defineExpose({
  barcodeUrl,
  copyBarcode,
  downloadBarcode
});
</script>

<style scoped>
.barcode-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #fff;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.barcode-container {
  padding: 1rem;
  background: #fff;
  border-radius: 0.25rem;
}

.barcode-image {
  max-width: 100%;
  height: auto;
}

.barcode-text {
  font-family: monospace;
  font-size: 1.25rem;
  color: #374151;
}

.barcode-actions {
  display: flex;
  gap: 0.5rem;
}
</style> 
