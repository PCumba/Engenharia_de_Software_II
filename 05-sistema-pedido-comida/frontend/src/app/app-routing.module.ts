import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '@core/guards/auth.guard';
import { RestaurantsComponent } from '@modules/restaurants/pages/restaurants.component';
import { MenuComponent } from '@modules/menu/pages/menu.component';
import { CheckoutComponent } from '@modules/checkout/pages/checkout.component';
import { OrdersComponent } from '@modules/orders/pages/orders.component';

const routes: Routes = [
  {
    path: 'auth',
    loadChildren: () => import('@modules/auth/auth.module').then(m => m.AuthModule)
  },
  {
    path: 'restaurants',
    component: RestaurantsComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'menu/:id',
    component: MenuComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'checkout',
    component: CheckoutComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'orders',
    component: OrdersComponent,
    canActivate: [AuthGuard]
  },
  { path: '', redirectTo: '/restaurants', pathMatch: 'full' },
  { path: '**', redirectTo: '/restaurants' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
