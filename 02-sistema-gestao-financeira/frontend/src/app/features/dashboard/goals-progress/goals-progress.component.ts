import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';
import { GoalProgress } from '../models/dashboard.model';

@Component({
  selector: 'app-goals-progress',
  templateUrl: './goals-progress.component.html',
  styleUrls: ['./goals-progress.component.scss']
})
export class GoalsProgressComponent {
  @Input() goals: GoalProgress[] = [];

  constructor(private router: Router) {}

  getCategoryIcon(category: string): string {
    const icons: { [key: string]: string } = {
      emergency_fund: 'health_and_safety',
      vacation: 'flight',
      house: 'home',
      car: 'directions_car',
      education: 'school',
      retirement: 'elderly',
      other: 'flag'
    };
    return icons[category] || 'flag';
  }

  getPriorityColor(priority: string): string {
    const colors: { [key: string]: string } = {
      high: '#f44336',
      medium: '#ff9800',
      low: '#4caf50'
    };
    return colors[priority] || '#9e9e9e';
  }

  getProgressColor(percentage: number): string {
    if (percentage >= 100) return '#4caf50';
    if (percentage >= 75) return '#8bc34a';
    if (percentage >= 50) return '#ff9800';
    if (percentage >= 25) return '#ff5722';
    return '#f44336';
  }

  getDaysRemaining(targetDate?: string): string {
    if (!targetDate) return '';

    const target = new Date(targetDate);
    const today = new Date();
    const diffTime = target.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) return 'Vencida';
    if (diffDays === 0) return 'Hoje';
    if (diffDays === 1) return '1 dia restante';
    if (diffDays < 30) return `${diffDays} dias restantes`;
    if (diffDays < 365) {
      const months = Math.floor(diffDays / 30);
      return `${months} ${months === 1 ? 'mês' : 'meses'} restantes`;
    }
    const years = Math.floor(diffDays / 365);
    return `${years} ${years === 1 ? 'ano' : 'anos'} restante`;
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value);
  }

  navigateToGoals(): void {
    this.router.navigate(['/goals']);
  }

  navigateToGoal(id: number): void {
    this.router.navigate(['/goals', id]);
  }
}