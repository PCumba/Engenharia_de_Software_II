import { Component } from '@angular/core';

@Component({
  selector: 'app-footer',
  template: `
    <footer class="footer">
      <div class="footer-content">
        <span class="footer-brand">◈ CryptoMonitor</span>
        <p>&copy; 2026 Crypto Monitor — Engenharia de Software II</p>
      </div>
    </footer>
  `,
  styles: [`
    .footer { background: rgba(13,17,23,0.6); backdrop-filter: blur(10px); border-top: 1px solid rgba(48,54,61,0.5); padding: 1.25rem 1.5rem; }
    .footer-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
    .footer-brand { font-weight: 700; font-size: 0.875rem; background: linear-gradient(135deg, #00D4AA 0%, #7B61FF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    p { margin: 0; font-size: 0.8125rem; color: #6E7681; }
    @media (max-width: 640px) { .footer-content { flex-direction: column; gap: 0.5rem; text-align: center; } }
  `]
})
export class FooterComponent { }
