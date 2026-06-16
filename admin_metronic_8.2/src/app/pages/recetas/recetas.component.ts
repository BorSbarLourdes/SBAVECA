import { Component, OnInit } from '@angular/core';
import { StateService, Recipe, RecipeIngredient, StockItem, compressImage } from '../state.service';
import { AuthService } from '../../modules/auth';

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
  tempUnit = 'kg';
  tempUnitWeight: number | null = null;

  constructor(private stateService: StateService, private authService: AuthService) {}

  hasAction(action: 'read' | 'create' | 'update' | 'delete'): boolean {
    return this.authService.hasAction(3, action); // 3: Gestión de Recetas
  }

  ngOnInit(): void {
    this.stateService.recipes$.subscribe(data => {
      this.recipes = data;
    });
    this.stateService.stock$.subscribe(data => {
      this.stockItems = data.filter(s => s.category === 'ingrediente');
      if (this.stockItems.length > 0) {
        this.tempStockId = this.stockItems[0].id;
        this.tempUnit = this.stockItems[0].unit || 'kg';
        this.tempUnitWeight = this.stockItems[0].unitWeight ?? null;
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

  onStockChange() {
    const item = this.stockItems.find(s => s.id === Number(this.tempStockId));
    if (item) {
      this.tempUnit = item.unit || 'kg';
      this.tempUnitWeight = item.unitWeight ?? null;
    }
  }

  addTempIngredient() {
    if (this.tempQuantity <= 0) {
      alert('La cantidad debe ser mayor a 0.');
      return;
    }
    const exists = this.formIngredients.find(i => Number(i.stockId) === Number(this.tempStockId) && i.unit === this.tempUnit);
    if (exists) {
      exists.quantity += this.tempQuantity;
    } else {
      this.formIngredients.push({
        stockId: Number(this.tempStockId),
        quantity: this.tempQuantity,
        unit: this.tempUnit,
        unitWeight: this.tempUnitWeight !== null ? this.tempUnitWeight : undefined
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
    if (this.stockItems.length > 0) {
      this.tempStockId = this.stockItems[0].id;
      this.tempUnit = this.stockItems[0].unit || 'kg';
      this.tempUnitWeight = this.stockItems[0].unitWeight ?? null;
    }
    this.isModalOpen = true;
  }

  editRecipe(recipe: Recipe) {
    this.formId = recipe.id;
    this.formName = recipe.name;
    this.formInstructions = recipe.instructions;
    this.formMarginPercent = recipe.marginPercent;
    this.formImage = recipe.image || '';
    // deep clone and initialize unit if missing
    this.formIngredients = recipe.ingredients.map(i => ({ 
      ...i, 
      unit: i.unit || this.getIngredientUnit(i.stockId) || 'kg'
    }));
    this.tempQuantity = 0;
    if (this.stockItems.length > 0) {
      this.tempStockId = this.stockItems[0].id;
      this.tempUnit = this.stockItems[0].unit || 'kg';
      this.tempUnitWeight = this.stockItems[0].unitWeight ?? null;
    }
    this.isModalOpen = true;
  }

  onFileChange(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        const base64 = e.target.result;
        compressImage(base64).then((compressed: string) => {
          this.formImage = compressed;
        });
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
