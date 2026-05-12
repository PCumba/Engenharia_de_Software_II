import { Component, OnInit } from '@Angular/core';
import { FinanceService } from '@core/services/finance.service';

@Component({
  selector: 'app-analytics',
  template: `
    <div class="analytics"><h2>Análises</h2>
      <div class="period-selector"><input type="month" [(ngModel)]="selectedMonth" (change)="loadAnalytics()"></div>
      <div class="analytics-grid">
        <div class="card"><h3>Receitas por Categoria</h3><div class="category-list"><div class="category-item" *ngFor="let cat of incomeByCategory"><span class="cat-name">{{ cat[0] }}</span><span class="cat-value income">{{ cat[1] | currency }}</span></div></div></div>
        <div class="card"><h3>Despesas por Categoria</h3><div class="category-list"><div class="category-item" *ngFor="let cat of expensesByCategory"><span class="cat-name">{{ cat[0] }}</span><span class="cat-value expense">{{ cat[1] | currency }}</span></div></div></div>
      </div>
      <div class="report" *ngIf="report"><h3>Relatório do Período</h3>
        <div class="report-grid">
          <div class="report-item"><p class="r-label">Receitas</p><p class="r-value income">{{ report.totalIncome | currency }}</p></div>
          <div class="report-item"><p class="r-label">Despesas</p><p class="r-value expense">{{ report.totalExpenses | currency }}</p></div>
          <div class="report-item"><p class="r-label">Saldo</p><p class="r-value" [class.negative]="report.balance < 0">{{ report.balance | currency }}</p></div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .analytics { background: white; padding: 1.75rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 2px 12px rgba(26,26,46,0.04); }
    h2 { font-size: 1.15rem; font-weight: 700; margin: 0 0 1.25rem; }
    h3 { font-size: 1rem; font-weight: 700; margin: 0 0 1rem; color: #1A1A2E; }
    .period-selector { margin-bottom: 1.5rem; }
    .period-selector input { padding: 0.6rem 1rem; border: 1.5px solid rgba(108,92,231,0.12); border-radius: 10px; font-size: 0.875rem; color: #1A1A2E; background: white; }
    .period-selector input:focus { border-color: #6C5CE7; outline: none; box-shadow: 0 0 0 3px rgba(108,92,231,0.08); }
    .analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; }
    .card { background: #FAFAFE; padding: 1.25rem; border-radius: 14px; border: 1px solid rgba(0,0,0,0.03); }
    .category-list { display: flex; flex-direction: column; gap: 0.4rem; }
    .category-item { display: flex; justify-content: space-between; padding: 0.6rem 0.75rem; background: white; border-radius: 10px; transition: all 200ms; }
    .category-item:hover { background: rgba(108,92,231,0.03); transform: translateX(2px); }
    .cat-name { font-weight: 500; color: #4A4A6A; font-size: 0.875rem; }
    .cat-value { font-weight: 700; font-size: 0.875rem; }
    .cat-value.income { color: #00B894; }
    .cat-value.expense { color: #E17055; }
    .report { margin-top: 1rem; }
    .report-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    .report-item { background: #FAFAFE; padding: 1.25rem; text-align: center; border-radius: 14px; border: 1px solid rgba(0,0,0,0.03); }
    .r-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9696B4; margin: 0 0 0.5rem; }
    .r-value { font-size: 1.5rem; font-weight: 800; margin: 0; color: #1A1A2E; }
    .r-value.income { color: #00B894; }
    .r-value.expense { color: #E17055; }
    .r-value.negative { color: #D63031; }
    @media (max-width: 768px) { .analytics-grid { grid-template-columns: 1fr; } .report-grid { grid-template-columns: 1fr; } }
  `]
})
export class AnalyticsComponent implements OnInit {
  selectedMonth = new Date().toISOString().slice(0, 7); incomeByCategory: any[] = []; expensesByCategory: any[] = []; report: any;
  constructor(private financeService: FinanceService) {}
  ngOnInit(): void { this.loadAnalytics(); }
  loadAnalytics(): void {
    const [year, month] = this.selectedMonth.split('-'); const startDate = `${year}-${month}-01`; const endDate = new Date(parseInt(year), parseInt(month), 0).toISOString().split('T')[0];
    this.financeService.getIncomeByCategory(startDate, endDate).subscribe({ next: (response) => { if (response.success) { this.incomeByCategory = Object.entries(response.data); } } });
    this.financeService.getExpensesByCategory(startDate, endDate).subscribe({ next: (response) => { if (response.success) { this.expensesByCategory = Object.entries(response.data); } } });
    this.financeService.getPeriodReport(startDate, endDate).subscribe({ next: (response) => { if (response.success) { this.report = response.data; } } });
  }
}
