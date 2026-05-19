import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { GoalService } from '../services/goal.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-goals-list',
  templateUrl: './goals-list.component.html',
  styleUrls: ['./goals-list.component.scss']
})
export class GoalsListComponent implements OnInit {
  goals: any[] = [];
  isLoading = true;

  constructor(private goalService: GoalService, private router: Router, public i18n: I18nService) {}

  ngOnInit(): void { this.loadGoals(); }

  loadGoals(): void {
    this.isLoading = true;
    this.goalService.getAll().subscribe({
      next: (res: any) => { this.goals = res.data?.goals || []; this.isLoading = false; },
      error: () => { this.isLoading = false; }
    });
  }

  getPriorityColor(priority: string): string {
    return { high: '#f44336', medium: '#ff9800', low: '#4caf50' }[priority] || '#9e9e9e';
  }

  onEdit(id: number): void { this.router.navigate(['/goals', id, 'edit']); }
  onNew(): void { this.router.navigate(['/goals/new']); }
  onDelete(id: number): void {
    if (confirm(this.i18n.t('transactions.confirmDelete'))) {
      this.goalService.delete(id).subscribe(() => this.loadGoals());
    }
  }
}
