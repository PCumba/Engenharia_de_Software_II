import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../../environments/environment';
import { I18nService } from '../../../core/services/i18n.service';
import { NotificationService } from '../../../core/services/notification.service';

@Component({
  selector: 'app-profile-settings',
  template: `
    <div class="page-container animate-fade-in">
      <div class="page-header">
        <button mat-icon-button routerLink="/settings"><mat-icon>arrow_back</mat-icon></button>
        <h1>{{ i18n.t('settings.profile') }}</h1>
      </div>
      <mat-card class="form-card">
        <form [formGroup]="profileForm" (ngSubmit)="onSaveProfile()">
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('auth.name') }}</mat-label>
            <input matInput formControlName="name">
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('settings.currency') }}</mat-label>
            <mat-select formControlName="currency">
              <mat-option value="BRL">BRL (R$)</mat-option>
              <mat-option value="USD">USD ($)</mat-option>
              <mat-option value="EUR">EUR (€)</mat-option>
            </mat-select>
          </mat-form-field>
          <button mat-raised-button color="primary" type="submit" [disabled]="profileForm.invalid">
            {{ i18n.t('common.save') }}
          </button>
        </form>
      </mat-card>
      <mat-card class="form-card" style="margin-top: 1rem;">
        <h3>{{ i18n.t('settings.changePassword') }}</h3>
        <form [formGroup]="passwordForm" (ngSubmit)="onChangePassword()">
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('settings.currentPassword') }}</mat-label>
            <input matInput type="password" formControlName="current_password">
          </mat-form-field>
          <mat-form-field appearance="outline" class="full-width">
            <mat-label>{{ i18n.t('settings.newPassword') }}</mat-label>
            <input matInput type="password" formControlName="new_password">
          </mat-form-field>
          <button mat-raised-button color="accent" type="submit" [disabled]="passwordForm.invalid">
            {{ i18n.t('settings.changePassword') }}
          </button>
        </form>
      </mat-card>
    </div>
  `,
  styles: [`
    .page-container { padding: 1.5rem; max-width: 600px; margin: 0 auto; }
    .page-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
    .page-header h1 { font-size: 1.25rem; margin: 0; }
    .form-card { padding: 1.5rem; border-radius: var(--border-radius-lg); }
    .form-card h3 { margin: 0 0 1rem; font-size: 1.1rem; }
  `]
})
export class ProfileSettingsComponent implements OnInit {
  profileForm: FormGroup;
  passwordForm: FormGroup;

  constructor(private fb: FormBuilder, private http: HttpClient,
              public i18n: I18nService, private notification: NotificationService) {
    this.profileForm = this.fb.group({ name: ['', Validators.required], currency: ['BRL'] });
    this.passwordForm = this.fb.group({
      current_password: ['', Validators.required],
      new_password: ['', [Validators.required, Validators.minLength(8)]]
    });
  }

  ngOnInit(): void {
    this.http.get<any>(`${environment.apiUrl}/user/profile`).subscribe(res => {
      this.profileForm.patchValue(res.data?.user || {});
    });
  }

  onSaveProfile(): void {
    this.http.put(`${environment.apiUrl}/user/profile`, this.profileForm.value).subscribe(() => {
      this.notification.success(this.i18n.t('settings.saved'));
    });
  }

  onChangePassword(): void {
    this.http.post(`${environment.apiUrl}/user/change-password`, this.passwordForm.value).subscribe({
      next: () => { this.notification.success(this.i18n.t('settings.saved')); this.passwordForm.reset(); },
      error: () => this.notification.error(this.i18n.t('auth.resetError'))
    });
  }
}
