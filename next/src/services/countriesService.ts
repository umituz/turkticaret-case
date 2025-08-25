import { BaseService } from './BaseService';

export interface Country {
  id: number;
  code: string;
  name: string;
  currency_code?: string;
  phone_code?: string;
}

class CountriesService extends BaseService<Country, Country, never> {
  protected endpoint = 'countries';

  protected mapFromApi(apiCountry: Country): Country {
    return apiCountry;
  }

  protected mapToApi(country: Partial<Country>): Partial<Country> {
    return country;
  }

  async getCountries(): Promise<Country[]> {
    const result = await this.getAll();
    return result.items;
  }
}

export const countriesService = new CountriesService();