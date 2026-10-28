const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8').replace(/\r\n/g, '\n');
const moduleSource = read('inc/modules/homepage/module.php');
const definitions = moduleSource.slice(moduleSource.indexOf('return array('), moduleSource.indexOf('function az_homepage_options'));
const ids = [...definitions.matchAll(/'([a-z-]+)' => array\( 'label'/g)].map(m => m[1]);
assert.equal(ids.length, 9);
assert.equal(new Set(ids).size, 9);
for (const id of ids) assert(fs.existsSync(path.join(root, 'inc/modules/homepage/templates', id + '.php')), id);
const names = [];
for (const file of ['module.php', 'admin.php']) {
    const source = read('inc/modules/homepage/' + file);
    for (const m of source.matchAll(/function\s+(\w+)\s*\(/g)) {
        assert(m[1].startsWith('az_'));
        names.push(m[1]);
    }
}
assert.equal(names.length, new Set(names).size);
const css = read('inc/modules/homepage/homepage.css');
// A component class such as .az-news-body is not a body element selector.
assert(!/!important|(?:^|[^\w-])body\b/.test(css));
// Optional pre-STEP-16 git ref for mechanical extraction regression checks.
if (process.argv[2]) {
    const repoRoot = execFileSync('git', ['rev-parse', '--show-toplevel'], { cwd: root, encoding: 'utf8' }).trim();
    const relative = path.relative(repoRoot, path.join(root, 'front-page.php')).replaceAll('\\', '/');
    const old = execFileSync('git', ['show', process.argv[2] + ':' + relative], { cwd: root, encoding: 'utf8' }).replace(/\r\n/g, '\n');
    assert(old.includes('<script>'), 'Ref must be the pre-STEP-16 source');
    assert.equal(read('inc/modules/homepage/homepage.js').trim(), old.split('<script>')[1].split('</script>')[0].trim());
    const current = read('front-page.php');
    const start = "if ( ! function_exists( 'az_home_price_html' ) )";
    const end = '$az_categories = array();';
    assert.equal(current.slice(current.indexOf(start), current.indexOf(end)), old.slice(old.indexOf(start), old.indexOf(end)));
    console.log('PASS: legacy product helpers and interaction JS unchanged.');
}
console.log('PASS: 9 unique section templates, unique az-prefixed PHP declarations, CSS conventions. Static checks only.');
