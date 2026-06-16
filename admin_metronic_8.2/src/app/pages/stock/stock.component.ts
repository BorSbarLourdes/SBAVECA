import { Component, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { Subscription } from 'rxjs';
import { StateService, StockItem, Supplier, compressImage } from '../state.service';
import { AuthService } from '../../modules/auth';

@Component({
  selector: 'app-stock',
  templateUrl: './stock.component.html',
  styleUrls: [],
})
export class StockComponent implements OnInit, OnDestroy {
  activeTab: 'inventario' | 'proveedores' = 'inventario';
  stock: StockItem[] = [];
  suppliers: Supplier[] = [];

  private subscriptions: Subscription[] = [];

  // Item Modal variables
  isItemModalOpen = false;
  itemId = 0;
  itemName = '';
  itemCategory: 'ingrediente' | 'utensilio' | 'producto' = 'ingrediente';
  itemQuantity = 0;
  itemUnit = 'kg';
  itemUnitWeight: number | null = null;
  itemMinThreshold = 0;
  itemCostPrice = 0;
  itemSupplierId = 0;
  itemImage = '';

  // Supplier Modal variables
  isSupplierModalOpen = false;
  supplierId = 0;
  supplierName = '';
  supplierContact = '';
  supplierPhone = '';
  supplierCatalogStr = '';

  constructor(
    private stateService: StateService,
    private cdr: ChangeDetectorRef,
    private authService: AuthService
  ) {}

  hasAction(action: 'read' | 'create' | 'update' | 'delete'): boolean {
    return this.authService.hasAction(2, action);
  }

  ngOnInit(): void {
    const stockSub = this.stateService.stock$.subscribe((data) => {
      this.stock = data;
      this.cdr.detectChanges();
    });
    this.subscriptions.push(stockSub);

    const supSub = this.stateService.suppliers$.subscribe((data) => {
      this.suppliers = data;
      this.cdr.detectChanges();
    });
    this.subscriptions.push(supSub);
  }

  getSupplierName(supplierId: number): string {
    if (!supplierId || supplierId <= 0) {
      return 'Ninguno';
    }
    const sup = this.suppliers.find((s) => s.id === +supplierId);
    return sup ? sup.name : 'Ninguno';
  }

  // Stock items CRUD
  openNewItem() {
    this.itemId = 0;
    this.itemName = '';
    this.itemCategory = 'ingrediente';
    this.itemQuantity = 0;
    this.itemUnit = 'kg';
    this.itemUnitWeight = null;
    this.itemMinThreshold = 0;
    this.itemCostPrice = 0;
    this.itemSupplierId = 0;
    this.itemImage = '';
    this.isItemModalOpen = true;
  }

  editItem(item: StockItem) {
    this.itemId = item.id;
    this.itemName = item.name;
    this.itemCategory = item.category;
    this.itemQuantity = item.quantity;
    this.itemUnit = item.unit || 'kg';
    this.itemUnitWeight = item.unitWeight ?? null;
    this.itemMinThreshold = item.minThreshold;
    this.itemCostPrice = item.costPrice;
    this.itemSupplierId = item.supplierId || 0;
    this.itemImage = item.image || '';
    this.isItemModalOpen = true;
  }

  deleteItem(id: number) {
    if (confirm('¿Está seguro de que desea eliminar este artículo?')) {
      this.stateService.deleteStockItem(id);
    }
  }

  onFileChange(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        const base64 = e.target.result;
        compressImage(base64).then((compressed: string) => {
          this.itemImage = compressed;
          this.cdr.detectChanges();
        });
      };
      reader.readAsDataURL(file);
    }
  }

  saveItem() {
    if (!this.itemName || this.itemQuantity < 0 || this.itemCostPrice < 0) {
      alert('Por favor complete todos los campos obligatorios con valores válidos.');
      return;
    }

    const item: StockItem = {
      id: this.itemId,
      name: this.itemName,
      category: this.itemCategory,
      quantity: this.itemQuantity,
      minThreshold: this.itemMinThreshold,
      unit: this.itemUnit,
      unitWeight: this.itemUnitWeight !== null ? this.itemUnitWeight : undefined,
      costPrice: this.itemCostPrice,
      supplierId: +this.itemSupplierId,
      image: this.itemImage
    };

    this.stateService.saveStockItem(item);
    this.isItemModalOpen = false;
  }

  // Suppliers CRUD
  openNewSupplier() {
    this.supplierId = 0;
    this.supplierName = '';
    this.supplierContact = '';
    this.supplierPhone = '';
    this.supplierCatalogStr = '';
    this.isSupplierModalOpen = true;
  }

  editSupplier(sup: Supplier) {
    this.supplierId = sup.id;
    this.supplierName = sup.name;
    this.supplierContact = sup.contact;
    this.supplierPhone = sup.phone;
    this.supplierCatalogStr = sup.catalog ? sup.catalog.join(', ') : '';
    this.isSupplierModalOpen = true;
  }

  deleteSupplier(id: number) {
    if (confirm('¿Está seguro de que desea eliminar este proveedor?')) {
      this.stateService.deleteSupplier(id);
    }
  }

  saveSupplier() {
    if (!this.supplierName || !this.supplierContact || !this.supplierPhone) {
      alert('Por favor complete todos los campos obligatorios.');
      return;
    }

    const catalog = this.supplierCatalogStr
      ? this.supplierCatalogStr.split(',').map((x) => x.trim()).filter((x) => x !== '')
      : [];

    const sup: Supplier = {
      id: this.supplierId,
      name: this.supplierName,
      contact: this.supplierContact,
      phone: this.supplierPhone,
      catalog: catalog,
    };

    this.stateService.saveSupplier(sup);
    this.isSupplierModalOpen = false;
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach((sub) => sub.unsubscribe());
  }
}
