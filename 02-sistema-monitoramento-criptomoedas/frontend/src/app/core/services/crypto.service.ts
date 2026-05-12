import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class CryptoService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  getTopCryptos(limit: number = 100): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/crypto/top`, { limit });
  }

  searchCrypto(query: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/crypto/search`, { query });
  }

  getCryptoDetails(cryptoId: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/crypto/${cryptoId}`);
  }

  getPriceHistory(cryptoId: string, days: number = 7): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/crypto/${cryptoId}/history?days=${days}`);
  }

  getPortfolio(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/portfolio`);
  }

  addToPortfolio(cryptoId: string, quantity: number, purchasePrice: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/portfolio`, { cryptoId, quantity, purchasePrice });
  }

  removeFromPortfolio(portfolioId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/portfolio/${portfolioId}`);
  }

  createPriceAlert(cryptoId: string, priceTarget: number, alertType: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/alerts`, { cryptoId, priceTarget, alertType });
  }

  getPriceAlerts(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/alerts`);
  }

  disableAlert(alertId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/alerts/${alertId}`);
  }
}
