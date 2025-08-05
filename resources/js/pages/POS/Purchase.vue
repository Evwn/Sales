<template>
  <div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-900">Payment Completion</h1>
        </div>
        
        <div v-if="ticket" class="p-6">
          <!-- Ticket Summary -->
          <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Ticket #{{ ticket.id }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="font-medium mb-2">Items</h3>
                <div class="space-y-2">
                  <div v-for="item in ticket.items" :key="item.id" class="flex justify-between">
                    <span>{{ item.name }}</span>
                    <span>KES {{ parseFloat(item.subtotal).toFixed(2) }}</span>
                  </div>
                </div>
              </div>
              <div>
                <h3 class="font-medium mb-2">Payment Summary</h3>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span>Total Amount:</span>
                    <span class="font-semibold">KES {{ parseFloat(ticket.total_amount).toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Amount Paid:</span>
                    <span class="text-green-600">KES {{ parseFloat(ticket.amount_paid).toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Amount Due:</span>
                    <span class="text-red-600 font-semibold">KES {{ parseFloat(ticket.amount_due).toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Status:</span>
                    <span :class="ticket.status === 'completed' ? 'text-green-600' : 'text-yellow-600'">
                      {{ ticket.status.toUpperCase() }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Panel -->
          <div v-if="parseFloat(ticket.amount_due) > 0" class="mt-8">
            <PaymentPanel 
              :total="parseFloat(ticket.amount_due)" 
              :mode="'recall'"
              :initial-splits="existingPayments"
              @complete="handlePaymentComplete"
              @back="goBack"
              @partial-update="handlePartialUpdate"
            />
          </div>
          
          <div v-else class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span class="text-green-800 font-medium">Payment completed successfully!</span>
            </div>
          </div>
        </div>
        
        <div v-else class="p-6 text-center">
          <div class="text-gray-500">Loading ticket...</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { usePage, router } from '@inertiajs/vue3';
import PaymentPanel from '@/components/POS/PaymentPanel.vue';

const page = usePage();
const ticketId = page.props.id;

const ticket = ref(null);
const paymentError = ref('');

// Convert existing payments to split format for PaymentPanel
const existingPayments = computed(() => {
  if (!ticket.value || !ticket.value.payments) return [];
  
  return ticket.value.payments.map(payment => ({
    id: payment.id,
    amount: parseFloat(payment.amount).toFixed(2),
    paymentMethod: payment.method,
    isCompleted: payment.status === 'completed',
    cashReceived: payment.meta?.cashReceived || '',
    cardNumber: payment.meta?.cardNumber || '',
    cardExpiry: payment.meta?.cardExpiry || '',
    cardCvv: payment.meta?.cardCvv || '',
    mpesaPhone: payment.meta?.mpesaPhone || '',
    mpesaManualMode: payment.meta?.mpesaManualMode || false,
    mpesaTransactionCode: payment.meta?.mpesaTransactionCode || '',
    mpesaAmountReceived: payment.meta?.mpesaAmountReceived || ''
  }));
});

async function fetchTicket() {
  try {
    const { data } = await axios.get(`/pos/ticket/${ticketId}`);
    ticket.value = data;
  } catch (error) {
    console.error('Error fetching ticket:', error);
    console.error('Error response:', error.response);
    paymentError.value = 'Failed to load ticket';
  }
}

async function handlePaymentComplete(paymentData) {
  try {
    // Update the ticket with new payment information
    const response = await axios.post(`/pos/ticket/${ticketId}/update-payment`, {
      payments: paymentData.splits.map(split => ({
        method: split.paymentMethod,
        amount: parseFloat(split.amount),
        status: split.isCompleted ? 'completed' : 'pending',
        meta: {
          cashReceived: split.cashReceived,
          cardNumber: split.cardNumber,
          cardExpiry: split.cardExpiry,
          cardCvv: split.cardCvv,
          mpesaPhone: split.mpesaPhone,
          mpesaManualMode: split.mpesaManualMode,
          mpesaTransactionCode: split.mpesaTransactionCode,
          mpesaAmountReceived: split.mpesaAmountReceived
        }
      }))
    });
    
    if (response.data.success) {
      // Refresh ticket data
      await fetchTicket();
    }
  } catch (error) {
    console.error('Error updating payment:', error);
    paymentError.value = 'Failed to update payment';
  }
}

async function handlePartialUpdate(paymentData) {
  try {
    // Update the ticket with new payment information (partial)
    await axios.post(`/pos/ticket/${ticketId}/update-payment`, {
      payments: paymentData.splits.map(split => ({
        method: split.paymentMethod,
        amount: parseFloat(split.amount),
        status: split.isCompleted ? 'completed' : 'pending',
        meta: {
          cashReceived: split.cashReceived,
          cardNumber: split.cardNumber,
          cardExpiry: split.cardExpiry,
          cardCvv: split.cardCvv,
          mpesaPhone: split.mpesaPhone,
          mpesaManualMode: split.mpesaManualMode,
          mpesaTransactionCode: split.mpesaTransactionCode,
          mpesaAmountReceived: split.mpesaAmountReceived
        }
      }))
    });
    // Refresh ticket data
    await fetchTicket();
  } catch (error) {
    paymentError.value = 'Failed to update payment (partial)';
  }
}

function goBack() {
  router.visit('/pos/dashboard');
}

onMounted(fetchTicket);
</script> 