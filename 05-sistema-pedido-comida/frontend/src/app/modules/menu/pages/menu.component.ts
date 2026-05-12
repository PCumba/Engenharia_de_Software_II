import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FoodService } from '@core/services/food.service';

@Component({
  selector: 'app-menu',
  template: `
    <div class="menu-page">
      <div class="header"><button (click)="goBack()" class="btn-back">← Voltar</button><h1>{{ restaurant?.name }}</h1></div>
      <div class="categories" *ngIf="categories.length > 0">
        <button *ngFor="let cat of categories" [class.active]="selectedCategory === cat" (click)="selectedCategory = cat" class="cat-btn">{{ cat }}</button>
      </div>
      <div class="menu-grid">
        <div class="menu-item" *ngFor="let item of filteredItems">
          <div class="item-info"><h3>{{ item.name }}</h3><p class="description">{{ item.description }}</p><p class="price">€{{ item.price | number:'1.2-2' }}</p></div>
          <button (click)="addToCart(item)" class="btn-add">+</button>
        </div>
      </div>
      <button *ngIf="cartItems.length > 0" (click)="goToCheckout()" class="btn-cart">🛒 Carrinho ({{ cartItems.length }}) — €{{ getCartTotal() | number:'1.2-2' }}</button>
    </div>
  `,
  styles: [`
    .menu-page { max-width: 900px; margin: 0 auto; padding: 2rem; padding-bottom: 6rem; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .btn-back { padding: 0.5rem 1rem; background: rgba(255,255,255,0.9); border: 1.5px solid rgba(255,71,87,0.1); border-radius: 10px; color: #6B5B4D; font-weight: 600; cursor: pointer; transition: all 250ms; font-size: 0.875rem; }
    .btn-back:hover { border-color: #FF4757; color: #FF4757; }
    h1 { font-size: 1.5rem; font-weight: 800; margin: 0; color: #2D1F10; }
    .categories { display: flex; gap: 0.4rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .cat-btn { padding: 0.5rem 1.1rem; background: white; border: 1.5px solid rgba(255,71,87,0.08); border-radius: 100px; cursor: pointer; font-size: 0.8125rem; font-weight: 600; color: #6B5B4D; transition: all 250ms; }
    .cat-btn:hover { border-color: rgba(255,71,87,0.25); color: #FF4757; }
    .cat-btn.active { background: linear-gradient(135deg, #FF4757, #FF6B81); color: white; border-color: transparent; box-shadow: 0 3px 10px rgba(255,71,87,0.2); }
    .menu-grid { display: flex; flex-direction: column; gap: 0.6rem; }
    .menu-item { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1.25rem; border-radius: 14px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 1px 6px rgba(45,31,16,0.03); transition: all 250ms ease; }
    .menu-item:hover { transform: translateX(4px); box-shadow: 0 4px 16px rgba(45,31,16,0.06); }
    .item-info { flex: 1; }
    .item-info h3 { margin: 0 0 0.25rem; font-size: 1rem; font-weight: 700; color: #2D1F10; }
    .description { margin: 0 0 0.4rem; font-size: 0.8rem; color: #A89888; }
    .price { margin: 0; font-size: 1.1rem; font-weight: 800; color: #FF4757; }
    .btn-add { width: 44px; height: 44px; border-radius: 12px; background: rgba(255,71,87,0.08); color: #FF4757; border: 1.5px solid rgba(255,71,87,0.15); cursor: pointer; font-size: 1.25rem; font-weight: 700; transition: all 250ms; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 1rem; }
    .btn-add:hover { background: #FF4757; color: white; transform: scale(1.08); }
    .btn-cart { position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%); padding: 0.875rem 2rem; background: linear-gradient(135deg, #FF4757 0%, #FF6B81 100%); color: white; border: none; border-radius: 100px; font-weight: 700; font-size: 1rem; cursor: pointer; box-shadow: 0 6px 24px rgba(255,71,87,0.35); z-index: 100; transition: all 300ms; }
    .btn-cart:hover { transform: translateX(-50%) translateY(-2px); box-shadow: 0 10px 32px rgba(255,71,87,0.4); }
  `]
})
export class MenuComponent implements OnInit {
  restaurant: any; menuItems: any[] = []; filteredItems: any[] = []; cartItems: any[] = []; categories: string[] = []; selectedCategory = '';
  constructor(private route: ActivatedRoute, private foodService: FoodService, private router: Router) {}
  ngOnInit(): void { const id = this.route.snapshot.params['id']; this.loadMenu(id); const cart = localStorage.getItem('cart'); this.cartItems = cart ? JSON.parse(cart) : []; }
  loadMenu(restaurantId: number): void { this.foodService.getRestaurantMenu(restaurantId).subscribe({ next: (response) => { if (response.success) { this.restaurant = response.data.restaurant; this.menuItems = response.data.menuItems; this.categories = [...new Set(this.menuItems.map((i: any) => i.category))]; this.filteredItems = this.menuItems; if (this.categories.length > 0) { this.selectedCategory = this.categories[0]; this.filterItems(); } } } }); }
  get filteredItemsComputed(): any[] { return this.selectedCategory ? this.menuItems.filter(i => i.category === this.selectedCategory) : this.menuItems; }
  filterItems(): void { this.filteredItems = this.selectedCategory ? this.menuItems.filter(i => i.category === this.selectedCategory) : this.menuItems; }
  addToCart(item: any): void { this.cartItems.push({ ...item, restaurantId: this.restaurant.id }); localStorage.setItem('cart', JSON.stringify(this.cartItems)); }
  getCartTotal(): number { return this.cartItems.reduce((sum, item) => sum + item.price, 0); }
  goToCheckout(): void { this.router.navigate(['/checkout']); }
  goBack(): void { this.router.navigate(['/restaurants']); }
}
