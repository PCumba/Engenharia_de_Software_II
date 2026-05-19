import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';

@Injectable()
export class CategoryService {
  private url = `${environment.apiUrl}/categories`;
  constructor(private http: HttpClient) {}

  getAll(type?: string): Observable<any> {
    const params = type ? `?type=${type}` : '';
    return this.http.get(`${this.url}${params}`);
  }
  getById(id: number): Observable<any> { return this.http.get(`${this.url}/${id}`); }
  create(data: any): Observable<any> { return this.http.post(this.url, data); }
  update(id: number, data: any): Observable<any> { return this.http.put(`${this.url}/${id}`, data); }
  delete(id: number): Observable<any> { return this.http.delete(`${this.url}/${id}`); }
}
