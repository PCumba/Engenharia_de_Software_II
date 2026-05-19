import { Component, OnInit } from '@angular/core';
import { ReportService } from '../services/report.service';
import { ExportService } from '../services/export.service';
import { I18nService } from '../../../core/services/i18n.service';
import { NotificationService } from '../../../core/services/notification.service';

@Component({
  selector: 'app-reports-dashboard',
  templateUrl: './reports-dashboard.component.html',
  styleUrls: ['./reports-dashboard.component.scss']
})
export class ReportsDashboardComponent implements OnInit {
  summary: any = null;
  categoryData: any[] = [];
  evolutionData: any[] = [];
  selectedPeriod = 'month';
  isLoading = true;

  periods = [
    { value: 'month', label: 'reports.thisMonth' },
    { value: 'quarter', label: 'reports.last3Months' },
    { value: 'year', label: 'reports.thisYear' }
  ];

  constructor(
    private reportService: ReportService,
    private exportService: ExportService,
    public i18n: I18nService,
    private notification: NotificationService
  ) {}

  ngOnInit(): void {
    this.loadData();
  }

  loadData(): void {
    this.isLoading = true;
    this.reportService.getSummary(this.selectedPeriod).subscribe({
      next: (res: any) => { this.summary = res.data; this.isLoading = false; },
      error: () => { this.isLoading = false; }
    });
    this.reportService.getCategoryAnalysis(this.selectedPeriod).subscribe({
      next: (res: any) => { this.categoryData = res.data || []; }
    });
    this.reportService.getEvolution().subscribe({
      next: (res: any) => { this.evolutionData = res.data || []; }
    });
  }

  onPeriodChange(): void {
    this.loadData();
  }

  exportCSV(): void {
    const data = this.categoryData.map(c => ({
      categoria: c.category,
      total: c.total,
      transacoes: c.count
    }));
    this.exportService.exportCSV(data, `relatorio_${this.selectedPeriod}`);
    this.notification.success(this.i18n.t('reports.exportCSV') + ' ✓');
  }

  exportPDF(): void {
    const data = this.categoryData.map(c => ({
      Categoria: c.category,
      Total: `R$ ${c.total.toFixed(2)}`,
      Transações: c.count
    }));
    this.exportService.exportPDF(data, this.i18n.t('reports.categoryAnalysis'), `relatorio_${this.selectedPeriod}`);
    this.notification.success(this.i18n.t('reports.exportPDF') + ' ✓');
  }
}
