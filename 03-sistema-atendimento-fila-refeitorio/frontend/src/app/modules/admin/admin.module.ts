import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { AdminDashboardComponent } from './pages/dashboard/dashboard.component';

@NgModule({
  declarations: [
    AdminDashboardComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild([
      { path: 'dashboard', component: AdminDashboardComponent }
    ])
  ]
})
export class AdminModule { }
