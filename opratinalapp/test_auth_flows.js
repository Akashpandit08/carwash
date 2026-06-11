const axios = require('axios');

const BASE_URL = 'http://127.0.0.1:8000/api';

const users = [
  { role: 'admin', mobile: '1000000001', expectedRole: 'admin' },
  { role: 'partner', mobile: '1000000002', expectedRole: 'partner' },
  { role: 'worker', mobile: '1000000003', expectedRole: 'worker' },
  { role: 'driver', mobile: '1000000004', expectedRole: 'pickup_driver' },
  { role: 'customer', mobile: '1000000005', expectedRole: 'customer' } // Should fail in operations app
];

const results = { passed: [], failed: [] };

async function login(mobile) {
  try {
    // We already seeded 1-4 with OTP 123456. User 5 (customer) will be created via send-otp
    if (mobile === '1000000005') {
      const sendRes = await axios.post(`${BASE_URL}/auth/send-otp`, { mobile_number: mobile });
      const otp = sendRes.data.data.otp;
      const verifyRes = await axios.post(`${BASE_URL}/auth/verify-otp`, { mobile_number: mobile, otp });
      return verifyRes.data.data.token;
    } else {
      const verifyRes = await axios.post(`${BASE_URL}/auth/verify-otp`, { mobile_number: mobile, otp: '123456' });
      return verifyRes.data.data.token;
    }
  } catch (error) {
    console.log(`Failed to login mobile ${mobile}:`, error.response?.data || error.message);
    return null;
  }
}

async function hitApi(token, method, url, body = {}) {
  try {
    const res = await axios({
      method,
      url: `${BASE_URL}${url}`,
      data: body,
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
    });
    results.passed.push({ url, status: res.status });
    console.log(`[PASS] ${method} ${url} -> ${res.status}`);
  } catch (error) {
    const status = error.response?.status || 500;
    const msg = error.response?.data?.message || error.message;
    results.failed.push({ url, status, msg });
    console.log(`[FAIL] ${method} ${url} -> ${status}: ${msg}`);
  }
}

async function runTests() {
  console.log("Starting Authenticated Tests...\n");
  
  // 1. Get Tokens
  const tokens = {};
  for (const u of users) {
    const token = await login(u.mobile);
    if (token) tokens[u.role] = token;
  }

  // 2. Test Customer Role Redirect
  if (tokens['customer']) {
    console.log("Testing Customer access to Admin endpoint (should fail):");
    await hitApi(tokens['customer'], 'GET', '/admin/dashboard');
  }

  // 3. Test Admin Endpoints
  console.log("\nTesting Admin Endpoints:");
  const adminToken = tokens['admin'];
  if (adminToken) {
    await hitApi(adminToken, 'GET', '/admin');
    await hitApi(adminToken, 'GET', '/admin/dashboard');
    await hitApi(adminToken, 'GET', '/admin/bookings');
    await hitApi(adminToken, 'GET', '/admin/bookings/1'); // May 404 if booking missing
    await hitApi(adminToken, 'POST', '/admin/bookings/1/assign-pickup-driver', { driver_id: 1 });
    await hitApi(adminToken, 'POST', '/admin/bookings/1/assign-partner', { partner_id: 1 });
    await hitApi(adminToken, 'POST', '/admin/bookings/1/assign-worker', { worker_id: 1 });
  }

  // 4. Test Partner Endpoints
  console.log("\nTesting Partner Endpoints:");
  const partnerToken = tokens['partner'];
  if (partnerToken) {
    await hitApi(partnerToken, 'GET', '/operations/partner/jobs');
    await hitApi(partnerToken, 'GET', '/operations/partner/workers');
    await hitApi(partnerToken, 'POST', '/operations/partner/jobs/1/assign-worker', { worker_id: 1 });
  }

  // 5. Test Worker Endpoints
  console.log("\nTesting Worker Endpoints:");
  const workerToken = tokens['worker'];
  if (workerToken) {
    await hitApi(workerToken, 'GET', '/operations/worker/jobs');
    await hitApi(workerToken, 'POST', '/operations/worker/jobs/1/status', { status: 'service_started' });
    // Media uploads are multipart, using empty obj here will likely yield 422 (validation error), which is fine for route testing
    await hitApi(workerToken, 'POST', '/operations/worker/jobs/1/media'); 
  }

  // 6. Test Driver Endpoints
  console.log("\nTesting Driver Endpoints:");
  const driverToken = tokens['driver'];
  if (driverToken) {
    await hitApi(driverToken, 'GET', '/operations/driver/jobs');
    await hitApi(driverToken, 'POST', '/operations/driver/jobs/1/status', { status: 'driver_on_the_way' });
    await hitApi(driverToken, 'POST', '/operations/location/update', { latitude: 12, longitude: 13 });
    await hitApi(driverToken, 'POST', '/operations/driver/jobs/1/media');
  }

  console.log("\n--- TEST SUMMARY ---");
  console.log(`Passed: ${results.passed.length}`);
  console.log(`Failed: ${results.failed.length}`);
}

runTests();
