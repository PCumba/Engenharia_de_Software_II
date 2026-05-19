import { Component } from '@angular/core';
import { AuthService } from '@core/services/auth.service';
import { Router } from '@angular/router';
import { ThemeService } from '@core/services/theme.service';
import { I18nService, Language } from '@core/services/i18n.service';

@Component({
  selector: 'app-header',
  template: `
    <header class="header">
      <div class="container">
        <div class="logo">
          <span class="logo-icon">☁️</span>
          <span class="logo-text">{{ i18n.t('app.title') }}</span>
        </div>
        <nav class="nav">
          <a routerLink="/weather/dashboard" routerLinkActive="active">
            <span class="nav-icon">📊</span>
            {{ i18n.t('nav.dashboard') }}
          </a>
          <a routerLink="/weather/search" routerLinkActive="active">
            <span class="nav-icon">🔍</span>
            {{ i18n.t('nav.search') }}
          </a>
          <a routerLink="/weather/favorites" routerLinkActive="active">
            <span class="nav-icon">⭐</span>
            {{ i18n.t('nav.favorites') }}
          </a>
          <a routerLink="/weather/preferences" routerLinkActive="active">
            <span class="nav-icon">⚙️</span>
            {{ i18n.t('nav.preferences') }}
          </a>

          <div class="header-actions">
            <!-- Language quick toggle -->
            <button class="btn-lang-toggle" (click)="toggleLanguage()" [title]="i18n.t('preferences.language')">
              {{ i18n.getCurrentLang() === 'pt' ? '🇵🇹' : '🇬🇧' }}
            </button>

            <!-- Theme toggle -->
            <button class="btn-theme" (click)="themeService.toggleTheme()" [title]="(themeService.isDarkMode$ | async) ? i18n.t('preferences.lightMode') : i18n.t('preferences.darkMode')">
              {{ (themeService.isDarkMode$ | async) ? '☀️' : '🌙' }}
            </button>

            <button (click)="logout()" class="btn-logout">
              {{ i18n.t('nav.logout') }}
            </button>
          </div>
        </nav>
      </div>
    </header>
  `,
  styles: [`
    .header {
      background: rgba(255, 255, 255, 0.82);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-bottom: 1px solid rgba(74, 144, 217, 0.1);
      padding: 0;
      position: sticky;
      top: 0;
      z-index: 100;
      transition: all 250ms ease;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 1.5rem;
    }

    .logo { display: flex; align-items: center; gap: 0.5rem; }
    .logo-icon { font-size: 1.5rem; line-height: 1; }
    .logo-text {
      font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em;
      background: linear-gradient(135deg, #4A90D9 0%, #2E6AB3 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }

    .nav { display: flex; gap: 0.25rem; align-items: center; }
    .nav a {
      display: flex; align-items: center; gap: 0.4rem;
      color: #4A5568; text-decoration: none; font-size: 0.875rem; font-weight: 500;
      padding: 0.5rem 0.875rem; border-radius: 10px;
      transition: all 250ms cubic-bezier(0.4, 0, 0.2, 1);
    }
    .nav-icon { font-size: 0.9rem; line-height: 1; }
    .nav a:hover { color: #4A90D9; background: rgba(74, 144, 217, 0.08); }
    .nav a.active { color: #4A90D9; background: rgba(74, 144, 217, 0.1); font-weight: 600; }

    .header-actions { display: flex; align-items: center; gap: 0.25rem; margin-left: 0.5rem; }

    .btn-lang-toggle, .btn-theme {
      width: 36px; height: 36px;
      border-radius: 10px;
      background: rgba(74, 144, 217, 0.06);
      border: 1px solid rgba(74, 144, 217, 0.1);
      font-size: 1rem;
      cursor: pointer;
      transition: all 250ms ease;
      display: flex; align-items: center; justify-content: center;
    }
    .btn-lang-toggle:hover, .btn-theme:hover {
      background: rgba(74, 144, 217, 0.12);
      transform: scale(1.05);
    }

    .btn-logout {
      margin-left: 0.5rem;
      padding: 0.5rem 1.1rem;
      background: transparent;
      color: #4A5568;
      border: 1.5px solid rgba(74, 144, 217, 0.2);
      border-radius: 10px;
      font-size: 0.8125rem; font-weight: 600;
      cursor: pointer;
      transition: all 250ms ease;
    }
    .btn-logout:hover { color: #E53E3E; border-color: rgba(229, 62, 62, 0.3); background: rgba(229, 62, 62, 0.05); transform: none; }

    @media (max-width: 768px) {
      .nav { gap: 0.125rem; }
      .nav a { padding: 0.4rem 0.6rem; font-size: 0.8rem; }
      .nav-icon { display: none; }
      .btn-lang-toggle, .btn-theme { width: 32px; height: 32px; font-size: 0.85rem; }
    }
  `]
})
export class HeaderComponent {
  constructor(
    private authService: AuthService,
    private router: Router,
    public themeService: ThemeService,
    public i18n: I18nService
  ) {}

  logout(): void {
    this.authService.logout();
    this.router.navigate(['/auth/login']);
  }

  toggleLanguage(): void {
    const newLang: Language = this.i18n.getCurrentLang() === 'pt' ? 'en' : 'pt';
    this.i18n.setLanguage(newLang);
  }
}
