import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-current-weather',
  template: `
    <div class="weather-card" *ngIf="weather">
      <div class="card-top">
        <div class="location">
          <h2>{{ weather.city }}, {{ weather.country }}</h2>
          <p class="timestamp">{{ weather.timestamp }}</p>
        </div>
        <div class="icon-wrapper">
          <img [src]="'https://openweathermap.org/img/wn/' + weather.icon + '@2x.png'" 
               alt="weather icon">
        </div>
      </div>

      <div class="temperature-section">
        <span class="temp-value">{{ weather.temperature }}°</span>
        <div class="temp-meta">
          <span class="description">{{ weather.description }}</span>
          <span class="feels-like">Sensação {{ weather.feelsLike }}°C</span>
        </div>
      </div>

      <div class="details-grid">
        <div class="detail">
          <span class="detail-icon">💧</span>
          <span class="detail-label">Humidade</span>
          <span class="detail-value">{{ weather.humidity }}%</span>
        </div>
        <div class="detail">
          <span class="detail-icon">🌡️</span>
          <span class="detail-label">Pressão</span>
          <span class="detail-value">{{ weather.pressure }} hPa</span>
        </div>
        <div class="detail">
          <span class="detail-icon">💨</span>
          <span class="detail-label">Vento</span>
          <span class="detail-value">{{ weather.windSpeed }} m/s</span>
        </div>
        <div class="detail">
          <span class="detail-icon">☁️</span>
          <span class="detail-label">Nebulosidade</span>
          <span class="detail-value">{{ weather.cloudiness }}%</span>
        </div>
        <div class="detail">
          <span class="detail-icon">👁️</span>
          <span class="detail-label">Visibilidade</span>
          <span class="detail-value">{{ (weather.visibility / 1000).toFixed(1) }} km</span>
        </div>
        <div class="detail">
          <span class="detail-icon">🌅</span>
          <span class="detail-label">Nascer do Sol</span>
          <span class="detail-value">{{ weather.sunrise }}</span>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .weather-card {
      background: linear-gradient(145deg, #4A90D9 0%, #3A7BD5 40%, #2E6AB3 100%);
      color: white;
      padding: 2rem;
      border-radius: 20px;
      box-shadow:
        0 12px 40px rgba(74, 144, 217, 0.3),
        0 4px 12px rgba(74, 144, 217, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.12);
      position: relative;
      overflow: hidden;
    }

    .weather-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -30%;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
      border-radius: 50%;
    }

    .card-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
      position: relative;
      z-index: 1;
    }

    .location h2 {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 700;
      color: white;
    }

    .timestamp {
      margin: 0.3rem 0 0 0;
      font-size: 0.8rem;
      opacity: 0.75;
      color: rgba(255,255,255,0.8);
    }

    .icon-wrapper img {
      width: 72px;
      height: 72px;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));
    }

    .temperature-section {
      display: flex;
      align-items: baseline;
      gap: 1rem;
      margin-bottom: 1.75rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid rgba(255,255,255,0.15);
      position: relative;
      z-index: 1;
    }

    .temp-value {
      font-size: 3.5rem;
      font-weight: 800;
      letter-spacing: -0.03em;
      line-height: 1;
    }

    .temp-meta {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }

    .description {
      font-size: 1rem;
      font-weight: 600;
      text-transform: capitalize;
    }

    .feels-like {
      font-size: 0.8rem;
      opacity: 0.75;
    }

    .details-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.75rem;
      position: relative;
      z-index: 1;
    }

    .detail {
      display: flex;
      flex-direction: column;
      align-items: center;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(4px);
      padding: 0.75rem 0.5rem;
      border-radius: 12px;
      text-align: center;
      transition: background 200ms ease;
    }

    .detail:hover {
      background: rgba(255,255,255,0.15);
    }

    .detail-icon {
      font-size: 1.1rem;
      margin-bottom: 0.3rem;
    }

    .detail-label {
      font-size: 0.7rem;
      opacity: 0.7;
      margin-bottom: 0.15rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .detail-value {
      font-size: 0.9rem;
      font-weight: 700;
    }

    @media (max-width: 768px) {
      .weather-card {
        padding: 1.5rem;
      }

      .temp-value {
        font-size: 2.75rem;
      }

      .details-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  `]
})
export class CurrentWeatherComponent {
  @Input() weather: any;
}
