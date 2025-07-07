<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Edit Tax Group</h1>
    <form @submit.prevent="submit">
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
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
      <Link href="/admin/tax-groups" class="ml-4 text-gray-600">Cancel</Link>
    </form>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
const props = defineProps({ taxGroup: Object });
const form = useForm({
  code: props.taxGroup.code,
  description: props.taxGroup.description,
  rate: props.taxGroup.rate,
});
function submit() {
  form.put(`/admin/tax-groups/${props.taxGroup.id}`);
}
</script> 