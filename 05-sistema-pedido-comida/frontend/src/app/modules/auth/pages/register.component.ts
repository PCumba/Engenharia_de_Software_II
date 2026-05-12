import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-register',
  template: `
    <div class="auth-container">
      <div class="auth-card">
        <h1>🍕 Food Delivery</h1>
        <p class="subtitle">Criar Nova Conta</p>
        
        <form [formGroup]="registerForm" (ngSubmit)="onSubmit()">
          <div class="form-group">
            <input type="text" formControlName="name" placeholder="Nome Completo" required>
          </div>
          <div class="form-group">
            <input type="email" formControlName="email" placeholder="Email" required>
          </div>
          <div class="form-group">
            <input type="tel" formControlName="phone" placeholder="Telefone" required>
          </div>
          <div class="form-group">
            <input type="text" formControlName="address" placeholder="Endereço" required>
          </div>
          <div class="form-group">
            <input type="password" formControlName="password" placeholder="Password" required>
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
      background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
    }
    .auth-card {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      width: 100%;
      max-width: 400px;
    }
    h1 { text-align: center; }
    .form-group { margin-bottom: 1rem; }
    input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    button {
      width: 100%;
      padding: 0.75rem;
      background: #FF6B6B;
      color: white;
      border: none;
      cursor: pointer;
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
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', Validators.required],
      address: ['', Validators.required],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit(): void {
    this.loading = true;
    this.authService.register(this.registerForm.value).subscribe({
      next: () => {
        alert('Registado! Faça login agora.');
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
