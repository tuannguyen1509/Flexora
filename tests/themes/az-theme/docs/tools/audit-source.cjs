/* Read-only theme scanner; writes generated documentation only. */
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const root = path.resolve(__dirname, '../..');
const out = path.join(root, 'docs/audit');
function walk(dir) {
  return fs.readdirSync(dir, { withFileTypes: true }).flatMap(entry => {
    if (['docs', '.git', 'node_modules'].includes(entry.name)) return [];
    const file = path.join(dir, entry.name);
    return entry.isDirectory() ? walk(file) : [file];
  }).sort();
}
const files = walk(root).map(file => {
  const data = fs.readFileSync(file);
  return { path: path.relative(root, file).replaceAll('\\', '/'), bytes: data.length,
    sha256: crypto.createHash('sha256').update(data).digest('hex'),
    ext: path.extname(file), text: /\.(php|css|js|json|po)$/.test(file) ? data.toString('utf8') : null };
});
const apis = ['get_option', 'update_option', 'add_option', 'delete_option', 'get_theme_mod', 'set_theme_mod', 'remove_theme_mod', 'register_setting', 'add_action', 'add_filter', 'remove_action', 'remove_filter', 'do_action', 'apply_filters', 'register_rest_route', 'register_post_type', 'register_taxonomy', 'add_shortcode', 'do_shortcode', 'wp_schedule_event', 'wp_schedule_single_event', 'wp_next_scheduled', 'wp_clear_scheduled_hook', 'add_menu_page', 'add_submenu_page', 'add_meta_box', 'get_post_meta', 'update_post_meta', 'get_term_meta', 'update_term_meta', 'set_transient', 'get_transient', 'delete_transient', 'wp_enqueue_style', 'wp_enqueue_script', 'wp_add_inline_style', 'wp_add_inline_script', 'get_template_part'];
// Balanced argument extraction, not a PHP AST. Dynamic expressions stay verbatim.
function argumentsAt(text, start) {
  let depth = 1, quote = '', begin = start, args = [];
  for (let i = start; i < text.length; i++) {
    const c = text[i];
    if (quote) { if (c === '\\') i++; else if (c === quote) quote = ''; continue; }
    if (c === '"' || c === "'") { quote = c; continue; }
    if ('([{'.includes(c)) depth++;
    if (')]}'.includes(c)) depth--;
    if (!depth) { args.push(text.slice(begin, i).trim()); return args; }
    if (c === ',' && depth === 1) { args.push(text.slice(begin, i).trim()); begin = i + 1; }
  }
  return [text.slice(start).split('\n')[0].trim()];
}
const literal = value => {
  const match = /^(?:'([^'\\]*)'|"([^"\\$]*)")$/.exec(value || '');
  return match ? match[1] ?? match[2] : null;
};
apis.push('az_theme_mod', 'add_setting');
const calls = [], includes = [], declarations = [], prefixes = {}, schema = [];
for (const file of files.filter(f => f.text !== null)) {
  const text = file.text;
  const lineAt = pos => text.slice(0, pos).split('\n').length;
  if (file.ext === '.php') {
    const re = new RegExp('\\b(' + apis.join('|') + ')\\s*\\(', 'g');
    for (const m of text.matchAll(re)) calls.push({ api: m[1], file: file.path, line: lineAt(m.index), args: argumentsAt(text, m.index + m[0].length) });
    for (const m of text.matchAll(/\b(require_once|require|include_once|include)\b\s*([^;\r\n]+)[;]?/g)) includes.push({ file: file.path, line: lineAt(m.index), kind: m[1], expression: m[2].trim() });
    for (const m of text.matchAll(/\b(function|class)\s+([A-Za-z_][\w]*)\s*(?:\(|\{|extends)/g)) declarations.push({ kind: m[1], name: m[2], file: file.path, line: lineAt(m.index) });
    for (const m of text.matchAll(/['"]((?:az_|az_)[\w]+)['"]\s*=>\s*array\(\s*['"]([^'"\r\n]+)['"]\s*,\s*['"](text|textarea|tel|email|url|map|brand|image)['"]/g)) schema.push({ key: m[1], title: m[2], type: m[3], file: file.path, line: lineAt(m.index) });
  }
  for (const m of text.matchAll(/\b([A-Za-z][A-Za-z0-9]*)_[A-Za-z0-9_]+\b/g)) prefixes[m[1] + '_'] = (prefixes[m[1] + '_'] || 0) + 1;
}
const counts = Object.fromEntries(['.php', '.css', '.js'].map(ext => [ext, files.filter(f => f.ext === ext).length]));
const duplicateDeclarations = declarations.filter((d, i) => declarations.findIndex(x => x.kind === d.kind && x.name === d.name) !== i);
const report = { scope: 'Theme source only, docs excluded. Static candidates, not runtime execution or PHP lint. Comments/embedded JS may appear; review context at cited lines.', files: files.map(({text, ...f}) => f), counts, calls, includes, declarations, duplicateDeclarations, prefixes, schema };
fs.mkdirSync(out, { recursive: true });
fs.writeFileSync(path.join(out, 'source-inventory.json'), JSON.stringify(report, null, 2) + '\n');
const esc = value => String(value).replaceAll('|', '\\|').replaceAll('\n', ' ').replaceAll('\r', '').replaceAll('`', "'");
const ref = item => `[${item.file}:${item.line}](../../${item.file}#L${item.line})`;
let inventory = '# Source inventory (generated)\n\nRun `node docs/tools/audit-source.cjs` from the theme. Static source evidence, not a list of enabled runtime features. No database reads.\n\n';
inventory += `PHP: ${counts['.php']}; CSS: ${counts['.css']}; JS: ${counts['.js']}; all files: ${files.length}.\n\n## Files\n\n| File | Bytes | Role |\n| --- | ---: | --- |\n`;
for (const f of files) {
  let role = f.path.startsWith('woocommerce/') ? 'WooCommerce override' : f.path.startsWith('template-parts/') ? 'Template part' : f.ext === '.php' && !f.path.includes('/') && f.path !== 'functions.php' ? 'Template / template helper' : f.ext.slice(1);
  inventory += `| [${f.path}](../../${f.path}) | ${f.bytes} | ${role} |\n`;
}
inventory += '\n## Includes / requires (source candidates)\n\n| Location | Kind | Expression |\n| --- | --- | --- |\n';
for (const i of includes) inventory += `| ${ref(i)} | ${i.kind} | ${esc(i.expression)} |\n`;
inventory += '\n## PHP declarations (includes embedded-language candidates)\n\n| Name | Kind | Location |\n| --- | --- | --- |\n';
for (const d of declarations) inventory += `| ${d.name} | ${d.kind} | ${ref(d)} |\n`;
inventory += '\n## Identifier prefixes (all textual occurrences)\n\nIncludes WordPress, WooCommerce, constants, strings and comments; not all are theme-owned. CSS classes/variables are listed separately below.\n\n';
inventory += Object.entries(prefixes).sort((a,b) => a[0].localeCompare(b[0])).map(([p,c]) => `- ${p}: ${c}`).join('\n');
const cssNames = new Set(), vars = new Set();
for (const f of files.filter(f => f.text)) {
  for (const m of f.text.matchAll(/--[A-Za-z][\w-]+/g)) vars.add(m[0]);
  for (const m of f.text.matchAll(/\.([A-Za-z][\w]*-[\w-]+)/g)) cssNames.add(m[1]);
}
inventory += '\n\n## CSS class / selector candidates\n\n' + [...cssNames].sort().map(n => '`' + n + '`').join(', ') + '\n\n## CSS variable candidates\n\n' + [...vars].sort().map(n => '`' + n + '`').join(', ') + '\n';
fs.writeFileSync(path.join(out, 'FILES-AND-PREFIXES.md'), inventory);
let hookDoc = '# API callsites (generated)\n\nEvery matched source call is listed, including dynamic arguments. Counts are static candidates; comments and nested embedded code require context review. Full arguments are in source-inventory.json.\n';
for (const api of apis) {
  const entries = calls.filter(c => c.api === api);
  hookDoc += `\n## ${api} (${entries.length})\n\n`;
  if (!entries.length) { hookDoc += 'No source call found.\n'; continue; }
  hookDoc += '| Location | First argument | Second argument |\n| --- | --- | --- |\n';
  for (const c of entries) hookDoc += `| ${ref(c)} | ${esc(c.args[0] || '')} | ${esc((c.args[1] || '').slice(0,250))} |\n`;
}
fs.writeFileSync(path.join(out, 'CALLSITES.md'), hookDoc);
const optionApis = ['get_option', 'update_option', 'add_option', 'delete_option', 'register_setting'];
const modApis = ['get_theme_mod', 'set_theme_mod', 'remove_theme_mod', 'az_theme_mod', 'add_setting'];
const keys = new Map(), dynamic = [];
for (const c of calls.filter(c => [...optionApis, ...modApis].includes(c.api))) {
  const value = c.args[c.api === 'register_setting' ? 1 : 0];
  const key = literal(value);
  if (key === null) { dynamic.push(c); continue; }
  const store = modApis.includes(c.api) ? 'theme_mod' : 'option';
  const id = store + ':' + key;
  if (!keys.has(id)) keys.set(id, { key, store, refs: [] });
  keys.get(id).refs.push(c);
}
for (const f of schema) {
  const id = 'option:' + f.key;
  if (!keys.has(id)) keys.set(id, { key: f.key, store: 'option', refs: [] });
  keys.get(id).refs.push({...f, api: 'contact field schema (dynamic save)'});
}
// Four advisor records are generated by the contact schema and registration loops.
for (let i = 1; i <= 4; i++) for (const suffix of ['name','role','phone','zalo','image']) {
  const key = `az_advisor_${i}_${suffix}`;
  if (!keys.has('option:' + key)) keys.set('option:' + key, { key, store: 'option', refs: [{file:'inc/functions/contact-settings.php', line:1, api:'expanded advisor loop'}] });
}
let options = '# Options schema: current source baseline\n\nSTEP 00; no values read from DB and no writes/migration. `az_theme_options` and dot-path helpers are planned, not implemented.\n\n## Status\n\n- **Active**: current source references or defines the key; this does not prove a stored value exists.\n- **Legacy**: current separate option or theme_mod contract retained for compatibility (including az_ and az_).\n- **Planned migration**: future mapping to az_theme_options; no automatic copying/deletion in STEP 00.\n- **External**: WordPress/plugin-owned key; preserve owner contract.\n\nThe contact screen reads option -> theme_mod -> default and mirrors submitted values to theme_mod. Home settings UI was removed earlier but frontend reads remain. Missing UI does not mean a key is unused.\n\n## Literal keys and schema-defined keys\n\n| Store | Key | Status | Source evidence |\n| --- | --- | --- | --- |\n';
for (const row of [...keys.values()].sort((a,b) => (a.store+a.key).localeCompare(b.store+b.key))) {
  const owned = /^(az_|az_)/.test(row.key);
  options += `| ${row.store} | ${esc(row.key)} | ${owned ? 'Active / Legacy / Planned migration' : 'Active / External-or-legacy; confirm owner'} | ${row.refs.map(r => `${r.api}: ${ref(r)}`).join('; ')} |\n`;
}
options += '\n## Dynamic keys: do not treat as missing\n\n| API | Expression | Location |\n| --- | --- | --- |\n';
for (const c of dynamic) options += `| ${c.api} | ${esc(c.args[c.api === 'register_setting' ? 1 : 0])} | ${ref(c)} |\n`;
options += '\nDynamic families include advisor 1..4, homepage sections/banner/brands, bilingual _en variants and contact schema writes. Use full expressions/callsites before designing migration. Default values and register_setting sanitizer arguments remain verbatim in audit/source-inventory.json; heterogeneous legacy keys must not receive invented uniform defaults.\n\n## Planned nested storage\n\n`az_theme_options`: general, branding, colors, typography, layout, header, footer, blog, homepage, woocommerce, modules, performance, custom_code. State: Planned migration only. Future reads via az_get_option(path, default); distinguish absent keys from explicit false/0/empty values. Do not copy or delete legacy data without approved mapping and rollback.\n';
fs.writeFileSync(path.join(root, 'docs/OPTIONS-SCHEMA.md'), options.replaceAll('](../../', '](../'));
console.log(JSON.stringify({counts, files: files.length, callsites: calls.length, includes: includes.length, keys: keys.size, dynamic: dynamic.length, duplicateDeclarations}, null, 2));
