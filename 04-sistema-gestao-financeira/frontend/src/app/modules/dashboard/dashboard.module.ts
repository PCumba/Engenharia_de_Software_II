import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { DashboardComponent } from './pages/dashboard.component';
import { TransactionsComponent } from '@modules/transactions/pages/transactions.component';
import { BudgetsComponent } from '@modules/budgets/pages/budgets.component';
import { AnalyticsComponent } from '@modules/analytics/pages/analytics.component';

@NgModule({
  declarations: [
    DashboardComponent,
    TransactionsComponent,
    BudgetsComponent,
    AnalyticsComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule
  ]
})
export class DashboardModule { }
