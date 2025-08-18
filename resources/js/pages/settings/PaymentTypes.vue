<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center space-x-4">
        <Link href="/settings" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Payment Types
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="savePaymentTypes" class="space-y-8">
              
              <!-- Cash Payment -->
              <div class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-green-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Cash</h3>
                      <p class="text-sm text-gray-500">Accept cash payments</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="paymentTypes.cash.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                <div v-if="paymentTypes.cash.enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Display Name
                    </label>
                    <input 
                      v-model="paymentTypes.cash.displayName" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Cash"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Sort Order
                    </label>
                    <input 
                      v-model="paymentTypes.cash.sortOrder" 
                      type="number" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="1"
                    />
                  </div>
                </div>
              </div>

              <!-- Mobile Money - M-PESA -->
              <div class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Mobile Money</h3>
                      <p class="text-sm text-gray-500">Accept mobile money payments</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="paymentTypes.mobileMoney.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                <div v-if="paymentTypes.mobileMoney.enabled" class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Display Name
                      </label>
                      <input 
                        v-model="paymentTypes.mobileMoney.displayName" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Mobile Money"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sort Order
                      </label>
                      <input 
                        v-model="paymentTypes.mobileMoney.sortOrder" 
                        type="number" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="3"
                      />
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Provider
                      </label>
                      <select 
                        v-model="paymentTypes.mobileMoney.provider" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="mpesa">M-Pesa</option>
                        <option value="airtel" disabled>Airtel Money (Coming Soon)</option>
                        <option value="orange" disabled>Orange Money (Coming Soon)</option>
                        <option value="mtn" disabled>MTN Mobile Money (Coming Soon)</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Transaction Fee (%)
                      </label>
                      <input 
                        v-model="paymentTypes.mobileMoney.transactionFee" 
                        type="number" 
                        step="0.01" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="1.0"
                      />
                    </div>
                  </div>

                  <!-- M-PESA Configuration Section -->
                  <div v-if="paymentTypes.mobileMoney.provider === 'mpesa'" class="border-t pt-6">
                    <div class="flex items-center justify-between mb-4">
                      <h4 class="text-md font-medium text-gray-900">M-PESA Configuration</h4>
                      <span v-if="mpesaCredentials" class="text-sm text-green-600 bg-green-100 px-2 py-1 rounded">
                        ✓ Configured
                      </span>
                    </div>
                    
                    <!-- Step-by-step M-PESA Setup -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                      <!-- Step Indicator -->
                      <div class="mb-6">
                        <div class="flex items-center justify-center space-x-4">
                          <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                 :class="currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'">
                              1
                            </div>
                            <span class="ml-2 text-sm font-medium" :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-500'">
                              Select Business
                            </span>
                          </div>
                          <div class="w-8 h-1 bg-gray-200"></div>
                          <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                 :class="currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'">
                              2
                            </div>
                            <span class="ml-2 text-sm font-medium" :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-500'">
                              Select Branch
                            </span>
                          </div>
                          <div class="w-8 h-1 bg-gray-200"></div>
                          <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                 :class="currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'">
                              3
                            </div>
                            <span class="ml-2 text-sm font-medium" :class="currentStep >= 3 ? 'text-blue-600' : 'text-gray-500'">
                              Configure M-PESA
                            </span>
                          </div>
                        </div>
                      </div>

                      <!-- Step 1: Business Selection -->
                      <div v-if="currentStep === 1" class="space-y-4">
                        <h5 class="text-sm font-medium text-gray-700">Select Your Business</h5>
                        
                        <div v-if="businesses.length === 0" class="text-center py-4">
                          <div class="text-gray-400 mb-2">
                            <svg class="mx-auto h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                          </div>
                          <h6 class="text-sm font-medium text-gray-900 mb-1">No Business Found</h6>
                          <p class="text-xs text-gray-600 mb-3">You need to create a business first to configure M-PESA settings.</p>
                          <a href="/businesses/create" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Create Business
                          </a>
                        </div>

                        <div v-else>
                          <div class="space-y-2">
                            <div v-for="business in businesses" :key="business.id" 
                                 class="border rounded-lg p-3 cursor-pointer transition-colors"
                                 :class="selectedBusiness?.id === business.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                                 @click="selectBusiness(business)">
                              <div class="flex items-center justify-between">
                                <div>
                                  <h6 class="font-medium text-gray-900 text-sm">{{ business.name }}</h6>
                                  <p v-if="business.legal_name" class="text-xs text-gray-500">{{ business.legal_name }}</p>
                                </div>
                                <div v-if="selectedBusiness?.id === business.id" class="text-blue-600">
                                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                  </svg>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div v-if="selectedBusiness" class="mt-4">
                            <button type="button" @click="confirmBusinessSelection" 
                                    class="w-full bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                              Continue with {{ selectedBusiness.name }}
                            </button>
                          </div>
                        </div>
                      </div>

                      <!-- Step 2: Branch Selection -->
                      <div v-if="currentStep === 2" class="space-y-4">
                        <h5 class="text-sm font-medium text-gray-700">Select Branch for {{ selectedBusiness?.name }}</h5>
                        
                        <div v-if="loadingBranches" class="text-center py-4">
                          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                          <p class="mt-2 text-xs text-gray-600">Loading branches...</p>
                        </div>

                        <div v-else-if="branches.length === 0" class="text-center py-4">
                          <div class="text-gray-400 mb-2">
                            <svg class="mx-auto h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                          </div>
                          <h6 class="text-sm font-medium text-gray-900 mb-1">No Branches Found</h6>
                          <p class="text-xs text-gray-600 mb-3">This business doesn't have any branches yet. You need to create a branch to configure M-PESA settings.</p>
                          <a href="/branches/create" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Create Branch
                          </a>
                        </div>

                        <div v-else>
                          <div class="space-y-2">
                            <div v-for="branch in branches" :key="branch.id" 
                                 class="border rounded-lg p-3 cursor-pointer transition-colors"
                                 :class="selectedBranch?.id === branch.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                                 @click="selectBranch(branch)">
                              <div class="flex items-center justify-between">
                                <div>
                                  <h6 class="font-medium text-gray-900 text-sm">{{ branch.name }}</h6>
                                  <p class="text-xs text-gray-500">{{ branch.address }}</p>
                                  <p class="text-xs text-gray-500">{{ branch.phone }}</p>
                                </div>
                                <div v-if="selectedBranch?.id === branch.id" class="text-blue-600">
                                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                  </svg>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div v-if="selectedBranch" class="mt-4">
                            <button type="button" @click="confirmBranchSelection" 
                                    class="w-full bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                              Continue with {{ selectedBranch.name }}
                            </button>
                          </div>
                        </div>

                        <div class="mt-3">
                          <button type="button" @click="goBackToStep1" class="text-blue-600 hover:text-blue-700 text-xs">
                            ← Back to Business Selection
                          </button>
                        </div>
                      </div>

                      <!-- Step 3: M-PESA Configuration -->
                      <div v-if="currentStep === 3" class="space-y-4">
                        <h5 class="text-sm font-medium text-gray-700">M-PESA Configuration for {{ selectedBranch?.name }}</h5>
                        
                        <!-- Credentials Status Indicator -->
                        <div v-if="loadingCredentials" class="bg-blue-50 border border-blue-200 rounded-md p-3">
                          <div class="flex items-center">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                            <span class="text-sm text-blue-700">Loading credentials...</span>
                          </div>
                        </div>
                        
                        <div v-else-if="credentialsLoaded" class="bg-green-50 border border-green-200 rounded-md p-3">
                          <div class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-sm text-green-700">Credentials loaded for {{ mpesaForm.environment }} environment</span>
                          </div>
                        </div>
                        
                        <!-- M-PESA Credentials Form -->
                        <div class="space-y-4">
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                              <label class="block text-xs font-medium text-gray-700 mb-1">
                                Environment
                              </label>
                              <select v-model="mpesaForm.environment" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="sandbox">Sandbox (Testing)</option>
                                <option value="live">Live (Production)</option>
                              </select>
                            </div>
                            
                            <div>
                              <label class="block text-xs font-medium text-gray-700 mb-1">
                                Business Shortcode
                              </label>
                              <input v-model="mpesaForm.business_shortcode" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="174379" />
                            </div>
                            
                            <div>
                              <label class="block text-xs font-medium text-gray-700 mb-1">
                                Consumer Key
                              </label>
                              <input v-model="mpesaForm.consumer_key" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Your Consumer Key" />
                            </div>
                            
                            <div>
                              <label class="block text-xs font-medium text-gray-700 mb-1">
                                Consumer Secret
                              </label>
                              <input v-model="mpesaForm.consumer_secret" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Your Consumer Secret" />
                            </div>
                            
                            <div class="md:col-span-2">
                              <label class="block text-xs font-medium text-gray-700 mb-1">
                                Passkey
                              </label>
                              <input v-model="mpesaForm.passkey" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Your Passkey" />
                            </div>
                            
                            <div class="md:col-span-2">
                              <label class="block text-xs font-medium text-gray-700 mb-1">
                                Description (Optional)
                              </label>
                              <input v-model="mpesaForm.description" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="M-PESA credentials for main branch" />
                            </div>
                          </div>

                          <!-- Test Credentials Section -->
                          <div class="border-t pt-4">
                            <h6 class="text-xs font-medium text-gray-700 mb-2">Test Credentials</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                              <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                  Test Phone Number
                                </label>
                                <input v-model="testForm.phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="0111383064 or 254111383064" />
                                <p class="text-xs text-gray-500 mt-1">Format: 0111383064 (10 digits) or 254111383064 (12 digits)</p>
                              </div>
                              <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                  Test Amount (KES)
                                </label>
                                <input v-model="testForm.amount" type="number" min="1" max="100000" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="1" />
                                <p class="text-xs text-gray-500 mt-1">Min: 1, Max: 100,000 KES</p>
                              </div>
                            </div>
                            <div class="flex space-x-2">
                              <button type="button" @click="testMpesaCredentials" :disabled="testingCredentials || !testForm.phone || !testForm.amount" class="bg-yellow-500 text-white px-3 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 disabled:opacity-50 text-xs">
                                {{ testingCredentials ? 'Testing...' : 'Test Credentials' }}
                              </button>
                              <button type="button" @click="saveMpesaCredentials" :disabled="savingCredentials" class="bg-green-600 text-white px-3 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 text-xs">
                                {{ savingCredentials ? 'Saving...' : 'Save Credentials' }}
                              </button>
                            </div>
                          </div>
                        </div>

                        <div class="mt-3">
                          <button type="button" @click="goBackToStep2" class="text-blue-600 hover:text-blue-700 text-xs">
                            ← Back to Branch Selection
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Callback URL Management Section -->
              <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                  <div>
                    <h3 class="text-lg font-medium text-gray-900">Callback URL Management</h3>
                    <p class="text-sm text-gray-500">Manage M-PESA callback URLs for different environments</p>
                  </div>
                  <button 
                    type="button" 
                    @click="loadCallbackUrls" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                  >
                    Refresh URLs
                  </button>
                </div>

                <!-- Environment Filter -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Environment</label>
                  <div class="flex space-x-4">
                    <label class="flex items-center">
                      <input 
                        type="radio" 
                        v-model="callbackUrlFilter" 
                        value="all" 
                        class="mr-2"
                      />
                      <span class="text-sm text-gray-700">All Environments</span>
                    </label>
                    <label class="flex items-center">
                      <input 
                        type="radio" 
                        v-model="callbackUrlFilter" 
                        value="sandbox" 
                        class="mr-2"
                      />
                      <span class="text-sm text-gray-700">Sandbox Only</span>
                    </label>
                    <label class="flex items-center">
                      <input 
                        type="radio" 
                        v-model="callbackUrlFilter" 
                        value="live" 
                        class="mr-2"
                      />
                      <span class="text-sm text-gray-700">Live Only</span>
                    </label>
                  </div>
                </div>

                <!-- Callback URLs List -->
                <div v-if="loadingCallbackUrls" class="text-center py-8">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                  <p class="text-sm text-gray-500">Loading callback URLs...</p>
                </div>

                <div v-else-if="callbackUrls.length === 0" class="text-center py-8">
                  <div class="text-gray-400 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                  </div>
                  <p class="text-gray-500">No callback URLs found</p>
                  <p class="text-sm text-gray-400">Callback URLs will be created automatically when you test M-PESA credentials</p>
                </div>

                <div v-else class="space-y-4">
                  <div 
                    v-for="url in filteredCallbackUrls" 
                    :key="url.id" 
                    class="border border-gray-200 rounded-lg p-4"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <div class="flex items-center space-x-2">
                        <span 
                          :class="[
                            'px-2 py-1 text-xs font-medium rounded-full',
                            url.environment === 'live' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                          ]"
                        >
                          {{ url.environment }}
                        </span>
                        <span 
                          :class="[
                            'px-2 py-1 text-xs font-medium rounded-full',
                            url.is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                          ]"
                        >
                          {{ url.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </div>
                      <div class="flex space-x-2">
                        <button 
                          @click="editCallbackUrl(url)" 
                          class="text-blue-600 hover:text-blue-700 text-sm"
                        >
                          Edit
                        </button>
                        <button 
                          @click="testCallbackUrl(url)" 
                          class="text-green-600 hover:text-green-700 text-sm"
                        >
                          Test
                        </button>
                      </div>
                    </div>
                    
                    <div class="space-y-1">
                      <p class="text-sm"><strong>URL:</strong> <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ url.callback_url }}</span></p>
                      <p class="text-sm"><strong>Payment Type:</strong> {{ url.payment_type }}</p>
                      <p class="text-sm"><strong>Provider:</strong> {{ url.provider }}</p>
                      <p v-if="url.description" class="text-sm"><strong>Description:</strong> {{ url.description }}</p>
                      <p class="text-sm"><strong>Last Callback:</strong> {{ url.last_callback_at || 'Never' }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card Payment - Coming Soon -->
              <div class="border-b border-gray-200 pb-6 opacity-50">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Card Payment</h3>
                      <p class="text-sm text-gray-500">Accept credit and debit cards</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">Coming Soon</span>
                    <div class="w-11 h-6 bg-gray-200 rounded-full"></div>
                  </div>
                </div>
              </div>

              <!-- Bank Transfer - Coming Soon -->
              <div class="border-b border-gray-200 pb-6 opacity-50">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <div class="bg-indigo-100 p-2 rounded-lg">
                      <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">Bank Transfer</h3>
                      <p class="text-sm text-gray-500">Accept bank transfers</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">Coming Soon</span>
                    <div class="w-11 h-6 bg-gray-200 rounded-full"></div>
                  </div>
                </div>
              </div>

              <!-- Save Button -->
              <div class="flex justify-end pt-6">
                <button 
                  type="submit" 
                  class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :disabled="saving"
                >
                  {{ saving ? 'Saving...' : 'Save Payment Types' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

// Props from backend
const props = defineProps({
  businesses: {
    type: Array,
    default: () => []
  },
  currentBusiness: {
    type: Object,
    default: null
  },
  currentBranch: {
    type: Object,
    default: null
  },
  mpesaCredentials: {
    type: Object,
    default: null
  },
  user: {
    type: Object,
    default: () => ({})
  }
});

// Reactive state
const saving = ref(false);
const currentStep = ref(1);
const selectedBusiness = ref(null);
const selectedBranch = ref(null);
const branches = ref([]);
const loadingBranches = ref(false);
const testingCredentials = ref(false);
const savingCredentials = ref(false);
const loadingCredentials = ref(false);
const credentialsLoaded = ref(false);

// Callback URL management
const callbackUrls = ref([]);
const loadingCallbackUrls = ref(false);
const callbackUrlFilter = ref('all');

// Form data
const paymentTypes = ref({
  cash: {
    enabled: true,
    displayName: 'Cash',
    sortOrder: 1,
  },
  mobileMoney: {
    enabled: false,
    displayName: 'Mobile Money',
    sortOrder: 3,
    provider: 'mpesa',
    transactionFee: 1.0,
  },
});

const mpesaForm = ref({
  environment: 'sandbox',
  consumer_key: '',
  consumer_secret: '',
  business_shortcode: '174379',
  passkey: '',
  description: '',
});

// Method to load M-PESA credentials for a specific branch and environment
const loadMpesaCredentials = async (branchId, environment) => {
  loadingCredentials.value = true;
  credentialsLoaded.value = false;
  
  try {
    const response = await fetch('/settings/payment-types/get-credentials', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ 
        branch_id: branchId, 
        environment: environment 
      }),
    });

    const result = await response.json();
    
    if (result.success && result.credentials) {
      // Auto-fill the form with existing credentials
      mpesaForm.value = {
        environment: result.credentials.environment,
        consumer_key: result.credentials.consumer_key,
        consumer_secret: result.credentials.consumer_secret,
        business_shortcode: result.credentials.business_shortcode,
        passkey: result.credentials.passkey,
        description: result.credentials.description || '',
      };
      credentialsLoaded.value = true;
    } else {
      // Clear the form if no credentials found
      mpesaForm.value = {
        environment: environment,
        consumer_key: '',
        consumer_secret: '',
        business_shortcode: environment === 'sandbox' ? '174379' : '',
        passkey: '',
        description: '',
      };
      credentialsLoaded.value = false;
    }
      } catch (error) {
      console.error('Error loading M-PESA credentials:', error);
      // Clear the form on error
      mpesaForm.value = {
        environment: environment,
        consumer_key: '',
        consumer_secret: '',
        business_shortcode: environment === 'sandbox' ? '174379' : '',
        passkey: '',
        description: '',
      };
      credentialsLoaded.value = false;
    } finally {
      loadingCredentials.value = false;
    }
};

const testForm = ref({
  phone: props.user?.phone || '254708374149',
  amount: '1',
});

// Computed properties
const hasBusiness = computed(() => props.businesses.length > 0);
const hasBranch = computed(() => branches.value.length > 0);

// Computed property for filtered callback URLs
const filteredCallbackUrls = computed(() => {
  if (callbackUrlFilter.value === 'all') {
    return callbackUrls.value;
  }
  return callbackUrls.value.filter(url => url.environment === callbackUrlFilter.value);
});

// Watch for environment changes to auto-load credentials
watch(() => mpesaForm.value.environment, async (newEnvironment) => {
  if (selectedBranch.value && newEnvironment) {
    await loadMpesaCredentials(selectedBranch.value.id, newEnvironment);
  }
});

// Initialize component
  onMounted(async () => {
  // Load existing credentials if available
  if (props.mpesaCredentials) {
    mpesaForm.value = {
      environment: props.mpesaCredentials.environment,
      consumer_key: props.mpesaCredentials.consumer_key,
      consumer_secret: props.mpesaCredentials.consumer_secret,
      business_shortcode: props.mpesaCredentials.business_shortcode,
      passkey: props.mpesaCredentials.passkey,
      description: props.mpesaCredentials.description || '',
    };
    paymentTypes.value.mobileMoney.enabled = true;
  }

  // If user already has business and branch selected, go to step 3
  if (props.user.business_id && props.user.branch_id) {
    selectedBusiness.value = props.currentBusiness;
    selectedBranch.value = props.currentBranch;
    currentStep.value = 3;
      // Auto-load credentials for the selected branch
      await loadMpesaCredentials(props.user.branch_id, mpesaForm.value.environment);
  } else if (props.user.business_id) {
    // If user has business but no branch, go to step 2
    selectedBusiness.value = props.currentBusiness;
    currentStep.value = 2;
    loadBranches(props.user.business_id);
  }

    // Load callback URLs
    await loadCallbackUrls();
});

// Methods
const selectBusiness = (business) => {
  selectedBusiness.value = business;
};

const confirmBusinessSelection = async () => {
  if (!selectedBusiness.value) return;
  
  try {
    const response = await fetch('/settings/payment-types/select-business', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ business_id: selectedBusiness.value.id }),
    });

    const result = await response.json();
    
    if (result.success) {
      currentStep.value = 2;
      await loadBranches(selectedBusiness.value.id);
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: result.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to select business. Please try again.',
    });
  }
};

const loadBranches = async (businessId) => {
  loadingBranches.value = true;
  
  try {
    const response = await fetch('/settings/payment-types/get-branches', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ business_id: businessId }),
    });

    const result = await response.json();
    
    if (result.success) {
      branches.value = result.branches;
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: result.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load branches. Please try again.',
    });
  } finally {
    loadingBranches.value = false;
  }
};

const selectBranch = async (branch) => {
  selectedBranch.value = branch;
  // Auto-load credentials for the selected branch and current environment
  if (branch && mpesaForm.value.environment) {
    await loadMpesaCredentials(branch.id, mpesaForm.value.environment);
  }
};

const confirmBranchSelection = async () => {
  if (!selectedBranch.value) return;
  
  try {
    const response = await fetch('/settings/payment-types/select-branch', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ branch_id: selectedBranch.value.id }),
    });

    const result = await response.json();
    
    if (result.success) {
      currentStep.value = 3;
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: result.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to select branch. Please try again.',
    });
  }
};

const goBackToStep1 = () => {
  currentStep.value = 1;
  selectedBusiness.value = null;
  selectedBranch.value = null;
  branches.value = [];
};

const goBackToStep2 = () => {
  currentStep.value = 2;
  selectedBranch.value = null;
};

const testMpesaCredentials = async () => {
  if (!mpesaForm.value.consumer_key || !mpesaForm.value.consumer_secret || 
      !mpesaForm.value.business_shortcode || !mpesaForm.value.passkey) {
    Swal.fire({
      icon: 'error',
      title: 'Missing Information',
      text: 'Please fill in all M-PESA credentials before testing.',
    });
    return;
  }

  if (!testForm.value.phone || !testForm.value.amount) {
    Swal.fire({
      icon: 'error',
      title: 'Missing Test Information',
      text: 'Please fill in both test phone number and amount.',
    });
    return;
  }

  // Validate phone number format - accept both local (0111383064) and international (254111383064) formats
  const localPhoneRegex = /^0[0-9]{9}$/;  // 10 digits starting with 0
  const internationalPhoneRegex = /^254[0-9]{9}$/;  // 12 digits starting with 254
  
  if (!localPhoneRegex.test(testForm.value.phone) && !internationalPhoneRegex.test(testForm.value.phone)) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Phone Number',
      text: 'Phone number must be in format: 0111383064 (10 digits) or 254111383064 (12 digits)',
    });
    return;
  }

  // Validate amount
  const amount = parseFloat(testForm.value.amount);
  if (amount < 1 || amount > 100000) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Amount',
      text: 'Amount must be between 1 and 100,000 KES',
    });
    return;
  }

  testingCredentials.value = true;
  
  // Show "sending STK push" dialog
  const sendingDialog = Swal.fire({
    title: 'Sending STK Push',
    html: `
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Sending STK push to ${testForm.value.phone}</p>
        <p class="text-gray-600">Amount: KES ${testForm.value.amount}</p>
        <p class="text-sm text-gray-500 mt-2">Please wait...</p>
      </div>
    `,
    showConfirmButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false,
  });
  
  try {
    const requestData = {
      ...mpesaForm.value,
      test_phone: testForm.value.phone,
      test_amount: testForm.value.amount,
    };

    const response = await fetch('/settings/payment-types/test-mpesa', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(requestData),
    });

    const result = await response.json();

    // Close the sending dialog
    sendingDialog.close();

    if (result.success) {
      const waitingDialog = Swal.fire({
        title: 'Waiting for Payment Response',
        html: `
          <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">STK push sent to ${result.test_phone}</p>
            <p class="text-gray-600">Amount: KES ${result.test_amount}</p>
            <p class="text-sm text-gray-500 mt-2">Please check your phone and enter M-PESA PIN</p>
            <p class="text-sm text-gray-500">Waiting for payment response...</p>
            <div class="mt-4 text-xs text-gray-400">
              <p>Checkout Request ID: ${result.checkout_request_id}</p>
            </div>
          </div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
      });

      // Poll for callback response
      await pollForCallbackResponse(result.checkout_request_id, waitingDialog);
    } else {
      let icon = 'error';
      let title = 'Test Failed';
      let html = `<p>${result.message}</p>`;
      
      // Add specific error details based on error type
      if (result.error_type) {
        switch (result.error_type) {
          case 'authentication_failed':
            title = 'Authentication Failed';
            html += `
              <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-700"><strong>Solution:</strong> Please check your Consumer Key and Consumer Secret.</p>
              </div>
            `;
            break;
          case 'token_expired':
            title = 'Token Expired';
            html += `
              <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm text-yellow-700"><strong>Note:</strong> The STK push was sent successfully, but we couldn't verify the result due to token expiration.</p>
                <p class="text-sm text-yellow-600 mt-1">Check your phone for the payment prompt.</p>
              </div>
            `;
            break;
          case 'network_error':
            title = 'Network Error';
            html += `
              <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                <p class="text-sm text-blue-700"><strong>Solution:</strong> Please check your internet connection and try again.</p>
              </div>
            `;
            break;
          case 'http_403':
            title = 'Access Denied (403)';
            html += `
              <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-700"><strong>Issue:</strong> Access denied by M-PESA API.</p>
                <p class="text-sm text-red-600 mt-1"><strong>Possible causes:</strong></p>
                <ul class="text-sm text-red-600 mt-1 list-disc list-inside">
                  <li>Invalid Consumer Key or Consumer Secret</li>
                  <li>Account not activated for M-PESA API</li>
                  <li>IP address not whitelisted</li>
                  <li>Account suspended or expired</li>
                </ul>
              </div>
            `;
            break;
          case 'http_401':
            title = 'Unauthorized (401)';
            html += `
              <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-700"><strong>Issue:</strong> Authentication failed.</p>
                <p class="text-sm text-red-600 mt-1"><strong>Solution:</strong> Please check your Consumer Key and Consumer Secret.</p>
              </div>
            `;
            break;
          case 'timeout':
            title = 'Request Timeout';
            html += `
              <div class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded">
                <p class="text-sm text-orange-700"><strong>Note:</strong> The request took too long to complete. Please try again.</p>
              </div>
            `;
            break;
          case 'invalid_shortcode':
            title = 'Invalid Business Shortcode';
            html += `
              <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-700"><strong>Solution:</strong> Please check your Business Shortcode.</p>
              </div>
            `;
            break;
          case 'invalid_phone':
            title = 'Invalid Phone Number';
            html += `
              <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-700"><strong>Solution:</strong> Please check the phone number format.</p>
              </div>
            `;
            break;
          case 'no_branch_selected':
            title = 'No Branch Selected';
            html += `
              <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-700"><strong>Solution:</strong> Please select a branch before testing M-PESA credentials.</p>
              </div>
            `;
            break;
          // M-PESA specific error codes
          case 'timeout_user_unreachable':
            title = 'User Unreachable (1037)';
            html = result.message; // Use the detailed message from backend
            break;
          case 'system_error':
            title = 'System Error (1025/9999)';
            html = result.message; // Use the detailed message from backend
            break;
          case 'user_cancelled':
            title = 'User Cancelled (1032)';
            html = result.message; // Use the detailed message from backend
            break;
          case 'insufficient_balance':
            title = 'Insufficient Balance (1)';
            html = result.message; // Use the detailed message from backend
            break;
          case 'invalid_credentials':
            title = 'Invalid Credentials (2001)';
            html = result.message; // Use the detailed message from backend
            break;
          case 'transaction_expired':
            title = 'Transaction Expired (1019)';
            html = result.message; // Use the detailed message from backend
            break;
          case 'subscriber_locked':
            title = 'Subscriber Locked (1001)';
            html = result.message; // Use the detailed message from backend
            break;
          case 'ongoing_transaction':
            title = 'Ongoing Transaction Detected (1001)';
            html = `
              <div class="text-center">
                <p class="text-lg font-medium text-orange-600 mb-3">${result.message}</p>
                <div class="bg-orange-50 border border-orange-200 rounded-md p-4 text-left">
                  <h6 class="font-medium text-orange-800 mb-2">Issue:</h6>
                  <p class="text-sm text-orange-700 mb-3">${result.details}</p>
                  
                  <h6 class="font-medium text-orange-800 mb-2">Solutions:</h6>
                  <ul class="text-sm text-orange-700 space-y-1">
                    ${result.solutions ? result.solutions.map(solution => `<li>• ${solution}</li>`).join('') : ''}
                  </ul>
                  
                  <div class="mt-4 pt-3 border-t border-orange-200">
                    <button onclick="checkTransactionStatus()" class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 text-sm">
                      Check Transaction Status
                    </button>
                  </div>
                </div>
              </div>
            `;
            break;
          default:
            if (result.original_error) {
              html += `
                <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
                  <p class="text-sm text-gray-600"><strong>Technical Details:</strong> ${result.original_error}</p>
                </div>
              `;
            }
        }
      }
      
      // Add test details if available
      if (result.test_phone || result.test_amount || result.branch_name) {
        html += `
          <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
            <p class="text-sm text-gray-600"><strong>Test Details:</strong></p>
            <p class="text-sm text-gray-600">Phone: ${result.test_phone || result.original_phone || 'N/A'}</p>
            <p class="text-sm text-gray-600">Amount: KES ${result.test_amount || 'N/A'}</p>
            <p class="text-sm text-gray-600">Branch: ${result.branch_name || 'N/A'}</p>
          </div>
        `;
      }
      
      Swal.fire({
        icon: icon,
        title: title,
        html: html,
        confirmButtonText: 'OK',
        width: '32rem'
      });
    }
  } catch (error) {
    // Close the sending dialog
    sendingDialog.close();
    
    console.error('M-PESA test error:', error);
    
    Swal.fire({
      icon: 'error',
      title: 'Network Error',
      html: `
        <p>Failed to test credentials due to a network error.</p>
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
          <p class="text-sm text-red-700"><strong>Possible causes:</strong></p>
          <ul class="text-sm text-red-600 mt-1 list-disc list-inside">
            <li>Internet connection issues</li>
            <li>Server temporarily unavailable</li>
            <li>Browser network restrictions</li>
          </ul>
        </div>
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
          <p class="text-sm text-blue-700"><strong>Solution:</strong> Please check your internet connection and try again.</p>
        </div>
      `,
      confirmButtonText: 'OK',
      width: '32rem'
    });
  } finally {
    testingCredentials.value = false;
  }
};

const pollForCallbackResponse = async (checkoutRequestId, waitingDialog) => {
  const maxAttempts = 60; // 60 seconds
  const interval = 1000; // 1 second
  
  console.log('Starting to poll for callback response:', checkoutRequestId);
  
  for (let attempt = 0; attempt < maxAttempts; attempt++) {
    try {
      console.log(`Polling attempt ${attempt + 1}/${maxAttempts}`);
      
      const response = await fetch('/settings/payment-types/query-payment-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
          checkout_request_id: checkoutRequestId
        }),
      });

      const result = await response.json();
      console.log('Polling result:', result);
      
      if (result.success && result.callback_response) {
        console.log('Callback response found!', result.callback_response);
        
        // Clear the cache to prevent duplicate responses
        try {
          await fetch('/settings/payment-types/clear-callback-cache', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
              checkout_request_id: checkoutRequestId
            }),
          });
          console.log('Cache cleared successfully');
        } catch (error) {
          console.error('Failed to clear cache:', error);
        }
        
        // Close waiting dialog
        waitingDialog.close();
        
        // Display the callback response
        displayCallbackResponse(result.callback_response);
        return;
      }
      
      // Update waiting dialog with elapsed time
      const elapsedSeconds = attempt + 1;
      waitingDialog.update({
        html: `
          <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">STK push sent successfully</p>
            <p class="text-sm text-gray-500 mt-2">Please check your phone and enter M-PESA PIN</p>
            <p class="text-sm text-gray-500">Waiting for payment response... (${elapsedSeconds}s)</p>
            <div class="mt-4 text-xs text-gray-400">
              <p>Checkout Request ID: ${checkoutRequestId}</p>
              <p>Status: ${result.message}</p>
            </div>
          </div>
        `
      });
      
    } catch (error) {
      console.error('Error polling for callback response:', error);
    }
    
    // Wait before next attempt
    await new Promise(resolve => setTimeout(resolve, interval));
  }
  
  // Timeout reached
  waitingDialog.close();
  
  Swal.fire({
    icon: 'warning',
    title: 'Payment Response Timeout',
    html: `
      <div class="text-center">
        <p class="text-gray-600 mb-3">No payment response received within 60 seconds.</p>
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 text-left">
          <h6 class="font-medium text-yellow-800 mb-2">Possible reasons:</h6>
          <ul class="text-sm text-yellow-700 space-y-1">
            <li>• User didn't complete the payment on their phone</li>
            <li>• Payment was cancelled or timed out</li>
            <li>• Network connectivity issues</li>
            <li>• M-PESA system delays</li>
          </ul>
        </div>
        <div class="mt-3 text-xs text-gray-500">
          <p>Checkout Request ID: ${checkoutRequestId}</p>
          <p>You can check the payment status later in the callback logs.</p>
        </div>
      </div>
    `,
    confirmButtonText: 'OK',
    width: '36rem'
  });
};

const displayCallbackResponse = (callbackResponse) => {
      let icon = 'success';
      let title = 'Payment Successful!';
  let bgColor = 'bg-green-50';
  let borderColor = 'border-green-200';
  let textColor = 'text-green-800';
  
  // Determine status based on result code (handle both string and number types)
  const resultCode = String(callbackResponse.result_code);
  if (resultCode === '0') {
    icon = 'success';
    title = 'Payment Successful!';
    bgColor = 'bg-green-50';
    borderColor = 'border-green-200';
    textColor = 'text-green-800';
  } else if (resultCode === '1032') {
        icon = 'warning';
        title = 'Payment Cancelled';
    bgColor = 'bg-yellow-50';
    borderColor = 'border-yellow-200';
    textColor = 'text-yellow-800';
  } else if (resultCode === '1037') {
    icon = 'warning';
    title = 'Payment Timeout';
    bgColor = 'bg-orange-50';
    borderColor = 'border-orange-200';
    textColor = 'text-orange-800';
  } else {
        icon = 'error';
        title = 'Payment Failed';
    bgColor = 'bg-red-50';
    borderColor = 'border-red-200';
    textColor = 'text-red-800';
  }
  
  // Use test data if callback data is not available
  const displayAmount = callbackResponse.amount || callbackResponse.test_amount || 'N/A';
  const displayPhone = callbackResponse.phone_number || callbackResponse.test_phone || 'N/A';

      Swal.fire({
        icon: icon,
        title: title,
        html: `
      <div class="text-center">
        <div class="${bgColor} border ${borderColor} rounded-md p-4 text-left mb-4">
          <h6 class="font-medium ${textColor} mb-2">Payment Response Details:</h6>
          <div class="space-y-1 text-sm">
            <p><span class="font-medium">Result Code:</span> <span class="${textColor}">${callbackResponse.result_code}</span></p>
            <p><span class="font-medium">Result Description:</span> <span class="${textColor}">${callbackResponse.result_desc}</span></p>
            <p><span class="font-medium">Amount:</span> <span class="${textColor}">KES ${displayAmount}</span></p>
            <p><span class="font-medium">Phone Number:</span> <span class="${textColor}">${displayPhone}</span></p>
            <p><span class="font-medium">M-PESA Receipt:</span> <span class="${textColor}">${callbackResponse.mpesa_receipt_number || 'N/A'}</span></p>
            <p><span class="font-medium">Transaction Date:</span> <span class="${textColor}">${callbackResponse.transaction_date || 'N/A'}</span></p>
            <p><span class="font-medium">Balance:</span> <span class="${textColor}">${callbackResponse.balance || 'N/A'}</span></p>
          </div>
        </div>
        <div class="text-xs text-gray-500">
          <p>Checkout Request ID: ${callbackResponse.checkout_request_id}</p>
          <p>Merchant Request ID: ${callbackResponse.merchant_request_id}</p>
          <p>Response received at: ${callbackResponse.created_at}</p>
          ${callbackResponse.test_amount && !callbackResponse.amount ? '<p class="text-blue-600">* Amount shown from test data (transaction was cancelled)</p>' : ''}
        </div>
      </div>
    `,
    confirmButtonText: 'OK',
    width: '36rem'
  });
};

// Callback URL management functions
const loadCallbackUrls = async () => {
  loadingCallbackUrls.value = true;
  
  try {
    const params = new URLSearchParams();
    if (callbackUrlFilter.value !== 'all') {
      params.append('environment', callbackUrlFilter.value);
    }
    
    const response = await fetch(`/settings/payment-types/callback-urls?${params.toString()}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
    });

    const result = await response.json();
    
    if (result.success) {
      // Flatten the grouped URLs into a single array
      const allUrls = [];
      if (result.callback_urls.sandbox) {
        allUrls.push(...result.callback_urls.sandbox);
      }
      if (result.callback_urls.live) {
        allUrls.push(...result.callback_urls.live);
      }
      callbackUrls.value = allUrls;
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: result.message || 'Failed to load callback URLs',
      });
    }
  } catch (error) {
    console.error('Error loading callback URLs:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load callback URLs. Please try again.',
    });
  } finally {
    loadingCallbackUrls.value = false;
  }
};

const editCallbackUrl = (url) => {
  Swal.fire({
    title: 'Edit Callback URL',
    html: `
      <div class="text-left">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Callback URL</label>
          <input id="callback-url-input" type="url" value="${url.callback_url}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
          <input id="callback-description-input" type="text" value="${url.description || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Update',
    cancelButtonText: 'Cancel',
    preConfirm: () => {
      const newUrl = document.getElementById('callback-url-input').value;
      const description = document.getElementById('callback-description-input').value;
      
      if (!newUrl) {
        Swal.showValidationMessage('Callback URL is required');
        return false;
      }
      
      return { url: newUrl, description };
    }
  }).then((result) => {
    if (result.isConfirmed) {
      updateCallbackUrl(url.id, result.value.url, result.value.description);
    }
  });
};

const updateCallbackUrl = async (urlId, newUrl, description) => {
  try {
    const response = await fetch('/settings/payment-types/callback-urls/' + urlId, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({
        callback_url: newUrl,
        description: description
      }),
    });

    const result = await response.json();
    
    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: 'Updated Successfully!',
        text: 'Callback URL has been updated.',
      });
      // Reload callback URLs
      await loadCallbackUrls();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Update Failed',
        text: 'Failed to update callback URL',
      });
    }
  } catch (error) {
    console.error('Error updating callback URL:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to update callback URL. Please try again.',
    });
  }
};

const testCallbackUrl = async (url) => {
  try {
    Swal.fire({
      title: 'Testing Callback URL',
      html: `
        <div class="text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
          <p class="text-sm text-gray-600">Testing: ${url.callback_url}</p>
        </div>
      `,
      showConfirmButton: false,
      allowOutsideClick: false,
    });

    // Simulate a test callback
    const testData = {
      test: true,
      environment: url.environment,
      payment_type: url.payment_type,
      timestamp: new Date().toISOString()
    };

    const response = await fetch(url.callback_url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(testData),
    });

    Swal.close();

    if (response.ok) {
      Swal.fire({
        icon: 'success',
        title: 'Test Successful!',
        text: `Callback URL is accessible and responding correctly.`,
      });
    } else {
      Swal.fire({
        icon: 'warning',
        title: 'Test Completed',
        text: `Callback URL responded with status: ${response.status}`,
      });
    }
  } catch (error) {
    Swal.close();
    Swal.fire({
      icon: 'error',
      title: 'Test Failed',
      text: `Failed to reach callback URL: ${error.message}`,
    });
  }
};

// Watch for filter changes to reload URLs
watch(callbackUrlFilter, () => {
  loadCallbackUrls();
});

const saveMpesaCredentials = async () => {
  if (!mpesaForm.value.consumer_key || !mpesaForm.value.consumer_secret || 
      !mpesaForm.value.business_shortcode || !mpesaForm.value.passkey) {
    Swal.fire({
      icon: 'error',
      title: 'Missing Information',
      text: 'Please fill in all M-PESA credentials before saving.',
    });
    return;
  }

  savingCredentials.value = true;
  
  try {
    const response = await fetch('/settings/payment-types/save-mpesa', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(mpesaForm.value),
    });

    const result = await response.json();

    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: 'Saved Successfully!',
        text: result.message,
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Save Failed',
        text: result.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save credentials. Please try again.',
    });
  } finally {
    savingCredentials.value = false;
  }
};

const savePaymentTypes = async () => {
  saving.value = true;
  
  try {
    const response = await fetch('/settings/payment-types', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({
        paymentTypes: paymentTypes.value,
      }),
    });

    const result = await response.json();

    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: 'Payment Types Updated!',
        text: result.message,
        timer: 2000,
        showConfirmButton: false,
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to save payment types. Please try again.',
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save payment types. Please try again.',
    });
  } finally {
    saving.value = false;
  }
};
</script> 