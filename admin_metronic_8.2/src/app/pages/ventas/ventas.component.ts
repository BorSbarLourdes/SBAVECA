import { Component, OnInit } from '@angular/core';
import { StateService, Recipe, Client, Receipt, StockItem } from '../state.service';

interface CartItem {
  recipeId: number;
  name: string;
  qty: number;
  unitPrice: number;
  subtotal: number;
}

@Component({
  selector: 'app-ventas',
  templateUrl: './ventas.component.html',
  styleUrls: [],
})
export class VentasComponent implements OnInit {
  recipes: Recipe[] = [];
  clients: Client[] = [];
  receipts: Receipt[] = [];
  stockItems: StockItem[] = [];

  // POS State
  cart: CartItem[] = [];
  selectedClientId = '';
  selectedClient: Client | null = null;
  paymentMethod: 'Efectivo' | 'Transferencia' = 'Efectivo';
  channel: string = 'presencial';

  // Recipe selection in POS
  currentRecipeId = '';
  currentQty = 1;

  // Invoice / Receipt Drawer
  showReceiptModal = false;
  activeReceipt: Receipt | null = null;

  // Search receipts history
  searchQueryReceipt = '';

  constructor(private stateService: StateService) {}

  ngOnInit(): void {
    this.stateService.recipes$.subscribe(data => {
      this.recipes = data;
    });
    this.stateService.clients$.subscribe(data => {
      this.clients = data;
      this.updateSelectedClient();
    });
    this.stateService.receipts$.subscribe(data => {
      this.receipts = [...data].reverse(); // Most recent first
    });
    this.stateService.stock$.subscribe(data => {
      this.stockItems = data;
    });
  }

  // Calculate dynamic price based on stock costs and margin
  getRecipePrice(recipeId: number): number {
    const cost = this.stateService.getRecipeCost(recipeId);
    const recipe = this.recipes.find(r => r.id === recipeId);
    if (!recipe) return 0;
    return cost * (1 + (recipe.marginPercent / 100));
  }

  onClientChange() {
    this.updateSelectedClient();
  }

  updateSelectedClient() {
    if (this.selectedClientId) {
      const client = this.clients.find(c => c.id === Number(this.selectedClientId));
      this.selectedClient = client ? client : null;
    } else {
      this.selectedClient = null;
    }
  }

  addToCart() {
    if (!this.currentRecipeId) return;
    const rid = Number(this.currentRecipeId);
    const recipe = this.recipes.find(r => r.id === rid);
    if (!recipe) return;

    const price = this.getRecipePrice(rid);
    if (price <= 0) {
      alert('Esta receta no tiene ingredientes configurados en stock o su costo es 0. Revise los costos de producción primero.');
      return;
    }

    const qty = Number(this.currentQty);
    if (qty <= 0) return;

    // Check if ingredient stock allows this sale
    let isStockOk = true;
    recipe.ingredients.forEach(ing => {
      const stock = this.stockItems.find(s => s.id === ing.stockId);
      if (stock) {
        const required = ing.quantity * qty;
        if (stock.quantity < required) {
          isStockOk = false;
        }
      }
    });

    if (!isStockOk) {
      const confirmSale = confirm('¡Advertencia! Algunos ingredientes requeridos para este producto están por debajo de la cantidad necesaria en stock. ¿Desea continuar con la venta de todos modos?');
      if (!confirmSale) return;
    }

    // Add to cart
    const existing = this.cart.find(item => item.recipeId === rid);
    if (existing) {
      existing.qty += qty;
      existing.subtotal = existing.qty * existing.unitPrice;
    } else {
      this.cart.push({
        recipeId: rid,
        name: recipe.name,
        qty: qty,
        unitPrice: price,
        subtotal: price * qty
      });
    }

    this.currentRecipeId = '';
    this.currentQty = 1;
  }

  removeFromCart(index: number) {
    this.cart.splice(index, 1);
  }

  getCartSubtotal(): number {
    return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
  }

  getCartDiscount(): number {
    if (this.selectedClient && this.selectedClient.isVIP) {
      return this.getCartSubtotal() * (this.selectedClient.vipDiscount / 100);
    }
    return 0;
  }

  getCartTotal(): number {
    return this.getCartSubtotal() - this.getCartDiscount();
  }

  checkout() {
    if (this.cart.length === 0) {
      alert('El carrito está vacío.');
      return;
    }

    const subtotal = this.getCartSubtotal();
    const discount = this.getCartDiscount();
    const total = this.getCartTotal();

    const clientName = this.selectedClient ? `${this.selectedClient.name} ${this.selectedClient.lastname}` : 'Consumidor Final';

    const newReceipt: Receipt = {
      id: 0,
      date: new Date().toISOString().split('T')[0],
      clientName: clientName,
      details: this.cart.map(item => ({
        productName: item.name,
        qty: item.qty,
        unitPrice: item.unitPrice,
        subtotal: item.subtotal
      })),
      discount: discount,
      total: total,
      method: this.paymentMethod,
      channel: this.channel
    };

    // Deduct stock ingredients
    this.cart.forEach(item => {
      this.stateService.deductIngredientsForRecipe(item.recipeId, item.qty);
    });

    // Update loyalty points if registered client
    if (this.selectedClient) {
      const pointsEarned = Math.floor(total / 100); // 1 point per $100 spent
      const updatedClient = {
        ...this.selectedClient,
        points: this.selectedClient.points + pointsEarned
      };
      this.stateService.saveClient(updatedClient);
      alert(`Venta registrada. Se acumularon ${pointsEarned} puntos de fidelidad para el cliente.`);
    } else {
      alert('Venta registrada correctamente.');
    }

    // Add to State Receipts
    this.stateService.addReceipt(newReceipt);

    // Get the newly added receipt with the generated ID (usually the last one added)
    const allReceipts = this.stateService.receipts$.value;
    const addedReceipt = allReceipts[allReceipts.length - 1];

    // Open receipt invoice drawer
    this.printReceipt(addedReceipt);

    // Reset POS cart
    this.cart = [];
    this.selectedClientId = '';
    this.selectedClient = null;
  }

  printReceipt(receipt: Receipt) {
    this.activeReceipt = receipt;
    this.showReceiptModal = true;
  }

  closeReceiptModal() {
    this.showReceiptModal = false;
    this.activeReceipt = null;
  }

  getFilteredReceipts(): Receipt[] {
    return this.receipts.filter(r => {
      const matchQuery = r.clientName.toLowerCase().includes(this.searchQueryReceipt.toLowerCase()) ||
                          r.method.toLowerCase().includes(this.searchQueryReceipt.toLowerCase()) ||
                          r.details.some(d => d.productName.toLowerCase().includes(this.searchQueryReceipt.toLowerCase()));
      return matchQuery;
    });
  }

  printAction() {
    window.print();
  }
}
