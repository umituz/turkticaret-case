

import { CheckCircle, Truck, Package, XCircle, AlertCircle, Clock } from 'lucide-react';


export function getOrderStatusIcon(status: string) {
  switch (status) {
    case 'delivered':
      return <CheckCircle className="h-5 w-5 text-green-600" />;
    case 'shipped':
      return <Truck className="h-5 w-5 text-blue-600" />;
    case 'processing':
      return <Package className="h-5 w-5 text-yellow-600" />;
    case 'cancelled':
      return <XCircle className="h-5 w-5 text-red-600" />;
    case 'refunded':
      return <AlertCircle className="h-5 w-5 text-purple-600" />;
    default:
      return <Clock className="h-5 w-5 text-gray-600" />;
  }
}


export function getOrderStatusColor(status: string) {
  switch (status) {
    case 'delivered':
      return 'bg-green-100 text-green-800 border-green-200';
    case 'shipped':
      return 'bg-blue-100 text-blue-800 border-blue-200';
    case 'processing':
      return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    case 'cancelled':
      return 'bg-red-100 text-red-800 border-red-200';
    case 'refunded':
      return 'bg-purple-100 text-purple-800 border-purple-200';
    case 'pending':
      return 'bg-gray-100 text-gray-800 border-gray-200';
    default:
      return 'bg-gray-100 text-gray-800 border-gray-200';
  }
}


export function getOrderStatusText(status: string): string {
  switch (status) {
    case 'pending':
      return 'Beklemede';
    case 'processing':
      return 'İşleniyor';
    case 'shipped':
      return 'Kargoya Verildi';
    case 'delivered':
      return 'Teslim Edildi';
    case 'cancelled':
      return 'İptal Edildi';
    case 'refunded':
      return 'İade Edildi';
    default:
      return status;
  }
}


export function getOrderStatusDescription(status: string): string {
  switch (status) {
    case 'pending':
      return 'Siparişiniz alındı ve onay bekleniyor';
    case 'processing':
      return 'Siparişiniz hazırlanıyor';
    case 'shipped':
      return 'Siparişiniz kargoya verildi';
    case 'delivered':
      return 'Siparişiniz teslim edildi';
    case 'cancelled':
      return 'Siparişiniz iptal edildi';
    case 'refunded':
      return 'Siparişiniz iade edildi';
    default:
      return 'Sipariş durumu güncellendi';
  }
}


export function getOrderStatusDotColor(status: string): string {
  switch (status) {
    case 'delivered':
      return 'bg-green-600';
    case 'shipped':
      return 'bg-blue-600';
    case 'processing':
      return 'bg-yellow-600';
    case 'confirmed':
      return 'bg-blue-600';
    case 'pending':
      return 'bg-yellow-600';
    case 'cancelled':
      return 'bg-red-600';
    case 'refunded':
      return 'bg-purple-600';
    default:
      return 'bg-gray-600';
  }
}


export function getPaymentStatusColor(status: string): string {
  switch (status) {
    case 'paid':
      return 'bg-green-100 text-green-800 border-green-200';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    case 'failed':
      return 'bg-red-100 text-red-800 border-red-200';
    case 'refunded':
      return 'bg-purple-100 text-purple-800 border-purple-200';
    default:
      return 'bg-gray-100 text-gray-800 border-gray-200';
  }
}


export function getOrderStatusPriority(status: string): number {
  switch (status) {
    case 'processing':
      return 1;
    case 'shipped':
      return 2;
    case 'pending':
      return 3;
    case 'delivered':
      return 4;
    case 'cancelled':
      return 5;
    case 'refunded':
      return 6;
    default:
      return 7;
  }
}