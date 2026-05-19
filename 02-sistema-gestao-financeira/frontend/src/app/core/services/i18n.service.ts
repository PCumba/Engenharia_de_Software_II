import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { StorageService } from './storage.service';

import ptTranslations from '../../../../assets/i18n/pt.json';
import enTranslations from '../../../../assets/i18n/en.json';

export type Language = 'pt' | 'en';

@Injectable({
  providedIn: 'root'
})
export class I18nService {
  private currentLangSubject = new BehaviorSubject<Language>('pt');
  public currentLang$: Observable<Language> = this.currentLangSubject.asObservable();

  private translations: { [key: string]: any } = {
    'pt': ptTranslations,
    'en': enTranslations
  };

  constructor(private storageService: StorageService) {
    this.initializeLanguage();
  }

  /**
   * Inicializar idioma a partir do storage ou navegador
   */
  private initializeLanguage(): void {
    const saved = this.storageService.getItem<string>('language');
    if (saved && (saved === 'pt' || saved === 'en')) {
      this.currentLangSubject.next(saved as Language);
    } else {
      const browserLang = navigator.language.substring(0, 2);
      const lang: Language = browserLang === 'pt' ? 'pt' : 'en';
      this.currentLangSubject.next(lang);
    }
  }

  /**
   * Alterar idioma
   */
  setLanguage(lang: Language): void {
    this.currentLangSubject.next(lang);
    this.storageService.setItem('language', lang);
  }

  /**
   * Obter idioma atual
   */
  getCurrentLang(): Language {
    return this.currentLangSubject.value;
  }

  /**
   * Traduzir chave (suporta chaves aninhadas com ponto: "auth.login")
   */
  t(key: string, params?: { [key: string]: any }): string {
    const lang = this.currentLangSubject.value;
    const keys = key.split('.');
    let result: any = this.translations[lang];

    for (const k of keys) {
      if (result && typeof result === 'object' && k in result) {
        result = result[k];
      } else {
        // Fallback para português
        result = this.getNestedValue(this.translations['pt'], keys);
        break;
      }
    }

    if (typeof result !== 'string') {
      return key; // Retornar chave se tradução não encontrada
    }

    // Substituir parâmetros: {{param}}
    if (params) {
      Object.keys(params).forEach(param => {
        result = result.replace(new RegExp(`{{\\s*${param}\\s*}}`, 'g'), params[param]);
      });
    }

    return result;
  }

  /**
   * Obter valor aninhado de um objeto
   */
  private getNestedValue(obj: any, keys: string[]): any {
    let current = obj;
    for (const k of keys) {
      if (current && typeof current === 'object' && k in current) {
        current = current[k];
      } else {
        return undefined;
      }
    }
    return current;
  }

  /**
   * Obter idiomas disponíveis
   */
  getAvailableLanguages(): { code: Language; name: string; flag: string }[] {
    return [
      { code: 'pt', name: 'Português', flag: '🇧🇷' },
      { code: 'en', name: 'English', flag: '🇬🇧' }
    ];
  }

  /**
   * Alternar entre idiomas
   */
  toggleLanguage(): void {
    const newLang: Language = this.currentLangSubject.value === 'pt' ? 'en' : 'pt';
    this.setLanguage(newLang);
  }
}
