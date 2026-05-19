import { Component, OnInit } from '@angular/core';
import { WeatherService } from '@core/services/weather.service';
import { I18nService } from '@core/services/i18n.service';

@Component({
  selector: 'app-favorites',
  template: `
    <div class="favorites-page">
      <h1>{{ i18n.t('favorites.title') }}</h1>

      <div class="favorites-list" *ngIf="favorites && favorites.length > 0">
        <div class="favorite-card" *ngFor="let fav of favorites">
          <div class="favorite-info">
            <h3>{{ fav.city }}, {{ fav.country }}</h3>
          </div>
          <div class="favorite-actions">
            <button (click)="viewWeather(fav)" class="btn-view">👁️ {{ i18n.t('favorites.view') }}</button>
            <button (click)="removeFavorite(fav.id)" class="btn-remove">🗑️ {{ i18n.t('favorites.remove') }}</button>
          </div>
        </div>
      </div>

      <div class="no-favorites" *ngIf="!favorites || favorites.length === 0">
        <span class="empty-icon">⭐</span>
        <p>{{ i18n.t('favorites.empty') }}</p>
        <p class="hint">{{ i18n.t('favorites.emptyHint') }}</p>
      </div>

      <div class="current-weather" *ngIf="selectedWeather">
        <app-current-weather [weather]="selectedWeather"></app-current-weather>
      </div>
    </div>
  `,
  styles: [`
    .favorites-page { max-width: 1000px; margin: 0 auto; padding: 1rem 0; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; color: var(--text-primary); margin-bottom: 1.5rem; }
    .favorites-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .favorite-card {
      background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
      padding: 1.25rem 1.5rem; border-radius: 16px;
      border: 1px solid rgba(74,144,217,0.08);
      box-shadow: 0 4px 20px rgba(26,35,50,0.06);
      display: flex; justify-content: space-between; align-items: center;
      transition: all 250ms ease;
    }
    .favorite-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(26,35,50,0.1); border-color: rgba(74,144,217,0.15); }
    .favorite-info h3 { margin: 0; color: var(--text-primary); font-size: 1rem; font-weight: 700; }
    .favorite-actions { display: flex; gap: 0.5rem; }
    .btn-view {
      padding: 0.45rem 0.875rem; background: linear-gradient(135deg, #4A90D9, #67B8DE);
      color: white; border: none; border-radius: 8px; font-size: 0.8rem; font-weight: 600;
      cursor: pointer; transition: all 250ms ease;
    }
    .btn-view:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(74,144,217,0.3); }
    .btn-remove {
      padding: 0.45rem 0.875rem; background: rgba(229,62,62,0.08);
      color: #E53E3E; border: 1px solid rgba(229,62,62,0.15); border-radius: 8px;
      font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 250ms ease;
    }
    .btn-remove:hover { background: rgba(229,62,62,0.15); transform: translateY(-1px); }
    .no-favorites {
      text-align: center; padding: 3rem;
      background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
      border-radius: 16px; border: 1px solid rgba(74,144,217,0.08);
    }
    .empty-icon { font-size: 2.5rem; display: block; margin-bottom: 1rem; }
    .no-favorites p { color: var(--text-secondary); font-size: 1rem; margin: 0; }
    .no-favorites .hint { color: var(--text-muted); font-size: 0.875rem; margin-top: 0.5rem; }
    .current-weather { margin-top: 2rem; animation: slideUp 0.5s ease-out; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) {
      .favorites-list { grid-template-columns: 1fr; }
      .favorite-card { flex-direction: column; text-align: center; gap: 0.75rem; }
      .favorite-actions { width: 100%; }
      .favorite-actions button { flex: 1; }
    }
  `]
})
export class FavoritesComponent implements OnInit {
  favorites: any[] = [];
  selectedWeather: any = null;

  constructor(private weatherService: WeatherService, public i18n: I18nService) {}

  ngOnInit(): void { this.loadFavorites(); }

  loadFavorites(): void {
    this.weatherService.getFavorites().subscribe({
      next: (response) => { if (response.success) { this.favorites = response.data; } },
      error: (err) => { console.error('Error loading favorites:', err); }
    });
  }

  viewWeather(fav: any): void {
    this.weatherService.getCurrentWeather(fav.city, this.i18n.getCurrentLang()).subscribe({
      next: (response) => { if (response.success) { this.selectedWeather = response.data; } },
      error: (err) => { alert(err.error?.message || this.i18n.t('common.error')); }
    });
  }

  removeFavorite(id: number): void {
    if (confirm(this.i18n.t('favorites.confirmRemove'))) {
      this.weatherService.removeFavorite(id).subscribe({
        next: (response) => { if (response.success) { this.loadFavorites(); this.selectedWeather = null; } },
        error: (err) => { alert(err.error?.message || this.i18n.t('common.error')); }
      });
    }
  }
}
