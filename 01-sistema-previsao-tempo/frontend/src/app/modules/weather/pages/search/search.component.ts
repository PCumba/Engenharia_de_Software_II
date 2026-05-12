import { Component } from '@angular/core';
import { WeatherService } from '@core/services/weather.service';

@Component({
  selector: 'app-search',
  template: `
    <div class="search-page">
      <div class="page-header">
        <h1>Buscar Previsão</h1>
        <p class="page-subtitle">Pesquise o tempo em qualquer cidade do mundo</p>
      </div>
      <div class="search-form">
        <div class="input-group">
          <div class="input-wrapper">
            <span class="input-icon">🔍</span>
            <input type="text" [(ngModel)]="city" placeholder="Digite o nome da cidade..." (keyup.enter)="search()">
          </div>
          <button (click)="search()" [disabled]="loading" class="btn-primary">{{ loading ? 'Buscando...' : 'Buscar' }}</button>
          <button (click)="addToFavorites()" *ngIf="currentWeather" class="btn-favorite">⭐ Favoritos</button>
        </div>
        <div class="error-box" *ngIf="error">{{ error }}</div>
      </div>
      <div class="results" *ngIf="currentWeather">
        <app-current-weather [weather]="currentWeather"></app-current-weather>
        <button (click)="exportCSV()" class="btn-export">📥 Exportar CSV</button>
      </div>
      <div class="loading-state" *ngIf="loading">
        <div class="spinner"></div>
        <p>Carregando dados...</p>
      </div>
    </div>
  `,
  styles: [`
    .search-page { max-width: 900px; margin: 0 auto; padding: 1rem 0; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; color: #1A2332; margin-bottom: 0.25rem; }
    .page-subtitle { color: #8896A6; font-size: 0.95rem; margin: 0; }
    .search-form { background: rgba(255,255,255,0.9); backdrop-filter: blur(16px); padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(74,144,217,0.08); box-shadow: 0 4px 20px rgba(26,35,50,0.06); margin-bottom: 2rem; }
    .input-group { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    .input-wrapper { flex: 1; min-width: 250px; position: relative; }
    .input-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); opacity: 0.5; }
    .input-wrapper input { width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; border: 1.5px solid rgba(74,144,217,0.15); border-radius: 12px; font-size: 0.9375rem; background: rgba(255,255,255,0.8); transition: all 250ms ease; }
    .input-wrapper input:focus { outline: none; border-color: #4A90D9; box-shadow: 0 0 0 3px rgba(74,144,217,0.1); background: white; }
    .btn-primary { padding: 0.85rem 1.5rem; background: linear-gradient(135deg, #4A90D9 0%, #67B8DE 100%); color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 0.9375rem; box-shadow: 0 4px 14px rgba(74,144,217,0.25); transition: all 300ms ease; }
    .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(74,144,217,0.35); }
    .btn-primary:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .btn-favorite { padding: 0.85rem 1.25rem; background: rgba(245,166,35,0.1); color: #E08A10; border: 1.5px solid rgba(245,166,35,0.2); border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 0.875rem; transition: all 250ms ease; }
    .btn-favorite:hover { background: rgba(245,166,35,0.15); border-color: rgba(245,166,35,0.35); transform: translateY(-1px); }
    .error-box { color: #E53E3E; padding: 0.875rem 1.25rem; background: rgba(229,62,62,0.06); border: 1px solid rgba(229,62,62,0.12); border-radius: 12px; margin-top: 1rem; font-size: 0.9rem; font-weight: 500; }
    .results { margin-top: 2rem; animation: slideUp 0.5s ease-out; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    .btn-export { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 1.25rem; padding: 0.7rem 1.25rem; background: rgba(255,255,255,0.9); border: 1.5px solid rgba(74,144,217,0.15); border-radius: 12px; color: #4A5568; font-weight: 600; font-size: 0.875rem; cursor: pointer; transition: all 250ms ease; }
    .btn-export:hover { border-color: #4A90D9; color: #4A90D9; background: rgba(74,144,217,0.04); transform: translateY(-1px); }
    .loading-state { text-align: center; padding: 3rem; }
    .spinner { width: 36px; height: 36px; border: 3px solid rgba(74,144,217,0.15); border-top-color: #4A90D9; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loading-state p { color: #8896A6; font-size: 0.9rem; }
  `]
})
export class SearchComponent {
  city = '';
  currentWeather: any = null;
  loading = false;
  error = '';
  constructor(private weatherService: WeatherService) {}
  search(): void {
    if (!this.city.trim()) return;
    this.loading = true; this.error = '';
    this.weatherService.getCurrentWeather(this.city, 'pt').subscribe({
      next: (response) => { if (response.success) { this.currentWeather = response.data; } this.loading = false; },
      error: (err) => { this.error = err.error?.message || 'Erro ao buscar previsão'; this.loading = false; }
    });
  }
  addToFavorites(): void {
    if (!this.currentWeather) return;
    this.weatherService.addFavorite(this.currentWeather.city, this.currentWeather.country).subscribe({
      next: (response) => { if (response.success) { alert('Adicionado aos favoritos!'); } },
      error: (err) => { alert(err.error?.message || 'Erro ao adicionar aos favoritos'); }
    });
  }
  exportCSV(): void { this.weatherService.exportHistoryCSV(); }
}
