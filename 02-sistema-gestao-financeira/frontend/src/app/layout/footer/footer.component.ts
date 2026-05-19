import { Component } from '@angular/core';

@Component({
  selector: 'app-footer',
  template: `
    <footer class="app-footer">
      <span>© {{ currentYear }} Sistema de Gestão Financeira. Todos os direitos reservados.</span>
    </footer>
  `,
  styles: [`
    .app-footer {
      padding: 0.75rem 1.5rem;
      text-align: center;
      font-size: 0.75rem;
      color: var(--text-secondary);
      border-top: 1px solid rgba(0, 0, 0, 0.08);
      background: var(--surface-color);
    }
  `]
})
export class FooterComponent {
  currentYear = new Date().getFullYear();
}