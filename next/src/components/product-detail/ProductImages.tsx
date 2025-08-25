'use client';

import { useState } from 'react';
import Image from 'next/image';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, Expand } from 'lucide-react';

interface ProductImagesProps {
  productName: string;
  imageUrl?: string;
}

export function ProductImages({ productName, imageUrl }: ProductImagesProps) {
  const [currentImage, setCurrentImage] = useState(0);
  
  
  const images = imageUrl ? [
    { id: 1, url: imageUrl, alt: `${productName} - Main image`, isMain: true },
    { id: 2, url: imageUrl, alt: `${productName} - Side view`, isMain: false },
    { id: 3, url: imageUrl, alt: `${productName} - Back view`, isMain: false },
    { id: 4, url: imageUrl, alt: `${productName} - Detail`, isMain: false },
  ] : [
    { id: 1, url: null, alt: `${productName} - Main image`, isMain: true },
  ];

  const nextImage = () => {
    setCurrentImage((prev) => (prev + 1) % images.length);
  };

  const prevImage = () => {
    setCurrentImage((prev) => (prev - 1 + images.length) % images.length);
  };

  const handleImageClick = () => {
    
  };

  return (
    <div className="space-y-4">
      {}
      <Card className="relative overflow-hidden group">
        <div className="aspect-square bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center relative cursor-pointer overflow-hidden">
          {images[currentImage]?.url ? (
            <Image
              src={images[currentImage].url}
              alt={images[currentImage].alt}
              fill
              className="object-cover transition-transform duration-300 hover:scale-105"
              sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 50vw"
              priority
            />
          ) : (
            <div className="w-full h-full bg-gradient-to-br from-red-500/10 to-red-600/10 flex items-center justify-center">
              <div className="text-8xl font-bold text-red-600/20">
                {productName.charAt(0)}
              </div>
            </div>
          )}
          
          {}
          {images.length > 1 && (
            <>
              <Button
                variant="ghost"
                size="icon"
                className="absolute left-2 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-background/80 hover:bg-background/90"
                onClick={prevImage}
              >
                <ChevronLeft className="w-5 h-5" />
              </Button>
              
              <Button
                variant="ghost"
                size="icon"
                className="absolute right-2 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-background/80 hover:bg-background/90"
                onClick={nextImage}
              >
                <ChevronRight className="w-5 h-5" />
              </Button>
            </>
          )}

          {}
          <Button
            variant="ghost"
            size="icon"
            className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-background/80 hover:bg-background/90"
            onClick={handleImageClick}
          >
            <Expand className="w-5 h-5" />
          </Button>

          {}
          <div className="absolute bottom-2 right-2 bg-background/80 text-xs px-2 py-1 rounded">
            {currentImage + 1} / {images.length}
          </div>
        </div>
      </Card>

      {}
      {images.length > 1 && (
        <div className="grid grid-cols-4 gap-2">
          {images.map((image, index) => (
            <Card
              key={image.id}
              className={`aspect-square cursor-pointer transition-all duration-200 overflow-hidden ${
                currentImage === index 
                  ? 'ring-2 ring-red-600 shadow-md' 
                  : 'hover:shadow-md hover:ring-1 hover:ring-red-300'
              }`}
              onClick={() => setCurrentImage(index)}
            >
              <div className="w-full h-full bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center overflow-hidden relative">
                {images[index]?.url ? (
                  <Image
                    src={images[index].url}
                    alt={images[index].alt}
                    fill
                    className="object-cover transition-all duration-200"
                    sizes="(max-width: 768px) 25vw, 12.5vw"
                  />
                ) : (
                  <div className="text-2xl font-bold text-red-600/30">
                    {productName.charAt(0)}
                  </div>
                )}
              </div>
            </Card>
          ))}
        </div>
      )}

      {}
      <div className="text-center text-sm text-muted-foreground space-y-1">
        <p>📷 Click on image for 360° view</p>
        <p>🔍 Double click to zoom</p>
      </div>
    </div>
  );
}