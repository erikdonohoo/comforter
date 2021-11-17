import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ProjectRoutingModule } from './project-routing.module';
import { ProjectComponent } from './project.component';
import { AppSharedModule } from '../app-shared.module';
import { CommitComponent } from './commit/commit.component';


@NgModule({
  declarations: [
    ProjectComponent,
    CommitComponent
  ],
  imports: [
    AppSharedModule,
    CommonModule,
    ProjectRoutingModule
  ]
})
export class ProjectModule { }
