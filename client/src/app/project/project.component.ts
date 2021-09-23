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
    this.app.commits = this.app.commits?.map((commit) => {
      // TODO: Currently make latest commit the "base", but Erik will make this be the actual base
      return {...commit, base_commit: this.app!.latest_commit};
    });
  }
}
