interface ProductImageFallbackProps {
  productName: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  className?: string;
}

export function ProductImageFallback({ 
  productName, 
  size = 'md',
  className = '' 
}: ProductImageFallbackProps) {
  const sizeClasses = {
    sm: 'w-12 h-12 text-sm',
    md: 'w-16 h-16 text-lg', 
    lg: 'w-24 h-24 text-2xl',
    xl: 'w-32 h-32 text-4xl'
  };

  const fallbackText = productName.charAt(0).toUpperCase();

  return (
    <div className={`
      ${sizeClasses[size]} 
      bg-gradient-to-br from-red-600 to-red-700 
      rounded-lg 
      flex items-center justify-center 
      text-white font-bold 
      ${className}
    `}>
      {fallbackText}
    </div>
  );
}