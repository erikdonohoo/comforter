import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { ComforterService } from '../common/services/comforter.service';
import { ComforterApp } from '../interfaces/app';

@Component({
  selector: 'app-project',
  templateUrl: './project.component.html',
  styleUrls: ['./project.component.scss']
})
export class ProjectComponent implements OnInit {
  app: ComforterApp | null = null;

  constructor(private comforter: ComforterService, private route: ActivatedRoute) { }

  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      if(params.has('projectId')) {
        this.getApp(parseInt(params.get('projectId')!, 10));
      }
    });
  }

  async getApp(projectId: number) {
    this.app = await this.comforter.getApp(projectId);
  }
}
