function cleanSeq(){
  return document.getElementById('seqInput').value.toUpperCase().replace(/[^A-Z]/g,'');
}
function sequenceStats(s){
  const hyd='AVILMFWY', charged='DEKR', positive='KR', negative='DE';
  const counts={};
  [...s].forEach(a=>counts[a]=(counts[a]||0)+1);
  const hydN=[...s].filter(a=>hyd.includes(a)).length;
  const chargeN=[...s].filter(a=>charged.includes(a)).length;
  const pos=[...s].filter(a=>positive.includes(a)).length;
  const neg=[...s].filter(a=>negative.includes(a)).length;
  const mw=([...s].reduce((sum,a)=>sum+({A:71.08,C:103.14,D:115.09,E:129.12,F:147.17,G:57.05,H:137.14,I:113.16,K:128.17,L:113.16,M:131.20,N:114.10,P:97.12,Q:128.13,R:156.19,S:87.08,T:101.11,V:99.13,W:186.21,Y:163.17}[a]||0),18.0153));
  return {counts,hydN,chargeN,pos,neg,mw};
}
function renderComposition(counts,total){
  const top=Object.entries(counts).sort((a,b)=>b[1]-a[1]);
  return top.map(([a,n])=>`<div class="aa-row"><span class="aa-letter">${a}</span><div class="aa-track"><i style="width:${Math.max(3,(n/total)*100)}%"></i></div><span class="aa-value">${n} · ${((n/total)*100).toFixed(1)}%</span></div>`).join('');
}
function analyzeSequence(){
  const raw=document.getElementById('seqInput').value;
  const s=cleanSeq(),box=document.getElementById('analysis'),st=document.getElementById('seqStatus');
  if(!raw.trim()){st.innerHTML='<b class="error-text">Enter a peptide sequence first.</b>';box.innerHTML='<div class="empty"><div class="empty-icon">⌁</div><strong>Waiting for sequence</strong><span>Paste a standard one-letter amino-acid sequence above.</span></div>';return;}
  if(!/^[ACDEFGHIKLMNPQRSTVWY]+$/.test(s)){
    st.innerHTML='<b class="error-text">Unsupported amino-acid code detected.</b>';
    box.innerHTML='<div class="empty error-state"><div class="empty-icon">!</div><strong>Sequence not accepted</strong><span>Use standard protein/peptide one-letter codes: A, C, D, E, F, G, H, I, K, L, M, N, P, Q, R, S, T, V, W, Y.</span></div>';
    return;
  }
  const x=sequenceStats(s);
  st.innerHTML='<span class="status-dot"></span><b>Sequence accepted.</b> Local analysis completed without sending the sequence anywhere.';
  box.innerHTML=`
    <div class="analysis-hero"><div><span class="mini-label">ANALYZED SEQUENCE</span><strong>${s.length} aa</strong><small>Standard amino-acid sequence</small></div><div class="analysis-code">${esc(s)}</div></div>
    <div class="metric-grid">
      <div class="metric-card"><span>Length</span><strong>${s.length}</strong><small>amino acids</small></div>
      <div class="metric-card"><span>Cysteines</span><strong>${x.counts.C||0}</strong><small>disulfide-relevant residue count</small></div>
      <div class="metric-card"><span>Hydrophobic</span><strong>${(x.hydN/s.length*100).toFixed(1)}%</strong><small>AVILMFWY residue-count proxy</small></div>
      <div class="metric-card"><span>Net charge proxy</span><strong>${x.pos-x.neg>0?'+':''}${x.pos-x.neg}</strong><small>K/R − D/E count</small></div>
      <div class="metric-card"><span>Approx. mass</span><strong>${x.mw.toFixed(1)}</strong><small>Da · sequence-derived estimate</small></div>
      <div class="metric-card"><span>Charged</span><strong>${x.chargeN}</strong><small>D/E/K/R residues</small></div>
    </div>
    <div class="composition-panel"><div class="mini-label">AMINO-ACID COMPOSITION</div>${renderComposition(x.counts,s.length)}</div>
    <div class="notice"><b>Interpretation boundary:</b> these are sequence-derived properties only. They do not establish anticancer activity, toxicity, mechanism, clinical efficacy or therapeutic suitability.</div>`;
}
function loadExample(){document.getElementById('seqInput').value='MCMPCFTTDHQMARKCDDCCGGKGRGKCYGPQCLCR';analyzeSequence();}
function clearBlast(){document.getElementById('seqInput').value='';document.getElementById('analysis').innerHTML='<div class="empty"><div class="empty-icon">⌁</div><strong>Waiting for sequence</strong><span>Enter a peptide sequence to calculate local properties.</span></div>';document.getElementById('seqStatus').textContent='';}
function submitBlast(){
  const s=cleanSeq(),st=document.getElementById('seqStatus');
  if(!s){st.innerHTML='<b class="error-text">Enter a sequence before opening BLASTP.</b>';return;}
  if(!/^[ACDEFGHIKLMNPQRSTVWY]+$/.test(s)){st.innerHTML='<b class="error-text">Sequence contains unsupported characters.</b>';return;}
  const u='https://blast.ncbi.nlm.nih.gov/Blast.cgi?PROGRAM=blastp&PAGE_TYPE=BlastSearch&QUERY='+encodeURIComponent(s);
  const w=window.open(u,'_blank','noopener');
  if(!w)st.innerHTML='<b class="error-text">Your browser blocked the BLASTP tab.</b> Allow pop-ups for localhost and try again.';
  else st.innerHTML='<span class="status-dot"></span><b>NCBI BLASTP opened in a new tab.</b>';
}
window.addEventListener('DOMContentLoaded',()=>{
  const ta=document.getElementById('seqInput');
  if(ta)ta.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key==='Enter')analyzeSequence();});
});
