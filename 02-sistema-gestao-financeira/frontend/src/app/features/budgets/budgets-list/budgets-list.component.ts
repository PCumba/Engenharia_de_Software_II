import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { BudgetService } from '../services/budget.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-budgets-list',
  templateUrl: './budgets-list.component.html',
  styleUrls: ['./budgets-list.component.scss']
})
export class BudgetsListComponent implements OnInit {
  budgets: any[] = [];
  isLoading = true;

  constructor(
    private budgetService: BudgetService,
    private router: Router,
    public i18n: I18nService
  ) {}

  ngOnInit(): void {
    this.loadBudgets();
  }

  loadBudgets(): void {
    this.isLoading = true;
    this.budgetService.getAll().subscribe({
      next: (res: any) => {
        this.budgets = res.data?.budgets || [];
        this.isLoading = false;
      },
      error: () => { this.isLoading = false; }
    });
  }

  getStatusColor(status: string): string {
    const colors: any = { ok: '#4caf50', warning: '#ff9800', exceeded: '#f44336' };
    return colors[status] || '#9e9e9e';
  }

  onEdit(id: number): void {
    this.router.navigate(['/budgets', id, 'edit']);
  }

  onDelete(id: number): void {
    if (confirm(this.i18n.t('transactions.confirmDelete'))) {
      this.budgetService.delete(id).subscribe(() => this.loadBudgets());
    }
  }

  onNew(): void {
    this.router.navigate(['/budgets/new']);
  }
}
