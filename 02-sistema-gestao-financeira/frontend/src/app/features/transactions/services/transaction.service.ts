import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

export interface Transaction {
  id: number;
  user_id: number;
  account_id: number;
  category_id?: number;
  type: 'income' | 'expense' | 'transfer';
  amount: number;
  description: string;
  transaction_date: string;
  payment_method?: string;
  reference_number?: string;
  location?: string;
  tags?: string[];
  notes?: string;
  attachment_path?: string;
  is_recurring: boolean;
  recurring_frequency?: string;
  recurring_end_date?: string;
  status: 'pending' | 'completed' | 'cancelled';
  account_name?: string;
  category_name?: string;
  category_color?: string;
  created_at: string;
  updated_at: string;
}

export interface TransactionFilters {
  type?: string;
  account_id?: number;
  category_id?: number;
  date_from?: string;
  date_to?: string;
  amount_min?: number;
  amount_max?: number;
  search?: string;
  status?: string;
  page?: number;
  limit?: number;
}

export interface TransactionFormData {
  account_id: number;
  category_id?: number;
  type: string;
  amount: number;
  description: string;
  transaction_date: string;
  payment_method?: string;
  reference_number?: string;
  location?: string;
  tags?: string[];
  notes?: string;
  is_recurring?: boolean;
  recurring_frequency?: string;
  recurring_end_date?: string;
  status?: string;
}

@Injectable()
export class TransactionService {
  private readonly API_URL = `${environment.apiUrl}/transactions`;

  constructor(private http: HttpClient) {}

  getAll(filters: TransactionFilters = {}): Observable<{ data: Transaction[]; pagination: any }> {
    let params = new HttpParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        params = params.set(key, value.toString());
      }
    });

    return this.http.get<{ success: boolean; data: any }>(this.API_URL, { params })
      .pipe(map(r => r.data));
  }

  getById(id: number): Observable<Transaction> {
    return this.http.get<{ success: boolean; data: { transaction: Transaction } }>(`${this.API_URL}/${id}`)
      .pipe(map(r => r.data.transaction));
  }

  create(data: TransactionFormData): Observable<Transaction> {
    return this.http.post<{ success: boolean; data: { transaction: Transaction } }>(this.API_URL, data)
      .pipe(map(r => r.data.transaction));
  }

  update(id: number, data: Partial<TransactionFormData>): Observable<Transaction> {
    return this.http.put<{ success: boolean; data: { transaction: Transaction } }>(`${this.API_URL}/${id}`, data)
      .pipe(map(r => r.data.transaction));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.API_URL}/${id}`);
  }

  import(file: File): Observable<{ imported: number; errors: string[] }> {
    const formData = new FormData();
    formData.append('file', file);
    return this.http.post<{ success: boolean; data: any }>(`${this.API_URL}/import`, formData)
      .pipe(map(r => r.data));
  }

  getSummary(period = 'month'): Observable<any> {
    return this.http.get<{ success: boolean; data: any }>(
      `${environment.apiUrl}/reports/income-expense?period=${period}`
    ).pipe(map(r => r.data));
  }
}