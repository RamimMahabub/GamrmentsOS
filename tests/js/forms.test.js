import test from 'node:test';
import assert from 'node:assert/strict';
import { registerForms } from '../../resources/js/forms.js';
import { sum } from '../../resources/js/math.js';
const factories={};
registerForms({data(name,factory){ factories[name]=factory; }});
globalThis.confirm = () => true;
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
    data.init(); data.summary={rows:[{dia:32,available_kg:'226.796',output_kg:'226.796'}]}; data.count=6; data.resize();
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
    data.allRows().forEach(row => { row.rolls=[]; row.returned_fabric_kg=''; });
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

test('new returned rolls receive a DIA, color, and sequence number',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[{color:'Black',rows:[{dia:32,rolls:[]}] }],jobId:null,colors:[]});
    const row=data.blocks[0].rows[0];
    row.returned_fabric_kg='37.421';
    data.addRoll(row,data.blocks[0],0);
    data.addRoll(row,data.blocks[0],0);
    assert.equal(row.rolls[0].number,'32BLACK01');
    assert.equal(row.rolls[1].number,'32BLACK02');
    assert.equal(row.rolls[0].colorIndex,0);
    assert.equal(row.rolls[1].colorIndex,0);
    assert.equal(row.rolls[0].weight,'18.71');
    assert.equal(row.rolls[1].weight,'18.711');
    assert.equal(row.returned_fabric_kg,'37.421');
});

test('new color and DIA rows receive one default roll with allocated weight',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[],jobId:null,colors:[]});
    data.init();
    data.summary={rows:[{dia:32,available_kg:'100.000'}]};
    data.count=6;
    data.resize();
    data.blocks[0].color='Black';
    data.ensureDefaultRolls(data.blocks[0]);
    const row=data.blocks[0].rows[0];
    assert.equal(row.rolls.length,1);
    assert.equal(row.rolls[0].number,'32BLACK01');
    assert.equal(row.rolls[0].weight,'16.666');
    assert.equal(row.returned_fabric_kg,'16.666');
});

test('default roll does not require a color and follows allocated input until edited',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[],jobId:null,colors:[]});
    data.init();
    data.summary={rows:[{dia:32,available_kg:'100.000'}]};
    data.count=6;
    data.resize();
    const row=data.blocks[0].rows[0];
    assert.equal(row.rolls.length,1);
    assert.equal(row.rolls[0].number,'32ROLL01');
    assert.equal(row.rolls[0].weight,'16.666');
    row.given_fabric_kg='20.000';
    data.ensureDefaultRolls(data.blocks[0]);
    assert.equal(row.rolls[0].weight,'20.000');
    row.rolls[0].weight='12';
    row.rolls[0].autoWeight=false;
    row.given_fabric_kg='30.000';
    data.ensureDefaultRolls(data.blocks[0]);
    assert.equal(row.rolls[0].weight,'12');
});

test('editing returned total updates the editable roll weight',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[{color:'White',rows:[{dia:32,given_fabric_kg:'20.000',returned_fabric_kg:'20.000',rolls:[{number:'32WHITE01',weight:'20.000',unit:'kg'}]}]}],jobId:null,colors:[]});
    const row=data.blocks[0].rows[0];
    row.returned_fabric_kg='18.500';
    data.syncRollsToReturned(row);
    assert.equal(row.rolls[0].weight,'18.500');
    assert.equal(row.returned_fabric_kg,'18.500');
});

test('existing returned weight becomes the first editable roll',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[{color:'Black',rows:[{dia:32,returned_fabric_kg:'37.421',rolls:[]}] }],jobId:null,colors:[]});
    data.init();
    const row=data.blocks[0].rows[0];
    assert.equal(row.rolls.length,1);
    assert.equal(row.rolls[0].number,'32BLACK01');
    assert.equal(row.rolls[0].weight,'37.421');
    assert.equal(row.returned_fabric_kg,'37.421');
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

test('roll color defaults to its containing color table',()=>{
    const data=form('dyeingForm',{lotId:'',blocks:[
        {color:'Black',rows:[{dia:32,rolls:[{number:'32BLACK01',weight:'10',unit:'kg'}]}]},
        {color:'White',rows:[{dia:32,rolls:[{number:'32WHITE01',weight:'10',unit:'kg'}]}]},
    ],jobId:null,colors:[]});
    data.init();
    assert.equal(data.blocks[0].rows[0].rolls[0].colorIndex,0);
    assert.equal(data.blocks[1].rows[0].rolls[0].colorIndex,1);
});
