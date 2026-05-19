import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { I18nService } from '../../core/services/i18n.service';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss']
})
export class ForgotPasswordComponent {
  forgotForm: FormGroup;
  resetForm: FormGroup;
  isLoading = false;
  errorMessage = '';
  successMessage = '';
  showResetForm = false;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    public i18n: I18nService
  ) {
    this.forgotForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });

    this.resetForm = this.fb.group({
      token: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required]
    });
  }

  onSubmitEmail(): void {
    if (this.forgotForm.invalid) return;

    this.isLoading = true;
    this.errorMessage = '';
    this.successMessage = '';

    this.authService.forgotPassword(this.forgotForm.get('email')!.value).subscribe({
      next: () => {
        this.isLoading = false;
        this.successMessage = this.i18n.t('auth.resetEmailSent');
        this.showResetForm = true;
        this.resetForm.patchValue({ email: this.forgotForm.get('email')!.value });
      },
      error: (error) => {
        this.isLoading = false;
        this.errorMessage = error.message || this.i18n.t('auth.resetError');
      }
    });
  }

  onSubmitReset(): void {
    if (this.resetForm.invalid) return;

    this.isLoading = true;
    this.errorMessage = '';

    const { token, password, email } = this.resetForm.value;
    this.authService.resetPassword(token, password, email).subscribe({
      next: () => {
        this.isLoading = false;
        this.router.navigate(['/auth/login'], {
          queryParams: { message: 'password_reset_success' }
        });
      },
      error: (error) => {
        this.isLoading = false;
        this.errorMessage = error.message || this.i18n.t('auth.resetError');
      }
    });
  }
}
