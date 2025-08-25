import { BaseService } from './BaseService';
import { BaseApiEntity } from '@/types/api';

export interface Language extends BaseApiEntity {
  name: string;
  code: string;
  native_name: string;
  is_active: boolean;
}

interface LanguageFilters {
  is_active?: boolean;
}

class LanguageService extends BaseService<Language, Language, LanguageFilters> {
  protected endpoint = 'languages';

  protected mapFromApi(apiLanguage: Language): Language {
    return apiLanguage;
  }

  protected mapToApi(language: Partial<Language>): Partial<Language> {
    return language;
  }

  async getLanguages(): Promise<Language[]> {
    const result = await this.getAll({ is_active: true });
    return result.items;
  }
}

export const languageService = new LanguageService();