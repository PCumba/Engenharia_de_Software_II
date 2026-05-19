import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import { ChartData, ChartOptions, ChartType } from 'chart.js';
import { ExpenseByCategory } from '../models/dashboard.model';

@Component({
  selector: 'app-expense-chart',
  templateUrl: './expense-chart.component.html',
  styleUrls: ['./expense-chart.component.scss']
})
export class ExpenseChartComponent implements OnChanges {
  @Input() data: ExpenseByCategory[] = [];
  @Input() period = 'month';

  chartType: ChartType = 'doughnut';
  chartData: ChartData<'doughnut'> = { labels: [], datasets: [] };
  chartOptions: ChartOptions<'doughnut'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'right',
        labels: {
          padding: 16,
          usePointStyle: true,
          pointStyle: 'circle',
          font: { size: 12 }
        }
      },
      tooltip: {
        callbacks: {
          label: (context) => {
            const value = context.parsed;
            const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
            const percentage = ((value / total) * 100).toFixed(1);
            return ` ${context.label}: R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })} (${percentage}%)`;
          }
        }
      }
    },
    cutout: '65%'
  };

  totalExpense = 0;
  hasData = false;

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['data'] && this.data) {
      this.buildChart();
    }
  }

  private buildChart(): void {
    if (!this.data || this.data.length === 0) {
      this.hasData = false;
      return;
    }

    this.hasData = true;
    this.totalExpense = this.data.reduce((sum, item) => sum + item.total, 0);

    // Calcular percentuais
    const dataWithPercentage = this.data.map(item => ({
      ...item,
      percentage: this.totalExpense > 0 ? (item.total / this.totalExpense) * 100 : 0
    }));

    this.chartData = {
      labels: dataWithPercentage.map(item => item.category),
      datasets: [{
        data: dataWithPercentage.map(item => item.total),
        backgroundColor: dataWithPercentage.map(item => item.color || '#6c757d'),
        borderWidth: 2,
        borderColor: '#ffffff',
        hoverBorderWidth: 3
      }]
    };
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value);
  }

  formatPercentage(value: number): string {
    return `${value.toFixed(1)}%`;
  }

  getPeriodLabel(): string {
    const labels: { [key: string]: string } = {
      week: 'Esta Semana',
      month: 'Este Mês',
      quarter: 'Este Trimestre',
      year: 'Este Ano'
    };
    return labels[this.period] || 'Período';
  }
}