import { Component, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { Subscription } from 'rxjs';
import { StateService, Order, StockItem, Receipt, Client } from '../state.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent implements OnInit, OnDestroy {
  todaySales = 0;
  urgentOrdersCount = 0;
  lowStockItems: StockItem[] = [];
  vipClientsCount = 0;
  totalLoyaltyPoints = 0;

  recentReceipts: Receipt[] = [];
  urgentOrdersList: Order[] = [];

  private subscriptions: Subscription[] = [];

  constructor(
    private stateService: StateService,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    // 1. Sales
    const receiptsSub = this.stateService.receipts$.subscribe(data => {
      const todayStr = new Date().toISOString().split('T')[0];
      const todayReceipts = data.filter(r => r.date === todayStr);
      this.todaySales = todayReceipts.reduce((sum, r) => sum + r.total, 0);
      this.recentReceipts = [...data].reverse().slice(0, 5);
      this.cdr.detectChanges();
    });
    this.subscriptions.push(receiptsSub);

    // 2. Orders & Urgency
    const ordersSub = this.stateService.orders$.subscribe(data => {
      const pendingOrders = data.filter(o => o.status !== 'Entregado' && o.status !== 'Cancelado');
      
      this.urgentOrdersList = pendingOrders.filter(o => {
        const delivery = new Date(o.deliveryTime).getTime();
        const now = new Date().getTime();
        const hoursLeft = (delivery - now) / (1000 * 60 * 60);
        return hoursLeft <= 24;
      });

      this.urgentOrdersCount = this.urgentOrdersList.length;
      this.cdr.detectChanges();
    });
    this.subscriptions.push(ordersSub);

    // 3. Stock
    const stockSub = this.stateService.stock$.subscribe(data => {
      this.lowStockItems = data.filter(s => s.quantity < s.minThreshold);
      this.cdr.detectChanges();
    });
    this.subscriptions.push(stockSub);

    // 4. Clients
    const clientsSub = this.stateService.clients$.subscribe(data => {
      this.vipClientsCount = data.filter(c => c.isVIP).length;
      this.totalLoyaltyPoints = data.reduce((sum, c) => sum + c.points, 0);
      this.cdr.detectChanges();
    });
    this.subscriptions.push(clientsSub);
  }

  getDaysLeft(deliveryTimeStr: string): string {
    const delivery = new Date(deliveryTimeStr).getTime();
    const now = new Date().getTime();
    const diffMs = delivery - now;
    const hoursLeft = diffMs / (1000 * 60 * 60);
    if (hoursLeft < 0) {
      return 'Vencido';
    } else if (hoursLeft <= 2) {
      return 'Entrega Inmediata (<2h)';
    } else {
      return `${Math.ceil(hoursLeft)} horas restantes`;
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
}
