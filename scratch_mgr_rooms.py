import re

fpath = 'c:/xampp/htdocs/amnen/views/manager/rooms.php'
with open(fpath, 'r', encoding='utf-8') as f:
    c = f.read()

# 1. Update queries to select image_url and correct ID alias
c = c.replace('SELECT r.id, r.room_number', 'SELECT r.room_id as id, r.image_url, r.room_number')
c = c.replace("SELECT *, 'N/A' as guest_name FROM rooms", "SELECT *, room_id as id, 'N/A' as guest_name FROM rooms")

# 2. Add image_url to rooms_data
c = re.sub(
    r"'id'\s*=>\s*\$row\['id'\]\s*\?\?\s*0,",
    r"'id' => $row['id'] ?? $row['room_id'] ?? 0,\n                    'image_url' => $row['image_url'] ?? '',",
    c
)

# 3. Add UI script
js_code = """
async function uploadRoomImage(input, roomId) {
  const file = input.files[0];
  if (!file) return;

  const label = input.closest('label');
  const cell  = input.closest('.room-img-cell');
  label.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';

  const formData = new FormData();
  formData.append('image', file);
  formData.append('room_id', roomId);

  try {
    const res = await fetch('/amnen/api/upload-room-image.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      const img = cell.querySelector('img') || document.createElement('img');
      img.src = data.url + '?t=' + Date.now();
      img.style.cssText = `width:80px;height:54px;object-fit:cover;border-radius:8px;border:1px solid var(--border)`;
      if (!cell.querySelector('img')) {
        const placeholder = cell.querySelector('div');
        if (placeholder) cell.replaceChild(img, placeholder);
      }
      label.innerHTML = '<i class="fa fa-check"></i> Updated';
      setTimeout(() => {
        label.innerHTML = `<i class="fa fa-upload"></i> Change<input type="file" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadRoomImage(this,${roomId})">`;
      }, 2000);
      
      // Update data locally
      const rd = window.ROOMS_DATA.find(x => x.id == roomId);
      if (rd) rd.image_url = data.url;
    } else {
      alert('Upload failed: ' + (data.error || 'Unknown error'));
      label.innerHTML = `<i class="fa fa-upload"></i> Upload<input type="file" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadRoomImage(this,${roomId})">`;
    }
  } catch (err) {
    alert('Upload error: ' + err.message);
    label.innerHTML = `<i class="fa fa-upload"></i> Upload<input type="file" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadRoomImage(this,${roomId})">`;
  }
}
"""

if 'async function uploadRoomImage' not in c:
    c = c.replace('</script>\n</body>', js_code + '\n</script>\n</body>')

# 4. Insert image UI in the template inside renderRooms
ui_template = """
        let imgHtml = '';
        if (r.image_url) {
            imgHtml = `<img src="${r.image_url}" alt="Room ${r.room_number}" style="width:80px;height:54px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">`;
        } else {
            imgHtml = `<div style="width:80px;height:54px;background:var(--bg2);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:18px;border:1px dashed var(--border)"><i class="fa fa-image"></i></div>`;
        }
        
        let imgCell = `
<div class="room-img-cell" style="margin-right:12px; flex-shrink:0;">
  ${imgHtml}
  <label style="display:block;margin-top:6px;font-size:11px;color:var(--accent3);cursor:pointer;text-align:center" onclick="event.stopPropagation()">
    <i class="fa fa-upload"></i> Upload
    <input type="file" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadRoomImage(this, ${r.id})">
  </label>
</div>
`;
"""

card_replacement = """        grid.innerHTML += `
            <div class="room-card" onclick="openModal(${r.id})">
                <div class="card-top ${statClass}"></div>
                <div class="card-body">
                    <div class="card-header" style="flex-wrap:nowrap;">
                        ${imgCell}
                        <div class="rn-wrap" style="flex:1;">"""

if 'imgCell' not in c:
    c = c.replace('grid.innerHTML += `', ui_template + '\n' + card_replacement)

with open(fpath, 'w', encoding='utf-8') as f:
    f.write(c)
