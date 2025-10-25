// =============================
// HEALTH LOGGER DASHBOARD JS
// =============================

// Automatically detect API folder
const API = (p) => new URL('./api/' + p, window.location.href).toString();

// ---- Constants ----
const SYMPTOM_DURATIONS = ['Just Now', 'Less than a day', '1–7 Days', 'More than a week'];
const WATER_TARGET_OZ = 64;
const EXERCISE_TYPES = ['None', 'Cardio', 'Strength Training', 'Yoga/Stretching', 'Sport', 'Other'];
const COMMON_MEDICATIONS = [
  'Aspirin (325mg)','Ibuprofen (200mg)','Acetaminophen (500mg)',
  'Multivitamin','Vitamin D','Vitamin C','Magnesium','Melatonin','Antacid',
  'Loratadine (Allergy)','Fish Oil / Omega-3','Probiotic','Tylenol PM','Zinc Supplement',
  'Caffeine Pill','Blood Pressure Med','Thyroid Med','Inhaler (Asthma)',
  'Insulin','Omeprazole (PPI)'
];
const COMMON_FOODS = [
  'Oatmeal','Eggs','Yogurt','Chicken','Beef','Tofu','Pasta','Salad',
  'Soup','Sandwich','Coffee','Water','Soda','Alcohol','Pizza','Nuts',
  'Chocolate','Protein Shake','Avocado Toast','Smoothie'
];

// ---- State ----
let currentMood = 3, painIntensity = 0, symptomDuration = SYMPTOM_DURATIONS[0];
let symptomLocations = [], waterIntakeOz = 0;
let exerciseType = EXERCISE_TYPES[0], exerciseDuration = 0;
let selectedMedications = [], customMedicationNote = '';
let selectedMeals = [], customMealLog = '';
let bedIndex = 0, wakeIndex = 14;

// ---- Helpers ----
const $ = (id) => document.getElementById(id);
function showError(msg){ const e=$('errorMessage'); if(!e)return; e.textContent=msg; e.classList.remove('d-none'); setTimeout(()=>e.classList.add('d-none'),3000); }
function indexToTime(i){const m=(21*60+i*30)%1440;return `${String(Math.floor(m/60)).padStart(2,'0')}:${String(m%60).padStart(2,'0')}`;}
function calculateSleepDuration(b,w){const min=(w-b)*30;return {h:Math.floor(min/60),m:min%60};}
function getMoodDetails(m){return({5:{emoji:'😊',text:'Excellent'},4:{emoji:'🙂',text:'Good'},3:{emoji:'😐',text:'Neutral'},2:{emoji:'😟',text:'Low'},1:{emoji:'😞',text:'Poor'}})[m];}

// ---- Renderers ----
function renderMoodButtons(){
  const wrap=$('moodButtons'); wrap.innerHTML='';
  [5,4,3,2,1].forEach(m=>{
    const det=getMoodDetails(m);
    const b=document.createElement('button');
    b.className=`btn btn-light border flex-fill py-2 ${m===currentMood?'border-primary':''}`;
    b.innerHTML=`<span class="fs-3">${det.emoji}</span><br><small>${det.text}</small>`;
    b.onclick=()=>{currentMood=m;renderMoodButtons();};
    wrap.appendChild(b);
  });
}

function updateWater(){ $('waterIntakeValue').textContent=waterIntakeOz; $('waterGoal').textContent=`Goal: ${WATER_TARGET_OZ} oz (${Math.floor(waterIntakeOz/8)}/8 cups)`;}
function updateExercise(){ $('exerciseDurationValue').textContent=exerciseDuration; }
function updateSleepSummary(){
  const el=$('sleepSummary');
  if(bedIndex===0&&wakeIndex===14) el.textContent='Click to log sleep';
  else {const d=calculateSleepDuration(bedIndex,wakeIndex); el.textContent=`${indexToTime(bedIndex)} → ${indexToTime(wakeIndex)} (${d.h}h ${d.m}m)`;}
}
function updateBodyMapSummary(){
  $('bodyMapSummary').textContent=symptomLocations.length?`Selected: ${symptomLocations.join(', ')}`:'No areas selected';
}
function updateMedicationSummary(){
  $('medicationSummary').textContent=selectedMedications.length?`${selectedMedications.length} items${customMedicationNote?' + note':''}`:'No medication logged';
}
function updateMealSummary(){
  $('mealSummary').textContent=selectedMeals.length?`${selectedMeals.length} items${customMealLog?' + note':''}`:'No meals logged';
}

// ---- Modals ----
function showSleepModal(){
  Swal.fire({
    title:'Sleep Tracker',
    html:`<div class="text-start">
      <label>Bed Time</label>
      <input type="range" id="bed" min="0" max="30" value="${bedIndex}" class="form-range">
      <p><span id="bval">${indexToTime(bedIndex)}</span></p>
      <label>Wake Time</label>
      <input type="range" id="wake" min="0" max="30" value="${wakeIndex}" class="form-range">
      <p><span id="wval">${indexToTime(wakeIndex)}</span></p>
    </div>`,
    didOpen:()=>{
      const b=$('bed'),w=$('wake'),bv=$('bval'),wv=$('wval');
      const upd=()=>{
        let bed = +b.value;
        let wake = +w.value;
        // Constraint: ensure wake time is at least 1 hour (2 steps) after bed time
        if (wake < bed + 2) {
            wake = bed + 2;
            w.value = wake; // Update slider position
        }
        bedIndex = bed;
        wakeIndex = wake;
        bv.textContent = indexToTime(bedIndex);
        wv.textContent = indexToTime(wakeIndex);
      };
      b.oninput=w.oninput=upd;
    }
  }).then(r=>{if(r.isConfirmed)updateSleepSummary();});
}

function showBodyMapModal() {
  const FRONT = [
    {name:'Head', d:'M150,35 a35,35 0 1,0 0.01,0 z'},
    {name:'Eyes', d:'M135,50 h30 v10 h-30 z'},
    {name:'Nose', d:'M147,62 h6 v12 h-6 z'},
    {name:'Mouth/Jaw', d:'M135,78 h30 v12 h-30 z'},
    {name:'Neck', d:'M130,92 h40 v18 h-40 z'},
    {name:'Left Shoulder', d:'M90,110 a16,16 0 1,0 32,0 a16,16 0 1,0 -32,0 z'},
    {name:'Right Shoulder', d:'M178,110 a16,16 0 1,0 32,0 a16,16 0 1,0 -32,0 z'},
    {name:'Chest (Left)', d:'M100,120 h50 v70 h-50 z'},
    {name:'Chest (Right)', d:'M150,120 h50 v70 h-50 z'},
    {name:'Abdomen (Left)', d:'M100,190 h50 v60 h-50 z'},
    {name:'Abdomen (Right)', d:'M150,190 h50 v60 h-50 z'},
    {name:'Pelvis/Groin', d:'M120,250 h60 v40 h-60 z'},
    {name:'Left Upper Arm', d:'M60,126 h32 v56 h-32 z'},
    {name:'Left Elbow', d:'M78,182 a10,10 0 1,0 1,0 z'},
    {name:'Left Forearm', d:'M60,192 h32 v56 h-32 z'},
    {name:'Left Wrist', d:'M60,248 h32 v10 h-32 z'},
    {name:'Left Hand', d:'M60,258 h32 v30 h-32 z'},
    {name:'Right Upper Arm', d:'M208,126 h32 v56 h-32 z'},
    {name:'Right Elbow', d:'M220,182 a10,10 0 1,0 1,0 z'},
    {name:'Right Forearm', d:'M208,192 h32 v56 h-32 z'},
    {name:'Right Wrist', d:'M208,248 h32 v10 h-32 z'},
    {name:'Right Hand', d:'M208,258 h32 v30 h-32 z'},
    {name:'Left Thigh', d:'M115,290 h40 v70 h-40 z'},
    {name:'Right Thigh', d:'M145,290 h40 v70 h-40 z'},
    {name:'Left Knee', d:'M115,360 h40 v18 h-40 z'},
    {name:'Right Knee', d:'M145,360 h40 v18 h-40 z'},
    {name:'Left Shin', d:'M115,378 h40 v70 h-40 z'},
    {name:'Right Shin', d:'M145,378 h40 v70 h-40 z'},
    {name:'Left Ankle', d:'M115,448 h40 v12 h-40 z'},
    {name:'Right Ankle', d:'M145,448 h40 v12 h-40 z'},
    {name:'Left Foot', d:'M110,460 h50 v30 h-50 z'},
    {name:'Right Foot', d:'M140,460 h50 v30 h-50 z'},
    {name:'Toes (Left)', d:'M110,490 h50 v12 h-50 z'},
    {name:'Toes (Right)', d:'M140,490 h50 v12 h-50 z'}
  ];
  const BACK = [
    {name:'Back of Head', d:'M150,35 a35,35 0 1,0 0.01,0 z'},
    {name:'Neck (Back)', d:'M130,92 h40 v18 h-40 z'},
    {name:'Upper Back (Left)', d:'M100,120 h50 v70 h-50 z'},
    {name:'Upper Back (Right)', d:'M150,120 h50 v70 h-50 z'},
    {name:'Shoulder Blade (Left)', d:'M110,150 a10,10 0 1,0 1,0 z'},
    {name:'Shoulder Blade (Right)', d:'M190,150 a10,10 0 1,0 1,0 z'},
    {name:'Lower Back (Left)', d:'M100,190 h50 v60 h-50 z'},
    {name:'Lower Back (Right)', d:'M150,190 h50 v60 h-50 z'},
    {name:'Buttocks/Glutes (Left)', d:'M100,250 h50 v40 h-50 z'},
    {name:'Buttocks/Glutes (Right)', d:'M150,250 h50 v40 h-50 z'},
    {name:'Left Upper Arm (Back)', d:'M60,126 h32 v56 h-32 z'},
    {name:'Left Elbow (Back)', d:'M78,182 a10,10 0 1,0 1,0 z'},
    {name:'Left Forearm (Back)', d:'M60,192 h32 v56 h-32 z'},
    {name:'Left Wrist (Back)', d:'M60,248 h32 v10 h-32 z'},
    {name:'Left Hand (Back)', d:'M60,258 h32 v30 h-32 z'},
    {name:'Right Upper Arm (Back)', d:'M208,126 h32 v56 h-32 z'},
    {name:'Right Elbow (Back)', d:'M220,182 a10,10 0 1,0 1,0 z'},
    {name:'Right Forearm (Back)', d:'M208,192 h32 v56 h-32 z'},
    {name:'Right Wrist (Back)', d:'M208,248 h32 v10 h-32 z'},
    {name:'Right Hand (Back)', d:'M208,258 h32 v30 h-32 z'},
    {name:'Left Hamstring', d:'M115,290 h40 v70 h-40 z'},
    {name:'Right Hamstring', d:'M145,290 h40 v70 h-40 z'},
    {name:'Left Knee (Back)', d:'M115,360 h40 v18 h-40 z'},
    {name:'Right Knee (Back)', d:'M145,360 h40 v18 h-40 z'},
    {name:'Left Calf', d:'M115,378 h40 v70 h-40 z'},
    {name:'Right Calf', d:'M145,378 h40 v70 h-40 z'},
    {name:'Left Achilles/Ankle', d:'M115,448 h40 v12 h-40 z'},
    {name:'Right Achilles/Ankle', d:'M145,448 h40 v12 h-40 z'},
    {name:'Left Foot (Sole)', d:'M110,460 h50 v30 h-50 z'},
    {name:'Right Foot (Sole)', d:'M140,460 h50 v30 h-50 z'},
    {name:'Toes (Back Left)', d:'M110,490 h50 v12 h-50 z'},
    {name:'Toes (Back Right)', d:'M140,490 h50 v12 h-50 z'}
  ];
  
  let side='front',temp=[...symptomLocations];
  const draw=()=>{
    const reg=side==='front'?FRONT:BACK;
    return `<svg viewBox="0 0 300 520" style="max-width:300px">
      ${reg.map(r=>{
        const sel=temp.includes(r.name);
        return `<path data-n="${r.name}" d="${r.d}" fill="${sel?'#fca5a5':'#bfdbfe'}" stroke="${sel?'#dc2626':'#2563eb'}" stroke-width="2"></path>`;
      }).join('')}
    </svg>`;
  };

  Swal.fire({
    title:'Select Pain Areas',
    html:`<div class="text-center">
      <button id="front" class="btn btn-sm btn-outline-primary me-2">Front</button>
      <button id="back" class="btn btn-sm btn-outline-primary">Back</button>
      <div id="map" class="mt-3">${draw()}</div>
      <p class="small mt-2">Click regions to toggle.</p>
    </div>`,
    width:400,
    didOpen:()=>{
      const updateSVG=()=>{document.getElementById('map').innerHTML=draw();attach();};
      function attach(){document.querySelectorAll('path').forEach(p=>p.onclick=()=>{const n=p.dataset.n;if(temp.includes(n))temp=temp.filter(x=>x!==n);else temp.push(n);updateSVG();});}
      attach();
      $('front').onclick=()=>{side='front';updateSVG();};
      $('back').onclick=()=>{side='back';updateSVG();};
    },
    preConfirm:()=>{symptomLocations=temp;updateBodyMapSummary();}
  });
}

function showMedicationModal(){
  let t=[...selectedMedications],note=customMedicationNote;
  Swal.fire({
    title:'Medications',
    html:`<div class="text-start" style="max-height:250px;overflow:auto;">
      ${COMMON_MEDICATIONS.map(m=>`<div><input type="checkbox" value="${m}" ${t.includes(m)?'checked':''}> ${m}</div>`).join('')}
    </div><hr><textarea id="note" class="form-control" placeholder="Notes...">${note}</textarea>`,
    didOpen:()=>{document.querySelectorAll('input[type=checkbox]').forEach(c=>c.onchange=e=>{const v=e.target.value;if(e.target.checked)t.push(v);else t=t.filter(x=>x!==v);});$('note').oninput=e=>note=e.target.value;},
    preConfirm:()=>{selectedMedications=t;customMedicationNote=note.trim();updateMedicationSummary();}
  });
}

function showDietModal(){
  let t=[...selectedMeals],note=customMealLog;
  Swal.fire({
    title:'Meals',
    html:`<div class="text-start" style="max-height:250px;overflow:auto;">
      ${COMMON_FOODS.map(m=>`<div><input type="checkbox" value="${m}" ${t.includes(m)?'checked':''}> ${m}</div>`).join('')}
    </div><hr><textarea id="mealnote" class="form-control" placeholder="Notes...">${note}</textarea>`,
    didOpen:()=>{document.querySelectorAll('input[type=checkbox]').forEach(c=>c.onchange=e=>{const v=e.target.value;if(e.target.checked)t.push(v);else t=t.filter(x=>x!==v);});$('mealnote').oninput=e=>note=e.target.value;},
    preConfirm:()=>{selectedMeals=t;customMealLog=note.trim();updateMealSummary();}
  });
}

// ---- Logging ----
async function saveLog(event){
  // Prevent default button action (e.g., form submission/page reload)
  if (event) event.preventDefault(); 
  
  const payload={
    mood:currentMood,
    note:$('generalNote')?.value||'',
    sleep:{bedTime:indexToTime(bedIndex),wakeUpTime:indexToTime(wakeIndex)},
    symptoms:{intensity:painIntensity,duration:symptomDuration,locations:symptomLocations},
    medication:selectedMedications.join(', ')+' '+customMedicationNote,
    meals:selectedMeals.join(', ')+' '+customMealLog,
    waterIntake:waterIntakeOz,
    exercise:{type:exerciseType,duration:exerciseDuration}
  };
  try{
    const res=await fetch(API('log_entry.php'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const j=await res.json();
    if(j.success) Swal.fire({icon:'success',title:'Saved!',timer:1200,showConfirmButton:false});
    else throw new Error(j.error||'Error');
    loadHistory();
  }catch(e){showError(e.message);}
}

// ---- Load logs ----
async function loadHistory(){
  const el=$('historyLogs'); el.innerHTML='<p class="text-muted">Loading...</p>';
  try{
    // The original app.js relies on the API to handle the authentication check (401 response).
    const res=await fetch(API('get_logs.php')); 
    if(res.status===401){location.href='login.php';return;} // Redirects if not logged in
    const data=await res.json();
    if(!data.length){el.innerHTML='<p class="text-muted">No logs yet.</p>';return;}
    el.innerHTML='';
    data.forEach(l=>{
      const d=new Date(l.timestamp).toLocaleString();
      el.innerHTML+=`<div class="p-3 mb-3 bg-white rounded shadow-sm">
        <div><strong>Mood:</strong> ${l.mood}/5 – ${d}</div>
        ${l.note?`<div>${l.note}</div>`:''}
        ${l.symptom_locations?`<div><strong>Areas:</strong> ${l.symptom_locations}</div>`:''}
      </div>`;
    });
  }catch(e){showError('Failed to load history');}
}

// ---- Init ----
window.addEventListener('load',()=>{
  renderMoodButtons();
  SYMPTOM_DURATIONS.forEach(d=>$('symptomDuration').innerHTML+=`<option>${d}</option>`);
  EXERCISE_TYPES.forEach(t=>$('exerciseType').innerHTML+=`<option>${t}</option>`);
  $('painIntensity').oninput=e=>{$('painIntensityValue').textContent=painIntensity=e.target.value;};
  $('symptomDuration').onchange=e=>symptomDuration=e.target.value;
  $('exerciseType').onchange=e=>exerciseType=e.target.value;
  $('increaseWater').onclick=()=>{waterIntakeOz=Math.min(waterIntakeOz+8,200);updateWater();};
  $('decreaseWater').onclick=()=>{waterIntakeOz=Math.max(waterIntakeOz-8,0);updateWater();};
  $('increaseDuration').onclick=()=>{exerciseDuration=Math.min(exerciseDuration+15,300);updateExercise();};
  $('decreaseDuration').onclick=()=>{exerciseDuration=Math.max(exerciseDuration-15,0);updateExercise();};
  $('openSleepModal').onclick=showSleepModal;
  $('openBodyMapModal').onclick=showBodyMapModal;
  $('openMedicationModal').onclick=showMedicationModal;
  $('openDietModal').onclick=showDietModal;
  
  // Updated to properly pass the event object to the async function
  $('logEntryButton').onclick=(e)=>saveLog(e);
  
  loadHistory();
});