import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ComforterService } from './comforter.service';

describe('ComforterService', () => {
  let http: HttpTestingController;
  let service: ComforterService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule]
    });
    http = TestBed.inject(HttpTestingController);
    service = TestBed.inject(ComforterService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('getApps - should call the correct endpoint', () => {
    service.getApps().then((apps) => {
      expect(apps.length).toBe(1);
    });
    const req = http.expectOne('/api/apps');
    expect(req.request.method).toBe('GET');
    req.flush([{ id: 1 }]);
    http.verify();
  });

  it('getApp - should call the correct endpoint', () => {
    service.getApp(1).then((app) => {
      expect(app.id).toBe(1);
    });
    const req = http.expectOne('/api/apps/1');
    expect(req.request.method).toBe('GET');
    req.flush({ id: 1 });
    http.verify();
  });
});
