import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GitlabIconComponent } from './gitlab-icon.component';

describe('GitlabIconComponent', () => {
  let component: GitlabIconComponent;
  let fixture: ComponentFixture<GitlabIconComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GitlabIconComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(GitlabIconComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
