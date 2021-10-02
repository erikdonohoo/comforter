import { ComponentFixture, fakeAsync, TestBed, tick } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';

import { HomeComponent } from './home.component';
import { ComforterService } from '../common/services/comforter.service';
import { ComforterApp } from '../interfaces/app';

const DUMMY_APPS = [
  {id: 1},
  {id: 2}
];

describe('HomeComponent', () => {
  let component: HomeComponent;
  let fixture: ComponentFixture<HomeComponent>;
  let comforter: ComforterService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      declarations: [ HomeComponent ]
    })
    .compileComponents();

    comforter = TestBed.inject(ComforterService);
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(HomeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should get all the apps on initialization', fakeAsync(() => {
    spyOn(comforter, 'getApps').and.returnValue(Promise.resolve(DUMMY_APPS as ComforterApp[]));
    component.ngOnInit();
    tick();
    expect(component.apps).toBe(DUMMY_APPS as ComforterApp[]);
  }));
});
