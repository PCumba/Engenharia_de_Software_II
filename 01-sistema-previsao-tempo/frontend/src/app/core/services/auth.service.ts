import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser$ = this.currentUserSubject.asObservable();
  private tokenKey = 'auth_token';

  constructor(private http: HttpClient) {
    this.loadUserFromStorage();
  }

  register(email: string, password: string, name: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/auth/register`, {
      email,
      password,
      name
    });
  }

  login(email: string, password: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/auth/login`, {
      email,
      password
    });
  }

  logout(): void {
    this.http.post(`${this.apiUrl}/api/auth/logout`, {}).subscribe();
    localStorage.removeItem(this.tokenKey);
    this.currentUserSubject.next(null);
  }

  forgotPassword(email: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/auth/forgot-password`, { email });
  }

  validateResetToken(token: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/auth/validate-token`, { token });
  }

  resetPassword(token: string, password: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/auth/reset-password`, { token, password });
  }

  changePassword(currentPassword: string, newPassword: string): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/auth/change-password`, {
      current_password: currentPassword,
      new_password: newPassword
    });
  }

  updateProfile(data: { name?: string; email?: string }): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/auth/profile`, data);
  }

  deleteAccount(password: string): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/auth/profile`, {
      body: { password }
    });
  }

  updatePreferences(language: string, theme: string): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/auth/preferences`, {
      language,
      theme
    });
  }

  setToken(token: string): void {
    localStorage.setItem(this.tokenKey, token);
  }

  getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }

  getCurrentUser(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/auth/me`);
  }

  setCurrentUser(user: any): void {
    this.currentUserSubject.next(user);
  }

  private loadUserFromStorage(): void {
    const token = this.getToken();
    if (token) {
      this.getCurrentUser().subscribe(
        response => this.currentUserSubject.next(response.data),
        () => this.logout()
      );
    }
  }
}
