<template>
<AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Add Tax Group
                </h2>
            </div>
        </template>
      <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
              <form @submit.prevent="submit" space-y-6>
                <div class="mb-4">
                  <label class="block mb-1">Code</label>
                  <input v-model="form.code" class="border px-2 py-1 w-full" maxlength="2" required />
                  <div v-if="form.errors.code" class="text-red-600 text-sm">{{ form.errors.code }}</div>
                </div>
                <div class="mb-4">
                  <label class="block mb-1">Description</label>
                  <input v-model="form.description" class="border px-2 py-1 w-full" required />
                  <div v-if="form.errors.description" class="text-red-600 text-sm">{{ form.errors.description }}</div>
                </div>
                <div class="mb-4">
                  <label class="block mb-1">Rate (%)</label>
                  <input v-model="form.rate" type="number" step="0.01" min="0" class="border px-2 py-1 w-full" required />
                  <div v-if="form.errors.rate" class="text-red-600 text-sm">{{ form.errors.rate }}</div>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                <Link href="/admin/tax-groups" class="ml-4 text-gray-600">Cancel</Link>
              </form>
              </div>
            </div>
      </div>
  </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
const form = useForm({ code: '', description: '', rate: '' });
function submit() {
  form.post('/admin/tax-groups');
}
</script> 