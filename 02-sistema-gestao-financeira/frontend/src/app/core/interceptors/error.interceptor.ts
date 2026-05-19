import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HttpErrorResponse
} from '@angular/common/http';
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { catchError, filter, take, switchMap } from 'rxjs/operators';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';

import { AuthService } from '../services/auth.service';

@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
  private isRefreshing = false;
  private refreshTokenSubject = new BehaviorSubject<string | null>(null);

  constructor(
    private authService: AuthService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {}

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    return next.handle(request).pipe(
      catchError((error: HttpErrorResponse) => {
        if (error.status === 401) {
          return this.handle401Error(request, next);
        }

        if (error.status === 403) {
          this.snackBar.open('Acesso negado', 'Fechar', {
            duration: 4000,
            panelClass: ['error-snackbar']
          });
          this.router.navigate(['/dashboard']);
        }

        if (error.status === 404) {
          // Não mostrar snackbar para 404 — deixar o componente tratar
        }

        if (error.status === 422) {
          // Erros de validação — deixar o componente tratar
        }

        if (error.status >= 500) {
          this.snackBar.open(
            'Erro no servidor. Tente novamente mais tarde.',
            'Fechar',
            { duration: 5000, panelClass: ['error-snackbar'] }
          );
        }

        if (error.status === 0) {
          this.snackBar.open(
            'Sem conexão com o servidor. Verifique sua internet.',
            'Fechar',
            { duration: 5000, panelClass: ['warn-snackbar'] }
          );
        }

        return throwError(() => error);
      })
    );
  }

  private handle401Error(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    if (!this.isRefreshing) {
      this.isRefreshing = true;
      this.refreshTokenSubject.next(null);

      return this.authService.refreshToken().pipe(
        switchMap((response: any) => {
          this.isRefreshing = false;
          const newToken = response.data.access_token;
          this.refreshTokenSubject.next(newToken);

          return next.handle(this.addToken(request, newToken));
        }),
        catchError(err => {
          this.isRefreshing = false;
          // Refresh falhou — redirecionar para login
          this.authService.logout().subscribe();
          return throwError(() => err);
        })
      );
    }

    // Aguardar o refresh em andamento
    return this.refreshTokenSubject.pipe(
      filter(token => token !== null),
      take(1),
      switchMap(token => next.handle(this.addToken(request, token!)))
    );
  }

  private addToken(request: HttpRequest<unknown>, token: string): HttpRequest<unknown> {
    return request.clone({
      setHeaders: { Authorization: `Bearer ${token}` }
    });
  }
}