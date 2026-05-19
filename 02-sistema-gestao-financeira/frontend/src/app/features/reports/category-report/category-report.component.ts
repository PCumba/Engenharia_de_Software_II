import { Component, OnInit } from '@angular/core';
import { ReportService } from '../services/report.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-category-report',
  template: `
    <div class="page-container animate-fade-in">
      <div class="page-header">
        <button mat-icon-button routerLink="/reports"><mat-icon>arrow_back</mat-icon></button>
        <h1>{{ i18n.t('reports.categoryAnalysis') }}</h1>
      </div>
      <div class="loading-container" *ngIf="isLoading"><mat-spinner diameter="40"></mat-spinner></div>
      <div class="categories-report" *ngIf="!isLoading && data.length > 0">
        <mat-card *ngFor="let cat of data" class="category-card">
          <div class="category-row">
            <div class="category-info">
              <div class="color-dot" [style.background-color]="cat.color"></div>
              <span class="name">{{ cat.category }}</span>
            </div>
            <div class="category-values">
              <span class="amount">{{ cat.total | currency:'BRL' }}</span>
              <span class="count">{{ cat.count }} {{ i18n.t('transactions.title').toLowerCase() }}</span>
            </div>
          </div>
          <mat-progress-bar mode="determinate" [value]="getPercentage(cat.total)"></mat-progress-bar>
        </mat-card>
      </div>
      <div class="empty-state" *ngIf="!isLoading && data.length === 0">
        <mat-icon>pie_chart</mat-icon>
        <p>{{ i18n.t('dashboard.noData') }}</p>
      </div>
    </div>
  `,
  styles: [`
    .page-container { padding: 1.5rem; max-width: 800px; margin: 0 auto; }
    .page-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
    .page-header h1 { font-size: 1.25rem; margin: 0; }
    .loading-container { display: flex; justify-content: center; padding: 3rem; }
    .empty-state { text-align: center; padding: 3rem; color: var(--text-secondary); }
    .empty-state mat-icon { font-size: 64px; width: 64px; height: 64px; opacity: 0.3; }
    .categories-report { display: flex; flex-direction: column; gap: 0.75rem; }
    .category-card { padding: 1rem; border-radius: var(--border-radius-lg); }
    .category-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
    .category-info { display: flex; align-items: center; gap: 0.5rem; }
    .color-dot { width: 14px; height: 14px; border-radius: 50%; }
    .name { font-weight: 500; }
    .category-values { text-align: right; }
    .amount { font-weight: 600; display: block; }
    .count { font-size: 0.8rem; color: var(--text-secondary); }
  `]
})
export class CategoryReportComponent implements OnInit {
  data: any[] = [];
  isLoading = true;
  maxTotal = 0;

  constructor(private reportService: ReportService, public i18n: I18nService) {}

  ngOnInit(): void {
    this.reportService.getCategoryAnalysis().subscribe({
      next: (res: any) => {
        this.data = res.data || [];
        this.maxTotal = Math.max(...this.data.map((d: any) => d.total), 1);
        this.isLoading = false;
      },
      error: () => { this.isLoading = false; }
    });
  }

  getPercentage(total: number): number {
    return this.maxTotal > 0 ? (total / this.maxTotal) * 100 : 0;
  }
}
