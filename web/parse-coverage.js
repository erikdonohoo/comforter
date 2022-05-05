const fs = require('fs');

const statements = /<directory name="\/">\W+<totals>\W+<lines .*executable="([0-9]+)/g;
const coveredstatements = /<directory name="\/">\W+<totals>\W+<lines .*executed="([0-9]+)/g;

const fileStr = fs.readFileSync('tests/output/xml/index.xml');

let regex = null;

if (process.argv.includes('--lines')) {
  regex = statements;
} else if (process.argv.includes('--covered')) {
  regex = coveredstatements;
}

let result = regex.exec(fileStr);
console.log(result[1]);
