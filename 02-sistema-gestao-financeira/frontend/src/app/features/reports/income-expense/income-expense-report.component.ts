import { Component, OnInit } from '@angular/core';
import { ReportService } from '../services/report.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-income-expense-report',
  template: `
    <div class="page-container animate-fade-in">
      <div class="page-header">
        <button mat-icon-button routerLink="/reports"><mat-icon>arrow_back</mat-icon></button>
        <h1>{{ i18n.t('reports.incomeExpense') }}</h1>
      </div>
      <div class="loading-container" *ngIf="isLoading"><mat-spinner diameter="40"></mat-spinner></div>
      <mat-card *ngIf="!isLoading && data.length > 0" class="report-card">
        <mat-card-content>
          <table mat-table [dataSource]="data" class="full-width">
            <ng-container matColumnDef="month">
              <th mat-header-cell *matHeaderCellDef>{{ i18n.t('reports.period') }}</th>
              <td mat-cell *matCellDef="let row">{{ row.month }}</td>
            </ng-container>
            <ng-container matColumnDef="income">
              <th mat-header-cell *matHeaderCellDef>{{ i18n.t('transactions.income') }}</th>
              <td mat-cell *matCellDef="let row" style="color:#4caf50">{{ row.income | currency:'BRL' }}</td>
            </ng-container>
            <ng-container matColumnDef="expense">
              <th mat-header-cell *matHeaderCellDef>{{ i18n.t('transactions.expense') }}</th>
              <td mat-cell *matCellDef="let row" style="color:#f44336">{{ row.expense | currency:'BRL' }}</td>
            </ng-container>
            <ng-container matColumnDef="balance">
              <th mat-header-cell *matHeaderCellDef>{{ i18n.t('dashboard.monthlyBalance') }}</th>
              <td mat-cell *matCellDef="let row" [style.color]="row.balance >= 0 ? '#4caf50' : '#f44336'">{{ row.balance | currency:'BRL' }}</td>
            </ng-container>
            <tr mat-header-row *matHeaderRowDef="displayedColumns"></tr>
            <tr mat-row *matRowDef="let row; columns: displayedColumns;"></tr>
          </table>
        </mat-card-content>
      </mat-card>
      <div class="empty-state" *ngIf="!isLoading && data.length === 0">
        <mat-icon>assessment</mat-icon>
        <p>{{ i18n.t('dashboard.noData') }}</p>
      </div>
    </div>
  `,
  styles: [`
    .page-container { padding: 1.5rem; max-width: 1000px; margin: 0 auto; }
    .page-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
    .page-header h1 { font-size: 1.25rem; margin: 0; }
    .loading-container { display: flex; justify-content: center; padding: 3rem; }
    .empty-state { text-align: center; padding: 3rem; color: var(--text-secondary); }
    .empty-state mat-icon { font-size: 64px; width: 64px; height: 64px; opacity: 0.3; }
    .report-card { border-radius: var(--border-radius-lg); }
  `]
})
export class IncomeExpenseReportComponent implements OnInit {
  data: any[] = [];
  isLoading = true;
  displayedColumns = ['month', 'income', 'expense', 'balance'];

  constructor(private reportService: ReportService, public i18n: I18nService) {}

  ngOnInit(): void {
    this.reportService.getEvolution().subscribe({
      next: (res: any) => { this.data = res.data || []; this.isLoading = false; },
      error: () => { this.isLoading = false; }
    });
  }
}
