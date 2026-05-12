import { Component, OnInit } from '@angular/core';
import { CryptoService } from '@core/services/crypto.service';

@Component({
  selector: 'app-portfolio',
  template: `
    <div class="portfolio">
      <div class="page-header"><h1>Portfólio</h1><p class="page-subtitle">Acompanhe os seus investimentos</p></div>
      <div class="summary" *ngIf="portfolio.length > 0">
        <div class="summary-card"><p class="label">Valor Total</p><p class="amount">{{ getTotalValue() | currency }}</p></div>
        <div class="summary-card"><p class="label">Investimento</p><p class="amount inv">{{ getTotalInvestment() | currency }}</p></div>
        <div class="summary-card"><p class="label">Ganho/Perda</p><p [ngClass]="{'positive': getProfit() >= 0, 'negative': getProfit() < 0}" class="amount">{{ getProfit() | currency }}</p></div>
      </div>
      <div class="portfolio-list">
        <div class="portfolio-item" *ngFor="let item of portfolio">
          <div class="crypto-info"><h3>{{ item.symbol }}</h3><p class="quantity">{{ item.quantity }} unidades</p></div>
          <div class="prices">
            <div><span class="label">Compra:</span><span>{{ item.purchase_price | currency }}</span></div>
            <div><span class="label">Atual:</span><span>{{ item.price | currency }}</span></div>
            <div><span class="label">Total:</span><span [ngClass]="{'positive': (item.price * item.quantity) >= (item.purchase_price * item.quantity), 'negative': (item.price * item.quantity) < (item.purchase_price * item.quantity)}">{{ (item.price * item.quantity) | currency }}</span></div>
          </div>
          <button (click)="removeFromPortfolio(item.id)" class="btn-delete">Remover</button>
        </div>
      </div>
      <div class="empty" *ngIf="portfolio.length === 0"><p>Portfólio vazio. Adiciona criptomoedas!</p></div>
    </div>
  `,
  styles: [`
    .portfolio { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; }
    .page-subtitle { color: #8B949E; font-size: 0.95rem; margin: 0.25rem 0 0; }
    .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .summary-card { background: rgba(33,38,45,0.8); border: 1px solid rgba(48,54,61,0.6); padding: 1.5rem; border-radius: 16px; transition: all 250ms ease; }
    .summary-card:hover { border-color: rgba(0,212,170,0.2); transform: translateY(-2px); }
    .summary-card .label { color: #6E7681; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; margin: 0 0 0.5rem; }
    .amount { font-size: 1.5rem; font-weight: 800; color: #00D4AA; margin: 0; }
    .amount.inv { color: #7B61FF; }
    .positive { color: #3FB950 !important; }
    .negative { color: #F85149 !important; }
    .portfolio-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem; }
    .portfolio-item { background: rgba(33,38,45,0.8); border: 1px solid rgba(48,54,61,0.6); padding: 1.5rem; border-radius: 16px; transition: all 250ms ease; }
    .portfolio-item:hover { border-color: rgba(0,212,170,0.15); transform: translateY(-2px); }
    .crypto-info h3 { margin: 0; font-size: 1.25rem; font-weight: 800; }
    .quantity { margin: 0.2rem 0 0; color: #6E7681; font-size: 0.85rem; }
    .prices { margin: 1rem 0; border-top: 1px solid rgba(48,54,61,0.6); padding-top: 1rem; }
    .prices div { display: flex; justify-content: space-between; margin: 0.4rem 0; font-size: 0.9rem; }
    .prices .label { color: #6E7681; }
    .btn-delete { width: 100%; padding: 0.65rem; background: rgba(248,81,73,0.1); color: #F85149; border: 1px solid rgba(248,81,73,0.2); border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 250ms; margin-top: 0.5rem; }
    .btn-delete:hover { background: rgba(248,81,73,0.15); transform: none; }
    .empty { text-align: center; padding: 3rem; color: #6E7681; }
  `]
})
export class PortfolioComponent implements OnInit {
  portfolio: any[] = [];
  constructor(private cryptoService: CryptoService) {}
  ngOnInit(): void { this.loadPortfolio(); }
  loadPortfolio(): void { this.cryptoService.getPortfolio().subscribe({ next: (response) => { if (response.success) { this.portfolio = response.data; } }, error: (err) => console.error('Erro ao carregar portfólio:', err) }); }
  getTotalValue(): number { return this.portfolio.reduce((total, item) => total + (item.price * item.quantity), 0); }
  getTotalInvestment(): number { return this.portfolio.reduce((total, item) => total + (item.purchase_price * item.quantity), 0); }
  getProfit(): number { return this.getTotalValue() - this.getTotalInvestment(); }
  removeFromPortfolio(portfolioId: number): void { if (confirm('Tem certeza que deseja remover?')) { this.cryptoService.removeFromPortfolio(portfolioId).subscribe({ next: () => this.loadPortfolio(), error: (err) => console.error('Erro ao remover:', err) }); } }
}
