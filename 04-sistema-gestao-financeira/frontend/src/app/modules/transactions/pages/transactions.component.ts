import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { FinanceService } from '@core/services/finance.service';

@Component({
  selector: 'app-transactions',
  template: `
    <div class="transactions">
      <h2>Transações</h2>
      <form [formGroup]="transactionForm" (ngSubmit)="addTransaction()" class="form-inline">
        <input type="text" formControlName="description" placeholder="Descrição">
        <input type="number" formControlName="amount" placeholder="Valor">
        <select formControlName="type"><option value="income">Receita</option><option value="expense">Despesa</option></select>
        <input type="date" formControlName="date">
        <button type="submit" class="btn-add" [disabled]="!transactionForm.valid">Adicionar</button>
      </form>
      <div class="transactions-list">
        <div class="transaction-item" *ngFor="let t of transactions" [class]="t.type">
          <div class="info"><p class="description">{{ t.description }}</p><p class="category">{{ t.category_name }}</p></div>
          <p class="amount">{{ t.type === 'income' ? '+' : '-' }} {{ t.amount | currency }}</p>
          <button (click)="deleteTransaction(t.id)" class="btn-delete">🗑️</button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .transactions { background: white; padding: 1.75rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 2px 12px rgba(26,26,46,0.04); }
    h2 { font-size: 1.15rem; font-weight: 700; margin: 0 0 1.25rem; }
    .form-inline { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 0.5rem; margin-bottom: 1.5rem; }
    .form-inline input, .form-inline select { padding: 0.65rem 0.875rem; border: 1.5px solid rgba(108,92,231,0.12); border-radius: 10px; font-size: 0.875rem; transition: all 250ms; background: white; color: #1A1A2E; }
    .form-inline input:focus, .form-inline select:focus { border-color: #6C5CE7; box-shadow: 0 0 0 3px rgba(108,92,231,0.08); outline: none; }
    .btn-add { padding: 0.65rem 1.25rem; background: linear-gradient(135deg, #6C5CE7, #A29BFE); color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 300ms; box-shadow: 0 3px 10px rgba(108,92,231,0.2); }
    .btn-add:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(108,92,231,0.3); }
    .transactions-list { display: flex; flex-direction: column; gap: 0.5rem; }
    .transaction-item { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 1rem; background: #FAFAFE; border-radius: 12px; border-left: 4px solid #6C5CE7; transition: all 200ms; }
    .transaction-item:hover { background: #F0EDFF; transform: translateX(2px); }
    .transaction-item.income { border-left-color: #00B894; }
    .transaction-item.expense { border-left-color: #E17055; }
    .info { flex: 1; }
    .description { margin: 0; font-weight: 600; font-size: 0.9rem; color: #1A1A2E; }
    .category { margin: 0.15rem 0 0; font-size: 0.8rem; color: #9696B4; }
    .amount { font-weight: 800; font-size: 1rem; margin: 0 1rem; white-space: nowrap; }
    .transaction-item.income .amount { color: #00B894; }
    .transaction-item.expense .amount { color: #E17055; }
    .btn-delete { padding: 0.35rem 0.5rem; background: rgba(225,112,85,0.08); border: 1px solid rgba(225,112,85,0.12); border-radius: 8px; cursor: pointer; font-size: 0.8rem; transition: all 200ms; }
    .btn-delete:hover { background: rgba(225,112,85,0.15); transform: none; }
    @media (max-width: 768px) { .form-inline { grid-template-columns: 1fr 1fr; } }
  `]
})
export class TransactionsComponent implements OnInit {
  transactionForm!: FormGroup; transactions: any[] = [];
  constructor(private fb: FormBuilder, private financeService: FinanceService) {}
  ngOnInit(): void { this.transactionForm = this.fb.group({ description: ['', Validators.required], amount: ['', Validators.required], type: ['expense'], date: [new Date().toISOString().split('T')[0]] }); this.loadTransactions(); }
  loadTransactions(): void { this.financeService.getRecentTransactions().subscribe({ next: (response) => { if (response.success) { this.transactions = response.data; } } }); }
  addTransaction(): void { if (this.transactionForm.valid) { this.financeService.createTransaction(this.transactionForm.value).subscribe({ next: () => { this.transactionForm.reset({ date: new Date().toISOString().split('T')[0], type: 'expense' }); this.loadTransactions(); } }); } }
  deleteTransaction(id: number): void { if (confirm('Tem certeza?')) { this.financeService.deleteTransaction(id).subscribe({ next: () => this.loadTransactions() }); } }
}
