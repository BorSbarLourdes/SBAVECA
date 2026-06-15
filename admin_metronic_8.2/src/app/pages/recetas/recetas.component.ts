import { Component, OnInit } from '@angular/core';
import { StateService, Recipe, RecipeIngredient, StockItem } from '../state.service';

@Component({
  selector: 'app-recetas',
  templateUrl: './recetas.component.html',
  styleUrls: [],
})
export class RecetasComponent implements OnInit {
  recipes: Recipe[] = [];
  stockItems: StockItem[] = [];

  isModalOpen = false;

  // Recipe Form
  formId = 0;
  formName = '';
  formInstructions = '';
  formMarginPercent = 50;
  formIngredients: RecipeIngredient[] = [];
  formImage = '';

  // Add new ingredient temp variables
  tempStockId = 0;
  tempQuantity = 0;

  constructor(private stateService: StateService) {}

  ngOnInit(): void {
    this.stateService.recipes$.subscribe(data => {
      this.recipes = data;
    });
    this.stateService.stock$.subscribe(data => {
      this.stockItems = data.filter(s => s.category === 'ingrediente');
      if (this.stockItems.length > 0) {
        this.tempStockId = this.stockItems[0].id;
      }
    });
  }

  getIngredientName(stockId: number): string {
    const item = this.stockItems.find(s => s.id === Number(stockId));
    return item ? item.name : 'Ingrediente Desconocido';
  }

  getIngredientUnit(stockId: number): string {
    const item = this.stockItems.find(s => s.id === Number(stockId));
    return item ? item.unit : '';
  }

  addTempIngredient() {
    if (this.tempQuantity <= 0) {
      alert('La cantidad debe ser mayor a 0.');
      return;
    }
    const exists = this.formIngredients.find(i => Number(i.stockId) === Number(this.tempStockId));
    if (exists) {
      exists.quantity += this.tempQuantity;
    } else {
      this.formIngredients.push({
        stockId: Number(this.tempStockId),
        quantity: this.tempQuantity
      });
    }
    this.tempQuantity = 0;
  }

  removeIngredient(index: number) {
    this.formIngredients.splice(index, 1);
  }

  openNew() {
    this.formId = 0;
    this.formName = '';
    this.formInstructions = '';
    this.formMarginPercent = 50;
    this.formIngredients = [];
    this.formImage = '';
    this.tempQuantity = 0;
    this.isModalOpen = true;
  }

  editRecipe(recipe: Recipe) {
    this.formId = recipe.id;
    this.formName = recipe.name;
    this.formInstructions = recipe.instructions;
    this.formMarginPercent = recipe.marginPercent;
    this.formImage = recipe.image || '';
    // deep clone
    this.formIngredients = recipe.ingredients.map(i => ({ ...i }));
    this.tempQuantity = 0;
    this.isModalOpen = true;
  }

  onFileChange(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.formImage = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  save() {
    if (!this.formName || this.formIngredients.length === 0) {
      alert('Por favor, complete el nombre de la receta y añada al menos un ingrediente.');
      return;
    }

    const recipe: Recipe = {
      id: this.formId,
      name: this.formName,
      ingredients: this.formIngredients,
      instructions: this.formInstructions,
      marginPercent: this.formMarginPercent,
      image: this.formImage
    };

    this.stateService.saveRecipe(recipe);
    this.isModalOpen = false;
  }

  deleteRecipe(id: number) {
    if (confirm('¿Está seguro de eliminar esta receta?')) {
      this.stateService.deleteRecipe(id);
    }
  }

  getCost(recipeId: number): number {
    return this.stateService.getRecipeCost(recipeId);
  }
}
