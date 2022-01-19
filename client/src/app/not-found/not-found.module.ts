import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NotFoundRoutingModule } from './not-found-routing.module';
import { NotFoundComponent } from './not-found.component';
import { NotFoundIconComponent } from './not-found-icon/not-found-icon.component';


@NgModule({
  declarations: [
    NotFoundComponent,
    NotFoundIconComponent
  ],
  imports: [
    CommonModule,
    NotFoundRoutingModule
  ]
})
export class NotFoundModule { }
