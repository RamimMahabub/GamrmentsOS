import test from 'node:test';
import assert from 'node:assert/strict';
import { registerForms } from '../../resources/js/forms.js';
import { sum } from '../../resources/js/math.js';
const factories={};
registerForms({data(name,factory){ factories[name]=factory; }});
function form(name,initial) {
    const data=factories[name](initial);
    data.$watch=()=>{};
    return data;
}
test('knitting bag entry, waste picks and repeated unit switches preserve canonical kg',()=>{
    const data=form('knittingForm',{yarns:[{id:'1',name:'Sudan',type:'34 Single',stock:'1000.000',bagKg:'45.35923700'}],selected:['1'],rows:[],presets:[32],unit:'lb',date:'2026-05-10',lotNumber:'SU-100526'});
    data.init(); const row=data.rows[0],yarn=data.yarns[0];
    data.setMode(row,yarn,'bags'); row.displays['1']='5'; data.setQuantity(row,yarn);
    assert.equal(row.quantities['1'],'226.796'); assert.equal(data.rowOutput(row),'226.796');
    row.waste_percent='1'; assert.equal(data.rowOutput(row),'224.528');
    data.setMode(row,yarn,'weight');
    for(let i=0;i<20;i++){ data.unit=i%2 ? 'lb':'kg'; data.refreshDisplays(); }
    assert.equal(row.quantities['1'],'226.796');
    assert.equal(row.displays['1'],'500.000');
});
test('price and bag unit switches change display without rewriting stored values',()=>{
    const data=form('yarnForm',{priceKg:'4.409245',unit:'lb',bagKg:'45.35923700',bagUnit:'lb',kg:'0',weightUnit:'lb'});
    data.init(); assert.equal(data.price,'2.00');
    data.unit='kg'; data.refreshPrice(); assert.equal(data.price,'4.41'); assert.equal(data.priceKg,'4.409245');
    data.bagUnit='kg'; data.refreshBag(); data.bagUnit='lb'; data.refreshBag();
    assert.equal(data.bag,'100.000'); assert.equal(data.bagKg,'45.35923700');
    data.quantityMode='bags'; data.quantityText='5'; data.quantityChanged();
    assert.equal(data.kg,'226.796'); assert.equal(data.quantityBags(),'5');
});
test('changing color count preserves touched allocations and balances untouched rows',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[],jobId:null,colors:[]});
    data.init(); data.summary={rows:[{dia:32,available_kg:'226.796',output_kg:'226.796'}]}; data.resize();
    assert.equal(data.blocks[0].rows[0].given_fabric_kg,'37.799');
    assert.equal(data.blocks[5].rows[0].given_fabric_kg,'37.801');
    data.blocks[0].rows[0].given_fabric_kg='30.000'; data.blocks[0].rows[0].touched=true;
    data.count=3; data.resize();
    assert.equal(data.blocks[0].rows[0].given_fabric_kg,'30.000');
    assert.equal(data.blocks[1].rows[0].given_fabric_kg,'98.398');
    assert.equal(sum(data.allRows().map(r=>r.given_fabric_kg)),'226.796');
});
test('pending returns remain pending in totals and added dye mass raises the confirmation',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[],jobId:null,colors:[]});
    data.init(); data.summary={rows:[{dia:32,available_kg:'200.000'}]}; data.count=2; data.resize();
    const first=data.blocks[0].rows[0]; first.returned_fabric_kg='110.000';
    assert.equal(data.excessReturn(),true);
    assert.equal(data.rowWaste(first),'-10.00');
    assert.equal(data.totals(data.allRows()).waste,null);
    data.blocks[1].rows[0].returned_fabric_kg='90.000';
    assert.equal(data.totals(data.allRows()).waste,'0.00');
});
test('negative or malformed weight input never becomes a valid zero allocation',()=>{
    const data=form('weightInput',{kg:'0',unit:'lb'});
    data.init(); data.text='-5'; data.change();
    assert.match(data.kg,/^invalid:/);
    data.text='2,500'; data.change(); assert.equal(data.kg,'1133.981');
});

test('returned rolls total mixed units and update after removal',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[],jobId:null,colors:[]});
    const row={rolls:[{number:'A',weight:'10',unit:'kg'},{number:'B',weight:'20',unit:'lb'}]};
    data.updateRollTotal(row);
    assert.equal(row.returned_fabric_kg,'19.072');
    data.removeRoll(row,0);
    assert.equal(row.returned_fabric_kg,'9.072');
    row.rolls[0].weight='invalid'; data.updateRollTotal(row);
    assert.equal(row.returned_fabric_kg,'');
    data.removeRoll(row,0);
    assert.equal(row.returned_fabric_kg,'');
});

test('selecting a roll color moves its weight without changing the job total',()=>{
    const source={dia:32,rolls:[{number:'A',weight:'20',unit:'lb'}]};
    const target={dia:32,rolls:[{number:'B',weight:'10',unit:'kg'}]};
    const data=form('dyeingForm',{lotId:'',blocks:[{color:'Navy',rows:[source]},{color:'Black',rows:[target]}],jobId:null,colors:[]});
    data.updateRollTotal(source); data.updateRollTotal(target);
    const before=sum(data.allRows().map(row=>row.returned_fabric_kg));
    data.moveRoll(source,0,1);
    assert.equal(source.returned_fabric_kg,'0.000');
    assert.equal(target.returned_fabric_kg,'19.072');
    assert.equal(target.rolls[1].number,'A');
    assert.equal(sum(data.allRows().map(row=>row.returned_fabric_kg)),before);
});
