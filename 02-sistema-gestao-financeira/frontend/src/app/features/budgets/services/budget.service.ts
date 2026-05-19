import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

export interface Budget {
  id: number;
  user_id: number;
  category_id?: number;
  name: string;
  amount: number;
  period: 'weekly' | 'monthly' | 'quarterly' | 'yearly';
  start_date: string;
  end_date?: string;
  alert_percentage: number;
  is_active: boolean;
  description?: string;
  category_name?: string;
  category_color?: string;
  spent?: number;
  remaining?: number;
  percentage?: number;
  status?: 'ok' | 'warning' | 'exceeded';
  created_at: string;
}

export interface BudgetFormData {
  name: string;
  category_id?: number;
  amount: number;
  period: string;
  start_date: string;
  end_date?: string;
  alert_percentage?: number;
  description?: string;
}

@Injectable()
export class BudgetService {
  private readonly API_URL = `${environment.apiUrl}/budgets`;

  constructor(private http: HttpClient) {}

  getAll(): Observable<Budget[]> {
    return this.http.get<{ success: boolean; data: { data: Budget[] } }>(this.API_URL)
      .pipe(map(r => r.data.data));
  }

  getById(id: number): Observable<Budget> {
    return this.http.get<{ success: boolean; data: { budget: Budget } }>(`${this.API_URL}/${id}`)
      .pipe(map(r => r.data.budget));
  }

  getStatus(id: number): Observable<any> {
    return this.http.get<{ success: boolean; data: any }>(`${this.API_URL}/${id}/status`)
      .pipe(map(r => r.data));
  }

  create(data: BudgetFormData): Observable<Budget> {
    return this.http.post<{ success: boolean; data: { budget: Budget } }>(this.API_URL, data)
      .pipe(map(r => r.data.budget));
  }

  update(id: number, data: Partial<BudgetFormData>): Observable<Budget> {
    return this.http.put<{ success: boolean; data: { budget: Budget } }>(`${this.API_URL}/${id}`, data)
      .pipe(map(r => r.data.budget));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.API_URL}/${id}`);
  }
}