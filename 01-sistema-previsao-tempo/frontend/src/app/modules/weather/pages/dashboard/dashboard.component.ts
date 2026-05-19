import { Component, OnInit } from '@angular/core';
import { WeatherService } from '@core/services/weather.service';
import { I18nService } from '@core/services/i18n.service';

@Component({
  selector: 'app-dashboard',
  template: `
    <div class="dashboard">
      <div class="page-header">
        <h1>{{ i18n.t('weather.dashboard') }}</h1>
        <p class="page-subtitle">{{ i18n.t('weather.dashboardSubtitle') }}</p>
      </div>

      <div class="search-box">
        <div class="search-input-wrapper">
          <span class="search-icon">🔍</span>
          <input
            type="text"
            [(ngModel)]="searchCity"
            [placeholder]="i18n.t('weather.searchPlaceholder')"
            (keyup.enter)="searchWeather()"
          >
        </div>
        <button (click)="searchWeather()" [disabled]="loading" class="btn-search">
          {{ loading ? i18n.t('weather.searching') : i18n.t('weather.search') }}
        </button>
      </div>

      <div class="error-box" *ngIf="error">{{ error }}</div>

      <div class="content" *ngIf="currentWeather">
        <app-current-weather [weather]="currentWeather"></app-current-weather>
        <app-forecast [city]="searchCity" *ngIf="showForecast"></app-forecast>
      </div>

      <div class="recent-searches" *ngIf="history && history.length > 0">
        <h3>{{ i18n.t('weather.recentSearches') }}</h3>
        <div class="search-chips">
          <button *ngFor="let item of history" (click)="loadFromHistory(item)" class="chip">
            <span class="chip-icon">📍</span>
            {{ item.city }}, {{ item.country }}
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .dashboard { max-width: 1200px; margin: 0 auto; padding: 1rem 0; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .page-header { margin-bottom: 2rem; }
    h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; color: var(--text-primary); margin-bottom: 0.25rem; }
    .page-subtitle { color: var(--text-muted); font-size: 0.95rem; margin: 0; }
    .search-box { display: flex; gap: 0.75rem; margin-bottom: 2rem; }
    .search-input-wrapper { flex: 1; position: relative; max-width: 500px; }
    .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1rem; opacity: 0.5; }
    .search-input-wrapper input { width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; border: 1.5px solid rgba(74, 144, 217, 0.15); border-radius: 14px; font-size: 0.9375rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); transition: all 250ms ease; box-shadow: 0 2px 8px rgba(26, 35, 50, 0.04); }
    .search-input-wrapper input:focus { outline: none; border-color: #4A90D9; box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.1), 0 4px 14px rgba(26, 35, 50, 0.06); background: white; }
    .btn-search { padding: 0.85rem 1.75rem; background: linear-gradient(135deg, #4A90D9 0%, #67B8DE 100%); color: white; border: none; border-radius: 14px; cursor: pointer; font-weight: 700; font-size: 0.9375rem; transition: all 300ms ease; box-shadow: 0 4px 14px rgba(74, 144, 217, 0.25); white-space: nowrap; }
    .btn-search:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(74, 144, 217, 0.35); }
    .btn-search:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .error-box { color: #E53E3E; text-align: center; padding: 0.875rem 1.25rem; background: rgba(229, 62, 62, 0.06); border: 1px solid rgba(229, 62, 62, 0.12); border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 500; }
    .content { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; animation: slideUp 0.5s ease-out; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .content { grid-template-columns: 1fr; } .search-box { flex-direction: column; } .search-input-wrapper { max-width: 100%; } }
    .recent-searches { margin-top: 2rem; }
    .recent-searches h3 { font-size: 1rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 1rem; }
    .search-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .chip { display: flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.9); border: 1.5px solid rgba(74, 144, 217, 0.12); border-radius: 100px; cursor: pointer; transition: all 250ms ease; font-size: 0.8125rem; font-weight: 500; color: var(--text-secondary); box-shadow: 0 1px 3px rgba(26, 35, 50, 0.04); }
    .chip:hover { border-color: #4A90D9; color: #4A90D9; background: rgba(74, 144, 217, 0.04); transform: translateY(-1px); box-shadow: 0 3px 10px rgba(74, 144, 217, 0.1); }
    .chip-icon { font-size: 0.75rem; }
  `]
})
export class DashboardComponent implements OnInit {
  searchCity = '';
  currentWeather: any = null;
  history: any[] = [];
  loading = false;
  error = '';
  showForecast = false;

  constructor(private weatherService: WeatherService, public i18n: I18nService) {}

  ngOnInit(): void { this.loadHistory(); }

  searchWeather(): void {
    if (!this.searchCity.trim()) return;
    this.loading = true; this.error = ''; this.currentWeather = null; this.showForecast = false;
    this.weatherService.getCurrentWeather(this.searchCity, this.i18n.getCurrentLang()).subscribe({
      next: (response) => { if (response.success) { this.currentWeather = response.data; this.showForecast = true; this.loadHistory(); } this.loading = false; },
      error: (err) => { this.error = err.error?.message || this.i18n.t('common.error'); this.loading = false; }
    });
  }

  loadFromHistory(item: any): void {
    this.searchCity = item.city;
    this.currentWeather = item.weather_data;
    this.showForecast = true;
  }

  loadHistory(): void {
    this.weatherService.getSearchHistory().subscribe({
      next: (response) => { if (response.success) { this.history = response.data.slice(0, 5); } },
      error: () => {}
    });
  }
}
