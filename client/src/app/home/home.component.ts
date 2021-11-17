import { Component, OnInit } from '@angular/core';
import { ComforterService } from '../common/services/comforter.service';
import { ComforterApp } from '../interfaces/app';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {
  apps: ComforterApp[] = [];

  constructor(private comforter: ComforterService) { }

  ngOnInit(): void {
    this.getApps();
  }

  async getApps() {
    this.apps = await this.comforter.getApps();
  }
}
