import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';

import { TransactionService } from '../services/transaction.service';

@Component({
  selector: 'app-transaction-form',
  templateUrl: './transaction-form.component.html',
  styleUrls: ['./transaction-form.component.scss']
})
export class TransactionFormComponent implements OnInit, OnDestroy {
  private destroy$ = new Subject<void>();

  form: FormGroup;
  isEditMode = false;
  transactionId: number | null = null;
  isLoading = false;
  isSaving = false;

  typeOptions = [
    { value: 'income', label: 'Receita', icon: 'trending_up' },
    { value: 'expense', label: 'Despesa', icon: 'trending_down' },
    { value: 'transfer', label: 'Transferência', icon: 'swap_horiz' }
  ];

  paymentMethods = [
    { value: 'cash', label: 'Dinheiro' },
    { value: 'debit_card', label: 'Cartão de Débito' },
    { value: 'credit_card', label: 'Cartão de Crédito' },
    { value: 'bank_transfer', label: 'Transferência Bancária' },
    { value: 'pix', label: 'PIX' },
    { value: 'check', label: 'Cheque' },
    { value: 'other', label: 'Outros' }
  ];

  recurringFrequencies = [
    { value: 'daily', label: 'Diário' },
    { value: 'weekly', label: 'Semanal' },
    { value: 'monthly', label: 'Mensal' },
    { value: 'quarterly', label: 'Trimestral' },
    { value: 'yearly', label: 'Anual' }
  ];

  // Dados para selects (seriam carregados da API)
  accounts: any[] = [];
  categories: any[] = [];

  constructor(
    private fb: FormBuilder,
    private transactionService: TransactionService,
    private router: Router,
    private route: ActivatedRoute,
    private snackBar: MatSnackBar
  ) {
    this.form = this.createForm();
  }

  ngOnInit(): void {
    this.transactionId = this.route.snapshot.params['id'] ? +this.route.snapshot.params['id'] : null;
    this.isEditMode = !!this.transactionId && !this.router.url.endsWith('/new');

    if (this.isEditMode && this.transactionId) {
      this.loadTransaction(this.transactionId);
    }

    // Escutar mudança no tipo para ajustar validações
    this.form.get('type')?.valueChanges.pipe(takeUntil(this.destroy$)).subscribe(() => {
      this.updateCategoryValidation();
    });

    // Escutar toggle de recorrência
    this.form.get('is_recurring')?.valueChanges.pipe(takeUntil(this.destroy$)).subscribe(val => {
      const freqCtrl = this.form.get('recurring_frequency');
      if (val) {
        freqCtrl?.setValidators([Validators.required]);
      } else {
        freqCtrl?.clearValidators();
      }
      freqCtrl?.updateValueAndValidity();
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private createForm(): FormGroup {
    return this.fb.group({
      type: ['expense', Validators.required],
      account_id: ['', Validators.required],
      category_id: [''],
      amount: ['', [Validators.required, Validators.min(0.01)]],
      description: ['', [Validators.required, Validators.maxLength(500)]],
      transaction_date: [new Date().toISOString().split('T')[0], Validators.required],
      payment_method: ['cash'],
      reference_number: [''],
      location: [''],
      notes: [''],
      status: ['completed'],
      is_recurring: [false],
      recurring_frequency: [''],
      recurring_end_date: ['']
    });
  }

  private updateCategoryValidation(): void {
    const type = this.form.get('type')?.value;
    const categoryCtrl = this.form.get('category_id');
    if (type === 'transfer') {
      categoryCtrl?.clearValidators();
    }
    categoryCtrl?.updateValueAndValidity();
  }

  private loadTransaction(id: number): void {
    this.isLoading = true;
    this.transactionService.getById(id).pipe(takeUntil(this.destroy$)).subscribe({
      next: (transaction) => {
        this.form.patchValue({
          ...transaction,
          transaction_date: transaction.transaction_date
        });
        this.isLoading = false;
      },
      error: () => {
        this.isLoading = false;
        this.snackBar.open('Erro ao carregar transação', 'Fechar', { duration: 3000 });
        this.router.navigate(['/transactions']);
      }
    });
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.isSaving = true;
    const data = this.form.value;

    // Limpar campos vazios
    if (!data.is_recurring) {
      delete data.recurring_frequency;
      delete data.recurring_end_date;
    }

    const request$ = this.isEditMode && this.transactionId
      ? this.transactionService.update(this.transactionId, data)
      : this.transactionService.create(data);

    request$.pipe(takeUntil(this.destroy$)).subscribe({
      next: () => {
        this.isSaving = false;
        this.snackBar.open(
          this.isEditMode ? 'Transação atualizada!' : 'Transação criada!',
          'Fechar',
          { duration: 3000, panelClass: ['success-snackbar'] }
        );
        this.router.navigate(['/transactions']);
      },
      error: (err) => {
        this.isSaving = false;
        const msg = err.error?.message || 'Erro ao salvar transação';
        this.snackBar.open(msg, 'Fechar', { duration: 4000 });
      }
    });
  }

  cancel(): void {
    this.router.navigate(['/transactions']);
  }

  getError(field: string): string {
    const ctrl = this.form.get(field);
    if (ctrl?.hasError('required')) return 'Campo obrigatório';
    if (ctrl?.hasError('min')) return 'Valor deve ser maior que zero';
    if (ctrl?.hasError('maxlength')) return `Máximo ${ctrl.errors?.['maxlength'].requiredLength} caracteres`;
    return '';
  }

  get isRecurring(): boolean {
    return this.form.get('is_recurring')?.value;
  }

  get selectedType(): string {
    return this.form.get('type')?.value;
  }
}