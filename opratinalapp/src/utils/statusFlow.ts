export const normalizeStatus = (status: string | undefined): string => {
  if (!status) return 'pending';
  const s = status.toLowerCase();
  switch (s) {
    case 'wash_started': return 'service_started';
    case 'wash_completed': return 'service_completed';
    case 'vehicle_picked_up': return 'car_picked_up';
    case 'vehicle_dropped_at_partner': return 'reached_partner';
    default: return s;
  }
};

export const getDriverActions = (booking: any) => {
  const mode = booking?.service_mode || 'partner_center';
  const status = normalizeStatus(booking?.status);

  if (mode !== 'pickup_drop') return [];

  const actions = [];
  if (['pending', 'confirmed', 'assigned_pickup_driver', 'assigned_partner', 'assigned_worker'].includes(status)) {
    actions.push({ title: 'Start Pickup', nextStatus: 'driver_on_the_way' });
  } else if (status === 'driver_on_the_way') {
    actions.push({ title: 'Mark Car Picked Up', nextStatus: 'car_picked_up' });
  } else if (status === 'car_picked_up') {
    actions.push({ title: 'Reached Partner/Garage', nextStatus: 'reached_partner' });
  } else if (status === 'ready_for_delivery') {
    actions.push({ title: 'Start Delivery', nextStatus: 'out_for_delivery' });
  } else if (status === 'out_for_delivery') {
    actions.push({ title: 'Mark Delivered', nextStatus: 'delivered' });
  }
  return actions;
};

export const getWorkerActions = (booking: any) => {
  const mode = booking?.service_mode || 'partner_center';
  const status = normalizeStatus(booking?.status);
  const actions = [];
  const isPickupDrop = mode === 'pickup_drop';
  const isDoorstep = mode === 'doorstep';
  const isPartnerCenter = mode === 'partner_center';

  if (isPickupDrop) {
    if (status === 'reached_partner' || status === 'partner_assigned') {
      actions.push({ title: 'Start Service', nextStatus: 'service_started' });
    } else if (status === 'service_started') {
      actions.push({ title: 'Complete Service', nextStatus: 'service_completed' });
    }
    // After service_completed, worker has no more actions. The Driver takes over for out_for_delivery.
  } else if (isDoorstep) {
    if (status === 'worker_assigned' || status === 'pending' || status === 'confirmed') {
      actions.push({ title: 'Start Travel', nextStatus: 'worker_on_the_way' });
    } else if (status === 'worker_on_the_way') {
      actions.push({ title: 'Start Service', nextStatus: 'service_started' });
    } else if (status === 'service_started') {
      actions.push({ title: 'Complete Service', nextStatus: 'service_completed' });
    } else if (status === 'service_completed') {
      actions.push({ title: 'Mark Completed', nextStatus: 'completed' });
    }
  } else if (isPartnerCenter) {
    if (status === 'partner_assigned' || status === 'pending' || status === 'confirmed') {
      actions.push({ title: 'Start Service', nextStatus: 'service_started' });
    } else if (status === 'service_started') {
      actions.push({ title: 'Complete Service', nextStatus: 'service_completed' });
    } else if (status === 'service_completed') {
      actions.push({ title: 'Mark Completed', nextStatus: 'completed' });
    }
  }
  return actions;
};

export const getPartnerActions = (booking: any) => {
  // Same logic as worker for simple execution, or just managing workers
  return getWorkerActions(booking);
};

export const canUploadBeforeImages = (booking: any) => {
  const actions = getWorkerActions(booking);
  return actions.some(a => a.nextStatus === 'service_started');
};

export const canUploadAfterImages = (booking: any) => {
  const actions = getWorkerActions(booking);
  return actions.some(a => a.nextStatus === 'service_completed' || a.nextStatus === 'ready_for_delivery');
};

export const getWaitingMessage = (booking: any, role: string) => {
  const mode = booking?.service_mode || 'partner_center';
  const status = normalizeStatus(booking?.status);

  if (role === 'worker' || role === 'partner') {
    if (mode === 'pickup_drop') {
      const waitStatuses = ['pending', 'confirmed', 'assigned_pickup_driver', 'assigned_partner', 'assigned_worker', 'driver_on_the_way', 'car_picked_up'];
      if (waitStatuses.includes(status)) {
        return "Waiting for driver to drop vehicle at partner/garage.";
      }
    }
  }
  return null;
};
