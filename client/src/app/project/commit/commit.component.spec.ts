import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ComforterCommit } from '../../interfaces/commit';

import { CommitComponent } from './commit.component';

describe('CommitComponent', () => {
  let component: CommitComponent;
  let fixture: ComponentFixture<CommitComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CommitComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(CommitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should calculate a negative coverage diff on init', () => {
    component.commit = { coverage: '95.5', base_commit: {coverage: '96.5'}} as ComforterCommit;
    component.ngOnInit();
    expect(component.coverageDiff).toEqual(-1);
  });

  it('should calculate a positive coverage diff on init', () => {
    component.commit = { coverage: '95.5', base_commit: {coverage: '94.5'}} as ComforterCommit;
    component.ngOnInit();
    expect(component.coverageDiff).toEqual(1);
  });

  it('should calculate 0 coverage diff on init', () => {
    component.commit = { coverage: '95.5', base_commit: {coverage: '95.5'}} as ComforterCommit;
    component.ngOnInit();
    expect(component.coverageDiff).toEqual(0);
  });
});
