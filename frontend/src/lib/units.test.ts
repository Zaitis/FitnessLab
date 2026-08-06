import { describe, expect, it } from 'vitest';
import {
  cmToIn,
  heightToCm,
  heightToUnit,
  inToCm,
  kgToLb,
  lbToKg,
  weightToKg,
  weightToUnit,
} from './units';

describe('units', () => {
  it('converts kg to lb and back within rounding tolerance', () => {
    const kg = 70;
    expect(kgToLb(kg)).toBeCloseTo(154.324, 2);
    expect(lbToKg(kgToLb(kg))).toBeCloseTo(kg, 6);
  });

  it('converts cm to in and back within rounding tolerance', () => {
    const cm = 180;
    expect(cmToIn(cm)).toBeCloseTo(70.866, 2);
    expect(inToCm(cmToIn(cm))).toBeCloseTo(cm, 6);
  });

  it('weightToUnit/weightToKg round-trip for both unit systems', () => {
    expect(weightToUnit(70, 'metric')).toBe(70);
    expect(weightToKg(weightToUnit(70, 'imperial'), 'imperial')).toBeCloseTo(70, 6);
  });

  it('heightToUnit/heightToCm round-trip for both unit systems', () => {
    expect(heightToUnit(180, 'metric')).toBe(180);
    expect(heightToCm(heightToUnit(180, 'imperial'), 'imperial')).toBeCloseTo(180, 6);
  });
});
