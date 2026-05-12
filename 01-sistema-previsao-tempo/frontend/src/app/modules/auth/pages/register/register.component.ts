import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '@core/services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-register',
  template: `
    <div class="register-container">
      <div class="register-card">
        <div class="card-header">
          <span class="brand-icon">☁️</span>
          <h1>Criar Conta</h1>
          <p class="subtitle">Junte-se ao WeatherApp</p>
        </div>

        <form [formGroup]="form" (ngSubmit)="onRegister()">
          <div class="form-group">
            <label>Nome</label>
            <input 
              type="text" 
              formControlName="name" 
              placeholder="Seu nome"
              required
            >
            <p class="field-error" *ngIf="form.get('name')?.invalid && form.get('name')?.touched">
              Nome é obrigatório
            </p>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input 
              type="email" 
              formControlName="email" 
              placeholder="seu@email.com"
              required
            >
            <p class="field-error" *ngIf="form.get('email')?.invalid && form.get('email')?.touched">
              Email inválido
            </p>
          </div>

          <div class="form-group">
            <label>Senha</label>
            <input 
              type="password" 
              formControlName="password" 
              placeholder="Mínimo 6 caracteres"
              required
            >
            <p class="field-error" *ngIf="form.get('password')?.invalid && form.get('password')?.touched">
              Senha deve ter pelo menos 6 caracteres
            </p>
          </div>

          <button type="submit" class="btn-submit" [disabled]="form.invalid || loading">
            {{ loading ? 'Registando...' : 'Registar' }}
          </button>

          <div class="error-box" *ngIf="error">{{ error }}</div>
        </form>

        <p class="login-link">
          Já tem conta? <a routerLink="/auth/login">Entre aqui</a>
        </p>
      </div>
    </div>
  `,
  styles: [`
    .register-container {
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

    .register-card {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      padding: 2.5rem;
      border-radius: 20px;
      border: 1px solid rgba(74, 144, 217, 0.1);
      box-shadow:
        0 20px 60px rgba(26, 35, 50, 0.08),
        0 4px 16px rgba(26, 35, 50, 0.04);
      width: 100%;
      max-width: 420px;
      animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.94) translateY(10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .card-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .brand-icon {
      font-size: 2.5rem;
      display: block;
      margin-bottom: 0.75rem;
    }

    h1 {
      font-size: 1.75rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      background: linear-gradient(135deg, #4A90D9 0%, #2E6AB3 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.4rem;
    }

    .subtitle {
      color: #8896A6;
      font-size: 0.9rem;
      margin: 0;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    label {
      display: block;
      margin-bottom: 0.4rem;
      color: #4A5568;
      font-size: 0.8125rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }

    input {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1.5px solid rgba(74, 144, 217, 0.2);
      border-radius: 12px;
      font-size: 0.9375rem;
      background: rgba(255, 255, 255, 0.8);
      transition: all 250ms ease;
      color: #1A2332;
    }

    input::placeholder {
      color: #B0BEC5;
    }

    input:hover {
      border-color: rgba(74, 144, 217, 0.35);
    }

    input:focus {
      outline: none;
      border-color: #4A90D9;
      box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.12);
      background: white;
    }

    .btn-submit {
      width: 100%;
      padding: 0.85rem;
      background: linear-gradient(135deg, #4A90D9 0%, #67B8DE 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 0.9375rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 300ms ease;
      margin-top: 0.5rem;
      box-shadow: 0 4px 14px rgba(74, 144, 217, 0.25);
    }

    .btn-submit:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(74, 144, 217, 0.35);
    }

    .btn-submit:disabled {
      opacity: 0.55;
      cursor: not-allowed;
      transform: none;
    }

    .field-error {
      color: #E53E3E;
      font-size: 0.8rem;
      margin-top: 0.35rem;
      font-weight: 500;
    }

    .error-box {
      color: #E53E3E;
      font-size: 0.875rem;
      text-align: center;
      padding: 0.75rem 1rem;
      background: rgba(229, 62, 62, 0.06);
      border: 1px solid rgba(229, 62, 62, 0.15);
      border-radius: 10px;
      margin-top: 1rem;
      font-weight: 500;
    }

    .login-link {
      text-align: center;
      margin-top: 1.75rem;
      color: #8896A6;
      font-size: 0.875rem;
    }

    .login-link a {
      color: #4A90D9;
      text-decoration: none;
      font-weight: 700;
    }

    .login-link a:hover {
      color: #2E6AB3;
    }
  `]
})
export class RegisterComponent {
  form: FormGroup;
  loading = false;
  error = '';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.form = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onRegister(): void {
    if (this.form.invalid) return;

    this.loading = true;
    this.error = '';

    const { name, email, password } = this.form.value;

    this.authService.register(email, password, name).subscribe({
      next: (response) => {
        if (response.success) {
          this.router.navigate(['/auth/login']);
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Erro ao registar';
        this.loading = false;
      }
    });
  }
}
