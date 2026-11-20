const test=require('node:test');const assert=require('node:assert/strict');const {items}=require('./server');
test('inventory contains synthetic SKU data',()=>assert.ok(items.every(x=>x.sku.startsWith('SYN-'))));
test('quantities are numeric',()=>assert.ok(items.every(x=>typeof x.quantity==='number')));
