import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { StorageService } from './storage.service';

@Injectable({
  providedIn: 'root'
})
export class ThemeService {
  private isDarkModeSubject = new BehaviorSubject<boolean>(false);
  public isDarkMode$: Observable<boolean> = this.isDarkModeSubject.asObservable();

  constructor(private storageService: StorageService) {}

  initializeTheme(): void {
    const savedTheme = this.storageService.getItem<string>('theme');

    if (savedTheme) {
      this.setDarkMode(savedTheme === 'dark');
    } else {
      // Usar preferência do sistema
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      this.setDarkMode(prefersDark);
    }

    // Escutar mudanças na preferência do sistema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
      if (!this.storageService.getItem<string>('theme')) {
        this.setDarkMode(e.matches);
      }
    });
  }

  toggleTheme(): void {
    const newValue = !this.isDarkModeSubject.value;
    this.setDarkMode(newValue);
    this.storageService.setItem('theme', newValue ? 'dark' : 'light');
  }

  setDarkMode(isDark: boolean): void {
    this.isDarkModeSubject.next(isDark);

    if (isDark) {
      document.body.classList.add('dark-theme');
    } else {
      document.body.classList.remove('dark-theme');
    }
  }

  isDarkMode(): boolean {
    return this.isDarkModeSubject.value;
  }
}