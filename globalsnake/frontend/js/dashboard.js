let charts=[];
let cancerTypes=[];
function pct(n,total){return total?Math.round((Number(n||0)/Number(total))*100):0}
function destroyCharts(){charts.forEach(c=>c.destroy());charts=[]}
function coverageCard(label,value,total,cls){const p=pct(value,total);return `<div class="coverage-card ${cls||''}"><div class="coverage-top"><span>${esc(label)}</span><strong>${p}%</strong></div><div class="coverage-track"><i style="width:${p}%"></i></div><small>${Number(value||0).toLocaleString()} of ${Number(total||0).toLocaleString()} peptides in the selected evidence set</small></div>`}
function selectedCancerId(){return new URLSearchParams(location.search).get('cancer_type_id')||''}
function changeCancerFilter(value){
 const url=new URL(location.href);
 if(value) url.searchParams.set('cancer_type_id',value); else url.searchParams.delete('cancer_type_id');
 history.replaceState({},'',url);
 loadDashboard();
}
function cancerFilterHTML(selected){
 return `<section class="panel insight-filter"><div><div class="eyebrow">EVIDENCE FILTER</div><h3>Cancer-type context</h3><p>Choose a cancer type to recalculate the evidence counts and publication coverage from matching cell-line activity records.</p></div><div class="filter-control"><label for="cancerFilter">Cancer type</label><select id="cancerFilter" class="select" onchange="changeCancerFilter(this.value)"><option value="">All cancer types</option>${cancerTypes.map(x=>`<option value="${x.cancer_type_id}" ${String(selected)===String(x.cancer_type_id)?'selected':''}>${esc(x.cancer_name)}</option>`).join('')}</select></div></section>`
}
async function loadDashboard(){
 const shell=document.getElementById('insightsApp'); if(!shell)return;
 try{
  const [r,c]=await Promise.all([api('dashboard',{cancer_type_id:selectedCancerId()}),api('cancers')]);
  if(!r.success)throw new Error(r.error||'Insights API error');
  if(!c.success)throw new Error(c.error||'Cancer-type API error');
  cancerTypes=c.data||[];
  const total=Number(r.summary.peptides||0), activity=Number(r.summary.activity_records||0), papers=Number(r.summary.publications||0), structures=Number(r.summary.structures||0);
  const cpl=r.completeness||{};
  const selected=r.filter?.cancer_name||'All cancer types';
  shell.innerHTML=`
   ${cancerFilterHTML(r.filter?.cancer_type_id||'')}
   <div class="filter-state"><span>Showing evidence context:</span><strong>${esc(selected)}</strong><span>· publication count uses distinct source publications from the matching activity records.</span></div>
   <section class="insight-kpis">
    <div class="insight-kpi"><span>VERIFIED VENOM PEPTIDES</span><strong>${total.toLocaleString()}</strong><small>unique natural peptides in the selected cancer context</small></div>
    <div class="insight-kpi"><span>ACTIVITY EVIDENCE</span><strong>${activity.toLocaleString()}</strong><small>cell-line / assay records matching the selected context</small></div>
    <div class="insight-kpi"><span>PUBLICATIONS</span><strong>${papers.toLocaleString()}</strong><small>distinct publications linked to matching activity evidence</small></div>
    <div class="insight-kpi"><span>STRUCTURES</span><strong>${structures.toLocaleString()}</strong><small>documented structures for peptides in the selected set</small></div>
   </section>
   <section class="insight-hero panel"><div><div class="eyebrow">PROVENANCE-AWARE EVIDENCE LANDSCAPE</div><h2>Connect source, sequence, experiment, mechanism and structure without filling gaps.</h2><p>Analytics are recalculated after each cancer-type selection. Publication totals count distinct source publications rather than repeated experimental rows.</p></div><div class="radar-orb"><div class="orb-ring r1"></div><div class="orb-ring r2"></div><div class="orb-core">EVIDENCE<br><small>INDEX</small></div></div></section>
   <section class="coverage-section"><div class="section-title compact"><div class="eyebrow">EVIDENCE COMPLETENESS</div><h2>What is actually documented?</h2></div><div class="coverage-grid">${coverageCard('Sequence source',cpl.seq_verified,total,'c1')}${coverageCard('Activity evidence',cpl.has_activity,total,'c2')}${coverageCard('Mechanism evidence',cpl.has_mechanism,total,'c3')}${coverageCard('Structure evidence',cpl.has_structure,total,'c4')}</div></section>
   <section class="insight-grid">
    <div class="panel chart-box insight-chart"><div class="eyebrow">VERIFIED VENOM LANDSCAPE</div><h3>Natural verified peptides by source group</h3><canvas id="venomChart"></canvas></div>
    <div class="panel chart-box insight-chart"><div class="eyebrow">CANCER CONTEXT</div><h3>${r.filter?.cancer_type_id?'Activity evidence in '+esc(selected):'Documented activity evidence by cancer type'}</h3><canvas id="cancerChart"></canvas></div>
    <div class="panel chart-box insight-wide"><div class="eyebrow">PUBLICATION TIMELINE</div><h3>Distinct source publications over time</h3><canvas id="yearChart"></canvas></div>
   </section>
   <section class="insight-lower"><div class="panel"><div class="eyebrow">VERIFICATION BOUNDARY</div><h3>What is outside the verified set?</h3><div class="group-list">${(r.review||[]).map(x=>`<div class="group-row"><div><b>${esc(x.status||'Needs review')}</b><span>records excluded from verified analytics</span></div><strong>${Number(x.count||0).toLocaleString()}</strong><i><em style="width:${r.summary.peptides?pct(x.count,r.summary.peptides):0}%"></em></i></div>`).join('') || '<div class="read-card"><b>Clean set</b><span>No unverified records are currently loaded.</span></div>'}</div></div><div class="panel"><div class="eyebrow">RESEARCH-SAFE INTERPRETATION</div><h3>How to read this dashboard</h3><div class="read-card"><b>Verified</b><span>Explicit source evidence supports natural venom origin; it is not a clinical efficacy claim.</span></div><div class="read-card"><b>Missing IC50</b><span>Means the source did not provide a captured numeric value, not that activity was absent.</span></div><div class="read-card"><b>Missing mechanism</b><span>Does not imply absence of a mechanism.</span></div><div class="read-card"><b>Filtered publication count</b><span>Counts distinct publications attached to activity records for the selected cancer type.</span></div></div></section>`;
  destroyCharts();
  const common={responsive:true,maintainAspectRatio:false,animation:{duration:650},plugins:{legend:{position:'bottom',labels:{color:'#c6d8dc',font:{size:11,weight:'600'},padding:16,usePointStyle:true,pointStyle:'circle'}}}};

  // A visible, research-friendly 3D doughnut for the verified venom landscape.
  // The depth is rendered as repeated offset arcs behind the main ring; no data is changed.
  const doughnut3DPlugin={
    id:'doughnut3D',
    beforeDatasetDraw(chart,args){
      if(chart.config.type!=='doughnut')return;
      const meta=chart.getDatasetMeta(args.index); if(!meta||!meta.data.length)return;
      const ctx=chart.ctx; const depth=14; ctx.save();
      for(let layer=depth;layer>=2;layer-=2){
        meta.data.forEach((arc,i)=>{
          const p=arc.getProps(['x','y','innerRadius','outerRadius','startAngle','endAngle'],true);
          ctx.beginPath(); ctx.arc(p.x,p.y+layer,p.outerRadius,p.startAngle,p.endAngle);
          ctx.arc(p.x,p.y+layer,p.innerRadius,p.endAngle,p.startAngle,true); ctx.closePath();
          ctx.fillStyle=['#087b70','#08759b','#6d5a22','#3e6a78','#71405e','#536a29'][i%6];
          ctx.fill();
        });
      }
      ctx.restore();
    },
    afterDraw(chart){
      const meta=chart.getDatasetMeta(0); if(!meta||!meta.data.length)return;
      const p=meta.data[0].getProps(['x','y'],true); const total=(chart.data.datasets[0].data||[]).reduce((a,b)=>a+Number(b||0),0);
      const ctx=chart.ctx; ctx.save(); ctx.textAlign='center'; ctx.textBaseline='middle';
      ctx.fillStyle='#e8f7f8'; ctx.font='800 25px Space Grotesk, Inter, sans-serif'; ctx.fillText(total.toLocaleString(),p.x,p.y-4);
      ctx.fillStyle='#6f8992'; ctx.font='700 8px Inter, sans-serif'; ctx.letterSpacing='1px'; ctx.fillText('VERIFIED',p.x,p.y+14); ctx.restore();
    }
  };
  const venomColors=['#24e2c1','#4cc9ff','#e8c56c','#b98cff','#ff7180','#7ee0a7'];
  const vctx=document.getElementById('venomChart');
  if(vctx)charts.push(new Chart(vctx,{type:'doughnut',plugins:[doughnut3DPlugin],data:{labels:r.venom_groups.map(x=>x.venom_group),datasets:[{data:r.venom_groups.map(x=>Number(x.count||0)),backgroundColor:venomColors.slice(0,Math.max(1,r.venom_groups.length)),hoverBackgroundColor:venomColors.slice(0,Math.max(1,r.venom_groups.length)),borderWidth:3,borderColor:'#06131b',hoverOffset:5}]},options:{...common,cutout:'64%',layout:{padding:{top:8,bottom:6}},plugins:{...common.plugins,tooltip:{callbacks:{label:(ctx)=>` ${ctx.label}: ${Number(ctx.raw||0).toLocaleString()} verified peptide${Number(ctx.raw||0)===1?'':'s'}`}}}}}));
  const cctx=document.getElementById('cancerChart'); if(cctx)charts.push(new Chart(cctx,{type:'bar',data:{labels:r.cancer_types.map(x=>x.cancer_name),datasets:[{label:'Activity records',data:r.cancer_types.map(x=>x.count),borderRadius:6}]},options:{...common,indexAxis:'y',scales:{x:{beginAtZero:true,ticks:{color:'#91aab4'},grid:{color:'rgba(95,190,210,.08)'}},y:{ticks:{color:'#91aab4'},grid:{display:false}}},plugins:{legend:{display:false}}}}));
  const yctx=document.getElementById('yearChart'); if(yctx)charts.push(new Chart(yctx,{type:'line',data:{labels:r.publication_years.map(x=>x.year),datasets:[{label:'Distinct publications',data:r.publication_years.map(x=>x.count),tension:.35,fill:true,borderWidth:2,pointRadius:3}]},options:{...common,scales:{x:{ticks:{color:'#91aab4'},grid:{color:'rgba(95,190,210,.08)'}},y:{beginAtZero:true,ticks:{color:'#91aab4'},grid:{color:'rgba(95,190,210,.08)'}}}}}));
 }catch(e){shell.innerHTML=`<section class="panel empty error-state"><div class="empty-icon">!</div><strong>Insights could not load</strong><span>${esc(e.message)}</span><button class="button" onclick="loadDashboard()">Retry</button></section>`;console.error(e)}
}
