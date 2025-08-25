export const getStockStatus = (stockQuantity: number) => ({
  isLowStock: stockQuantity < 10 && stockQuantity > 0,
  isOutOfStock: stockQuantity === 0,
  isInStock: stockQuantity > 0
});