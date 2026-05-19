import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';
import { RecentTransaction } from '../models/dashboard.model';

@Component({
  selector: 'app-recent-transactions',
  templateUrl: './recent-transactions.component.html',
  styleUrls: ['./recent-transactions.component.scss']
})
export class RecentTransactionsComponent {
  @Input() transactions: RecentTransaction[] = [];

  constructor(private router: Router) {}

  getTypeIcon(type: string): string {
    const icons: { [key: string]: string } = {
      income: 'arrow_downward',
      expense: 'arrow_upward',
      transfer: 'swap_horiz'
    };
    return icons[type] || 'attach_money';
  }

  getTypeColor(type: string): string {
    const colors: { [key: string]: string } = {
      income: 'income',
      expense: 'expense',
      transfer: 'transfer'
    };
    return colors[type] || '';
  }

  getStatusLabel(status: string): string {
    const labels: { [key: string]: string } = {
      pending: 'Pendente',
      completed: 'Concluída',
      cancelled: 'Cancelada'
    };
    return labels[status] || status;
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value);
  }

  formatDate(dateStr: string): string {
    const date = new Date(dateStr + 'T00:00:00');
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
      return 'Hoje';
    } else if (date.toDateString() === yesterday.toDateString()) {
      return 'Ontem';
    } else {
      return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    }
  }

  navigateToTransactions(): void {
    this.router.navigate(['/transactions']);
  }

  navigateToTransaction(id: number): void {
    this.router.navigate(['/transactions', id]);
  }
}