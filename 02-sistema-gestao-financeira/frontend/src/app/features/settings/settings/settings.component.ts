import { Component } from '@angular/core';
import { ThemeService } from '../../../core/services/theme.service';
import { I18nService, Language } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-settings',
  template: `
    <div class="page-container animate-fade-in">
      <h1>{{ i18n.t('settings.title') }}</h1>
      <div class="settings-grid">
        <mat-card class="settings-card">
          <mat-card-header><mat-card-title>{{ i18n.t('settings.appearance') }}</mat-card-title></mat-card-header>
          <mat-card-content>
            <div class="setting-item">
              <div class="setting-label">
                <mat-icon>{{ themeService.isDarkMode ? 'dark_mode' : 'light_mode' }}</mat-icon>
                <span>{{ themeService.isDarkMode ? i18n.t('settings.darkMode') : i18n.t('settings.lightMode') }}</span>
              </div>
              <mat-slide-toggle [checked]="themeService.isDarkMode" (change)="themeService.toggleTheme()"></mat-slide-toggle>
            </div>
            <mat-divider></mat-divider>
            <div class="setting-item">
              <div class="setting-label">
                <mat-icon>language</mat-icon>
                <span>{{ i18n.t('settings.language') }}</span>
              </div>
              <mat-select [value]="i18n.getCurrentLang()" (selectionChange)="onLanguageChange($event.value)" class="lang-select">
                <mat-option *ngFor="let lang of i18n.getAvailableLanguages()" [value]="lang.code">
                  {{ lang.flag }} {{ lang.name }}
                </mat-option>
              </mat-select>
            </div>
          </mat-card-content>
        </mat-card>
        <mat-card class="settings-card">
          <mat-card-header><mat-card-title>{{ i18n.t('settings.profile') }}</mat-card-title></mat-card-header>
          <mat-card-content>
            <a mat-stroked-button routerLink="/settings/profile" class="full-width">
              <mat-icon>person</mat-icon> {{ i18n.t('settings.profile') }}
            </a>
            <a mat-stroked-button routerLink="/settings/security" class="full-width" style="margin-top: 0.5rem;">
              <mat-icon>security</mat-icon> {{ i18n.t('settings.security') }}
            </a>
          </mat-card-content>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .page-container { padding: 1.5rem; max-width: 800px; margin: 0 auto; }
    h1 { font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem; }
    .settings-grid { display: grid; gap: 1rem; }
    .settings-card { border-radius: var(--border-radius-lg); }
    .setting-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; }
    .setting-label { display: flex; align-items: center; gap: 0.75rem; font-size: 0.95rem; }
    .lang-select { width: 150px; }
  `]
})
export class SettingsComponent {
  constructor(public themeService: ThemeService, public i18n: I18nService) {}

  onLanguageChange(lang: Language): void {
    this.i18n.setLanguage(lang);
  }
}
