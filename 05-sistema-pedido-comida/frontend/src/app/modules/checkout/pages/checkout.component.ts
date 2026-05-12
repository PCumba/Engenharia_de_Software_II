import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { FoodService } from '@core/services/food.service';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-checkout',
  template: `
    <div class="checkout-page">
      <div class="header"><button (click)="goBack()" class="btn-back">← Voltar</button><h1>🛒 Checkout</h1></div>
      <div class="checkout-layout">
        <div class="cart-section">
          <h2>Itens do Pedido</h2>
          <div class="item" *ngFor="let item of cartItems; let i = index"><div class="item-info"><h3>{{ item.name }}</h3><p class="item-price">€{{ item.price | number:'1.2-2' }}</p></div><button (click)="removeItem(i)" class="btn-remove">✕</button></div>
          <div class="summary"><div class="summary-line"><span>Subtotal</span><span>€{{ getSubtotal() | number:'1.2-2' }}</span></div><div class="summary-line"><span>Taxa de Entrega</span><span>€{{ deliveryFee | number:'1.2-2' }}</span></div><div class="summary-line total"><span>Total</span><span>€{{ getTotal() | number:'1.2-2' }}</span></div></div>
        </div>
        <div class="delivery-section">
          <h2>Dados de Entrega</h2>
          <form [formGroup]="checkoutForm" (ngSubmit)="placeOrder()">
            <div class="form-group"><label>Endereço</label><input type="text" formControlName="deliveryAddress" placeholder="Rua, número..." required></div>
            <div class="form-group"><label>Observações</label><textarea formControlName="deliveryNotes" rows="3" placeholder="Instruções especiais..."></textarea></div>
            <button type="submit" [disabled]="cartItems.length === 0 || loading" class="btn-order">{{ loading ? 'A enviar...' : '🍕 Confirmar Pedido' }}</button>
          </form>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .checkout-page { max-width: 1000px; margin: 0 auto; padding: 2rem; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
    .btn-back { padding: 0.5rem 1rem; background: rgba(255,255,255,0.9); border: 1.5px solid rgba(255,71,87,0.1); border-radius: 10px; color: #6B5B4D; font-weight: 600; cursor: pointer; transition: all 250ms; font-size: 0.875rem; }
    .btn-back:hover { border-color: #FF4757; color: #FF4757; transform: none; }
    h1 { font-size: 1.5rem; font-weight: 800; margin: 0; color: #2D1F10; }
    h2 { font-size: 1.1rem; font-weight: 700; color: #2D1F10; margin: 0 0 1.25rem; }
    .checkout-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .cart-section { background: white; padding: 1.75rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 2px 12px rgba(45,31,16,0.04); }
    .item { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 0; border-bottom: 1px solid rgba(0,0,0,0.04); }
    .item:last-of-type { border-bottom: none; }
    .item-info h3 { margin: 0; font-size: 0.9rem; font-weight: 600; }
    .item-price { margin: 0.15rem 0 0; font-size: 0.85rem; color: #FF4757; font-weight: 700; }
    .btn-remove { width: 32px; height: 32px; border-radius: 8px; background: rgba(255,71,87,0.06); color: #FF4757; border: none; cursor: pointer; font-size: 0.8rem; transition: all 200ms; display: flex; align-items: center; justify-content: center; }
    .btn-remove:hover { background: rgba(255,71,87,0.12); transform: none; }
    .summary { margin-top: 1rem; padding-top: 1rem; border-top: 2px solid rgba(255,71,87,0.08); }
    .summary-line { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.9rem; color: #6B5B4D; }
    .summary-line.total { font-weight: 800; font-size: 1.1rem; color: #FF4757; padding-top: 0.75rem; border-top: 1px solid rgba(0,0,0,0.04); margin-top: 0.25rem; }
    .delivery-section { background: white; padding: 1.75rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 2px 12px rgba(45,31,16,0.04); }
    .form-group { margin-bottom: 1.25rem; }
    label { display: block; margin-bottom: 0.4rem; color: #6B5B4D; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }
    input, textarea { width: 100%; padding: 0.8rem 1rem; border: 1.5px solid rgba(255,71,87,0.1); border-radius: 12px; font-size: 0.9375rem; background: rgba(255,255,255,0.8); transition: all 250ms; color: #2D1F10; font-family: inherit; }
    input:focus, textarea:focus { outline: none; border-color: #FF4757; box-shadow: 0 0 0 3px rgba(255,71,87,0.08); background: white; }
    .btn-order { width: 100%; padding: 0.9rem; background: linear-gradient(135deg, #FF4757 0%, #FF6B81 100%); color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 300ms; box-shadow: 0 4px 14px rgba(255,71,87,0.25); }
    .btn-order:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,71,87,0.35); }
    .btn-order:disabled { opacity: 0.55; transform: none; }
    @media (max-width: 768px) { .checkout-layout { grid-template-columns: 1fr; } }
  `]
})
export class CheckoutComponent implements OnInit {
  cartItems: any[] = []; checkoutForm!: FormGroup; deliveryFee = 2.50; loading = false;
  constructor(private fb: FormBuilder, private foodService: FoodService, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { const cart = localStorage.getItem('cart'); this.cartItems = cart ? JSON.parse(cart) : []; const user = this.authService.getCurrentUser(); this.checkoutForm = this.fb.group({ deliveryAddress: [user?.address || '', Validators.required], deliveryNotes: [''] }); }
  getSubtotal(): number { return this.cartItems.reduce((sum, item) => sum + item.price, 0); }
  getTotal(): number { return this.getSubtotal() + this.deliveryFee; }
  removeItem(index: number): void { this.cartItems.splice(index, 1); localStorage.setItem('cart', JSON.stringify(this.cartItems)); }
  placeOrder(): void { if (this.cartItems.length === 0) { alert('Carrinho vazio'); return; } this.loading = true; const restaurantId = this.cartItems[0].restaurantId; const orderData = { restaurantId, items: this.cartItems.map(item => ({ menuItemId: item.id, quantity: 1 })), deliveryAddress: this.checkoutForm.get('deliveryAddress')?.value, deliveryNotes: this.checkoutForm.get('deliveryNotes')?.value }; this.foodService.createOrder(orderData).subscribe({ next: (response) => { if (response.success) { localStorage.removeItem('cart'); alert('Pedido criado com sucesso!'); this.router.navigate(['/orders']); } this.loading = false; }, error: () => this.loading = false }); }
  goBack(): void { this.router.navigate(['/restaurants']); }
}
