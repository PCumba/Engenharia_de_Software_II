import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { User } from '../../core/models/user.model';
import { ThemeService } from '../../core/services/theme.service';
import { I18nService, Language } from '../../core/services/i18n.service';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
})
export class HeaderComponent {
  @Input() user: User | null = null;
  @Input() unreadAlerts = 0;
  @Output() toggleSidenav = new EventEmitter<void>();
  @Output() logout = new EventEmitter<void>();

  constructor(
    private router: Router,
    public themeService: ThemeService,
    public i18n: I18nService
  ) {}

  onToggleSidenav(): void {
    this.toggleSidenav.emit();
  }

  onLogout(): void {
    this.logout.emit();
  }

  navigateToProfile(): void {
    this.router.navigate(['/settings/profile']);
  }

  navigateToAlerts(): void {
    this.router.navigate(['/settings/alerts']);
  }

  navigateToSettings(): void {
    this.router.navigate(['/settings']);
  }

  onLanguageChange(lang: Language): void {
    this.i18n.setLanguage(lang);
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

  getPageTitle(): string {
    const url = this.router.url;
    const titles: { [key: string]: string } = {
      '/dashboard': this.i18n.t('nav.dashboard'),
      '/transactions': this.i18n.t('nav.transactions'),
      '/accounts': this.i18n.t('nav.accounts'),
      '/categories': this.i18n.t('nav.categories'),
      '/budgets': this.i18n.t('nav.budgets'),
      '/goals': this.i18n.t('nav.goals'),
      '/reports': this.i18n.t('nav.reports'),
      '/settings': this.i18n.t('nav.settings')
    };

    for (const [path, title] of Object.entries(titles)) {
      if (url.startsWith(path)) return title;
    }
    return this.i18n.t('app.title');
  }
}