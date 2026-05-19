import { Component } from '@angular/core';
import { I18nService } from '@core/services/i18n.service';

@Component({
  selector: 'app-footer',
  template: `
    <footer class="footer">
      <div class="footer-content">
        <span class="footer-brand">☁️ {{ i18n.t('app.title') }}</span>
        <p>{{ i18n.t('footer.copyright') }}</p>
      </div>
    </footer>
  `,
  styles: [`
    .footer {
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border-top: 1px solid rgba(74, 144, 217, 0.08);
      padding: 1.25rem 1.5rem;
    }
    .footer-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
    .footer-brand { font-weight: 700; font-size: 0.875rem; color: #4A90D9; }
    p { margin: 0; font-size: 0.8125rem; color: #8896A6; }
    @media (max-width: 640px) {
      .footer-content { flex-direction: column; gap: 0.5rem; text-align: center; }
    }
  `]
})
export class FooterComponent {
  constructor(public i18n: I18nService) {}
}
