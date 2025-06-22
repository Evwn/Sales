<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead>
        <tr>
          <th class="px-4 py-2 text-left">#</th>
          <th class="px-4 py-2 text-left">Date</th>
          <th class="px-4 py-2 text-left">Business</th>
          <th class="px-4 py-2 text-left">Branch</th>
          <th class="px-4 py-2 text-left">Seller</th>
          <th class="px-4 py-2 text-left">Products</th>
          <th class="px-4 py-2 text-left">Profit</th>
          <th class="px-4 py-2 text-left">Amount</th>
          <th class="px-4 py-2 text-left">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(sale, index) in sales" :key="sale.id" 
            class="hover:bg-gray-50 cursor-pointer" 
            @click="showRowModal(sale)">
          <td class="px-4 py-2 font-medium text-gray-900">{{ index + 1 }}</td>
          <td class="px-4 py-2">{{ formatDate(sale.created_at) }}</td>
          <td class="px-4 py-2">{{ sale.business?.name || '—' }}</td>
          <td class="px-4 py-2">{{ sale.branch?.name || '—' }}</td>
          <td class="px-4 py-2">{{ sale.seller?.name || '—' }}</td>
          <td class="px-4 py-2">
            <span v-if="sale.items && sale.items.length" 
                  :title="sale.items.length > 1 ? sale.items.map(i => {
                    let productName = 'Unknown Product';
                    if (i.product?.inventoryItem?.name) {
                      productName = i.product.inventoryItem.name;
                    } else if (i.product?.name) {
                      productName = i.product.name;
                    } else if (i.product?.display_name) {
                      productName = i.product.display_name;
                    }
                    return `${productName} x${i.quantity}`;
                  }).join(', ') : ''"
                  :class="sale.items.length > 1 ? 'cursor-pointer hover:text-blue-600' : 'cursor-help'"
                  @click.stop="sale.items.length > 1 ? showItemsModal(sale) : null">
              {{ sale.items.map((i, itemIndex) => {
                // Try multiple sources for product name
                let productName = 'Unknown Product';
                if (i.product?.inventoryItem?.name) {
                  productName = i.product.inventoryItem.name;
                } else if (i.product?.name) {
                  productName = i.product.name;
                } else if (i.product?.display_name) {
                  productName = i.product.display_name;
                }
                
                const buyingPrice = i.product?.buying_price || 0;
                const sellingPrice = i.unit_price || 0;
                const profit = sellingPrice - buyingPrice;
                const totalProfit = profit * i.quantity;
                const itemText = `${productName} x${i.quantity} (Buy: KES ${buyingPrice}, Sell: KES ${sellingPrice}, Profit: KES ${totalProfit.toFixed(2)})`;
                
                // Show only first item in table, add "..." if there are more
                if (itemIndex === 0) {
                  if (sale.items.length > 1) {
                    return itemText + ' ...';
                  }
                  return itemText;
                }
                return null; // Don't show other items in table
              }).filter(text => text !== null).join(', ') }}
            </span>
            <span v-else>—</span>
          </td>
          <td class="px-4 py-2 font-medium text-green-600">
            KES {{ calculateTotalProfit(sale).toFixed(2) }}
          </td>
          <td class="px-4 py-2">KES {{ Number(sale.amount).toFixed(2) }}</td>
          <td class="px-4 py-2">{{ sale.status }}</td>
        </tr>
        <tr v-if="!sales || sales.length === 0">
          <td colspan="10" class="text-center py-4 text-gray-400">No sales found</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script setup>
import Swal from 'sweetalert2';

const props = defineProps({
  sales: { type: Array, default: () => [] }
});

function formatDate(date) {
  return new Date(date).toLocaleDateString();
}

function calculateTotalProfit(sale) {
  if (!sale.items || sale.items.length === 0) return 0;
  
  return sale.items.reduce((total, item) => {
    const buyingPrice = item.product?.buying_price || 0;
    const sellingPrice = item.unit_price || 0;
    const profit = sellingPrice - buyingPrice;
    return total + (profit * item.quantity);
  }, 0);
}

function showRowModal(sale) {
  const totalProfit = calculateTotalProfit(sale);
  const profitPercentage = sale.amount > 0 ? ((totalProfit / sale.amount) * 100).toFixed(1) : 0;
  
  const itemsList = sale.items.map((i, index) => {
    // Try multiple sources for product name
    let productName = 'Unknown Product';
    if (i.product?.inventoryItem?.name) {
      productName = i.product.inventoryItem.name;
    } else if (i.product?.name) {
      productName = i.product.name;
    } else if (i.product?.display_name) {
      productName = i.product.display_name;
    }
    
    const buyingPrice = i.product?.buying_price || 0;
    const sellingPrice = i.unit_price || 0;
    const profit = sellingPrice - buyingPrice;
    const totalProfit = profit * i.quantity;
    const itemProfitPercentage = sellingPrice > 0 ? ((profit / sellingPrice) * 100).toFixed(1) : 0;
    
    return `${index + 1}. ${productName} x${i.quantity}<br>
            &nbsp;&nbsp;&nbsp;&nbsp;Buy: KES ${buyingPrice} | Sell: KES ${sellingPrice} | Profit: KES ${totalProfit.toFixed(2)} (${itemProfitPercentage}%)`;
  }).join('<br><br>');

  Swal.fire({
    title: `Sale #${sale.reference || sale.id} - Complete Details`,
    html: `
      <div class="text-left max-h-96 overflow-y-auto">
        <div class="mb-4 p-3 bg-gray-50 rounded">
          <strong>Sale Information:</strong><br>
          Reference: ${sale.reference || sale.id}<br>
          Date: ${formatDate(sale.created_at)}<br>
          Business: ${sale.business?.name || 'N/A'}<br>
          Branch: ${sale.branch?.name || 'N/A'}<br>
          Seller: ${sale.seller?.name || 'N/A'}<br>
          Status: ${sale.status}<br>
          Payment Method: ${sale.payment_method || 'N/A'}
        </div>
        <div class="mb-4 p-3 bg-blue-50 rounded">
          <strong>Financial Summary:</strong><br>
          Total Amount: KES ${Number(sale.amount).toFixed(2)}<br>
          Total Profit: KES ${totalProfit.toFixed(2)}<br>
          Profit Margin: ${profitPercentage}%
        </div>
        <div class="border-t pt-4">
          <strong>Items (${sale.items.length}):</strong><br><br>
          ${itemsList}
        </div>
      </div>
    `,
    width: '700px',
    confirmButtonText: 'Close',
    confirmButtonColor: '#3085d6',
    showCloseButton: true,
    customClass: {
      container: 'swal-row-modal'
    }
  });
}

function showItemsModal(sale) {
  const itemsList = sale.items.map((i, index) => {
    // Try multiple sources for product name
    let productName = 'Unknown Product';
    if (i.product?.inventoryItem?.name) {
      productName = i.product.inventoryItem.name;
    } else if (i.product?.name) {
      productName = i.product.name;
    } else if (i.product?.display_name) {
      productName = i.product.display_name;
    }
    
    const buyingPrice = i.product?.buying_price || 0;
    const sellingPrice = i.unit_price || 0;
    const profit = sellingPrice - buyingPrice;
    const totalProfit = profit * i.quantity;
    
    return `${index + 1}. ${productName} x${i.quantity}<br>
            &nbsp;&nbsp;&nbsp;&nbsp;Buy: KES ${buyingPrice} | Sell: KES ${sellingPrice} | Profit: KES ${totalProfit.toFixed(2)}`;
  }).join('<br><br>');

  Swal.fire({
    title: `Sale #${sale.reference || sale.id} - All Items`,
    html: `
      <div class="text-left max-h-96 overflow-y-auto">
        <div class="mb-4 p-3 bg-gray-50 rounded">
          <strong>Sale Details:</strong><br>
          Date: ${formatDate(sale.created_at)}<br>
          Business: ${sale.business?.name || 'N/A'}<br>
          Branch: ${sale.branch?.name || 'N/A'}<br>
          Seller: ${sale.seller?.name || 'N/A'}<br>
          Total Amount: KES ${Number(sale.amount).toFixed(2)}
        </div>
        <div class="border-t pt-4">
          <strong>Items (${sale.items.length}):</strong><br><br>
          ${itemsList}
        </div>
      </div>
    `,
    width: '600px',
    confirmButtonText: 'Close',
    confirmButtonColor: '#3085d6',
    showCloseButton: true,
    customClass: {
      container: 'swal-items-modal'
    }
  });
}
</script> 