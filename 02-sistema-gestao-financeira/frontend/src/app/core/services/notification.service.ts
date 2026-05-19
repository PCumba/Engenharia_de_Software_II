import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

export interface Alert {
  id: number;
  type: string;
  title: string;
  message: string;
  priority: 'low' | 'medium' | 'high';
  is_read: boolean;
  created_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private readonly API_URL = environment.apiUrl;
  private unreadCountSubject = new BehaviorSubject<number>(0);
  public unreadCount$ = this.unreadCountSubject.asObservable();

  constructor(private http: HttpClient) {}

  getAlerts(page = 1, limit = 20): Observable<{ data: Alert[]; pagination: any }> {
    return this.http.get<{ success: boolean; data: any }>(
      `${this.API_URL}/alerts?page=${page}&limit=${limit}`
    ).pipe(map(r => r.data));
  }

  markAsRead(id: number): Observable<any> {
    return this.http.post(`${this.API_URL}/alerts/${id}/mark-read`, {}).pipe(
      tap(() => {
        const current = this.unreadCountSubject.value;
        if (current > 0) this.unreadCountSubject.next(current - 1);
      })
    );
  }

  markAllAsRead(): Observable<any> {
    return this.http.post(`${this.API_URL}/alerts/mark-all-read`, {}).pipe(
      tap(() => this.unreadCountSubject.next(0))
    );
  }

  refreshUnreadCount(): void {
    this.http.get<{ success: boolean; data: { count: number } }>(
      `${this.API_URL}/alerts/unread-count`
    ).subscribe({
      next: r => this.unreadCountSubject.next(r.data.count),
      error: () => {}
    });
  }

  deleteAlert(id: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/alerts/${id}`);
  }
}