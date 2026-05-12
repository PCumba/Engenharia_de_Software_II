import { Component, OnInit } from '@angular/core';
import { FinanceService } from '@core/services/finance.service';
import { AuthService } from '@core/services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-dashboard',
  template: `
    <div class="dashboard">
      <div class="header"><div><h1>Gestão Financeira</h1><p class="subtitle">Visão geral das suas finanças</p></div><button (click)="logout()" class="btn-logout">Sair</button></div>
      <div class="content">
        <div class="cards-grid">
          <div class="card income"><div class="card-icon">📈</div><p class="card-label">Receitas</p><p class="card-value">{{ summary?.balance?.income | currency }}</p></div>
          <div class="card expense"><div class="card-icon">📉</div><p class="card-label">Despesas</p><p class="card-value">{{ summary?.balance?.expenses | currency }}</p></div>
          <div class="card balance" [class.negative]="(summary?.balance?.balance || 0) < 0"><div class="card-icon">💰</div><p class="card-label">Saldo</p><p class="card-value">{{ summary?.balance?.balance | currency }}</p></div>
        </div>
        <div class="nav-tabs"><button [class.active]="activeTab === 'transactions'" (click)="activeTab = 'transactions'">📊 Transações</button><button [class.active]="activeTab === 'budgets'" (click)="activeTab = 'budgets'">📋 Orçamentos</button><button [class.active]="activeTab === 'analytics'" (click)="activeTab = 'analytics'">📈 Análises</button></div>
        <div class="tab-content" *ngIf="activeTab === 'transactions'"><app-transactions></app-transactions></div>
        <div class="tab-content" *ngIf="activeTab === 'budgets'"><app-budgets></app-budgets></div>
        <div class="tab-content" *ngIf="activeTab === 'analytics'"><app-analytics></app-analytics></div>
      </div>
    </div>
  `,
  styles: [`
    .dashboard { min-height: 100vh; padding: 2rem; font-family: 'Inter', sans-serif; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.03em; margin: 0; }
    .subtitle { color: #9696B4; font-size: 0.9rem; margin: 0.2rem 0 0; }
    .btn-logout { padding: 0.5rem 1.25rem; background: rgba(225,112,85,0.06); color: #E17055; border: 1px solid rgba(225,112,85,0.15); border-radius: 10px; font-weight: 600; font-size: 0.8125rem; cursor: pointer; transition: all 250ms; }
    .btn-logout:hover { background: rgba(225,112,85,0.1); transform: none; }
    .cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
    .card { background: white; padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 2px 12px rgba(26,26,46,0.04); position: relative; overflow: hidden; transition: all 300ms ease; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(26,26,46,0.08); }
    .card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
    .card.income::before { background: linear-gradient(180deg, #00B894, #55EFC4); }
    .card.expense::before { background: linear-gradient(180deg, #E17055, #FAB1A0); }
    .card.balance::before { background: linear-gradient(180deg, #6C5CE7, #A29BFE); }
    .card.balance.negative::before { background: linear-gradient(180deg, #E17055, #D63031); }
    .card-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
    .card-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9696B4; margin: 0 0 0.4rem; }
    .card-value { font-size: 1.5rem; font-weight: 800; margin: 0; color: #1A1A2E; }
    .nav-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
    .nav-tabs button { padding: 0.6rem 1.25rem; background: white; border: 1.5px solid rgba(108,92,231,0.1); cursor: pointer; border-radius: 100px; font-weight: 600; font-size: 0.85rem; color: #4A4A6A; transition: all 250ms; }
    .nav-tabs button:hover { border-color: rgba(108,92,231,0.25); color: #6C5CE7; }
    .nav-tabs button.active { background: linear-gradient(135deg, #6C5CE7 0%, #A29BFE 100%); color: white; border-color: transparent; box-shadow: 0 4px 14px rgba(108,92,231,0.25); }
    .tab-content { animation: slideIn 0.3s ease-out; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .cards-grid { grid-template-columns: 1fr; } .nav-tabs { flex-wrap: wrap; } }
  `]
})
export class DashboardComponent implements OnInit {
  summary: any; activeTab = 'transactions'; loading = false;
  constructor(private financeService: FinanceService, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { this.loadSummary(); }
  loadSummary(): void { this.loading = true; this.financeService.getSummary().subscribe({ next: (response) => { if (response.success) { this.summary = response.data; } this.loading = false; }, error: () => this.loading = false }); }
  logout(): void { this.authService.logout(); this.router.navigate(['/auth/login']); }
}
