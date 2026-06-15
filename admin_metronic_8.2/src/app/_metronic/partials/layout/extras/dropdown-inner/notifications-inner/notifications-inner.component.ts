import { Component, HostBinding, OnInit } from '@angular/core';
import { LayoutService } from '../../../../../layout';

export type NotificationsTabsType =
  | 'kt_topbar_notifications_1'
  | 'kt_topbar_notifications_2'
  | 'kt_topbar_notifications_3';

@Component({
  selector: 'app-notifications-inner',
  templateUrl: './notifications-inner.component.html',
})
export class NotificationsInnerComponent implements OnInit {
  @HostBinding('class') class =
    'menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px';
  @HostBinding('attr.data-kt-menu') dataKtMenu = 'true';

  activeTabId: NotificationsTabsType = 'kt_topbar_notifications_2';
  alerts: Array<AlertModel> = defaultAlerts;
  logs: Array<LogModel> = defaultLogs;
  constructor() {}

  ngOnInit(): void {}

  setActiveTabId(tabId: NotificationsTabsType) {
    this.activeTabId = tabId;
  }
}

interface AlertModel {
  title: string;
  description: string;
  time: string;
  icon: string;
  state: 'primary' | 'danger' | 'warning' | 'success' | 'info';
}

const defaultAlerts: Array<AlertModel> = [
  {
    title: 'Stock Crítico de Insumos',
    description: 'Harina 000 y Leche Entera están bajo el mínimo.',
    time: '15 min',
    icon: 'icons/duotune/general/gen044.svg',
    state: 'danger',
  },
  {
    title: 'Nueva Venta Registrada',
    description: 'Pedido #4023 pagado con MercadoPago.',
    time: '1 hora',
    icon: 'icons/duotune/finance/fin006.svg',
    state: 'success',
  },
  {
    title: 'Pedido para Delivery',
    description: 'Enviar pedido a Calle Falsa 123.',
    time: '2 horas',
    icon: 'icons/duotune/maps/map001.svg',
    state: 'primary',
  },
  {
    title: 'Recetario Actualizado',
    description: 'Se agregó "Milanesa con Puré" al recetario.',
    time: '1 día',
    icon: 'icons/duotune/files/fil023.svg',
    state: 'info',
  },
  {
    title: 'Menú Semanal Listo',
    description: 'Menú para la semana del 15 de Junio listo.',
    time: '2 días',
    icon: 'icons/duotune/art/art002.svg',
    state: 'warning',
  },
];

interface LogModel {
  code: string;
  state: 'success' | 'danger' | 'warning';
  message: string;
  time: string;
}

const defaultLogs: Array<LogModel> = [
  { code: 'OK', state: 'success', message: 'Venta registrada exitosamente', time: 'Ahora' },
  { code: 'OK', state: 'success', message: 'MercadoPago Webhook recibido', time: '5 min' },
  { code: 'ALERTA', state: 'warning', message: 'Insumo "Azúcar" bajo el mínimo', time: '2 horas' },
  { code: 'ERROR', state: 'danger', message: 'Error al sincronizar caja diaria', time: '5 horas' },
  { code: 'OK', state: 'success', message: 'Nueva receta guardada en recetario', time: '1 día' },
  { code: 'OK', state: 'success', message: 'Respaldo de Base de Datos completado', time: 'Mar 5' },
  { code: 'ALERTA', state: 'warning', message: 'Cierre de caja con diferencia menor', time: 'May 15' },
];

