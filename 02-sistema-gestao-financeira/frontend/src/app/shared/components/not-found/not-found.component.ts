import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-not-found',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule],
  template: `
    <div class="not-found-container">
      <div class="not-found-content">
        <mat-icon class="error-icon">search_off</mat-icon>
        <h1>404</h1>
        <h2>Página não encontrada</h2>
        <p>A página que você está procurando não existe ou foi movida.</p>
        <button mat-raised-button color="primary" (click)="goHome()">
          <mat-icon>home</mat-icon>
          Voltar ao início
        </button>
      </div>
    </div>
  `,
  styles: [`
    .not-found-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: var(--background-color);
    }

    .not-found-content {
      text-align: center;
      padding: 2rem;

      .error-icon {
        font-size: 5rem;
        width: 5rem;
        height: 5rem;
        color: var(--text-secondary);
        opacity: 0.4;
        margin-bottom: 1rem;
      }

      h1 {
        font-size: 6rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
      }

      h2 {
        font-size: 1.5rem;
        margin: 0.5rem 0;
        color: var(--text-color);
      }

      p {
        color: var(--text-secondary);
        margin-bottom: 2rem;
      }

      button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
      }
    }
  `]
})
export class NotFoundComponent {
  constructor(private router: Router) {}

  goHome(): void {
    this.router.navigate(['/dashboard']);
  }
}