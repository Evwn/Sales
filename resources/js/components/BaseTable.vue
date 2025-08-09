<template>
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
      <div class="p-6 lg:p-8">
        <!-- Empty state -->
        <div v-if="!rows.length" class="text-center">
          <p class="text-gray-500">{{ emptyMessage }}</p>
          <slot name="empty-action" />
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 overflow-hidden rounded-lg shadow">
            <!-- Table head -->
            <thead class="bg-[#B76E79]/80 backdrop-blur-md">
              <tr>
                <template v-if="$slots.head">
                  <slot name="head" />
                </template>
                <template v-else>
                  <th v-for="(header, index) in headers" :key="index"
                    scope="col"
                    class="w-1/8 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700"
                  >
                    {{ header }}
                  </th>
                </template>
              </tr>
            </thead>

            <!-- Table body -->
            <tbody class="backdrop-blur-sm bg-white/60 divide-y divide-gray-200">
              <tr v-for="(row, rowIndex) in rows" :key="row.id || rowIndex" class="hover:bg-gray-50">
                <template v-if="$slots.row">
                  <!-- User-defined row slot -->
                  <slot name="row" :row="row" :index="rowIndex" />
                </template>
                <template v-else>
                  <!-- Auto-generate cells if no slot -->
                  <td v-for="(header, colIndex) in headers" :key="colIndex"
                    class="w-1/8 px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                  >
                    {{ row[header] }}
                  </td>
                </template>
              </tr>

              <!-- No rows fallback inside table -->
              <tr v-if="rows.length === 0">
                <td :colspan="headers.length" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                  {{ emptyMessage }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  headers: {
    type: Array,
    default: () => []
  },
  rows: {
    type: Array,
    default: () => []
  },
  emptyMessage: {
    type: String,
    default: 'No data found.'
  }
});
</script>
