import { Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Subject } from 'rxjs';
import { takeUntil, debounceTime, distinctUntilChanged } from 'rxjs/operators';

import { TransactionService, Transaction, TransactionFilters } from '../services/transaction.service';
import { DialogService } from '../../../shared/services/dialog.service';

@Component({
  selector: 'app-transactions-list',
  templateUrl: './transactions-list.component.html',
  styleUrls: ['./transactions-list.component.scss']
})
export class TransactionsListComponent implements OnInit, OnDestroy {
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  private destroy$ = new Subject<void>();

  transactions: Transaction[] = [];
  isLoading = false;
  totalItems = 0;
  pageSize = 20;
  currentPage = 1;

  displayedColumns = ['type', 'description', 'category', 'account', 'date', 'amount', 'status', 'actions'];

  filterForm: FormGroup;
  showFilters = false;

  typeOptions = [
    { value: '', label: 'Todos' },
    { value: 'income', label: 'Receitas' },
    { value: 'expense', label: 'Despesas' },
    { value: 'transfer', label: 'Transferências' }
  ];

  statusOptions = [
    { value: '', label: 'Todos' },
    { value: 'completed', label: 'Concluídas' },
    { value: 'pending', label: 'Pendentes' },
    { value: 'cancelled', label: 'Canceladas' }
  ];

  // Resumo do período
  summary = { income: 0, expense: 0, balance: 0 };

  constructor(
    private transactionService: TransactionService,
    private dialogService: DialogService,
    private router: Router,
    private fb: FormBuilder,
    private snackBar: MatSnackBar
  ) {
    this.filterForm = this.createFilterForm();
  }

  ngOnInit(): void {
    this.loadTransactions();
    this.setupFilterListener();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private createFilterForm(): FormGroup {
    return this.fb.group({
      search: [''],
      type: [''],
      status: [''],
      date_from: [''],
      date_to: [''],
      amount_min: [''],
      amount_max: ['']
    });
  }

  private setupFilterListener(): void {
    this.filterForm.valueChanges.pipe(
      takeUntil(this.destroy$),
      debounceTime(400),
      distinctUntilChanged()
    ).subscribe(() => {
      this.currentPage = 1;
      this.loadTransactions();
    });
  }

  loadTransactions(): void {
    this.isLoading = true;
    const filters = this.buildFilters();

    this.transactionService.getAll(filters).pipe(
      takeUntil(this.destroy$)
    ).subscribe({
      next: (result) => {
        this.transactions = result.data;
        this.totalItems = result.pagination.total;
        this.calculateSummary();
        this.isLoading = false;
      },
      error: () => {
        this.isLoading = false;
        this.snackBar.open('Erro ao carregar transações', 'Fechar', { duration: 3000 });
      }
    });
  }

  private buildFilters(): TransactionFilters {
    const form = this.filterForm.value;
    const filters: TransactionFilters = {
      page: this.currentPage,
      limit: this.pageSize
    };

    if (form.search) filters.search = form.search;
    if (form.type) filters.type = form.type;
    if (form.status) filters.status = form.status;
    if (form.date_from) filters.date_from = form.date_from;
    if (form.date_to) filters.date_to = form.date_to;
    if (form.amount_min) filters.amount_min = +form.amount_min;
    if (form.amount_max) filters.amount_max = +form.amount_max;

    return filters;
  }

  private calculateSummary(): void {
    this.summary = this.transactions.reduce((acc, t) => {
      if (t.status === 'completed') {
        if (t.type === 'income') acc.income += t.amount;
        if (t.type === 'expense') acc.expense += t.amount;
      }
      return acc;
    }, { income: 0, expense: 0, balance: 0 });
    this.summary.balance = this.summary.income - this.summary.expense;
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    this.loadTransactions();
  }

  navigateToNew(): void {
    this.router.navigate(['/transactions/new']);
  }

  navigateToDetail(id: number): void {
    this.router.navigate(['/transactions', id]);
  }

  navigateToEdit(id: number, event: Event): void {
    event.stopPropagation();
    this.router.navigate(['/transactions', id, 'edit']);
  }

  deleteTransaction(transaction: Transaction, event: Event): void {
    event.stopPropagation();
    this.dialogService.confirmDelete(transaction.description).subscribe(confirmed => {
      if (confirmed) {
        this.transactionService.delete(transaction.id).subscribe({
          next: () => {
            this.snackBar.open('Transação excluída com sucesso', 'Fechar', {
              duration: 3000,
              panelClass: ['success-snackbar']
            });
            this.loadTransactions();
          },
          error: () => {
            this.snackBar.open('Erro ao excluir transação', 'Fechar', { duration: 3000 });
          }
        });
      }
    });
  }

  clearFilters(): void {
    this.filterForm.reset({ search: '', type: '', status: '', date_from: '', date_to: '', amount_min: '', amount_max: '' });
  }

  toggleFilters(): void {
    this.showFilters = !this.showFilters;
  }

  getTypeIcon(type: string): string {
    return { income: 'arrow_downward', expense: 'arrow_upward', transfer: 'swap_horiz' }[type] || 'attach_money';
  }

  getTypeClass(type: string): string {
    return { income: 'type-income', expense: 'type-expense', transfer: 'type-transfer' }[type] || '';
  }

  getStatusClass(status: string): string {
    return { completed: 'status-completed', pending: 'status-pending', cancelled: 'status-cancelled' }[status] || '';
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
  }

  formatDate(dateStr: string): string {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');
  }

  hasActiveFilters(): boolean {
    const v = this.filterForm.value;
    return !!(v.search || v.type || v.status || v.date_from || v.date_to || v.amount_min || v.amount_max);
  }
}