import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';

@Injectable()
export class ReportService {
  private url = `${environment.apiUrl}/reports`;
  constructor(private http: HttpClient) {}

  getSummary(period: string = 'month'): Observable<any> {
    return this.http.get(`${this.url}/summary?period=${period}`);
  }
  getCategoryAnalysis(period: string = 'month'): Observable<any> {
    return this.http.get(`${this.url}/category-analysis?period=${period}`);
  }
  getEvolution(months: number = 12): Observable<any> {
    return this.http.get(`${this.url}/evolution?months=${months}`);
  }
  getBudgetPerformance(): Observable<any> {
    return this.http.get(`${this.url}/budget-performance`);
  }
  exportData(format: string, period: string): Observable<Blob> {
    return this.http.post(`${this.url}/export`, { format, period }, { responseType: 'blob' });
  }
}
