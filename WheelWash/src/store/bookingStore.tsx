import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';
import { BookingDto, createBookingFromSelection, getBooking, listBookings, ServiceMode, trackBooking, WashType } from '@/api/bookingApi';
import { UserLocation, STORAGE_KEYS } from '@/lib/wheelwash-data';

type BookingState = {
  bookings: BookingDto[];
  currentBooking: BookingDto | null;
  tracking: unknown;
  loading: boolean;
  error: string | null;
  loadBookings: () => Promise<void>;
  loadBooking: (id: string | number) => Promise<BookingDto | null>;
  loadTracking: (id: string | number) => Promise<unknown>;
  createBooking: (input: {
    vehicleId: string | number;
    serviceId: string | number;
    serviceMode?: ServiceMode;
    washType?: WashType;
    bookingDate: string;
    bookingTime: string;
    location: UserLocation;
  }) => Promise<BookingDto>;
};

const BookingContext = createContext<BookingState | null>(null);

export function BookingProvider({ children }: { children: React.ReactNode }) {
  const [bookings, setBookings] = useState<BookingDto[]>([]);
  const [currentBooking, setCurrentBooking] = useState<BookingDto | null>(null);
  const [tracking, setTracking] = useState<unknown>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadBookings = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      setBookings(await listBookings());
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Booking load failed.');
    } finally {
      setLoading(false);
    }
  }, []);

  const loadBooking = useCallback(async (id: string | number) => {
    setLoading(true);
    setError(null);
    try {
      const booking = await getBooking(id);
      setCurrentBooking(booking);
      return booking;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Booking detail failed.');
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const loadTracking = useCallback(async (id: string | number) => {
    setLoading(true);
    setError(null);
    try {
      const result = await trackBooking(id);
      setTracking(result);
      return result;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Booking tracking failed.');
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const createBooking = useCallback(async (input: Parameters<typeof createBookingFromSelection>[0]) => {
    setLoading(true);
    setError(null);
    try {
      const booking = await createBookingFromSelection(input);
      setCurrentBooking(booking);
      setBookings((current) => [booking, ...current]);
      await AsyncStorage.multiSet([
        [STORAGE_KEYS.bookingId, String(booking.id)],
        [STORAGE_KEYS.paymentId, String(booking.latest_payment?.id || '')],
      ]);
      return booking;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Booking failed.');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  const value = useMemo(() => ({ bookings, currentBooking, tracking, loading, error, loadBookings, loadBooking, loadTracking, createBooking }), [bookings, currentBooking, tracking, loading, error, loadBookings, loadBooking, loadTracking, createBooking]);
  return <BookingContext.Provider value={value}>{children}</BookingContext.Provider>;
}

export function useBookingStore() {
  const context = useContext(BookingContext);
  if (!context) throw new Error('useBookingStore must be used inside BookingProvider');
  return context;
}
