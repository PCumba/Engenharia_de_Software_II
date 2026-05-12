import { Component, OnInit } from '@angular/core';
import { FoodService } from '@core/services/food.service';
import { AuthService } from '@core/services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-restaurants',
  template: `
    <div class="restaurants-page">
      <div class="header"><div class="brand"><span>🍕</span><h1>FoodApp</h1></div><div class="user-menu"><span class="greeting">Olá, {{ currentUser?.name }}</span><a routerLink="/orders" class="link-orders">📦 Pedidos</a><button (click)="logout()" class="btn-logout">Sair</button></div></div>
      <div class="search-bar"><span class="search-icon">🔍</span><input type="search" placeholder="Buscar restaurante..." (keyup)="onSearch($event)"></div>
      <div class="restaurants-grid">
        <div class="restaurant-card" *ngFor="let r of restaurants" (click)="viewRestaurant(r.id)">
          <div class="card-image"><img [src]="r.image_url" [alt]="r.name"><div class="delivery-badge">🚚 {{ r.delivery_time }}min</div></div>
          <div class="card-body"><h3>{{ r.name }}</h3><p class="cuisine">{{ r.cuisine_type }}</p><div class="card-footer"><span class="rating">⭐ {{ r.rating }}</span><span class="fee">Entrega €{{ r.delivery_fee }}</span></div></div>
        </div>
      </div>
      <div class="pagination" *ngIf="totalPages > 1"><button (click)="previousPage()" [disabled]="currentPage === 1" class="btn-page">← Anterior</button><span class="page-info">{{ currentPage }} / {{ totalPages }}</span><button (click)="nextPage()" [disabled]="currentPage === totalPages" class="btn-page">Próxima →</button></div>
    </div>
  `,
  styles: [`
    .restaurants-page { max-width: 1200px; margin: 0 auto; padding: 2rem; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .brand { display: flex; align-items: center; gap: 0.5rem; }
    .brand span { font-size: 1.5rem; }
    h1 { margin: 0; font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #FF4757 0%, #FECA57 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .user-menu { display: flex; align-items: center; gap: 0.75rem; }
    .greeting { font-size: 0.875rem; color: #6B5B4D; font-weight: 500; }
    .link-orders { padding: 0.4rem 0.875rem; background: rgba(255,71,87,0.06); color: #FF4757; border-radius: 8px; font-weight: 600; font-size: 0.8125rem; transition: all 200ms; text-decoration: none; }
    .link-orders:hover { background: rgba(255,71,87,0.1); }
    .btn-logout { padding: 0.4rem 1rem; background: transparent; color: #A89888; border: 1.5px solid rgba(255,71,87,0.1); border-radius: 8px; font-weight: 600; font-size: 0.8125rem; cursor: pointer; transition: all 250ms; }
    .btn-logout:hover { color: #FF4757; border-color: rgba(255,71,87,0.25); transform: none; }
    .search-bar { position: relative; margin-bottom: 2rem; }
    .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); opacity: 0.4; font-size: 1rem; }
    .search-bar input { width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem; border: 1.5px solid rgba(255,71,87,0.1); border-radius: 14px; font-size: 0.9375rem; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); transition: all 250ms; }
    .search-bar input:focus { outline: none; border-color: #FF4757; box-shadow: 0 0 0 3px rgba(255,71,87,0.08); background: white; }
    .restaurants-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }
    .restaurant-card { background: white; border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 2px 12px rgba(45,31,16,0.04); cursor: pointer; transition: all 300ms ease; }
    .restaurant-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(45,31,16,0.1); }
    .card-image { position: relative; overflow: hidden; }
    .card-image img { width: 100%; height: 180px; object-fit: cover; transition: transform 400ms ease; }
    .restaurant-card:hover .card-image img { transform: scale(1.05); }
    .delivery-badge { position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(255,255,255,0.92); backdrop-filter: blur(8px); padding: 0.3rem 0.7rem; border-radius: 100px; font-size: 0.75rem; font-weight: 700; color: #2D1F10; }
    .card-body { padding: 1rem 1.25rem 1.25rem; }
    .card-body h3 { margin: 0 0 0.25rem; font-size: 1.05rem; font-weight: 700; color: #2D1F10; }
    .cuisine { margin: 0 0 0.75rem; color: #A89888; font-size: 0.85rem; font-weight: 500; }
    .card-footer { display: flex; justify-content: space-between; align-items: center; }
    .rating { font-weight: 700; font-size: 0.875rem; color: #2D1F10; }
    .fee { font-size: 0.8rem; color: #FF4757; font-weight: 600; }
    .pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; }
    .btn-page { padding: 0.6rem 1.25rem; background: white; border: 1.5px solid rgba(255,71,87,0.12); border-radius: 10px; font-weight: 600; color: #6B5B4D; cursor: pointer; transition: all 250ms; }
    .btn-page:hover:not(:disabled) { border-color: #FF4757; color: #FF4757; }
    .btn-page:disabled { opacity: 0.4; }
    .page-info { font-size: 0.875rem; color: #A89888; font-weight: 600; }
  `]
})
export class RestaurantsComponent implements OnInit {
  restaurants: any[] = []; currentPage = 1; totalPages = 1; currentUser: any; loading = false;
  constructor(private foodService: FoodService, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { this.currentUser = this.authService.getCurrentUser(); this.loadRestaurants(); }
  loadRestaurants(): void { this.loading = true; this.foodService.getAllRestaurants(this.currentPage).subscribe({ next: (response) => { if (response.success) { this.restaurants = response.data; this.totalPages = response.pagination.pages; } this.loading = false; }, error: () => this.loading = false }); }
  viewRestaurant(id: number): void { this.router.navigate(['/menu', id]); }
  onSearch(event: any): void {}
  nextPage(): void { this.currentPage++; this.loadRestaurants(); }
  previousPage(): void { this.currentPage--; this.loadRestaurants(); }
  logout(): void { this.authService.logout(); this.router.navigate(['/auth/login']); }
}
