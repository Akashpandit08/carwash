export function extractCollection(payload: any): any[] {
  const candidates = [
    payload?.data?.data,
    payload?.data,
    payload,
  ];

  for (const candidate of candidates) {
    if (Array.isArray(candidate)) return candidate;
    if (Array.isArray(candidate?.data)) return candidate.data;
  }

  return [];
}

export function extractObject(payload: any): Record<string, any> {
  return payload?.data?.data || payload?.data || payload || {};
}

export function apiErrorMessage(error: any, fallback = 'Something went wrong. Please try again.'): string {
  const validation = error?.response?.data?.errors;
  if (validation && typeof validation === 'object') {
    const first = Object.values(validation).flat()[0];
    if (first) return String(first);
  }

  return error?.response?.data?.message || error?.message || fallback;
}

export function devLog(label: string, payload?: any) {
  if (__DEV__) {
    console.log(label, payload ?? '');
  }
}
