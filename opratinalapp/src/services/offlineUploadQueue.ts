import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../api/client';

const QUEUE_KEY = 'wheelwash.pendingPhotoUploads';

export type PendingPhotoUpload = {
  id: string;
  bookingId: number;
  role: 'worker' | 'pickup_driver';
  action: 'start_service' | 'complete_service' | 'pickup_vehicle' | 'deliver_vehicle';
  api: string;
  actionApi?: string;
  photoType: 'before_image' | 'after_image' | 'pickup_proof' | 'delivery_proof';
  side: 'front' | 'back' | 'left' | 'right' | 'extra';
  localUri: string;
  createdAt: string;
  retryCount: number;
};

export async function getPendingUploads(): Promise<PendingPhotoUpload[]> {
  const raw = await AsyncStorage.getItem(QUEUE_KEY);
  return raw ? JSON.parse(raw) : [];
}

async function saveQueue(items: PendingPhotoUpload[]) {
  await AsyncStorage.setItem(QUEUE_KEY, JSON.stringify(items));
}

export async function enqueuePhotoUploads(items: PendingPhotoUpload[]) {
  const current = await getPendingUploads();
  await saveQueue([...current, ...items]);
}

function createFormData(item: PendingPhotoUpload) {
  const data = new FormData();
  data.append('type', item.photoType);
  data.append('side', item.side);
  data.append('file', {
    uri: item.localUri,
    type: 'image/jpeg',
    name: `${item.photoType}_${item.side}.jpg`,
  } as any);
  return data;
}

export async function syncPendingUploads() {
  const queue = await getPendingUploads();
  if (!queue.length) return { synced: 0, remaining: 0 };

  const remaining: PendingPhotoUpload[] = [];
  const completedActions = new Map<string, string>();
  let synced = 0;

  for (const item of queue) {
    try {
      await apiClient.post(item.api, createFormData(item), {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      synced += 1;
      if (item.actionApi) {
        completedActions.set(`${item.bookingId}:${item.action}`, item.actionApi);
      }
    } catch {
      remaining.push({ ...item, retryCount: item.retryCount + 1 });
    }
  }

  for (const [key, actionApi] of completedActions) {
    const [bookingId, action] = key.split(':');
    const stillPending = remaining.some((item) => String(item.bookingId) === bookingId && item.action === action);
    if (!stillPending) {
      try {
        await apiClient.post(actionApi, {});
      } catch {
        // Status remains unchanged; the user can retry from the job screen.
      }
    }
  }

  await saveQueue(remaining);
  return { synced, remaining: remaining.length };
}
