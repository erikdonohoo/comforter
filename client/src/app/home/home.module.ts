import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { HomeRoutingModule } from './home-routing.module';
import { HomeComponent } from './home.component';
import { ProjectCardComponent } from './project-card/project-card.component';
import { AppMaterialModule } from '../app-material.module';
import { AppSharedModule } from '../app-shared.module';


@NgModule({
  declarations: [
    HomeComponent,
    ProjectCardComponent
  ],
  imports: [
    CommonModule,
    AppSharedModule,
    HomeRoutingModule
  ]
})
export class HomeModule { }
