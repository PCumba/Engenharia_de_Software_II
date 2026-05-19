import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';

import { TransactionService, Transaction } from '../services/transaction.service';
import { DialogService } from '../../../shared/services/dialog.service';

@Component({
  selector: 'app-transaction-detail',
  templateUrl: './transaction-detail.component.html',
  styleUrls: ['./transaction-detail.component.scss']
})
export class TransactionDetailComponent implements OnInit {
  transaction: Transaction | null = null;
  isLoading = true;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private transactionService: TransactionService,
    private dialogService: DialogService,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    const id = +this.route.snapshot.params['id'];
    this.transactionService.getById(id).subscribe({
      next: (t) => { this.transaction = t; this.isLoading = false; },
      error: () => { this.isLoading = false; this.router.navigate(['/transactions']); }
    });
  }

  edit(): void {
    this.router.navigate(['/transactions', this.transaction!.id, 'edit']);
  }

  delete(): void {
    this.dialogService.confirmDelete(this.transaction!.description).subscribe(confirmed => {
      if (confirmed) {
        this.transactionService.delete(this.transaction!.id).subscribe({
          next: () => {
            this.snackBar.open('Transação excluída', 'Fechar', { duration: 3000, panelClass: ['success-snackbar'] });
            this.router.navigate(['/transactions']);
          },
          error: () => this.snackBar.open('Erro ao excluir', 'Fechar', { duration: 3000 })
        });
      }
    });
  }

  back(): void { this.router.navigate(['/transactions']); }

  getTypeLabel(type: string): string {
    return { income: 'Receita', expense: 'Despesa', transfer: 'Transferência' }[type] || type;
  }

  getTypeClass(type: string): string {
    return { income: 'type-income', expense: 'type-expense', transfer: 'type-transfer' }[type] || '';
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
  }

  formatDate(dateStr: string): string {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
  }
}