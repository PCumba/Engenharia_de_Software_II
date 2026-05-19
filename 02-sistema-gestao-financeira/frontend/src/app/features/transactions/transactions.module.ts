import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NgChartsModule } from 'ng2-charts';

import { SharedModule } from '../../shared/shared.module';
import { TransactionsListComponent } from './transactions-list/transactions-list.component';
import { TransactionFormComponent } from './transaction-form/transaction-form.component';
import { TransactionDetailComponent } from './transaction-detail/transaction-detail.component';
import { TransactionService } from './services/transaction.service';

const routes: Routes = [
  { path: '', component: TransactionsListComponent, title: 'Transações - Sistema Financeiro' },
  { path: 'new', component: TransactionFormComponent, title: 'Nova Transação' },
  { path: ':id', component: TransactionDetailComponent, title: 'Detalhe da Transação' },
  { path: ':id/edit', component: TransactionFormComponent, title: 'Editar Transação' }
];

@NgModule({
  declarations: [
    TransactionsListComponent,
    TransactionFormComponent,
    TransactionDetailComponent
  ],
  imports: [
    SharedModule,
    NgChartsModule,
    RouterModule.forChild(routes)
  ],
  providers: [TransactionService]
})
export class TransactionsModule {}