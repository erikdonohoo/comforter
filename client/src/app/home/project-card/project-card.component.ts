import { Component, Input, OnInit } from '@angular/core';
import { ComforterApp } from '../../interfaces/app';

@Component({
  selector: 'app-project-card',
  templateUrl: './project-card.component.html',
  styleUrls: ['./project-card.component.scss']
})
export class ProjectCardComponent implements OnInit {
  @Input() app: ComforterApp | null = null;

  constructor() {}

  ngOnInit(): void {}
}
