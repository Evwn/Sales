<template>
  <div class="receipt scrollable-receipt" ref="receiptRef">
    <!-- Front Side -->
    <div class="receipt-side front">
      <div class="receipt-header">
        <div v-if="businessLogo" class="business-logo center-logo">
          <img :src="businessLogo" :alt="businessDisplayName" crossorigin="anonymous" />
        </div>
        <h2 class="business-name">{{ businessDisplayName }}</h2>
        <p class="branch-name">{{ sale.branch?.name }}</p>
        <p class="receipt-type">SALES RECEIPT</p>
      </div>

      <div class="receipt-info">
        <div class="info-row">
          <span>Receipt No:</span>
          <span>{{ sale.receipt_reference || sale.reference }}</span>
        </div>
        <div class="info-row">
          <span>Date:</span>
          <span>{{ formatDate(sale.created_at) }}</span>
        </div>
        <div class="info-row">
          <span>Cashier:</span>
          <span>{{ sale.seller?.name || sale.cashier?.name }}</span>
        </div>
        <div class="info-row" v-if="sale.customer">
          <span>Customer:</span>
          <span>{{ sale.customer?.name }}</span>
        </div>
      </div>

      <div class="receipt-items">
        <div class="items-header">
          <span>Item</span>
          <span>Qty</span>
          <span>Price (KES)</span>
          <span>Total (KES)</span>
        </div>
        <div v-for="item in (sale.receipt_items || sale.items)" :key="item.id" class="item-row">
          <span>{{ item.product_name || item.product?.name || 'N/A' }}</span>
          <span>{{ item.quantity }}</span>
          <span>{{ Number(item.unit_price || item.price).toFixed(2) }}</span>
          <span>{{ Number(item.total || item.total_price || item.subtotal).toFixed(2) }}</span>
        </div>
      </div>

      <div class="receipt-totals">
        <div class="total-row">
          <span>Subtotal:</span>
          <span>{{ formatCurrency(sale.subtotal || sale.receipt_total || sale.total_amount) }}</span>
        </div>
        <div v-if="sale.tax && sale.tax > 0" class="total-row">
          <span>Tax:</span>
          <span>{{ formatCurrency(sale.tax) }}</span>
        </div>
        <div v-if="sale.discount && sale.discount > 0" class="total-row">
          <span>Discount:</span>
          <span>{{ formatCurrency(sale.discount) }}</span>
        </div>
        <div class="total-row">
          <span>Total:</span>
          <span>{{ formatCurrency(sale.total || sale.total_amount) }}</span>
        </div>
        <div class="total-row">
          <span>Payment Method:</span>
          <span>{{ sale.payment_method }}</span>
        </div>
      </div>

      <div class="receipt-barcode">
        <BarcodeDisplay :value="sale.receipt_barcode || sale.barcode" />
      </div>

      <div class="receipt-qr" style="text-align:center;margin:10px 0;">
        <qrcode-vue :value="receiptUrl" :size="80" />
        <div style="font-size:11px;word-break:break-all;">{{ receiptUrl }}</div>
      </div>

      <div class="receipt-footer">
        <p>{{ footerText }}</p>
        <p class="small">This receipt serves as proof of purchase</p>
      </div>
    </div>

    <!-- Back Side (if enabled) -->
    <div v-if="showBackSide" class="receipt-side back">
      <div class="back-content">
        <div v-if="businessLogo" class="business-logo watermark">
          <img :src="businessLogo" :alt="businessDisplayName" crossorigin="anonymous" />
        </div>
        <div class="terms">
          <h3>Terms & Conditions</h3>
          <p>{{ termsAndConditions }}</p>
        </div>
        <div class="contact-info">
          <p>{{ contactInformation }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import BarcodeDisplay from './BarcodeDisplay.vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
  sale: {
    type: Object,
    required: true
  },
  template: {
    type: String,
    default: 'default'
  },
  showBackSide: {
    type: Boolean,
    default: false
  }
});

const receiptRef = ref(null);

const businessLogo = computed(() => {
  const logoPath = props.sale.branch?.business?.logo_url || 
                   props.sale.business?.logo_url || 
                   props.sale.branch?.business?.logo_path || 
                   props.sale.business?.logo_path || '';
  
  if (!logoPath) return '';
  
  // If it's already a full URL, return as is
  if (logoPath.startsWith('http') || logoPath.startsWith('data:')) {
    return logoPath;
  }
  
  // If it starts with /storage, return as is
  if (logoPath.startsWith('/storage/')) {
    return logoPath;
  }
  
  // Otherwise, construct the full path
  return `/storage/${logoPath}`;
});

const businessDisplayName = computed(() => {
  return props.sale.branch?.business?.name || props.sale.business?.name || '';
});

const receiptUrl = computed(() => {
  const reference = props.sale.receipt_reference || props.sale.reference;
  return `${window.location.origin}/sales/receipt/${reference}`;
});

const footerText = computed(() => {
  return props.sale.branch?.business?.receipt_footer || 'Thank you for your purchase!';
});

const termsAndConditions = computed(() => {
  return props.sale.branch?.business?.terms_and_conditions || 'Standard terms and conditions apply.';
});

const contactInformation = computed(() => {
  return props.sale.branch?.business?.contact_information || 'For any queries, please contact us.';
});

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

const formatCurrency = (amount) => {
  return 'KES ' + Number(amount).toFixed(2);
};
</script>

<style scoped>
.receipt {
  width: 100mm;
  max-width: 100vw;
  margin: 0 auto;
  background: white;
  font-family: 'Courier New', Courier, monospace;
  font-size: 13px;
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 12px 8px;
}

.scrollable-receipt {
  max-height: 80vh;
  overflow-y: auto;
}

.receipt-side {
  padding: 1rem;
}

.receipt-header {
  text-align: center;
  margin-bottom: 1rem;
}

.business-logo {
  text-align: center;
  margin-bottom: 1rem;
}

.business-logo img {
  max-width: 60mm;
  max-height: 30mm;
  object-fit: contain;
}

.business-logo.watermark img {
  opacity: 0.1;
}

.business-name {
  font-size: 1.2rem;
  font-weight: bold;
}

.branch-name {
  font-size: 0.9rem;
  color: #666;
}

.receipt-type {
  font-size: 1.1rem;
  font-weight: bold;
  margin-top: 0.5rem;
}

.receipt-info {
  margin-bottom: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.25rem;
  word-break: keep-all;
}

.receipt-items {
  margin: 1rem 0;
}

.items-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  font-weight: bold;
  border-bottom: 1px solid #ddd;
  padding-bottom: 0.5rem;
  margin-bottom: 0.5rem;
  word-break: keep-all;
}

.item-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  margin-bottom: 0.25rem;
  word-break: keep-all;
}

.receipt-totals {
  margin: 1rem 0;
  border-top: 1px solid #ddd;
  padding-top: 0.5rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.25rem;
  word-break: keep-all;
}

.receipt-barcode {
  text-align: center;
  margin: 1rem 0;
}

.receipt-qr {
  text-align: center;
  margin: 10px 0;
}

.receipt-footer {
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
}

.small {
  font-size: 0.8rem;
  color: #666;
}

.back-content {
  padding: 1rem;
}

.terms {
  margin: 1rem 0;
}

.contact-info {
  margin-top: 1rem;
}

.center-logo {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 4px;
}

@media print {
  body {
    background: white !important;
  }
  .receipt {
    width: 100mm !important;
    max-width: 100vw !important;
    font-size: 13px !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    border: none !important;
    padding: 0 !important;
  }
  .scrollable-receipt {
    max-height: none !important;
    overflow: visible !important;
  }
  .receipt-side {
    padding: 0.5rem !important;
    min-height: 0 !important;
  }
  .items-header, .item-row, .info-row, .total-row {
    word-break: keep-all !important;
    white-space: nowrap !important;
  }
}
</style> 