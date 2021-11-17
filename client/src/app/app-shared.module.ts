import { HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { AppMaterialModule } from './app-material.module';
import { GitlabIconComponent } from './gitlab-icon/gitlab-icon.component';
import { ShaPipe } from './common/pipes/sha/sha.pipe';

@NgModule({
  imports: [
    HttpClientModule,
    AppMaterialModule
  ],
  declarations: [
    GitlabIconComponent,
    ShaPipe
  ],
  exports: [
    GitlabIconComponent,
    HttpClientModule,
    AppMaterialModule,
    ShaPipe
  ]
})
export class AppSharedModule { }
