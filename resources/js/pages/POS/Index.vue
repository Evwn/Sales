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
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSidebar = false"></div>
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
            @click="logout"
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
        <div class="text-lg font-semibold">Ticket</div>
        <div class="flex items-center gap-2">
          <button @click="showCustomerModal = true" class="text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </button>
          <button class="text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
            </svg>
          </button>
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
        <button @click="charge" class="flex-1 bg-green-700 hover:bg-green-800 text-white font-semibold py-2 rounded">CHARGE</button>
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
    <!-- Header -->
    <div class="bg-green-600 text-white px-6 py-4 flex items-center justify-between">
      <button @click="backToPOS" class="text-white hover:text-green-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div class="text-xl font-semibold">Payment</div>
      <button @click="initializeSplitPayment" class="text-white font-semibold">SPLIT</button>
    </div>

    <!-- Main Payment Interface -->
    <div class="flex h-full">
      <!-- Left Panel: Ticket Summary -->
      <div class="w-1/3 bg-white border-r border-gray-200">
        <div class="p-6">
          <!-- Ticket Header -->
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Ticket</h2>
            <div class="flex items-center space-x-2">
              <button class="p-2 text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </button>
              <button class="p-2 text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="space-y-3 mb-4">
            <div v-for="item in cart" :key="item.id" class="flex justify-between items-center">
              <div class="flex-1">
                <div class="font-medium">{{ item.name }}</div>
                <div v-if="item.note" class="text-sm text-gray-500">{{ item.note }}</div>
              </div>
              <div class="text-right">
                <div class="font-medium">KES {{ (item.price * item.qty).toFixed(2) }}</div>
                <div class="text-sm text-gray-500">x{{ item.qty }}</div>
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
      <div class="w-2/3 bg-gray-50 p-6 overflow-y-auto">
        <!-- Total Amount Due -->
        <div class="text-center mb-8">
          <div class="text-4xl font-bold text-gray-900">{{ remainingAmount.toFixed(2) }}</div>
          <div class="text-gray-600">Amount due</div>
        </div>

        <!-- Payment Method Input Section -->
        <div class="mb-6">
          <!-- Cash Payment -->
          <div v-if="selectedPaymentMethod === 'cash'">
            <div class="flex items-center mb-2">
              <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <span class="text-green-600 font-medium">Cash received</span>
            </div>
            
            <div class="flex items-center mb-4">
              <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <input 
                  v-model="cashReceived" 
                  type="number" 
                  step="0.01"
                  class="w-full pl-10 pr-4 py-3 text-xl font-semibold border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="0.00"
                />
              </div>
              <button @click="processPayment" class="ml-4 bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded-lg">
                CHARGE
              </button>
            </div>

            <!-- Change Display -->
            <div v-if="changeAmount > 0" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-blue-800 font-medium">Change to give:</span>
                <span class="text-blue-800 font-bold text-lg">KES {{ changeAmount.toFixed(2) }}</span>
              </div>
            </div>
            
            <!-- Quick Amount Buttons -->
            <div class="grid grid-cols-4 gap-2 mb-6">
              <button 
                v-for="amount in quickAmounts" 
                :key="amount"
                @click="cashReceived = amount.toString()"
                class="py-2 px-3 bg-gray-200 hover:bg-gray-300 rounded text-sm font-medium transition-colors"
              >
                {{ amount }}.00
              </button>
            </div>
          </div>

          <!-- Card Payment -->
          <div v-if="selectedPaymentMethod === 'card'">
            <div class="flex items-center mb-2">
              <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" />
              </svg>
              <span class="text-blue-600 font-medium">Card Payment</span>
            </div>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                <input 
                  v-model="cardNumber" 
                  type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="1234 5678 9012 3456"
                />
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                  <input 
                    v-model="cardExpiry" 
                    type="text" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="MM/YY"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                  <input 
                    v-model="cardCvv" 
                    type="text" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="123"
                  />
                </div>
              </div>
              
              <button @click="processPayment" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg">
                CHARGE
              </button>
            </div>
          </div>

          <!-- MPESA Payment -->
          <div v-if="selectedPaymentMethod === 'mpesa'">
            <div class="flex items-center mb-2">
              <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
              </svg>
              <span class="text-green-600 font-medium">MPESA Payment</span>
            </div>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input 
                  v-model="mpesaPhone" 
                  type="tel" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="07XX XXX XXX"
                />
              </div>
              
              <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-yellow-800 text-sm">Enter the phone number to receive the payment prompt</span>
                </div>
              </div>
              
              <!-- Manual MPESA Input Option -->
              <div class="border-t pt-4">
                <div class="flex items-center mb-2">
                  <input 
                    v-model="mpesaManualMode" 
                    type="checkbox" 
                    id="mpesaManual" 
                    class="mr-2"
                  />
                  <label for="mpesaManual" class="text-sm font-medium text-gray-700">Manual MPESA Entry</label>
                </div>
                
                <div v-if="mpesaManualMode" class="space-y-3 mt-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Code</label>
                    <input 
                      v-model="mpesaTransactionCode" 
                      type="text" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="e.g., QK123456789"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Received</label>
                    <input 
                      v-model="mpesaAmountReceived" 
                      type="number" 
                      step="0.01"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="0.00"
                    />
                  </div>
                </div>
              </div>
              
              <button @click="processPayment" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded-lg">
                CHARGE
              </button>
            </div>
          </div>
        </div>

        <!-- Other Payment Methods -->
        <div class="space-y-3">
          <button 
            @click="selectedPaymentMethod = 'card'"
            :class="['w-full py-3 px-4 rounded-lg border-2 transition-colors', selectedPaymentMethod === 'card' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400']"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" />
              </svg>
              <span class="font-medium">CARD</span>
            </div>
          </button>

          <button 
            @click="selectedPaymentMethod = 'mpesa'"
            :class="['w-full py-3 px-4 rounded-lg border-2 transition-colors', selectedPaymentMethod === 'mpesa' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400']"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
              </svg>
              <span class="font-medium">MPESA</span>
            </div>
          </button>
        </div>

        <!-- Payment Status -->
        <div v-if="paymentCompleted" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center justify-between">
            <span class="text-green-800 font-medium">Payment Status</span>
            <span class="text-green-600 font-bold">✓ Paid {{ selectedPaymentMethod.toUpperCase() }}</span>
          </div>
          <div class="mt-2 text-sm text-green-700">
            <span v-if="selectedPaymentMethod === 'cash'">
              Amount received: KES {{ cashReceivedValue.toFixed(2) }}
              <span v-if="changeAmount > 0" class="ml-2">(Change: KES {{ changeAmount.toFixed(2) }})</span>
            </span>
            <span v-else-if="selectedPaymentMethod === 'card'">
              Card payment processed successfully
            </span>
            <span v-else-if="selectedPaymentMethod === 'mpesa'">
              <span v-if="mpesaManualMode">
                Manual MPESA: {{ mpesaTransactionCode }} (KES {{ mpesaAmountReceived }})
              </span>
              <span v-else>
                MPESA payment sent to {{ mpesaPhone }}
              </span>
            </span>
          </div>
        </div>

        <!-- DONE Button -->
        <div v-if="allPaymentsComplete" class="mt-6">
          <button 
            @click="completeTransaction"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg text-lg"
          >
            DONE
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Split Payment View -->
  <div v-if="showSplitView" class="fixed inset-0 bg-gray-100 z-50">
    <!-- Header -->
    <div class="bg-green-600 text-white px-6 py-4 flex items-center justify-between">
      <button @click="backToSplitView" class="text-white hover:text-green-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div class="text-xl font-semibold">Remaining {{ remainingAmount.toFixed(2) }}</div>
      <div class="text-white font-semibold">SPLIT</div>
    </div>

    <!-- Main Split Payment Interface -->
    <div class="flex h-full">
      <!-- Left Panel: Ticket Summary -->
      <div class="w-1/3 bg-white border-r border-gray-200">
        <div class="p-6">
          <!-- Ticket Header -->
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Ticket</h2>
            <div class="flex items-center space-x-2">
              <button class="p-2 text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </button>
              <button class="p-2 text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="space-y-3 mb-4">
            <div v-for="item in cart" :key="item.id" class="flex justify-between items-center">
              <div class="flex-1">
                <div class="font-medium">{{ item.name }}</div>
                <div v-if="item.note" class="text-sm text-gray-500">{{ item.note }}</div>
              </div>
              <div class="text-right">
                <div class="font-medium">KES {{ (item.price * item.qty).toFixed(2) }}</div>
                <div class="text-sm text-gray-500">x{{ item.qty }}</div>
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

      <!-- Right Panel: Split Payment Interface -->
      <div class="w-2/3 bg-gray-50 p-6 overflow-y-auto">
        <!-- Payments Counter -->
        <div class="flex items-center justify-center mb-6">
          <button @click="removeSplit(paymentSplits.length - 1)" class="p-2 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
          </button>
          <div class="mx-4 text-center">
            <div class="text-3xl font-bold text-gray-900">{{ paymentSplits.length }}</div>
            <div class="text-gray-600">Payments</div>
          </div>
          <button @click="addSplit" class="p-2 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
        </div>

        <!-- Split Payment Lines -->
        <div class="space-y-4">
          <div v-for="(split, index) in paymentSplits" :key="split.id" class="bg-white p-4 rounded-lg border border-gray-200">
            <!-- Split Header -->
            <div class="flex items-center space-x-3 mb-3">
              <!-- Remove Button -->
              <button @click="removeSplit(index)" class="text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>

              <!-- Payment Method Dropdown -->
              <div class="relative">
                <select 
                  v-model="split.paymentMethod"
                  class="appearance-none bg-white border border-gray-300 rounded px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                >
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="mpesa">Mpesa</option>
                </select>
                <svg class="absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>

              <!-- Amount Display -->
              <span class="font-medium text-gray-700">KES {{ split.amount }}</span>

              <!-- Charge Button or Payment Status -->
              <div v-if="!split.isCompleted" class="ml-auto">
                <button 
                  @click="processSplitPayment(index)"
                  class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded"
                >
                  CHARGE
                </button>
              </div>
              <div v-else class="ml-auto">
                <span class="text-green-600 font-bold">Paid ✓ {{ split.paymentMethod.toUpperCase() }}</span>
              </div>
            </div>
            
            <!-- Payment Method Specific Inputs -->
            <div v-if="!split.isCompleted" class="mt-3 pt-3 border-t border-gray-200">
              <!-- Cash Payment Input -->
              <div v-if="split.paymentMethod === 'cash'" class="space-y-3">
                <div class="flex items-center space-x-3">
                  <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <input 
                      v-model="split.cashReceived" 
                      type="number" 
                      step="0.01"
                      class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                      placeholder="Cash received"
                    />
                  </div>
                </div>
                
                <!-- Change Display for Cash -->
                <div v-if="getSplitChangeAmount(index) > 0" class="p-2 bg-blue-50 border border-blue-200 rounded">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-800">Change:</span>
                    <span class="text-blue-800 font-bold">KES {{ getSplitChangeAmount(index).toFixed(2) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Card Payment Input -->
              <div v-if="split.paymentMethod === 'card'" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Card Number</label>
                    <input 
                      v-model="split.cardNumber" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="1234 5678 9012 3456"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">CVV</label>
                    <input 
                      v-model="split.cardCvv" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="123"
                    />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Expiry Date</label>
                  <input 
                    v-model="split.cardExpiry" 
                    type="text" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="MM/YY"
                  />
                </div>
              </div>
              
              <!-- MPESA Payment Input -->
              <div v-if="split.paymentMethod === 'mpesa'" class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                  <input 
                    v-model="split.mpesaPhone" 
                    type="tel" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="07XX XXX XXX"
                  />
                </div>
                
                <!-- Manual MPESA Option -->
                <div class="border-t pt-3">
                  <div class="flex items-center mb-2">
                    <input 
                      v-model="split.mpesaManualMode" 
                      type="checkbox" 
                      :id="'mpesaManual' + index" 
                      class="mr-2"
                    />
                    <label :for="'mpesaManual' + index" class="text-xs font-medium text-gray-700">Manual Entry</label>
                  </div>
                  
                  <div v-if="split.mpesaManualMode" class="space-y-2">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Code</label>
                      <input 
                        v-model="split.mpesaTransactionCode" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="e.g., QK123456789"
                      />
                    </div>
                    
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Amount Received</label>
                      <input 
                        v-model="split.mpesaAmountReceived" 
                        type="number" 
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="0.00"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Validation -->
        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
          <div class="flex justify-between items-center">
            <span class="text-gray-700">Split Total:</span>
            <span class="font-bold text-lg">
              KES {{ paymentSplits.reduce((sum, split) => sum + parseFloat(split.amount), 0).toFixed(2) }}
            </span>
          </div>
          <div class="flex justify-between items-center mt-2">
            <span class="text-gray-700">Ticket Total:</span>
            <span class="font-bold text-lg">KES {{ total.toFixed(2) }}</span>
          </div>
          <div class="mt-2 text-sm" :class="paymentSplits.reduce((sum, split) => sum + parseFloat(split.amount), 0) === total ? 'text-green-600' : 'text-red-600'">
            {{ paymentSplits.reduce((sum, split) => sum + parseFloat(split.amount), 0) === total ? '✓ Amounts match' : '✗ Amounts do not match' }}
          </div>
        </div>

        <!-- DONE Button for Split Payments -->
        <div v-if="allPaymentsComplete" class="mt-6">
          <button 
            @click="completeTransaction"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg text-lg"
          >
            DONE
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios'; // Added axios import

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
const cashReceived = ref('');
const selectedPaymentMethod = ref('cash'); // 'cash', 'card', 'mpesa'
const showSplitView = ref(false);
const paymentSplits = ref([]);
const currentSplitIndex = ref(0);
const cashPaymentCompleted = ref(false);

// Card payment fields
const cardNumber = ref('');
const cardExpiry = ref('');
const cardCvv = ref('');

// MPESA payment fields
const mpesaPhone = ref('');
const mpesaManualMode = ref(false);
const mpesaTransactionCode = ref('');
const mpesaAmountReceived = ref('');

// Add missing computed properties and functions
const filteredItems = computed(() => {
  if (!props.stockItems) return [];
    let items = props.stockItems;

    if (selectedCategory.value) {
      items = items.filter(item => {
    return item.item.category && item.item.category.name === selectedCategory.value;
  });
    }

    // Debug: Log items being displayed
    const compositeItems = items.filter(item => item.item.is_composite);
    console.log('POS Items Debug:', {
      totalItems: items.length,
      compositeItems: compositeItems.length,
      compositeItemDetails: compositeItems.map(item => ({
        id: item.item.id,
        name: item.item.name,
        is_composite: item.item.is_composite,
        has_variants: item.item.has_variants,
        components_info: item.item.components_info
      }))
    });

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
    console.log('Selected customer:', customer);
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
  
  console.log('Total calculation:', {
    cart: cart.value,
    subtotal,
    tax: tax.value,
    discount: discount.value,
    total: totalValue
  });
  
  return totalValue;
});

function addToCart(stock) {
  const existingItem = cart.value.find(item => item.id === stock.id);
  if (existingItem) {
    existingItem.qty += 1;
  } else {
    // Use the price from stock_items.price for all items
    const price = Number(stock.price) || 0;

    // Debug logging to help identify price issues
    if (price === 0) {
      console.warn('Item added with zero price:', {
        stockId: stock.id,
        stockPrice: stock.price,
        itemName: stock.item?.name,
        isComposite: stock.item?.is_composite,
        calculatedPrice: price
      });
    }

    cart.value.push({
      id: stock.id,
      name: stock.variant ? `${stock.item.name} - ${Object.values(stock.variant.options).join(' / ')}` : stock.item.name,
      price: price,
      qty: 1,
      note: '',
      isComposite: stock.item?.is_composite || false
    });
  }
}

function incrementQty(item) {
  item.qty += 1;
}

function decrementQty(item) {
  if (item.qty > 1) {
    item.qty -= 1;
  }
}

function removeFromCart(item) {
  const index = cart.value.findIndex(cartItem => cartItem.id === item.id);
  if (index > -1) {
    cart.value.splice(index, 1);
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
  await axios.post('/time-clock/clock-out');
  showSidebar.value = false; // Close the sidebar on clock out
}

function logout() {
  router.post('/logout').then(() => {
    // Perform a full page reload after logout
    window.location.reload();
  }).catch(() => {
    // Even if the logout request fails, still reload the page
    window.location.reload();
  });
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
  console.log('Starting customer save...', newCustomer.value);
  isSavingCustomer.value = true;
  try {
    const response = await axios.post('/customers', newCustomer.value);
    console.log('Customer saved successfully:', response.data);
    
    if (response.data.success) {
      showNewCustomerModal.value = false;
      closeNewCustomerModal();
      showToaster('Customer created successfully!', 'success');
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

function charge() {
  if (cart.value.length === 0) {
    showToaster('Please add items to cart first', 'error');
    return;
  }
  showPaymentView.value = true;
  cashReceived.value = remainingAmount.value.toFixed(2);
}

function backToPOS() {
  showPaymentView.value = false;
  cashReceived.value = '';
  selectedPaymentMethod.value = 'cash';
  cashPaymentCompleted.value = false;
  
  // Reset card fields
  cardNumber.value = '';
  cardExpiry.value = '';
  cardCvv.value = '';
  
  // Reset MPESA fields
  mpesaPhone.value = '';
  mpesaManualMode.value = false;
  mpesaTransactionCode.value = '';
  mpesaAmountReceived.value = '';
}

function processPayment() {
  const totalValue = remainingAmount.value;
  
  if (selectedPaymentMethod.value === 'cash') {
    if (cashReceivedValue.value < totalValue) {
      showToaster('Cash received is less than remaining amount', 'error');
      return;
    }
    
    // Mark the cash payment as completed
    cashPaymentCompleted.value = true;
    
    // Show success message with change info if applicable
    if (changeAmount.value > 0) {
      showToaster(`Cash payment processed! Change: KES ${changeAmount.value.toFixed(2)}`, 'success');
    } else {
      showToaster('Cash payment processed successfully!', 'success');
    }
  } else if (selectedPaymentMethod.value === 'card') {
    if (!cardNumber.value || !cardExpiry.value || !cardCvv.value) {
      showToaster('Please fill in all card details', 'error');
      return;
    }
    
    // Mark the card payment as completed
    cashPaymentCompleted.value = true;
    showToaster('Card payment processed successfully!', 'success');
  } else if (selectedPaymentMethod.value === 'mpesa') {
    if (!mpesaPhone.value) {
      showToaster('Please enter a phone number', 'error');
      return;
    }
    
    if (mpesaManualMode.value) {
      if (!mpesaTransactionCode.value || !mpesaAmountReceived.value) {
        showToaster('Please fill in transaction code and amount received', 'error');
        return;
      }
      
      const amountReceived = parseFloat(mpesaAmountReceived.value);
      if (amountReceived < remainingAmount.value) {
        showToaster('Amount received is less than remaining amount', 'error');
        return;
      }
      
      // Mark the manual MPESA payment as completed
      cashPaymentCompleted.value = true;
      showToaster(`Manual MPESA payment confirmed: ${mpesaTransactionCode.value}`, 'success');
    } else {
      // Mark the automatic MPESA payment as completed
      cashPaymentCompleted.value = true;
      showToaster(`MPESA payment sent to ${mpesaPhone.value}`, 'success');
    }
  }
  
  // Don't clear cart or return to POS yet - wait for DONE button
}

function initializeSplitPayment() {
  const totalAmount = remainingAmount.value;
  const splitCount = 2; // Default to 2 splits
  const amountPerSplit = totalAmount / splitCount;
  
  paymentSplits.value = [];
  for (let i = 0; i < splitCount; i++) {
    paymentSplits.value.push({
      id: i,
      amount: amountPerSplit.toFixed(2),
      paymentMethod: 'cash',
      isCompleted: false,
      // Cash fields
      cashReceived: '',
      changeAmount: 0,
      // Card fields
      cardNumber: '',
      cardExpiry: '',
      cardCvv: '',
      // MPESA fields
      mpesaPhone: '',
      mpesaManualMode: false,
      mpesaTransactionCode: '',
      mpesaAmountReceived: ''
    });
  }
  showSplitView.value = true;
}

function addSplit() {
  const newSplit = {
    id: paymentSplits.value.length,
    amount: '0.00',
    paymentMethod: 'cash',
    isCompleted: false,
    // Cash fields
    cashReceived: '',
    changeAmount: 0,
    // Card fields
    cardNumber: '',
    cardExpiry: '',
    cardCvv: '',
    // MPESA fields
    mpesaPhone: '',
    mpesaManualMode: false,
    mpesaTransactionCode: '',
    mpesaAmountReceived: ''
  };
  paymentSplits.value.push(newSplit);
  redistributeAmounts();
}

function removeSplit(index) {
  if (paymentSplits.value.length > 1) {
    paymentSplits.value.splice(index, 1);
    // Reassign IDs
    paymentSplits.value.forEach((split, i) => {
      split.id = i;
    });
    redistributeAmounts();
  }
}

function redistributeAmounts() {
  const totalAmount = total.value;
  const paidSplits = paymentSplits.value.filter(s => s.isCompleted);
  const paidTotal = paidSplits.reduce((sum, s) => sum + parseFloat(s.amount), 0);
  const remainingAmount = totalAmount - paidTotal;
  const unpaidSplits = paymentSplits.value.filter(s => !s.isCompleted);
  const amountPerUnpaidSplit = remainingAmount / unpaidSplits.length;
  
  // Update only unpaid splits
  paymentSplits.value.forEach((split) => {
    if (!split.isCompleted) {
      split.amount = amountPerUnpaidSplit.toFixed(2);
    }
  });
}

function updateSplitAmount(index, newAmount) {
  const split = paymentSplits.value[index];
  split.amount = parseFloat(newAmount).toFixed(2);
  
  // Calculate total paid so far
  const totalAmount = total.value;
  const paidSplits = paymentSplits.value.filter(s => s.isCompleted);
  const paidTotal = paidSplits.reduce((sum, s) => sum + parseFloat(s.amount), 0);
  const currentAmount = parseFloat(split.amount);
  const remainingAmountToDistribute = totalAmount - paidTotal - currentAmount;

  // Only redistribute among other incomplete splits
  const otherIncompleteSplits = paymentSplits.value
    .map((split, i) => ({ split, i }))
    .filter(({ split, i }) => !split.isCompleted && i !== index);

  if (otherIncompleteSplits.length > 0) {
    const amountPerOtherSplit = remainingAmountToDistribute / otherIncompleteSplits.length;
    otherIncompleteSplits.forEach(({ split }) => {
      split.amount = amountPerOtherSplit.toFixed(2);
    });
  }
}

function getSplitChangeAmount(splitIndex) {
  const split = paymentSplits.value[splitIndex];
  if (!split || split.paymentMethod !== 'cash') return 0;
  
  const splitAmount = parseFloat(split.amount) || 0;
  const cashReceived = parseFloat(split.cashReceived) || 0;
  
  return Math.max(0, cashReceived - splitAmount);
}

function redistributeRemainingAmount(amountToRedistribute, excludeIndex) {
  if (amountToRedistribute <= 0) return;
  
  // Get all incomplete splits except the one we're processing
  const incompleteSplits = paymentSplits.value
    .map((split, index) => ({ split, index }))
    .filter(({ split, index }) => !split.isCompleted && index !== excludeIndex);
  
  if (incompleteSplits.length === 0) return;
  
  // Calculate how much to add to each incomplete split
  const amountPerSplit = amountToRedistribute / incompleteSplits.length;
  
  // Redistribute the amount
  incompleteSplits.forEach(({ split }) => {
    const currentAmount = parseFloat(split.amount) || 0;
    split.amount = (currentAmount + amountPerSplit).toFixed(2);
  });
}

function processSplitPayment(splitIndex) {
  const split = paymentSplits.value[splitIndex];
  if (!split || split.isCompleted) return;
  
  const splitAmount = parseFloat(split.amount);
  
  if (split.paymentMethod === 'cash') {
    const cashReceived = parseFloat(split.cashReceived) || 0;
    if (cashReceived < splitAmount) {
      // Allow partial payment and redistribute remaining amount
      const actualPaid = cashReceived;
      const remainingToRedistribute = splitAmount - actualPaid;
      
      // Update this split's amount to what was actually paid
      split.amount = actualPaid.toFixed(2);
      split.isCompleted = true;
      
      // Redistribute the remaining amount to other incomplete splits
      redistributeRemainingAmount(remainingToRedistribute, splitIndex);
      
      const changeAmount = getSplitChangeAmount(splitIndex);
      if (changeAmount > 0) {
        showToaster(`Cash payment processed! Change: KES ${changeAmount.toFixed(2)}`, 'success');
      } else {
        showToaster('Cash payment processed successfully!', 'success');
      }
    } else {
      split.isCompleted = true;
      const changeAmount = getSplitChangeAmount(splitIndex);
      if (changeAmount > 0) {
        showToaster(`Cash payment processed! Change: KES ${changeAmount.toFixed(2)}`, 'success');
      } else {
        showToaster('Cash payment processed successfully!', 'success');
      }
    }
  } else if (split.paymentMethod === 'card') {
    if (!split.cardNumber || !split.cardExpiry || !split.cardCvv) {
      showToaster('Please fill in all card details', 'error');
      return;
    }
    
    split.isCompleted = true;
    showToaster('Card payment processed successfully!', 'success');
  } else if (split.paymentMethod === 'mpesa') {
    if (!split.mpesaPhone) {
      showToaster('Please enter a phone number', 'error');
      return;
    }
    
    if (split.mpesaManualMode) {
      if (!split.mpesaTransactionCode || !split.mpesaAmountReceived) {
        showToaster('Please fill in transaction code and amount received', 'error');
        return;
      }
      
      const amountReceived = parseFloat(split.mpesaAmountReceived);
      if (amountReceived < splitAmount) {
        // Allow partial payment and redistribute remaining amount
        const actualPaid = amountReceived;
        const remainingToRedistribute = splitAmount - actualPaid;
        
        // Update this split's amount to what was actually paid
        split.amount = actualPaid.toFixed(2);
        split.isCompleted = true;
        
        // Redistribute the remaining amount to other incomplete splits
        redistributeRemainingAmount(remainingToRedistribute, splitIndex);
        
        showToaster(`Manual MPESA payment confirmed: ${split.mpesaTransactionCode}`, 'success');
      } else {
        split.isCompleted = true;
        showToaster(`Manual MPESA payment confirmed: ${split.mpesaTransactionCode}`, 'success');
      }
    } else {
      split.isCompleted = true;
      showToaster(`MPESA payment sent to ${split.mpesaPhone}`, 'success');
    }
  }
}

function completeTransaction() {
  // All payments are complete, show success message but don't clear cart yet
  showToaster('Transaction completed successfully!', 'success');
  // Don't clear cart or return to POS - let user manually go back
  // cart.value = [];
  // backToPOS();
}

// Payment completion status
const paymentCompleted = computed(() => {
  if (showSplitView.value) {
    return paymentSplits.value.every(split => split.isCompleted);
  } else {
    return cashPaymentCompleted.value;
  }
});

// Computed property to check if all payments are complete
const allPaymentsComplete = computed(() => {
  if (showSplitView.value) {
    // Check if all splits are completed and amounts match
    const allCompleted = paymentSplits.value.every(split => split.isCompleted);
    const totalSplitAmount = paymentSplits.value.reduce((sum, split) => sum + parseFloat(split.amount), 0);
    const ticketTotal = total.value;
    const amountsMatch = Math.abs(totalSplitAmount - ticketTotal) < 0.01;
    
    console.log('Split payment status:', {
      allCompleted,
      totalSplitAmount,
      ticketTotal,
      amountsMatch,
      splits: paymentSplits.value
    });
    
    return allCompleted && amountsMatch;
  } else {
    // For single payment, consider it complete when payment is processed
    return paymentCompleted.value;
  }
});

function backToSplitView() {
  showSplitView.value = false;
  paymentSplits.value = [];
  currentSplitIndex.value = 0;
}

// Cart calculations
const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

const discount = computed(() => {
  // For now, return 0. You can implement discount logic later
  return 0;
});

// Cash received value (robust parsing)
const cashReceivedValue = computed(() => {
  return parseFloat(cashReceived.value) || 0;
});

// Change calculation
const changeAmount = computed(() => {
  const totalValue = total.value;
  return Math.max(0, cashReceivedValue.value - totalValue);
});

// Quick amount buttons (50s increments)
const quickAmounts = computed(() => {
  const totalValue = total.value;
  const baseAmount = Math.ceil(totalValue / 50) * 50; // Round up to nearest 50
  return [
    baseAmount,
    baseAmount + 50,
    baseAmount + 100,
    baseAmount + 150
  ];
});

// Remaining amount for split payments
const remainingAmount = computed(() => {
  const totalValue = total.value;
  const paidAmount = paymentSplits.value.reduce((sum, split) => {
    return sum + (split.isCompleted ? parseFloat(split.amount) : 0);
  }, 0);
  return Math.max(0, totalValue - paidAmount);
});


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