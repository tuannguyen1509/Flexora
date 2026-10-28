/* Real primary WordPress admin browser acceptance; credentials stay in ignored local files. */
const fs = require('fs');
const http = require('http');
const {chromium} = require('playwright');
const origin = 'http://localhost:8090';
const environment = Object.fromEntries(fs.readFileSync('/workspace/local/.env','utf8').split(/\r?\n/).filter(line=>line.includes('=')&&!line.startsWith('#')).map(line=>{const i=line.indexOf('=');return [line.slice(0,i),line.slice(i+1).replace(/^['"]|['"]$/g,'')];}));
const fixtures = JSON.parse(fs.readFileSync('/workspace/local/results/fields-fixtures.json','utf8').replace(/^\uFEFF/,''));
let count=0; const results=[];
function check(ok,label){if(!ok)throw new Error(label); count++;results.push(label);}
const proxy=http.createServer((request,response)=>{const upstream=http.request({hostname:'wordpress',port:80,path:request.url,method:request.method,headers:request.headers},result=>{response.writeHead(result.statusCode,result.headers);result.pipe(response);});upstream.on('error',()=>{response.writeHead(502);response.end();});request.pipe(upstream);});
let browser;
const base=origin+'/wp-admin/admin.php?page=flx-advanced';
async function login(page,username,password){await page.goto(origin+'/wp-login.php');await page.locator('#user_login').fill(username);await page.locator('#user_pass').fill(password);await Promise.all([page.waitForURL(/wp-admin/),page.locator('#wp-submit').click()]);}
async function go(page,section){await page.goto(base+'&section='+section,{waitUntil:'networkidle'});check(await page.locator('.flx-admin-content > h2').count()===1,'open section '+section);}
async function save(page){await Promise.all([page.waitForURL(/saved=1/),page.getByRole('button',{name:'Lưu thay đổi',exact:true}).click()]);check(await page.getByText('Đã lưu cấu hình.',{exact:true}).count()===1,'save notice');}
async function editorSet(page,selector,html){const id=await page.locator(selector).getAttribute('id');await page.waitForFunction(id=>window.tinymce&&tinymce.get(id)&&tinymce.get(id).initialized&&!tinymce.get(id).isHidden(),id);await page.evaluate(({id,html})=>{tinymce.get(id).setContent(html);tinymce.get(id).save();},{id,html});return id;}
async function choose(page,container,ids,multiple=false){await container.locator(multiple?'[data-flx-gallery-select]':'[data-flx-media-select]').click();const modal=page.locator('.media-modal:visible');await modal.waitFor();check(await modal.isVisible(),'real Media Library opens');for(const id of ids){const attachment=modal.locator('.attachments [data-id="'+id+'"]');await attachment.waitFor();if(await attachment.getAttribute('aria-checked')!=='true')await attachment.click({modifiers:multiple?['Control']:[]});}await modal.locator('.media-button-select').click();await modal.waitFor({state:'hidden'});}
(async()=>{
 await new Promise(resolve=>proxy.listen(8090,'127.0.0.1',resolve));browser=await chromium.launch({headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:1000}});const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await login(page,environment.FLX_ADMIN_USER,environment.FLX_ADMIN_PASSWORD);await go(page,'general');
 check(await page.getByRole('navigation',{name:'Settings sections'}).count()===1,'section navigation landmark');
 await page.locator('[name="flx_values[company_name]"]').fill('FLX Browser Test');await save(page);await page.reload();check(await page.locator('[name="flx_values[company_name]"]').inputValue()==='FLX Browser Test','text persists');
 await go(page,'design');await go(page,'playground');
 await page.locator('[name="flx_values[title]"]').fill('Field acceptance');await page.locator('[name="flx_values[plain]"]').fill('Plain\nMultiline');await page.locator('[name="flx_values[count]"]').fill('0');
 const richId=await editorSet(page,'[name="flx_values[rich]"]','<p><strong>Rich bold</strong> <a href="https://example.org">Link</a></p><ul><li>List item</li></ul>');
 check(await page.evaluate(id=>!!tinymce.get(id).getBody().querySelector('strong'),richId),'top editor rich HTML');
 await page.frameLocator('[id="'+richId+'_ifr"]').locator('body').click();await page.keyboard.press('Control+End');await page.keyboard.type(' Keyboard entry');check(await page.evaluate(id=>tinymce.get(id).getContent().includes('Keyboard entry'),richId),'top rich editor accepts keyboard input');
 await page.locator('[name="flx_values[layout]"]').selectOption('compact');await page.locator('input[name="flx_values[alignment]"][value="center"]').check();
 check(await page.locator('[data-flx-field="color"] .wp-picker-container').count()===1,'native WordPress color picker');await page.locator('[data-flx-field="color"] .wp-color-result').click();check(await page.locator('[data-flx-field="color"] .iris-picker').isVisible(),'color picker opens');await page.locator('[name="flx_values[color]"]').fill('#123456');await page.locator('[name="flx_values[color]"]').dispatchEvent('change');await page.locator('[data-flx-field="color"] .wp-color-result').click();
 const toggle=page.locator('input.flx-toggle').first();await toggle.focus();const before=await toggle.isChecked();await page.keyboard.press('Space');check(await toggle.isChecked()!==before,'toggle keyboard');
 const media=page.locator('[data-flx-field="image"] [data-flx-media]').first();await choose(page,media,[fixtures.images[0]]);check(await media.locator('input').inputValue()===String(fixtures.images[0]),'media ID selected');check(await media.locator('img').count()===1,'media preview');await choose(page,media,[fixtures.images[1]]);check(await media.locator('input').inputValue()===String(fixtures.images[1]),'replace image');await media.locator('[data-flx-media-remove]').click();check(await media.locator('input').inputValue()==='0','remove image');await choose(page,media,[fixtures.images[0]]);
 const gallery=page.locator('[data-flx-gallery]').first();while(await gallery.locator('.flx-gallery-item').count())await gallery.locator('[data-flx-gallery-remove]').first().click();await choose(page,gallery,fixtures.images,true);check(await gallery.locator('.flx-gallery-item').count()===2,'gallery multiple');await gallery.locator('[data-flx-gallery-down]').first().click();check(await gallery.locator('input[name$="[]"]').first().inputValue()===String(fixtures.images[1]),'gallery reorder');
 const repeater=page.locator('[data-flx-repeater]').first();const rows=repeater.locator('.flx-repeater-rows > [data-flx-row]');
 while(await rows.count())await rows.first().locator('[data-flx-row-remove]').click();
 await repeater.locator('[data-flx-row-add]').click();check(await rows.count()===1,'add row');
 await rows.first().locator('[data-flx-field="title"] input').fill('Service one');const id1=await rows.first().getAttribute('data-flx-row');
 const editor1=await editorSet(page,'[data-flx-row="'+id1+'"] textarea.flx-rich-editor','<p><strong>Service rich</strong></p><ul><li>Row list</li></ul>');check(!!editor1,'dynamic editor initialized');
 await choose(page,rows.first().locator('[data-flx-media]'),[fixtures.images[1]]);
 await rows.first().locator('[data-flx-row-duplicate]').click();check(await rows.count()===2,'duplicate row');const id2=await rows.nth(1).getAttribute('data-flx-row');check(id1!==id2,'duplicate gets new identity');
 const editor2=await rows.nth(1).locator('textarea.flx-rich-editor').getAttribute('id');check(editor1!==editor2,'duplicate editor ID unique');await editorSet(page,'[data-flx-row="'+id2+'"] textarea.flx-rich-editor','<p><em>Duplicate rich</em></p>');
 check(await page.locator('[id="'+editor2+'_ifr"]').isVisible(),'duplicate rich editor is visually usable');
 await rows.nth(1).locator('.switch-html').click();await rows.nth(1).locator('textarea.flx-rich-editor').fill('<p><em>Code mode survives move</em></p>');
 await rows.nth(1).locator('[data-flx-row-up]').click();check(await rows.first().getAttribute('data-flx-row')===id2,'keyboard move up keeps ID');check(await rows.first().locator('textarea.flx-rich-editor').isVisible(),'Code mode survives editor reinitialization');check((await rows.first().locator('textarea.flx-rich-editor').inputValue()).includes('Code mode survives move'),'Code mode text survives move');await rows.first().locator('.switch-tmce').click();await editorSet(page,'[data-flx-row="'+id2+'"] textarea.flx-rich-editor','<p><em>Moved editor usable</em></p>');
 await rows.first().locator('[data-flx-row-down]').click();check(await rows.first().getAttribute('data-flx-row')===id1,'move down keeps ID');
 await rows.first().locator('[data-flx-row-collapse]').click();check(await rows.first().locator('.flx-row-fields').isHidden(),'collapse row');await rows.first().locator('[data-flx-row-collapse]').click();
 // Real HTML5 drag event path using a browser DataTransfer object.
 await page.evaluate(()=>{const rows=document.querySelectorAll('.flx-repeater-rows > [data-flx-row]');const dt=new DataTransfer();rows[1].querySelector('.flx-drag-handle').dispatchEvent(new DragEvent('dragstart',{bubbles:true,dataTransfer:dt}));rows[0].dispatchEvent(new DragEvent('drop',{bubbles:true,dataTransfer:dt}));});
 check(await rows.first().getAttribute('data-flx-row')===id2,'drag reorder keeps ID');await editorSet(page,'[data-flx-row="'+id1+'"] textarea.flx-rich-editor','<p><strong>Persisted row content</strong></p>');
 await page.locator('[data-flx-row="'+id1+'"] iframe').scrollIntoViewIfNeeded();const movedId=await rows.nth(1).locator('textarea').getAttribute('id');await page.frameLocator('[id="'+movedId+'_ifr"]').locator('body').click();await page.keyboard.press('Control+End');await page.keyboard.type(' Row keyboard');
 await page.screenshot({path:'/workspace/local/results/phase06-repeater-desktop.png',fullPage:true});
 await save(page);await page.reload();check(await rows.first().getAttribute('data-flx-row')===id2,'row order survives reload');check(await rows.nth(1).getAttribute('data-flx-row')===id1,'row identity survives reload');
 check((await rows.nth(1).locator('textarea.flx-rich-editor').inputValue()).includes('<strong>Persisted row content'),'repeater rich formatting persists');
 check((await page.locator('[name="flx_values[rich]"]').inputValue()).includes('<ul>'),'top editor list persists');check(await page.locator('[name="flx_values[count]"]').inputValue()==='0','number zero persists');check(await page.locator('input.flx-toggle').first().isChecked()!==before,'toggle persists');
 check((await rows.nth(1).locator('textarea.flx-rich-editor').inputValue()).includes('Row keyboard'),'moved editor keyboard content persists');check(await page.locator('[name="flx_values[layout]"]').inputValue()==='compact','select persists');check(await page.locator('[name="flx_values[alignment]"][value="center"]').isChecked(),'radio persists');check(await page.locator('[name="flx_values[color]"]').inputValue()==='#123456','color persists');
 check(await gallery.locator('input[name$="[]"]').first().inputValue()===String(fixtures.images[1]),'gallery order persists');
 const removedEditor=await rows.first().locator('textarea').getAttribute('id');await rows.first().locator('[data-flx-row-remove]').click();check(await page.evaluate(id=>!tinymce.get(id),removedEditor),'remove cleans TinyMCE instance');
 await gallery.locator('[data-flx-gallery-remove]').first().click();await save(page);check(await gallery.locator('.flx-gallery-item').count()===1,'gallery remove persists');
 await page.locator('[name="flx_values[link]"]').fill('not-a-url');await page.locator('[name="flx_values[email]"]').fill('bad-email');await page.locator('[name="flx_values[count]"]').fill('101');
 await Promise.all([page.waitForNavigation(),page.getByRole('button',{name:'Lưu thay đổi',exact:true}).click()]);check(await page.locator('.flx-field-error').count()===3,'structured validation errors');check(await page.locator('[name="flx_values[link]"]').inputValue()==='not-a-url','invalid scalar retained');check(await page.locator('[name="flx_values[link]"]').getAttribute('aria-invalid')==='true','error accessibility state');
 await page.reload(); // reload repeats invalid POST, navigate explicitly instead
 await go(page,'playground');check(await page.locator('[name="flx_values[count]"]').inputValue()==='0','invalid form atomic no writes');
 while(await rows.count())await rows.first().locator('[data-flx-row-remove]').click();while(await gallery.locator('.flx-gallery-item').count())await gallery.locator('[data-flx-gallery-remove]').first().click();await save(page);await page.reload();check(await rows.count()===0,'clear all repeater persists empty list');check(await gallery.locator('.flx-gallery-item').count()===0,'clear gallery persists empty list');
 await page.screenshot({path:'/workspace/local/results/phase06-desktop.png',fullPage:true});
 await page.setViewportSize({width:390,height:844});check(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),'mobile no horizontal overflow');check(await page.getByRole('navigation',{name:'Settings sections'}).isVisible(),'mobile section navigation');await page.screenshot({path:'/workspace/local/results/phase06-mobile.png',fullPage:true});
 await repeater.locator('[data-flx-row-add]').click();const mobileRow=await rows.first().getAttribute('data-flx-row');await editorSet(page,'[data-flx-row="'+mobileRow+'"] textarea.flx-rich-editor','<p>Mobile editor</p>');check(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),'mobile repeater editor no overflow');await page.screenshot({path:'/workspace/local/results/phase06-repeater-mobile.png',fullPage:true});await rows.first().locator('[data-flx-row-remove]').click();await save(page);
 // HTTP security tests use a real authenticated cookie jar and real WP save handlers.
 await go(page,'general');const nonce=await page.locator('[name="flx_nonce"]').inputValue();const revision=await page.locator('[name="flx_revision"]').inputValue();
 let response=await context.request.post(base+'&section=general',{form:{flx_nonce:'invalid',flx_revision:revision,flx_complete:'1'}});check(response.status()===403,'invalid nonce HTTP403');
 response=await context.request.post(base+'&section=general',{form:{flx_nonce:nonce,flx_revision:revision,flx_complete:'1','flx_values[intruder]':'x'}});check((await response.text()).includes('Trường không có trong schema'),'unexpected field rejected');
 response=await context.request.post(base+'&section=general',{form:{flx_nonce:nonce,flx_revision:revision}});check(response.status()===400,'truncated payload HTTP400');
 await go(page,'playground');const playgroundNonce=await page.locator('[name="flx_nonce"]').inputValue();
 for(const [field,value] of [['layout','invalid'],['image','999999999'],['services','malformed']]){
  response=await context.request.post(base+'&section=playground',{form:{flx_nonce:playgroundNonce,flx_revision:revision,flx_complete:'1',['flx_values['+field+']']:value}});check((await response.text()).includes('flx-field-error'),'server rejects '+field);
 }
 await go(page,'general');check(await page.locator('[name="flx_values[company_name]"]').inputValue()==='FLX Browser Test','other sections preserved');
 const other=await browser.newContext();const subscriber=await other.newPage();await login(subscriber,fixtures.subscriber.username,fixtures.subscriber.password);response=await other.request.get(base);check(response.status()===403,'subscriber page denied');response=await other.request.post(base+'&section=general',{form:{flx_nonce:nonce,flx_revision:revision,flx_complete:'1'}});check(response.status()===403,'subscriber save denied');await other.close();
 await page.goto(origin+'/');check(await page.locator('link[href*="assets/admin/"],script[src*="assets/admin/"]').count()===0,'no admin assets frontend');
 await page.goto(origin+'/wp-admin/index.php');check(await page.locator('link[href*="assets/admin/"],script[src*="assets/admin/"]').count()===0,'no Advanced assets dashboard');
 check(errors.length===0,'no uncaught JS errors: '+errors.join(';'));
 fs.writeFileSync('/workspace/local/results/phase06-browser.json',JSON.stringify({assertions:count,results,chromium:browser.version()},null,2));console.log('PASS Advanced real WordPress browser: '+count+' assertions; '+browser.version());
})().catch(error=>{console.error(error);process.exitCode=1;}).finally(async()=>{if(browser)await browser.close();proxy.close();});
