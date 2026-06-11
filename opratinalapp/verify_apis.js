const axios = require('axios');

const BASE_URL = 'http://127.0.0.1:8000/api'; // Assuming default local Laravel

const endpoints = [
  { method: 'GET', url: '/admin/dashboard' },
  { method: 'GET', url: '/admin/bookings' },
  { method: 'GET', url: '/admin/bookings/1' },
  { method: 'POST', url: '/admin/bookings/1/assign-pickup-driver' },
  { method: 'POST', url: '/admin/bookings/1/assign-partner' },
  { method: 'POST', url: '/admin/bookings/1/assign-worker' },
  { method: 'POST', url: '/admin/bookings/1/status' },
  { method: 'GET', url: '/admin/users' },
  { method: 'GET', url: '/admin/partners' },
  { method: 'GET', url: '/admin/workers' },
  { method: 'GET', url: '/admin/pickup-drivers' },
  { method: 'GET', url: '/admin/services' },
  { method: 'GET', url: '/admin/slots' },
  { method: 'GET', url: '/admin/coupons' },
  { method: 'GET', url: '/admin/reports' },

  { method: 'GET', url: '/operations/worker/dashboard' },
  { method: 'GET', url: '/operations/worker/jobs' },
  { method: 'GET', url: '/operations/worker/jobs/1' },
  { method: 'POST', url: '/operations/worker/jobs/1/status' },
  { method: 'POST', url: '/operations/worker/jobs/1/media' },
  { method: 'GET', url: '/operations/worker/earnings' },

  { method: 'GET', url: '/operations/partner/dashboard' },
  { method: 'GET', url: '/operations/partner/jobs' },
  { method: 'GET', url: '/operations/partner/jobs/1' },
  { method: 'GET', url: '/operations/partner/workers' },
  { method: 'POST', url: '/operations/partner/jobs/1/assign-worker' },
  { method: 'POST', url: '/operations/partner/jobs/1/status' },
  { method: 'GET', url: '/partner/earnings' },

  { method: 'GET', url: '/operations/driver/dashboard' },
  { method: 'GET', url: '/operations/driver/jobs' },
  { method: 'GET', url: '/operations/driver/jobs/1' },
  { method: 'POST', url: '/operations/driver/jobs/1/status' },
  { method: 'POST', url: '/operations/location/update' },
  { method: 'POST', url: '/operations/driver/jobs/1/media' },
  { method: 'GET', url: '/operations/driver/earnings' }
];

async function checkEndpoints() {
  console.log('Starting Live API Verification...\n');
  for (const ep of endpoints) {
    try {
      await axios({ method: ep.method, url: `${BASE_URL}${ep.url}`, headers: { Accept: 'application/json' } });
      console.log(`[OK] ${ep.method} ${ep.url}`);
    } catch (error) {
      const status = error.response ? error.response.status : error.code;
      console.log(`[${status}] ${ep.method} ${ep.url}`);
    }
  }
}

checkEndpoints();
