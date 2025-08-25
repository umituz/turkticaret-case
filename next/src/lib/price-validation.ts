export interface PriceRange {
  min: string;
  max: string;
}

export interface PriceValidationResult {
  isValid: boolean;
  errorMessage?: string;
}

export const validatePriceRange = (priceRange: PriceRange): PriceValidationResult => {
  if (priceRange.min && priceRange.max && parseFloat(priceRange.min) > parseFloat(priceRange.max)) {
    return {
      isValid: false,
      errorMessage: 'Min price cannot be greater than max'
    };
  }

  return {
    isValid: true
  };
};

export const isPriceInputValid = (value: string): boolean => {
  return value === '' || (!isNaN(Number(value)) && Number(value) >= 0);
};