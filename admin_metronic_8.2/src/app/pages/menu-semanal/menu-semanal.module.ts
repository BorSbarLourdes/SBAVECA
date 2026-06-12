import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MenuSemanalComponent } from './menu-semanal.component';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { SharedModule } from 'src/app/_metronic/shared/shared.module';

@NgModule({
  declarations: [MenuSemanalComponent],
  imports: [
    CommonModule,
    FormsModule,
    NgbTooltipModule,
    SharedModule,
    RouterModule.forChild([
      {
        path: '',
        component: MenuSemanalComponent
      }
    ])
  ]
})
export class MenuSemanalModule { }
