const STORAGE_KEY = 'fitnesslab.pendingMeasurement';

export interface PendingMeasurement {
  weightKg: number;
  heightCm: number;
}

export function savePendingMeasurement(measurement: PendingMeasurement): void {
  window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(measurement));
}

export function readPendingMeasurement(): PendingMeasurement | null {
  const raw = window.sessionStorage.getItem(STORAGE_KEY);

  return raw ? (JSON.parse(raw) as PendingMeasurement) : null;
}

export function clearPendingMeasurement(): void {
  window.sessionStorage.removeItem(STORAGE_KEY);
}
