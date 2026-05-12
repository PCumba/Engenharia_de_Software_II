import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-register',
  template: `
    <div class="auth-container">
      <div class="auth-card">
        <h1>🍽️ Fila Refeitório</h1>
        <p class="subtitle">Criar Nova Conta</p>
        
        <form [formGroup]="registerForm" (ngSubmit)="onSubmit()">
          <div class="form-group">
            <label>Nome:</label>
            <input type="text" formControlName="name" placeholder="Seu nome completo">
            <span class="error" *ngIf="registerForm.get('name')?.invalid && registerForm.get('name')?.touched">
              Nome obrigatório (mínimo 3 caracteres)
            </span>
          </div>

          <div class="form-group">
            <label>Email:</label>
            <input type="email" formControlName="email" placeholder="seu@email.com">
            <span class="error" *ngIf="registerForm.get('email')?.invalid && registerForm.get('email')?.touched">
              Email inválido
            </span>
          </div>

          <div class="form-group">
            <label>Password:</label>
            <input type="password" formControlName="password" placeholder="Mínimo 6 caracteres">
            <span class="error" *ngIf="registerForm.get('password')?.invalid && registerForm.get('password')?.touched">
              Password deve ter mínimo 6 caracteres
            </span>
          </div>

          <button type="submit" [disabled]="!registerForm.valid || loading">
            {{ loading ? 'A registar...' : 'Registar' }}
          </button>
        </form>

        <div class="error" *ngIf="error">{{ error }}</div>

        <p>Já tens conta? <a routerLink="/auth/login">Fazer login</a></p>
      </div>
    </div>
  `,
  styles: [`
    .auth-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .auth-card {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
    }

    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 0.5rem;
    }

    .subtitle {
      text-align: center;
      color: #999;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      color: #333;
      font-weight: 600;
    }

    input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    input:focus {
      outline: none;
      border-color: #667eea;
    }

    .error {
      color: #dc3545;
      font-size: 0.85rem;
      margin-top: 0.25rem;
    }

    button {
      width: 100%;
      padding: 0.75rem;
      background: #667eea;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 1rem;
    }

    button:hover:not(:disabled) {
      background: #5568d3;
    }

    button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    p {
      text-align: center;
      margin-top: 1rem;
      color: #666;
    }

    a {
      color: #667eea;
      text-decoration: none;
    }
  `]
})
export class RegisterComponent implements OnInit {
  registerForm!: FormGroup;
  loading = false;
  error = '';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.registerForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit(): void {
    if (!this.registerForm.valid) return;

    this.loading = true;
    this.error = '';

    this.authService.register(this.registerForm.value).subscribe({
      next: () => {
        alert('Registado com sucesso! Agora faça login.');
        this.router.navigate(['/auth/login']);
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Erro ao registar';
        this.loading = false;
      }
    });
  }
}
