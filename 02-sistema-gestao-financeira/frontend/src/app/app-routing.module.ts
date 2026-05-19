import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Guards
import { AuthGuard } from './core/guards/auth.guard';
import { GuestGuard } from './core/guards/guest.guard';

// Componentes
import { LoginComponent } from './auth/login/login.component';
import { RegisterComponent } from './auth/register/register.component';
import { ForgotPasswordComponent } from './auth/forgot-password/forgot-password.component';
import { DashboardComponent } from './features/dashboard/dashboard.component';

const routes: Routes = [
  // Redirecionamento padrão
  {
    path: '',
    redirectTo: '/dashboard',
    pathMatch: 'full'
  },

  // Rotas de autenticação (apenas para usuários não autenticados)
  {
    path: 'auth',
    canActivate: [GuestGuard],
    children: [
      {
        path: 'login',
        component: LoginComponent,
        title: 'Login - Sistema Financeiro'
      },
      {
        path: 'register',
        component: RegisterComponent,
        title: 'Cadastro - Sistema Financeiro'
      },
      {
        path: 'forgot-password',
        component: ForgotPasswordComponent,
        title: 'Recuperar Senha - Sistema Financeiro'
      },
      {
        path: '',
        redirectTo: 'login',
        pathMatch: 'full'
      }
    ]
  },

  // Dashboard (protegido)
  {
    path: 'dashboard',
    component: DashboardComponent,
    canActivate: [AuthGuard],
    title: 'Dashboard - Sistema Financeiro'
  },

  // Contas (lazy loading)
  {
    path: 'accounts',
    loadChildren: () => import('./features/accounts/accounts.module').then(m => m.AccountsModule),
    canActivate: [AuthGuard]
  },

  // Transações (lazy loading)
  {
    path: 'transactions',
    loadChildren: () => import('./features/transactions/transactions.module').then(m => m.TransactionsModule),
    canActivate: [AuthGuard]
  },

  // Categorias (lazy loading)
  {
    path: 'categories',
    loadChildren: () => import('./features/categories/categories.module').then(m => m.CategoriesModule),
    canActivate: [AuthGuard]
  },

  // Orçamentos (lazy loading)
  {
    path: 'budgets',
    loadChildren: () => import('./features/budgets/budgets.module').then(m => m.BudgetsModule),
    canActivate: [AuthGuard]
  },

  // Metas (lazy loading)
  {
    path: 'goals',
    loadChildren: () => import('./features/goals/goals.module').then(m => m.GoalsModule),
    canActivate: [AuthGuard]
  },

  // Relatórios (lazy loading)
  {
    path: 'reports',
    loadChildren: () => import('./features/reports/reports.module').then(m => m.ReportsModule),
    canActivate: [AuthGuard]
  },

  // Configurações (lazy loading)
  {
    path: 'settings',
    loadChildren: () => import('./features/settings/settings.module').then(m => m.SettingsModule),
    canActivate: [AuthGuard]
  },

  // Página não encontrada
  {
    path: '404',
    loadComponent: () => import('./shared/components/not-found/not-found.component').then(c => c.NotFoundComponent),
    title: 'Página não encontrada'
  },

  // Redirecionamento para 404
  {
    path: '**',
    redirectTo: '/404'
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {
    enableTracing: false, // Apenas para debug
    scrollPositionRestoration: 'top',
    preloadingStrategy: 'lazy' // Pré-carregamento de módulos lazy
  })],
  exports: [RouterModule]
})
export class AppRoutingModule { }