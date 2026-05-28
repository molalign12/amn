import re

def process_file(fpath):
    with open(fpath, 'r', encoding='utf-8') as f:
        c = f.read()
    
    # 1. Colors
    # A bit more sophisticated function or multiple plain replacements to avoid messy lambdas
    replacements = [
        (r'(?i)var\(--guest-bg,\s*#F5F0E8\)', 'var(--g-bg)'),
        (r'(?i)var\(--guest-surface,\s*#2a1f00\)', 'var(--g-surface)'),
        (r'(?i)var\(--guest-text,\s*#1A1A1A\)', 'var(--g-text)'),
        (r'(?i)var\(--guest-text,\s*#f5f0e8\)', 'var(--g-text)'),
        (r'(?i)var\(--guest-accent,\s*#D4A843\)', 'var(--g-gold)'),
        (r'(?i)background:\s*#F5F0E8', 'background:var(--g-bg)'),
        (r'(?i)background-color:\s*#F5F0E8', 'background-color:var(--g-bg)'),
        (r'(?i)background:\s*#FFFFFF', 'background:var(--g-surface)'),
        (r'(?i)background:\s*#fff(?![\da-fA-F])', 'background:var(--g-surface)'),
        (r'(?i)background-color:\s*#fff(?![\da-fA-F])', 'background-color:var(--g-surface)'),
        (r'(?i)background:\s*#FAFAF8', 'background:var(--g-card)'),
        # Borders
        (r'(?i)border-color:\s*(?:#E8E2D9|#EDE8DF|#E0D8CC)', 'border-color:var(--g-border)'),
        (r'(?i)border:\s*([^;]+?)\s+(?:#E8E2D9|#EDE8DF|#E0D8CC)', r'border:\1 var(--g-border)'),
        (r'(?i)color:\s*#1A1A1A', 'color:var(--g-text)'),
        (r'(?i)color:\s*#6B6B6B', 'color:var(--g-muted)'),
        (r'(?i)color:\s*#9A9090', 'color:var(--g-sub)'),
        (r'(?i)color:\s*#D4A843', 'color:var(--g-gold)'),
        (r'(?i)color:\s*#8C6E2F', 'color:var(--g-gold-dk)'),
        (r'(?i)background:\s*#D4A843', 'background:var(--g-gold)'),
        (r'(?i)color:\s*#2A7A6F', 'color:var(--g-success)'),
        (r'(?i)color:\s*#C4623A', 'color:var(--g-error)'),
        (r"'Playfair Display',\s*serif", 'var(--g-font-display)'),
        (r"'DM Sans',\s*sans-serif", 'var(--g-font-body)'),
        (r"'JetBrains Mono',\s*monospace", 'var(--g-font-mono)'),
        (r'(?i)linear-gradient\(135deg,\s*#1A1A1A\s*,\s*#2C2417\s*,\s*#3D2E0F\)', 'linear-gradient(135deg,var(--g-hero-from),var(--g-hero-mid),var(--g-hero-to))'),
        (r'box-shadow:\s*0 8px 40px rgba\(0,0,0,0\.12\)', 'box-shadow:var(--g-shadow-lg)'),
        (r'box-shadow:\s*0 2px 16px rgba\(0,0,0,0\.07\)', 'box-shadow:var(--g-shadow)'),
        (r'box-shadow:\s*0 16px 48px rgba\(0,0,0,0\.13\)', 'box-shadow:var(--g-shadow-lg)')
    ]
    
    for pat, rep in replacements:
        c = re.sub(pat, rep, c)
        
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(c)
    print(f"Processed {fpath}")

files = [
    'c:/xampp/htdocs/amnen/views/guest/home.php',
    'c:/xampp/htdocs/amnen/views/guest/rooms.php',
    'c:/xampp/htdocs/amnen/views/guest/booking.php',
    'c:/xampp/htdocs/amnen/views/guest/my-bookings.php',
    'c:/xampp/htdocs/amnen/views/guest/profile.php',
    'c:/xampp/htdocs/amnen/views/partials/guest-nav.php'
]
for f in files:
    try:
        process_file(f)
    except Exception as e:
        print(e)
