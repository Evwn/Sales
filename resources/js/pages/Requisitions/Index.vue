<template>
  <AppLayout>
    <!-- Header -->
    <PageHeader title="Requisitions" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#B76E79]/80 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 lg:p-8">
            
            <!-- Search & Filters -->
            <div class="flex flex-wrap gap-4 mb-4 mt-4 bg-white/50 backdrop-blur sm:rounded-lg p-4">
              <div class="flex-1">
                <Input
                  v-model="search"
                  placeholder=" 🔍 Search by reference, status, date, items..."
                  class="w-full"
                />
              </div>
              <div>
                <select v-model="statusFilter" class="border rounded p-2 text-sm">
                  <option value="">All Status</option>
                  <option value="draft">Draft</option>
                  <option value="submitted">Submitted</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div>
                <select v-model="locationFilter" class="border rounded p-2 text-sm">
                  <option value="">All Locations</option>
                  <option 
                    v-for="loc in uniqueLocations" 
                    :key="loc" 
                    :value="loc"
                  >
                    {{ loc }}
                  </option>
                </select>
              </div>
            </div>

            <!-- New Button -->
            <div class="mb-4">
              <Link href="/requisitions/create" class="bg-blue-600 text-white px-4 py-2 rounded">+ New Requisition</Link>
            </div>

            <!-- Table -->
            <div v-if="filteredRequisitions.length" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow">
                <thead class="bg-[#B76E79]/80 backdrop-blur-md">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white/60 divide-y divide-gray-200">
                  <tr v-for="(req, index) in filteredRequisitions" :key="req.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
                    <td class="px-6 py-4 text-sm font-medium">{{ req.reference }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ req.requisition_date }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ req.location?.name || 'N/A' }}</td>

                    <!-- Inline editable status -->
                    <td class="px-6 py-4 text-sm">
                      <select v-model="req.status" @change="updateStatus(req)" class="border rounded px-2 py-1 text-sm">
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                      </select>
                    </td>

                    <!-- Inline editable items -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                      <ul>
                        <li v-for="ri in req.items" :key="ri.id" class="flex items-center gap-2 mb-1">
                          <input
                            v-model="ri.item.name"
                            class="border rounded px-2 py-1 text-sm w-32 bg-gray-100"
                            disabled
                          />
                          <input
                            type="number"
                            v-model="ri.quantity"
                            class="border rounded px-2 py-1 text-sm w-20"
                            @blur="updateItem(req.id, ri)"
                          />
                          <button @click="removeItem(req.id, ri.id)" class="text-red-600">✕</button>
                        </li>
                      </ul>

                      <!-- Add new item -->
                      <div class="relative mt-2" :class="'item-dropdown-' + req.id">
                        <input
                          v-model="newItemSearch[req.id]"
                          @focus="showDropdown[req.id] = true"
                          placeholder="Click to add item..."
                          class="border-b border-gray-400 w-full py-1 focus:outline-none"
                        />

                        <!-- Dropdown -->
                        <ul
                          v-if="showDropdown[req.id]"
                          class="absolute z-10 bg-white border rounded shadow-md max-h-40 overflow-y-auto w-full"
                        >
                          <li
                            v-for="item in filteredItems(newItemSearch[req.id])"
                            :key="item.id"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                            @click="selectItem(req.id, item)"
                          >
                            {{ item.name }}
                          </li>
                        </ul>
                      </div>

                    <!-- Selected item form -->
                    <div v-if="pendingItem[req.id]" class="flex items-center gap-2 mt-2">
                      <input
                        v-model="pendingItem[req.id].name"
                        class="border rounded px-2 py-1 text-sm w-32 bg-gray-100"
                        disabled
                      />
                      <input
                        type="number"
                        v-model="pendingItem[req.id].quantity"
                        placeholder="Qty"
                        class="border rounded px-2 py-1 text-sm w-20"
                      />

                      <!-- Submit button ✔ -->
                      <button
                        @click="submitNewItem(req.id)"
                        class="px-3 py-1 rounded border flex items-center"
                        :class="{
                          'bg-gray-100': !loading[req.id],
                          'bg-green-100': success[req.id],
                          'bg-red-100': error[req.id]
                        }"
                      >
                        <span v-if="loading[req.id]" class="animate-spin">⏳</span>
                        <span v-else-if="success[req.id]" class="text-green-600">✔</span>
                        <span v-else-if="error[req.id]" class="text-red-600">✕</span>
                        <span v-else>✔</span>
                      </button>

                      <!-- Cancel button ✖ -->
                      <button
                        @click="removePendingItem(req.id)"
                        class="px-3 py-1 rounded border bg-gray-100 hover:bg-red-100 text-red-600"
                      >
                        ✖
                      </button>
                    </div>
                    </td>

                    <td class="px-6 py-4 text-sm">
                      <Link :href="`/requisitions/${req.id}/edit`" class="text-blue-600">Edit</Link> |
                      <Link as="button" method="delete" :href="`/requisitions/${req.id}`" class="text-red-600">Delete</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-6 text-gray-500">
              No requisitions found.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { Input } from '@/components/ui/input'

const props = defineProps({
  requisitions: { type: Array, default: () => [] },
  items: { type: Array, default: () => [] }
})

const search = ref('')
const statusFilter = ref('')
const locationFilter = ref('')

const uniqueLocations = computed(() => {
  const locs = props.requisitions.map(r => r.location?.name).filter(Boolean)
  return [...new Set(locs)]
})

const filteredRequisitions = computed(() => {
  const term = search.value.trim().toLowerCase()
  return props.requisitions.filter(req => {
    const matchesSearch =
      !term ||
      (req.reference || '').toLowerCase().includes(term) ||
      (req.status || '').toLowerCase().includes(term) ||
      (req.requisition_date || '').toLowerCase().includes(term)

    const matchesStatus = !statusFilter.value || req.status === statusFilter.value
    const matchesLocation = !locationFilter.value || req.location?.name === locationFilter.value

    return matchesSearch && matchesStatus && matchesLocation
  })
})

/**
 * State for inline add
 */
const newItemSearch = reactive({})
const showDropdown = reactive({})
const pendingItem = reactive({})
const loading = reactive({})
const success = reactive({})
const error = reactive({})

/**
 * Helpers
 */
const filteredItems = (term) => {
  if (!term) return props.items
  return props.items.filter(i => i.name.toLowerCase().includes(term.toLowerCase()))
}

const selectItem = (reqId, item) => {
  pendingItem[reqId] = { id: item.id, name: item.name, quantity: 1 }
  newItemSearch[reqId] = item.name
  showDropdown[reqId] = false
}

/**
 * Update requisition status
 */
const updateStatus = (req) => {
  router.put(`/requisitions/${req.id}/status`, { status: req.status })
}

const removePendingItem = (reqId) => {
  delete pendingItem[reqId]
  delete loading[reqId]
  delete success[reqId]
  delete error[reqId]
}


/**
 * Update existing requisition item
 */
const updateItem = (reqId, item) => {
  router.put(`/requisitions/${reqId}/items`, {
    item_id: pendingItem[reqId]?.id ?? item.item_id,   // fallback
    quantity: pendingItem[reqId]?.quantity ?? item.quantity,
    unit: pendingItem[reqId]?.unit ?? item.unit ?? null,
  })
}


const handleClickOutside = (event) => {
  Object.keys(showDropdown).forEach((reqId) => {
    const target = event.target
    if (!target.closest('.item-dropdown-' + reqId)) {
      showDropdown[reqId] = false
    }
  })
}

/**
 * Submit new item
 */
const submitNewItem = (reqId) => {
  if (!pendingItem[reqId]) return

  loading[reqId] = true
  success[reqId] = false
  error[reqId] = false

  router.put(`/requisitions/${reqId}/items`, {
    item_id: pendingItem[reqId].id,
    quantity: pendingItem[reqId].quantity
  }, {
    onSuccess: () => {
      loading[reqId] = false
      success[reqId] = true
      pendingItem[reqId] = null
      newItemSearch[reqId] = ''   // ✅ clear input
      showDropdown[reqId] = false // ✅ close dropdown
      setTimeout(() => success[reqId] = false, 2000)
    },
    onError: () => {
      loading[reqId] = false
      error[reqId] = true
      setTimeout(() => error[reqId] = false, 2000)
    }
  })
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

/**
 * Remove requisition item
 */
const removeItem = (reqId, itemId) => {
  router.delete(`/requisitions/${reqId}/items/${itemId}`)
}
</script>
