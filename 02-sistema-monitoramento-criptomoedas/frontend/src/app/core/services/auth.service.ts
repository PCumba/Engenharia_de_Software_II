import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { environment } from '@environments/environment';

interface AuthResponse {
  success: boolean;
  data?: {
    token?: string;
    user?: any;
  };
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {
    this.loadUserFromStorage();
  }

  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/auth/register`, userData);
  }

  login(credentials: any): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/api/auth/login`, credentials).pipe(
      tap((response: AuthResponse) => {
        if (response.success && response.data?.token) {
          this.setToken(response.data.token);
          this.currentUserSubject.next(response.data.user ?? null);
          localStorage.setItem('currentUser', JSON.stringify(response.data.user ?? null));
        }
      })
    );
  }

  logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('currentUser');
    this.currentUserSubject.next(null);
  }

  setToken(token: string): void {
    localStorage.setItem('token', token);
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }

  getCurrentUser(): any {
    return this.currentUserSubject.value;
  }

  private loadUserFromStorage(): void {
    const user = localStorage.getItem('currentUser');
    if (user) {
      this.currentUserSubject.next(JSON.parse(user));
    }
  }
}
