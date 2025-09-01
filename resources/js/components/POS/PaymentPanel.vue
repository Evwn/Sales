<template>
  <div class="payment-panel" @keydown.enter.prevent>
    <!-- Header -->
    <div class="flex items-center justify-between bg-green-600 text-white px-6 py-4 rounded-t-lg">
      <div class="flex items-center space-x-2">
        <button @click="$emit('back')" class="focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <span v-if="splitMode" class="font-semibold">Remaining {{ formatCurrency(customRound(remainingAmount)) }}</span>
        <span v-else class="font-semibold">{{ formatCurrency(customRound(total)) }} Total amount due</span>
      </div>
      <button v-if="!splitMode" @click="enableSplit" class="uppercase font-bold text-sm tracking-wider">SPLIT</button>
    </div>

    <!-- Loading State -->
    <div v-if="loadingTicket" class="flex items-center justify-center p-8">
      <div class="text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading ticket data...</p>
      </div>
    </div>

    <!-- Split Payment UI -->
    <div v-if="splitMode" class="p-6" @keydown.enter.prevent>
      <div class="flex items-center justify-center mb-4">
        <button
          @click="removeLastUnpaidSplit"
          :disabled="splits.length <= paidSplitsCount + 1"
          class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-4"
        >
          <span class="text-xl font-bold">-</span>
        </button>
        <span class="text-2xl font-bold">{{ displaySplits.length }}</span>
        <span class="ml-2 text-gray-500">Payments</span>
        <button @click="addSplit" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center ml-4">
          <span class="text-xl font-bold">+</span>
        </button>
      </div>
      <div class="split-scroll-area space-y-4">
        <div v-for="(split, index) in displaySplits" :key="split.id" class="bg-white p-4 rounded-lg border border-gray-200">
          <div class="flex items-center space-x-3 mb-3">
            <!-- Only show remove button for unpaid splits -->
            <button v-if="!split.isCompleted" @click="removeSplit(split.originalIndex)" class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-7 7-7-7" /></svg></button>
            <div class="relative">
              <select v-model="split.paymentMethod" class="appearance-none bg-white border border-gray-300 rounded px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" :disabled="split.isCompleted">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="mpesa">Mpesa</option>
              </select>
              <svg class="absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
            <span class="font-medium text-gray-700">
                              <template v-if="!split.isCompleted">
                  <template v-if="split.paymentMethod === 'cash'">
                    <input
                      v-model.number="split.cashReceived"
                      type="number"
                      :min="0"
                      step="1"
                      class="w-24 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 text-right"
                      placeholder="Cash given"
                      @input="validateSplitAmount(split.originalIndex, 'cash')"
                    />
                  </template>
                                  <template v-else>
                    <input
                      v-model.number="split.amount"
                      type="number"
                      :min="0"
                      :max="getSplitMaxPayableWithTolerance(split.originalIndex)"
                      step="1"
                      class="w-24 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 text-right"
                      :disabled="split.isCompleted"
                      @input="validateSplitAmount(split.originalIndex, 'amount')"
                    />
                  </template>
              </template>
              <template v-else>
                KES {{ customRound(split.amount) }}
              </template>
            </span>
            <div v-if="!split.isCompleted" class="ml-auto">
              <button @click="processSplitPayment(split.originalIndex)" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">CHARGE</button>
            </div>
            <div v-else class="ml-auto">
              <span class="text-green-600 font-bold">Paid ✓ {{ split.paymentMethod.toUpperCase() }}</span>
            </div>
          </div>
          <!-- All input fields below are disabled for paid splits -->
          <div v-if="!split.isCompleted" class="mt-3 pt-3 border-t border-gray-200">
            <div v-if="split.paymentMethod === 'card'" class="space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Card Number</label>
                  <input 
                    v-model="split.cardNumber" 
                    type="text" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="1234 5678 9012 3456"
                    @keydown.enter.prevent
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">CVV</label>
                  <input 
                    v-model="split.cardCvv" 
                    type="text" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="123"
                    @keydown.enter.prevent
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Expiry Date</label>
                <input 
                  v-model="split.cardExpiry" 
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                  placeholder="MM/YY"
                  @keydown.enter.prevent
                />
              </div>
            </div>
            <div v-if="split.paymentMethod === 'mpesa'" class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                <input 
                  v-model="split.mpesaPhone" 
                  type="tel" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                  placeholder="07XX XXX XXX"
                  @keydown.enter.prevent
                />
              </div>
              <div class="border-t pt-3">
                <div class="flex items-center mb-2">
                  <input v-model="split.mpesaManualMode" type="checkbox" :id="'mpesaManual' + split.originalIndex" class="mr-2" />
                  <label :for="'mpesaManual' + split.originalIndex" class="text-xs font-medium text-gray-700">Manual Entry</label>
                </div>
                <div v-if="split.mpesaManualMode" class="space-y-2">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Code</label>
                    <input 
                      v-model="split.mpesaTransactionCode" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                      placeholder="e.g., QK123456789"
                      @keydown.enter.prevent
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Amount Received</label>
                    <input 
                      v-model="split.mpesaAmountReceived" 
                      type="number" 
                      :min="0"
                      :max="getSplitMaxPayableWithTolerance(split.originalIndex)"
                      step="1" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                      placeholder="0"
                      @keydown.enter.prevent
                      @keydown.escape.prevent
                      @input="validateSplitMpesaAmount(split.originalIndex)"
                    />
                  </div>
                </div>
              </div>

              <!-- M-PESA Status Display for Split -->
              <div v-if="split.mpesaStatus" class="p-3 rounded-lg border" :class="{
                'bg-green-50 border-green-200 text-green-800': split.mpesaStatus === 'success',
                'bg-red-50 border-red-200 text-red-800': split.mpesaStatus === 'failed',
                'bg-yellow-50 border-yellow-200 text-yellow-800': split.mpesaStatus === 'pending'
              }">
                <div class="flex items-center">
                  <svg v-if="split.mpesaStatus === 'success'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else-if="split.mpesaStatus === 'failed'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  <span class="text-sm font-medium">
                    {{ split.mpesaStatus === 'success' ? 'Payment Successful' : 
                       split.mpesaStatus === 'failed' ? 'Payment Failed' : 'Processing Payment...' }}
                  </span>
                </div>
                <div v-if="split.mpesaReceiptNumber" class="text-xs mt-1">
                  Receipt: {{ split.mpesaReceiptNumber }}
                </div>
                <div v-if="split.mpesaErrorMessage" class="text-xs mt-1">
                  {{ split.mpesaErrorMessage }}
                </div>
              </div>
            </div>
          </div>
          <!-- For paid splits, show a summary only (read-only) -->
          <div v-else class="pt-3 border-t border-gray-200 text-gray-500 text-sm">
            <div v-if="split.paymentMethod === 'card'">
              Card: {{ split.cardNumber ? '****' + split.cardNumber.slice(-4) : '' }}
            </div>
            <div v-else-if="split.paymentMethod === 'mpesa'">
              Mpesa: {{ split.mpesaPhone }}
            </div>
          </div>
          <div v-if="split.paymentMethod === 'cash' && getSplitChangeAmount(split.originalIndex) > 0">
            <div class="p-2 bg-blue-50 border border-blue-200 rounded mt-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-blue-800">Change given:</span>
                <span class="text-blue-800 font-bold">KES {{ customRound(getSplitChangeAmount(split.originalIndex)) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                    <div class="flex justify-between items-center">
              <span class="text-gray-700">Split Total:</span>
              <span class="font-bold text-lg">KES {{ customRound(splitTotal) }}</span>
            </div>
            <div class="flex justify-between items-center mt-2">
              <span class="text-gray-700">Ticket Total:</span>
              <span class="font-bold text-lg">KES {{ customRound(total) }}</span>
            </div>
        <div class="mt-2 text-sm" :class="customRound(splitTotal) === customRound(total) ? 'text-green-600' : 'text-red-600'">
          {{ customRound(splitTotal) === customRound(total) ? '✓ Amounts match' : '✗ Amounts do not match' }}
        </div>
      </div>
      <div v-if="allSplitsComplete" class="mt-6">
        <button @click="completeTransaction" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg text-lg">DONE</button>
      </div>
    </div>

    <!-- Single Payment UI -->
    <div v-else class="p-6" @keydown.enter.prevent>
              <div class="flex flex-col items-center mb-6">
          <div class="text-3xl font-bold mb-2">{{ formatCurrency(customRound(total)) }}</div>
          <div class="text-gray-500">Total amount due</div>
        </div>
      <div class="mb-4">
        <label class="block text-green-700 font-semibold mb-1">Cash received</label>
        <input 
          v-model="cashReceived" 
          type="number" 
          :min="0"
          step="1" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
          placeholder="0"
          @keydown.enter.prevent
          @keydown.escape.prevent
        />
      </div>
      <div class="flex space-x-2 mb-6">
        <button v-for="amt in quickAmounts" :key="amt" @click="cashReceived = amt" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-lg font-semibold">{{ formatCurrency(amt) }}</button>
      </div>
      <div class="mb-4">
        <button @click="selectedPaymentMethod = 'card'" class="w-full flex items-center justify-center border border-gray-300 rounded-lg py-3 mb-2"><svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H3m18 4H3m18-8H3a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2z" /></svg> CARD</button>
        <button @click="selectedPaymentMethod = 'mpesa'" class="w-full flex items-center justify-center border border-gray-300 rounded-lg py-3"><svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2z" /></svg> MOBILE PAYMENT</button>
      </div>
      <div v-if="selectedPaymentMethod === 'card'" class="mb-4">
                        <input 
                  v-model="cardNumber" 
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2" 
                  placeholder="Card Number"
                  @keydown.enter.prevent
                />
                  <input 
            v-model="cardExpiry" 
            type="text" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2" 
            placeholder="Expiry (MM/YY)"
            @keydown.enter.prevent
          />
                  <input 
            v-model="cardCvv" 
            type="text" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
            placeholder="CVV"
            @keydown.enter.prevent
          />
      </div>
      <div v-if="selectedPaymentMethod === 'mpesa'" class="mb-4">
                  <input 
            v-model="mpesaPhone" 
            type="tel" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2" 
            placeholder="Phone Number"
            @keydown.enter.prevent
          />
        <div class="flex items-center mb-2">
          <input v-model="mpesaManualMode" type="checkbox" id="mpesaManual" class="mr-2" />
          <label for="mpesaManual" class="text-xs font-medium text-gray-700">Manual Entry</label>
        </div>
        <div v-if="mpesaManualMode">
          <input v-model="mpesaTransactionCode" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2" placeholder="Transaction Code" />
          <input 
            v-model="mpesaAmountReceived" 
            type="number" 
            :min="0"
            :max="getTotalWithTolerance()"
            step="1" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
            placeholder="Amount Received"
            @input="validateMpesaAmount"
          />
        </div>

        <!-- M-PESA Status Display -->
        <div v-if="mpesaStatus" class="p-3 rounded-lg border mt-3" :class="{
          'bg-green-50 border-green-200 text-green-800': mpesaStatus === 'success',
          'bg-red-50 border-red-200 text-red-800': mpesaStatus === 'failed',
          'bg-yellow-50 border-yellow-200 text-yellow-800': mpesaStatus === 'pending'
        }">
          <div class="flex items-center">
            <svg v-if="mpesaStatus === 'success'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg v-else-if="mpesaStatus === 'failed'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="text-sm font-medium">
              {{ mpesaStatus === 'success' ? 'Payment Successful' : 
                 mpesaStatus === 'failed' ? 'Payment Failed' : 'Processing Payment...' }}
            </span>
          </div>
          <div v-if="mpesaReceiptNumber" class="text-xs mt-1">
            Receipt: {{ mpesaReceiptNumber }}
          </div>
          <div v-if="mpesaErrorMessage" class="text-xs mt-1">
            {{ mpesaErrorMessage }}
          </div>
        </div>
      </div>
              <div class="flex justify-between items-center mb-4">
          <span class="text-gray-700">Change:</span>
          <span class="font-bold text-lg">KES {{ Math.round(changeAmount) }}</span>
        </div>
      <button @click="processPayment" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg text-lg">CHARGE</button>
      <div v-if="paymentCompleted" class="mt-6">
        <button @click="completeTransaction" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg text-lg">DONE</button>
      </div>
    </div>

    <!-- Cancel Order Button -->
    <div class="mt-6">
      <button
        @click="cancelOrder"
        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg text-lg"
      >
        CANCEL ORDER
      </button>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
const props = defineProps({
  total: { type: Number, required: true },
  mode: { type: String, default: 'new' }, // 'new' or 'recall'
  initialSplits: { type: Array, default: () => [] },
  ticketId: { type: Number, default: null }, // New prop for ticket ID
});
async function cancelOrder() {
  if (!props.ticketId) return;
  try {
    await axios.post(`/pos/ticket/${props.ticketId}/cancel`);
    emit('complete', { cancelled: true });
  } catch (error) {
    console.error('Error cancelling order:', error);
    alert('Failed to cancel order. Please try again.');
  }
}
const emit = defineEmits(['complete', 'back', 'partial-update']);

// Get page context for user data
const page = usePage();

// Get device UUID from localStorage or generate one
function getDeviceUUID() {
  let uuid = localStorage.getItem('pos_device_uuid');
  if (!uuid) {
    uuid = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('pos_device_uuid', uuid);
  }
  return uuid;
}

const deviceUuid = getDeviceUUID();

// State
const splitMode = ref(false);
const splits = ref([]);
const cashReceived = ref('');
const selectedPaymentMethod = ref('cash');
const cardNumber = ref('');
const cardExpiry = ref('');
const cardCvv = ref('');
const mpesaPhone = ref('');
const mpesaManualMode = ref(false);
const mpesaTransactionCode = ref('');
const mpesaAmountReceived = ref('');

// M-PESA status tracking
const mpesaStatus = ref(null);
const mpesaReceiptNumber = ref('');
const mpesaErrorMessage = ref('');
const mpesaCheckoutRequestId = ref(null);

const paymentCompleted = ref(false);

// Global flag to prevent duplicate callback processing
const callbackProcessed = ref(new Set());

// Global polling state to prevent multiple polling intervals
const activePollingIntervals = ref(new Set());

// Function to clear processed callbacks
function clearProcessedCallbacks() {
  callbackProcessed.value.clear();
}

// Function to clear all polling intervals
function clearAllPollingIntervals() {
  activePollingIntervals.value.forEach(intervalId => {
    clearInterval(intervalId);
  });
  activePollingIntervals.value.clear();
}

// Load ticket data from DB if ticketId is provided
const ticketData = ref(null);
const loadingTicket = ref(false);

// Load ticket data when component mounts
import { onMounted, onBeforeUnmount } from 'vue';

// Prevent form submission globally for this component
function preventFormSubmission(e) {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
    e.preventDefault();
    e.stopPropagation();
  }
}

onMounted(async () => {
  // Add global event listeners to prevent form submission
  document.addEventListener('submit', preventFormSubmission, true);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT')) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);
  
  if (props.ticketId) {
    loadingTicket.value = true;
    try {
      const response = await axios.get(`/pos/ticket/${props.ticketId}`);
      ticketData.value = response.data;
      // Only enable splitMode if there are 2+ payments, or any payment has amount > 0
      if (response.data.payments && response.data.payments.length > 1) {
        splitMode.value = true;
        splits.value = response.data.payments.map(payment => ({
          id: payment.id,
          amount: payment.amount.toString(),
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
      } else if (response.data.payments && response.data.payments.length === 1 && parseFloat(response.data.payments[0].amount) > 0) {
        splitMode.value = true;
        splits.value = response.data.payments.map(payment => ({
          id: payment.id,
          amount: payment.amount.toString(),
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
      } else {
        splitMode.value = false;
        splits.value = [];
      }
    } catch (error) {
      console.error('Error loading ticket:', error);
    } finally {
      loadingTicket.value = false;
    }
  }
});

// Custom rounding function: always round up for any decimal
function customRound(amount) {
  const decimal = amount % 1;
  const baseAmount = Math.floor(amount);
  
  if (decimal > 0) {
    return baseAmount + 1; // Round up for any decimal (.1, .2, .3, .4, .5, .6, .7, .8, .9)
  } else {
    return baseAmount; // Keep as is if no decimal
  }
}

// Cleanup event listeners
onBeforeUnmount(() => {
  document.removeEventListener('submit', preventFormSubmission, true);
  document.removeEventListener('keydown', (e) => {
    if (e.key === 'Enter' && (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT')) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);
  
  // Clear all polling intervals and processed callbacks
  clearAllPollingIntervals();
  clearProcessedCallbacks();
});

// Quick amounts (50s, 100s, etc)
const quickAmounts = computed(() => {
  const amounts = [];
  const due = customRound(Number(props.total));
  amounts.push(due);
  // Next multiple of 50 above due
  let next50 = due % 50 === 0 ? due : due + (50 - (due % 50));
  if (next50 !== due) amounts.push(next50);
  // Next multiples of 100 above due (and above next50)
  let base = next50 > due ? next50 : due;
  for (let i = 1; amounts.length < 5; i++) {
    let next100 = base + i * 100 - (base % 100);
    if (!amounts.includes(next100)) amounts.push(next100);
  }
  return amounts.slice(0, 5);
});

// Split logic
function syncSplitsWithTotal() {
  const total = props.total;
  const paidSplits = splits.value.filter(s => s.isCompleted);
  const paidTotal = paidSplits.reduce((sum, s) => sum + parseFloat(s.amount), 0);
  const unpaidSplits = splits.value.filter(s => !s.isCompleted);
  const unpaidCount = unpaidSplits.length;
  if (unpaidCount === 0) return;
  const perSplit = (total - paidTotal) / unpaidCount;
  unpaidSplits.forEach(s => {
    // Round the split amount using custom rounding
    const roundedAmount = customRound(perSplit);
    s.amount = roundedAmount.toString();
    // For cash splits, also set cashReceived to the split amount
    if (s.paymentMethod === 'cash') {
      s.cashReceived = roundedAmount.toString();
    }
  });
}

function enableSplit() {
  splitMode.value = true;
  // If there are paid splits, preserve them and add one unpaid split for the remaining
  const paid = splits.value.filter(s => s.isCompleted);
  if (paid.length > 0) {
    const paidTotal = paid.reduce((sum, s) => sum + parseFloat(s.amount), 0);
    const remaining = props.total - paidTotal;
    // Round the remaining amount using custom rounding
    const roundedRemaining = customRound(remaining > 0 ? remaining : 0);
    splits.value = [
      ...paid,
      createSplit(roundedRemaining)
    ];
  } else {
    // Always reset to two equal unpaid splits
    const amountPerSplit = props.total / 2;
    // Round the split amount using custom rounding
    const roundedAmountPerSplit = customRound(amountPerSplit);
    splits.value = [
      createSplit(roundedAmountPerSplit),
      createSplit(roundedAmountPerSplit),
    ];
  }
  syncSplitsWithTotal();
}
function addSplit() {
  // Add a new unpaid split
  splits.value.push(createSplit(0));
  nextTick(() => {
    syncSplitsWithTotal();
  });
}
function createSplit(amount) {
  // Round the amount to nearest whole number
  const roundedAmount = Math.round(amount);
  return {
    id: Date.now() + Math.random(),
    amount: roundedAmount.toString(),
    paymentMethod: 'cash',
    isCompleted: false,
    cashReceived: '',
    cardNumber: '',
    cardExpiry: '',
    cardCvv: '',
    mpesaPhone: '',
    mpesaManualMode: false,
    mpesaTransactionCode: '',
    mpesaAmountReceived: '',
    mpesaStatus: null,
    mpesaReceiptNumber: '',
    mpesaErrorMessage: '',
    mpesaCheckoutRequestId: null
  };
}
function redistributeAmounts() {
  const unpaid = splits.value.filter(s => !s.isCompleted);
  const paid = splits.value.filter(s => s.isCompleted);
  const paidTotal = paid.reduce((sum, s) => sum + parseFloat(s.amount), 0);
  const remaining = props.total - paidTotal;
  const perUnpaid = remaining / unpaid.length;
  unpaid.forEach(s => { 
    // Round the amount to nearest whole number
    const roundedAmount = Math.round(perUnpaid);
    s.amount = roundedAmount.toString(); 
  });
}
// Filter out cash payments with zero amounts from display
const displaySplits = computed(() => {
  return splits.value
    .map((split, originalIndex) => ({ ...split, originalIndex }))
    .filter(split => {
      // Don't hide if it's not a cash payment
      if (split.paymentMethod !== 'cash') return true;
      // Don't hide if it's not completed (user might still be entering amount)
      if (!split.isCompleted) return true;
      // Hide if it's a completed cash payment with zero amount
      const amount = parseFloat(split.amount) || 0;
      return amount > 0;
    });
});

const splitTotal = computed(() => splits.value.reduce((sum, s) => sum + parseFloat(s.amount), 0));
const allSplitsComplete = computed(() => {
  const allCompleted = splits.value.every(s => s.isCompleted);
  const amountsMatch = customRound(splitTotal.value) === customRound(props.total);
  return allCompleted && amountsMatch;
});
const remainingAmount = computed(() => {
  if (!splitMode.value) return props.total;
  const paid = splits.value.filter(s => s.isCompleted).reduce((sum, s) => sum + parseFloat(s.amount), 0);
  return props.total - paid;
});
function getSplitMaxPayable(index) {
  let paid = 0;
  for (let i = 0; i < splits.value.length; i++) {
    if (i === index) break;
    if (splits.value[i].isCompleted) {
      paid += parseFloat(splits.value[i].amount) || 0;
    }
  }
  return Math.max(props.total - paid, 0);
}

function getSplitMaxPayableWithTolerance(index) {
  const maxPayable = getSplitMaxPayable(index);
  
  // Allow ±1 tolerance from the rounded amount
  const roundedAmount = customRound(maxPayable);
  const maxAllowed = roundedAmount + 1; // Allow +1 tolerance
  
  return maxAllowed;
}

function getTotalWithTolerance() {
  // Allow ±1 tolerance from the rounded total
  const roundedTotal = customRound(props.total);
  const maxAllowed = roundedTotal + 1; // Allow +1 tolerance
  
  return maxAllowed;
}

function getSplitChangeAmount(index) {
  const split = splits.value[index];
  if (!split || split.paymentMethod !== 'cash') return 0;
  const maxPayable = getSplitMaxPayable(index);
  const cash = parseFloat(split.cashReceived) || 0;
  const change = cash > maxPayable ? (cash - maxPayable) : 0;
  // Only show change if it's 1 or more
  return change >= 1 ? customRound(change) : 0;
}

// Validate split amount with ±1 tolerance
function validateSplitAmount(index, type) {
  const split = splits.value[index];
  if (!split || split.isCompleted) return;
  
  const maxPayable = getSplitMaxPayable(index);
  
  // Allow ±1 tolerance from the rounded amount
  const roundedAmount = customRound(maxPayable);
  const maxAllowed = roundedAmount + 1; // Allow +1 tolerance
  
  if (type === 'cash') {
    const cashAmount = parseFloat(split.cashReceived) || 0;
    
    // Round off the cash amount using custom rounding
    if (cashAmount > 0) {
      const roundedCash = customRound(cashAmount);
      split.cashReceived = roundedCash.toString();
      split.amount = roundedCash.toString();
    }
    
    // Allow cash to exceed by ±1 tolerance, but don't force it down
    if (cashAmount > maxAllowed) {
      // Don't automatically reduce - let user decide
      // Only validate that it's not way too much
      if (cashAmount > (maxAllowed + 10)) {
        const roundedMax = customRound(maxAllowed);
        split.cashReceived = roundedMax.toString();
        split.amount = roundedMax.toString();
      }
    }
  } else {
    const amount = parseFloat(split.amount) || 0;
    
    // Round off the amount using custom rounding
    if (amount > 0) {
      const roundedAmount = customRound(amount);
      split.amount = roundedAmount.toString();
      if (split.paymentMethod === 'cash') {
        split.cashReceived = roundedAmount.toString();
      }
    }
    
    // Allow amount to exceed by ±1 tolerance, but don't force it down
    if (amount > maxAllowed) {
      // Don't automatically reduce - let user decide
      // Only validate that it's not way too much
      if (amount > (maxAllowed + 10)) {
        const roundedMax = customRound(maxAllowed);
        split.amount = roundedMax.toString();
        if (split.paymentMethod === 'cash') {
          split.cashReceived = roundedMax.toString();
        }
      }
    }
  }
}

// Validate single payment cash amount with ±1 tolerance
function validateCashAmount() {
  const cashAmount = parseFloat(cashReceived.value) || 0;
  
  // Round off the cash amount using custom rounding
  if (cashAmount > 0) {
    const roundedCash = customRound(cashAmount);
    cashReceived.value = roundedCash.toString();
  }
  
  // Allow ±1 tolerance from the rounded total
  const roundedTotal = customRound(props.total);
  const maxAllowed = roundedTotal + 1; // Allow +1 tolerance
  
  // Only validate that it's not way too much (more than +10)
  if (cashAmount > (maxAllowed + 10)) {
    cashReceived.value = maxAllowed.toString();
  }
}

// Validate M-PESA manual entry amount with ±1 tolerance
function validateMpesaAmount() {
  const mpesaAmount = parseFloat(mpesaAmountReceived.value) || 0;
  
  // Round off the M-PESA amount using custom rounding
  if (mpesaAmount > 0) {
    const roundedMpesa = customRound(mpesaAmount);
    mpesaAmountReceived.value = roundedMpesa.toString();
  }
  
  // Allow ±1 tolerance from the rounded total
  const roundedTotal = customRound(props.total);
  const maxAllowed = roundedTotal + 1; // Allow +1 tolerance
  
  // Only validate that it's not way too much (more than +10)
  if (mpesaAmount > (maxAllowed + 10)) {
    mpesaAmountReceived.value = maxAllowed.toString();
  }
}

// Validate split M-PESA manual entry amount with ±1 tolerance
function validateSplitMpesaAmount(index) {
  const split = splits.value[index];
  if (!split || split.isCompleted) return;
  
  const maxPayable = getSplitMaxPayable(index);
  const mpesaAmount = parseFloat(split.mpesaAmountReceived) || 0;
  
  // Round off the M-PESA amount using custom rounding
  if (mpesaAmount > 0) {
    const roundedMpesa = customRound(mpesaAmount);
    split.mpesaAmountReceived = roundedMpesa.toString();
  }
  
  // Allow ±1 tolerance from the rounded amount
  const roundedAmount = customRound(maxPayable);
  const maxAllowed = roundedAmount + 1; // Allow +1 tolerance
  
  // Only validate that it's not way too much (more than +10)
  if (mpesaAmount > (maxAllowed + 10)) {
    split.mpesaAmountReceived = maxAllowed.toString();
  }
}

async function processSplitPayment(index) {
  const split = splits.value[index];
  if (!split || split.isCompleted) return;
  let splitAmount;
  let paymentProcessed = false;
  let actualPaid = 0;

  if (split.paymentMethod === 'cash') {
    const cash = parseFloat(split.cashReceived) || 0;
    const maxPayable = getSplitMaxPayable(index);
    const maxAllowed = getSplitMaxPayableWithTolerance(index);
    
    // Round the cash amount to nearest whole number
    const roundedCash = Math.round(cash);
    
    // Allow cash to exceed by smart tolerance, but cap at maxAllowed
    actualPaid = Math.min(roundedCash, maxAllowed);
    if (actualPaid > 0) {
      paymentProcessed = true;
    }
  } else if (split.paymentMethod === 'mpesa') {
    // Handle M-PESA payment
    if (!split.mpesaPhone) {
      alert('Please enter a phone number for M-PESA payment');
      return;
    }

    if (split.mpesaManualMode) {
      // Manual entry mode
      if (!split.mpesaTransactionCode || !split.mpesaAmountReceived) {
        alert('Please enter both transaction code and amount received for manual entry');
        return;
      }
      const amountReceived = parseFloat(split.mpesaAmountReceived);
      const splitAmount = parseFloat(split.amount);
      const maxAllowed = getSplitMaxPayableWithTolerance(index);
      
      // Allow M-PESA amount to be less than split amount (partial payment) or up to smart tolerance more than split amount
      if (amountReceived < 0 || amountReceived > maxAllowed) {
        alert('Amount received must be between 0 and ' + maxAllowed.toFixed(2));
        return;
      }
      // Round the amount to nearest whole number
      const roundedAmount = Math.round(splitAmount);
      actualPaid = roundedAmount;
      paymentProcessed = true;
  } else {
      // STK Push mode
      try {
        // Show processing dialog
        const processingDialog = Swal.fire({
          title: 'Initiating M-PESA Payment',
          text: 'Sending STK push to your phone...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Initiate STK Push
        const response = await axios.post('/pos/mpesa/initiate', {
          ticket_id: props.ticketId,
          amount: parseFloat(split.amount),
          phone: split.mpesaPhone
        }, {
          headers: {
            'X-Device-UUID': deviceUuid
          }
        });

        if (response.data.success) {
          split.mpesaStatus = 'pending';
          split.mpesaCheckoutRequestId = response.data.checkout_request_id;
          
          // Update dialog
          processingDialog.update({
            title: 'M-PESA Payment Initiated',
            text: 'Please check your phone and enter your M-PESA PIN. We\'ll notify you when the payment is complete.',
            icon: 'info'
          });

          // Show waiting dialog - no polling, just wait for callback
          handleMpesaWaiting(split, processingDialog);
          return; // Don't mark as completed yet, wait for callback
        } else {
          processingDialog.close();
          alert('M-PESA payment failed: ' + response.data.message);
          return;
        }

      } catch (error) {
        console.error('M-PESA payment error:', error);
        alert('Failed to initiate M-PESA payment. Please try again.');
        return;
      }
    }
  } else {
    // Card payment
    splitAmount = parseFloat(split.amount);
    // Round the amount to nearest whole number
    const roundedAmount = Math.round(splitAmount);
    actualPaid = roundedAmount;
    if (roundedAmount > 0) {
      paymentProcessed = true;
    }
  }

  if (paymentProcessed) {
    split.amount = actualPaid.toFixed(2);
    split.isCompleted = true;
    emit('partial-update', { splits: splits.value, mode: 'split' });
    nextTick(() => {
      syncSplitsWithTotal();
    });
  }
}

// M-PESA waiting handler - with polling like settings page
function handleMpesaWaiting(split, dialog) {
  split.mpesaStatus = 'waiting';
  
  // Clear any previously processed callbacks for this checkout request
  if (split.mpesaCheckoutRequestId) {
    callbackProcessed.value.delete(split.mpesaCheckoutRequestId);
  }
  
  // Clear all active polling intervals to prevent multiple polling
  clearAllPollingIntervals();
  
  // Show waiting dialog - with polling for callback response
  Swal.fire({
    icon: 'info',
    title: 'M-PESA Payment Initiated',
    html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-mobile-alt fa-3x text-primary"></i>
        </div>
        <p class="mb-2">Please complete the payment on your phone</p>
        <p class="text-muted small">Waiting for payment confirmation...</p>
        <p class="text-sm text-gray-500 mt-2">The system will automatically update when payment is complete</p>
        <button id="check-mpesa-status-split" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Check Payment Status
        </button>
      </div>
    `,
    showConfirmButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      // Add click handler for check status button
      document.getElementById('check-mpesa-status-split').addEventListener('click', async () => {
        try {
          const response = await axios.post('/pos/mpesa/check-status', {
            checkout_request_id: split.mpesaCheckoutRequestId,
            ticket_id: props.ticketId
          }, {
            headers: {
              'X-Device-UUID': deviceUuid
            }
          });

          if (response.data.success && response.data.callback_response) {
            const callback = response.data.callback_response;
            
            if (callback.result_code == 0) {
              // Success
              split.mpesaStatus = 'success';
              split.mpesaReceiptNumber = callback.mpesa_receipt_number;
              split.isCompleted = true;
              
              Swal.close();
              Swal.fire({
                icon: 'success',
                title: 'M-PESA Payment Successful',
                text: `Receipt: ${callback.mpesa_receipt_number}`,
                timer: 3000
              });
              
              emit('partial-update', { splits: splits.value, mode: 'split' });
              nextTick(() => {
                syncSplitsWithTotal();
              });
              
            } else {
              // Failed
              split.mpesaStatus = 'failed';
              split.mpesaErrorMessage = callback.result_desc;
              
              Swal.close();
              Swal.fire({
                icon: 'error',
                title: 'M-PESA Payment Failed',
                text: callback.result_desc
              });
            }
          } else {
            // Still pending
            Swal.fire({
              icon: 'info',
              title: 'Payment Still Pending',
              text: 'The payment is still being processed. Please complete the payment on your phone and try again.',
              timer: 2000
            });
          }
        } catch (error) {
          console.error('M-PESA status check error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error Checking Status',
            text: 'Failed to check payment status. Please try again.'
          });
        }
      });
    }
  });
  
  // Start polling for callback response (similar to settings page)
  pollForCallbackResponse(split, dialog);
}

// Poll for callback response (similar to settings page)
function pollForCallbackResponse(split, dialog) {
  const maxAttempts = 60; // 60 seconds
  const interval = 1000; // 1 second
  
  console.log('Starting to poll for callback response:', split.mpesaCheckoutRequestId);
  
  // Store start time for elapsed time calculation
  const startTime = Date.now();
  let callbackFound = false;
  
  const pollInterval = setInterval(async () => {
    try {
      // Don't poll if callback already found or already processed
      if (callbackFound || callbackProcessed.value.has(split.mpesaCheckoutRequestId)) {
        clearInterval(pollInterval);
        activePollingIntervals.value.delete(pollInterval);
        return;
      }
      
      const response = await axios.post('/pos/mpesa/check-status', {
        checkout_request_id: split.mpesaCheckoutRequestId,
        ticket_id: props.ticketId
      }, {
        headers: {
          'X-Device-UUID': deviceUuid
        }
      });

      if (response.data.success && response.data.callback_response) {
        console.log('Callback response found!', response.data.callback_response);
        
        // Check if this callback has already been processed
        if (callbackProcessed.value.has(split.mpesaCheckoutRequestId)) {
          console.log('Callback already processed, skipping...');
          clearInterval(pollInterval);
          return;
        }
        
        // Mark as found and processed to prevent multiple processing
        callbackFound = true;
        callbackProcessed.value.add(split.mpesaCheckoutRequestId);
        
        // Clear the interval
        clearInterval(pollInterval);
        
        // Close waiting dialog safely
        try {
          if (Swal.isVisible()) {
            Swal.close();
          }
        } catch (e) {
          console.log('Dialog already closed');
        }
        
        // Handle the callback response
        handleCallbackResponse(split, response.data.callback_response);
        return;
      }
      
      // Update waiting dialog with elapsed time (only if dialog exists and is visible)
      try {
        const elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
        // Only update if dialog exists, has update function, and SweetAlert is visible
        if (dialog && typeof dialog.update === 'function' && Swal.isVisible() && !callbackFound) {
          dialog.update({
            html: `
              <div class="text-center">
                <div class="mb-3">
                  <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                </div>
                <p class="mb-2">Please complete the payment on your phone</p>
                <p class="text-muted small">Waiting for payment confirmation... (${elapsedSeconds}s)</p>
                <p class="text-sm text-gray-500 mt-2">The system will automatically update when payment is complete</p>
                <button id="check-mpesa-status-split" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                  Check Payment Status
                </button>
              </div>
            `
          });
        }
      } catch (e) {
        console.log('Dialog update failed:', e.message);
      }
      
    } catch (error) {
      console.error('Error polling for callback response:', error);
    }
  }, interval);

  // Stop polling after max attempts
  setTimeout(() => {
    if (!callbackFound) {
      clearInterval(pollInterval);
      if (split.mpesaStatus === 'waiting') {
        split.mpesaStatus = 'failed';
        split.mpesaErrorMessage = 'Payment timeout. Please try again or use manual entry.';
        try {
          if (Swal.isVisible()) {
            Swal.close();
          }
        } catch (e) {
          console.log('Dialog already closed');
        }
        Swal.fire({
          icon: 'warning',
          title: 'Payment Timeout',
          text: 'The payment is taking longer than expected. You can try again or use manual entry.'
        });
      }
    }
  }, maxAttempts * interval);
}

// Handle callback response
function handleCallbackResponse(split, callbackResponse) {
  const resultCode = String(callbackResponse.result_code);
  
  if (resultCode === '0') {
    // Success
    split.mpesaStatus = 'success';
    split.mpesaReceiptNumber = callbackResponse.mpesa_receipt_number;
    split.isCompleted = true;
    
    Swal.fire({
      icon: 'success',
      title: 'M-PESA Payment Successful',
      text: `Receipt: ${callbackResponse.mpesa_receipt_number}`,
      timer: 3000
    });
    
    emit('partial-update', { splits: splits.value, mode: 'split' });
    nextTick(() => {
      syncSplitsWithTotal();
    });
    
  } else {
    // Failed
    split.mpesaStatus = 'failed';
    split.mpesaErrorMessage = callbackResponse.result_desc;
    
    Swal.fire({
      icon: 'error',
      title: 'M-PESA Payment Failed',
      text: callbackResponse.result_desc
    });
  }
}

async function completeTransaction() {
  try {
    // Prepare payment data for the ticket update
    let payments = [];
    
    if (splitMode.value) {
      // For split payments, use the completed splits
      payments = splits.value
        .filter(split => split.isCompleted)
        .map(split => ({
          method: split.paymentMethod,
          amount: parseFloat(split.amount),
          status: 'completed',
          meta: {
            cashReceived: split.cashReceived,
            cardNumber: split.cardNumber,
            cardExpiry: split.cardExpiry,
            cardCvv: split.cardCvv,
            mpesaPhone: split.mpesaPhone,
            mpesaTransactionCode: split.mpesaTransactionCode,
            mpesaAmountReceived: split.mpesaAmountReceived
          }
        }));
    } else {
      // For single payment, create payment data
      if (selectedPaymentMethod.value === 'cash') {
        payments.push({
          method: 'cash',
          amount: parseFloat(cashReceived.value) || 0,
          status: 'completed',
          meta: { cashReceived: cashReceived.value }
        });
      } else if (selectedPaymentMethod.value === 'card') {
        payments.push({
          method: 'card',
          amount: props.total,
          status: 'completed',
          meta: {
            cardNumber: cardNumber.value,
            cardExpiry: cardExpiry.value,
            cardCvv: cardCvv.value
          }
        });
      } else if (selectedPaymentMethod.value === 'mpesa') {
        payments.push({
          method: 'mpesa',
          amount: parseFloat(mpesaAmountReceived.value) || props.total,
          status: 'completed',
          meta: {
            mpesaPhone: mpesaPhone.value,
            mpesaTransactionCode: mpesaTransactionCode.value,
            mpesaAmountReceived: mpesaAmountReceived.value
          }
        });
      }
    }

    // Update the ticket with completed payments
    if (props.ticketId && payments.length > 0) {
      await axios.post(`/pos/ticket/${props.ticketId}/update-payment`, {
        payments: payments
      });
    }

    // Emit complete event
    emit('complete', { splits: splits.value, mode: splitMode.value ? 'split' : 'single' });
  } catch (error) {
    console.error('Error updating ticket payment:', error);
    // Still emit the event even if there's an error, let the parent handle it
    emit('complete', { splits: splits.value, mode: splitMode.value ? 'split' : 'single' });
  }
}

// Single payment logic
const changeAmount = computed(() => {
  if (selectedPaymentMethod.value !== 'cash') return 0;
  const cash = parseFloat(cashReceived.value) || 0;
  const change = Math.max(0, cash - props.total);
  // Only show change if it's 1 or more
  return change >= 1 ? Math.round(change) : 0;
});
async function processPayment() {
  if (selectedPaymentMethod.value === 'cash') {
    const cashAmount = parseFloat(cashReceived.value) || 0;
    const maxAllowed = getTotalWithTolerance();
    
    // Round the cash amount using custom rounding
    const roundedCash = customRound(cashAmount);
    
    // Allow cash to be less than total (partial payment) or up to smart tolerance more than total
    if (roundedCash > 0) {
      paymentCompleted.value = true;
    }
  } else if (selectedPaymentMethod.value === 'card') {
    if (!cardNumber.value || !cardExpiry.value || !cardCvv.value) return;
    paymentCompleted.value = true;
  } else if (selectedPaymentMethod.value === 'mpesa') {
    if (!mpesaPhone.value) {
      alert('Please enter a phone number for M-PESA payment');
      return;
    }
    
    if (mpesaManualMode.value) {
      if (!mpesaTransactionCode.value || !mpesaAmountReceived.value) {
        alert('Please enter both transaction code and amount received for manual entry');
        return;
      }
      const mpesaAmount = parseFloat(mpesaAmountReceived.value);
      const maxAllowed = getTotalWithTolerance();
      
      // Round the M-PESA amount using custom rounding
      const roundedMpesa = customRound(mpesaAmount);
      
      // Allow M-PESA amount to be less than total (partial payment) or up to smart tolerance more than total
      if (roundedMpesa < 0 || roundedMpesa > maxAllowed) {
        alert('Amount received must be between 0 and ' + maxAllowed.toFixed(2));
        return;
      }
      paymentCompleted.value = true;
    } else {
      // STK Push mode for single payment
      try {
        // Show processing dialog
        const processingDialog = Swal.fire({
          title: 'Initiating M-PESA Payment',
          text: 'Sending STK push to your phone...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Initiate STK Push
        const response = await axios.post('/pos/mpesa/initiate', {
          ticket_id: props.ticketId,
          amount: props.total,
          phone: mpesaPhone.value
        }, {
          headers: {
            'X-Device-UUID': deviceUuid
          }
        });

        if (response.data.success) {
          mpesaStatus.value = 'pending';
          mpesaCheckoutRequestId.value = response.data.checkout_request_id;
          
          // Update dialog
          processingDialog.update({
            title: 'M-PESA Payment Initiated',
            text: 'Please check your phone and enter your M-PESA PIN. We\'ll notify you when the payment is complete.',
            icon: 'info'
          });

          // Show waiting dialog - no polling, just wait for callback
          handleSingleMpesaWaiting(processingDialog);
        } else {
          processingDialog.close();
          alert('M-PESA payment failed: ' + response.data.message);
        }

      } catch (error) {
        console.error('M-PESA payment error:', error);
        alert('Failed to initiate M-PESA payment. Please try again.');
      }
    }
  }
}

// Single M-PESA waiting handler - with polling like settings page
function handleSingleMpesaWaiting(dialog) {
  mpesaStatus.value = 'waiting';
  
  // Clear any previously processed callbacks for this checkout request
  if (mpesaCheckoutRequestId.value) {
    callbackProcessed.value.delete(mpesaCheckoutRequestId.value);
  }
  
  // Clear all active polling intervals to prevent multiple polling
  clearAllPollingIntervals();
  
  // Show waiting dialog - with polling for callback response
  Swal.fire({
    icon: 'info',
    title: 'M-PESA Payment Initiated',
    html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-mobile-alt fa-3x text-primary"></i>
        </div>
        <p class="mb-2">Please complete the payment on your phone</p>
        <p class="text-muted small">Waiting for payment confirmation...</p>
        <p class="text-sm text-gray-500 mt-2">The system will automatically update when payment is complete</p>
        <button id="check-mpesa-status-single" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Check Payment Status
        </button>
      </div>
    `,
    showConfirmButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      // Add click handler for check status button
      document.getElementById('check-mpesa-status-single').addEventListener('click', async () => {
        try {
          const response = await axios.post('/pos/mpesa/check-status', {
            checkout_request_id: mpesaCheckoutRequestId.value,
            ticket_id: props.ticketId
          }, {
            headers: {
              'X-Device-UUID': deviceUuid
            }
          });

          if (response.data.success && response.data.callback_response) {
            const callback = response.data.callback_response;
            
            if (callback.result_code == 0) {
              // Success
              mpesaStatus.value = 'success';
              mpesaReceiptNumber.value = callback.mpesa_receipt_number;
      paymentCompleted.value = true;
              
              Swal.close();
              Swal.fire({
                icon: 'success',
                title: 'M-PESA Payment Successful',
                text: `Receipt: ${callback.mpesa_receipt_number}`,
                timer: 3000
              });
              
            } else {
              // Failed
              mpesaStatus.value = 'failed';
              mpesaErrorMessage.value = callback.result_desc;
              
              Swal.close();
              Swal.fire({
                icon: 'error',
                title: 'M-PESA Payment Failed',
                text: callback.result_desc
              });
            }
          } else {
            // Still pending
            Swal.fire({
              icon: 'info',
              title: 'Payment Still Pending',
              text: 'The payment is still being processed. Please complete the payment on your phone and try again.',
              timer: 2000
            });
          }
        } catch (error) {
          console.error('M-PESA status check error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error Checking Status',
            text: 'Failed to check payment status. Please try again.'
          });
        }
      });
    }
  });
  
  // Start polling for callback response (like in settings)
  pollForSingleCallbackResponse(dialog);
}

// Poll for single payment callback response
function pollForSingleCallbackResponse(dialog) {
  const maxAttempts = 60; // 60 seconds
  const interval = 1000; // 1 second
  
  console.log('Starting to poll for single callback response:', mpesaCheckoutRequestId.value);
  
  // Store start time for elapsed time calculation
  const startTime = Date.now();
  let callbackFound = false;
  
  const pollInterval = setInterval(async () => {
    try {
      // Don't poll if callback already found or already processed
      if (callbackFound || callbackProcessed.value.has(mpesaCheckoutRequestId.value)) {
        clearInterval(pollInterval);
        return;
      }
      
      const response = await axios.post('/pos/mpesa/check-status', {
        checkout_request_id: mpesaCheckoutRequestId.value,
        ticket_id: props.ticketId
      }, {
        headers: {
          'X-Device-UUID': deviceUuid
        }
      });

      if (response.data.success && response.data.callback_response) {
        console.log('Single callback response found!', response.data.callback_response);
        
        // Check if this callback has already been processed
        if (callbackProcessed.value.has(mpesaCheckoutRequestId.value)) {
          console.log('Single callback already processed, skipping...');
          clearInterval(pollInterval);
          return;
        }
        
        // Mark as found and processed to prevent multiple processing
        callbackFound = true;
        callbackProcessed.value.add(mpesaCheckoutRequestId.value);
        
        // Clear the interval
        clearInterval(pollInterval);
        
        // Close waiting dialog safely
        try {
          if (Swal.isVisible()) {
            Swal.close();
          }
        } catch (e) {
          console.log('Dialog already closed');
        }
        
        // Handle the callback response
        handleSingleCallbackResponse(response.data.callback_response);
        return;
      }
      
      // Update waiting dialog with elapsed time (only if dialog exists and is visible)
      try {
        const elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
        // Only update if dialog exists, has update function, and SweetAlert is visible
        if (dialog && typeof dialog.update === 'function' && Swal.isVisible() && !callbackFound) {
          dialog.update({
            html: `
              <div class="text-center">
              <div class="mb-3">
                <i class="fas fa-mobile-alt fa-3x text-primary"></i>
              </div>
              <p class="mb-2">Please complete the payment on your phone</p>
              <p class="text-muted small">Waiting for payment confirmation... (${elapsedSeconds}s)</p>
              <p class="text-sm text-gray-500 mt-2">The system will automatically update when payment is complete</p>
              <button id="check-mpesa-status-single" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Check Payment Status
              </button>
            </div>
          `
          });
        }
      } catch (e) {
        console.log('Dialog update failed:', e.message);
      }
      
    } catch (error) {
      console.error('Error polling for single callback response:', error);
    }
  }, interval);

  // Stop polling after max attempts
  setTimeout(() => {
    if (!callbackFound) {
      clearInterval(pollInterval);
      if (mpesaStatus.value === 'waiting') {
        mpesaStatus.value = 'failed';
        mpesaErrorMessage.value = 'Payment timeout. Please try again or use manual entry.';
        try {
          if (Swal.isVisible()) {
            Swal.close();
          }
        } catch (e) {
          console.log('Dialog already closed');
        }
        Swal.fire({
          icon: 'warning',
          title: 'Payment Timeout',
          text: 'The payment is taking longer than expected. You can try again or use manual entry.'
        });
      }
    }
  }, maxAttempts * interval);
}

// Handle single payment callback response
function handleSingleCallbackResponse(callbackResponse) {
  const resultCode = String(callbackResponse.result_code);
  
  if (resultCode === '0') {
    // Success
    mpesaStatus.value = 'success';
    mpesaReceiptNumber.value = callbackResponse.mpesa_receipt_number;
    paymentCompleted.value = true;
    
    Swal.fire({
      icon: 'success',
      title: 'M-PESA Payment Successful',
      text: `Receipt: ${callbackResponse.mpesa_receipt_number}`,
      timer: 3000
    });
    
  } else {
    // Failed
    mpesaStatus.value = 'failed';
    mpesaErrorMessage.value = callbackResponse.result_desc;
    
    Swal.fire({
      icon: 'error',
      title: 'M-PESA Payment Failed',
      text: callbackResponse.result_desc
    });
  }
}

// No polling - callback will update the UI automatically
function formatCurrency(amount) {
  return 'KES ' + (Number(amount).toFixed(2).replace('.', ','));
}
watch(() => props.initialSplits, (val) => {
  if (val && val.length > 0) {
    splitMode.value = true;
    splits.value = val.map(s => ({ ...s }));
  }
}, { immediate: true });

// Add this computed property and update removeSplit logic
const paidSplitsCount = computed(() => splits.value.filter(s => s.isCompleted).length);

function removeSplit(index) {
  // Prevent removing paid splits
  if (splits.value[index]?.isCompleted) return;
  // Prevent removing if only minimum splits left
  if (splits.value.length <= paidSplitsCount.value + 1) return;
  splits.value.splice(index, 1);
  nextTick(() => {
    syncSplitsWithTotal();
  });
}

function removeLastUnpaidSplit() {
  // Only remove if more than (paid + 1) splits
  if (splits.value.length <= paidSplitsCount.value + 1) return;
  // Find the last unpaid split
  const lastUnpaidIdx = [...splits.value].reverse().findIndex(s => !s.isCompleted);
  if (lastUnpaidIdx === -1) return;
  // Convert to correct index
  const idx = splits.value.length - 1 - lastUnpaidIdx;
  splits.value.splice(idx, 1);
  nextTick(() => {
    syncSplitsWithTotal();
  });
}

// Add this watcher after defining splits, paidSplitsCount, etc.

watch(
  splits,
  (newSplits, oldSplits) => {
    // Guard: only run if lengths match
    if (newSplits.length !== oldSplits.length) return;
    // Only consider unpaid splits
    const unpaidSplits = newSplits.filter(s => !s.isCompleted);
    if (unpaidSplits.length < 2) return; // nothing to redistribute
    // Find which split changed (amount or cashReceived changed and not completed)
    let changedIndex = -1;
    let editedAmount = 0;
    for (let i = 0; i < newSplits.length; i++) {
      if (!newSplits[i].isCompleted) {
        if (newSplits[i].paymentMethod === 'cash') {
          if (newSplits[i].cashReceived !== oldSplits[i].cashReceived) {
            changedIndex = i;
            editedAmount = parseFloat(newSplits[i].cashReceived) || 0;
            newSplits[i].amount = editedAmount.toFixed(2); // keep in sync
            break;
          }
        } else {
          if (newSplits[i].amount !== oldSplits[i].amount) {
            changedIndex = i;
            editedAmount = parseFloat(newSplits[i].amount) || 0;
            break;
          }
        }
      }
    }
    if (changedIndex === -1) return;
    // Calculate remaining unpaid amount
    const total = props.total;
    const paidTotal = newSplits.filter(s => s.isCompleted).reduce((sum, s) => sum + parseFloat(s.amount), 0);
    // Clamp editedAmount to not exceed total - paidTotal if only one unpaid split
    if (unpaidSplits.length === 1) {
      const remaining = (total - paidTotal);
      const roundedRemaining = Math.round(remaining);
      newSplits[changedIndex].amount = roundedRemaining.toString();
      if (newSplits[changedIndex].paymentMethod === 'cash') {
        newSplits[changedIndex].cashReceived = roundedRemaining.toString();
      }
      return;
    }
    // Redistribute remaining among other unpaid splits
    let otherUnpaidIndexes = [];
    unpaidSplits.forEach((s, idx) => {
      const globalIdx = newSplits.indexOf(s);
      if (globalIdx !== changedIndex) otherUnpaidIndexes.push(globalIdx);
    });
    let remaining = total - paidTotal - editedAmount;
    if (remaining < 0) remaining = 0;
    const perSplit = remaining / otherUnpaidIndexes.length;
    otherUnpaidIndexes.forEach(idx => {
      const roundedPerSplit = Math.round(perSplit);
      newSplits[idx].amount = roundedPerSplit.toString();
      if (newSplits[idx].paymentMethod === 'cash') {
        newSplits[idx].cashReceived = roundedPerSplit.toString();
      }
    });
  },
  { deep: true }
);

// Add this watcher after defining splits and remainingAmount
watch(
  [splits, () => remainingAmount.value],
  ([newSplits, newRemaining]) => {
    // If remaining > 0 and all splits are paid, add a new unpaid split
    const allPaid = newSplits.length > 0 && newSplits.every(s => s.isCompleted);
    if (newRemaining > 0 && allPaid) {
      const roundedRemaining = Math.round(newRemaining);
      splits.value.push({
        id: Date.now() + Math.random(),
        amount: roundedRemaining.toString(),
        paymentMethod: 'cash',
        isCompleted: false,
        cashReceived: roundedRemaining.toString(),
        cardNumber: '',
        cardExpiry: '',
        cardCvv: '',
        mpesaPhone: '',
        mpesaManualMode: false,
        mpesaTransactionCode: '',
        mpesaAmountReceived: ''
      });
    }
  },
  { deep: true }
);
</script>
<style scoped>
.payment-panel { border-radius: 16px; box-shadow: 0 4px 32px rgba(0,0,0,0.12); background: #fff; overflow: hidden; }
.split-scroll-area {
  max-height: 400px;
  overflow-y: auto;
  padding-right: 4px;
}
</style>