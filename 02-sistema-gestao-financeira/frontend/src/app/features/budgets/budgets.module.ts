import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NgChartsModule } from 'ng2-charts';

import { SharedModule } from '../../shared/shared.module';
import { BudgetsListComponent } from './budgets-list/budgets-list.component';
import { BudgetFormComponent } from './budget-form/budget-form.component';
import { BudgetService } from './services/budget.service';

const routes: Routes = [
  { path: '', component: BudgetsListComponent, title: 'Orçamentos - Sistema Financeiro' },
  { path: 'new', component: BudgetFormComponent, title: 'Novo Orçamento' },
  { path: ':id/edit', component: BudgetFormComponent, title: 'Editar Orçamento' }
];

@NgModule({
  declarations: [BudgetsListComponent, BudgetFormComponent],
  imports: [SharedModule, NgChartsModule, RouterModule.forChild(routes)],
  providers: [BudgetService]
})
export class BudgetsModule {}