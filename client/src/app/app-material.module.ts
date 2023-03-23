import { NgModule } from '@angular/core';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatIconModule } from '@angular/material/icon';
import { MatLegacyProgressBarModule as MatProgressBarModule } from '@angular/material/legacy-progress-bar';

@NgModule({
  imports: [
    MatToolbarModule,
    MatIconModule,
    MatProgressBarModule,
    MatIconModule
  ],
  exports: [
    MatToolbarModule,
    MatIconModule,
    MatProgressBarModule,
    MatIconModule
  ]
})
export class AppMaterialModule { }
