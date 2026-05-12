import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-register',
  template: `
    <div class="auth-container">
      <div class="auth-card">
        <div class="card-header"><span class="brand-icon">◈</span><h1>Criar Conta</h1><p class="subtitle">Junte-se ao CryptoMonitor</p></div>
        <form [formGroup]="registerForm" (ngSubmit)="onSubmit()">
          <div class="form-group"><label>Nome</label><input type="text" formControlName="name" placeholder="Seu nome"><span class="error" *ngIf="registerForm.get('name')?.invalid && registerForm.get('name')?.touched">Nome obrigatório (mín 3 caracteres)</span></div>
          <div class="form-group"><label>Email</label><input type="email" formControlName="email" placeholder="seu@email.com"><span class="error" *ngIf="registerForm.get('email')?.invalid && registerForm.get('email')?.touched">Email inválido</span></div>
          <div class="form-group"><label>Password</label><input type="password" formControlName="password" placeholder="Mínimo 6 caracteres"><span class="error" *ngIf="registerForm.get('password')?.invalid && registerForm.get('password')?.touched">Password obrigatória (mín 6 caracteres)</span></div>
          <button type="submit" class="btn-submit" [disabled]="!registerForm.valid || loading">{{ loading ? 'A registar...' : 'Registar' }}</button>
        </form>
        <div class="error-box" *ngIf="error">{{ error }}</div>
        <p class="link">Já tens conta? <a routerLink="/auth/login">Fazer login</a></p>
      </div>
    </div>
  `,
  styles: [`
    .auth-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #0D1117; background-image: radial-gradient(ellipse at 30% 20%, rgba(0,212,170,0.06) 0%, transparent 50%), radial-gradient(ellipse at 70% 80%, rgba(123,97,255,0.05) 0%, transparent 50%); padding: 2rem; }
    .auth-card { background: rgba(33,38,45,0.9); backdrop-filter: blur(20px); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(48,54,61,0.8); box-shadow: 0 20px 60px rgba(0,0,0,0.4); width: 100%; max-width: 420px; animation: scaleIn 0.5s cubic-bezier(0.34,1.56,0.64,1); }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.94) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .card-header { text-align: center; margin-bottom: 2rem; }
    .brand-icon { font-size: 2.5rem; color: #00D4AA; display: block; margin-bottom: 0.75rem; }
    h1 { font-size: 1.75rem; font-weight: 800; background: linear-gradient(135deg, #00D4AA 0%, #7B61FF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.4rem; }
    .subtitle { color: #6E7681; font-size: 0.9rem; margin: 0; }
    .form-group { margin-bottom: 1.25rem; }
    label { display: block; margin-bottom: 0.4rem; color: #8B949E; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    input { width: 100%; padding: 0.8rem 1rem; border: 1.5px solid rgba(48,54,61,0.8); border-radius: 12px; font-size: 0.9375rem; background: rgba(22,27,34,0.8); color: #E6EDF3; transition: all 250ms ease; }
    input::placeholder { color: #6E7681; }
    input:focus { outline: none; border-color: #00D4AA; box-shadow: 0 0 0 3px rgba(0,212,170,0.1); background: #161B22; }
    .error { color: #F85149; font-size: 0.8rem; margin-top: 0.3rem; display: block; }
    .btn-submit { width: 100%; padding: 0.85rem; background: linear-gradient(135deg, #00D4AA 0%, #00B894 100%); color: #0D1117; border: none; border-radius: 12px; font-size: 0.9375rem; font-weight: 700; cursor: pointer; transition: all 300ms ease; margin-top: 0.5rem; box-shadow: 0 4px 14px rgba(0,212,170,0.25); }
    .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,212,170,0.35); }
    .btn-submit:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .error-box { color: #F85149; font-size: 0.875rem; text-align: center; padding: 0.75rem 1rem; background: rgba(248,81,73,0.08); border: 1px solid rgba(248,81,73,0.15); border-radius: 10px; margin-top: 1rem; }
    .link { text-align: center; margin-top: 1.75rem; color: #6E7681; font-size: 0.875rem; }
    .link a { color: #00D4AA; font-weight: 700; }
  `]
})
export class RegisterComponent implements OnInit {
  registerForm!: FormGroup; loading = false; error = '';
  constructor(private fb: FormBuilder, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { this.registerForm = this.fb.group({ name: ['', [Validators.required, Validators.minLength(3)]], email: ['', [Validators.required, Validators.email]], password: ['', [Validators.required, Validators.minLength(6)]] }); }
  onSubmit(): void {
    if (!this.registerForm.valid) return;
    this.loading = true; this.error = '';
    this.authService.register(this.registerForm.value).subscribe({ next: () => { this.router.navigate(['/auth/login']); this.loading = false; }, error: (err) => { this.error = err.error?.message || 'Erro ao registar'; this.loading = false; } });
  }
}
