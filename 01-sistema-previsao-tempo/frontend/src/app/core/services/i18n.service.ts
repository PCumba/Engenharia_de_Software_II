import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

import ptTranslations from '../../../assets/i18n/pt.json';
import enTranslations from '../../../assets/i18n/en.json';

export type Language = 'pt' | 'en';

interface LanguageOption {
  code: Language;
  name: string;
  flag: string;
}

@Injectable({
  providedIn: 'root'
})
export class I18nService {
  private readonly LANG_KEY = 'weather_language';
  private currentLang: Language = 'pt';
  private langSubject = new BehaviorSubject<Language>('pt');
  public lang$ = this.langSubject.asObservable();

  private translations: Record<Language, any> = {
    pt: ptTranslations,
    en: enTranslations
  };

  constructor() {
    const saved = localStorage.getItem(this.LANG_KEY) as Language;
    if (saved && this.translations[saved]) {
      this.currentLang = saved;
      this.langSubject.next(saved);
    }
  }

  setLanguage(lang: Language): void {
    this.currentLang = lang;
    localStorage.setItem(this.LANG_KEY, lang);
    this.langSubject.next(lang);
  }

  getCurrentLang(): Language {
    return this.currentLang;
  }

  getAvailableLanguages(): LanguageOption[] {
    return [
      { code: 'pt', name: 'Português', flag: '🇵🇹' },
      { code: 'en', name: 'English', flag: '🇬🇧' }
    ];
  }

  t(key: string, params?: Record<string, any>): string {
    const keys = key.split('.');
    let value: any = this.translations[this.currentLang];

    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k];
      } else {
        // Fallback to Portuguese
        value = this.translations['pt'];
        for (const fk of keys) {
          if (value && typeof value === 'object' && fk in value) {
            value = value[fk];
          } else {
            return key; // Return key if not found
          }
        }
        break;
      }
    }

    if (typeof value !== 'string') return key;

    // Replace parameters like {{min}}
    if (params) {
      Object.keys(params).forEach(param => {
        value = value.replace(new RegExp(`{{${param}}}`, 'g'), params[param]);
      });
    }

    return value;
  }
}
