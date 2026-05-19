import { Component, Input } from '@angular/core';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-financial-summary',
  template: `
    <div class="summary-grid">
      <mat-card class="summary-card balance-card">
        <div class="summary-icon"><mat-icon>account_balance_wallet</mat-icon></div>
        <div class="summary-content">
          <span class="summary-label">{{ i18n.t('dashboard.totalBalance') }}</span>
          <span class="summary-value">{{ totalBalance | currency:'BRL' }}</span>
        </div>
      </mat-card>
      <mat-card class="summary-card income-card">
        <div class="summary-icon"><mat-icon>trending_up</mat-icon></div>
        <div class="summary-content">
          <span class="summary-label">{{ i18n.t('dashboard.monthlyIncome') }}</span>
          <span class="summary-value income">{{ monthlyIncome | currency:'BRL' }}</span>
        </div>
      </mat-card>
      <mat-card class="summary-card expense-card">
        <div class="summary-icon"><mat-icon>trending_down</mat-icon></div>
        <div class="summary-content">
          <span class="summary-label">{{ i18n.t('dashboard.monthlyExpense') }}</span>
          <span class="summary-value expense">{{ monthlyExpense | currency:'BRL' }}</span>
        </div>
      </mat-card>
      <mat-card class="summary-card net-card">
        <div class="summary-icon"><mat-icon>balance</mat-icon></div>
        <div class="summary-content">
          <span class="summary-label">{{ i18n.t('dashboard.monthlyBalance') }}</span>
          <span class="summary-value" [class.income]="monthlyBalance >= 0" [class.expense]="monthlyBalance < 0">
            {{ monthlyBalance | currency:'BRL' }}
          </span>
        </div>
      </mat-card>
    </div>
  `,
  styles: [`
    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .summary-card { display: flex; align-items: center; gap: 1rem; padding: 1.25rem; border-radius: 12px; transition: transform 0.2s; }
    .summary-card:hover { transform: translateY(-2px); }
    .summary-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .balance-card .summary-icon { background: rgba(33, 150, 243, 0.1); color: #2196f3; }
    .income-card .summary-icon { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
    .expense-card .summary-icon { background: rgba(244, 67, 54, 0.1); color: #f44336; }
    .net-card .summary-icon { background: rgba(156, 39, 176, 0.1); color: #9c27b0; }
    .summary-content { display: flex; flex-direction: column; }
    .summary-label { font-size: 0.8rem; color: var(--text-secondary); }
    .summary-value { font-size: 1.25rem; font-weight: 700; color: var(--text-color); }
    .summary-value.income { color: #4caf50; }
    .summary-value.expense { color: #f44336; }
  `]
})
export class FinancialSummaryComponent {
  @Input() totalBalance = 0;
  @Input() monthlyIncome = 0;
  @Input() monthlyExpense = 0;
  @Input() monthlyBalance = 0;

  constructor(public i18n: I18nService) {}
}
