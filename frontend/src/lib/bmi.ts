export type BmiCategory = 'underweight' | 'normal' | 'overweight' | 'obese';

export interface BmiCalculation {
  value: number;
  category: BmiCategory;
}

export type Sex = 'male' | 'female';

export type ActivityLevel = 'sedentary' | 'light' | 'moderate' | 'active' | 'very_active';
