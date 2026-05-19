import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { BudgetService } from '../services/budget.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-budget-form',
  template: `
    <div class="page-container animate-fade-in">
      <div class="page-header">
        <button mat-icon-button (click)="onBack()"><mat-icon>arrow_back</mat-icon></button>
        <h1>{{ isEditing ? i18n.t('budgets.edit') : i18n.t('budgets.new') }}</h1>
      </div>
      <mat-card class="form-card">
        <form [formGroup]="form" (ngSubmit)="onSubmit()">
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('budgets.name') }}</mat-label>
            <input matInput formControlName="name">
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('budgets.amount') }}</mat-label>
            <input matInput type="number" formControlName="amount" min="0" step="0.01">
            <mat-icon matPrefix>attach_money</mat-icon>
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('budgets.period') }}</mat-label>
            <mat-select formControlName="period">
              <mat-option value="weekly">{{ i18n.t('budgets.weekly') }}</mat-option>
              <mat-option value="monthly">{{ i18n.t('budgets.monthly') }}</mat-option>
              <mat-option value="quarterly">{{ i18n.t('budgets.quarterly') }}</mat-option>
              <mat-option value="yearly">{{ i18n.t('budgets.yearly') }}</mat-option>
            </mat-select>
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('budgets.alertAt') }} (%)</mat-label>
            <input matInput type="number" formControlName="alert_percentage" min="50" max="100">
          </mat-form-field>
          <div class="form-actions">
            <button mat-button type="button" (click)="onBack()">{{ i18n.t('common.cancel') }}</button>
            <button mat-raised-button color="primary" type="submit" [disabled]="form.invalid || isLoading">
              {{ i18n.t('common.save') }}
            </button>
          </div>
        </form>
      </mat-card>
    </div>
  `,
  styles: [`
    .page-container { padding: 1.5rem; max-width: 600px; margin: 0 auto; }
    .page-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
    .page-header h1 { font-size: 1.25rem; margin: 0; }
    .form-card { padding: 1.5rem; border-radius: var(--border-radius-lg); }
    .form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem; }
  `]
})
export class BudgetFormComponent implements OnInit {
  form: FormGroup;
  isEditing = false;
  isLoading = false;
  budgetId: number | null = null;

  constructor(private fb: FormBuilder, private budgetService: BudgetService,
              private route: ActivatedRoute, private router: Router, public i18n: I18nService) {
    this.form = this.fb.group({
      name: ['', Validators.required],
      amount: [0, [Validators.required, Validators.min(1)]],
      period: ['monthly'],
      alert_percentage: [80, [Validators.min(50), Validators.max(100)]]
    });
  }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.isEditing = true;
      this.budgetId = +id;
      this.budgetService.getById(this.budgetId).subscribe((res: any) => {
        this.form.patchValue(res.data?.budget || {});
      });
    }
  }

  onSubmit(): void {
    if (this.form.invalid) return;
    this.isLoading = true;
    const obs = this.isEditing
      ? this.budgetService.update(this.budgetId!, this.form.value)
      : this.budgetService.create(this.form.value);
    obs.subscribe({ next: () => this.router.navigate(['/budgets']), error: () => { this.isLoading = false; } });
  }

  onBack(): void { this.router.navigate(['/budgets']); }
}
