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
        const sub = ing.quantity * stock.costPrice;
        total += sub;
        this.ingredientDetails.push({
          name: stock.name,
          quantity: ing.quantity,
          unit: stock.unit,
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
