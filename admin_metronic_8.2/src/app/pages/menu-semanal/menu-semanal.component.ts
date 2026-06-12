import { Component, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { Subscription } from 'rxjs';
import { StateService, WeeklyMenu, Recipe, StockItem } from '../state.service';

@Component({
  selector: 'app-menu-semanal',
  templateUrl: './menu-semanal.component.html',
  styleUrls: []
})
export class MenuSemanalComponent implements OnInit, OnDestroy {
  menu: WeeklyMenu = {
    lunes: [], martes: [], miercoles: [], jueves: [], viernes: [], sabado: [], domingo: []
  };
  recipes: Recipe[] = [];
  stockItems: StockItem[] = [];

  // Temp selections for adding recipes
  selectedRecipes: { [day: string]: string } = {};

  // Stock validation variables
  isStockSufficient = true;
  stockWarnings: Array<{ name: string; required: number; available: number; unit: string }> = [];

  private subscriptions: Subscription[] = [];

  daysList: Array<{ key: keyof WeeklyMenu; name: string }> = [
    { key: 'lunes', name: 'Lunes' },
    { key: 'martes', name: 'Martes' },
    { key: 'miercoles', name: 'Miércoles' },
    { key: 'jueves', name: 'Jueves' },
    { key: 'viernes', name: 'Viernes' },
    { key: 'sabado', name: 'Sábado' },
    { key: 'domingo', name: 'Domingo' }
  ];

  constructor(
    private stateService: StateService,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    const menuSub = this.stateService.menu$.subscribe(data => {
      this.menu = JSON.parse(JSON.stringify(data || {
        lunes: [], martes: [], miercoles: [], jueves: [], viernes: [], sabado: [], domingo: []
      }));
      this.validateStock();
      this.cdr.detectChanges();
    });
    this.subscriptions.push(menuSub);

    const recipesSub = this.stateService.recipes$.subscribe(data => {
      this.recipes = data;
      this.validateStock();
      this.cdr.detectChanges();
    });
    this.subscriptions.push(recipesSub);

    const stockSub = this.stateService.stock$.subscribe(data => {
      this.stockItems = data;
      this.validateStock();
      this.cdr.detectChanges();
    });
    this.subscriptions.push(stockSub);
  }

  getRecipeName(recipeId: number): string {
    const recipe = this.recipes.find(r => r.id === +recipeId);
    return recipe ? recipe.name : 'Receta Eliminada';
  }

  addRecipe(day: keyof WeeklyMenu) {
    const rId = Number(this.selectedRecipes[day]);
    if (!rId) return;

    if (!this.menu[day]) {
      this.menu[day] = [];
    }
    this.menu[day].push(rId);
    this.selectedRecipes[day] = ''; // Reset selection
    this.saveMenu();
  }

  removeRecipe(day: keyof WeeklyMenu, idx: number) {
    if (this.menu[day]) {
      this.menu[day].splice(idx, 1);
      this.saveMenu();
    }
  }

  saveMenu() {
    this.stateService.saveMenu(this.menu);
    this.validateStock();
  }

  validateStock() {
    const needed: { [stockId: number]: number } = {};
    const days: Array<keyof WeeklyMenu> = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    days.forEach(day => {
      const recipeIds = this.menu[day] || [];
      recipeIds.forEach(rId => {
        const recipe = this.recipes.find(r => r.id === rId);
        if (recipe) {
          recipe.ingredients.forEach(ing => {
            needed[ing.stockId] = (needed[ing.stockId] || 0) + ing.quantity;
          });
        }
      });
    });

    this.stockWarnings = [];
    this.isStockSufficient = true;

    for (const stockIdStr in needed) {
      const stockId = Number(stockIdStr);
      const qtyNeeded = needed[stockId];
      const stockItem = this.stockItems.find(s => s.id === stockId);
      if (stockItem) {
        if (qtyNeeded > stockItem.quantity) {
          this.isStockSufficient = false;
          this.stockWarnings.push({
            name: stockItem.name,
            required: Number(qtyNeeded.toFixed(3)),
            available: Number(stockItem.quantity.toFixed(3)),
            unit: stockItem.unit
          });
        }
      }
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
}
