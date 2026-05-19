import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, forkJoin, map } from 'rxjs';

import { environment } from '../../../../environments/environment';
import { DashboardData, FinancialSummary, ExpenseByCategory, MonthlyEvolution } from '../models/dashboard.model';

@Injectable({
  providedIn: 'root'
})
export class DashboardService {
  private readonly API_URL = environment.apiUrl;

  constructor(private http: HttpClient) {}

  /**
   * Carregar todos os dados do dashboard em paralelo
   */
  getDashboardData(period: string = 'month'): Observable<DashboardData> {
    const params = new HttpParams().set('period', period);

    return this.http.get<{ success: boolean; data: DashboardData }>(
      `${this.API_URL}/reports/dashboard`,
      { params }
    ).pipe(
      map(response => response.data)
    );
  }

  /**
   * Obter resumo financeiro
   */
  getFinancialSummary(period: string = 'month'): Observable<FinancialSummary> {
    const params = new HttpParams().set('period', period);

    return this.http.get<{ success: boolean; data: { summary: FinancialSummary } }>(
      `${this.API_URL}/reports/income-expense`,
      { params }
    ).pipe(
      map(response => response.data.summary)
    );
  }

  /**
   * Obter gastos por categoria
   */
  getExpensesByCategory(period: string = 'month'): Observable<ExpenseByCategory[]> {
    const params = new HttpParams().set('period', period);

    return this.http.get<{ success: boolean; data: { expenses_by_category: ExpenseByCategory[] } }>(
      `${this.API_URL}/reports/category-analysis`,
      { params }
    ).pipe(
      map(response => response.data.expenses_by_category)
    );
  }

  /**
   * Obter evolução mensal
   */
  getMonthlyEvolution(months: number = 12): Observable<MonthlyEvolution[]> {
    const params = new HttpParams().set('months', months.toString());

    return this.http.get<{ success: boolean; data: { monthly_evolution: MonthlyEvolution[] } }>(
      `${this.API_URL}/reports/monthly-summary`,
      { params }
    ).pipe(
      map(response => response.data.monthly_evolution)
    );
  }
}