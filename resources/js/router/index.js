import FlutterwaveTest from '../components/FlutterwaveTest.vue'
import Purchase from '../pages/POS/Purchase.vue';

const routes = [
  {
    path: '/test',
    name: 'FlutterwaveTest',
    component: FlutterwaveTest,
    meta: { requiresAdmin: true }
  },
  {
    path: '/pos/purchase/:id',
    name: 'pos.purchase',
    component: Purchase
  },
]