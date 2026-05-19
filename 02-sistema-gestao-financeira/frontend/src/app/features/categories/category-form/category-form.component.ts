import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { CategoryService } from '../services/category.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-category-form',
  template: `
    <div class="page-container animate-fade-in">
      <div class="page-header">
        <button mat-icon-button (click)="router.navigate(['/categories'])"><mat-icon>arrow_back</mat-icon></button>
        <h1>{{ isEditing ? i18n.t('categories.edit') : i18n.t('categories.new') }}</h1>
      </div>
      <mat-card class="form-card">
        <form [formGroup]="form" (ngSubmit)="onSubmit()">
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('categories.name') }}</mat-label>
            <input matInput formControlName="name">
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('categories.type') }}</mat-label>
            <mat-select formControlName="type">
              <mat-option value="expense">{{ i18n.t('transactions.expense') }}</mat-option>
              <mat-option value="income">{{ i18n.t('transactions.income') }}</mat-option>
              <mat-option value="both">{{ i18n.t('common.all') }}</mat-option>
            </mat-select>
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('categories.color') }}</mat-label>
            <input matInput type="color" formControlName="color">
          </mat-form-field>
          <div class="form-actions">
            <button mat-button type="button" (click)="router.navigate(['/categories'])">{{ i18n.t('common.cancel') }}</button>
            <button mat-raised-button color="primary" type="submit" [disabled]="form.invalid">{{ i18n.t('common.save') }}</button>
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
export class CategoryFormComponent implements OnInit {
  form: FormGroup;
  isEditing = false;
  categoryId: number | null = null;

  constructor(private fb: FormBuilder, private categoryService: CategoryService,
              private route: ActivatedRoute, public router: Router, public i18n: I18nService) {
    this.form = this.fb.group({
      name: ['', Validators.required], type: ['expense'], color: ['#6c757d']
    });
  }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) { this.isEditing = true; this.categoryId = +id;
      this.categoryService.getById(this.categoryId).subscribe((res: any) => this.form.patchValue(res.data?.category || {}));
    }
  }

  onSubmit(): void {
    if (this.form.invalid) return;
    const obs = this.isEditing ? this.categoryService.update(this.categoryId!, this.form.value) : this.categoryService.create(this.form.value);
    obs.subscribe(() => this.router.navigate(['/categories']));
  }
}
