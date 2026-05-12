import { Component, OnInit } from '@angular/core';
import { WeatherService } from '@core/services/weather.service';

@Component({
  selector: 'app-favorites',
  template: `
    <div class="favorites-page">
      <h1>Localizações Favoritas</h1>

      <div class="favorites-list" *ngIf="favorites && favorites.length > 0">
        <div class="favorite-card" *ngFor="let fav of favorites">
          <div class="favorite-info">
            <h3>{{ fav.city }}, {{ fav.country }}</h3>
          </div>
          <div class="favorite-actions">
            <button (click)="viewWeather(fav)" class="btn-view">👁️ Ver</button>
            <button (click)="removeFavorite(fav.id)" class="btn-remove">🗑️ Remover</button>
          </div>
        </div>
      </div>

      <div class="no-favorites" *ngIf="!favorites || favorites.length === 0">
        <p>Nenhuma localização favorita ainda.</p>
        <p>Adicione localizações à medida que as consulta!</p>
      </div>

      <div class="current-weather" *ngIf="selectedWeather">
        <app-current-weather [weather]="selectedWeather"></app-current-weather>
      </div>
    </div>
  `,
  styles: [`
    .favorites-page {
      max-width: 1000px;
      margin: 0 auto;
      padding: 2rem;
    }

    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 2rem;
    }

    .favorites-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .favorite-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .favorite-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .favorite-info h3 {
      margin: 0;
      color: #333;
    }

    .favorite-actions {
      display: flex;
      gap: 0.5rem;
    }

    button {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
      transition: all 0.3s;
    }

    .btn-view {
      background: #667eea;
      color: white;
    }

    .btn-view:hover {
      background: #764ba2;
    }

    .btn-remove {
      background: #dc3545;
      color: white;
    }

    .btn-remove:hover {
      background: #c82333;
    }

    .no-favorites {
      text-align: center;
      padding: 3rem;
      background: #f8f9fa;
      border-radius: 8px;
      color: #666;
    }

    .current-weather {
      margin-top: 2rem;
    }

    @media (max-width: 768px) {
      .favorites-list {
        grid-template-columns: 1fr;
      }

      .favorite-card {
        flex-direction: column;
        text-align: center;
      }

      .favorite-actions {
        margin-top: 1rem;
        width: 100%;
      }

      button {
        flex: 1;
      }
    }
  `]
})
export class FavoritesComponent implements OnInit {
  favorites: any[] = [];
  selectedWeather: any = null;

  constructor(private weatherService: WeatherService) {}

  ngOnInit(): void {
    this.loadFavorites();
  }

  loadFavorites(): void {
    this.weatherService.getFavorites().subscribe({
      next: (response) => {
        if (response.success) {
          this.favorites = response.data;
        }
      },
      error: (err) => {
        console.error('Erro ao carregar favoritos:', err);
      }
    });
  }

  viewWeather(fav: any): void {
    this.weatherService.getCurrentWeather(fav.city, 'pt').subscribe({
      next: (response) => {
        if (response.success) {
          this.selectedWeather = response.data;
        }
      },
      error: (err) => {
        alert(err.error?.message || 'Erro ao buscar previsão');
      }
    });
  }

  removeFavorite(id: number): void {
    if (confirm('Tem certeza que deseja remover este favorito?')) {
      this.weatherService.removeFavorite(id).subscribe({
        next: (response) => {
          if (response.success) {
            this.loadFavorites();
            this.selectedWeather = null;
          }
        },
        error: (err) => {
          alert(err.error?.message || 'Erro ao remover favorito');
        }
      });
    }
  }
}
