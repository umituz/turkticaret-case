'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import { useCart } from '@/hooks/useCart';
import { useAppDispatch } from '@/store/hooks';
import { clearCartAPI } from '@/store/slices/cartSlice';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { getAddresses } from '@/services/addressService';
import { Address } from '@/types/user';
import { ShippingMethod, getShippingMethods } from '@/services/shippingService';
import { createOrder, CreateOrderData } from '@/services/checkoutService';
import Image from 'next/image';
import {
  ArrowLeft,
  CreditCard,
  MapPin,
  Package,
  Truck,
  CheckCircle,
  AlertCircle
} from 'lucide-react';

interface CheckoutFormData {
  email: string;
  firstName: string;
  lastName: string;
  address1: string;
  address2: string;
  city: string;
  state: string;
  postalCode: string;
  country: string;
  phone: string;
  shippingMethod: string;
  paymentMethod: string;
  cardNumber: string;
  expiryDate: string;
  cvv: string;
  cardName: string;
  saveAddress: boolean;
  notes: string;
}

export default function CheckoutPage() {
  const { items, total, itemCount } = useCart();
  const { user, isAuthenticated } = useAuth();
  const dispatch = useAppDispatch();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [shippingMethods, setShippingMethods] = useState<ShippingMethod[]>([]);
  const [selectedAddress, setSelectedAddress] = useState<string>('');
  const [useNewAddress, setUseNewAddress] = useState(true);
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [formErrors, setFormErrors] = useState<Record<string, string>>({});
  
  const [formData, setFormData] = useState<CheckoutFormData>({
    email: user?.email || 'john.doe@example.com',
    firstName: 'John',
    lastName: 'Doe',
    address1: '123 Main Street',
    address2: 'Apt 4B',
    city: 'New York',
    state: 'NY',
    postalCode: '10001',
    country: 'US',
    phone: '+1 555 123 4567',
    shippingMethod: 'standard',
    paymentMethod: 'card',
    cardNumber: '4242 4242 4242 4242',
    expiryDate: '09/25',
    cvv: '123',
    cardName: 'John Doe',
    saveAddress: false,
    notes: 'Please leave at front door'
  });

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadAddresses = useCallback(async () => {
    if (!user) return;
    
    try {
      const userAddresses = await getAddresses();
      setAddresses(userAddresses);
    } catch (error) {
      console.error('Failed to load addresses:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load addresses.',
        variant: 'destructive',
      });
    }
  }, [user]);

  const loadShippingMethods = useCallback(async () => {
    try {
      const methods = await getShippingMethods();
      setShippingMethods(methods);
    } catch (error) {
      console.error('Failed to load shipping methods:', error);
      
      setShippingMethods([
        {
          uuid: 'standard',
          name: 'Standard Shipping',
          description: 'Standard delivery',
          price: {
            raw: 0,
            formatted: 'Free',
            formatted_minus: '-Free',
            type: 'nil'
          },
          delivery_time: '5-7 business days',
          min_delivery_days: 5,
          max_delivery_days: 7,
          is_active: true,
          sort_order: 1,
          created_at: '',
          updated_at: ''
        }
      ]);
      toastRef.current({
        title: 'Warning',
        description: 'Using default shipping method.',
        variant: 'default',
      });
    }
  }, []);

  useEffect(() => {
    if (!isAuthenticated) {
      router.push('/auth/login?redirect=/checkout');
      return;
    }

    if (items.length === 0) {
      router.push('/cart');
      return;
    }

    const initializeData = async () => {
      if (user) {
        setLoadingData(true);
        try {
          await Promise.all([
            loadAddresses(),
            loadShippingMethods()
          ]);
          
          setFormData(prev => ({
            ...prev,
            email: user.email || '',
            firstName: user.name?.split(' ')[0] || '',
            lastName: user.name?.split(' ')[1] || ''
          }));
        } finally {
          setLoadingData(false);
        }
      }
    };

    initializeData();
  }, [isAuthenticated, user, items.length, router, loadAddresses, loadShippingMethods]);

  const populateAddressForm = (address: Address) => {
    setFormData(prev => ({
      ...prev,
      firstName: address.firstName,
      lastName: address.lastName,
      address1: address.address1,
      address2: address.address2 || '',
      city: address.city,
      state: address.state,
      postalCode: address.postalCode,
      country: address.country,
      phone: address.phone || ''
    }));
  };

  const handleAddressChange = (addressId: string) => {
    setSelectedAddress(addressId);
    if (addressId === 'new') {
      setUseNewAddress(true);
      setFormData(prev => ({
        ...prev,
        firstName: user?.name?.split(' ')[0] || '',
        lastName: user?.name?.split(' ')[1] || '',
        address1: '',
        address2: '',
        city: '',
        state: '',
        postalCode: '',
        phone: ''
      }));
    } else {
      setUseNewAddress(false);
      const address = addresses.find(addr => addr.uuid === addressId);
      if (address) {
        populateAddressForm(address);
      }
    }
  };


  const validateForm = (): boolean => {
    const errors: Record<string, string> = {};

    if (!formData.firstName.trim()) errors.firstName = 'First name is required';
    if (!formData.lastName.trim()) errors.lastName = 'Last name is required';
    if (!formData.address1.trim()) errors.address1 = 'Address is required';
    if (!formData.city.trim()) errors.city = 'City is required';
    if (!formData.state.trim()) errors.state = 'State is required';
    if (!formData.postalCode.trim()) errors.postalCode = 'Postal code is required';
    if (!formData.phone.trim()) errors.phone = 'Phone number is required';

    if (formData.paymentMethod === 'card') {
      if (!formData.cardNumber.trim()) errors.cardNumber = 'Card number is required';
      if (!formData.expiryDate.trim()) errors.expiryDate = 'Expiry date is required';
      if (!formData.cvv.trim()) errors.cvv = 'CVV is required';
      if (!formData.cardName.trim()) errors.cardName = 'Cardholder name is required';
    }

    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      toast({
        title: 'Please fix the errors',
        description: 'Please fill in all required fields correctly.',
        variant: 'destructive',
      });
      return;
    }

    setLoading(true);

    try {
      
      const shippingAddressParts = [
        `${formData.firstName} ${formData.lastName}`,
        formData.address1,
        formData.address2,
        formData.city,
        `${formData.state} ${formData.postalCode}`,
        formData.phone
      ].filter(Boolean);
      
      
      const orderData: CreateOrderData = {
        shipping_address: shippingAddressParts.join(', '),
        notes: formData.notes || undefined,
      };

      
      const order = await createOrder(orderData);
      
      toast({
        title: 'Order placed successfully!',
        description: `Your order ${order.order_number} has been confirmed.`,
      });

      
      await dispatch(clearCartAPI(isAuthenticated));
      router.push(`/checkout/success?order=${order.order_number}`);
      
    } catch (error) {
      console.error('Order creation failed:', error);
      toast({
        title: 'Payment failed',
        description: error instanceof Error ? error.message : 'There was an error processing your order. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  

  if (!isAuthenticated || items.length === 0) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <AlertCircle className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
          <h2 className="text-2xl font-bold mb-2">Unable to proceed</h2>
          <p className="text-muted-foreground mb-4">
            Please ensure you are logged in and have items in your cart.
          </p>
          <Button onClick={() => router.push('/cart')}>
            Go to Cart
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      {}
      <div className="mb-8">
        <Button variant="ghost" onClick={() => router.back()} className="mb-4">
          <ArrowLeft className="mr-2 h-4 w-4" />
          Back to Cart
        </Button>
        <h1 className="text-3xl font-bold">Checkout</h1>
        <p className="text-muted-foreground">Complete your order</p>
      </div>

      <form onSubmit={handleSubmit}>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {}
          <div className="lg:col-span-2 space-y-6">
            {}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <MapPin className="h-5 w-5" />
                  <span>Shipping Address</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {addresses.length > 0 && (
                  <div className="space-y-2">
                    <Label>Select Address</Label>
                    <Select value={selectedAddress} onValueChange={handleAddressChange}>
                      <SelectTrigger>
                        <SelectValue placeholder="Choose an address" />
                      </SelectTrigger>
                      <SelectContent>
                        {addresses.map((address) => (
                          <SelectItem key={address.uuid} value={address.uuid}>
                            {address.firstName} {address.lastName} - {address.address1}, {address.city}
                          </SelectItem>
                        ))}
                        <SelectItem value="new">+ Add new address</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                )}

                {(useNewAddress || addresses.length === 0) && (
                  <>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="firstName">First Name *</Label>
                        <Input
                          id="firstName"
                          value={formData.firstName}
                          onChange={(e) => setFormData(prev => ({ ...prev, firstName: e.target.value }))}
                          className={formErrors.firstName ? 'border-red-500' : ''}
                        />
                        {formErrors.firstName && <p className="text-sm text-red-600">{formErrors.firstName}</p>}
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="lastName">Last Name *</Label>
                        <Input
                          id="lastName"
                          value={formData.lastName}
                          onChange={(e) => setFormData(prev => ({ ...prev, lastName: e.target.value }))}
                          className={formErrors.lastName ? 'border-red-500' : ''}
                        />
                        {formErrors.lastName && <p className="text-sm text-red-600">{formErrors.lastName}</p>}
                      </div>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="address1">Address Line 1 *</Label>
                      <Input
                        id="address1"
                        value={formData.address1}
                        onChange={(e) => setFormData(prev => ({ ...prev, address1: e.target.value }))}
                        className={formErrors.address1 ? 'border-red-500' : ''}
                      />
                      {formErrors.address1 && <p className="text-sm text-red-600">{formErrors.address1}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="address2">Address Line 2</Label>
                      <Input
                        id="address2"
                        value={formData.address2}
                        onChange={(e) => setFormData(prev => ({ ...prev, address2: e.target.value }))}
                      />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="city">City *</Label>
                        <Input
                          id="city"
                          value={formData.city}
                          onChange={(e) => setFormData(prev => ({ ...prev, city: e.target.value }))}
                          className={formErrors.city ? 'border-red-500' : ''}
                        />
                        {formErrors.city && <p className="text-sm text-red-600">{formErrors.city}</p>}
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="state">State *</Label>
                        <Input
                          id="state"
                          value={formData.state}
                          onChange={(e) => setFormData(prev => ({ ...prev, state: e.target.value }))}
                          className={formErrors.state ? 'border-red-500' : ''}
                        />
                        {formErrors.state && <p className="text-sm text-red-600">{formErrors.state}</p>}
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="postalCode">Postal Code *</Label>
                        <Input
                          id="postalCode"
                          value={formData.postalCode}
                          onChange={(e) => setFormData(prev => ({ ...prev, postalCode: e.target.value }))}
                          className={formErrors.postalCode ? 'border-red-500' : ''}
                        />
                        {formErrors.postalCode && <p className="text-sm text-red-600">{formErrors.postalCode}</p>}
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="phone">Phone *</Label>
                        <Input
                          id="phone"
                          type="tel"
                          value={formData.phone}
                          onChange={(e) => setFormData(prev => ({ ...prev, phone: e.target.value }))}
                          className={formErrors.phone ? 'border-red-500' : ''}
                        />
                        {formErrors.phone && <p className="text-sm text-red-600">{formErrors.phone}</p>}
                      </div>
                    </div>
                  </>
                )}
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <Truck className="h-5 w-5" />
                  <span>Shipping Method</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {loadingData ? (
                  <div className="space-y-3">
                    {[...Array(3)].map((_, i) => (
                      <div key={i} className="p-4 border rounded-lg animate-pulse">
                        <div className="flex items-center justify-between">
                          <div className="space-y-2">
                            <div className="h-4 bg-gray-200 rounded w-32"></div>
                            <div className="h-3 bg-gray-200 rounded w-24"></div>
                          </div>
                          <div className="h-4 bg-gray-200 rounded w-16"></div>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  shippingMethods.map((method) => (
                    <div
                      key={method.uuid}
                      className={`p-4 border rounded-lg cursor-pointer transition-colors ${
                        formData.shippingMethod === method.uuid
                          ? 'border-red-500 bg-red-50'
                          : 'border-gray-200 hover:border-gray-300'
                      }`}
                      onClick={() => setFormData(prev => ({ ...prev, shippingMethod: method.uuid }))}
                    >
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">{method.name}</p>
                          <p className="text-sm text-muted-foreground">{method.delivery_time}</p>
                          {method.description && (
                            <p className="text-xs text-muted-foreground mt-1">{method.description}</p>
                          )}
                        </div>
                        <div className="text-right">
                          <p className="font-semibold">{method.price.formatted}</p>
                        </div>
                      </div>
                    </div>
                  ))
                )}
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <CreditCard className="h-5 w-5" />
                  <span>Payment Method</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-3">
                  <div
                    className={`p-4 border rounded-lg cursor-pointer transition-colors ${
                      formData.paymentMethod === 'card'
                        ? 'border-red-500 bg-red-50'
                        : 'border-gray-200 hover:border-gray-300'
                    }`}
                    onClick={() => setFormData(prev => ({ ...prev, paymentMethod: 'card' }))}
                  >
                    <div className="flex items-center space-x-3">
                      <CreditCard className="h-5 w-5" />
                      <span className="font-medium">Credit/Debit Card</span>
                    </div>
                  </div>
                </div>

                {formData.paymentMethod === 'card' && (
                  <div className="space-y-4 pt-4 border-t">
                    <div className="space-y-2">
                      <Label htmlFor="cardName">Cardholder Name *</Label>
                      <Input
                        id="cardName"
                        value={formData.cardName}
                        onChange={(e) => setFormData(prev => ({ ...prev, cardName: e.target.value }))}
                        className={formErrors.cardName ? 'border-red-500' : ''}
                      />
                      {formErrors.cardName && <p className="text-sm text-red-600">{formErrors.cardName}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="cardNumber">Card Number *</Label>
                      <Input
                        id="cardNumber"
                        value={formData.cardNumber}
                        onChange={(e) => setFormData(prev => ({ ...prev, cardNumber: e.target.value }))}
                        className={formErrors.cardNumber ? 'border-red-500' : ''}
                      />
                      {formErrors.cardNumber && <p className="text-sm text-red-600">{formErrors.cardNumber}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="expiryDate">Expiry Date *</Label>
                        <Input
                          id="expiryDate"
                          value={formData.expiryDate}
                          onChange={(e) => setFormData(prev => ({ ...prev, expiryDate: e.target.value }))}
                          className={formErrors.expiryDate ? 'border-red-500' : ''}
                        />
                        {formErrors.expiryDate && <p className="text-sm text-red-600">{formErrors.expiryDate}</p>}
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="cvv">CVV *</Label>
                        <Input
                          id="cvv"
                          value={formData.cvv}
                          onChange={(e) => setFormData(prev => ({ ...prev, cvv: e.target.value }))}
                          className={formErrors.cvv ? 'border-red-500' : ''}
                        />
                        {formErrors.cvv && <p className="text-sm text-red-600">{formErrors.cvv}</p>}
                      </div>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle>Order Notes (Optional)</CardTitle>
              </CardHeader>
              <CardContent>
                <Textarea
                  placeholder="Any special instructions for your order..."
                  value={formData.notes}
                  onChange={(e) => setFormData(prev => ({ ...prev, notes: e.target.value }))}
                  rows={3}
                />
              </CardContent>
            </Card>
          </div>

          {}
          <div className="lg:col-span-1">
            <Card className="sticky top-8">
              <CardHeader>
                <CardTitle>Order Summary</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {}
                <div className="space-y-3">
                  {items.map((item) => (
                    <div key={item.productUuid} className="flex items-center space-x-3">
                      <div className="w-12 h-12 bg-muted rounded flex items-center justify-center flex-shrink-0">
                        {item.image ? (
                          <Image
                            src={item.image}
                            alt={item.name}
                            width={48}
                            height={48}
                            className="w-full h-full object-cover rounded"
                          />
                        ) : (
                          <Package className="h-6 w-6 text-muted-foreground" />
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium text-sm truncate">{item.name}</p>
                        <p className="text-sm text-muted-foreground">Qty: {item.quantity}</p>
                      </div>
                      <div className="text-sm font-medium">
                        {item.total?.formatted}
                      </div>
                    </div>
                  ))}
                </div>

                <hr />

                {}
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span>Subtotal ({itemCount} items)</span>
                    <span>{total.formatted}</span>
                  </div>
                </div>

                <hr />

                <div className="flex justify-between font-semibold text-lg">
                  <span>Total</span>
                  <span>{total.formatted}</span>
                </div>


                {}
                <Button
                  type="submit"
                  className="w-full bg-red-600 hover:bg-red-700"
                  disabled={loading}
                >
                  {loading ? (
                    <>
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                      Processing...
                    </>
                  ) : (
                    <>
                      <CheckCircle className="mr-2 h-4 w-4" />
                      Place Order
                    </>
                  )}
                </Button>

                <p className="text-xs text-muted-foreground text-center">
                  By placing your order, you agree to our terms and conditions
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </div>
  );
}