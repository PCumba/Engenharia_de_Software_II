import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NgChartsModule } from 'ng2-charts';

import { SharedModule } from '../../shared/shared.module';
import { AccountsListComponent } from './accounts-list/accounts-list.component';
import { AccountFormComponent } from './account-form/account-form.component';
import { AccountDetailComponent } from './account-detail/account-detail.component';
import { AccountService } from './services/account.service';

const routes: Routes = [
  { path: '', component: AccountsListComponent, title: 'Contas - Sistema Financeiro' },
  { path: 'new', component: AccountFormComponent, title: 'Nova Conta' },
  { path: ':id', component: AccountDetailComponent, title: 'Detalhe da Conta' },
  { path: ':id/edit', component: AccountFormComponent, title: 'Editar Conta' }
];

@NgModule({
  declarations: [AccountsListComponent, AccountFormComponent, AccountDetailComponent],
  imports: [SharedModule, NgChartsModule, RouterModule.forChild(routes)],
  providers: [AccountService]
})
export class AccountsModule {}