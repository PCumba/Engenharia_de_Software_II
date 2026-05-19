import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';

import { AccountService, Account } from '../services/account.service';
import { DialogService } from '../../../shared/services/dialog.service';

@Component({
  selector: 'app-accounts-list',
  templateUrl: './accounts-list.component.html',
  styleUrls: ['./accounts-list.component.scss']
})
export class AccountsListComponent implements OnInit {
  accounts: Account[] = [];
  isLoading = true;
  totalBalance = 0;

  typeLabels: { [key: string]: string } = {
    checking: 'Conta Corrente',
    savings: 'Poupança',
    credit_card: 'Cartão de Crédito',
    investment: 'Investimento',
    cash: 'Dinheiro',
    other: 'Outros'
  };

  typeIcons: { [key: string]: string } = {
    checking: 'account_balance',
    savings: 'savings',
    credit_card: 'credit_card',
    investment: 'trending_up',
    cash: 'payments',
    other: 'account_balance_wallet'
  };

  constructor(
    private accountService: AccountService,
    private dialogService: DialogService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.loadAccounts();
  }

  loadAccounts(): void {
    this.isLoading = true;
    this.accountService.getAll().subscribe({
      next: (accounts) => {
        this.accounts = accounts;
        this.totalBalance = accounts.reduce((sum, a) => sum + a.current_balance, 0);
        this.isLoading = false;
      },
      error: () => {
        this.isLoading = false;
        this.snackBar.open('Erro ao carregar contas', 'Fechar', { duration: 3000 });
      }
    });
  }

  navigateToNew(): void { this.router.navigate(['/accounts/new']); }
  navigateToDetail(id: number): void { this.router.navigate(['/accounts', id]); }
  navigateToEdit(id: number, e: Event): void { e.stopPropagation(); this.router.navigate(['/accounts', id, 'edit']); }

  deleteAccount(account: Account, e: Event): void {
    e.stopPropagation();
    this.dialogService.confirmDelete(account.name).subscribe(confirmed => {
      if (confirmed) {
        this.accountService.delete(account.id).subscribe({
          next: () => {
            this.snackBar.open('Conta excluída', 'Fechar', { duration: 3000, panelClass: ['success-snackbar'] });
            this.loadAccounts();
          },
          error: () => this.snackBar.open('Erro ao excluir conta', 'Fechar', { duration: 3000 })
        });
      }
    });
  }

  formatCurrency(value: number, currency = 'BRL'): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency }).format(value);
  }

  getTypeLabel(type: string): string { return this.typeLabels[type] || type; }
  getTypeIcon(type: string): string { return this.typeIcons[type] || 'account_balance_wallet'; }

  getBalanceClass(balance: number, type: string): string {
    if (type === 'credit_card') return balance > 0 ? 'negative' : 'positive';
    return balance >= 0 ? 'positive' : 'negative';
  }
}