import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { GoalService } from '../services/goal.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-goal-form',
  template: `
    <div class="page-container animate-fade-in">
      <div class="page-header">
        <button mat-icon-button (click)="onBack()"><mat-icon>arrow_back</mat-icon></button>
        <h1>{{ isEditing ? i18n.t('goals.edit') : i18n.t('goals.new') }}</h1>
      </div>
      <mat-card class="form-card">
        <form [formGroup]="form" (ngSubmit)="onSubmit()">
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('goals.name') }}</mat-label>
            <input matInput formControlName="name">
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('goals.targetAmount') }}</mat-label>
            <input matInput type="number" formControlName="target_amount" min="0">
            <mat-icon matPrefix>attach_money</mat-icon>
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('goals.currentAmount') }}</mat-label>
            <input matInput type="number" formControlName="current_amount" min="0">
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('goals.targetDate') }}</mat-label>
            <input matInput [matDatepicker]="picker" formControlName="target_date">
            <mat-datepicker-toggle matSuffix [for]="picker"></mat-datepicker-toggle>
            <mat-datepicker #picker></mat-datepicker>
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('goals.priority') }}</mat-label>
            <mat-select formControlName="priority">
              <mat-option value="low">{{ i18n.t('goals.low') }}</mat-option>
              <mat-option value="medium">{{ i18n.t('goals.medium') }}</mat-option>
              <mat-option value="high">{{ i18n.t('goals.high') }}</mat-option>
            </mat-select>
          </mat-form-field>
          <div class="form-actions">
            <button mat-button type="button" (click)="onBack()">{{ i18n.t('common.cancel') }}</button>
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
export class GoalFormComponent implements OnInit {
  form: FormGroup;
  isEditing = false;
  goalId: number | null = null;

  constructor(private fb: FormBuilder, private goalService: GoalService,
              private route: ActivatedRoute, private router: Router, public i18n: I18nService) {
    this.form = this.fb.group({
      name: ['', Validators.required], target_amount: [0, [Validators.required, Validators.min(1)]],
      current_amount: [0], target_date: [null], priority: ['medium']
    });
  }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) { this.isEditing = true; this.goalId = +id;
      this.goalService.getById(this.goalId).subscribe((res: any) => this.form.patchValue(res.data?.goal || {}));
    }
  }

  onSubmit(): void {
    if (this.form.invalid) return;
    const obs = this.isEditing ? this.goalService.update(this.goalId!, this.form.value) : this.goalService.create(this.form.value);
    obs.subscribe(() => this.router.navigate(['/goals']));
  }

  onBack(): void { this.router.navigate(['/goals']); }
}
