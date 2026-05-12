import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class FoodService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  // Restaurantes
  getAllRestaurants(page: number = 1, perPage: number = 20): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/restaurants`, {
      params: { page, perPage }
    });
  }

  getRestaurantById(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/restaurants/${id}`);
  }

  searchRestaurants(cuisine?: string, open?: boolean): Observable<any> {
    let params: any = {};
    if (cuisine) params.cuisine = cuisine;
    if (open !== undefined) params.open = open;
    return this.http.get(`${this.apiUrl}/api/restaurants/search`, { params });
  }

  // Menu
  getRestaurantMenu(restaurantId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/restaurants/${restaurantId}/menu`);
  }

  searchMenu(restaurantId: number, query: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/restaurants/${restaurantId}/menu/search`, {
      params: { q: query }
    });
  }

  // Pedidos
  createOrder(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/orders`, data);
  }

  getOrderHistory(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/orders`);
  }

  getOrderById(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/orders/${id}`);
  }

  trackOrder(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/orders/${id}/track`);
  }

  // Avaliações
  createReview(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/reviews`, data);
  }

  getRestaurantReviews(restaurantId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/restaurants/${restaurantId}/reviews`);
  }

  getReviewStats(restaurantId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/restaurants/${restaurantId}/reviews/stats`);
  }
}
