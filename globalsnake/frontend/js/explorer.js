let searchTimer=null;
let searchRequestId=0;

function scheduleSearch(){
  clearTimeout(searchTimer);
  searchTimer=setTimeout(searchPeptides,300);
}

function clearSearch(){
  document.getElementById('search').value='';
  document.getElementById('venom').value='';
  document.getElementById('cancer').value='';
  history.replaceState({},'',location.pathname);
  searchPeptides();
}

function initExplorer(){
  const params=new URLSearchParams(location.search);
  const q=params.get('q');
  if(q!==null) document.getElementById('search').value=q;
  const venom=params.get('venom');
  if(venom!==null) document.getElementById('venom').value=venom;
  const cancer=params.get('cancer');
  if(cancer!==null) document.getElementById('cancer').value=cancer;
  searchPeptides();
}

function renderSearchState(type,title,detail){
  const box=document.getElementById('results');
  box.innerHTML=`<tr><td colspan="9" class="empty ${type||''}"><div class="search-spinner" aria-hidden="true"></div><strong>${esc(title)}</strong><span>${esc(detail)}</span></td></tr>`;
}

async function searchPeptides(){
  const requestId=++searchRequestId;
  const q=document.getElementById('search').value.trim();
  const venom=document.getElementById('venom').value;
  const cancer=document.getElementById('cancer').value.trim();
  const box=document.getElementById('results');
  const related=document.getElementById('related');
  const status=document.getElementById('status');
  renderSearchState('loading','Searching the evidence index…','Checking peptide, source, cancer-context and provenance fields.');
  related.hidden=true; status.textContent='';
  const params={q,venom,cancer};
  try{
    const broadSnakeQuery = /^(snake|snakes|venom|venomous|all|all snakes|snake venom|toxin|toxins|healthcare|healthcare use|medicine|medical|drug|drug discovery)$/i.test(q) || /^(snake|snakes)$/i.test(venom);
    if(broadSnakeQuery){
      const catInput=document.getElementById('snake-catalog-search'); if(catInput)catInput.value=q||'snake';
      const cr=await api('snake-catalog',{q:q||'snake',limit:150});
      if(requestId!==searchRequestId)return;
      if(cr?.success && cr.data?.length){
        box.innerHTML=cr.data.map(x=>`<tr><td><b>${esc(x.common_name)}</b><br><small>Species reference</small></td><td><i>${esc(x.scientific_name)}</i></td><td>—</td><td>Not established in this catalogue</td><td>Not assigned</td><td>Species reference only</td><td>No linked peptide structure</td><td>Species listing; peptide origin not verified</td><td><span class="badge">Reference record</span></td>`).join('');
        status.innerHTML=`<span class="status-dot"></span><b>${cr.count}</b> matching snake-species reference records shown. These are not confirmed anticancer peptide records.`;
        loadSnakeCatalog(); return;
      }
    }
    const r=await api('peptides',params);
    if(requestId!==searchRequestId)return;
    if(!r || !r.success) throw new Error(r?.error || 'The evidence API returned an error.');
    box.innerHTML='';
    if(r.data?.length){
      status.innerHTML=`<span class="status-dot"></span><b>${r.count}</b> record${r.count===1?'':'s'} found in the local evidence index.`;
      r.data.forEach(p=>{
        const tr=document.createElement('tr');
        tr.innerHTML=`<td><a class="link" href="peptide.html?id=${encodeURIComponent(p.public_id)}">${esc(p.peptide_name)}</a><br><small>${esc(p.public_id)} · ${esc(p.venom_groups||'Source group not reported')}</small></td><td>${esc(p.animal_sources||'Animal/species not reported')}<br><small>${esc(p.biological_nature||'Nature unknown')}</small></td><td>${p.sequence_length??'Not reported'}</td><td>${p.activity_records||0} assay record(s)</td><td>${p.mechanism_records||0} mechanism record(s)</td><td>${p.first_evidence_year||'Year unknown'} – ${p.latest_evidence_year||'latest year unknown'}<br><small>Based on linked evidence records; not a claim of newest literature globally</small></td><td>${p.pdb_ids?`<a class="link" href="structure.html?pdb=${encodeURIComponent(String(p.pdb_ids).split(',')[0])}">${esc(String(p.pdb_ids).split(',')[0])} · Open 3D</a><br><small>Linked structure; sequence match must be checked</small>`:'<span class="badge">No linked structure</span><br><small>Modelling status not recorded</small>'}</td><td><span class="badge">${esc(p.venom_verification_status||'Not established')}</span></td><td><span class="badge">${esc(p.curation_status||'Needs review')}</span><br><small>${p.curation_status==='Curated'?'Curated record':p.curation_status==='Partially curated'?'Curation in progress':'Awaiting verification/review'}</small></td>`;
        box.appendChild(tr);
      });
      const url=new URL(location.href);
      q?url.searchParams.set('q',q):url.searchParams.delete('q');
      venom?url.searchParams.set('venom',venom):url.searchParams.delete('venom');
      cancer?url.searchParams.set('cancer',cancer):url.searchParams.delete('cancer');
      history.replaceState({},'',url);
      return;
    }
    const candidateMatches=(candidateCatalog||[]).filter(x=>q&&[x.name,x.group,x.species,x.evidence,x.status,x.search].join(' ').toLowerCase().includes(q.toLowerCase()));
    if(candidateMatches.length && !cancer && !venom){
      box.innerHTML=candidateMatches.map(x=>`<tr><td><b>${esc(x.name)}</b><br><small>Literature-linked candidate index</small></td><td>${esc(x.species)}<br><small>${esc(x.group)} venom</small></td><td>See source / sequence record</td><td>${esc(x.evidence)}</td><td>Mechanism requires source-specific review</td><td><a class="link" target="_blank" rel="noopener" href="${esc(x.source)}">Open literature source ↗</a></td><td>${x.pdb?`<a class="link" href="structure.html?pdb=${encodeURIComponent(x.pdb)}">PDB ${esc(x.pdb)} · Open 3D</a><br><small>${esc(x.structure)}</small>`:`<span class="badge">No verified PDB linked</span><br><small>${esc(x.structure)}</small>`}</td><td><span class="badge">${esc(x.status)}</span></td><td><span class="badge">Discovery index</span><br><small>Not yet a full relational assay record</small></td></tr>`).join('');
      status.innerHTML=`<b>${candidateMatches.length} literature-linked candidate match(es)</b> for “${esc(q)}”. These discovery records are distinct from fully curated local assay records.`;
      return;
    }
    const filters=[];
    if(q)filters.push(`“${q}”`);
    if(venom)filters.push(venom);
    if(cancer)filters.push(cancer);
    status.innerHTML=`<b>No exact curated peptide match</b> for ${filters.join(' + ')||'the current filters'}.`;
    if(q && !cancer && !venom){
      try{
        const cr=await api('snake-catalog',{q,limit:50});
        if(requestId!==searchRequestId)return;
        if(cr?.success && cr.data?.length){
          const catInput=document.getElementById('snake-catalog-search'); if(catInput)catInput.value=q;
          box.innerHTML=cr.data.map(x=>`<tr><td><b>${esc(x.common_name)}</b><br><small>Species reference</small></td><td><i>${esc(x.scientific_name)}</i></td><td>—</td><td>${esc(x.anticancer_evidence_status)}</td><td>Not assigned</td><td>Species reference; no peptide assay inferred</td><td>No linked peptide structure</td><td>Species listing; peptide origin not verified</td><td><span class="badge">Reference record</span><br><small><a class="link" target="_blank" rel="noopener" href="${esc(x.healthcare_source||x.taxonomy_reference)}">Source ↗</a></small></td>`).join('');
          status.innerHTML=`<b>${cr.count}</b> species/reference matches shown for “${esc(q)}”. These are not automatically anticancer peptide records.`;
          loadSnakeCatalog(); return;
        }
      }catch(e){}
    }
    if(r.database_empty){
      box.innerHTML='<tr><td colspan="9" class="empty empty-action"><div class="empty-icon">⌕</div><strong>The local peptide evidence index is empty.</strong><span>Snake species references are available in the catalogue below. To search experimentally reported peptides and assays, import source-backed peptide records. No synthetic records are created.</span><a class="button primary" href="#snake-catalog-panel">Open snake-species catalogue ↓</a></td></tr>';
    }else{
      box.innerHTML='<tr><td colspan="9" class="empty"><div class="empty-icon">⌕</div><strong>No exact peptide record found.</strong><span>Try a peptide name or use “snake” to browse the 107-species reference catalogue below. Species references are separate from confirmed anticancer peptide records.</span></td></tr>';
    }
    if(r.related?.length){
      related.hidden=false;
      related.innerHTML='<span><b>Related evidence:</b></span>'+r.related.map(x=>`<a class="related-item" href="peptide.html?id=${encodeURIComponent(x.public_id)}">${esc(x.peptide_name)} · ${esc(x.venom_group||'Unclassified')}</a>`).join('');
    }
  }catch(e){
    if(requestId!==searchRequestId)return;
    status.innerHTML='<b class="error-text">Search could not be completed.</b>';
    box.innerHTML=`<tr><td colspan="9" class="empty error-state"><div class="empty-icon">!</div><strong>Database / API connection problem</strong><span>${esc(e.message)} Check Apache, MySQL and database configuration.</span><button class="button" onclick="searchPeptides()">Retry search</button></td></tr>`;
  }
}


async function loadSnakeCatalog(){
  const box=document.getElementById('snake-catalog-results'); if(!box)return;
  const q=(document.getElementById('snake-catalog-search')?.value||'').trim();
  try{
    const r=await api('snake-catalog',{q,limit:150});
    if(!r?.success) throw new Error('Catalogue API error');
    document.getElementById('snake-catalog-count').textContent=`${r.count} shown · ${r.total_catalog_records} bundled reference records`;
    box.innerHTML='';
    if(!r.data.length){box.innerHTML='<tr><td colspan="7">No species match that filter.</td></tr>';return;}
    r.data.forEach(x=>{const tr=document.createElement('tr');tr.innerHTML=`<td><i>${esc(x.scientific_name)}</i></td><td>${esc(x.common_name)}</td><td>${esc(x.record_type)}</td><td><span class="badge">${esc(x.anticancer_evidence_status)}</span><br><small>${esc(x.evidence_note)}</small></td><td>${esc(x.healthcare_research_use||'No species-specific application curated')}</td><td>${esc(x.healthcare_research_status||'Current status unknown')}</td><td><a class="link" href="${esc(x.healthcare_source||x.taxonomy_reference)}" target="_blank" rel="noopener">Healthcare / literature source ↗</a><br><small><a class="link" href="${esc(x.taxonomy_reference)}" target="_blank" rel="noopener">Taxonomy ↗</a></small></td>`;box.appendChild(tr);});
  }catch(e){box.innerHTML='<tr><td colspan="7">Could not load the bundled catalogue. Import database/seed_global_snake_catalog.sql after schema.sql, or re-import database/seed.sql on a fresh database.</td></tr>';document.getElementById('snake-catalog-count').textContent='Catalogue unavailable';}
}


let candidateCatalog=[];
async function loadCandidateCatalog(){const body=document.getElementById('candidate-catalog-results');if(!body)return;try{const response=await fetch('data/anticancer-candidate-catalog.json');if(!response.ok)throw new Error('Candidate catalogue could not be loaded');candidateCatalog=await response.json();document.getElementById('candidate-count').textContent=`${candidateCatalog.length} candidates · evidence status differentiated`;filterCandidateCatalog();}catch(e){body.innerHTML='<tr><td colspan="7">Candidate catalogue unavailable. Check that frontend/data/anticancer-candidate-catalog.json is present.</td></tr>';document.getElementById('candidate-count').textContent='Catalogue unavailable';}}
function filterCandidateCatalog(){const body=document.getElementById('candidate-catalog-results');if(!body)return;const q=(document.getElementById('candidate-search')?.value||'').trim().toLowerCase();const rows=candidateCatalog.filter(x=>!q||[x.name,x.group,x.species,x.evidence,x.status,x.search].join(' ').toLowerCase().includes(q));document.getElementById('candidate-count').textContent=`${rows.length} shown / ${candidateCatalog.length} candidates`;if(!rows.length){body.innerHTML='<tr><td colspan="7">No candidate matches this filter.</td></tr>';return;}body.innerHTML=rows.map(x=>{const structure=x.pdb?`PDB ${esc(x.pdb)} · ${esc(x.structure)}`:esc(x.structure);const viewer=x.pdb?`<a class="link" href="structure.html?pdb=${encodeURIComponent(x.pdb)}">Open 3D Lab ↗</a><br><a class="link" target="_blank" rel="noopener" href="https://www.rcsb.org/structure/${encodeURIComponent(x.pdb)}">RCSB record ↗</a>`:`<a class="link" target="_blank" rel="noopener" href="https://www.rcsb.org/search?query=${encodeURIComponent(x.search||x.name)}">Search RCSB ↗</a>`;return `<tr><td><b>${esc(x.name)}</b></td><td>${esc(x.group)}<br><small>${esc(x.species)}</small></td><td>${esc(x.evidence)}</td><td><span class="badge">${esc(x.status)}</span></td><td><a class="link" target="_blank" rel="noopener" href="${esc(x.source)}">Open paper / literature source ↗</a><br><small>Source is a paper or discovery search; inspect before asserting assay details.</small></td><td>${structure}</td><td>${viewer}</td></tr>`}).join('');}
