import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { MenuComponent } from './pages/menu.component';

@NgModule({
  declarations: [MenuComponent],
  imports: [CommonModule, ReactiveFormsModule, FormsModule]
})
export class MenuModule { }
