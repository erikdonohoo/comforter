import { TestBed } from '@angular/core/testing';

import { ComforterService } from './comforter.service';

describe('ComforterService', () => {
  let service: ComforterService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ComforterService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
