import { Component } from '@angular/core';
import { AuthService } from './core/services/auth.service';

@Component({
  selector: 'app-root',
  template: `
    <app-header *ngIf="(authService.currentUser$ | async)"></app-header>
    <main class="main-container" [ngClass]="{'authenticated': (authService.currentUser$ | async)}">
      <router-outlet></router-outlet>
    </main>
    <app-footer *ngIf="(authService.currentUser$ | async)"></app-footer>
  `,
  styles: [`
    :host {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-container {
      flex: 1;
      padding: 2rem 1.5rem;
      animation: fadeIn 0.4s ease-out;
    }

    .main-container.authenticated {
      padding: 2rem 1.5rem 4rem 1.5rem;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  `]
})
export class AppComponent {
  constructor(public authService: AuthService) {}
}
