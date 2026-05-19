import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

export interface Account {
  id: number;
  user_id: number;
  name: string;
  type: string;
  bank_name?: string;
  account_number?: string;
  initial_balance: number;
  current_balance: number;
  currency: string;
  color: string;
  icon: string;
  is_active: boolean;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface AccountFormData {
  name: string;
  type: string;
  bank_name?: string;
  account_number?: string;
  initial_balance: number;
  currency: string;
  color?: string;
  icon?: string;
  description?: string;
}

@Injectable()
export class AccountService {
  private readonly API_URL = `${environment.apiUrl}/accounts`;

  constructor(private http: HttpClient) {}

  getAll(activeOnly = true): Observable<Account[]> {
    const params = activeOnly ? '?active=1' : '';
    return this.http.get<{ success: boolean; data: { data: Account[] } }>(`${this.API_URL}${params}`)
      .pipe(map(r => r.data.data));
  }

  getById(id: number): Observable<Account> {
    return this.http.get<{ success: boolean; data: { account: Account } }>(`${this.API_URL}/${id}`)
      .pipe(map(r => r.data.account));
  }

  create(data: AccountFormData): Observable<Account> {
    return this.http.post<{ success: boolean; data: { account: Account } }>(this.API_URL, data)
      .pipe(map(r => r.data.account));
  }

  update(id: number, data: Partial<AccountFormData>): Observable<Account> {
    return this.http.put<{ success: boolean; data: { account: Account } }>(`${this.API_URL}/${id}`, data)
      .pipe(map(r => r.data.account));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.API_URL}/${id}`);
  }

  getBalance(id: number): Observable<{ balance: number }> {
    return this.http.get<{ success: boolean; data: { balance: number } }>(`${this.API_URL}/${id}/balance`)
      .pipe(map(r => r.data));
  }

  getTotalBalance(): Observable<number> {
    return this.getAll().pipe(
      map(accounts => accounts.reduce((sum, a) => sum + a.current_balance, 0))
    );
  }
}