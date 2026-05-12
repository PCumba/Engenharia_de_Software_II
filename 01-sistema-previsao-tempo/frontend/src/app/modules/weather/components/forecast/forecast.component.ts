import { Component, Input, OnInit } from '@angular/core';
import { WeatherService } from '@core/services/weather.service';

@Component({
  selector: 'app-forecast',
  template: `
    <div class="forecast-card" *ngIf="forecasts && forecasts.length > 0">
      <h3>Previsão para 5 Dias</h3>
      <div class="forecast-scroll">
        <div class="forecast-item" *ngFor="let forecast of forecasts">
          <div class="date-time">
            {{ forecast.dateTime | slice:0:10 }}<br>
            <strong>{{ forecast.dateTime | slice:11:16 }}</strong>
          </div>
          <div class="forecast-temp">{{ forecast.temperature }}°C</div>
          <div class="forecast-desc">{{ forecast.description }}</div>
          <div class="forecast-meta">
            <span>💧 {{ forecast.humidity }}%</span>
            <span>💨 {{ forecast.windSpeed }} m/s</span>
          </div>
          <div class="rain-badge" *ngIf="forecast.probability > 0">🌧️ {{ forecast.probability }}%</div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .forecast-card {
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(16px);
      padding: 1.75rem;
      border-radius: 20px;
      border: 1px solid rgba(74,144,217,0.08);
      box-shadow: 0 4px 20px rgba(26,35,50,0.06);
    }
    .forecast-card h3 { margin: 0 0 1.25rem 0; color: #1A2332; font-size: 1.1rem; font-weight: 700; }
    .forecast-scroll { display: flex; flex-direction: column; gap: 0.6rem; max-height: 420px; overflow-y: auto; }
    .forecast-item {
      display: grid; grid-template-columns: 1fr auto auto; gap: 0.75rem; align-items: center;
      padding: 0.875rem 1rem; background: rgba(74,144,217,0.05); border-radius: 12px; transition: all 250ms ease;
    }
    .forecast-item:hover { background: rgba(74,144,217,0.1); transform: translateX(2px); }
    .date-time { font-size: 0.8rem; color: #8896A6; line-height: 1.3; }
    .date-time strong { color: #4A5568; font-weight: 700; }
    .forecast-temp { font-size: 1.15rem; font-weight: 800; color: #4A90D9; }
    .forecast-desc { text-transform: capitalize; font-size: 0.8rem; color: #4A5568; font-weight: 500; }
    .forecast-meta { display: flex; gap: 0.75rem; font-size: 0.75rem; color: #8896A6; grid-column: 1 / -1; }
    .rain-badge { font-size: 0.75rem; color: #4A90D9; font-weight: 600; background: rgba(74,144,217,0.08); padding: 0.2rem 0.6rem; border-radius: 100px; width: fit-content; }
  `]
})
export class ForecastComponent implements OnInit {
  @Input() city: string = '';
  forecasts: any[] = [];
  loading = false;
  constructor(private weatherService: WeatherService) {}
  ngOnInit(): void { if (this.city) { this.loadForecast(); } }
  loadForecast(): void {
    this.loading = true;
    this.weatherService.getFiveDayForecast(this.city, 'pt').subscribe({
      next: (response) => { if (response.success) { this.forecasts = response.data.forecasts; } this.loading = false; },
      error: (err) => { console.error('Erro ao carregar previsão:', err); this.loading = false; }
    });
  }
}
