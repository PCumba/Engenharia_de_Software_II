import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { SharedModule } from '../../shared/shared.module';
import { CategoriesListComponent } from './categories-list/categories-list.component';
import { CategoryFormComponent } from './category-form/category-form.component';
import { CategoryService } from './services/category.service';

const routes: Routes = [
  { path: '', component: CategoriesListComponent, title: 'Categorias - Sistema Financeiro' },
  { path: 'new', component: CategoryFormComponent, title: 'Nova Categoria' },
  { path: ':id/edit', component: CategoryFormComponent, title: 'Editar Categoria' }
];

@NgModule({
  declarations: [CategoriesListComponent, CategoryFormComponent],
  imports: [SharedModule, RouterModule.forChild(routes)],
  providers: [CategoryService]
})
export class CategoriesModule {}