import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of, BehaviorSubject } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

export interface ExchangeRates {
  base: string;
  rates: { [currency: string]: number };
  last_updated: string;
}

export interface ConversionResult {
  from: string;
  to: string;
  amount: number;
  rate: number;
  converted: number;
  last_updated: string;
}

@Injectable({
  providedIn: 'root'
})
export class ExchangeRateService {
  private readonly API_URL = environment.apiUrl;
  private ratesCache = new BehaviorSubject<ExchangeRates | null>(null);

  constructor(private http: HttpClient) {}

  /**
   * Obter taxas de câmbio
   */
  getRates(baseCurrency: string = 'USD'): Observable<ExchangeRates> {
    return this.http.get<any>(`${this.API_URL}/exchange-rates?base=${baseCurrency}`).pipe(
      map(response => response.data),
      tap(rates => this.ratesCache.next(rates)),
      catchError(() => {
        // Fallback: chamar API externa diretamente
        return this.http.get<any>(`https://open.er-api.com/v6/latest/${baseCurrency}`).pipe(
          map(data => ({
            base: data.base_code,
            rates: data.rates,
            last_updated: data.time_last_update_utc
          })),
          tap(rates => this.ratesCache.next(rates))
        );
      })
    );
  }

  /**
   * Converter valor entre moedas
   */
  convert(amount: number, from: string, to: string): Observable<ConversionResult> {
    return this.http.get<any>(
      `${this.API_URL}/exchange-rates/convert?amount=${amount}&from=${from}&to=${to}`
    ).pipe(
      map(response => response.data),
      catchError(() => {
        // Fallback: calcular localmente
        return this.getRates(from).pipe(
          map(rates => ({
            from,
            to,
            amount,
            rate: rates.rates[to] || 1,
            converted: Math.round(amount * (rates.rates[to] || 1) * 100) / 100,
            last_updated: rates.last_updated
          }))
        );
      })
    );
  }

  /**
   * Obter taxas em cache
   */
  getCachedRates(): ExchangeRates | null {
    return this.ratesCache.value;
  }
}
