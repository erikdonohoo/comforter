import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ComforterApp } from '../../interfaces/app';

@Injectable({
  providedIn: 'root'
})
export class ComforterService {

  constructor(private http: HttpClient) {}

  async getApps(): Promise<ComforterApp[]> {
    return this.http.get<ComforterApp[]>('/api/apps').toPromise();
  }

  async getApp(appId: number): Promise<ComforterApp> {
    return this.http.get<ComforterApp>(`/api/apps/${appId}`).toPromise();
  }
}
