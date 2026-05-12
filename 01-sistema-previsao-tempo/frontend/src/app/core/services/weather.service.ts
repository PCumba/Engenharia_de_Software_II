import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class WeatherService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  getCurrentWeather(city: string, language: string = 'pt'): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/weather/current`, {
      city,
      language
    });
  }

  getFiveDayForecast(city: string, language: string = 'pt'): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/weather/forecast`, {
      city,
      language
    });
  }

  getSearchHistory(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/weather/history`);
  }

  getFavorites(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/weather/favorites`);
  }

  addFavorite(city: string, country: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/weather/favorites`, {
      city,
      country
    });
  }

  removeFavorite(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/weather/favorites/remove`, {
      body: { id }
    });
  }

  exportHistoryCSV(): void {
    window.open(`${this.apiUrl}/api/weather/export/csv`, '_blank');
  }
}
