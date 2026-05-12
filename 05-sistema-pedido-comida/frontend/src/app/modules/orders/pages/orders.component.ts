import { Component, OnInit } from '@angular/core';
import { FoodService } from '@core/services/food.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-orders',
  template: `
    <div class="container">
      <h1>📦 Meus Pedidos</h1>

      <div class="orders-list">
        <div class="order-card" *ngFor="let order of orders">
          <div class="order-header">
            <h3>Pedido #{{ order.id }}</h3>
            <span class="status" [ngClass]="order.status">{{ getStatusLabel(order.status) }}</span>
          </div>

          <div class="order-info">
            <p><strong>Restaurante:</strong> {{ order.restaurant_name }}</p>
            <p><strong>Total:</strong> €{{ order.total_price | number:'1.2-2' }}</p>
            <p><strong>Data:</strong> {{ order.created_at | date:'short' }}</p>
            <p><strong>Endereço:</strong> {{ order.delivery_address }}</p>
          </div>

          <div class="order-items">
            <h4>Itens:</h4>
            <div class="item-list">
              <div class="item" *ngFor="let item of order.items">
                <span>{{ item.name }} x{{ item.quantity }}</span>
                <span>€{{ item.price * item.quantity | number:'1.2-2' }}</span>
              </div>
            </div>
          </div>

          <div class="order-actions">
            <button (click)="trackOrder(order.id)" class="btn-track">
              Rastrear Pedido
            </button>
            <button (click)="reviewOrder(order.id)" *ngIf="order.status === 'delivered'" class="btn-review">
              Avaliar
            </button>
          </div>
        </div>
      </div>

      <div class="empty" *ngIf="orders.length === 0">
        <p>Nenhum pedido encontrado</p>
        <button (click)="goToRestaurants()" class="btn-order">Fazer um Pedido</button>
      </div>
    </div>
  `,
  styles: [`
    .container { padding: 2rem; max-width: 1000px; margin: 0 auto; }
    .orders-list {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      margin-top: 2rem;
    }
    .order-card {
      background: white;
      border-radius: 8px;
      padding: 1.5rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      border-bottom: 2px solid #FF6B6B;
      padding-bottom: 0.5rem;
    }
    .status {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: bold;
      color: white;
    }
    .status.pending { background: #ffc107; }
    .status.confirmed { background: #17a2b8; }
    .status.preparing { background: #ff9800; }
    .status.on_the_way { background: #2196f3; }
    .status.delivered { background: #28a745; }
    .order-info { margin-bottom: 1rem; }
    .order-info p { margin: 0.25rem 0; }
    .item-list {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 4px;
      margin-bottom: 1rem;
    }
    .item {
      display: flex;
      justify-content: space-between;
      padding: 0.25rem 0;
    }
    .order-actions {
      display: flex;
      gap: 1rem;
    }
    button {
      flex: 1;
      padding: 0.75rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }
    .btn-track { background: #FF6B6B; color: white; }
    .btn-review { background: #ffc107; }
    .empty { text-align: center; padding: 2rem; }
  `]
})
export class OrdersComponent implements OnInit {
  orders: any[] = [];
  loading = false;

  constructor(
    private foodService: FoodService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadOrders();
  }

  loadOrders(): void {
    this.loading = true;
    this.foodService.getOrderHistory().subscribe({
      next: (response) => {
        if (response.success) {
          this.orders = response.data;
        }
        this.loading = false;
      }
    });
  }

  trackOrder(orderId: number): void {
    this.foodService.trackOrder(orderId).subscribe({
      next: (response) => {
        if (response.success) {
          alert('Status: ' + response.data.status);
        }
      }
    });
  }

  reviewOrder(orderId: number): void {
    this.router.navigate(['/review', orderId]);
  }

  goToRestaurants(): void {
    this.router.navigate(['/restaurants']);
  }

  getStatusLabel(status: string): string {
    const labels: {[key: string]: string} = {
      'pending': '⏳ Pendente',
      'confirmed': '✅ Confirmado',
      'preparing': '🍳 A Preparar',
      'on_the_way': '🚚 A Caminho',
      'delivered': '📦 Entregue',
      'cancelled': '❌ Cancelado'
    };
    return labels[status] || status;
  }
}
