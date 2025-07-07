<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
  Plus, 
  Search, 
  Filter, 
  MoreVertical, 
  Edit, 
  Trash2, 
  Eye, 
  Shield, 
  User, 
  Mail, 
  Building, 
  MapPin, 
  Calendar,
  CheckCircle,
  XCircle,
  Clock,
  Settings,
  Key,
  Users,
  BadgeCheck,
  AlertCircle,
  MailCheck,
  MailX
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  business: { id: number; name: string } | null;
  branch: { id: number; name: string } | null;
  businesses: { id: number; name: string }[];
  branches: { id: number; name: string; business_name: string }[];
  roles: { id: number; name: string }[];
  created_at: string;
  updated_at: string;
  is_online: boolean;
  last_seen_at: string | null;
  all_permissions: { id: number; name: string }[];
}

interface Role {
  id: number;
  name: string;
  permissions: { id: number; name: string }[];
}

interface Permission {
  id: number;
  name: string;
}

interface Business {
  id: number;
  name: string;
}

interface Branch {
  id: number;
  name: string;
  business_id: number;
}

const props = defineProps<{
  users: User[];
  roles: Role[];
  permissions: Permission[];
  businesses: Business[];
  branches: Branch[];
}>();

// Get flash messages
const page = usePage();
const flash = computed(() => page.props.flash);

// Show flash messages on mount
onMounted(() => {
  if (flash.value?.success) {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: flash.value.success,
      timer: 3000,
      showConfirmButton: false,
    });
  }
  
  if (flash.value?.error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: flash.value.error,
      confirmButtonText: 'OK',
    });
  }

  // Add click outside handler for dropdowns
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
      userDropdownStates.value.clear();
    }
  });
});

// Watch for flash message changes and show SweetAlert
watch(
  () => flash.value,
  (newFlash) => {
    if (newFlash?.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: newFlash.success,
        timer: 3000,
        showConfirmButton: false,
      });
    }
    if (newFlash?.error) {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: newFlash.error,
        confirmButtonText: 'OK',
      });
    }
  }
);

// Reactive data
const searchQuery = ref('');
const selectedRole = ref('');
const selectedBusiness = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showPasswordModal = ref(false);
const showPermissionsModal = ref(false);
const selectedUser = ref<User | null>(null);
const activeTab = ref('users');
const userDropdownStates = ref(new Map<number, { businesses: boolean; branches: boolean }>());

// Forms
const createForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_ids: [] as number[],
  business_id: null as number | null,
  branch_id: null as number | null,
});

const editForm = useForm({
  name: '',
  email: '',
  role_ids: [] as number[],
  business_id: null as number | null,
  branch_id: null as number | null,
});

const passwordForm = useForm({
  password: '',
  password_confirmation: '',
});

const permissionsForm = useForm({
  permission_ids: [] as number[],
});

// Computed properties
const filteredUsers = computed(() => {
  let filtered = props.users;

  if (searchQuery.value) {
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (selectedRole.value) {
    filtered = filtered.filter(user =>
      user.roles.some(role => role.name === selectedRole.value)
    );
  }

  if (selectedBusiness.value) {
    filtered = filtered.filter(user =>
      user.business?.name === selectedBusiness.value
    );
  }

  return filtered;
});

const availableBranches = computed(() => {
  if (!editForm.business_id) return [];
  return props.branches.filter(branch => branch.business_id === editForm.business_id);
});

const roleOptions = computed(() => {
  return props.roles.map(role => ({
    value: role.name,
    label: role.name.charAt(0).toUpperCase() + role.name.slice(1)
  }));
});

const businessOptions = computed(() => {
  return props.businesses.map(business => ({
    value: business.name,
    label: business.name
  }));
});

// Check if admin roles are selected (only admin should not have business/branch)
const isAdminSelected = computed(() => {
  const selectedRoles = props.roles.filter(role => 
    createForm.role_ids.includes(role.id) || editForm.role_ids.includes(role.id)
  );
  return selectedRoles.some(role => role.name === 'admin');
});

// Check if admin roles are selected for create form
const isAdminInCreateForm = computed(() => {
  const selectedRoles = props.roles.filter(role => createForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'admin');
});

// Check if admin roles are selected for edit form
const isAdminInEditForm = computed(() => {
  const selectedRoles = props.roles.filter(role => editForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'admin');
});

// Check if owner roles are selected for create form
const isOwnerInCreateForm = computed(() => {
  const selectedRoles = props.roles.filter(role => createForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'owner');
});

// Check if owner roles are selected for edit form
const isOwnerInEditForm = computed(() => {
  const selectedRoles = props.roles.filter(role => editForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'owner');
});

// Check if seller roles are selected for create form
const isSellerInCreateForm = computed(() => {
  const selectedRoles = props.roles.filter(role => createForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'seller');
});

// Check if customer roles are selected for create form
const isCustomerInCreateForm = computed(() => {
  const selectedRoles = props.roles.filter(role => createForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'customer');
});

// Check if supplier roles are selected for create form
const isSupplierInCreateForm = computed(() => {
  const selectedRoles = props.roles.filter(role => createForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'supplier');
});

// Check if seller roles are selected for edit form
const isSellerInEditForm = computed(() => {
  const selectedRoles = props.roles.filter(role => editForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'seller');
});

// Check if customer roles are selected for edit form
const isCustomerInEditForm = computed(() => {
  const selectedRoles = props.roles.filter(role => editForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'customer');
});

// Check if supplier roles are selected for edit form
const isSupplierInEditForm = computed(() => {
  const selectedRoles = props.roles.filter(role => editForm.role_ids.includes(role.id));
  return selectedRoles.some(role => role.name === 'supplier');
});

// Methods
const openCreateModal = () => {
  showCreateModal.value = true;
  createForm.reset();
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  createForm.reset();
};

const openEditModal = (user: User) => {
  selectedUser.value = user;
  editForm.name = user.name;
  editForm.email = user.email;
  editForm.role_ids = user.roles.map(role => role.id);

  // If user has a branch, set business_id to the branch's business_id
  if (user.branch && user.branch.id) {
    const branchObj = props.branches.find(b => b.id === user.branch.id);
    editForm.business_id = branchObj ? branchObj.business_id : null;
    editForm.branch_id = user.branch.id;
  } else {
    editForm.business_id = user.business && user.business.id ? user.business.id : null;
    editForm.branch_id = null;
  }

  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  selectedUser.value = null;
  editForm.reset();
};

const openPasswordModal = (user: User) => {
  selectedUser.value = user;
  passwordForm.reset();
  showPasswordModal.value = true;
};

const closePasswordModal = () => {
  showPasswordModal.value = false;
  selectedUser.value = null;
  passwordForm.reset();
};

const openPermissionsModal = (user: User) => {
  selectedUser.value = user;
  // Get user's all effective permissions (direct + via roles)
  permissionsForm.permission_ids = user.all_permissions.map(p => p.id);
  showPermissionsModal.value = true;
};

const closePermissionsModal = () => {
  showPermissionsModal.value = false;
  selectedUser.value = null;
  permissionsForm.reset();
};

const toggleBusinessesDropdown = (userId: number) => {
  const currentState = userDropdownStates.value.get(userId) || { businesses: false, branches: false };
  userDropdownStates.value.set(userId, { 
    businesses: !currentState.businesses, 
    branches: false 
  });
};

const toggleBranchesDropdown = (userId: number) => {
  const currentState = userDropdownStates.value.get(userId) || { businesses: false, branches: false };
  userDropdownStates.value.set(userId, { 
    businesses: false, 
    branches: !currentState.branches 
  });
};

const closeDropdowns = () => {
  userDropdownStates.value.clear();
};

const handleCreateUser = async () => {
  // Business/branch validation for roles
  const businessRequired = isSellerInCreateForm.value || isCustomerInCreateForm.value || isSupplierInCreateForm.value;
  const branchRequired = isSellerInCreateForm.value || isCustomerInCreateForm.value;
  if (businessRequired && !createForm.business_id) {
    Swal.fire({
      icon: 'error',
      title: 'Business Required',
      text: 'Please select a business for this user.',
      confirmButtonText: 'OK',
    });
    return;
  }
  if (branchRequired && !createForm.branch_id) {
    Swal.fire({
      icon: 'error',
      title: 'Branch Required',
      text: 'Please select a branch for this user.',
      confirmButtonText: 'OK',
    });
    return;
  }
  try {
    await createForm.post('/users', {
      onSuccess: () => {
        closeCreateModal();
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: Object.values(errors).join('\n'),
          confirmButtonText: 'OK',
        });
      }
    });
  } catch (error) {
    console.error('Error creating user:', error);
  }
};

const handleUpdateUser = async () => {
  if (!selectedUser.value) return;
  // Business/branch validation for roles
  const businessRequired = isSellerInEditForm.value || isCustomerInEditForm.value || isSupplierInEditForm.value;
  const branchRequired = isSellerInEditForm.value || isCustomerInEditForm.value;
  if (businessRequired && !editForm.business_id) {
    Swal.fire({
      icon: 'error',
      title: 'Business Required',
      text: 'Please select a business for this user.',
      confirmButtonText: 'OK',
    });
    return;
  }
  if (branchRequired && !editForm.branch_id) {
    Swal.fire({
      icon: 'error',
      title: 'Branch Required',
      text: 'Please select a branch for this user.',
      confirmButtonText: 'OK',
    });
    return;
  }
  try {
    await editForm.put(`/users/${selectedUser.value.id}`, {
      onSuccess: () => {
        closeEditModal();
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: Object.values(errors).join('\n'),
          confirmButtonText: 'OK',
        });
      }
    });
  } catch (error) {
    console.error('Error updating user:', error);
  }
};

const handleUpdatePassword = async () => {
  if (!selectedUser.value) return;

  try {
    await passwordForm.patch(`/users/${selectedUser.value.id}/password`, {
      onSuccess: () => {
        closePasswordModal();
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: Object.values(errors).join('\n'),
          confirmButtonText: 'OK',
        });
      }
    });
  } catch (error) {
    console.error('Error updating password:', error);
  }
};

const handleAssignPermissions = async () => {
  if (!selectedUser.value) return;

  try {
    await permissionsForm.post(`/users/${selectedUser.value.id}/permissions`, {
      onSuccess: () => {
        closePermissionsModal();
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: Object.values(errors).join('\n'),
          confirmButtonText: 'OK',
        });
      }
    });
  } catch (error) {
    console.error('Error assigning permissions:', error);
  }
};

const handleDeleteUser = async (user: User) => {
  try {
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: `Do you want to delete ${user.name}? This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete user',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#ef4444',
    });

    if (result.isConfirmed) {
      await router.delete(`/users/${user.id}`, {
        onError: () => {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to delete user',
            confirmButtonText: 'OK',
          });
        }
      });
    }
  } catch (error) {
    console.error('Error deleting user:', error);
  }
};

const toggleUserStatus = async (user: User) => {
  try {
    const action = user.is_online ? 'deactivate' : 'activate';
    const result = await Swal.fire({
      title: `Confirm User ${action.charAt(0).toUpperCase() + action.slice(1)}`,
      text: `Do you want to ${action} the user ${user.name} (${user.email})?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: `Yes, ${action} user`,
      cancelButtonText: 'Cancel',
      confirmButtonColor: user.is_online ? '#ef4444' : '#10b981',
    });

    if (result.isConfirmed) {
      await router.patch(`/users/${user.id}/status`, {}, {
        preserveState: false,
      });
    }
  } catch (error) {
    console.error('Error toggling user status:', error);
  }
};

const toggleEmailVerification = async (user: User) => {
  try {
    const action = user.email_verified_at ? 'unverify' : 'verify';
    const result = await Swal.fire({
      title: `Confirm Email ${action.charAt(0).toUpperCase() + action.slice(1)}`,
      text: `Do you want to ${action} the email for ${user.name} (${user.email})?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: `Yes, ${action} email`,
      cancelButtonText: 'Cancel',
      confirmButtonColor: user.email_verified_at ? '#ef4444' : '#10b981',
    });

    if (result.isConfirmed) {
      const response = await fetch(`/users/${user.id}/email-verification`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      const data = await response.json();

      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: data.message,
          timer: 3000,
          showConfirmButton: false,
        });
        
        // Refresh the page to update the UI
        window.location.reload();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: data.message,
          confirmButtonText: 'OK',
        });
      }
    }
  } catch (error) {
    console.error('Error toggling email verification:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to update email verification status',
      confirmButtonText: 'OK',
    });
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getRoleBadgeColor = (roleName: string) => {
  const colors = {
    admin: 'bg-red-100 text-red-800',
    owner: 'bg-purple-100 text-purple-800',
    seller: 'bg-blue-100 text-blue-800',
    customer: 'bg-green-100 text-green-800',
    supplier: 'bg-yellow-100 text-yellow-800',
  };
  return colors[roleName as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

// Methods
function handleRoleChange(role) {
  // Only one of the main roles can be selected at a time
  const mainRoles = ['owner', 'admin', 'seller', 'customer', 'supplier'];
  if (mainRoles.includes(role.name)) {
    createForm.role_ids = [role.id];
  } else {
    // For any other roles, just toggle as normal (if you have any)
    if (!createForm.role_ids.includes(role.id)) {
      createForm.role_ids.push(role.id);
    } else {
      createForm.role_ids = createForm.role_ids.filter(id => id !== role.id);
    }
  }
}

function handleEditRoleChange(role) {
  // Only one of the main roles can be selected at a time
  const mainRoles = ['owner', 'admin', 'seller', 'customer', 'supplier'];
  if (mainRoles.includes(role.name)) {
    editForm.role_ids = [role.id];
  } else {
    // For any other roles, just toggle as normal (if you have any)
    if (!editForm.role_ids.includes(role.id)) {
      editForm.role_ids.push(role.id);
    } else {
      editForm.role_ids = editForm.role_ids.filter(id => id !== role.id);
    }
  }
}

// Watchers
watch(isAdminSelected, (newValue) => {
  if (newValue) {
    createForm.business_id = null;
    createForm.branch_id = null;
    editForm.business_id = null;
    editForm.branch_id = null;
  }
});

watch(isAdminInCreateForm, (newValue) => {
  if (newValue) {
    createForm.business_id = null;
    createForm.branch_id = null;
  }
});

watch(isAdminInEditForm, (newValue) => {
  if (newValue) {
    editForm.business_id = null;
    editForm.branch_id = null;
  }
});

// In the edit modal, add a watcher for editForm.business_id to clear branch when business changes
watch(
  () => editForm.business_id,
  (newBusinessId, oldBusinessId) => {
    if (newBusinessId !== oldBusinessId) {
      editForm.branch_id = null;
    }
  }
);
</script>

<template>
  <AppLayout>
    <Head title="User Management" />

    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Management
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            Manage system users, roles, and permissions
          </p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <Plus class="w-4 h-4 mr-2" />
          Add User
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Users class="h-8 w-8 text-indigo-600" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Users
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ users.length }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CheckCircle class="h-8 w-8 text-green-600" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Online Users
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ users.filter(u => u.is_online).length }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <BadgeCheck class="h-8 w-8 text-blue-600" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Verified Users
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ users.filter(u => u.email_verified_at).length }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Shield class="h-8 w-8 text-purple-600" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Admin Users
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ users.filter(u => u.roles.some(r => r.name === 'admin')).length }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1">
                <div class="relative">
                  <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users by name or email..."
                    class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
              </div>
              
              <div class="flex gap-4">
                <select
                  v-model="selectedRole"
                  class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">All Roles</option>
                  <option v-for="role in roleOptions" :key="role.value" :value="role.value">
                    {{ role.label }}
                  </option>
                </select>

                <select
                  v-model="selectedBusiness"
                  class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">All Businesses</option>
                  <option v-for="business in businessOptions" :key="business.value" :value="business.value">
                    {{ business.label }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Roles
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Business/Branch
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Email Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Created
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <User class="h-6 w-6 text-indigo-600" />
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ user.name }}
                          </div>
                          <div class="text-sm text-gray-500 flex items-center">
                            <Mail class="h-4 w-4 mr-1" />
                            {{ user.email }}
                          </div>
                        </div>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-wrap gap-1">
                        <span
                          v-for="role in user.roles"
                          :key="role.id"
                          :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getRoleBadgeColor(role.name)}`"
                        >
                          {{ role.name }}
                        </span>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <!-- For owners, show dropdowns for all their businesses and branches -->
                      <div v-if="user.roles.some(role => role.name === 'owner')" class="relative dropdown-container">
                        <div class="flex flex-col space-y-2">
                          <!-- Businesses Dropdown -->
                          <div class="relative">
                            <button 
                              @click="toggleBusinessesDropdown(user.id)"
                              class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                              <span class="flex items-center">
                                <Building class="h-4 w-4 mr-2 text-blue-500" />
                                Businesses ({{ user.businesses.length }})
                              </span>
                              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                              </svg>
                            </button>
                            
                            <!-- Businesses Dropdown Menu -->
                            <div v-if="userDropdownStates.get(user.id)?.businesses && user.businesses.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                              <div class="py-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                  All Businesses
                                </div>
                                <div v-for="business in user.businesses" :key="business.id" class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                  <Link 
                                    :href="`/admin/businesses/${business.id}`"
                                    class="flex items-center w-full text-gray-700 hover:text-indigo-600 transition-colors"
                                  >
                                    <Building class="h-4 w-4 mr-2 text-blue-500" />
                                    {{ business.name }}
                                  </Link>
                                </div>
                              </div>
                            </div>
                          </div>
                          
                          <!-- Branches Dropdown -->
                          <div class="relative">
                            <button 
                              @click="toggleBranchesDropdown(user.id)"
                              class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                              <span class="flex items-center">
                                <MapPin class="h-4 w-4 mr-2 text-green-500" />
                                Branches ({{ user.branches.length }})
                              </span>
                              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                              </svg>
                            </button>
                            
                            <!-- Branches Dropdown Menu -->
                            <div v-if="userDropdownStates.get(user.id)?.branches && user.branches.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                              <div class="py-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                  All Branches
                                </div>
                                <div v-for="branch in user.branches" :key="branch.id" class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                  <Link 
                                    :href="`/admin/branches/${branch.id}`"
                                    class="flex items-center w-full text-gray-700 hover:text-indigo-600 transition-colors"
                                  >
                                    <MapPin class="h-4 w-4 mr-2 text-green-500" />
                                    <span>{{ branch.name }}</span>
                                    <span class="ml-2 text-xs text-gray-400">({{ branch.business_name }})</span>
                                  </Link>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Show message if no businesses/branches -->
                        <div v-if="user.businesses.length === 0 && user.branches.length === 0" class="text-gray-400 text-sm">
                          No businesses/branches
                        </div>
                      </div>
                      
                      <!-- For non-owners, show single business/branch assignment -->
                      <div v-else>
                        <div v-if="user.business" class="flex items-center">
                          <Building class="h-4 w-4 mr-1" />
                          <Link 
                            :href="`/admin/businesses/${user.business.id}`"
                            class="text-gray-700 hover:text-indigo-600 transition-colors"
                          >
                            {{ user.business.name }}
                          </Link>
                        </div>
                        <div v-if="user.branch" class="flex items-center mt-1">
                          <MapPin class="h-4 w-4 mr-1" />
                          <Link 
                            :href="`/admin/branches/${user.branch.id}`"
                            class="text-gray-700 hover:text-indigo-600 transition-colors"
                          >
                            {{ user.branch.name }}
                          </Link>
                        </div>
                        <div v-if="!user.business && !user.branch" class="text-gray-400">
                          No assignment
                        </div>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <button 
                        @click="toggleUserStatus(user)"
                        class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors duration-200 w-full"
                        :title="user.is_online ? 'Click to deactivate user' : 'Click to activate user'"
                      >
                        <div class="flex items-center flex-1">
                          <div
                            :class="`h-3 w-3 rounded-full mr-3 ${user.is_online ? 'bg-green-400' : 'bg-gray-400'}`"
                          ></div>
                          <span :class="`text-sm font-medium ${user.is_online ? 'text-green-600' : 'text-gray-500'}`">
                            {{ user.is_online ? 'Online' : 'Offline' }}
                          </span>
                        </div>
                        <div class="ml-2">
                          <CheckCircle v-if="user.is_online" class="h-4 w-4 text-green-500" />
                          <XCircle v-else class="h-4 w-4 text-gray-500" />
                        </div>
                      </button>
                      <div v-if="user.last_seen_at && !user.is_online" class="text-xs text-gray-400 mt-1 ml-5">
                        Last seen: {{ formatDate(user.last_seen_at) }}
                      </div>
                      <div v-else-if="!user.is_online" class="text-xs text-gray-400 mt-1 ml-5">
                        Click to activate
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <button 
                        @click="toggleEmailVerification(user)"
                        class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors duration-200 w-full"
                        :title="user.email_verified_at ? 'Click to unverify email' : 'Click to verify email'"
                      >
                        <div class="flex items-center flex-1">
                          <div
                            :class="`h-3 w-3 rounded-full mr-3 ${user.email_verified_at ? 'bg-green-400' : 'bg-red-400'}`"
                          ></div>
                          <span :class="`text-sm font-medium ${user.email_verified_at ? 'text-green-600' : 'text-red-600'}`">
                            {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                          </span>
                        </div>
                        <div class="ml-2">
                          <MailCheck v-if="user.email_verified_at" class="h-4 w-4 text-green-500" />
                          <MailX v-else class="h-4 w-4 text-red-500" />
                        </div>
                      </button>
                      <div v-if="user.email_verified_at" class="text-xs text-gray-400 mt-1 ml-5">
                        Verified: {{ formatDate(user.email_verified_at) }}
                      </div>
                      <div v-else class="text-xs text-gray-400 mt-1 ml-5">
                        Click to verify
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div class="flex items-center">
                        <Calendar class="h-4 w-4 mr-1" />
                        {{ formatDate(user.created_at) }}
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex items-center justify-end space-x-2">
                        <button
                          @click="openEditModal(user)"
                          class="text-indigo-600 hover:text-indigo-900 p-1"
                          title="Edit User"
                        >
                          <Edit class="h-4 w-4" />
                        </button>
                        
                        <button
                          @click="openPasswordModal(user)"
                          class="text-yellow-600 hover:text-yellow-900 p-1"
                          title="Change Password"
                        >
                          <Key class="h-4 w-4" />
                        </button>
                        
                        <button
                          @click="openPermissionsModal(user)"
                          class="text-purple-600 hover:text-purple-900 p-1"
                          title="Manage Permissions"
                        >
                          <Shield class="h-4 w-4" />
                        </button>
                        
                        <button
                          @click="handleDeleteUser(user)"
                          class="text-red-600 hover:text-red-900 p-1"
                          title="Delete User"
                        >
                          <Trash2 class="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="filteredUsers.length === 0" class="text-center py-12">
                <Users class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                <p class="mt-1 text-sm text-gray-500">
                  {{ searchQuery || selectedRole || selectedBusiness ? 'Try adjusting your search or filters.' : 'Get started by creating a new user.' }}
                </p>
                <div class="mt-6">
                  <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    <Plus class="w-4 h-4 mr-2" />
                    Add User
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create User Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Create New User</h3>
            <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
              <XCircle class="h-6 w-6" />
            </button>
          </div>
          
          <form @submit.prevent="handleCreateUser">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input
                  v-model="createForm.name"
                  type="text"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <div v-if="createForm.errors.name" class="text-red-500 text-sm mt-1">
                  {{ createForm.errors.name }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                  v-model="createForm.email"
                  type="email"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <div v-if="createForm.errors.email" class="text-red-500 text-sm mt-1">
                  {{ createForm.errors.email }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input
                  v-model="createForm.password"
                  type="password"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <div v-if="createForm.errors.password" class="text-red-500 text-sm mt-1">
                  {{ createForm.errors.password }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input
                  v-model="createForm.password_confirmation"
                  type="password"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Roles</label>
                <div class="mt-2 space-y-2">
                  <label v-for="role in roles" :key="role.id" class="flex items-center">
                    <input
                      v-model="createForm.role_ids"
                      type="checkbox"
                      :value="role.id"
                      class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      @change="handleRoleChange(role)"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ role.name }}</span>
                  </label>
                </div>
                <div v-if="createForm.errors.role_ids" class="text-red-500 text-sm mt-1">
                  {{ createForm.errors.role_ids }}
                </div>
              </div>
              
              <!-- Show business and branch fields only for non-admin/owner roles -->
              <div v-if="!isAdminInCreateForm && !isOwnerInCreateForm">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Business</label>
                  <select
                    v-model="createForm.business_id"
                    :required="isSellerInCreateForm || isCustomerInCreateForm || isSupplierInCreateForm"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="">Select Business</option>
                    <option v-for="business in businesses" :key="business.id" :value="business.id">
                      {{ business.name }}
                    </option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700">Branch</label>
                  <select
                    v-model="createForm.branch_id"
                    :required="isSellerInCreateForm || isCustomerInCreateForm"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="">Select Branch</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                      {{ branch.name }}
                    </option>
                  </select>
                </div>
              </div>
              
              <!-- Show info message for admin roles -->
              <div v-if="isAdminInCreateForm || isOwnerInCreateForm" class="bg-blue-50 border border-blue-200 rounded-md p-3">
                <div class="flex">
                  <AlertCircle class="h-5 w-5 text-blue-400 mr-2" />
                  <div class="text-sm text-blue-700">
                    <p class="font-medium">Admin/Owner Role Selected</p>
                    <p class="mt-1">Business and branch assignments are not required for admin or owner roles as they manage the entire system.</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="createForm.processing"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ createForm.processing ? 'Creating...' : 'Create User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Edit User</h3>
            <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
              <XCircle class="h-6 w-6" />
            </button>
          </div>
          
          <form @submit.prevent="handleUpdateUser">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input
                  v-model="editForm.name"
                  type="text"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <div v-if="editForm.errors.name" class="text-red-500 text-sm mt-1">
                  {{ editForm.errors.name }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                  v-model="editForm.email"
                  type="email"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <div v-if="editForm.errors.email" class="text-red-500 text-sm mt-1">
                  {{ editForm.errors.email }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Roles</label>
                <div class="mt-2 space-y-2">
                  <label v-for="role in roles" :key="role.id" class="flex items-center">
                    <input
                      v-model="editForm.role_ids"
                      type="checkbox"
                      :value="role.id"
                      class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      @change="handleEditRoleChange(role)"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ role.name }}</span>
                  </label>
                </div>
                <div v-if="editForm.errors.role_ids" class="text-red-500 text-sm mt-1">
                  {{ editForm.errors.role_ids }}
                </div>
              </div>
              
              <!-- Show business and branch fields only for non-admin/owner roles -->
              <div v-if="!isAdminInEditForm && !isOwnerInEditForm">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Business</label>
                  <select
                    v-model="editForm.business_id"
                    :required="isSellerInEditForm || isCustomerInEditForm || isSupplierInEditForm"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="">Select Business</option>
                    <option v-for="business in businesses" :key="business.id" :value="business.id">
                      {{ business.name }}
                    </option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700">Branch</label>
                  <select
                    v-model="editForm.branch_id"
                    :required="isSellerInEditForm || isCustomerInEditForm"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="">Select Branch</option>
                    <option v-for="branch in availableBranches" :key="branch.id" :value="branch.id">
                      {{ branch.name }}
                    </option>
                  </select>
                </div>
              </div>
              
              <!-- Show info message for admin roles -->
              <div v-if="isAdminInEditForm || isOwnerInEditForm" class="bg-blue-50 border border-blue-200 rounded-md p-3">
                <div class="flex">
                  <AlertCircle class="h-5 w-5 text-blue-400 mr-2" />
                  <div class="text-sm text-blue-700">
                    <p class="font-medium">Admin/Owner Role Selected</p>
                    <p class="mt-1">Business and branch assignments are not required for admin or owner roles as they manage the entire system.</p>
                  </div>
                </div>
              </div>

              <!-- In the edit modal, above the branch dropdown: -->
              <div v-if="editForm.branch_id">
                <span class="text-xs text-gray-500">Current branch: {{ branches.find(b => b.id === editForm.branch_id)?.name }}</span>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="closeEditModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="editForm.processing"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ editForm.processing ? 'Updating...' : 'Update User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Change Password Modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
            <button @click="closePasswordModal" class="text-gray-400 hover:text-gray-600">
              <XCircle class="h-6 w-6" />
            </button>
          </div>
          
          <form @submit.prevent="handleUpdatePassword">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input
                  v-model="passwordForm.password"
                  type="password"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <div v-if="passwordForm.errors.password" class="text-red-500 text-sm mt-1">
                  {{ passwordForm.errors.password }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="closePasswordModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="passwordForm.processing"
                class="px-4 py-2 bg-yellow-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-yellow-700 disabled:opacity-50"
              >
                {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Permissions Modal -->
    <div v-if="showPermissionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-h-96 overflow-y-auto">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Manage Permissions</h3>
            <button @click="closePermissionsModal" class="text-gray-400 hover:text-gray-600">
              <XCircle class="h-6 w-6" />
            </button>
          </div>
          
          <form @submit.prevent="handleAssignPermissions">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Permissions</label>
                <div class="mt-2 space-y-2 max-h-64 overflow-y-auto">
                  <label v-for="permission in permissions" :key="permission.id" class="flex items-center">
                    <input
                      v-model="permissionsForm.permission_ids"
                      type="checkbox"
                      :value="permission.id"
                      class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ permission.name }}</span>
                  </label>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="closePermissionsModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="permissionsForm.processing"
                class="px-4 py-2 bg-purple-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-purple-700 disabled:opacity-50"
              >
                {{ permissionsForm.processing ? 'Updating...' : 'Update Permissions' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 