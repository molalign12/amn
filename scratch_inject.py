import re

THEME_CSS = """/* ── THEME 1: Warm Ivory (DEFAULT) ── */
:root,
[data-guest-theme="ivory"] {
  --g-bg:         #F5F0E8;
  --g-surface:    #FFFFFF;
  --g-card:       #FAFAF8;
  --g-border:     #E8E2D9;
  --g-text:       #1A1A1A;
  --g-muted:      #6B6B6B;
  --g-sub:        #9A9090;
  --g-gold:       #D4A843;
  --g-gold-dk:    #8C6E2F;
  --g-gold-lt:    #F0D98A;
  --g-hero-from:  #1A1A1A;
  --g-hero-mid:   #2C2417;
  --g-hero-to:    #3D2E0F;
  --g-success:    #2A7A6F;
  --g-error:      #C4623A;
  --g-nav-bg:     rgba(245,240,232,0.92);
  --g-nav-border: rgba(212,168,67,0.15);
  --g-shadow:     0 2px 20px rgba(0,0,0,0.08);
  --g-shadow-lg:  0 8px 40px rgba(0,0,0,0.12);
  --g-shadow-gold:0 4px 24px rgba(212,168,67,0.25);
  --g-radius:     16px;
  --g-font-display:'Playfair Display',serif;
  --g-font-body:   'DM Sans',sans-serif;
  --g-font-mono:   'JetBrains Mono',monospace;
}
/* ── THEME 2: Midnight Dark ── */
[data-guest-theme="midnight"] {
  --g-bg:         #0A0A0F;
  --g-surface:    #12121A;
  --g-card:       #1A1A24;
  --g-border:     rgba(99,179,237,0.12);
  --g-text:       #E8E8F0;
  --g-muted:      rgba(232,232,240,0.55);
  --g-sub:        rgba(232,232,240,0.3);
  --g-gold:       #7CB9E8;
  --g-gold-dk:    #4A90D9;
  --g-gold-lt:    #AED6F1;
  --g-hero-from:  #050510;
  --g-hero-mid:   #0A0A20;
  --g-hero-to:    #0F0F2E;
  --g-nav-bg:     rgba(10,10,15,0.92);
  --g-nav-border: rgba(99,179,237,0.15);
  --g-shadow:     0 2px 20px rgba(0,0,0,0.4);
  --g-shadow-lg:  0 8px 40px rgba(0,0,0,0.5);
  --g-shadow-gold:0 4px 24px rgba(124,185,232,0.2);
}
/* ── THEME 3: Forest Green ── */
[data-guest-theme="forest"] {
  --g-bg:         #F0F7F4;
  --g-surface:    #FFFFFF;
  --g-card:       #F7FBF9;
  --g-border:     #C8E6DA;
  --g-text:       #0D2818;
  --g-muted:      #3D6B52;
  --g-sub:        #7AA88C;
  --g-gold:       #2A7A4F;
  --g-gold-dk:    #1A5C38;
  --g-gold-lt:    #68C490;
  --g-hero-from:  #0A1F12;
  --g-hero-mid:   #122B1A;
  --g-hero-to:    #1A3D24;
  --g-nav-bg:     rgba(240,247,244,0.92);
  --g-nav-border: rgba(42,122,79,0.2);
  --g-shadow:     0 2px 20px rgba(0,0,0,0.06);
  --g-shadow-gold:0 4px 24px rgba(42,122,79,0.2);
}
/* ── THEME 4: Rose Blush ── */
[data-guest-theme="rose"] {
  --g-bg:         #FFF5F7;
  --g-surface:    #FFFFFF;
  --g-card:       #FFF9FA;
  --g-border:     #F5C6D0;
  --g-text:       #2D0A12;
  --g-muted:      #7D3344;
  --g-sub:        #C4849A;
  --g-gold:       #C4415C;
  --g-gold-dk:    #9C2D46;
  --g-gold-lt:    #F0A0B4;
  --g-hero-from:  #1A0008;
  --g-hero-mid:   #2D0A12;
  --g-hero-to:    #3D1020;
  --g-nav-bg:     rgba(255,245,247,0.92);
  --g-nav-border: rgba(196,65,92,0.15);
  --g-shadow:     0 2px 20px rgba(0,0,0,0.06);
  --g-shadow-gold:0 4px 24px rgba(196,65,92,0.2);
}
.g-theme-opt:hover { border-color: var(--g-gold) !important; transform: translateY(-2px); }
.g-theme-opt.active { border-color: var(--g-gold) !important; background: rgba(212,168,67,0.08) !important; }
#g-fab-btn:hover { transform: scale(1.1) rotate(15deg); box-shadow: 0 8px 32px rgba(212,168,67,0.5); }
@keyframes scaleIn { from { opacity:0; transform:scale(0.85) } to { opacity:1; transform:scale(1) } }
"""

FAB_HTML = """
<!-- GUEST THEME SWITCHER -->
<div id="g-theme-fab" style="position:fixed;bottom:28px;right:28px;z-index:9000;display:flex;flex-direction:column;align-items:flex-end;gap:12px">
  <div id="g-theme-panel" style="background:var(--g-surface);border:1.5px solid var(--g-border);border-radius:20px;padding:20px;box-shadow:var(--g-shadow-lg);display:none;animation:scaleIn 300ms cubic-bezier(0.34,1.56,0.64,1) both;width:220px">
    <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--g-sub);margin-bottom:14px">Choose Theme</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      <div class="g-theme-opt" data-theme="ivory" onclick="setGuestTheme('ivory',this)" style="padding:12px;border-radius:12px;border:2px solid var(--g-border);cursor:pointer;text-align:center;transition:all 200ms;background:var(--g-card)">
        <div style="width:32px;height:32px;border-radius:8px;margin:0 auto 8px;background:linear-gradient(135deg,#F5F0E8,#D4A843)"></div>
        <div style="font-size:11.5px;font-weight:500;color:var(--g-text)">Warm Ivory</div>
        <div style="font-size:10px;color:var(--g-sub);margin-top:2px">Default</div>
      </div>
      <div class="g-theme-opt" data-theme="midnight" onclick="setGuestTheme('midnight',this)" style="padding:12px;border-radius:12px;border:2px solid var(--g-border);cursor:pointer;text-align:center;transition:all 200ms;background:var(--g-card)">
        <div style="width:32px;height:32px;border-radius:8px;margin:0 auto 8px;background:linear-gradient(135deg,#0A0A0F,#7CB9E8)"></div>
        <div style="font-size:11.5px;font-weight:500;color:var(--g-text)">Midnight</div>
        <div style="font-size:10px;color:var(--g-sub);margin-top:2px">Dark</div>
      </div>
      <div class="g-theme-opt" data-theme="forest" onclick="setGuestTheme('forest',this)" style="padding:12px;border-radius:12px;border:2px solid var(--g-border);cursor:pointer;text-align:center;transition:all 200ms;background:var(--g-card)">
        <div style="width:32px;height:32px;border-radius:8px;margin:0 auto 8px;background:linear-gradient(135deg,#F0F7F4,#2A7A4F)"></div>
        <div style="font-size:11.5px;font-weight:500;color:var(--g-text)">Forest</div>
        <div style="font-size:10px;color:var(--g-sub);margin-top:2px">Green</div>
      </div>
      <div class="g-theme-opt" data-theme="rose" onclick="setGuestTheme('rose',this)" style="padding:12px;border-radius:12px;border:2px solid var(--g-border);cursor:pointer;text-align:center;transition:all 200ms;background:var(--g-card)">
        <div style="width:32px;height:32px;border-radius:8px;margin:0 auto 8px;background:linear-gradient(135deg,#FFF5F7,#C4415C)"></div>
        <div style="font-size:11.5px;font-weight:500;color:var(--g-text)">Rose</div>
        <div style="font-size:10px;color:var(--g-sub);margin-top:2px">Blush</div>
      </div>
    </div>
  </div>
  <button onclick="toggleGuestThemePanel()" id="g-fab-btn" title="Change Theme" style="width:52px;height:52px;border-radius:50%;background:var(--g-gold);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:20px;color:#1A1A1A;box-shadow:var(--g-shadow-gold);transition:all 300ms cubic-bezier(0.34,1.56,0.64,1)">
    <i class="fa fa-palette" id="fab-icon"></i>
  </button>
</div>
<script>
function setGuestTheme(theme, el) {
  document.documentElement.setAttribute('data-guest-theme', theme);
  localStorage.setItem('amnen-guest-theme', theme);
  document.querySelectorAll('.g-theme-opt').forEach(o => o.classList.remove('active'));
  if (el) el.classList.add('active');
  document.getElementById('g-theme-panel').style.display = 'none';
  const fab = document.getElementById('g-fab-btn');
  fab.style.transform = 'scale(0.9) rotate(360deg)';
  setTimeout(() => { fab.style.transform = ''; }, 300);
}
function toggleGuestThemePanel() {
  const panel = document.getElementById('g-theme-panel');
  const isOpen = panel.style.display !== 'none';
  panel.style.display = isOpen ? 'none' : 'block';
  if (!isOpen) { panel.style.animation = 'scaleIn 300ms cubic-bezier(0.34,1.56,0.64,1) both'; }
}
document.addEventListener('click', function(e) {
  const fab = document.getElementById('g-theme-fab');
  if (fab && !fab.contains(e.target)) { document.getElementById('g-theme-panel').style.display = 'none'; }
});
(function() {
  const saved = localStorage.getItem('amnen-guest-theme') || 'ivory';
  document.documentElement.setAttribute('data-guest-theme', saved);
  window.addEventListener('DOMContentLoaded', () => {
    const opt = document.querySelector('.g-theme-opt[data-theme="'+saved+'"]');
    if (opt) opt.classList.add('active');
  });
})();
</script>
</body>
"""

def inject(fpath):
    with open(fpath, 'r', encoding='utf-8') as f:
        c = f.read()
    
    if '<html lang="en"' in c and 'data-guest-theme' not in c:
        c = c.replace('<html lang="en"', '<html lang="en" data-guest-theme="ivory"')
    if 'Warm Ivory (DEFAULT)' not in c:
        c = c.replace('<style>', '<style>\n' + THEME_CSS)
    if 'g-theme-fab' not in c:
        c = c.replace('</body>', FAB_HTML)

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(c)

files = [
    'c:/xampp/htdocs/amnen/views/guest/home.php',
    'c:/xampp/htdocs/amnen/views/guest/rooms.php',
    'c:/xampp/htdocs/amnen/views/guest/booking.php',
    'c:/xampp/htdocs/amnen/views/guest/my-bookings.php'
]
for f in files:
    try:
        inject(f)
    except Exception as e:
        print(f"Error {f}: {e}")
