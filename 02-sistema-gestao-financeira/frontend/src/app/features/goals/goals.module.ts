import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NgChartsModule } from 'ng2-charts';

import { SharedModule } from '../../shared/shared.module';
import { GoalsListComponent } from './goals-list/goals-list.component';
import { GoalFormComponent } from './goal-form/goal-form.component';
import { GoalService } from './services/goal.service';

const routes: Routes = [
  { path: '', component: GoalsListComponent, title: 'Metas - Sistema Financeiro' },
  { path: 'new', component: GoalFormComponent, title: 'Nova Meta' },
  { path: ':id/edit', component: GoalFormComponent, title: 'Editar Meta' }
];

@NgModule({
  declarations: [GoalsListComponent, GoalFormComponent],
  imports: [SharedModule, NgChartsModule, RouterModule.forChild(routes)],
  providers: [GoalService]
})
export class GoalsModule {}