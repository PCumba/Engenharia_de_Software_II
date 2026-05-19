import { Component, OnInit } from '@angular/core';
import { ExchangeRateService, ExchangeRates } from '../../../core/services/exchange-rate.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-currency-converter',
  template: `
    <mat-card class="converter-card">
      <mat-card-header>
        <mat-icon mat-card-avatar>currency_exchange</mat-icon>
        <mat-card-title>{{ i18n.t('exchange.title') }}</mat-card-title>
        <mat-card-subtitle *ngIf="lastUpdated">{{ i18n.t('exchange.lastUpdated') }}: {{ lastUpdated }}</mat-card-subtitle>
      </mat-card-header>
      <mat-card-content>
        <div class="converter-form">
          <mat-form-field appearance="outline">
            <mat-label>{{ i18n.t('exchange.amount') }}</mat-label>
            <input matInput type="number" [(ngModel)]="amount" (ngModelChange)="onConvert()" min="0" step="0.01">
          </mat-form-field>
          <div class="currency-selectors">
            <mat-form-field appearance="outline">
              <mat-label>{{ i18n.t('exchange.from') }}</mat-label>
              <mat-select [(ngModel)]="fromCurrency" (ngModelChange)="onConvert()">
                <mat-option *ngFor="let c of currencies" [value]="c.code">{{ c.code }} ({{ c.symbol }})</mat-option>
              </mat-select>
            </mat-form-field>
            <button mat-icon-button (click)="swapCurrencies()" class="swap-btn">
              <mat-icon>swap_horiz</mat-icon>
            </button>
            <mat-form-field appearance="outline">
              <mat-label>{{ i18n.t('exchange.to') }}</mat-label>
              <mat-select [(ngModel)]="toCurrency" (ngModelChange)="onConvert()">
                <mat-option *ngFor="let c of currencies" [value]="c.code">{{ c.code }} ({{ c.symbol }})</mat-option>
              </mat-select>
            </mat-form-field>
          </div>
          <div class="result" *ngIf="result !== null">
            <div class="result-value">{{ result | number:'1.2-2' }} <span class="currency-code">{{ toCurrency }}</span></div>
            <div class="rate-info">1 {{ fromCurrency }} = {{ rate | number:'1.4-4' }} {{ toCurrency }}</div>
          </div>
        </div>
        <div class="live-rates" *ngIf="rates">
          <h4>{{ i18n.t('exchange.liveRates') }} ({{ rates.base }})</h4>
          <div class="rates-grid">
            <div class="rate-item" *ngFor="let r of ratesList">
              <span class="rate-currency">{{ r.code }}</span>
              <span class="rate-value">{{ r.value | number:'1.2-4' }}</span>
            </div>
          </div>
        </div>
      </mat-card-content>
    </mat-card>
  `,
  styles: [`
    .converter-card { margin-bottom: 1.5rem; }
    mat-card-header mat-icon { font-size: 28px; color: var(--primary-color); }
    .converter-form { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem; }
    .currency-selectors { display: flex; align-items: center; gap: 0.5rem; }
    .currency-selectors mat-form-field { flex: 1; }
    .swap-btn { color: var(--primary-color); }
    .result { text-align: center; padding: 1rem; background: var(--background-color); border-radius: 8px; margin-top: 0.5rem; }
    .result-value { font-size: 1.75rem; font-weight: 700; color: var(--primary-color); }
    .currency-code { font-size: 1rem; font-weight: 400; opacity: 0.7; }
    .rate-info { font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem; }
    .live-rates { margin-top: 1.5rem; }
    .live-rates h4 { font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem; }
    .rates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.5rem; }
    .rate-item { display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--background-color); border-radius: 6px; font-size: 0.85rem; }
    .rate-currency { font-weight: 600; }
    .rate-value { color: var(--text-secondary); }
  `]
})
export class CurrencyConverterComponent implements OnInit {
  amount = 100;
  fromCurrency = 'USD';
  toCurrency = 'BRL';
  result: number | null = null;
  rate = 0;
  rates: ExchangeRates | null = null;
  ratesList: { code: string; value: number }[] = [];
  lastUpdated = '';

  currencies = [
    { code: 'USD', symbol: '$' },
    { code: 'BRL', symbol: 'R$' },
    { code: 'EUR', symbol: '€' },
    { code: 'GBP', symbol: '£' },
    { code: 'JPY', symbol: '¥' }
  ];

  constructor(
    private exchangeService: ExchangeRateService,
    public i18n: I18nService
  ) {}

  ngOnInit(): void {
    this.loadRates();
  }

  loadRates(): void {
    this.exchangeService.getRates(this.fromCurrency).subscribe(rates => {
      this.rates = rates;
      this.lastUpdated = rates.last_updated ? new Date(rates.last_updated).toLocaleDateString() : '';
      this.ratesList = Object.entries(rates.rates)
        .filter(([code]) => this.currencies.some(c => c.code === code))
        .map(([code, value]) => ({ code, value: value as number }));
      this.onConvert();
    });
  }

  onConvert(): void {
    if (!this.rates || !this.amount) { this.result = null; return; }
    this.exchangeService.convert(this.amount, this.fromCurrency, this.toCurrency).subscribe(res => {
      this.result = res.converted;
      this.rate = res.rate;
    });
  }

  swapCurrencies(): void {
    [this.fromCurrency, this.toCurrency] = [this.toCurrency, this.fromCurrency];
    this.loadRates();
  }
}
