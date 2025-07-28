<template>
  <div class="pos-login-bg min-h-screen flex flex-col justify-center items-center">
    <div class="w-full flex justify-end items-center px-6 pt-3">
      <button class="time-clock-btn flex items-center gap-2 px-4 py-2 border border-green-600 text-green-700 bg-white rounded shadow hover:bg-green-50" @click="showTimeClock = true">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        <span class="font-semibold">TIME CLOCK</span>
      </button>
        </div>
    <div class="flex-1 w-full flex flex-col justify-center items-center">
      <div v-if="showClockedIn" class="clocked-in-confirm flex flex-col items-center justify-center w-full h-full">
        <div class="text-lg text-gray-700 mb-2">{{ userName || 'User' }} clocked in at</div>
        <div class="text-4xl font-bold mb-4">{{ formatTime(clockedInTime) }}</div>
        <button class="go-to-pos-btn bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded text-lg" @click="goToPOS">GO TO POS</button>
      </div>
      <div v-else class="pin-fullscreen-card flex flex-col items-center justify-center w-full h-full">
        <div v-if="showTimeClock" class="flex items-center justify-center gap-8 mb-4">
          <button class="clock-btn clock-in" :disabled="!pinVerified" @click="clockIn">CLOCK IN</button>
          <div class="clock-icon">
            <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          </div>
          <button class="clock-btn clock-out" :disabled="!pinVerified" @click="clockOut">CLOCK OUT</button>
        </div>
        <div class="mb-2 text-gray-700 font-semibold text-xl">Enter PIN</div>
        <div class="flex justify-center mb-6 pin-dots-row">
          <div v-for="(digit, idx) in pinDigits" :key="idx" class="pin-dot" :class="{ filled: digit }"></div>
        </div>
        <div v-if="errorMsg" class="text-red-600 text-sm mb-2 text-center">{{ errorMsg }}</div>
        <div class="keypad-grid mb-2 expanded-keypad">
          <button v-for="n in 9" :key="n" class="keypad-btn" :disabled="isLoggingIn" @click="onKeypad(n.toString())">{{ n }}</button>
          <div></div>
          <button class="keypad-btn" :disabled="isLoggingIn" @click="onKeypad('0')">0</button>
          <button class="keypad-btn" :disabled="isLoggingIn" @click="onKeypad('clear')">Clear</button>
        </div>
      </div>
    </div>
    <div v-if="showGoodbye" class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-90 z-50">
      <div class="text-3xl font-bold text-green-700">Goodbye!</div>
    </div>
  </div>
</template>
<script setup>
import { ref, nextTick, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
const pinDigits = ref(['', '', '', '']);
const errorMsg = ref('');
const isLoggingIn = ref(false);
const deviceStatus = ref('checking');
const showTimeClock = ref(false);
const pinVerified = ref(false);
const showGoodbye = ref(false);
const showClockedIn = ref(false);
const clockedInTime = ref(null);
const userName = ref('');
function getDeviceUUID() {
  return localStorage.getItem('device_uuid');
}
onMounted(async () => {
  const uuid = getDeviceUUID();
  if (!uuid) {
    deviceStatus.value = 'not_found';
    return;
  }
  const res = await fetch(`/api/check-device?uuid=${uuid}`);
  const data = await res.json();
  if (!data.deviceRegistered) {
    deviceStatus.value = 'not_found';
    return;
  }
  const device = await fetch(`/api/pos-device-status?uuid=${uuid}`).then(r => r.json());
  if (device.is_disabled) {
    deviceStatus.value = 'disabled';
    return;
  }
  deviceStatus.value = 'ok';
});
function onKeypad(val) {
  if (isLoggingIn.value) return;
  if (val === 'clear') {
    pinDigits.value = ['', '', '', ''];
    pinVerified.value = false;
    return;
  }
  for (let i = 0; i < 4; i++) {
    if (!pinDigits.value[i]) {
      pinDigits.value[i] = val;
      break;
    }
  }
  if (pinDigits.value.every(d => d.length === 1)) {
    if (showTimeClock.value) {
      verifyPin();
    } else {
    submit();
  }
  }
}
function onKeydown(e) {
  if (isLoggingIn.value) return;
  if (e.key === 'Backspace') {
    for (let i = 3; i >= 0; i--) {
      if (pinDigits.value[i]) {
        pinDigits.value[i] = '';
        pinVerified.value = false;
        break;
      }
    }
    return;
  }
  if (/^\d$/.test(e.key)) {
    onKeypad(e.key);
  }
}
onMounted(() => {
  window.addEventListener('keydown', onKeydown);
});
function verifyPin() {
  if (isLoggingIn.value || deviceStatus.value !== 'ok') return;
  const pin_code = pinDigits.value.join('');
  if (!/^\d{4}$/.test(pin_code)) {
    errorMsg.value = 'PIN code must be exactly 4 digits.';
    return;
  }
  const device_uuid = getDeviceUUID();
  if (!device_uuid) {
    errorMsg.value = 'Device not registered.';
    return;
  }
  isLoggingIn.value = true;
  errorMsg.value = '';
  router.post('/pos/verify-pin', { pin_code, device_uuid }, {
    onError: (errors) => {
      errorMsg.value = errors?.error || 'PIN incorrect.';
      pinDigits.value = ['', '', '', ''];
      pinVerified.value = false;
      isLoggingIn.value = false;
    },
    onSuccess: (page) => {
      errorMsg.value = '';
      pinVerified.value = true;
      isLoggingIn.value = false;
      if (page?.props?.userName) {
        userName.value = page.props.userName;
      }
    },
    onFinish: () => {
      isLoggingIn.value = false;
  }
  });
}
function submit() {
  if (isLoggingIn.value || deviceStatus.value !== 'ok') return;
  const pin_code = pinDigits.value.join('');
  if (!/^\d{4}$/.test(pin_code)) {
    errorMsg.value = 'PIN code must be exactly 4 digits.';
    return;
  }
  const device_uuid = getDeviceUUID();
  if (!device_uuid) {
    errorMsg.value = 'Device not registered.';
    return;
  }
  isLoggingIn.value = true;
  errorMsg.value = '';
  router.post('/pos/login', { pin_code, device_uuid }, {
    onError: (errors) => {
      errorMsg.value = errors?.error || 'Login failed.';
      pinDigits.value = ['', '', '', ''];
      isLoggingIn.value = false;
    },
    onSuccess: () => {
      errorMsg.value = '';
      isLoggingIn.value = false;
      router.visit('/pos/dashboard');
    },
    onFinish: () => {
      isLoggingIn.value = false;
    }
  });
}
function clockIn() {
  if (!pinVerified.value) return;
  // Show clocked in confirmation instead of immediate login
  showClockedIn.value = true;
  clockedInTime.value = new Date();
  // Optionally, you can POST to a clock-in endpoint here if needed
}
function goToPOS() {
  // Now actually log in the user
  isLoggingIn.value = true;
  const pin_code = pinDigits.value.join('');
  const device_uuid = getDeviceUUID();
  router.post('/pos/login', { pin_code, device_uuid }, {
    onError: (errors) => {
      errorMsg.value = errors?.error || 'Login failed.';
      pinDigits.value = ['', '', '', ''];
      pinVerified.value = false;
      isLoggingIn.value = false;
      showClockedIn.value = false;
    },
    onSuccess: () => {
      errorMsg.value = '';
      isLoggingIn.value = false;
      router.visit('/pos/dashboard');
    },
    onFinish: () => {
      isLoggingIn.value = false;
    }
  });
}
function clockOut() {
  if (!pinVerified.value) return;
  showGoodbye.value = true;
  setTimeout(() => {
    showGoodbye.value = false;
    pinDigits.value = ['', '', '', ''];
    pinVerified.value = false;
    showTimeClock.value = false;
  }, 2000);
}
function formatTime(date) {
  if (!date) return '';
  const d = new Date(date);
  let h = d.getHours();
  const m = d.getMinutes().toString().padStart(2, '0');
  const ampm = h >= 12 ? 'PM' : 'AM';
  h = h % 12;
  h = h ? h : 12;
  return `${h}:${m} ${ampm}`;
}
</script> 
<style scoped>
.pos-login-bg {
  background: #f3f4f6;
  min-height: 100vh;
}
.time-clock-btn {
  margin-top: 8px;
  font-size: 1rem;
}
.pin-fullscreen-card {
  width: 100vw;
  height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #fff;
  border-radius: 0;
  box-shadow: none;
  padding: 0;
}
.expanded-keypad {
  width: 33vw;
  min-width: 320px;
  max-width: 480px;
  margin: 0 auto;
}
.keypad-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: repeat(4, 1fr);
  gap: 16px;
  justify-content: center;
}
.keypad-btn {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  font-size: 2.5rem;
  font-weight: 500;
  color: #222;
  transition: background 0.2s, box-shadow 0.2s;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  cursor: pointer;
  height: 90px;
}
.keypad-btn:active {
  background: #d1fae5;
}
.keypad-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.pin-dot {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid #10b981;
  background: #fff;
  transition: background 0.2s;
}
.pin-dot.filled {
  background: #10b981;
}
.clock-btn {
  font-size: 1.1rem;
  font-weight: 600;
  padding: 8px 24px;
  border-radius: 8px;
  border: 2px solid;
  transition: background 0.2s, color 0.2s;
}
.clock-in {
  border-color: #10b981;
  color: #10b981;
  background: #e6f9f0;
}
.clock-in:disabled {
  background: #f3f4f6;
  color: #b5e5d0;
  border-color: #b5e5d0;
}
.clock-out {
  border-color: #ef4444;
  color: #ef4444;
  background: #fbeaea;
}
.clock-out:disabled {
  background: #f3f4f6;
  color: #fbbdbd;
  border-color: #fbbdbd;
}
.clock-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}
.clocked-in-confirm {
  min-height: 60vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.go-to-pos-btn {
  margin-top: 24px;
  min-width: 200px;
}
</style> 