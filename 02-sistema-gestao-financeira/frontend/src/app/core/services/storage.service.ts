import { Injectable } from '@angular/core';
import { User } from '../models/user.model';

@Injectable({
  providedIn: 'root'
})
export class StorageService {
  private readonly ACCESS_TOKEN_KEY = 'access_token';
  private readonly REFRESH_TOKEN_KEY = 'refresh_token';
  private readonly USER_KEY = 'user';
  private readonly PREFERENCES_KEY = 'preferences';

  constructor() {}

  /**
   * Armazenar token de acesso
   */
  setAccessToken(token: string): void {
    localStorage.setItem(this.ACCESS_TOKEN_KEY, token);
  }

  /**
   * Obter token de acesso
   */
  getAccessToken(): string | null {
    return localStorage.getItem(this.ACCESS_TOKEN_KEY);
  }

  /**
   * Armazenar refresh token
   */
  setRefreshToken(token: string): void {
    localStorage.setItem(this.REFRESH_TOKEN_KEY, token);
  }

  /**
   * Obter refresh token
   */
  getRefreshToken(): string | null {
    return localStorage.getItem(this.REFRESH_TOKEN_KEY);
  }

  /**
   * Armazenar dados do usuário
   */
  setUser(user: User): void {
    localStorage.setItem(this.USER_KEY, JSON.stringify(user));
  }

  /**
   * Obter dados do usuário
   */
  getUser(): User | null {
    const userData = localStorage.getItem(this.USER_KEY);
    return userData ? JSON.parse(userData) : null;
  }

  /**
   * Armazenar preferências do usuário
   */
  setPreferences(preferences: any): void {
    localStorage.setItem(this.PREFERENCES_KEY, JSON.stringify(preferences));
  }

  /**
   * Obter preferências do usuário
   */
  getPreferences(): any {
    const preferences = localStorage.getItem(this.PREFERENCES_KEY);
    return preferences ? JSON.parse(preferences) : null;
  }

  /**
   * Armazenar item genérico
   */
  setItem(key: string, value: any): void {
    try {
      const serializedValue = typeof value === 'string' ? value : JSON.stringify(value);
      localStorage.setItem(key, serializedValue);
    } catch (error) {
      console.error('Erro ao armazenar item:', error);
    }
  }

  /**
   * Obter item genérico
   */
  getItem<T>(key: string): T | null {
    try {
      const item = localStorage.getItem(key);
      if (!item) return null;

      // Tentar fazer parse JSON, se falhar retornar como string
      try {
        return JSON.parse(item);
      } catch {
        return item as unknown as T;
      }
    } catch (error) {
      console.error('Erro ao obter item:', error);
      return null;
    }
  }

  /**
   * Remover item específico
   */
  removeItem(key: string): void {
    localStorage.removeItem(key);
  }

  /**
   * Verificar se item existe
   */
  hasItem(key: string): boolean {
    return localStorage.getItem(key) !== null;
  }

  /**
   * Limpar todos os dados de autenticação
   */
  clearAuth(): void {
    localStorage.removeItem(this.ACCESS_TOKEN_KEY);
    localStorage.removeItem(this.REFRESH_TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
  }

  /**
   * Limpar todas as preferências
   */
  clearPreferences(): void {
    localStorage.removeItem(this.PREFERENCES_KEY);
  }

  /**
   * Limpar todos os dados
   */
  clearAll(): void {
    localStorage.clear();
  }

  /**
   * Obter tamanho do storage usado
   */
  getStorageSize(): number {
    let total = 0;
    for (let key in localStorage) {
      if (localStorage.hasOwnProperty(key)) {
        total += localStorage[key].length + key.length;
      }
    }
    return total;
  }

  /**
   * Verificar se o storage está disponível
   */
  isStorageAvailable(): boolean {
    try {
      const test = '__storage_test__';
      localStorage.setItem(test, test);
      localStorage.removeItem(test);
      return true;
    } catch {
      return false;
    }
  }

  /**
   * Exportar todos os dados (para backup)
   */
  exportData(): string {
    const data: { [key: string]: any } = {};
    
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key) {
        data[key] = localStorage.getItem(key);
      }
    }
    
    return JSON.stringify(data, null, 2);
  }

  /**
   * Importar dados (de backup)
   */
  importData(jsonData: string): boolean {
    try {
      const data = JSON.parse(jsonData);
      
      for (const key in data) {
        if (data.hasOwnProperty(key)) {
          localStorage.setItem(key, data[key]);
        }
      }
      
      return true;
    } catch (error) {
      console.error('Erro ao importar dados:', error);
      return false;
    }
  }

  /**
   * Migrar dados de versões antigas (se necessário)
   */
  migrateData(): void {
    // Implementar migrações de dados se necessário
    // Por exemplo, mudanças na estrutura de dados entre versões
    
    const version = this.getItem<string>('data_version');
    
    if (!version || version < '1.0.0') {
      // Executar migrações necessárias
      this.setItem('data_version', '1.0.0');
    }
  }
}