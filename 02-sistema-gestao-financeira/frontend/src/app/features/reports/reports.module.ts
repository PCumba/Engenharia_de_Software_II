import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NgChartsModule } from 'ng2-charts';

import { SharedModule } from '../../shared/shared.module';
import { ReportsDashboardComponent } from './reports-dashboard/reports-dashboard.component';
import { IncomeExpenseReportComponent } from './income-expense/income-expense-report.component';
import { CategoryReportComponent } from './category-report/category-report.component';
import { ReportService } from './services/report.service';
import { ExportService } from './services/export.service';

const routes: Routes = [
  { path: '', component: ReportsDashboardComponent, title: 'Relatórios - Sistema Financeiro' },
  { path: 'income-expense', component: IncomeExpenseReportComponent, title: 'Receitas e Despesas' },
  { path: 'categories', component: CategoryReportComponent, title: 'Análise por Categoria' }
];

@NgModule({
  declarations: [ReportsDashboardComponent, IncomeExpenseReportComponent, CategoryReportComponent],
  imports: [SharedModule, NgChartsModule, RouterModule.forChild(routes)],
  providers: [ReportService, ExportService]
})
export class ReportsModule {}