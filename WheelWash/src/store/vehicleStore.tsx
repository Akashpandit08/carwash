import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';
import { addVehicle, deleteVehicle, listVehicles, updateVehicle, VehicleDto, VehiclePayload } from '@/api/vehicleApi';
import { Vehicle } from '@/lib/wheelwash-data';
import { getSelectedVehicle, saveVehicle as persistSelectedVehicle } from '@/lib/wheelwash-storage';

function normalizeVehicle(vehicle: VehicleDto): Vehicle {
  return {
    id: String(vehicle.id),
    type: vehicle.vehicle_type || vehicle.type || 'car',
    brand: vehicle.brand,
    model: vehicle.model,
    registrationNumber: vehicle.registration_number || vehicle.number || '',
    color: vehicle.color || vehicle.fuel_type || 'White',
  };
}

type VehicleState = {
  vehicles: Vehicle[];
  selectedVehicle: Vehicle | null;
  loading: boolean;
  error: string | null;
  loadVehicles: () => Promise<void>;
  createVehicle: (payload: VehiclePayload) => Promise<Vehicle>;
  saveExistingVehicle: (id: string | number, payload: VehiclePayload) => Promise<Vehicle>;
  removeVehicle: (id: string | number) => Promise<void>;
  selectVehicle: (vehicle: Vehicle) => Promise<void>;
};

const VehicleContext = createContext<VehicleState | null>(null);

export function VehicleProvider({ children }: { children: React.ReactNode }) {
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [selectedVehicle, setSelectedVehicle] = useState<Vehicle | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const selectVehicle = useCallback(async (vehicle: Vehicle) => {
    await persistSelectedVehicle(vehicle);
    setSelectedVehicle(vehicle);
  }, []);

  const loadVehicles = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const [remote, stored] = await Promise.all([listVehicles(), getSelectedVehicle()]);
      const normalized = remote.map(normalizeVehicle);
      setVehicles(normalized);
      const selected = normalized.find((item) => item.id === stored?.id) || normalized[0] || stored || null;
      setSelectedVehicle(selected);
      if (selected) await persistSelectedVehicle(selected);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Vehicle load failed.');
    } finally {
      setLoading(false);
    }
  }, []);

  const createVehicle = useCallback(async (payload: VehiclePayload) => {
    setLoading(true);
    setError(null);
    try {
      const created = normalizeVehicle(await addVehicle(payload));
      setVehicles((current) => [created, ...current.filter((item) => item.id !== created.id)]);
      await selectVehicle(created);
      return created;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Vehicle add failed.');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [selectVehicle]);

  const saveExistingVehicle = useCallback(async (id: string | number, payload: VehiclePayload) => {
    setLoading(true);
    setError(null);
    try {
      const updated = normalizeVehicle(await updateVehicle(id, payload));
      setVehicles((current) => current.map((item) => (item.id === String(id) ? updated : item)));
      await selectVehicle(updated);
      return updated;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Vehicle update failed.');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [selectVehicle]);

  const removeVehicle = useCallback(async (id: string | number) => {
    setLoading(true);
    setError(null);
    try {
      await deleteVehicle(id);
      setVehicles((current) => current.filter((item) => item.id !== String(id)));
      if (selectedVehicle?.id === String(id)) setSelectedVehicle(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Vehicle delete failed.');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [selectedVehicle?.id]);

  const value = useMemo(() => ({ vehicles, selectedVehicle, loading, error, loadVehicles, createVehicle, saveExistingVehicle, removeVehicle, selectVehicle }), [vehicles, selectedVehicle, loading, error, loadVehicles, createVehicle, saveExistingVehicle, removeVehicle, selectVehicle]);
  return <VehicleContext.Provider value={value}>{children}</VehicleContext.Provider>;
}

export function useVehicleStore() {
  const context = useContext(VehicleContext);
  if (!context) throw new Error('useVehicleStore must be used inside VehicleProvider');
  return context;
}
