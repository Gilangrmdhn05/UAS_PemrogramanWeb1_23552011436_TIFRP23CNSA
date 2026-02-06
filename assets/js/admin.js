// Simple admin JS: sidebar toggle, tooltips, active nav
document.addEventListener('DOMContentLoaded', function(){
  const body = document.body;
  const sidebarToggle = document.getElementById('sidebarToggle');
  const collapsedKey = 'sidebarCollapsed';

  // Apply saved state
  try{
    const saved = localStorage.getItem(collapsedKey);
    if(saved === '1') body.classList.add('sidebar-collapsed');
  }catch(e){/* ignore */}

  if(sidebarToggle){
    sidebarToggle.addEventListener('click', function(e){
      e.preventDefault();
      body.classList.toggle('sidebar-collapsed');
      try{
        if(body.classList.contains('sidebar-collapsed')) localStorage.setItem(collapsedKey,'1');
        else localStorage.setItem(collapsedKey,'0');
      }catch(e){/* ignore */}
    });
  }

  // Initialize Bootstrap tooltips if available
  try{
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  }catch(e){/* not available */}

  // Mark active link in sidebar
  const path = window.location.pathname.split('/').pop();
  document.querySelectorAll('.sidebar a.nav-link').forEach(a => {
    const href = a.getAttribute('href');
    if(href && href === path){
      a.classList.add('active');
    }
  });

});
