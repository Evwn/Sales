<template>
  <div class="payment-demo">
    <h2>Flutterwave Inline Test Payment</h2>
    <form @submit.prevent="makePayment">
      <div>
        <label>Phone Number (for M-PESA):</label>
        <input v-model="phone" placeholder="e.g. 0712345678" required />
      </div>
      <div>
        <label>Amount:</label>
        <input v-model.number="amount" type="number" min="1" required />
      </div>
      <div>
        <label>Payment Method:</label>
        <select v-model="method">
          <option value="mpesa">M-PESA (Mobile Money)</option>
          <option value="card">Card</option>
        </select>
      </div>
      <button type="submit">Pay Now</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const phone = ref('')
const amount = ref(100)
const method = ref('mpesa')

function makePayment() {
  const txRef = 'test-tx-' + Date.now()
  let payment_options = ''
  let currency = 'KES'
  if (method.value === 'mpesa') {
    payment_options = 'mobilemoneykenya'
    currency = 'KES'
  } else {
    payment_options = 'card'
    currency = 'NGN' // Card test works with NGN in test mode
  }

  if (!window.FlutterwaveCheckout) {
    alert('Flutterwave script not loaded. Please ensure https://checkout.flutterwave.com/v3.js is included in your public/index.html.')
    return
  }

  window.FlutterwaveCheckout({
    public_key: 'FLWPUBK_TEST-15890fb231e4601bf94e587c54e01b97-X',
    tx_ref: txRef,
    amount: amount.value,
    currency,
    payment_options,
    customer: {
      email: 'test@example.com',
      phone_number: phone.value,
      name: 'Test User',
    },
    customizations: {
      title: 'Demo Store',
      description: 'Test Payment',
      logo: 'https://flutterwave.com/images/logo.png',
    },
    callback: function (payment) {
      alert('Payment status: ' + payment.status + '\nTransaction ID: ' + payment.transaction_id)
      // In real app: send payment.transaction_id to your backend for verification!
    },
    onclose: function (incomplete) {
      if (incomplete === true) {
        alert('Payment was cancelled.')
      }
    },
  })
}
</script>

<style scoped>
.payment-demo {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #eee;
  border-radius: 8px;
  background: #fafbfc;
}
.payment-demo label {
  display: block;
  margin-bottom: 0.2rem;
}
.payment-demo input, .payment-demo select {
  width: 100%;
  margin-bottom: 1rem;
  padding: 0.5rem;
}
.payment-demo button {
  width: 100%;
  padding: 0.7rem;
  background: #f5a623;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
}
</style> 