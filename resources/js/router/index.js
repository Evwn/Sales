import FlutterwaveTest from '../components/FlutterwaveTest.vue'

const routes = [
  {
    path: '/test',
    name: 'FlutterwaveTest',
    component: FlutterwaveTest,
    meta: { requiresAdmin: true }
  },
] 