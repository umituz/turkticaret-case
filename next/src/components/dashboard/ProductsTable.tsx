import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ProductImageFallback } from '@/components/ui/product-image-fallback';

import { 
  Package, 
  MoreHorizontal, 
  Edit, 
  Eye, 
  AlertTriangle,
  CheckCircle,
  XCircle
} from 'lucide-react';
import Image from 'next/image';

interface Product {
  uuid: string;
  name: string;
  slug: string;
  description: string;
  price: number;
  image_url?: string;
  category: {
    uuid: string;
    name: string;
  };
  stock_quantity: number;
  status: 'active' | 'inactive' | 'draft';
  sales: number;
  created_at: string;
}

interface ProductsTableProps {
  products?: Product[];
  title?: string;
  description?: string;
  maxRows?: number;
}

export const ProductsTable = ({ 
  products = [], 
  title = "Product Management",
  description = "Manage your product inventory and details",
  maxRows = 5
}: ProductsTableProps) => {
  const displayedProducts = products.slice(0, maxRows);

  const getStatusConfig = (status: Product['status']) => {
    switch (status) {
      case 'active':
        return {
          color: 'bg-green-100 text-green-800',
          icon: CheckCircle
        };
      case 'inactive':
        return {
          color: 'bg-gray-100 text-gray-800',
          icon: XCircle
        };
      case 'draft':
        return {
          color: 'bg-yellow-100 text-yellow-800',
          icon: Edit
        };
      default:
        return {
          color: 'bg-gray-100 text-gray-800',
          icon: XCircle
        };
    }
  };

  const getStockStatus = (quantity: number) => {
    if (quantity === 0) {
      return { label: 'Out of Stock', color: 'bg-red-100 text-red-800', icon: XCircle };
    } else if (quantity < 10) {
      return { label: 'Low Stock', color: 'bg-yellow-100 text-yellow-800', icon: AlertTriangle };
    } else {
      return { label: 'In Stock', color: 'bg-green-100 text-green-800', icon: CheckCircle };
    }
  };


  return (
    <Card className="border-border/50">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <div>
          <CardTitle className="flex items-center gap-2">
            <Package className="h-5 w-5" />
            {title}
          </CardTitle>
          <CardDescription>{description}</CardDescription>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" size="sm">
            <Package className="h-4 w-4 mr-2" />
            Add Product
          </Button>
          <Button variant="outline" size="sm">
            View All
          </Button>
        </div>
      </CardHeader>
      <CardContent>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border/50">
                <th className="text-left py-2 px-1 text-sm font-medium text-muted-foreground">
                  Product
                </th>
                <th className="text-left py-2 px-1 text-sm font-medium text-muted-foreground">
                  Category
                </th>
                <th className="text-left py-2 px-1 text-sm font-medium text-muted-foreground">
                  Price
                </th>
                <th className="text-left py-2 px-1 text-sm font-medium text-muted-foreground">
                  Stock
                </th>
                <th className="text-left py-2 px-1 text-sm font-medium text-muted-foreground">
                  Status
                </th>
                <th className="text-left py-2 px-1 text-sm font-medium text-muted-foreground">
                  Sales
                </th>
                <th className="text-center py-2 px-1 text-sm font-medium text-muted-foreground">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              {displayedProducts.map((product) => {
                const statusConfig = getStatusConfig(product.status);
                const stockStatus = getStockStatus(product.stock_quantity);
                const StatusIcon = statusConfig.icon;
                const StockIcon = stockStatus.icon;

                return (
                  <tr key={product.uuid} className="border-b border-border/30 hover:bg-muted/50">
                    <td className="py-3 px-1">
                      <div className="flex items-center gap-3">
                        <div className="w-12 h-12 rounded-lg overflow-hidden bg-gradient-to-br from-red-50 to-red-100 flex-shrink-0">
                          {product.image_url ? (
                            <Image
                              src={product.image_url}
                              alt={product.name}
                              width={48}
                              height={48}
                              className="w-full h-full object-cover"
                            />
                          ) : (
                            <ProductImageFallback 
                              productName={product.name} 
                              size="sm" 
                              className="w-full h-full rounded-none"
                            />
                          )}
                        </div>
                        <div className="min-w-0">
                          <p className="font-medium text-sm truncate">{product.name}</p>
                          <p className="text-xs text-muted-foreground line-clamp-2">
                            {product.description}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-1">
                      <Badge variant="secondary" className="text-xs">
                        {product.category.name}
                      </Badge>
                    </td>
                    <td className="py-3 px-1">
                      <span className="text-sm font-medium">
                        -
                      </span>
                    </td>
                    <td className="py-3 px-1">
                      <div className="space-y-1">
                        <span className="text-sm font-medium">{product.stock_quantity}</span>
                        <div className="flex items-center">
                          <Badge variant="secondary" className={`text-xs ${stockStatus.color}`}>
                            <StockIcon className="h-3 w-3 mr-1" />
                            {stockStatus.label}
                          </Badge>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-1">
                      <Badge variant="secondary" className={`text-xs ${statusConfig.color}`}>
                        <StatusIcon className="h-3 w-3 mr-1" />
                        {product.status.charAt(0).toUpperCase() + product.status.slice(1)}
                      </Badge>
                    </td>
                    <td className="py-3 px-1">
                      <span className="text-sm font-medium">{product.sales}</span>
                      <div className="text-xs text-muted-foreground">units sold</div>
                    </td>
                    <td className="py-3 px-1">
                      <div className="flex items-center justify-center gap-1">
                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                          <Eye className="h-3 w-3" />
                        </Button>
                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                          <Edit className="h-3 w-3" />
                        </Button>
                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                          <MoreHorizontal className="h-3 w-3" />
                        </Button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>

        {products.length > maxRows && (
          <div className="text-center mt-4">
            <Button variant="ghost" size="sm" className="text-red-600 hover:text-red-700">
              View {products.length - maxRows} more products
            </Button>
          </div>
        )}
      </CardContent>
    </Card>
  );
};