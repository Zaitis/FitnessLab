export type BmiCategory = 'underweight' | 'normal' | 'overweight' | 'obese';

export interface BmiCalculation {
  value: number;
  category: BmiCategory;
}
