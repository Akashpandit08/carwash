export type JobAction = {
  phase?: 'pickup' | 'delivery' | 'waiting';
  key: string;
  label: string;
  api: string | null;
  requiresGpsCheck?: boolean;
  requiresPhotos?: boolean;
  photoType?: 'before_image' | 'after_image' | 'pickup_proof' | 'delivery_proof';
  minPhotos?: number;
  requiresCashCollection?: boolean;
};

export const normalizeStatus = (status: string | undefined): string => {
  if (!status) return 'pending';

  switch (status.toLowerCase()) {
    case 'wash_started':
      return 'service_started';
    case 'wash_completed':
      return 'service_completed';
    case 'vehicle_picked_up':
      return 'car_picked_up';
    case 'vehicle_reached_garage':
    case 'vehicle_dropped_at_partner':
      return 'reached_partner';
    case 'delivery_started':
      return 'out_for_delivery';
    case 'vehicle_delivered':
      return 'delivered';
    case 'assigned_pickup_driver':
      return 'pickup_driver_assigned';
    default:
      return status.toLowerCase();
  }
};

export const normalizeWashType = (job: any): string => {
  const raw = String(job?.wash_type || job?.service_mode || '').toLowerCase();

  switch (raw) {
    case 'doorstep':
    case 'door_to_door':
      return 'door_to_door';
    case 'pickup_drop':
    case 'pickup_wash':
      return 'pickup_wash';
    default:
      return raw;
  }
};

const isCash = (job: any): boolean => ['cash', 'cod'].includes(String(job?.payment_method || '').toLowerCase());
const payableAmount = (job: any): number => Number(job?.payable_amount || job?.total_amount || job?.final_price || job?.price || 0);

export function getWorkerAction(job: any): JobAction | null {
  if (normalizeWashType(job) !== 'door_to_door') return null;

  switch (normalizeStatus(job?.status)) {
    case 'worker_assigned':
      return { key: 'start_travel', label: 'Start Travel', api: `/worker/jobs/${job.id}/start-travel` };
    case 'worker_on_the_way':
      return { key: 'mark_arrived', label: 'I Have Arrived', api: `/worker/jobs/${job.id}/arrived`, requiresGpsCheck: true };
    case 'reached_location':
      return {
        key: 'upload_before_start',
        label: 'Upload Before Images & Start Wash',
        api: `/worker/jobs/${job.id}/start-service`,
        requiresPhotos: true,
        photoType: 'before_image',
        minPhotos: 4,
      };
    case 'service_started':
      return {
        key: 'upload_after_complete',
        label: 'Upload After Images & Complete Wash',
        api: `/worker/jobs/${job.id}/complete-service`,
        requiresPhotos: true,
        photoType: 'after_image',
        minPhotos: 4,
      };
    case 'service_completed':
      if (isCash(job) && payableAmount(job) > 0) {
        return {
          key: 'collect_cash_complete',
          label: `Collect Rs ${payableAmount(job)} Cash & Complete`,
          api: `/worker/jobs/${job.id}/collect-cash-complete`,
          requiresCashCollection: true,
        };
      }
      
      if (job?.is_subscription_booking || job?.payment_method === 'subscription') {
        return { key: 'mark_completed', label: 'Subscription Wash - Collect ₹0', api: `/worker/jobs/${job.id}/complete` };
      }

      if (job?.payment_method === 'online' || job?.payment_status === 'paid') {
        return { key: 'mark_completed', label: 'Already Paid - Complete', api: `/worker/jobs/${job.id}/complete` };
      }

      return { key: 'mark_completed', label: 'Mark as Completed', api: `/worker/jobs/${job.id}/complete` };
    default:
      return null;
  }
}

export function getPickupDriverAction(job: any): JobAction | null {
  const washType = normalizeWashType(job);
  if (washType !== 'pickup_wash' && washType !== 'pickup_drop') return null;

  switch (normalizeStatus(job?.status)) {
    case 'pickup_driver_assigned':
      return { phase: 'pickup', key: 'start_travel_to_customer', label: 'Start Travel to Customer', api: `/pickup-driver/jobs/${job.id}/start-pickup-travel` };
    case 'driver_on_the_way':
      return { phase: 'pickup', key: 'arrived_at_customer', label: 'Arrived at Customer', api: `/pickup-driver/jobs/${job.id}/arrived-customer`, requiresGpsCheck: true };
    case 'reached_location':
      return {
        phase: 'pickup',
        key: 'upload_pickup_proof',
        label: 'Upload Vehicle Condition & Pick Up Key',
        api: `/pickup-driver/jobs/${job.id}/pickup-vehicle`,
        requiresPhotos: true,
        photoType: 'pickup_proof',
        minPhotos: 4,
      };
    case 'car_picked_up':
      return { phase: 'pickup', key: 'arrived_at_partner', label: 'Arrived at Washing Center', api: `/pickup-driver/jobs/${job.id}/arrived-partner`, requiresGpsCheck: true };
    case 'reached_partner':
      return { phase: 'waiting', key: 'waiting_for_partner', label: 'Waiting for washing center to complete service', api: null };
    case 'service_completed':
      return { phase: 'delivery', key: 'start_delivery', label: 'Start Delivery to Customer', api: `/pickup-driver/jobs/${job.id}/start-delivery` };
    case 'out_for_delivery':
      return { phase: 'delivery', key: 'arrived_delivery_location', label: 'Arrived at Customer Location', api: `/pickup-driver/jobs/${job.id}/arrived-delivery`, requiresGpsCheck: true };
    case 'reached_delivery_location':
      return {
        phase: 'delivery',
        key: 'upload_delivery_proof',
        label: 'Upload Delivery Proof & Handover Keys',
        api: `/pickup-driver/jobs/${job.id}/deliver-vehicle`,
        requiresPhotos: true,
        photoType: 'delivery_proof',
        minPhotos: 4,
      };
    case 'delivered':
      if (isCash(job) && payableAmount(job) > 0) {
        return {
          phase: 'delivery',
          key: 'collect_cash_complete',
          label: `Collect Rs ${payableAmount(job)} Cash & Complete Booking`,
          api: `/pickup-driver/jobs/${job.id}/collect-cash-complete`,
          requiresCashCollection: true,
        };
      }

      if (job?.is_subscription_booking || job?.payment_method === 'subscription') {
        return { phase: 'delivery', key: 'complete_booking', label: 'Subscription Wash - Collect ₹0', api: `/pickup-driver/jobs/${job.id}/complete` };
      }

      if (job?.payment_method === 'online' || job?.payment_status === 'paid') {
        return { phase: 'delivery', key: 'complete_booking', label: 'Already Paid - Complete Booking', api: `/pickup-driver/jobs/${job.id}/complete` };
      }

      return { phase: 'delivery', key: 'complete_booking', label: 'Complete Booking', api: `/pickup-driver/jobs/${job.id}/complete` };
    default:
      return null;
  }
}

export const getDriverActions = (booking: any) => {
  const action = getPickupDriverAction(booking);
  return action ? [action] : [];
};

export const getWorkerActions = (booking: any) => {
  const action = getWorkerAction(booking);
  return action ? [action] : [];
};

export const getPartnerActions = () => [];
export const canUploadBeforeImages = (booking: any) => getWorkerAction(booking)?.photoType === 'before_image';
export const canUploadAfterImages = (booking: any) => getWorkerAction(booking)?.photoType === 'after_image';

export const getWaitingMessage = (booking: any, role: string) => {
  if (role === 'pickup_driver' && getPickupDriverAction(booking)?.phase === 'waiting') {
    return 'Vehicle handed to washing center. Waiting for service completion.';
  }
  return null;
};
