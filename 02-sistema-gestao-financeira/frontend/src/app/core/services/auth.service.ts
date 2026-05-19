import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { Router } from '@angular/router';

import { environment } from '../../../environments/environment';
import { User } from '../models/user.model';
import { LoginRequest, LoginResponse, RegisterRequest } from '../models/auth.model';
import { StorageService } from './storage.service';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly API_URL = environment.apiUrl;
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);

  public currentUser$ = this.currentUserSubject.asObservable();
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    private storageService: StorageService
  ) {}

  /**
   * Realizar login
   */
  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.API_URL}/auth/login`, credentials)
      .pipe(
        tap(response => {
          if (response.success) {
            this.setSession(response.data);
          }
        }),
        catchError(this.handleError)
      );
  }

  /**
   * Realizar registro
   */
  register(userData: RegisterRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.API_URL}/auth/register`, userData)
      .pipe(
        tap(response => {
          if (response.success) {
            this.setSession(response.data);
          }
        }),
        catchError(this.handleError)
      );
  }

  /**
   * Logout
   */
  logout(): Observable<any> {
    return this.http.post(`${this.API_URL}/auth/logout`, {})
      .pipe(
        tap(() => {
          this.clearSession();
        }),
        catchError(() => {
          // Mesmo se der erro na API, limpar sessão local
          this.clearSession();
          return throwError(() => new Error('Erro ao fazer logout'));
        })
      );
  }

  /**
   * Renovar token
   */
  refreshToken(): Observable<LoginResponse> {
    const refreshToken = this.storageService.getRefreshToken();
    
    if (!refreshToken) {
      return throwError(() => new Error('Refresh token não encontrado'));
    }

    return this.http.post<LoginResponse>(`${this.API_URL}/auth/refresh`, {
      refresh_token: refreshToken
    }).pipe(
      tap(response => {
        if (response.success) {
          this.setSession(response.data);
        }
      }),
      catchError(error => {
        this.clearSession();
        return throwError(() => error);
      })
    );
  }

  /**
   * Solicitar recuperação de senha
   */
  forgotPassword(email: string): Observable<any> {
    return this.http.post(`${this.API_URL}/auth/forgot-password`, { email })
      .pipe(catchError(this.handleError));
  }

  /**
   * Redefinir senha
   */
  resetPassword(token: string, password: string, email: string): Observable<any> {
    return this.http.post(`${this.API_URL}/auth/reset-password`, {
      token,
      password,
      email
    }).pipe(catchError(this.handleError));
  }

  /**
   * Obter perfil do usuário atual
   */
  getCurrentUser(): Observable<User> {
    return this.http.get<{success: boolean, data: {user: User}}>(`${this.API_URL}/auth/me`)
      .pipe(
        map(response => response.data.user),
        tap(user => {
          this.currentUserSubject.next(user);
        }),
        catchError(this.handleError)
      );
  }

  /**
   * Verificar se há token armazenado e validar
   */
  checkStoredToken(): void {
    const token = this.storageService.getAccessToken();
    const user = this.storageService.getUser();

    if (token && user) {
      // Verificar se o token não está expirado
      if (this.isTokenValid(token)) {
        this.currentUserSubject.next(user);
        this.isAuthenticatedSubject.next(true);
      } else {
        // Tentar renovar o token
        this.refreshToken().subscribe({
          next: () => {
            // Token renovado com sucesso
          },
          error: () => {
            this.clearSession();
          }
        });
      }
    }
  }

  /**
   * Verificar se o token é válido (não expirado)
   */
  private isTokenValid(token: string): boolean {
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      const currentTime = Math.floor(Date.now() / 1000);
      return payload.exp > currentTime;
    } catch {
      return false;
    }
  }

  /**
   * Configurar sessão do usuário
   */
  private setSession(authData: any): void {
    this.storageService.setAccessToken(authData.access_token);
    this.storageService.setRefreshToken(authData.refresh_token);
    this.storageService.setUser(authData.user);

    this.currentUserSubject.next(authData.user);
    this.isAuthenticatedSubject.next(true);
  }

  /**
   * Limpar sessão do usuário
   */
  private clearSession(): void {
    this.storageService.clearAll();
    this.currentUserSubject.next(null);
    this.isAuthenticatedSubject.next(false);
    this.router.navigate(['/auth/login']);
  }

  /**
   * Obter token de acesso
   */
  getAccessToken(): string | null {
    return this.storageService.getAccessToken();
  }

  /**
   * Verificar se o usuário está autenticado
   */
  isAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }

  /**
   * Obter usuário atual
   */
  getCurrentUserValue(): User | null {
    return this.currentUserSubject.value;
  }

  /**
   * Tratamento de erros
   */
  private handleError = (error: any) => {
    let errorMessage = 'Erro desconhecido';

    if (error.error?.message) {
      errorMessage = error.error.message;
    } else if (error.message) {
      errorMessage = error.message;
    } else if (typeof error.error === 'string') {
      errorMessage = error.error;
    }

    return throwError(() => new Error(errorMessage));
  };
}