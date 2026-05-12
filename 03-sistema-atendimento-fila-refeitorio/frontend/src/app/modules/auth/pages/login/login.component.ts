import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-login',
  template: `
    <div class="auth-container">
      <div class="auth-card">
        <div class="card-header"><span class="brand-icon">🍽️</span><h1>Fila Refeitório</h1><p class="subtitle">Sistema de Gerenciamento de Filas</p></div>
        <form [formGroup]="loginForm" (ngSubmit)="onSubmit()">
          <div class="form-group"><label>Email</label><input type="email" formControlName="email" placeholder="seu@email.com"><span class="error" *ngIf="loginForm.get('email')?.invalid && loginForm.get('email')?.touched">Email inválido</span></div>
          <div class="form-group"><label>Password</label><input type="password" formControlName="password" placeholder="••••••••"><span class="error" *ngIf="loginForm.get('password')?.invalid && loginForm.get('password')?.touched">Password obrigatória</span></div>
          <button type="submit" class="btn-submit" [disabled]="!loginForm.valid || loading">{{ loading ? 'A fazer login...' : 'Entrar' }}</button>
        </form>
        <div class="error-box" *ngIf="error">{{ error }}</div>
        <p class="link">Não tens conta? <a routerLink="/auth/register">Registar aqui</a></p>
      </div>
    </div>
  `,
  styles: [`
    .auth-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(160deg, #FFF3E0 0%, #FFE0B2 30%, #FFCC80 100%); background-image: radial-gradient(ellipse at 30% 20%, rgba(255,107,53,0.1) 0%, transparent 50%), radial-gradient(ellipse at 70% 80%, rgba(247,201,72,0.08) 0%, transparent 50%), linear-gradient(160deg, #FFF3E0 0%, #FFE0B2 30%, #FFCC80 100%); padding: 2rem; }
    .auth-card { background: rgba(255,255,255,0.88); backdrop-filter: blur(20px) saturate(180%); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,107,53,0.1); box-shadow: 0 20px 60px rgba(139,69,19,0.08), 0 4px 16px rgba(139,69,19,0.04); width: 100%; max-width: 420px; animation: scaleIn 0.5s cubic-bezier(0.34,1.56,0.64,1); }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.94) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .card-header { text-align: center; margin-bottom: 2rem; }
    .brand-icon { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; }
    h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; background: linear-gradient(135deg, #FF6B35 0%, #F7C948 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.4rem; }
    .subtitle { color: #8D6E63; font-size: 0.9rem; margin: 0; }
    .form-group { margin-bottom: 1.25rem; }
    label { display: block; margin-bottom: 0.4rem; color: #5D4037; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }
    input { width: 100%; padding: 0.8rem 1rem; border: 1.5px solid rgba(255,107,53,0.2); border-radius: 12px; font-size: 0.9375rem; background: rgba(255,255,255,0.8); transition: all 250ms ease; color: #3E2723; font-family: 'Inter', sans-serif; }
    input::placeholder { color: #BCAAA4; }
    input:focus { outline: none; border-color: #FF6B35; box-shadow: 0 0 0 3px rgba(255,107,53,0.12); background: white; }
    .error { color: #D32F2F; font-size: 0.8rem; margin-top: 0.3rem; display: block; }
    .btn-submit { width: 100%; padding: 0.85rem; background: linear-gradient(135deg, #FF6B35 0%, #F7C948 100%); color: white; border: none; border-radius: 12px; font-size: 0.9375rem; font-weight: 700; cursor: pointer; transition: all 300ms ease; margin-top: 0.5rem; box-shadow: 0 4px 14px rgba(255,107,53,0.25); font-family: 'Inter', sans-serif; }
    .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.35); }
    .btn-submit:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .error-box { color: #D32F2F; font-size: 0.875rem; text-align: center; padding: 0.75rem 1rem; background: rgba(211,47,47,0.06); border: 1px solid rgba(211,47,47,0.15); border-radius: 10px; margin-top: 1rem; }
    .link { text-align: center; margin-top: 1.75rem; color: #8D6E63; font-size: 0.875rem; }
    .link a { color: #FF6B35; font-weight: 700; text-decoration: none; }
  `]
})
export class LoginComponent implements OnInit {
  loginForm!: FormGroup; loading = false; error = '';
  constructor(private fb: FormBuilder, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { this.loginForm = this.fb.group({ email: ['', [Validators.required, Validators.email]], password: ['', [Validators.required, Validators.minLength(6)]] }); }
  onSubmit(): void {
    if (!this.loginForm.valid) return;
    this.loading = true; this.error = '';
    this.authService.login(this.loginForm.value).subscribe({ next: (res) => { const user = res.data.user; if (user.role === 'admin') { this.router.navigate(['/admin/dashboard']); } else { this.router.navigate(['/queue/customer']); } this.loading = false; }, error: (err) => { this.error = err.error?.message || 'Erro ao fazer login'; this.loading = false; } });
  }
}
