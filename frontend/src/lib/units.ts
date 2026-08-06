export type UnitSystem = 'metric' | 'imperial';

const KG_PER_LB = 0.45359237;
const CM_PER_IN = 2.54;

export function kgToLb(kg: number): number {
  return kg / KG_PER_LB;
}

export function lbToKg(lb: number): number {
  return lb * KG_PER_LB;
}

export function cmToIn(cm: number): number {
  return cm / CM_PER_IN;
}

export function inToCm(inches: number): number {
  return inches * CM_PER_IN;
}

export function weightToUnit(weightKg: number, unit: UnitSystem): number {
  return unit === 'imperial' ? kgToLb(weightKg) : weightKg;
}

export function weightToKg(weight: number, unit: UnitSystem): number {
  return unit === 'imperial' ? lbToKg(weight) : weight;
}

export function heightToUnit(heightCm: number, unit: UnitSystem): number {
  return unit === 'imperial' ? cmToIn(heightCm) : heightCm;
}

export function heightToCm(height: number, unit: UnitSystem): number {
  return unit === 'imperial' ? inToCm(height) : height;
}
