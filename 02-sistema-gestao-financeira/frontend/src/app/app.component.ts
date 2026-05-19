import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from './core/services/auth.service';
import { ThemeService } from './core/services/theme.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {
  title = 'Sistema de Gestão Financeira';
  isAuthenticated = false;
  isLoading = true;

  constructor(
    private authService: AuthService,
    private themeService: ThemeService,
    private router: Router
  ) {}

  ngOnInit(): void {
    // Verificar autenticação
    this.authService.isAuthenticated$.subscribe(
      isAuth => {
        this.isAuthenticated = isAuth;
        this.isLoading = false;
        
        if (!isAuth && !this.isPublicRoute()) {
          this.router.navigate(['/auth/login']);
        }
      }
    );

    // Inicializar tema
    this.themeService.initializeTheme();

    // Verificar token salvo
    this.authService.checkStoredToken();
  }

  private isPublicRoute(): boolean {
    const publicRoutes = ['/auth/login', '/auth/register', '/auth/forgot-password'];
    return publicRoutes.includes(this.router.url);
  }
}