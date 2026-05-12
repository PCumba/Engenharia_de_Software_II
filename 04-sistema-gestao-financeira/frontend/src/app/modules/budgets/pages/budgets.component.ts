import { Component, OnInit } from '@Angular/core';
import { FinanceService } from '@core/services/finance.service';

@Component({
  selector: 'app-budgets',
  template: `
    <div class="budgets"><h2>Orçamentos</h2>
      <div class="budgets-grid">
        <div class="budget-card" *ngFor="let budget of budgets">
          <h3>{{ budget.category_name }}</h3>
          <p class="spent">{{ budget.spent | currency }} / {{ budget.limit_amount | currency }}</p>
          <div class="progress-bar" [class.exceeded]="budget.spent > budget.limit_amount"><div class="progress" [style.width.%]="Math.min(budget.spent / budget.limit_amount * 100, 100)"></div></div>
          <p class="percentage" [class.exceeded]="budget.spent > budget.limit_amount">{{ (budget.spent / budget.limit_amount * 100) | number:'1.0-0' }}%</p>
          <div class="actions"><button (click)="editBudget(budget)" class="btn-edit">Editar</button><button (click)="deleteBudget(budget.id)" class="btn-delete">Apagar</button></div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .budgets { background: white; padding: 1.75rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 2px 12px rgba(26,26,46,0.04); }
    h2 { font-size: 1.15rem; font-weight: 700; margin: 0 0 1.25rem; }
    .budgets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
    .budget-card { background: #FAFAFE; padding: 1.5rem; border-radius: 14px; border-left: 4px solid #6C5CE7; transition: all 250ms; }
    .budget-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(26,26,46,0.06); }
    h3 { margin: 0 0 0.5rem; font-size: 1rem; font-weight: 700; color: #1A1A2E; }
    .spent { color: #9696B4; font-size: 0.85rem; margin: 0 0 0.75rem; }
    .progress-bar { background: #EEF0F6; height: 8px; border-radius: 100px; overflow: hidden; margin-bottom: 0.5rem; }
    .progress { background: linear-gradient(90deg, #00B894, #55EFC4); height: 100%; border-radius: 100px; transition: width 600ms ease; }
    .progress-bar.exceeded .progress { background: linear-gradient(90deg, #E17055, #D63031); }
    .percentage { font-size: 0.85rem; font-weight: 800; color: #00B894; margin: 0 0 0.75rem; }
    .percentage.exceeded { color: #E17055; }
    .actions { display: flex; gap: 0.5rem; }
    .actions button { flex: 1; padding: 0.5rem; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.8rem; transition: all 250ms; }
    .btn-edit { background: rgba(108,92,231,0.08); color: #6C5CE7; border: 1px solid rgba(108,92,231,0.12) !important; }
    .btn-edit:hover { background: rgba(108,92,231,0.12); }
    .btn-delete { background: rgba(225,112,85,0.08); color: #E17055; border: 1px solid rgba(225,112,85,0.12) !important; }
    .btn-delete:hover { background: rgba(225,112,85,0.12); }
  `]
})
export class BudgetsComponent implements OnInit {
  budgets: any[] = []; Math = Math;
  constructor(private financeService: FinanceService) {}
  ngOnInit(): void { this.loadBudgets(); }
  loadBudgets(): void { this.financeService.getBudgets().subscribe({ next: (response) => { if (response.success) { this.budgets = response.data.budgets; } } }); }
  editBudget(budget: any): void { const newLimit = prompt('Novo limite:', budget.limit_amount); if (newLimit) { this.financeService.updateBudget(budget.id, { limitAmount: newLimit }).subscribe({ next: () => this.loadBudgets() }); } }
  deleteBudget(id: number): void { if (confirm('Deletar orçamento?')) { this.financeService.deleteBudget(id).subscribe({ next: () => this.loadBudgets() }); } }
}
