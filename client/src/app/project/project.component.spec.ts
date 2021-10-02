import { HttpClientTestingModule } from '@angular/common/http/testing';
import { ComponentFixture, fakeAsync, TestBed, tick } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap, ParamMap } from '@angular/router';
import { Subject } from 'rxjs';
import { ComforterService } from '../common/services/comforter.service';
import { ComforterApp } from '../interfaces/app';

import { ProjectComponent } from './project.component';

class MockActivatedRoute {
	paramMap = new Subject<ParamMap>();
}

describe('ProjectComponent', () => {
  let component: ProjectComponent;
  let fixture: ComponentFixture<ProjectComponent>;
  let route: MockActivatedRoute;
  let comforter: ComforterService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [
        { provide: ActivatedRoute, useClass: MockActivatedRoute }
      ],
      declarations: [ ProjectComponent ]
    })
    .compileComponents();

    route = (TestBed.inject(ActivatedRoute) as unknown) as MockActivatedRoute;
    comforter = TestBed.inject(ComforterService);
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ProjectComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should get the project when the project id updates', fakeAsync(() => {
    spyOn(comforter, 'getApp').and.returnValue(Promise.resolve({id: 1} as ComforterApp));
    route.paramMap.next(convertToParamMap({projectId: '1'}));
    tick();
    expect(comforter.getApp).toHaveBeenCalledWith(1);
    expect(component.app).toEqual({id: 1} as ComforterApp);
  }));

  it('should not attempt to get a project if there is no project id', fakeAsync(() => {
    spyOn(comforter, 'getApp');
    route.paramMap.next(convertToParamMap({}));
    tick();
    expect(comforter.getApp).not.toHaveBeenCalled();
    expect(component.app).toBeNull();
  }));
});
