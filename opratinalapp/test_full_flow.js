const { execSync } = require('child_process');
const axios = require('axios');
const fs = require('fs');
const FormData = require('form-data');

const BASE_URL = 'http://127.0.0.1:8000/api';

// Create a dummy image
const dummyImageBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
const dummyImagePath = __dirname + '/dummy.png';
fs.writeFileSync(dummyImagePath, Buffer.from(dummyImageBase64, 'base64'));

async function runFlow() {
  console.log("Seeding Database...");
  const rawOutput = execSync('php d:\\xampp\\htdocs\\app\\wheelwashfull\\public\\test_full_flow_setup.php').toString();
  const dbData = JSON.parse(rawOutput);

  console.log(`\n--- SEED DATA ---`);
  console.log(`Garage Booking ID: ${dbData.garage_booking_id}`);
  console.log(`Doorstep Booking ID: ${dbData.doorstep_booking_id}`);
  
  const tokens = {};
  
  async function login(mobile) {
    const verifyRes = await axios.post(`${BASE_URL}/auth/verify-otp`, { mobile_number: mobile, otp: '123456' });
    return verifyRes.data.data.token;
  }
  
  console.log("\nLogging in users...");
  tokens.admin = await login(dbData.admin_phone);
  tokens.partner = await login(dbData.partner_phone);
  tokens.worker = await login(dbData.worker_phone);
  tokens.driver = await login(dbData.pickup_driver_phone);

  const results = { passed: [], failed: [] };

  async function hitApi(role, token, method, url, body = {}, oldStatus = 'unknown', requestedStatus = 'unknown') {
    try {
      const res = await axios({
        method,
        url: `${BASE_URL}${url}`,
        data: body,
        headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
      });
      const newStatus = res.data?.data?.status || 'unknown';
      results.passed.push({ role, url, method });
      console.log(`[PASS] Role: ${role} | ID: ${url.match(/\\d+/)?.[0]} | Old: ${oldStatus} -> Requested: ${requestedStatus} | Code: ${res.status} | New: ${newStatus}`);
      return newStatus;
    } catch (error) {
      const status = error.response?.status || 500;
      const data = error.response?.data || {};
      const msg = data.message || error.message;
      results.failed.push({ role, url, method, status, error: msg, data });
      console.log(`[FAIL] Role: ${role} | ID: ${url.match(/\\d+/)?.[0]} | Old: ${oldStatus} -> Requested: ${requestedStatus} | Code: ${status}`);
      console.log(`       Backend error: ${msg}`);
      return oldStatus;
    }
  }

  async function uploadMedia(role, token, bookingId, type) {
    const formData = new FormData();
    formData.append('type', type);
    formData.append('file', fs.createReadStream(dummyImagePath));
    
    const url = role === 'driver' ? `/operations/driver/jobs/${bookingId}/media` : `/operations/worker/jobs/${bookingId}/media`;
    
    try {
      const res = await axios.post(`${BASE_URL}${url}`, formData, {
        headers: { ...formData.getHeaders(), Authorization: `Bearer ${token}`, Accept: 'application/json' }
      });
      console.log(`[MEDIA UPLOAD PASS] Role: ${role} | ID: ${bookingId} | Type: ${type}`);
    } catch (error) {
      console.log(`[MEDIA UPLOAD FAIL] Role: ${role} | ID: ${bookingId} | Type: ${type}`);
      console.log(`       Backend error: ${error.response?.data?.message || error.message}`);
    }
  }

  const gId = dbData.garage_booking_id;
  const dId = dbData.doorstep_booking_id;

  let gStatus = 'pending';
  let dStatus = 'pending';

  console.log("\n================ GARAGE BOOKING FLOW (pickup_drop) ================");
  gStatus = await hitApi('admin', tokens.admin, 'POST', `/admin/bookings/${gId}/assign-pickup-driver`, { pickup_driver_id: dbData.driver_id }, gStatus, 'pickup_driver_assigned');
  gStatus = await hitApi('admin', tokens.admin, 'POST', `/admin/bookings/${gId}/assign-partner`, { partner_id: dbData.partner_id }, gStatus, 'partner_assigned');
  gStatus = await hitApi('partner', tokens.partner, 'POST', `/operations/partner/jobs/${gId}/assign-worker`, { worker_id: dbData.worker_id }, gStatus, 'worker_assigned_by_partner');
  gStatus = await hitApi('driver', tokens.driver, 'POST', `/operations/driver/jobs/${gId}/status`, { status: 'driver_on_the_way' }, gStatus, 'driver_on_the_way');
  
  await uploadMedia('driver', tokens.driver, gId, 'pickup_proof');
  gStatus = await hitApi('driver', tokens.driver, 'POST', `/operations/driver/jobs/${gId}/status`, { status: 'car_picked_up' }, gStatus, 'car_picked_up');
  gStatus = await hitApi('driver', tokens.driver, 'POST', `/operations/driver/jobs/${gId}/status`, { status: 'reached_partner' }, gStatus, 'reached_partner');
  
  // Backend doesn't strictly require before_image for service_started but frontend optionally allows it, we'll just test the status change.
  gStatus = await hitApi('worker', tokens.worker, 'POST', `/operations/worker/jobs/${gId}/status`, { status: 'service_started' }, gStatus, 'service_started');
  
  await uploadMedia('worker', tokens.worker, gId, 'after_image');
  gStatus = await hitApi('worker', tokens.worker, 'POST', `/operations/worker/jobs/${gId}/status`, { status: 'service_completed' }, gStatus, 'service_completed');
  
  gStatus = await hitApi('driver', tokens.driver, 'POST', `/operations/driver/jobs/${gId}/status`, { status: 'out_for_delivery' }, gStatus, 'out_for_delivery');
  
  await uploadMedia('driver', tokens.driver, gId, 'delivery_proof');
  gStatus = await hitApi('driver', tokens.driver, 'POST', `/operations/driver/jobs/${gId}/status`, { status: 'delivered' }, gStatus, 'delivered');

  console.log("\n================ DOORSTEP BOOKING FLOW (doorstep) ================");
  dStatus = await hitApi('admin', tokens.admin, 'POST', `/admin/bookings/${dId}/assign-worker`, { worker_id: dbData.worker_id }, dStatus, 'worker_assigned');
  dStatus = await hitApi('worker', tokens.worker, 'POST', `/operations/worker/jobs/${dId}/status`, { status: 'worker_on_the_way' }, dStatus, 'worker_on_the_way');
  dStatus = await hitApi('worker', tokens.worker, 'POST', `/operations/worker/jobs/${dId}/status`, { status: 'service_started' }, dStatus, 'service_started');
  
  await uploadMedia('worker', tokens.worker, dId, 'after_image');
  dStatus = await hitApi('worker', tokens.worker, 'POST', `/operations/worker/jobs/${dId}/status`, { status: 'service_completed' }, dStatus, 'service_completed');
  dStatus = await hitApi('worker', tokens.worker, 'POST', `/operations/worker/jobs/${dId}/status`, { status: 'completed' }, dStatus, 'completed');

  console.log("\n--- TEST SUMMARY ---");
  console.log(`Garage flow failures: ${results.failed.filter(f => f.url.includes(gId)).length}`);
  console.log(`Doorstep flow failures: ${results.failed.filter(f => f.url.includes(dId)).length}`);
  
  try { fs.unlinkSync(dummyImagePath); } catch(e){}
}

runFlow().catch(console.error);
