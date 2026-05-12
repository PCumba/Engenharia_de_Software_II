import { Component, OnInit } from '@angular/core';
import { QueueService } from '@core/services/queue.service';
import { AuthService } from '@core/services/auth.service';
import { Router } from '@angular/router';
import { interval, Subscription } from 'rxjs';

@Component({
  selector: 'app-customer-dashboard',
  template: `
    <div class="customer-dashboard">
      <div class="header"><div class="brand"><span>🍽️</span><h1>Fila Refeitório</h1></div><button (click)="logout()" class="btn-logout">Sair</button></div>
      <div class="content">
        <div *ngIf="!myTicket" class="no-ticket">
          <h2>Criar Novo Ticket</h2>
          <p class="hint">Selecione o serviço desejado</p>
          <div class="services-grid">
            <button *ngFor="let service of services" (click)="createTicket(service.id)" class="service-btn" [disabled]="loading">{{ service.name }}</button>
          </div>
          <div class="error-box" *ngIf="error">{{ error }}</div>
        </div>
        <div *ngIf="myTicket" class="ticket-info">
          <div class="ticket-card">
            <p class="ticket-label">Seu Ticket</p>
            <div class="ticket-number">{{ myTicket.ticket.ticket_number }}</div>
            <p class="service">{{ myTicket.ticket.service_name }}</p>
            <div class="status" [ngClass]="myTicket.ticket.status">{{ getStatusLabel(myTicket.ticket.status) }}</div>
            <div class="position" *ngIf="myTicket.ticket.status === 'waiting'">
              <div class="pos-item"><span class="pos-label">Posição na fila</span><span class="pos-value">{{ myTicket.position }}</span></div>
              <div class="pos-item"><span class="pos-label">Tempo estimado</span><span class="pos-value">{{ estimatedWait }} min</span></div>
            </div>
            <button (click)="cancelTicket(myTicket.ticket.id)" class="btn-cancel">Cancelar Ticket</button>
          </div>
          <div class="queue-info">
            <h3>Informações da Fila</h3>
            <div class="info-grid">
              <div class="info-item"><span class="info-icon">👥</span><span class="info-value">{{ queueInfo?.stats?.waiting_count }}</span><span class="info-label">Aguardando</span></div>
              <div class="info-item"><span class="info-icon">📢</span><span class="info-value">{{ queueInfo?.stats?.calling_count }}</span><span class="info-label">Sendo atendidos</span></div>
              <div class="info-item"><span class="info-icon">✅</span><span class="info-value">{{ queueInfo?.stats?.completed_today }}</span><span class="info-label">Completados</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .customer-dashboard { min-height: 100vh; background: linear-gradient(160deg, #FFF8F0 0%, #FFE8D6 50%, #FFDAB9 100%); padding: 2rem; font-family: 'Inter', sans-serif; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; max-width: 800px; margin-left: auto; margin-right: auto; }
    .brand { display: flex; align-items: center; gap: 0.5rem; }
    .brand span { font-size: 1.5rem; }
    h1 { margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; background: linear-gradient(135deg, #FF6B35 0%, #F7C948 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .btn-logout { padding: 0.5rem 1.25rem; background: rgba(255,255,255,0.8); color: #5D4037; border: 1.5px solid rgba(255,107,53,0.15); border-radius: 10px; font-weight: 600; font-size: 0.8125rem; cursor: pointer; transition: all 250ms; backdrop-filter: blur(10px); }
    .btn-logout:hover { color: #D32F2F; border-color: rgba(211,47,47,0.2); background: rgba(211,47,47,0.04); transform: none; }
    .content { max-width: 800px; margin: 0 auto; }
    .no-ticket { background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,107,53,0.08); box-shadow: 0 8px 32px rgba(139,69,19,0.06); text-align: center; animation: scaleIn 0.5s cubic-bezier(0.34,1.56,0.64,1); }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
    .no-ticket h2 { color: #3E2723; font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem; }
    .hint { color: #8D6E63; font-size: 0.9rem; margin-bottom: 1.5rem; }
    .services-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin: 1rem 0; }
    .service-btn { padding: 1.25rem; border: 2px solid rgba(255,107,53,0.12); background: rgba(255,255,255,0.9); border-radius: 14px; cursor: pointer; font-size: 1rem; font-weight: 700; color: #3E2723; transition: all 300ms ease; font-family: 'Inter', sans-serif; }
    .service-btn:hover:not(:disabled) { border-color: #FF6B35; color: #FF6B35; background: rgba(255,107,53,0.04); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.1); }
    .service-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .ticket-info { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; animation: scaleIn 0.5s ease-out; }
    .ticket-card { background: rgba(255,255,255,0.88); backdrop-filter: blur(20px); padding: 2rem; border-radius: 20px; border: 1px solid rgba(255,107,53,0.08); box-shadow: 0 8px 32px rgba(139,69,19,0.06); text-align: center; }
    .ticket-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #8D6E63; margin-bottom: 0.5rem; }
    .ticket-number { font-size: 3rem; font-weight: 900; background: linear-gradient(135deg, #FF6B35 0%, #F7C948 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0.5rem 0; letter-spacing: -0.02em; }
    .service { font-size: 1rem; color: #5D4037; margin: 0.25rem 0 1rem; font-weight: 500; }
    .status { display: inline-block; padding: 0.4rem 1rem; border-radius: 100px; font-weight: 700; font-size: 0.85rem; margin-bottom: 1rem; }
    .status.waiting { background: rgba(247,201,72,0.15); color: #E65100; }
    .status.calling { background: rgba(0,150,136,0.12); color: #00796B; animation: pulse 1.5s ease-in-out infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
    .position { background: rgba(255,107,53,0.04); padding: 1rem; border-radius: 12px; margin-bottom: 1rem; }
    .pos-item { display: flex; justify-content: space-between; align-items: center; padding: 0.3rem 0; }
    .pos-label { color: #8D6E63; font-size: 0.875rem; }
    .pos-value { font-weight: 800; font-size: 1.1rem; color: #FF6B35; }
    .btn-cancel { width: 100%; padding: 0.7rem; background: rgba(211,47,47,0.08); color: #D32F2F; border: 1.5px solid rgba(211,47,47,0.15); border-radius: 12px; font-weight: 700; font-size: 0.875rem; cursor: pointer; transition: all 250ms; }
    .btn-cancel:hover { background: rgba(211,47,47,0.12); transform: none; }
    .queue-info { background: rgba(255,255,255,0.88); backdrop-filter: blur(20px); padding: 2rem; border-radius: 20px; border: 1px solid rgba(255,107,53,0.08); box-shadow: 0 8px 32px rgba(139,69,19,0.06); }
    .queue-info h3 { margin: 0 0 1.25rem; font-size: 1rem; font-weight: 700; color: #3E2723; }
    .info-grid { display: flex; flex-direction: column; gap: 0.75rem; }
    .info-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: rgba(255,107,53,0.04); border-radius: 12px; }
    .info-icon { font-size: 1.25rem; }
    .info-value { font-size: 1.5rem; font-weight: 800; color: #FF6B35; min-width: 2.5rem; }
    .info-label { color: #5D4037; font-size: 0.875rem; font-weight: 500; }
    .error-box { color: #D32F2F; padding: 0.75rem; background: rgba(211,47,47,0.06); border: 1px solid rgba(211,47,47,0.12); border-radius: 10px; margin-top: 1rem; font-size: 0.9rem; }
    @media (max-width: 768px) { .ticket-info { grid-template-columns: 1fr; } .services-grid { grid-template-columns: 1fr; } }
  `]
})
export class CustomerDashboardComponent implements OnInit {
  services: any[] = []; myTicket: any = null; queueInfo: any = null; estimatedWait = 0; loading = false; error = '';
  private refreshSubscription?: Subscription;
  constructor(private queueService: QueueService, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { this.loadServices(); this.loadMyTicket(); this.refreshSubscription = interval(5000).subscribe(() => { if (this.myTicket) { this.loadMyTicket(); } }); }
  loadServices(): void { this.queueService.getServices().subscribe({ next: (response) => { if (response.success) { this.services = response.data; } }, error: (err) => console.error('Erro ao carregar serviços:', err) }); }
  loadMyTicket(): void { this.queueService.getMyTicket().subscribe({ next: (response) => { if (response.success) { this.myTicket = response.data; this.loadQueueInfo(this.myTicket.ticket.service_id); } }, error: () => { this.myTicket = null; } }); }
  loadQueueInfo(serviceId: number): void { this.queueService.getQueueInfo(serviceId).subscribe({ next: (response) => { if (response.success) { this.queueInfo = response.data; this.estimatedWait = response.data.estimatedWait; } }, error: (err) => console.error('Erro:', err) }); }
  createTicket(serviceId: number): void { this.loading = true; this.error = ''; this.queueService.createTicket(serviceId).subscribe({ next: () => { this.loadMyTicket(); this.loading = false; }, error: (err) => { this.error = err.error?.message || 'Erro ao criar ticket'; this.loading = false; } }); }
  cancelTicket(ticketId: number): void { if (confirm('Tem certeza que deseja cancelar o ticket?')) { this.queueService.cancelTicket(ticketId).subscribe({ next: () => { this.myTicket = null; this.queueInfo = null; }, error: (err) => console.error('Erro:', err) }); } }
  getStatusLabel(status: string): string { const labels: any = { waiting: '⏳ Aguardando', calling: '📢 PRÓXIMO!', completed: '✅ Concluído', cancelled: '❌ Cancelado' }; return labels[status] || status; }
  logout(): void { this.authService.logout(); this.router.navigate(['/auth/login']); }
  ngOnDestroy(): void { if (this.refreshSubscription) { this.refreshSubscription.unsubscribe(); } }
}
