import { Component, OnInit } from '@angular/core';
import { QueueService } from '@core/services/queue.service';
import { AuthService } from '@core/services/auth.service';
import { Router } from '@angular/router';
import { interval, Subscription } from 'rxjs';

@Component({
  selector: 'app-admin-dashboard',
  template: `
    <div class="admin-dashboard">
      <div class="header-bar"><div class="brand"><span>👨‍💼</span><h1>Administração</h1></div><button (click)="logout()" class="btn-logout">Sair</button></div>
      <div class="services-tabs"><button *ngFor="let service of services" [class.active]="selectedService?.id === service.id" (click)="selectService(service)" class="tab">{{ service.name }}</button></div>
      <div class="admin-content" *ngIf="selectedService">
        <div class="queue-section">
          <h2>Fila — {{ selectedService.name }}</h2>
          <div class="stats"><div class="stat-card"><span class="stat-value">{{ stats?.waiting_count }}</span><span class="stat-label">Aguardando</span></div><div class="stat-card"><span class="stat-value">{{ stats?.calling_count }}</span><span class="stat-label">Atendendo</span></div><div class="stat-card"><span class="stat-value">{{ stats?.completed_today }}</span><span class="stat-label">Completados</span></div></div>
          <button (click)="callNextTicket()" class="btn-call">📢 Chamar Próximo</button>
        </div>
        <div class="current-ticket" *ngIf="currentTicket">
          <p class="ct-label">ATUALMENTE CHAMADO</p>
          <div class="big-number">{{ currentTicket.ticket_number }}</div>
          <p class="customer">{{ currentTicket.user_name }}</p>
          <button (click)="completeTicket(currentTicket.id)" class="btn-complete">✅ Completar</button>
        </div>
        <div class="queue-list">
          <h3>Próximos na Fila</h3>
          <div class="queue-item" *ngFor="let ticket of queue"><div class="ticket-info"><span class="number">{{ ticket.ticket_number }}</span><span class="name">{{ ticket.user_name || 'Anônimo' }}</span></div><span class="time">{{ getWaitTime(ticket.created_at) }}</span></div>
          <div class="empty" *ngIf="queue.length === 0"><p>Nenhum ticket na fila</p></div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .admin-dashboard { min-height: 100vh; background: #FAFAFA; padding: 2rem; font-family: 'Inter', sans-serif; }
    .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; background: white; padding: 1rem 1.5rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
    .brand { display: flex; align-items: center; gap: 0.5rem; }
    .brand span { font-size: 1.3rem; }
    h1 { margin: 0; font-size: 1.25rem; font-weight: 800; color: #1A1A2E; }
    .btn-logout { padding: 0.5rem 1.25rem; background: rgba(211,47,47,0.06); color: #D32F2F; border: 1px solid rgba(211,47,47,0.12); border-radius: 10px; font-weight: 600; font-size: 0.8125rem; cursor: pointer; transition: all 250ms; }
    .btn-logout:hover { background: rgba(211,47,47,0.1); transform: none; }
    .services-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .tab { padding: 0.6rem 1.25rem; background: white; border: 1.5px solid rgba(0,0,0,0.08); border-radius: 100px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 250ms ease; color: #5D4037; }
    .tab:hover { border-color: rgba(255,107,53,0.3); color: #FF6B35; }
    .tab.active { background: linear-gradient(135deg, #FF6B35 0%, #F7C948 100%); color: white; border-color: transparent; box-shadow: 0 4px 14px rgba(255,107,53,0.25); }
    .admin-content { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; max-width: 1400px; }
    .queue-section { background: white; padding: 1.75rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 12px rgba(0,0,0,0.03); }
    .queue-section h2 { margin: 0 0 1.25rem; font-size: 1.1rem; font-weight: 700; color: #1A1A2E; }
    .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
    .stat-card { background: rgba(255,107,53,0.04); padding: 1rem; border-radius: 12px; text-align: center; }
    .stat-value { display: block; font-size: 2rem; font-weight: 900; color: #FF6B35; }
    .stat-label { display: block; color: #8D6E63; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 0.25rem; }
    .btn-call { width: 100%; padding: 0.875rem; background: linear-gradient(135deg, #2E7D32 0%, #43A047 100%); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(46,125,50,0.25); transition: all 300ms; }
    .btn-call:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,125,50,0.35); }
    .current-ticket { background: linear-gradient(145deg, #FF6B35 0%, #E65100 100%); color: white; padding: 2rem; border-radius: 16px; text-align: center; box-shadow: 0 8px 28px rgba(255,107,53,0.3); }
    .ct-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.85; margin-bottom: 0.5rem; }
    .big-number { font-size: 3.5rem; font-weight: 900; letter-spacing: -0.02em; animation: pulse 2s ease-in-out infinite; }
    @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } }
    .customer { font-size: 1.1rem; margin: 0.75rem 0; font-weight: 500; }
    .btn-complete { width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.2); color: white; border: 1.5px solid rgba(255,255,255,0.3); border-radius: 10px; font-weight: 700; font-size: 0.875rem; cursor: pointer; transition: all 250ms; backdrop-filter: blur(4px); margin-top: 0.5rem; }
    .btn-complete:hover { background: rgba(255,255,255,0.3); transform: none; }
    .queue-list { background: white; padding: 1.75rem; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 12px rgba(0,0,0,0.03); grid-column: 1 / -1; }
    .queue-list h3 { margin: 0 0 1rem; font-size: 1rem; font-weight: 700; }
    .queue-item { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.04); transition: background 200ms; }
    .queue-item:hover { background: rgba(255,107,53,0.02); }
    .queue-item:last-child { border-bottom: none; }
    .ticket-info { display: flex; align-items: center; gap: 1rem; }
    .number { font-size: 1.25rem; font-weight: 800; color: #FF6B35; min-width: 3rem; }
    .name { color: #5D4037; font-weight: 500; }
    .time { color: #8D6E63; font-size: 0.85rem; font-weight: 500; }
    .empty { text-align: center; color: #8D6E63; padding: 2rem; }
    @media (max-width: 768px) { .admin-content { grid-template-columns: 1fr; } }
  `]
})
export class AdminDashboardComponent implements OnInit {
  services: any[] = []; selectedService: any = null; queue: any[] = []; currentTicket: any = null; stats: any = null;
  private refreshSubscription?: Subscription;
  constructor(private queueService: QueueService, private authService: AuthService, private router: Router) {}
  ngOnInit(): void { this.loadServices(); this.refreshSubscription = interval(3000).subscribe(() => { if (this.selectedService) { this.loadQueue(); } }); }
  loadServices(): void { this.queueService.getServices().subscribe({ next: (response) => { if (response.success) { this.services = response.data; if (this.services.length > 0) { this.selectService(this.services[0]); } } }, error: (err) => console.error('Erro:', err) }); }
  selectService(service: any): void { this.selectedService = service; this.loadQueue(); this.loadStats(); }
  loadQueue(): void { this.queueService.getQueue(this.selectedService.id).subscribe({ next: (response) => { if (response.success) { this.queue = response.data.filter((t: any) => t.status !== 'calling'); this.currentTicket = response.data.find((t: any) => t.status === 'calling'); } }, error: (err) => console.error('Erro:', err) }); }
  loadStats(): void { this.queueService.getStats(this.selectedService.id).subscribe({ next: (response) => { if (response.success) { this.stats = response.data; } }, error: (err) => console.error('Erro:', err) }); }
  callNextTicket(): void { this.queueService.callNextTicket(this.selectedService.id).subscribe({ next: () => { this.loadQueue(); this.loadStats(); }, error: (err) => alert(err.error?.message || 'Erro ao chamar próximo') }); }
  completeTicket(ticketId: number): void { this.queueService.completeTicket(ticketId).subscribe({ next: () => { this.loadQueue(); this.loadStats(); }, error: (err) => console.error('Erro:', err) }); }
  getWaitTime(createdAt: string): string { const created = new Date(createdAt); const now = new Date(); const diff = Math.floor((now.getTime() - created.getTime()) / 60000); return `${diff}m`; }
  logout(): void { this.authService.logout(); this.router.navigate(['/auth/login']); }
  ngOnDestroy(): void { if (this.refreshSubscription) { this.refreshSubscription.unsubscribe(); } }
}
