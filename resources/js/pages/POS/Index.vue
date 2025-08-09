<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Success/Error Messages -->
    <div v-if="flashSuccess" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
      {{ flashSuccess }}
    </div>
    <div v-if="flashError" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      {{ flashError }}
    </div>
    
    <!-- Toaster Notification -->
    <div v-if="toaster.show" class="fixed top-4 right-4 z-50 max-w-sm">
      <div :class="[
        'px-4 py-3 rounded-lg shadow-lg border-l-4 transition-all duration-300 ease-in-out',
        toaster.type === 'success' 
          ? 'bg-green-50 border-green-400 text-green-800' 
          : 'bg-red-50 border-red-400 text-red-800'
      ]">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg v-if="toaster.type === 'success'" class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium">{{ toaster.message }}</p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="toaster.show = false" class="inline-flex text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Left Sidebar -->
    <div v-if="showSidebar" class="fixed inset-0 z-50 flex">
      <div class="fixed inset-0 bg-white/40 bg-opacity-50" @click="showSidebar = false"></div>
      <div class="relative w-80 bg-white shadow-xl">
        <!-- Header -->
        <div class="bg-green-600 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="text-white">
              <div class="text-xl font-bold">{{ user?.name || 'User' }}</div>
              <div class="text-sm opacity-90">Main POS</div>
              <div class="text-sm opacity-90">{{ business?.name || 'Business' }}</div>
            </div>
            <button @click="clockOut" class="p-2 bg-white bg-opacity-20 rounded-full hover:bg-opacity-30 transition-all">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Navigation Items -->
        <div class="py-4">
          <!-- Sales Section -->
          <div class="px-6 py-2">
            <div class="flex items-center text-green-600 font-semibold mb-2">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              Sales
            </div>
            <div class="ml-8 space-y-2">
              <button class="flex items-center w-full text-left py-2 px-3 rounded hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Receipts
              </button>
              <button @click="showShiftView = true; showSidebar = false" class="flex items-center w-full text-left py-2 px-3 rounded hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Shift
              </button>
              <button class="flex items-center w-full text-left py-2 px-3 rounded hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Items
              </button>
              <button @click="showSettingsView = true; showSidebar = false" class="flex items-center w-full text-left py-2 px-3 rounded hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
              </button>
            </div>
          </div>

          <div class="border-t border-gray-200 my-4"></div>

          <!-- Other Sections -->
          <div class="px-6 space-y-2">
            <button class="flex items-center w-full text-left py-2 px-3 rounded hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Back office
            </button>
            <button class="flex items-center w-full text-left py-2 px-3 rounded hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
              Apps
            </button>
            <button class="flex items-center w-full text-left py-2 px-3 rounded hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Support
            </button>
          </div>
        </div>

        <!-- Version -->
        <div class="absolute bottom-4 left-6 text-xs text-gray-400">
          v. 2.25.2
        </div>
      </div>
    </div>

    <!-- Shift View -->
    <div v-if="showShiftView" class="flex-1 flex flex-col bg-white">
      <div class="flex items-center justify-between bg-green-600 px-6 py-3">
        <div class="flex items-center gap-4">
          <button @click="showShiftView = false" class="text-white font-semibold hover:text-red-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="text-white text-xl font-bold">Shift</div>
        </div>
        <button class="text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-6">
        <!-- Top Buttons -->
        <div class="flex gap-4 mb-6">
          <button class="px-6 py-3 border border-green-600 text-green-600 rounded-lg font-semibold hover:bg-green-50 transition-colors">
            CASH MANAGEMENT
          </button>
          <button @click="closeShift" class="px-6 py-3 border border-green-600 text-green-600 rounded-lg font-semibold hover:bg-green-50 transition-colors">
            CLOSE SHIFT
          </button>
        </div>

        <!-- Shift Information -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <div class="flex justify-between items-center">
            <div class="text-gray-700">
              <div class="font-semibold">Shift number: {{ props.shiftNumber || 0 }}</div>
              <div class="flex items-center gap-4">
                <span>Shift opened: {{ user?.name || 'Owner' }}</span>
                <span class="text-gray-500">{{ formatShiftDate() }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Cash Drawer Section -->
        <div class="mb-6">
          <h3 class="text-green-600 font-semibold text-lg mb-4">Cash drawer</h3>
          <div class="bg-white border rounded-lg p-4 space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-700">Starting cash:</span>
              <span class="font-semibold">KES {{ formatCurrency(props.cashDrawerData?.starting_cash || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Cash payments:</span>
              <span class="font-semibold">KES {{ formatCurrency(props.cashDrawerData?.cash_payments || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Cash refunds:</span>
              <span class="font-semibold">KES {{ formatCurrency(props.cashDrawerData?.cash_refunds || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Paid in:</span>
              <span class="font-semibold">KES {{ formatCurrency(props.cashDrawerData?.paid_in || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Paid out:</span>
              <span class="font-semibold">KES {{ formatCurrency(props.cashDrawerData?.paid_out || 0) }}</span>
            </div>
            <div class="flex justify-between border-t pt-3">
              <span class="text-gray-700 font-bold">Expected cash amount:</span>
              <span class="font-bold text-lg">KES {{ formatCurrency(props.cashDrawerData?.expected_cash_amount || 0) }}</span>
            </div>
          </div>
        </div>

        <!-- Sales Summary Section -->
        <div>
          <h3 class="text-green-600 font-semibold text-lg mb-4">Sales summary</h3>
          <div class="bg-white border rounded-lg p-4 space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-700">Gross sales:</span>
              <span class="font-semibold">KES {{ formatCurrency(0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Refunds:</span>
              <span class="font-semibold">KES {{ formatCurrency(0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Discounts:</span>
              <span class="font-semibold">KES {{ formatCurrency(0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Net sales:</span>
              <span class="font-semibold">KES {{ formatCurrency(0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Taxes:</span>
              <span class="font-semibold">KES {{ formatCurrency(0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-700">Total tendered:</span>
              <span class="font-semibold">KES {{ formatCurrency(0) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings View -->
    <div v-if="showSettingsView" class="flex-1 flex bg-gray-100">
      <!-- Left Panel: Settings Menu -->
      <div class="w-80 bg-gray-50 border-r border-gray-200 flex flex-col">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <button @click="showSettingsView = false" class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div class="text-xl font-semibold text-gray-800">Settings</div>
            </div>
            <div class="text-sm text-gray-500">{{ formatTime() }}</div>
          </div>
        </div>

        <!-- Settings Menu -->
        <div class="flex-1 py-4">
          <div class="px-4 space-y-1">
            <button
              @click="selectedSetting = 'printers'"
              :class="[
                'flex items-center w-full px-4 py-3 rounded-lg transition-colors',
                selectedSetting === 'printers'
                  ? 'bg-green-100 text-green-700 border border-green-200'
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Printers
            </button>
            <button
              @click="selectedSetting = 'displays'"
              :class="[
                'flex items-center w-full px-4 py-3 rounded-lg transition-colors',
                selectedSetting === 'displays'
                  ? 'bg-green-100 text-green-700 border border-green-200'
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Customer displays
            </button>
            <button
              @click="selectedSetting = 'general'"
              :class="[
                'flex items-center w-full px-4 py-3 rounded-lg transition-colors',
                selectedSetting === 'general'
                  ? 'bg-green-100 text-green-700 border border-green-200'
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              General
            </button>
          </div>
        </div>

        <!-- User Info and Logout -->
        <div class="px-4 py-4 border-t border-gray-200">
          <div class="text-sm text-gray-600 mb-3">{{ user?.email || 'user@example.com' }}</div>
          <button
            @click="clockOut"
            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors"
          >
            SIGN OUT
          </button>
        </div>
      </div>

      <!-- Right Panel: Settings Content -->
      <div class="flex-1 bg-white">
        <!-- Content Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-800">{{ getSettingTitle() }}</h2>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-6">
          <div v-if="selectedSetting === 'printers'" class="flex flex-col items-center justify-center h-full">
            <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">You have no printers yet</h3>
            <p class="text-gray-600 mb-6">To add the printer, press the (+) button</p>
            <button class="w-12 h-12 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </button>
          </div>

          <div v-else-if="selectedSetting === 'displays'" class="flex flex-col items-center justify-center h-full">
            <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Customer Displays</h3>
            <p class="text-gray-600">Configure customer-facing displays</p>
          </div>

          <div v-else-if="selectedSetting === 'general'" class="flex flex-col items-center justify-center h-full">
            <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">General Settings</h3>
            <p class="text-gray-600">Configure general system settings</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Left: Items grid -->
    <div class="flex-1 flex flex-col bg-white" style="min-width:0;">
      <div class="flex items-center justify-between bg-green-600 px-6 py-3">
        <div class="flex items-center gap-4">
          <button @click="showSidebar = true" class="text-white font-semibold hover:text-red-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="text-white text-xl font-bold">POS</div>
        </div>
        <div class="flex items-center gap-2">
          <button class="text-white text-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></button>
          <button class="text-white text-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></button>
        </div>
      </div>
      <div class="flex-1 overflow-y-auto p-4">
        <div v-if="!clockedIn" class="shift-closed-container">
          <div class="shift-closed-icon">
            <svg class="w-20 h-20 text-gray-400 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          </div>
          <div class="text-xl text-gray-700 mb-2">Please clock in to continue</div>
        </div>
        <div v-else-if="!shiftIsOpen" class="shift-closed-container">
          <div class="shift-closed-icon">
            <svg class="w-20 h-20 text-gray-400 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          </div>
          <div class="text-xl text-gray-700 mb-2">Shift is closed</div>
          <div class="text-gray-500 mb-4">Open shift to continue</div>
          <button class="open-shift-btn bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded text-lg" @click="openShift">OPEN SHIFT</button>
        </div>
        <div v-else class="pos-items-container">
          <div
            v-for="stock in filteredItems"
            :key="stock.id"
            :class="[
              'pos-item-card',
              { 'layered-card': stock.item.has_variants },
              { 'composite-item': stock.item.is_composite }
            ]"
            @click="addToCart(stock)"
          >
            <div class="pos-item-image-wrapper">
              <img :src="stock.variant && stock.variant.image_path ? `/storage/${stock.variant.image_path}` : (stock.item.image_path ? `/storage/${stock.item.image_path}` : '/images/default_item.jpg')" alt="Item Image" />
              <div class="item-name-overlay">
                {{ stock.variant ? `${stock.item.name} - ${Object.values(stock.variant.options).join(' / ')}` : stock.item.name }}
                <!-- Show composite indicator -->
                <span v-if="stock.item.is_composite" class="composite-badge">Composite</span>
              </div>
              <!-- Variant indicator for items with variants -->
              <div v-if="stock.item.has_variants" class="variant-indicator">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              <!-- Composite indicator -->
              <div v-if="stock.item.is_composite" class="composite-indicator">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
            </div>
          </div>
        </div>
        <div v-if="showCashModal" class="cash-modal-overlay">
          <div class="cash-modal">
            <div class="text-2xl font-bold text-gray-700 mb-2">Cash in drawer</div>
            <div class="text-gray-500 mb-4">Enter the amount of cash in the drawer at the start of the shift.</div>
            <input v-model="cashInDrawer" type="number" min="0" step="0.01" class="cash-input" placeholder="KES" />
            <div v-if="cashError" class="text-red-500 text-sm mt-1">{{ cashError }}</div>
            <button class="start-shift-btn bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded text-lg mt-4" @click="startShift">START SHIFT</button>
          </div>
        </div>
        <div v-if="showClockOutModal" class="cash-modal-overlay">
          <div class="cash-modal">
            <div class="text-2xl font-bold text-gray-700 mb-2">Close Shift</div>
            <div class="text-gray-500 mb-4">Enter the amount of cash in the drawer at the end of the shift.</div>
            <div class="mb-2">Expected cash: <span class="font-bold">KES {{ expectedCash }}</span></div>
            <input v-model="realCash" type="number" min="0" step="0.01" class="cash-input" placeholder="KES" />
            <div v-if="closingError" class="text-red-500 text-sm mt-1">{{ closingError }}</div>
            <input v-model="closingNote" class="cash-input mt-2" placeholder="Closing note (optional)" />
            <input v-model="closingReason" class="cash-input mt-2" placeholder="Reason for difference (if any)" />
            <button class="start-shift-btn bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded text-lg mt-4" @click="confirmCloseShift">CONFIRM CLOSE</button>
            <button class="start-shift-btn bg-gray-400 hover:bg-gray-500 text-white font-semibold px-8 py-3 rounded text-lg mt-2" @click="showClockOutModal = false">CANCEL</button>
          </div>
        </div>
      </div>
      <div v-if="shiftIsOpen" class="flex items-center justify-between bg-gray-100 px-4 py-2 border-t">
        <div class="flex gap-2">
          <button
            v-for="cat in categories"
            :key="cat"
            :class="['px-3 py-1 rounded border text-xs font-semibold', selectedCategory === cat ? 'bg-green-500 text-white' : 'bg-white text-gray-700 hover:bg-green-100']"
            @click="selectedCategory = cat"
          >
            {{ cat }}
          </button>
          <button v-if="selectedCategory" class="ml-2 px-2 py-1 text-xs text-gray-500 underline" @click="selectedCategory = ''">All</button>
        </div>

      </div>
    </div>
    <!-- Right: Cart -->
    <div v-if="shiftIsOpen" class="w-1/3 max-w-md bg-white border-l flex flex-col h-full">
      <div class="flex items-center justify-between px-6 py-3 border-b">
        <div class="flex items-center gap-2">
          <div class="text-lg font-semibold">Ticket</div>
          <div v-if="currentTicketId" class="flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Active
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click="showCustomerModal = true" class="text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </button>
          <div class="relative inline-block">
            <button @click="toggleTopMenu" class="text-gray-600 hover:text-gray-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
      <div class="flex-1 overflow-y-auto px-6 py-4">
        <template v-if="cart.length > 0">
          <div class="text-sm text-gray-700 mb-2">Take out</div>
          <div v-for="item in cart" :key="item.id" class="mb-2">
            <div class="flex justify-between items-center">
              <div>
                <span class="font-semibold">{{ item.name }}</span>
                <span class="text-xs text-gray-500"> x {{ item.qty }}</span>
              </div>
              <div class="flex items-center gap-1">
                <button class="text-gray-400 px-1" @click="decrementQty(item)">-</button>
                <div class="text-gray-700 font-semibold">KES {{ ((Number(item.price) || 0) * (Number(item.qty) || 0)).toFixed(2) }}</div>
                <button class="text-gray-400 px-1" @click="incrementQty(item)">+</button>
                <button class="text-red-400 ml-2" @click="removeFromCart(item)">&times;</button>
              </div>
            </div>
            <div v-if="item.note" class="text-xs text-gray-400 ml-2">{{ item.note }}</div>
          </div>
          <div class="flex justify-between text-sm text-gray-600 mt-4">
            <span>Discounts</span>
            <span>KES {{ discounts.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-sm text-gray-600">
            <span>Tax</span>
            <span>KES {{ tax.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-base font-bold mt-2">
            <span>Total</span>
            <span>KES {{ total.toFixed(2) }}</span>
          </div>
        </template>
      </div>
      <div class="flex gap-2 px-6 py-4 border-t bg-gray-50">
        <button v-if="currentTicketId" @click="clearCurrentTicket" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded">NEW TRANSACTION</button>
        <button @click="charge" class="flex-1 bg-green-700 hover:bg-green-800 text-white font-semibold py-2 rounded">CHARGE</button>
      </div>
    </div>
  </div>

  <!-- Active Tickets Modal -->
  <div v-if="showTopMenu" class="fixed inset-0 bg-white/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 max-h-[80vh] overflow-hidden shadow-2xl border border-gray-200">
      <!-- Modal Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Active Tickets</h3>
        <button @click="showTopMenu = false" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Tickets List -->
      <div class="px-6 py-4">
        <div v-if="activeTickets.length === 0" class="text-center py-8">
          <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-gray-500">No active tickets found</p>
        </div>
        <div v-else class="space-y-2 max-h-64 overflow-y-auto">
          <button
            v-for="ticket in activeTickets"
            :key="ticket.id"
            @click="recallTicket(ticket.id)"
            class="w-full text-left p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="font-semibold text-gray-900">Ticket #{{ ticket.id }}</div>
                <div class="text-sm text-gray-600 mt-1">
                  <span class="font-medium">KES {{ parseFloat(ticket.total_amount).toFixed(2) }}</span>
                  <span class="mx-2">•</span>
                  <span class="capitalize">{{ ticket.status }}</span>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  Due: KES {{ parseFloat(ticket.amount_due).toFixed(2) }}
                </div>
              </div>
              <svg class="w-5 h-5 text-gray-400 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Customer Modal -->
  <div v-if="showCustomerModal" class="fixed inset-0 bg-transparent backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 max-h-[80vh] overflow-hidden shadow-2xl border border-gray-200">
      <!-- Modal Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Add customer to ticket</h3>
        <button @click="showCustomerModal = false" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Search Bar -->
      <div class="px-6 py-4">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <input
            v-model="customerSearch"
            type="text"
            placeholder="Search"
            class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500"
          />
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- ADD NEW CUSTOMER Button -->
      <div class="px-6 pb-4">
        <button @click="showNewCustomerModal = true" class="w-full text-green-600 font-semibold py-2 px-4 rounded border border-green-600 hover:bg-green-50 transition-colors">
          ADD NEW CUSTOMER
        </button>
      </div>

      <!-- Recent customers -->
      <div class="px-6 pb-4">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Recent customers</h4>
        <div class="max-h-64 overflow-y-auto space-y-2">
          <div
            v-for="customer in filteredCustomers"
            :key="customer.id"
            @click="selectCustomer(customer)"
            class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-gray-900 truncate">{{ customer.name }}</div>
              <div class="text-xs text-gray-500 truncate">
                {{ customer.email || customer.phone }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- New Customer Modal -->
  <div v-if="showNewCustomerModal" class="fixed inset-0 bg-transparent backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 max-h-[80vh] overflow-hidden shadow-2xl border border-gray-200">
      <!-- Modal Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Add New Customer</h3>
        <button @click="showNewCustomerModal = false" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Customer Form -->
      <div class="px-6 py-4">
        <form @submit.prevent="saveNewCustomer" class="space-y-4">
          <!-- Name Field -->
          <div>
            <label for="customer-name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input
              id="customer-name"
              v-model="newCustomer.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
              placeholder="Enter customer name"
            />
          </div>

          <!-- Email Field -->
          <div>
            <label for="customer-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              id="customer-email"
              v-model="newCustomer.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
              placeholder="Enter email address"
            />
          </div>

          <!-- Phone Field -->
          <div>
            <label for="customer-phone" class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
            <input
              id="customer-phone"
              v-model="newCustomer.phone"
              type="tel"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
              placeholder="Enter phone number"
            />
          </div>

          <!-- Address Field -->
          <div>
            <label for="customer-address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea
              id="customer-address"
              v-model="newCustomer.address"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
              placeholder="Enter address"
            ></textarea>
          </div>

          <!-- Form Buttons -->
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeNewCustomerModal"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
              :disabled="isSavingCustomer"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
              :disabled="isSavingCustomer"
            >
              {{ isSavingCustomer ? 'Saving...' : 'Save Customer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Payment View -->
  <div v-if="showPaymentView" class="fixed inset-0 bg-gray-100 z-50">
    <!-- Main Payment Interface -->
    <div class="flex h-full">
      <!-- Left Panel: Ticket Summary -->
      <div class="w-1/3 bg-white border-r border-gray-200">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Ticket</h2>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <button @click="toggleTopMenu" class="p-1 hover:bg-gray-100 rounded">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Cart Items -->
          <div class="space-y-3 mb-4">
            <div v-for="item in cart" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-100">
              <div class="flex-1">
                <div class="font-medium">{{ item.name }}</div>
                <div class="text-sm text-gray-500">x{{ item.qty }}</div>
              </div>
              <div class="text-right">
                <div class="font-medium">KES {{ (item.price * item.qty).toFixed(2) }}</div>
                <div class="text-sm text-gray-500">KES {{ item.price.toFixed(2) }} each</div>
              </div>
            </div>
          </div>
          
          <!-- Summary -->
          <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between">
              <span class="text-gray-600">Discounts</span>
              <span class="font-medium">KES {{ discount.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tax (included)</span>
              <span class="font-medium">KES {{ tax.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold">
              <span>Total</span>
              <span>KES {{ total.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel: Payment Interface -->
      <div class="w-2/3 bg-gray-50 p-6">
        <PaymentPanel 
          :total="total" 
          :mode="'new'"
          :ticket-id="currentTicketId"
          @complete="handlePaymentComplete"
          @partial-update="handlePartialUpdate"
          @back="backToPOS"
        />
      </div>
    </div>
  </div>
  
  <!-- Split payment view is now handled by PaymentPanel component -->
</template>
<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import PaymentPanel from '@/components/POS/PaymentPanel.vue';

const page = usePage();
const props = defineProps({ 
    stockItems: Array, 
    categories: Array,
    customers: Array,
    shiftIsOpen: Boolean,
    shiftId: Number,
    openShift: Object,
    shiftNumber: Number,
    cashDrawerData: Object,
});

// Auto-hide flash messages after 5 seconds
watch(() => page.props.flash, (flash) => {
    if (flash?.success || flash?.error) {
        setTimeout(() => {
            // Clear flash messages by navigating to the same page without flash
            router.visit(window.location.pathname, { 
                preserveState: true,
                preserveScroll: true,
                replace: true 
            });
        }, 5000);
    }
}, { immediate: true });

// Computed properties for flash messages
const flashSuccess = computed(() => page.props.flash?.success || '');
const flashError = computed(() => page.props.flash?.error || '');

const selectedCategory = ref('');
const cart = ref([]);
const clockedIn = ref(true); // Always true, since login handles clock in
const shiftIsOpen = ref(props.shiftIsOpen || false);
const shiftId = ref(props.shiftId || null);
const shiftClosing = ref(false);
const showCashModal = ref(false);
const cashInDrawer = ref('');
const cashError = ref('');
const showCustomerModal = ref(false);
const customerSearch = ref('');
const selectedCustomer = ref(null);
const showNewCustomerModal = ref(false);
const newCustomer = ref({
  name: '',
  email: '',
  phone: '',
  address: ''
});
const isSavingCustomer = ref(false);
const showClockOutModal = ref(false);
const closingCash = ref('');
const closingError = ref('');
const expectedCash = ref(0);
const realCash = ref('');
const closingNote = ref('');
const closingReason = ref('');
// Toaster notifications
const toaster = ref({
  show: false,
  message: '',
  type: 'success', // 'success' or 'error'
  timeout: null
});
// Removed showClockInModal, pin, pinError, and clockIn logic
const branchId = ref(1); // Replace with actual branch selection if needed
const showSidebar = ref(false);
const user = ref(props.auth?.user || null);
const business = ref(null);
const showShiftView = ref(false);
const showSettingsView = ref(false);
const selectedSetting = ref('printers');
const showPaymentView = ref(false);
const currentTicketId = ref(null);

// Add missing computed properties and functions
const filteredItems = computed(() => {
  if (!props.stockItems) return [];
    let items = props.stockItems;

    if (selectedCategory.value) {
      items = items.filter(item => {
    return item.item.category && item.item.category.name === selectedCategory.value;
  });
    }



    // Return all items as individual cards - no grouping needed
    // This includes: ordinary items, composite items, variants of both
    return items;
  });

  const filteredCustomers = computed(() => {
    if (!props.customers) return [];

    if (!customerSearch.value) {
      return props.customers;
    }

    const searchTerm = customerSearch.value.toLowerCase();
    return props.customers.filter(customer =>
      customer.name.toLowerCase().includes(searchTerm) ||
      (customer.email && customer.email.toLowerCase().includes(searchTerm)) ||
      (customer.phone && customer.phone.toLowerCase().includes(searchTerm))
    );
  });

  function selectCustomer(customer) {
    selectedCustomer.value = customer;
    showCustomerModal.value = false;
    // You can add logic here to associate the customer with the current transaction
  }

const discounts = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.discount || 0), 0);
});

const tax = computed(() => {
  const subtotal = cart.value.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);
  return subtotal * 0.16; // 16% tax rate
});

const total = computed(() => {
  const subtotal = cart.value.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);
  const totalValue = subtotal + tax.value - discount.value;
  
  return totalValue;
});

async function addToCart(stock) {
  // Determine if this is a variant or a simple item
  const isVariant = !!stock.variant;
  const item_id = isVariant ? stock.item.id : stock.id;
  const variant_id = isVariant ? stock.id : null;

  // Check if already in cart (by item_id + variant_id)
  const existingItem = cart.value.find(
    item => item.item_id === item_id && item.variant_id === variant_id
  );
  if (existingItem) {
    existingItem.qty += 1;
  } else {
    const price = Number(stock.price) || 0;
    cart.value.push({
      item_id,
      variant_id,
      stock_item_id: stock.id,
      name: isVariant
        ? `${stock.item.name} - ${Object.values(stock.variant.options).join(' / ')}`
        : stock.item.name,
      price: price,
      qty: 1,
      note: '',
      isComposite: stock.item?.is_composite || false
    });
  }

  // If there's an active ticket, update it with the new cart
  if (currentTicketId.value) {
    await updateExistingTicket();
  }
}

async function updateExistingTicket() {
  if (!currentTicketId.value) return;

  try {
    // Build items array for API
    const items = cart.value.map(item => ({
      item_id: item.item_id,
      variant_id: item.variant_id,
      stock_item_id: item.stock_item_id,
      qty: item.qty,
      price: item.price,
      subtotal: (item.price * item.qty)
    }));

    // Get current ticket to preserve existing payments
    const ticketResponse = await axios.get(`/pos/ticket/${currentTicketId.value}`);
    const existingPayments = ticketResponse.data.payments || [];

    // Calculate new totals
    const newTotal = total.value;
    const paidAmount = existingPayments.filter(p => p.status === 'completed').reduce((sum, p) => sum + parseFloat(p.amount), 0);
    const newAmountDue = newTotal - paidAmount;

    // Update ticket with new items and totals
    const payload = {
      items,
      total_amount: newTotal,
      amount_paid: paidAmount,
      amount_due: newAmountDue,
      status: newAmountDue <= 0 ? 'completed' : 'active'
    };

    await axios.post(`/pos/ticket/${currentTicketId.value}/update`, payload);
    
    showToaster('Ticket updated with new items', 'success');
  } catch (error) {
    console.error('Error updating ticket:', error);
    showToaster('Failed to update ticket', 'error');
  }
}

async function incrementQty(item) {
  item.qty += 1;
  
  // If there's an active ticket, update it
  if (currentTicketId.value) {
    await updateExistingTicket();
  }
}

async function decrementQty(item) {
  if (item.qty > 1) {
    item.qty -= 1;
    
    // If there's an active ticket, update it
    if (currentTicketId.value) {
      await updateExistingTicket();
    }
  }
}

async function removeFromCart(item) {
  const index = cart.value.findIndex(cartItem => cartItem.id === item.id);
  if (index > -1) {
    cart.value.splice(index, 1);
    
    // If there's an active ticket, update it
    if (currentTicketId.value) {
      await updateExistingTicket();
    }
  }
}

function openShift() {
  showCashModal.value = true;
  cashInDrawer.value = '';
  cashError.value = '';
}
async function startShift() {
  if (!/^[0-9]+(\.[0-9]{1,2})?$/.test(cashInDrawer.value) || Number(cashInDrawer.value) < 0) {
    cashError.value = 'Enter a valid amount in KES.';
    return;
  }
  
  try {
    const device_uuid = localStorage.getItem('device_uuid');
    const { data } = await axios.post('/shifts/open', { 
      opening_balance: cashInDrawer.value,
      device_uuid: device_uuid
    });
    
    shiftId.value = data.shift_id;
    showCashModal.value = false;
    shiftIsOpen.value = true;
    cashError.value = '';
  } catch (error) {
    cashError.value = error.response?.data?.error || 'Failed to open shift.';
  }
}
function closeShift() {
  showClockOutModal.value = true;
  closingCash.value = '';
  closingError.value = '';
  realCash.value = '';
  closingNote.value = '';
  closingReason.value = '';
  // Calculate expected cash (pseudo, replace with real logic)
  expectedCash.value = Number(cashInDrawer.value) + cart.value.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);
}
async function confirmCloseShift() {
  if (!/^[0-9]+(\.[0-9]{1,2})?$/.test(realCash.value) || Number(realCash.value) < 0) {
    closingError.value = 'Enter a valid amount in KES.';
    return;
  }
  closingCash.value = realCash.value;
  await axios.post('/shifts/close', {
    shift_id: shiftId.value,
    closing_balance: closingCash.value,
    real_close_cash: realCash.value,
    expected_close_cash: expectedCash.value,
    closing_note: closingNote.value,
    closing_reason: closingReason.value
  });
  shiftIsOpen.value = false;
  showClockOutModal.value = false;
  cart.value = [];
}
async function clockOut() {
  await axios.post('/pos/logout');
  window.location.href = '/pos';
}

function logout() {
  router.post('/logout');
  window.location.href = '/pos';
  
}

// Helper functions for shift view
function formatCurrency(amount) {
  return Number(amount).toFixed(2).replace('.', ',');
}

function formatShiftDate() {
  const now = new Date();
  const month = (now.getMonth() + 1).toString().padStart(2, '0');
  const day = now.getDate().toString().padStart(2, '0');
  const year = now.getFullYear().toString().slice(-2);
  const hours = now.getHours();
  const minutes = now.getMinutes().toString().padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  const displayHours = hours % 12 || 12;
  
  return `${month}/${day}/${year} ${displayHours}:${minutes} ${ampm}`;
}

// Helper functions for settings view
function formatTime() {
  const now = new Date();
  const hours = now.getHours().toString().padStart(2, '0');
  const minutes = now.getMinutes().toString().padStart(2, '0');
  return `${hours}:${minutes}`;
}

function getSettingTitle() {
  switch (selectedSetting.value) {
    case 'printers':
      return 'Printers';
    case 'displays':
      return 'Customer displays';
    case 'general':
      return 'General';
    default:
      return 'Settings';
  }
}

// Toaster functions
function showToaster(message, type = 'success') {
  // Clear existing timeout
  if (toaster.value.timeout) {
    clearTimeout(toaster.value.timeout);
  }
  
  toaster.value.show = true;
  toaster.value.message = message;
  toaster.value.type = type;
  
  // Auto-hide after 5 seconds
  toaster.value.timeout = setTimeout(() => {
    toaster.value.show = false;
  }, 5000);
}

async function saveNewCustomer() {
  isSavingCustomer.value = true;
  try {
    const response = await axios.post('/customers', newCustomer.value);
    
    if (response.data.success) {
      showNewCustomerModal.value = false;
      closeNewCustomerModal();
      showToaster('Customer created successfully!', 'success');
      // Refresh the page to get the updated customers list
      window.location.reload();
    }
  } catch (error) {
    console.error('Error saving new customer:', error);
    if (error.response?.data?.message) {
      showToaster('Error: ' + error.response.data.message, 'error');
    } else {
      showToaster('Error creating customer. Please try again.', 'error');
    }
  } finally {
    isSavingCustomer.value = false;
  }
}

function closeNewCustomerModal() {
  showNewCustomerModal.value = false;
  newCustomer.value = {
    name: '',
    email: '',
    phone: '',
    address: ''
  };
}

async function charge() {
  if (cart.value.length === 0) {
    showToaster('Please add items to cart first', 'error');
    return;
  }
  
  // If there's already an active ticket, update it instead of creating new one
  if (currentTicketId.value) {
    try {
      // Update existing ticket with current cart items
      await updateExistingTicket();
      
      // Open payment view - PaymentPanel will load updated ticket data from DB
      showPaymentView.value = true;
    } catch (err) {
      showToaster('Error updating ticket: ' + (err.response?.data?.message || err.message), 'error');
    }
    return;
  }
  
  // Build items array for API
  const items = cart.value.map(item => ({
    item_id: item.item_id,
    variant_id: item.variant_id,
    stock_item_id: item.stock_item_id,
    qty: item.qty,
    price: item.price,
    subtotal: (item.price * item.qty)
  }));

  // Prepare initial payload with default cash payment
  const payload = {
    items,
    payments: [{
      method: 'cash',
      amount: 0,
      status: 'pending',
      meta: {}
    }],
    total_amount: total.value,
    amount_paid: 0,
    amount_due: total.value,
    status: 'pending'
  };

  try {
    // Save ticket to backend first
    const response = await axios.post('/pos/ticket/store', payload);
    
    if (response.data.success) {
      // Store the ticket ID for PaymentPanel
      currentTicketId.value = response.data.ticket_id;
      
      // Open payment view - PaymentPanel will load ticket data from DB
      showPaymentView.value = true;
    } else {
      showToaster('Failed to create ticket', 'error');
    }
  } catch (err) {
    showToaster('Error creating ticket: ' + (err.response?.data?.message || err.message), 'error');
  }
}

function backToPOS() {
  showPaymentView.value = false;
  // Keep the currentTicketId so user can continue adding items to the same ticket
  // Load existing ticket items into cart if not already loaded
  if (currentTicketId.value && cart.value.length === 0) {
    loadTicketItemsToCart();
  }
}

async function loadTicketItemsToCart() {
  if (!currentTicketId.value) return;
  
  try {
    const response = await axios.get(`/pos/ticket/${currentTicketId.value}`);
    const ticket = response.data;
    
    // Clear current cart and load ticket items
    cart.value = ticket.items.map(item => ({
      item_id: item.item_id,
      variant_id: item.variant_id,
      stock_item_id: item.stock_item_id,
      name: item.name,
      price: parseFloat(item.price),
      qty: item.qty,
      note: '',
      isComposite: false // You might need to determine this from the item data
    }));
    
    showToaster('Ticket items loaded', 'success');
  } catch (error) {
    console.error('Error loading ticket items:', error);
    showToaster('Failed to load ticket items', 'error');
  }
}

function clearCurrentTicket() {
  currentTicketId.value = null;
  cart.value = [];
  showToaster('New transaction started', 'success');
}

// Payment logic is now handled by PaymentPanel component

// Cart calculations
const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

const discount = computed(() => {
  // For now, return 0. You can implement discount logic later
  return 0;
});

// Payment logic is now handled by PaymentPanel component

const showRecallModal = ref(false);
const activeTickets = ref([]);

async function fetchActiveTickets() {
  try {
    const { data } = await axios.get('/pos/tickets/active');
    activeTickets.value = data;
  } catch (error) {
    console.error('Error fetching active tickets:', error);
    showToaster('Failed to load active tickets', 'error');
  }
}
const loadingTickets = ref(false);

async function openRecallModal() {
  showRecallModal.value = true;
  loadingTickets.value = true;
  try {
    const { data } = await axios.get('/pos/tickets/active');
    activeTickets.value = data;
  } finally {
    loadingTickets.value = false;
  }
}
async function recallTicket(id) {
  showRecallModal.value = false;
  
  // Set the current ticket ID
  currentTicketId.value = id;
  
  // Load ticket items into cart
  await loadTicketItemsToCart();
  
  // Open payment view
  showPaymentView.value = true;
}

const showTopMenu = ref(false);
const menuRef = ref(null);

function toggleTopMenu() {
  showTopMenu.value = !showTopMenu.value;
  if (showTopMenu.value) {
    fetchActiveTickets();
  }
}
function closeTopMenu() {
  showTopMenu.value = false;
}
function openRecallModalFromTop() {
  closeTopMenu();
  openRecallModal();
}
// Click outside to close modal
onMounted(() => {
  document.addEventListener('click', onClickOutsideTopMenu);
  // Heartbeat to keep session alive every 10 seconds using existing endpoint
  setInterval(() => {
    fetch('/api/check-device', { 
      credentials: 'same-origin',
      method: 'GET'
    }).catch(error => {
      // Silently handle any network errors without logging to console
      console.debug('Heartbeat failed:', error);
    });
  }, 10000);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutsideTopMenu);
});
function onClickOutsideTopMenu(e) {
  if (showTopMenu.value && e.target.classList.contains('bg-transparent')) {
    showTopMenu.value = false;
  }
}

function handlePartialUpdate(paymentData) {
  // PaymentPanel handles all payment responses - just update the ticket
  console.log('Partial payment update received from PaymentPanel');
}

async function handlePaymentComplete(paymentData) {
  console.log('Payment complete received from PaymentPanel', paymentData);
    if (paymentData.cancelled) {
      showToaster('Order cancelled.', 'success');
      showPaymentView.value = false;
      currentTicketId.value = null;
      cart.value = [];
      return;
    }

  
  try {
    const response = await axios.post(`/pos/ticket/${currentTicketId.value}/convert-to-sale`, {
      customer_id: selectedCustomer.value?.id || null
    });
    
    if (response.data.success) {
      console.log('Sale created successfully:', response.data.sale);
      showToaster('Sale completed successfully!', 'success');

      showPaymentView.value = false;
      currentTicketId.value = null;
      cart.value = [];
      selectedCustomer.value = null;
    } else {
      throw new Error(response.data.error || 'Failed to create sale');
    }
  } catch (error) {
    console.error('Error creating sale:', error);
    
    // Show error message
    showToaster(error.response?.data?.error || 'Failed to create sale. Please try again.', 'error');
  }
}
</script>
<style scoped>
body, html, #app { height: 100%; margin: 0; padding: 0; }
.pos-items-container {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  justify-content: flex-start;
}
.pos-item-card {
  width: 180px;
  height: 180px;
  background: #f8f9fa;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: box-shadow 0.2s, background 0.2s;
  margin-bottom: 16px;
  cursor: pointer;
  padding: 0;
  overflow: hidden;
}
.pos-item-card:hover {
  background: #d1fae5;
}

/* Layered card effect for items with variants */
.layered-card {
  position: relative;
  box-shadow:
    0 2px 8px rgba(0,0,0,0.04),
    0 4px 16px rgba(0,0,0,0.08),
    0 8px 24px rgba(0,0,0,0.12);
  transform: translateY(-2px);
}

.layered-card::before {
  content: '';
  position: absolute;
  top: -4px;
  left: -4px;
  right: -4px;
  bottom: -4px;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6, #06b6d4);
  border-radius: 20px;
  z-index: -1;
  opacity: 0.3;
}

.layered-card::after {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, #10b981, #059669, #047857);
  border-radius: 18px;
  z-index: -1;
  opacity: 0.2;
}

.layered-card:hover {
  transform: translateY(-4px);
  box-shadow:
    0 4px 16px rgba(0,0,0,0.08),
    0 8px 24px rgba(0,0,0,0.12),
    0 16px 32px rgba(0,0,0,0.16);
}

.layered-card:hover::before {
  opacity: 0.5;
}

.layered-card:hover::after {
  opacity: 0.4;
}

/* Variant indicator */
.variant-indicator {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(59, 130, 246, 0.9);
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.pos-item-image-wrapper {
  width: 100%;
  height: 100%;
  position: relative;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}
.pos-item-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.item-name-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  background: rgba(0,0,0,0.45);
  color: #fff;
  font-size: 1rem;
  font-weight: 500;
  text-align: center;
  padding: 8px 0 6px 0;
  border-bottom-left-radius: 16px;
  border-bottom-right-radius: 16px;
  pointer-events: none;
}
.shift-closed-container {
  min-height: 60vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.shift-closed-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}
.open-shift-btn {
  margin-top: 24px;
  min-width: 200px;
}
.cash-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}
.cash-modal {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 32px rgba(0,0,0,0.12);
  padding: 40px 32px 32px 32px;
  min-width: 340px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.cash-input {
  width: 180px;
  font-size: 1.5rem;
  padding: 10px 16px;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  margin-bottom: 8px;
  text-align: center;
}
.start-shift-btn {
  margin-top: 12px;
  min-width: 180px;
}
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}
.modal {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 32px rgba(0,0,0,0.12);
  padding: 40px 32px 32px 32px;
  min-width: 340px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Composite item styles */
.composite-item {
  border: 2px solid #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.composite-item:hover {
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
  transform: translateY(-2px);
}

.composite-badge {
  display: inline-block;
  background: #10b981;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.composite-indicator {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(16, 185, 129, 0.9);
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}
</style> 