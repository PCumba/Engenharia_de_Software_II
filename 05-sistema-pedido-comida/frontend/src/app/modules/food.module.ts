import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { RestaurantsComponent } from './pages/restaurants.component';
import { MenuComponent } from '@modules/menu/pages/menu.component';
import { CheckoutComponent } from '@modules/checkout/pages/checkout.component';
import { OrdersComponent } from '@modules/orders/pages/orders.component';

@NgModule({
  declarations: [
    RestaurantsComponent,
    MenuComponent,
    CheckoutComponent,
    OrdersComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule
  ]
})
export class FoodModule { }
