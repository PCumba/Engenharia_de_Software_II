import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { CategoryService } from '../services/category.service';
import { I18nService } from '../../../core/services/i18n.service';

@Component({
  selector: 'app-categories-list',
  template: `
    <div class="page-container animate-fade-in">
      <div class="page-header">
        <h1>{{ i18n.t('categories.title') }}</h1>
        <button mat-raised-button color="primary" (click)="router.navigate(['/categories/new'])">
          <mat-icon>add</mat-icon> {{ i18n.t('categories.new') }}
        </button>
      </div>
      <div class="loading-container" *ngIf="isLoading"><mat-spinner diameter="40"></mat-spinner></div>
      <div class="empty-state" *ngIf="!isLoading && categories.length === 0">
        <mat-icon>category</mat-icon>
        <p>{{ i18n.t('categories.noCategories') }}</p>
      </div>
      <div class="categories-grid" *ngIf="!isLoading && categories.length > 0">
        <mat-card *ngFor="let cat of categories" class="category-card hoverable-card">
          <div class="category-info">
            <div class="category-color" [style.background-color]="cat.color"></div>
            <div class="category-details">
              <h3>{{ cat.name }}</h3>
              <span class="category-type">{{ cat.type }}</span>
            </div>
          </div>
          <div class="category-actions" *ngIf="cat.user_id !== 0">
            <button mat-icon-button (click)="router.navigate(['/categories', cat.id, 'edit'])"><mat-icon>edit</mat-icon></button>
            <button mat-icon-button color="warn" (click)="onDelete(cat.id)"><mat-icon>delete</mat-icon></button>
          </div>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .page-container { padding: 1.5rem; max-width: 1200px; margin: 0 auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .page-header h1 { font-size: 1.5rem; font-weight: 600; margin: 0; }
    .loading-container { display: flex; justify-content: center; padding: 3rem; }
    .empty-state { text-align: center; padding: 3rem; color: var(--text-secondary); }
    .empty-state mat-icon { font-size: 64px; width: 64px; height: 64px; opacity: 0.3; }
    .categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
    .category-card { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-radius: var(--border-radius-lg); }
    .category-info { display: flex; align-items: center; gap: 1rem; }
    .category-color { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; }
    .category-details h3 { margin: 0; font-size: 0.95rem; }
    .category-type { font-size: 0.8rem; color: var(--text-secondary); text-transform: capitalize; }
    .category-actions { display: flex; }
  `]
})
export class CategoriesListComponent implements OnInit {
  categories: any[] = [];
  isLoading = true;

  constructor(private categoryService: CategoryService, public router: Router, public i18n: I18nService) {}

  ngOnInit(): void { this.load(); }

  load(): void {
    this.isLoading = true;
    this.categoryService.getAll().subscribe({
      next: (res: any) => { this.categories = res.data?.categories || []; this.isLoading = false; },
      error: () => { this.isLoading = false; }
    });
  }

  onDelete(id: number): void {
    if (confirm(this.i18n.t('transactions.confirmDelete'))) {
      this.categoryService.delete(id).subscribe(() => this.load());
    }
  }
}
