import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ThemeService } from '@core/services/theme.service';
import { I18nService, Language } from '@core/services/i18n.service';
import { AuthService } from '@core/services/auth.service';
import { ExportService } from '@core/services/export.service';
import { WeatherService } from '@core/services/weather.service';

@Component({
  selector: 'app-preferences',
  template: `
    <div class="preferences-page">
      <h1>{{ i18n.t('preferences.title') }}</h1>

      <!-- Appearance -->
      <div class="pref-card">
        <h2>{{ i18n.t('preferences.appearance') }}</h2>

        <div class="pref-row">
          <div class="pref-label">
            <span class="pref-icon">🎨</span>
            <div>
              <strong>{{ i18n.t('preferences.theme') }}</strong>
              <p>{{ (themeService.isDarkMode$ | async) ? i18n.t('preferences.darkMode') : i18n.t('preferences.lightMode') }}</p>
            </div>
          </div>
          <button class="btn-toggle" (click)="themeService.toggleTheme()">
            {{ (themeService.isDarkMode$ | async) ? '☀️' : '🌙' }}
          </button>
        </div>

        <div class="pref-row">
          <div class="pref-label">
            <span class="pref-icon">🌐</span>
            <div>
              <strong>{{ i18n.t('preferences.language') }}</strong>
              <p>{{ getCurrentLangName() }}</p>
            </div>
          </div>
          <div class="lang-buttons">
            <button
              *ngFor="let lang of i18n.getAvailableLanguages()"
              (click)="onLanguageChange(lang.code)"
              class="btn-lang"
              [class.active]="lang.code === i18n.getCurrentLang()"
            >
              {{ lang.flag }} {{ lang.name }}
            </button>
          </div>
        </div>
      </div>

      <!-- Profile -->
      <div class="pref-card">
        <h2>{{ i18n.t('preferences.profile') }}</h2>
        <form [formGroup]="profileForm" (ngSubmit)="onSaveProfile()">
          <div class="form-row">
            <div class="form-group">
              <label>{{ i18n.t('auth.name') }}</label>
              <input type="text" formControlName="name" />
            </div>
            <div class="form-group">
              <label>{{ i18n.t('auth.email') }}</label>
              <input type="email" formControlName="email" />
            </div>
          </div>
          <button type="submit" class="btn-save" [disabled]="profileForm.invalid || saving">
            {{ saving ? i18n.t('common.saving') : i18n.t('preferences.save') }}
          </button>
          <div class="success-box" *ngIf="profileSuccess">{{ i18n.t('preferences.saved') }}</div>
          <div class="error-box" *ngIf="profileError">{{ profileError }}</div>
        </form>
      </div>

      <!-- Change Password -->
      <div class="pref-card">
        <h2>{{ i18n.t('preferences.changePassword') }}</h2>
        <form [formGroup]="passwordForm" (ngSubmit)="onChangePassword()">
          <div class="form-row">
            <div class="form-group">
              <label>{{ i18n.t('preferences.currentPassword') }}</label>
              <input type="password" formControlName="currentPassword" />
            </div>
            <div class="form-group">
              <label>{{ i18n.t('preferences.newPassword') }}</label>
              <input type="password" formControlName="newPassword" />
            </div>
          </div>
          <button type="submit" class="btn-save" [disabled]="passwordForm.invalid || savingPwd">
            {{ savingPwd ? i18n.t('common.saving') : i18n.t('preferences.changePassword') }}
          </button>
          <div class="success-box" *ngIf="pwdSuccess">{{ i18n.t('preferences.saved') }}</div>
          <div class="error-box" *ngIf="pwdError">{{ pwdError }}</div>
        </form>
      </div>

      <!-- Export -->
      <div class="pref-card">
        <h2>{{ i18n.t('preferences.export') }}</h2>
        <p class="pref-desc">{{ i18n.t('preferences.exportHistory') }}</p>
        <div class="export-buttons">
          <button class="btn-export" (click)="onExportCSV()">
            📥 {{ i18n.t('preferences.exportCSV') }}
          </button>
          <button class="btn-export btn-export-pdf" (click)="onExportPDF()">
            📄 {{ i18n.t('preferences.exportPDF') }}
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .preferences-page {
      max-width: 700px;
      margin: 0 auto;
      padding: 1rem 0;
      animation: fadeIn 0.4s ease-out;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; color: var(--text-primary); margin-bottom: 1.5rem; }

    .pref-card {
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(74,144,217,0.08);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 1.25rem;
      box-shadow: 0 4px 20px rgba(26,35,50,0.06);
    }
    .pref-card h2 { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0 0 1rem 0; }

    .pref-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid rgba(74,144,217,0.06);
    }
    .pref-row:last-child { border-bottom: none; }

    .pref-label { display: flex; align-items: center; gap: 0.75rem; }
    .pref-icon { font-size: 1.5rem; }
    .pref-label strong { display: block; color: var(--text-primary); font-size: 0.9rem; }
    .pref-label p { margin: 0.15rem 0 0 0; color: var(--text-muted); font-size: 0.8rem; }

    .btn-toggle {
      width: 44px; height: 44px;
      border-radius: 12px;
      background: rgba(74,144,217,0.08);
      border: 1.5px solid rgba(74,144,217,0.12);
      font-size: 1.25rem;
      cursor: pointer;
      transition: all 250ms ease;
      display: flex; align-items: center; justify-content: center;
    }
    .btn-toggle:hover { background: rgba(74,144,217,0.14); transform: scale(1.05); }

    .lang-buttons { display: flex; gap: 0.5rem; }
    .btn-lang {
      padding: 0.5rem 0.875rem;
      border-radius: 10px;
      background: rgba(74,144,217,0.06);
      border: 1.5px solid rgba(74,144,217,0.12);
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all 250ms ease;
    }
    .btn-lang:hover { border-color: rgba(74,144,217,0.3); color: #4A90D9; }
    .btn-lang.active {
      background: rgba(74,144,217,0.12);
      border-color: #4A90D9;
      color: #4A90D9;
    }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.3rem; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; }
    .form-group input { width: 100%; padding: 0.7rem 0.875rem; border: 1.5px solid rgba(74,144,217,0.15); border-radius: 10px; font-size: 0.9rem; transition: all 250ms ease; }
    .form-group input:focus { outline: none; border-color: #4A90D9; box-shadow: 0 0 0 3px rgba(74,144,217,0.1); }

    .btn-save {
      padding: 0.7rem 1.5rem;
      background: linear-gradient(135deg, #4A90D9 0%, #67B8DE 100%);
      color: white; border: none; border-radius: 10px;
      font-weight: 700; font-size: 0.875rem;
      cursor: pointer; transition: all 300ms ease;
      box-shadow: 0 4px 14px rgba(74,144,217,0.25);
    }
    .btn-save:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(74,144,217,0.35); }
    .btn-save:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }

    .pref-desc { color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 1rem 0; }
    .export-buttons { display: flex; gap: 0.75rem; }
    .btn-export {
      padding: 0.7rem 1.25rem;
      background: rgba(255,255,255,0.9);
      border: 1.5px solid rgba(74,144,217,0.15);
      border-radius: 10px;
      color: var(--text-secondary);
      font-weight: 600; font-size: 0.875rem;
      cursor: pointer; transition: all 250ms ease;
    }
    .btn-export:hover { border-color: #4A90D9; color: #4A90D9; background: rgba(74,144,217,0.04); transform: translateY(-1px); }
    .btn-export-pdf { border-color: rgba(229,62,62,0.15); }
    .btn-export-pdf:hover { border-color: #E53E3E; color: #E53E3E; background: rgba(229,62,62,0.04); }

    .success-box { color: #38B2AC; font-size: 0.85rem; padding: 0.6rem 1rem; background: rgba(56,178,172,0.06); border: 1px solid rgba(56,178,172,0.15); border-radius: 8px; margin-top: 0.75rem; font-weight: 500; }
    .error-box { color: #E53E3E; font-size: 0.85rem; padding: 0.6rem 1rem; background: rgba(229,62,62,0.06); border: 1px solid rgba(229,62,62,0.15); border-radius: 8px; margin-top: 0.75rem; font-weight: 500; }

    @media (max-width: 640px) {
      .form-row { grid-template-columns: 1fr; }
      .lang-buttons { flex-direction: column; }
      .export-buttons { flex-direction: column; }
    }
  `]
})
export class PreferencesComponent implements OnInit {
  profileForm: FormGroup;
  passwordForm: FormGroup;
  saving = false;
  savingPwd = false;
  profileSuccess = false;
  profileError = '';
  pwdSuccess = false;
  pwdError = '';
  historyData: any[] = [];

  constructor(
    private fb: FormBuilder,
    public themeService: ThemeService,
    public i18n: I18nService,
    private authService: AuthService,
    private exportService: ExportService,
    private weatherService: WeatherService
  ) {
    this.profileForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]]
    });
    this.passwordForm = this.fb.group({
      currentPassword: ['', [Validators.required]],
      newPassword: ['', [Validators.required, Validators.minLength(8)]]
    });
  }

  ngOnInit(): void {
    this.authService.getCurrentUser().subscribe({
      next: (res) => {
        if (res.data) {
          this.profileForm.patchValue({
            name: res.data.name,
            email: res.data.email
          });
        }
      }
    });
    this.weatherService.getSearchHistory().subscribe({
      next: (res) => { if (res.success) { this.historyData = res.data; } }
    });
  }

  getCurrentLangName(): string {
    const lang = this.i18n.getAvailableLanguages().find(l => l.code === this.i18n.getCurrentLang());
    return lang ? `${lang.flag} ${lang.name}` : '';
  }

  onLanguageChange(lang: Language): void {
    this.i18n.setLanguage(lang);
    this.authService.updatePreferences(lang, this.themeService.getThemeName()).subscribe();
  }

  onSaveProfile(): void {
    if (this.profileForm.invalid) return;
    this.saving = true;
    this.profileSuccess = false;
    this.profileError = '';

    this.authService.updateProfile(this.profileForm.value).subscribe({
      next: () => { this.saving = false; this.profileSuccess = true; setTimeout(() => this.profileSuccess = false, 3000); },
      error: (err) => { this.saving = false; this.profileError = err.error?.message || this.i18n.t('common.error'); }
    });
  }

  onChangePassword(): void {
    if (this.passwordForm.invalid) return;
    this.savingPwd = true;
    this.pwdSuccess = false;
    this.pwdError = '';

    const { currentPassword, newPassword } = this.passwordForm.value;
    this.authService.changePassword(currentPassword, newPassword).subscribe({
      next: () => { this.savingPwd = false; this.pwdSuccess = true; this.passwordForm.reset(); setTimeout(() => this.pwdSuccess = false, 3000); },
      error: (err) => { this.savingPwd = false; this.pwdError = err.error?.message || this.i18n.t('common.error'); }
    });
  }

  onExportCSV(): void {
    this.exportService.exportCSV(this.historyData, 'weather_history');
  }

  onExportPDF(): void {
    this.exportService.exportPDF(this.historyData, 'weather_history');
  }
}
