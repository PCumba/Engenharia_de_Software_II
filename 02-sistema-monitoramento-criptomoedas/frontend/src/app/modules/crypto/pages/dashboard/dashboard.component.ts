import { Component, OnInit } from '@angular/core';
import { CryptoService } from '@core/services/crypto.service';

@Component({
  selector: 'app-dashboard',
  template: `
    <div class="dashboard">
      <div class="page-header"><h1>Dashboard</h1><p class="page-subtitle">Monitoramento de criptomoedas em tempo real</p></div>
      <div class="search-section">
        <div class="search-wrapper"><span class="search-icon">🔍</span><input type="text" [(ngModel)]="searchQuery" placeholder="Buscar criptomoeda..." (keyup.enter)="search()"></div>
        <button (click)="search()" [disabled]="loading" class="btn-search">{{ loading ? 'Buscando...' : 'Buscar' }}</button>
      </div>
      <div class="error-box" *ngIf="error">{{ error }}</div>
      <div class="cryptos-list" *ngIf="cryptos && cryptos.length > 0">
        <div class="crypto-card" *ngFor="let crypto of cryptos">
          <div class="crypto-info"><img [src]="crypto.image" alt="{{ crypto.name }}" class="crypto-logo"><div class="crypto-details"><h3>{{ crypto.name }} <span class="symbol">{{ crypto.symbol }}</span></h3><p class="price">{{ crypto.price | currency }}</p><p [ngClass]="{'positive': crypto.percentChange24h >= 0, 'negative': crypto.percentChange24h < 0}" class="change">24h: {{ crypto.percentChange24h | number: '1.2-2' }}%</p></div></div>
          <div class="crypto-actions"><button (click)="addToPortfolio(crypto)" class="btn-add">+ Adicionar</button><button (click)="addToFavorites(crypto)" class="btn-fav">⭐</button></div>
        </div>
      </div>
      <div class="top-cryptos" *ngIf="!searchQuery && topCryptos.length > 0">
        <h2>Top 10 Criptomoedas</h2>
        <div class="cryptos-table">
          <div class="table-header"><span>#</span><span>Nome</span><span>Preço</span><span>24h</span><span>Market Cap</span><span></span></div>
          <div class="table-row" *ngFor="let crypto of topCryptos">
            <span class="rank">{{ crypto.marketCapRank }}</span>
            <span class="name-col">{{ crypto.name }} <span class="sym">({{ crypto.symbol }})</span></span>
            <span>{{ crypto.price | currency }}</span>
            <span [ngClass]="{'positive': crypto.percentChange24h >= 0, 'negative': crypto.percentChange24h < 0}">{{ crypto.percentChange24h | number: '1.2-2' }}%</span>
            <span class="muted">{{ crypto.marketCap | currency }}</span>
            <span><button (click)="addToPortfolio(crypto)" class="btn-small">Adicionar</button></span>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .dashboard { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; }
    .page-subtitle { color: #8B949E; font-size: 0.95rem; margin: 0.25rem 0 0; }
    .search-section { display: flex; gap: 0.75rem; margin-bottom: 2rem; }
    .search-wrapper { flex: 1; max-width: 500px; position: relative; }
    .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); opacity: 0.4; }
    .search-wrapper input { width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; border: 1.5px solid rgba(48,54,61,0.8); border-radius: 14px; background: rgba(33,38,45,0.8); color: #E6EDF3; font-size: 0.9375rem; transition: all 250ms ease; }
    .search-wrapper input:focus { outline: none; border-color: #00D4AA; box-shadow: 0 0 0 3px rgba(0,212,170,0.1); }
    .search-wrapper input::placeholder { color: #6E7681; }
    .btn-search { padding: 0.85rem 1.75rem; background: linear-gradient(135deg, #00D4AA 0%, #00B894 100%); color: #0D1117; border: none; border-radius: 14px; font-weight: 700; font-size: 0.9375rem; cursor: pointer; transition: all 300ms ease; box-shadow: 0 4px 14px rgba(0,212,170,0.2); }
    .btn-search:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,212,170,0.3); }
    .btn-search:disabled { opacity: 0.5; transform: none; }
    .error-box { color: #F85149; padding: 0.875rem; background: rgba(248,81,73,0.08); border: 1px solid rgba(248,81,73,0.12); border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.9rem; }
    .cryptos-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .crypto-card { background: rgba(33,38,45,0.8); border: 1px solid rgba(48,54,61,0.6); padding: 1.25rem; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; transition: all 250ms ease; }
    .crypto-card:hover { border-color: rgba(0,212,170,0.2); background: rgba(33,38,45,0.95); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
    .crypto-info { display: flex; gap: 0.75rem; flex: 1; }
    .crypto-logo { width: 44px; height: 44px; border-radius: 50%; }
    .crypto-details h3 { margin: 0; font-size: 1rem; font-weight: 700; }
    .symbol { color: #6E7681; font-size: 0.75rem; font-weight: 500; }
    .price { margin: 0.2rem 0 0; font-weight: 700; color: #00D4AA; font-size: 0.95rem; }
    .change { margin: 0.1rem 0 0; font-size: 0.8rem; font-weight: 600; }
    .positive { color: #3FB950; }
    .negative { color: #F85149; }
    .crypto-actions { display: flex; gap: 0.4rem; }
    .btn-add { padding: 0.4rem 0.8rem; background: rgba(0,212,170,0.12); color: #00D4AA; border: 1px solid rgba(0,212,170,0.2); border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 200ms; }
    .btn-add:hover { background: rgba(0,212,170,0.2); transform: translateY(-1px); }
    .btn-fav { padding: 0.4rem 0.6rem; background: rgba(210,153,34,0.1); border: 1px solid rgba(210,153,34,0.2); border-radius: 8px; font-size: 0.85rem; cursor: pointer; transition: all 200ms; }
    .btn-fav:hover { background: rgba(210,153,34,0.2); transform: translateY(-1px); }
    .top-cryptos { margin-top: 2rem; }
    .top-cryptos h2 { font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; }
    .cryptos-table { background: rgba(33,38,45,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 16px; overflow: hidden; }
    .table-header { display: grid; grid-template-columns: 40px 1.5fr 1fr 80px 1fr 80px; gap: 0.75rem; padding: 0.875rem 1.25rem; background: rgba(22,27,34,0.6); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #8B949E; border-bottom: 1px solid rgba(48,54,61,0.6); }
    .table-row { display: grid; grid-template-columns: 40px 1.5fr 1fr 80px 1fr 80px; gap: 0.75rem; padding: 0.875rem 1.25rem; border-bottom: 1px solid rgba(48,54,61,0.3); align-items: center; font-size: 0.9rem; transition: background 200ms; }
    .table-row:hover { background: rgba(255,255,255,0.02); }
    .table-row:last-child { border-bottom: none; }
    .rank { font-weight: 700; color: #8B949E; }
    .name-col { font-weight: 600; }
    .sym { color: #6E7681; font-weight: 400; font-size: 0.8rem; }
    .muted { color: #8B949E; font-size: 0.85rem; }
    .btn-small { padding: 0.3rem 0.6rem; background: rgba(0,212,170,0.12); color: #00D4AA; border: 1px solid rgba(0,212,170,0.2); border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 200ms; }
    .btn-small:hover { background: rgba(0,212,170,0.2); }
  `]
})
export class DashboardComponent implements OnInit {
  topCryptos: any[] = []; cryptos: any[] = []; searchQuery = ''; loading = false; error = '';
  constructor(private cryptoService: CryptoService) {}
  ngOnInit(): void { this.loadTopCryptos(); }
  loadTopCryptos(): void { this.cryptoService.getTopCryptos(10).subscribe({ next: (response) => { if (response.success) { this.topCryptos = response.data; } }, error: (err) => console.error('Erro ao carregar top cryptos:', err) }); }
  search(): void {
    if (!this.searchQuery.trim()) return;
    this.loading = true; this.error = '';
    this.cryptoService.searchCrypto(this.searchQuery).subscribe({ next: (response) => { if (response.success) { this.cryptos = response.data; } this.loading = false; }, error: (err) => { this.error = err.error?.message || 'Erro na busca'; this.loading = false; } });
  }
  addToPortfolio(crypto: any): void { alert(`${crypto.name} adicionado ao portfólio!`); }
  addToFavorites(crypto: any): void { alert(`${crypto.name} adicionado aos favoritos!`); }
}
