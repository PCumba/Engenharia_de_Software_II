import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class FinanceService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  // Resumo
  getSummary(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/summary`);
  }

  // Transações
  createTransaction(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/transactions`, data);
  }

  getTransactionsByPeriod(startDate: string, endDate: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/transactions/period`, {
      params: { startDate, endDate }
    });
  }

  getRecentTransactions(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/transactions/recent`);
  }

  updateTransaction(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/transactions/${id}`, data);
  }

  deleteTransaction(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/transactions/${id}`);
  }

  // Orçamentos
  getBudgets(month?: string, year?: string): Observable<any> {
    let params: any = {};
    if (month) params.month = month;
    if (year) params.year = year;
    return this.http.get(`${this.apiUrl}/api/budgets`, { params });
  }

  createBudget(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/budgets`, data);
  }

  updateBudget(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/budgets/${id}`, data);
  }

  deleteBudget(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/budgets/${id}`);
  }

  checkBudgetStatus(month?: string, year?: string): Observable<any> {
    let params: any = {};
    if (month) params.month = month;
    if (year) params.year = year;
    return this.http.get(`${this.apiUrl}/api/budgets/status`, { params });
  }

  // Relatórios
  getExpensesByCategory(startDate: string, endDate: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/reports/expenses-category`, {
      params: { startDate, endDate }
    });
  }

  getIncomeByCategory(startDate: string, endDate: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/reports/income-category`, {
      params: { startDate, endDate }
    });
  }

  getMonthlyEvolution(year: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/reports/monthly-evolution`, {
      params: { year }
    });
  }

  getPeriodReport(startDate: string, endDate: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/reports/period`, {
      params: { startDate, endDate }
    });
  }
}
