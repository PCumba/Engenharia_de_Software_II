import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-header',
  template: `
    <header class="header">
      <div class="container">
        <div class="logo"><span class="logo-icon">◈</span><span class="logo-text">CryptoMonitor</span></div>
        <nav class="nav">
          <a routerLink="/crypto/dashboard" routerLinkActive="active">Dashboard</a>
          <a routerLink="/crypto/portfolio" routerLinkActive="active">Portfólio</a>
          <a routerLink="/crypto/alerts" routerLinkActive="active">Alertas</a>
        </nav>
        <div class="user-menu">
          <span *ngIf="currentUser$ | async as user" class="user-name">{{ user.name }}</span>
          <button (click)="logout()" class="btn-logout">Sair</button>
        </div>
      </div>
    </header>
  `,
  styles: [`
    .header { background: rgba(13,17,23,0.85); backdrop-filter: blur(20px) saturate(180%); border-bottom: 1px solid rgba(48,54,61,0.6); position: sticky; top: 0; z-index: 100; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0.75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
    .logo { display: flex; align-items: center; gap: 0.5rem; }
    .logo-icon { font-size: 1.3rem; color: #00D4AA; }
    .logo-text { font-size: 1.2rem; font-weight: 800; letter-spacing: -0.02em; background: linear-gradient(135deg, #00D4AA 0%, #7B61FF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .nav { display: flex; gap: 0.25rem; }
    .nav a { color: #8B949E; font-size: 0.875rem; font-weight: 500; padding: 0.5rem 0.875rem; border-radius: 10px; transition: all 250ms ease; text-decoration: none; }
    .nav a:hover { color: #E6EDF3; background: rgba(255,255,255,0.04); }
    .nav a.active { color: #00D4AA; background: rgba(0,212,170,0.08); font-weight: 600; }
    .user-menu { display: flex; align-items: center; gap: 0.75rem; }
    .user-name { font-size: 0.8125rem; font-weight: 500; color: #8B949E; }
    .btn-logout { padding: 0.4rem 1rem; background: transparent; color: #8B949E; border: 1.5px solid rgba(48,54,61,0.8); border-radius: 8px; font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: all 250ms ease; }
    .btn-logout:hover { color: #F85149; border-color: rgba(248,81,73,0.3); background: rgba(248,81,73,0.06); transform: none; }
    @media (max-width: 768px) { .nav a { padding: 0.4rem 0.6rem; font-size: 0.8rem; } .user-name { display: none; } }
  `]
})
export class HeaderComponent implements OnInit {
  currentUser$;
  constructor(private authService: AuthService, private router: Router) { this.currentUser$ = this.authService.currentUser$; }
  ngOnInit(): void {}
  logout(): void { this.authService.logout(); this.router.navigate(['/auth/login']); }
}
