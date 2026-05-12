import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { OrdersComponent } from './pages/orders.component';

@NgModule({
  declarations: [OrdersComponent],
  imports: [CommonModule, ReactiveFormsModule, FormsModule]
})
export class OrdersModule { }
