import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { RestaurantsComponent } from './pages/restaurants.component';

@NgModule({
  declarations: [RestaurantsComponent],
  imports: [CommonModule, ReactiveFormsModule, FormsModule]
})
export class RestaurantsModule { }
