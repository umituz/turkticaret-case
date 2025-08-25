export interface PriceRange {
  min: string;
  max: string;
}

export interface PriceValidationResult {
  isValid: boolean;
  minPrice?: number;
  maxPrice?: number;
  errorMessage?: string;
}

export const validatePriceRange = (priceRange: PriceRange): PriceValidationResult => {
  const minPrice = priceRange.min ? parseFloat(priceRange.min) : undefined;
  const maxPrice = priceRange.max ? parseFloat(priceRange.max) : undefined;

  if (minPrice !== undefined && maxPrice !== undefined && minPrice > maxPrice) {
    return {
      isValid: false,
      errorMessage: 'Min price cannot be greater than max'
    };
  }

  return {
    isValid: true,
    minPrice,
    maxPrice
  };
};

export const isPriceInputValid = (value: string): boolean => {
  return value === '' || (!isNaN(Number(value)) && Number(value) >= 0);
};