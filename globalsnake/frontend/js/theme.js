(function(){
  const key='venomoncodb-theme';
  const saved=localStorage.getItem(key);
  /* Light is the platform default; only an explicit current dark preference switches it. */
  if(saved==='dark') {
    /* One-time migration from the old dark-first package: reopen in light mode. */
    document.documentElement.setAttribute('data-theme','light');
    localStorage.setItem(key,'light');
  } else document.documentElement.setAttribute('data-theme','light');
  function sync(){
    const light=document.documentElement.getAttribute('data-theme')==='light';
    document.querySelectorAll('[data-theme-toggle]').forEach(b=>{b.innerHTML=light?'☾ <span>Dark theme</span>':'☼ <span>Light theme</span>';b.setAttribute('aria-label',light?'Switch to dark theme':'Switch to light theme');b.title=light?'Switch to dark theme':'Switch to light theme';});
  }
  document.addEventListener('click',e=>{const b=e.target.closest('[data-theme-toggle]');if(!b)return;const light=document.documentElement.getAttribute('data-theme')!=='light';if(light)document.documentElement.setAttribute('data-theme','light');else document.documentElement.removeAttribute('data-theme');localStorage.setItem(key,light?'light':'dark');sync();});
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',sync);else sync();
})();
