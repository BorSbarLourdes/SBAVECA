import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { RecetasComponent } from './recetas.component';
import { SharedModule } from 'src/app/_metronic/shared/shared.module';

@NgModule({
  declarations: [RecetasComponent],
  imports: [
    CommonModule,
    FormsModule,
    SharedModule,
    RouterModule.forChild([
      {
        path: '',
        component: RecetasComponent,
      },
    ]),
  ],
})
export class RecetasModule {}
