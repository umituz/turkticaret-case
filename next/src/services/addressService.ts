import { BaseService } from './BaseService';
import { Address as UIAddress, AddressFormData } from '@/types/user';

export interface Address {
  uuid: string;
  type: 'shipping' | 'billing';
  is_default: boolean;
  first_name: string;
  last_name: string;
  company?: string;
  address_line_1: string;
  address_line_2?: string;
  city: string;
  state: string;
  postal_code: string;
  country_uuid: string;
  country?: {
    uuid: string;
    name: string;
    iso_code: string;
  };
  phone?: string;
  created_at: string;
  updated_at: string;
}

export interface CreateAddressData {
  type: 'shipping' | 'billing';
  is_default?: boolean;
  first_name: string;
  last_name: string;
  company?: string;
  address_line_1: string;
  address_line_2?: string;
  city: string;
  state: string;
  postal_code: string;
  country_uuid: string;
  phone?: string;
}

class AddressService extends BaseService<UIAddress, Address> {
  protected endpoint = 'addresses';

  protected mapFromApi(apiAddress: Address): UIAddress {
    return {
      uuid: apiAddress.uuid,
      userUuid: apiAddress.uuid, 
      type: apiAddress.type,
      isDefault: apiAddress.is_default,
      firstName: apiAddress.first_name,
      lastName: apiAddress.last_name,
      company: apiAddress.company,
      address1: apiAddress.address_line_1,
      address2: apiAddress.address_line_2,
      city: apiAddress.city,
      state: apiAddress.state,
      postalCode: apiAddress.postal_code,
      country: apiAddress.country?.name || 'US',
      phone: apiAddress.phone,
      createdAt: apiAddress.created_at,
      updatedAt: apiAddress.updated_at,
    };
  }

  protected mapToApi(uiAddress: Partial<UIAddress> | AddressFormData): Partial<Address> {
    const isFormData = 'isDefault' in uiAddress;
    
    if (isFormData) {
      const formData = uiAddress as AddressFormData;
      return {
        type: formData.type,
        is_default: formData.isDefault,
        first_name: formData.firstName,
        last_name: formData.lastName,
        company: formData.company,
        address_line_1: formData.address1,
        address_line_2: formData.address2,
        city: formData.city,
        state: formData.state,
        postal_code: formData.postalCode,
        country_uuid: formData.country === 'US' ? '1' : '2', 
        phone: formData.phone,
      };
    } else {
      const uiAddr = uiAddress as Partial<UIAddress>;
      return {
        type: uiAddr.type,
        is_default: uiAddr.isDefault,
        first_name: uiAddr.firstName,
        last_name: uiAddr.lastName,
        company: uiAddr.company,
        address_line_1: uiAddr.address1,
        address_line_2: uiAddr.address2,
        city: uiAddr.city,
        state: uiAddr.state,
        postal_code: uiAddr.postalCode,
        country_uuid: uiAddr.country === 'US' ? '1' : '2',
        phone: uiAddr.phone,
      };
    }
  }

  async getAddresses(): Promise<UIAddress[]> {
    const result = await this.getAll();
    return result.items;
  }

  async getAddress(uuid: string): Promise<UIAddress> {
    const address = await this.getByUuid(uuid);
    if (!address) {
      throw new Error('Address not found');
    }
    return address;
  }

  async createAddress(data: AddressFormData): Promise<UIAddress> {
    return this.create(data);
  }

  async updateAddress(uuid: string, data: Partial<AddressFormData>): Promise<UIAddress> {
    return this.update(uuid, data);
  }

  async deleteAddress(uuid: string): Promise<void> {
    return this.delete(uuid);
  }
}

export const addressService = new AddressService();


export const getAddresses = () => addressService.getAddresses();
export const getAddress = (uuid: string) => addressService.getAddress(uuid);
export const createAddress = (data: AddressFormData) => addressService.createAddress(data);
export const updateAddress = (uuid: string, data: Partial<AddressFormData>) => addressService.updateAddress(uuid, data);
export const deleteAddress = (uuid: string) => addressService.deleteAddress(uuid);