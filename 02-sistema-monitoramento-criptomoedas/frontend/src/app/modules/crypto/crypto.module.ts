import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { AuthGuard } from '@core/guards/auth.guard';

import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { PortfolioComponent } from './pages/portfolio/portfolio.component';
import { AlertsComponent } from './pages/alerts/alerts.component';
import { HeaderComponent } from '@shared/components/header/header.component';
import { FooterComponent } from '@shared/components/footer/footer.component';

const routes: Routes = [
  {
    path: '',
    canActivate: [AuthGuard],
    children: [
      { path: 'dashboard', component: DashboardComponent },
      { path: 'portfolio', component: PortfolioComponent },
      { path: 'alerts', component: AlertsComponent },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  }
];

@NgModule({
  declarations: [
    DashboardComponent,
    PortfolioComponent,
    AlertsComponent,
    HeaderComponent,
    FooterComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild(routes),
    ReactiveFormsModule,
    FormsModule
  ]
})
export class CryptoModule { }
