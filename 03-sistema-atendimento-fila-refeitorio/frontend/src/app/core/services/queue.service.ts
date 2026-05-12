import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class QueueService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  getServices(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/services`);
  }

  getQueueInfo(serviceId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/queue/${serviceId}`);
  }

  createTicket(serviceId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/tickets`, { serviceId });
  }

  getMyTicket(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/tickets/my`);
  }

  cancelTicket(ticketId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/tickets/${ticketId}`);
  }

  // Admin
  getQueue(serviceId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/admin/queue/${serviceId}`);
  }

  callNextTicket(serviceId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/admin/call/${serviceId}`, {});
  }

  completeTicket(ticketId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/admin/complete/${ticketId}`, {});
  }

  getStats(serviceId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/admin/stats/${serviceId}`);
  }
}
