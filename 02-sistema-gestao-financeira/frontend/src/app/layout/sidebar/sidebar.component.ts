import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { User } from '../../core/models/user.model';
import { I18nService } from '../../core/services/i18n.service';

interface NavItem {
  labelKey: string;
  icon: string;
  route: string;
  badge?: number;
}

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent implements OnInit {
  @Input() user: User | null = null;
  @Output() closeSidenav = new EventEmitter<void>();

  currentRoute = '';

  navItems: NavItem[] = [
    { labelKey: 'nav.dashboard', icon: 'dashboard', route: '/dashboard' },
    { labelKey: 'nav.transactions', icon: 'receipt_long', route: '/transactions' },
    { labelKey: 'nav.accounts', icon: 'account_balance', route: '/accounts' },
    { labelKey: 'nav.categories', icon: 'category', route: '/categories' },
    { labelKey: 'nav.budgets', icon: 'pie_chart', route: '/budgets' },
    { labelKey: 'nav.goals', icon: 'flag', route: '/goals' },
    { labelKey: 'nav.reports', icon: 'assessment', route: '/reports' },
    { labelKey: 'nav.settings', icon: 'settings', route: '/settings' }
  ];

  constructor(private router: Router, public i18n: I18nService) {
    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe((event: any) => {
        this.currentRoute = event.urlAfterRedirects;
      });
  }

  ngOnInit(): void {}

  isActive(route: string): boolean {
    return this.currentRoute.startsWith(route);
  }

  navigate(route: string): void {
    this.router.navigate([route]);
    this.closeSidenav.emit();
  }

  getUserInitials(): string {
    if (!this.user?.name) return 'U';
    return this.user.name
      .split(' ')
      .slice(0, 2)
      .map(n => n[0])
      .join('')
      .toUpperCase();
  }
}