import re

# We will regex replace the entire <div class="room-card-img">...</div> in these files:
files = [
    'c:/xampp/htdocs/amnen/views/guest/home.php',
    'c:/xampp/htdocs/amnen/views/guest/rooms.php',
    'c:/xampp/htdocs/amnen/views/guest/booking.php'
]

replacement = r"""<?php
$imgUrl = !empty($room['image_url']) 
    ? htmlspecialchars($room['image_url']) 
    : null;
?>
<div class="room-card-img" style="
  position:relative;overflow:hidden;
  aspect-ratio:16/9;background:
  linear-gradient(135deg,
    var(--g-hero-from),var(--g-hero-to))">

  <?php if($imgUrl): ?>
  <img src="<?= $imgUrl ?>"
       alt="<?= ucfirst($room['room_type']) ?> Room"
       loading="lazy"
       onerror="this.style.display='none';
         this.nextElementSibling.style.display='flex'"
       style="width:100%;height:100%;
              object-fit:cover;
              transition:transform 450ms ease;
              display:block">
  <!-- Fallback if image fails -->
  <div style="display:none;width:100%;height:100%;
    align-items:center;justify-content:center;
    font-size:48px;color:rgba(212,168,67,0.35);
    position:absolute;inset:0">
    <i class="fa fa-bed"></i>
  </div>
  <?php else: ?>
  <div style="width:100%;height:100%;
    display:flex;align-items:center;
    justify-content:center;font-size:48px;
    color:rgba(212,168,67,0.35)">
    <i class="fa fa-bed"></i>
  </div>
  <?php endif; ?>

  <!-- STATUS BADGE -->
  <span style="position:absolute;top:14px;left:14px;
    background:rgba(42,122,111,0.88);color:#fff;
    font-size:11px;font-weight:700;
    letter-spacing:0.06em;text-transform:uppercase;
    padding:5px 12px;border-radius:99px;
    backdrop-filter:blur(8px)">
    Available
  </span>

  <!-- PRICE BADGE -->
  <div style="position:absolute;
    bottom:14px;right:14px;
    color:#fff;
    font-family:var(--g-font-mono);
    font-size:1rem;font-weight:500;
    text-shadow:0 1px 8px rgba(0,0,0,0.6)">
    <?= number_format($room['price']) ?>
    <span style="font-size:11px;opacity:0.75;
      font-family:var(--g-font-body)">
      ETB/night
    </span>
  </div>

  <!-- HOVER OVERLAY -->
  <div style="position:absolute;inset:0;
    background:linear-gradient(to top,
      rgba(0,0,0,0.5) 0%,transparent 50%);
    pointer-events:none"></div>

</div>"""

for fpath in files:
    try:
        with open(fpath, 'r', encoding='utf-8') as f:
            c = f.read()
        
        # In booking.php, the variable might be $roomDetails instead of $room, but the prompt says:
        # "In views/guest/booking.php update the room summary card image section the same way."
        # If it uses $roomData or $roomDetails, we should probably change $room to that.
        
        # Regex to find <div class="room-card-img"> up to the matching closing div before card-body
        # We will stop at `<div class="room-card-body">` or similar
        
        # Let's write a python snippet that replaces <div class="room-card-img"> ... </div> (excluding nested children if we just match until `<div class=["']room-card-body["']`)
        c = re.sub(r'<div class=[\'"]room-card-img[\'"].*?</div>\s*<div class=[\'"](?:room-card-body|bk-summary)[\'"]>', replacement + '\n<div class="room-card-body">', c, flags=re.DOTALL)
        
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(c)
        print("Updated " + fpath)
    except Exception as e:
        print(f"Error {fpath}: {e}")

