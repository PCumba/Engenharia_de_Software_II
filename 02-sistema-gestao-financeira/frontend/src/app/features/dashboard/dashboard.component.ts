import { Component, OnInit, OnDestroy } from '@angular/core';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';

import { DashboardService } from './services/dashboard.service';
import { AuthService } from '../../core/services/auth.service';
import { User } from '../../core/models/user.model';
import { DashboardData, FinancialSummary } from './models/dashboard.model';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit, OnDestroy {
  private destroy$ = new Subject<void>();
  
  user: User | null = null;
  dashboardData: DashboardData | null = null;
  isLoading = true;
  selectedPeriod = 'month';
  
  // Dados para os cards de resumo
  financialSummary: FinancialSummary = {
    totalBalance: 0,
    monthlyIncome: 0,
    monthlyExpense: 0,
    monthlyBalance: 0,
    previousMonthBalance: 0,
    balanceChange: 0,
    balanceChangePercentage: 0
  };

  // Opções de período
  periodOptions = [
    { value: 'week', label: 'Esta Semana' },
    { value: 'month', label: 'Este Mês' },
    { value: 'quarter', label: 'Este Trimestre' },
    { value: 'year', label: 'Este Ano' }
  ];

  constructor(
    private dashboardService: DashboardService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.loadUserData();
    this.loadDashboardData();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private loadUserData(): void {
    this.authService.currentUser$
      .pipe(takeUntil(this.destroy$))
      .subscribe(user => {
        this.user = user;
      });
  }

  private loadDashboardData(): void {
    this.isLoading = true;
    
    this.dashboardService.getDashboardData(this.selectedPeriod)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (data) => {
          this.dashboardData = data;
          this.financialSummary = data.summary;
          this.isLoading = false;
        },
        error: (error) => {
          console.error('Erro ao carregar dados do dashboard:', error);
          this.isLoading = false;
        }
      });
  }

  onPeriodChange(period: string): void {
    this.selectedPeriod = period;
    this.loadDashboardData();
  }

  refreshData(): void {
    this.loadDashboardData();
  }

  getGreeting(): string {
    const hour = new Date().getHours();
    const name = this.user?.name?.split(' ')[0] || 'Usuário';
    
    if (hour < 12) {
      return `Bom dia, ${name}!`;
    } else if (hour < 18) {
      return `Boa tarde, ${name}!`;
    } else {
      return `Boa noite, ${name}!`;
    }
  }

  getBalanceChangeIcon(): string {
    if (this.financialSummary.balanceChange > 0) {
      return 'trending_up';
    } else if (this.financialSummary.balanceChange < 0) {
      return 'trending_down';
    } else {
      return 'trending_flat';
    }
  }

  getBalanceChangeColor(): string {
    if (this.financialSummary.balanceChange > 0) {
      return 'success';
    } else if (this.financialSummary.balanceChange < 0) {
      return 'warn';
    } else {
      return 'primary';
    }
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: this.user?.currency || 'BRL'
    }).format(value);
  }

  formatPercentage(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
      style: 'percent',
      minimumFractionDigits: 1,
      maximumFractionDigits: 1
    }).format(value / 100);
  }

  // Métodos para navegação rápida
  navigateToTransactions(): void {
    // Implementar navegação para transações
  }

  navigateToAccounts(): void {
    // Implementar navegação para contas
  }

  navigateToGoals(): void {
    // Implementar navegação para metas
  }

  navigateToReports(): void {
    // Implementar navegação para relatórios
  }
}