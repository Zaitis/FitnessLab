import { useSyncExternalStore } from 'react';
import type { UnitSystem } from './units';

export const UNIT_STORAGE_KEY = 'fitnesslab.unitSystem';

const listeners = new Set<() => void>();

function isUnitSystem(value: string | null): value is UnitSystem {
  return value === 'metric' || value === 'imperial';
}

function readStoredUnitSystem(): UnitSystem {
  if (typeof window === 'undefined') {
    return 'metric';
  }

  const stored = window.localStorage.getItem(UNIT_STORAGE_KEY);

  return isUnitSystem(stored) ? stored : 'metric';
}

// Module-level cache so every useUnitSystem() call in the tree observes the
// same value and re-renders together — localStorage itself only fires a
// 'storage' event for *other* tabs, not the tab that made the change.
let currentUnitSystem = readStoredUnitSystem();

export function setUnitSystem(unit: UnitSystem): void {
  currentUnitSystem = unit;
  window.localStorage.setItem(UNIT_STORAGE_KEY, unit);
  listeners.forEach((listener) => listener());
}

function subscribe(listener: () => void): () => void {
  listeners.add(listener);

  return () => listeners.delete(listener);
}

function getSnapshot(): UnitSystem {
  return currentUnitSystem;
}

function getServerSnapshot(): UnitSystem {
  return 'metric';
}

export function useUnitSystem(): [UnitSystem, (unit: UnitSystem) => void] {
  const unit = useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot);

  return [unit, setUnitSystem];
}
