import { useEffect, useState } from 'react';
import apiClient from '../api/client';

export function useNetworkStatus() {
  const [isOnline, setIsOnline] = useState(true);

  useEffect(() => {
    let mounted = true;

    const check = async () => {
      try {
        await apiClient.get('/auth/me');
        if (mounted) setIsOnline(true);
      } catch (error: any) {
        if (!error?.response && mounted) setIsOnline(false);
      }
    };

    check();
    const timer = setInterval(check, 30000);

    return () => {
      mounted = false;
      clearInterval(timer);
    };
  }, []);

  return isOnline;
}
