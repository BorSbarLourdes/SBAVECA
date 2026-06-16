import { Component, OnInit } from '@angular/core';
import { StateService, Recipe, StockItem } from '../state.service';

@Component({
  selector: 'app-recetario-costes',
  templateUrl: './recetario-costes.component.html',
  styleUrls: [],
})
export class RecetarioCostesComponent implements OnInit {
  recipes: Recipe[] = [];
  stockItems: StockItem[] = [];
  selectedRecipe: Recipe | null = null;

  // Cost calculations
  ingredientDetails: Array<{ name: string; quantity: number; unit: string; unitCost: number; subtotal: number }> = [];
  productionCost = 0;
  suggestedPrice = 0;
  marginPercent = 50;

  constructor(private stateService: StateService) {}

  ngOnInit(): void {
    this.stateService.recipes$.subscribe(data => {
      this.recipes = data;
      if (this.selectedRecipe) {
        const updated = this.recipes.find(r => r.id === this.selectedRecipe!.id);
        if (updated) {
          this.selectedRecipe = updated;
          this.calculateCosts();
        }
      }
    });
    this.stateService.stock$.subscribe(data => {
      this.stockItems = data;
    });
  }

  selectRecipe(recipe: Recipe) {
    this.selectedRecipe = recipe;
    this.marginPercent = recipe.marginPercent;
    this.calculateCosts();
  }

  calculateCosts() {
    if (!this.selectedRecipe) return;

    this.ingredientDetails = [];
    let total = 0;

    this.selectedRecipe.ingredients.forEach(ing => {
      const stock = this.stockItems.find(s => s.id === Number(ing.stockId));
      if (stock) {
        const getBaseQty = (q: number, u: string, w?: number) => {
          if (!u) return q;
          const lower = u.toLowerCase();
          if (lower.includes('kg') || lower.includes('ltr') || lower.includes('litro')) return q * 1000;
          if (lower.includes('gr') || lower.includes('ml')) return q;
          if (w && w > 0) return q * w;
          return q;
        };

        const ingUnit = ing.unit || stock.unit;
        const ingBase = getBaseQty(ing.quantity, ingUnit, ing.unitWeight);
        const stockBase = getBaseQty(1, stock.unit, stock.unitWeight);
        
        const sub = ingBase * (stock.costPrice / stockBase);
        
        total += sub;
        this.ingredientDetails.push({
          name: stock.name,
          quantity: ing.quantity,
          unit: ingUnit,
          unitCost: stock.costPrice,
          subtotal: sub
        });
      }
    });

    this.productionCost = total;
    this.suggestedPrice = total * (1 + (this.marginPercent / 100));
  }

  saveNewMargin() {
    if (this.selectedRecipe) {
      const updated = { ...this.selectedRecipe, marginPercent: this.marginPercent };
      this.stateService.saveRecipe(updated);
      alert('Margen de ganancia actualizado correctamente.');
      this.calculateCosts();
    }
  }
}
