import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { CustomerDashboardComponent } from './pages/customer/customer.component';

@NgModule({
  declarations: [
    CustomerDashboardComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild([
      { path: 'customer', component: CustomerDashboardComponent }
    ])
  ]
})
export class QueueModule { }
