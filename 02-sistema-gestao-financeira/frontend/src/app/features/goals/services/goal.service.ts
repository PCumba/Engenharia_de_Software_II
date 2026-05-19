import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

export interface Goal {
  id: number;
  user_id: number;
  name: string;
  description?: string;
  target_amount: number;
  current_amount: number;
  target_date?: string;
  category: string;
  priority: 'low' | 'medium' | 'high';
  is_active: boolean;
  achieved_at?: string;
  percentage: number;
  created_at: string;
  updated_at: string;
}

export interface GoalFormData {
  name: string;
  description?: string;
  target_amount: number;
  current_amount?: number;
  target_date?: string;
  category: string;
  priority: string;
}

@Injectable()
export class GoalService {
  private readonly API_URL = `${environment.apiUrl}/goals`;

  constructor(private http: HttpClient) {}

  getAll(): Observable<Goal[]> {
    return this.http.get<{ success: boolean; data: { data: Goal[] } }>(this.API_URL)
      .pipe(map(r => r.data.data.map(g => ({
        ...g,
        percentage: g.target_amount > 0 ? Math.min((g.current_amount / g.target_amount) * 100, 100) : 0
      }))));
  }

  getById(id: number): Observable<Goal> {
    return this.http.get<{ success: boolean; data: { goal: Goal } }>(`${this.API_URL}/${id}`)
      .pipe(map(r => r.data.goal));
  }

  create(data: GoalFormData): Observable<Goal> {
    return this.http.post<{ success: boolean; data: { goal: Goal } }>(this.API_URL, data)
      .pipe(map(r => r.data.goal));
  }

  update(id: number, data: Partial<GoalFormData>): Observable<Goal> {
    return this.http.put<{ success: boolean; data: { goal: Goal } }>(`${this.API_URL}/${id}`, data)
      .pipe(map(r => r.data.goal));
  }

  updateProgress(id: number, amount: number): Observable<Goal> {
    return this.http.post<{ success: boolean; data: { goal: Goal } }>(
      `${this.API_URL}/${id}/progress`, { amount }
    ).pipe(map(r => r.data.goal));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.API_URL}/${id}`);
  }
}