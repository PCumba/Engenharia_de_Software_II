import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-login',
  template: `
    <div class="auth-container">
      <div class="auth-card">
        <div class="card-header"><span class="brand-icon">💰</span><h1>Gestão Financeira</h1><p class="subtitle">Controle suas finanças</p></div>
        <form [formGroup]="loginForm" (ngSubmit)="onSubmit()">
          <div class="form-group"><input type="email" formControlName="email" placeholder="Email" required></div>
          <div class="form-group"><input type="password" formControlName="password" placeholder="Password" required></div>
          <button type="submit" class="btn-submit" [disabled]="!loginForm.valid || loading">{{ loading ? 'A entrar...' : 'Entrar' }}</button>
        </form>
        <div class="error-box" *ngIf="error">{{ error }}</div>
        <p class="link">Não tens conta? <a routerLink="/auth/register">Registar</a></p>
      </div>
    </div>
  `,
  styles: [`
    .auth-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(160deg, #F0EDFF 0%, #F7F8FC 40%, #E8F8F5 100%); background-image: radial-gradient(ellipse at 25% 15%, rgba(108,92,231,0.1) 0%, transparent 50%), radial-gradient(ellipse at 75% 85%, rgba(0,184,148,0.06) 0%, transparent 50%), linear-gradient(160deg, #F0EDFF 0%, #F7F8FC 40%, #E8F8F5 100%); padding: 2rem; }
    .auth-card { background: rgba(255,255,255,0.88); backdrop-filter: blur(20px) saturate(180%); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(108,92,231,0.08); box-shadow: 0 20px 60px rgba(26,26,46,0.07); width: 100%; max-width: 400px; animation: scaleIn 0.5s cubic-bezier(0.34,1.56,0.64,1); }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.94) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .card-header { text-align: center; margin-bottom: 2rem; }
    .brand-icon { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; }
    h1 { font-size: 1.75rem; font-weight: 800; background: linear-gradient(135deg, #6C5CE7 0%, #00B894 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.3rem; }
    .subtitle { color: #9696B4; font-size: 0.9rem; margin: 0; }
    .form-group { margin-bottom: 1rem; }
    input { width: 100%; padding: 0.85rem 1rem; border: 1.5px solid rgba(108,92,231,0.15); border-radius: 12px; font-size: 0.9375rem; background: rgba(255,255,255,0.8); transition: all 250ms; color: #1A1A2E; }
    input::placeholder { color: #B0B0C8; }
    input:focus { outline: none; border-color: #6C5CE7; box-shadow: 0 0 0 3px rgba(108,92,231,0.1); background: white; }
    .btn-submit { width: 100%; padding: 0.85rem; background: linear-gradient(135deg, #6C5CE7 0%, #A29BFE 100%); color: white; border: none; border-radius: 12px; font-size: 0.9375rem; font-weight: 700; cursor: pointer; transition: all 300ms; box-shadow: 0 4px 14px rgba(108,92,231,0.25); margin-top: 0.5rem; }
    .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(108,92,231,0.35); }
    .btn-submit:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .error-box { color: #E17055; margin-top: 1rem; font-size: 0.875rem; text-align: center; padding: 0.75rem; background: rgba(225,112,85,0.06); border: 1px solid rgba(225,112,85,0.12); border-radius: 10px; }
    .link { text-align: center; margin-top: 1.5rem; color: #9696B4; font-size: 0.875rem; }
    .link a { color: #6C5CE7; font-weight: 700; }
  `]
})
export class LoginComponent implements OnInit {
  loginForm!: FormGroup; loading = false; error = '';
  constructor(private fb: FormBuilder, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { this.loginForm = this.fb.group({ email: ['', [Validators.required, Validators.email]], password: ['', [Validators.required]] }); }
  onSubmit(): void { this.loading = true; this.authService.login(this.loginForm.value).subscribe({ next: () => { this.router.navigate(['/dashboard']); this.loading = false; }, error: (err) => { this.error = err.error?.message || 'Erro ao fazer login'; this.loading = false; } }); }
}
