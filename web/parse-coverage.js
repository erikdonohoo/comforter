const fs = require('fs');

const statements = /<metrics files="[0-9]+" [^/]+ statements="([0-9]+)/g;
const coveredstatements = /<metrics files="[0-9]+" [^/]+ coveredstatements="([0-9]+)/g;

const fileStr = fs.readFileSync('tests/_output/coverage.xml');

let regex = null;

if (process.argv.includes('--lines')) {
  regex = statements;
} else if (process.argv.includes('--covered')) {
  regex = coveredstatements;
}

let result = regex.exec(fileStr);
console.log(result[1]);
