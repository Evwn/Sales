<template>
  <div class="gps-location-picker">
    <div class="mb-4">
      <div class="mt-1 flex rounded-md shadow-sm">
        <input
          type="text"
          v-model="location"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
          placeholder="Enter location"
        />
        <button
          type="button"
          @click="getCurrentLocation"
          class="ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
          :disabled="isLoading"
        >
          <svg
            v-if="isLoading"
            class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
          <svg
            v-else
            class="-ml-1 mr-2 h-4 w-4 text-gray-500"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
            />
          </svg>
          {{ isLoading ? 'Retrieving Location...' : 'Get Current Location' }}
        </button>
      </div>
    </div>

    <div v-if="coordinates" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      <p>Latitude: {{ coordinates.latitude }}</p>
      <p>Longitude: {{ coordinates.longitude }}</p>
    </div>

    <div v-if="error && bestLocation" class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">
      {{ error }}
      <div class="mt-2">
        <button type="button" class="btn btn-primary" @click="acceptBestLocation">
          Use this location (accuracy: {{ Math.round(bestAccuracy) }}m)
        </button>
      </div>
    </div>
    <div v-else-if="error && !coordinates" class="mt-2 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      latitude: null,
      longitude: null,
      location: '',
    }),
  },
});

const emit = defineEmits(['update:modelValue']);

const location = ref(props.modelValue.location || '');
const coordinates = ref(
  props.modelValue.latitude && props.modelValue.longitude
    ? {
        latitude: props.modelValue.latitude,
        longitude: props.modelValue.longitude,
      }
    : null
);
const isLoading = ref(false);
const error = ref('');
const bestLocation = ref(null);
const bestAccuracy = ref(null);

// Watch for changes in the modelValue prop
watch(() => props.modelValue, (newValue) => {
  location.value = newValue.location || '';
  if (newValue.latitude && newValue.longitude) {
    coordinates.value = {
      latitude: newValue.latitude,
      longitude: newValue.longitude,
    };
  }
}, { deep: true });

const getCurrentLocation = () => {
  if (!navigator.geolocation) {
    error.value = 'Geolocation is not supported by your browser';
    return;
  }

  isLoading.value = true;
  error.value = '';

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const { latitude, longitude, accuracy } = position.coords;
      if (typeof accuracy === 'number' && accuracy > 1) {
        bestLocation.value = { latitude, longitude };
        bestAccuracy.value = accuracy;
        error.value = `Location accuracy is ${Math.round(accuracy)}m. You can accept this location or try again for better accuracy (Required: ≤1m).`;
        isLoading.value = false;
        return;
      }
      coordinates.value = { latitude, longitude };
      bestLocation.value = null;
      bestAccuracy.value = null;
      try {
        // Reverse geocoding to get the address
        const response = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
        );
        const data = await response.json();
        location.value = data.display_name;
        emit('update:modelValue', {
          latitude,
          longitude,
          location: data.display_name,
        });
        error.value = '';
      } catch (err) {
        error.value = 'Failed to get address from coordinates';
      } finally {
        isLoading.value = false;
      }
    },
    (err) => {
      // Handle specific geolocation errors
      switch (err.code) {
        case err.PERMISSION_DENIED:
          error.value = 'Location access denied. Please enable location services in your browser settings.';
          break;
        case err.POSITION_UNAVAILABLE:
          error.value = 'Location information is unavailable. Please try again.';
          break;
        case err.TIMEOUT:
          error.value = 'Location request timed out. Please try again.';
          break;
        default:
          error.value = '';
      }
      isLoading.value = false;
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    }
  );
};

function acceptBestLocation() {
  if (!bestLocation.value) return;
  coordinates.value = bestLocation.value;
  // Reverse geocode for address
  fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${bestLocation.value.latitude}&lon=${bestLocation.value.longitude}`)
    .then(res => res.json())
    .then(data => {
      location.value = data.display_name;
      emit('update:modelValue', {
        latitude: bestLocation.value.latitude,
        longitude: bestLocation.value.longitude,
        location: data.display_name,
      });
      error.value = '';
      bestLocation.value = null;
      bestAccuracy.value = null;
    })
    .catch(() => {
      error.value = 'Failed to get address from coordinates';
    });
}
</script>

<style scoped>
.btn {
  @apply px-4 py-2 rounded font-semibold shadow transition-colors duration-150;
}
.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}
</style> 
