import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NotFoundIconComponent } from './not-found-icon.component';

describe('NotFoundIconComponent', () => {
  let component: NotFoundIconComponent;
  let fixture: ComponentFixture<NotFoundIconComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NotFoundIconComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(NotFoundIconComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
