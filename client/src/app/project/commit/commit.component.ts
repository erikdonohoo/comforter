import { Component, Input, OnInit } from '@angular/core';
import { ComforterCommit } from '../../interfaces/commit';

@Component({
  selector: 'app-commit',
  templateUrl: './commit.component.html',
  styleUrls: ['./commit.component.scss']
})
export class CommitComponent implements OnInit {
  @Input() commit: ComforterCommit | null = null;
  coverageDiff: number | undefined;

  constructor() {}

  ngOnInit(): void {
    if (this.commit) {
      const commitCoverage = parseFloat(this.commit.coverage);
      const baseCoverage = parseFloat(this.commit.base_commit!.coverage);
      this.coverageDiff = commitCoverage - baseCoverage;
    }
  }
}
