import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { CryptoService } from '@core/services/crypto.service';

@Component({
  selector: 'app-alerts',
  template: `
    <div class="alerts-container">
      <div class="page-header"><h1>Alertas de Preço</h1><p class="page-subtitle">Receba notificações quando o preço atingir o valor alvo</p></div>
      <div class="create-alert">
        <h2>Criar Novo Alerta</h2>
        <form [formGroup]="alertForm" (ngSubmit)="createAlert()" class="alert-form">
          <div class="form-group"><label>Criptomoeda</label><select formControlName="cryptoId"><option value="">Selecionar...</option><option value="bitcoin">Bitcoin (BTC)</option><option value="ethereum">Ethereum (ETH)</option><option value="cardano">Cardano (ADA)</option><option value="polkadot">Polkadot (DOT)</option></select></div>
          <div class="form-group"><label>Preço Alvo (USD)</label><input type="number" formControlName="priceTarget" placeholder="50000" step="0.01"></div>
          <div class="form-group"><label>Tipo</label><select formControlName="alertType"><option value="above">Acima de</option><option value="below">Abaixo de</option></select></div>
          <button type="submit" class="btn-submit" [disabled]="!alertForm.valid || loading">{{ loading ? 'A criar...' : 'Criar Alerta' }}</button>
        </form>
      </div>
      <div class="alerts-list">
        <h2>Meus Alertas</h2>
        <div class="alert-item" *ngFor="let alert of alerts">
          <div class="alert-info"><h3>{{ alert.symbol }}</h3><p class="alert-type" [ngClass]="alert.alert_type">{{ alert.alert_type === 'above' ? '📈 Acima de' : '📉 Abaixo de' }} {{ alert.price_target | currency }}</p><p class="timestamp">Criado em {{ alert.created_at }}</p></div>
          <button (click)="disableAlert(alert.id)" class="btn-delete">Desativar</button>
        </div>
        <div class="empty" *ngIf="alerts.length === 0"><p>Nenhum alerta ativo. Cria um novo!</p></div>
      </div>
    </div>
  `,
  styles: [`
    .alerts-container { max-width: 800px; margin: 0 auto; padding: 2rem 1.5rem; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; }
    .page-subtitle { color: #8B949E; font-size: 0.95rem; margin: 0.25rem 0 0; }
    h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.25rem; }
    .create-alert { background: rgba(33,38,45,0.8); border: 1px solid rgba(48,54,61,0.6); padding: 1.75rem; border-radius: 16px; margin-bottom: 2rem; }
    .alert-form { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { display: flex; flex-direction: column; }
    .form-group:last-of-type { grid-column: 1; }
    label { margin-bottom: 0.4rem; color: #8B949E; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    input, select { padding: 0.75rem 1rem; border: 1.5px solid rgba(48,54,61,0.8); border-radius: 12px; background: rgba(22,27,34,0.8); color: #E6EDF3; font-size: 0.9375rem; transition: all 250ms; }
    input:focus, select:focus { outline: none; border-color: #00D4AA; box-shadow: 0 0 0 3px rgba(0,212,170,0.1); }
    input::placeholder { color: #6E7681; }
    .btn-submit { grid-column: 1 / -1; padding: 0.85rem; background: linear-gradient(135deg, #00D4AA 0%, #00B894 100%); color: #0D1117; border: none; border-radius: 12px; font-weight: 700; font-size: 0.9375rem; cursor: pointer; transition: all 300ms ease; box-shadow: 0 4px 14px rgba(0,212,170,0.2); }
    .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,212,170,0.3); }
    .btn-submit:disabled { opacity: 0.55; transform: none; cursor: not-allowed; }
    .alerts-list { margin-top: 2rem; }
    .alert-item { background: rgba(33,38,45,0.8); border: 1px solid rgba(48,54,61,0.6); padding: 1.25rem; border-radius: 16px; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; transition: all 250ms ease; }
    .alert-item:hover { border-color: rgba(0,212,170,0.15); transform: translateY(-1px); }
    .alert-info h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
    .alert-type { margin: 0.4rem 0 0; font-weight: 600; font-size: 0.9rem; }
    .alert-type.above { color: #3FB950; }
    .alert-type.below { color: #F85149; }
    .timestamp { margin: 0.3rem 0 0; color: #6E7681; font-size: 0.8rem; }
    .btn-delete { padding: 0.5rem 1rem; background: rgba(248,81,73,0.1); color: #F85149; border: 1px solid rgba(248,81,73,0.2); border-radius: 10px; font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 250ms; }
    .btn-delete:hover { background: rgba(248,81,73,0.15); transform: none; }
    .empty { text-align: center; padding: 2rem; color: #6E7681; background: rgba(33,38,45,0.4); border-radius: 12px; }
    @media (max-width: 768px) { .alert-form { grid-template-columns: 1fr; } }
  `]
})
export class AlertsComponent implements OnInit {
  alerts: any[] = []; alertForm!: FormGroup; loading = false;
  constructor(private cryptoService: CryptoService, private fb: FormBuilder) {}
  ngOnInit(): void { this.alertForm = this.fb.group({ cryptoId: ['', Validators.required], priceTarget: ['', [Validators.required, Validators.min(0)]], alertType: ['above', Validators.required] }); this.loadAlerts(); }
  loadAlerts(): void { this.cryptoService.getPriceAlerts().subscribe({ next: (response) => { if (response.success) { this.alerts = response.data; } }, error: (err) => console.error('Erro ao carregar alertas:', err) }); }
  createAlert(): void { if (!this.alertForm.valid) return; this.loading = true; this.cryptoService.createPriceAlert(this.alertForm.value.cryptoId, this.alertForm.value.priceTarget, this.alertForm.value.alertType).subscribe({ next: () => { this.alertForm.reset({ alertType: 'above' }); this.loadAlerts(); this.loading = false; }, error: (err) => { console.error('Erro ao criar alerta:', err); this.loading = false; } }); }
  disableAlert(alertId: number): void { if (confirm('Tem certeza que deseja desativar este alerta?')) { this.cryptoService.disableAlert(alertId).subscribe({ next: () => this.loadAlerts(), error: (err: any) => console.error('Erro ao desativar:', err) }); } }
}
