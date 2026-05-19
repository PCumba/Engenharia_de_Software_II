import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '@core/services/auth.service';
import { Router } from '@angular/router';
import { I18nService } from '@core/services/i18n.service';

@Component({
  selector: 'app-forgot-password',
  template: `
    <div class="forgot-container">
      <div class="forgot-card">
        <div class="card-header">
          <span class="brand-icon">🔐</span>
          <h1>{{ i18n.t('auth.recoverPassword') }}</h1>
          <p class="subtitle">{{ step === 1 ? i18n.t('auth.recoverSubtitle') : i18n.t('auth.newPasswordSubtitle') }}</p>
        </div>

        <!-- Step 1: Email -->
        <form *ngIf="step === 1" [formGroup]="emailForm" (ngSubmit)="onRequestReset()">
          <div class="form-group">
            <label>{{ i18n.t('auth.email') }}</label>
            <input
              type="email"
              formControlName="email"
              placeholder="seu@email.com"
              required
            >
            <p class="field-error" *ngIf="emailForm.get('email')?.invalid && emailForm.get('email')?.touched">
              {{ i18n.t('validation.invalidEmail') }}
            </p>
          </div>

          <button type="submit" class="btn-submit" [disabled]="emailForm.invalid || loading">
            {{ loading ? i18n.t('common.sending') : i18n.t('auth.sendCode') }}
          </button>

          <div class="success-box" *ngIf="successMessage">{{ successMessage }}</div>
          <div class="error-box" *ngIf="error">{{ error }}</div>
        </form>

        <!-- Step 2: Token + New Password -->
        <form *ngIf="step === 2" [formGroup]="resetForm" (ngSubmit)="onResetPassword()">
          <div class="form-group">
            <label>{{ i18n.t('auth.resetToken') }}</label>
            <input
              type="text"
              formControlName="token"
              [placeholder]="i18n.t('auth.tokenPlaceholder')"
              required
            >
          </div>

          <div class="form-group">
            <label>{{ i18n.t('auth.newPassword') }}</label>
            <input
              type="password"
              formControlName="password"
              [placeholder]="i18n.t('auth.minChars')"
              required
            >
            <p class="field-error" *ngIf="resetForm.get('password')?.invalid && resetForm.get('password')?.touched">
              {{ i18n.t('validation.minLength') }}
            </p>
          </div>

          <div class="form-group">
            <label>{{ i18n.t('auth.confirmPassword') }}</label>
            <input
              type="password"
              formControlName="confirmPassword"
              [placeholder]="i18n.t('auth.confirmPassword')"
              required
            >
            <p class="field-error" *ngIf="resetForm.hasError('mismatch')">
              {{ i18n.t('validation.passwordMismatch') }}
            </p>
          </div>

          <button type="submit" class="btn-submit" [disabled]="resetForm.invalid || loading">
            {{ loading ? i18n.t('common.saving') : i18n.t('auth.resetPassword') }}
          </button>

          <div class="success-box" *ngIf="successMessage">{{ successMessage }}</div>
          <div class="error-box" *ngIf="error">{{ error }}</div>
        </form>

        <div class="step-toggle">
          <button *ngIf="step === 1" type="button" class="btn-link" (click)="step = 2">
            {{ i18n.t('auth.alreadyHaveToken') }}
          </button>
          <button *ngIf="step === 2" type="button" class="btn-link" (click)="step = 1">
            ← {{ i18n.t('auth.requestNewCode') }}
          </button>
        </div>

        <p class="login-link">
          <a routerLink="/auth/login">← {{ i18n.t('auth.backToLogin') }}</a>
        </p>
      </div>
    </div>
  `,
  styles: [`
    .forgot-container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background:
        radial-gradient(ellipse at 30% 20%, rgba(74, 144, 217, 0.15) 0%, transparent 50%),
        radial-gradient(ellipse at 70% 80%, rgba(103, 184, 222, 0.10) 0%, transparent 50%),
        linear-gradient(160deg, #E8F0FE 0%, #F0F4F8 30%, #FDF5E6 100%);
      padding: 2rem;
    }

    .forgot-card {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      padding: 2.5rem;
      border-radius: 20px;
      border: 1px solid rgba(74, 144, 217, 0.1);
      box-shadow: 0 20px 60px rgba(26, 35, 50, 0.08), 0 4px 16px rgba(26, 35, 50, 0.04);
      width: 100%;
      max-width: 420px;
      animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.94) translateY(10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .card-header { text-align: center; margin-bottom: 2rem; }
    .brand-icon { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; }
    h1 { font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #4A90D9 0%, #2E6AB3 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.4rem; }
    .subtitle { color: #8896A6; font-size: 0.9rem; margin: 0; }

    .form-group { margin-bottom: 1.25rem; }
    label { display: block; margin-bottom: 0.4rem; color: #4A5568; font-size: 0.8125rem; font-weight: 600; letter-spacing: 0.02em; text-transform: uppercase; }
    input { width: 100%; padding: 0.8rem 1rem; border: 1.5px solid rgba(74, 144, 217, 0.2); border-radius: 12px; font-size: 0.9375rem; background: rgba(255, 255, 255, 0.8); transition: all 250ms ease; color: #1A2332; }
    input::placeholder { color: #B0BEC5; }
    input:hover { border-color: rgba(74, 144, 217, 0.35); }
    input:focus { outline: none; border-color: #4A90D9; box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.12); background: white; }

    .btn-submit { width: 100%; padding: 0.85rem; background: linear-gradient(135deg, #4A90D9 0%, #67B8DE 100%); color: white; border: none; border-radius: 12px; font-size: 0.9375rem; font-weight: 700; cursor: pointer; transition: all 300ms ease; margin-top: 0.5rem; box-shadow: 0 4px 14px rgba(74, 144, 217, 0.25); }
    .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(74, 144, 217, 0.35); }
    .btn-submit:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }

    .field-error { color: #E53E3E; font-size: 0.8rem; margin-top: 0.35rem; font-weight: 500; }
    .error-box { color: #E53E3E; font-size: 0.875rem; text-align: center; padding: 0.75rem 1rem; background: rgba(229, 62, 62, 0.06); border: 1px solid rgba(229, 62, 62, 0.15); border-radius: 10px; margin-top: 1rem; font-weight: 500; }
    .success-box { color: #38B2AC; font-size: 0.875rem; text-align: center; padding: 0.75rem 1rem; background: rgba(56, 178, 172, 0.06); border: 1px solid rgba(56, 178, 172, 0.15); border-radius: 10px; margin-top: 1rem; font-weight: 500; }

    .step-toggle { text-align: center; margin-top: 1rem; }
    .btn-link { background: none; border: none; color: #4A90D9; font-size: 0.875rem; font-weight: 600; cursor: pointer; padding: 0.5rem; }
    .btn-link:hover { color: #2E6AB3; transform: none; }

    .login-link { text-align: center; margin-top: 1.5rem; }
    .login-link a { color: #4A90D9; text-decoration: none; font-weight: 700; font-size: 0.875rem; }
    .login-link a:hover { color: #2E6AB3; }
  `]
})
export class ForgotPasswordComponent {
  step = 1;
  loading = false;
  error = '';
  successMessage = '';

  emailForm: FormGroup;
  resetForm: FormGroup;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    public i18n: I18nService
  ) {
    this.emailForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });

    this.resetForm = this.fb.group({
      token: ['', [Validators.required]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      confirmPassword: ['', [Validators.required]]
    }, { validators: this.passwordMatchValidator });
  }

  passwordMatchValidator(g: FormGroup) {
    const password = g.get('password')?.value;
    const confirm = g.get('confirmPassword')?.value;
    return password === confirm ? null : { mismatch: true };
  }

  onRequestReset(): void {
    if (this.emailForm.invalid) return;
    this.loading = true;
    this.error = '';
    this.successMessage = '';

    this.authService.forgotPassword(this.emailForm.value.email).subscribe({
      next: (res) => {
        this.loading = false;
        this.successMessage = res.message || this.i18n.t('auth.resetEmailSent');
        this.step = 2;
      },
      error: (err) => {
        this.loading = false;
        this.error = err.error?.message || this.i18n.t('common.error');
      }
    });
  }

  onResetPassword(): void {
    if (this.resetForm.invalid) return;
    this.loading = true;
    this.error = '';
    this.successMessage = '';

    const { token, password } = this.resetForm.value;

    this.authService.resetPassword(token, password).subscribe({
      next: (res) => {
        this.loading = false;
        this.successMessage = res.message || this.i18n.t('auth.passwordChanged');
        setTimeout(() => this.router.navigate(['/auth/login']), 2000);
      },
      error: (err) => {
        this.loading = false;
        this.error = err.error?.message || this.i18n.t('common.error');
      }
    });
  }
}
