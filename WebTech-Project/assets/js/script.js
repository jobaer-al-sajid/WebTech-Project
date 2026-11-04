// =========================================================
// Dental Palace — shared JS
// =========================================================

// Confirm before delete actions
document.addEventListener('click', function (e) {
  const el = e.target.closest('[data-confirm]');
  if (el) {
    const msg = el.getAttribute('data-confirm') || 'Are you sure?';
    if (!confirm(msg)) e.preventDefault();
  }
});

// Auto-hide alert boxes after 4 seconds
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert').forEach(function (box) {
    setTimeout(function () {
      box.style.transition = 'opacity .5s';
      box.style.opacity = '0';
      setTimeout(function () { box.style.display = 'none'; }, 500);
    }, 4000);
  });
});

// Live billing calculator: used on employee/billing.php
function calcBillTotal() {
  const rows = document.querySelectorAll('.bill-item-row');
  let total = 0;
  rows.forEach(function (row) {
    const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
    const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
    const lineTotal = qty * price;
    const lineTotalEl = row.querySelector('.item-line-total');
    if (lineTotalEl) lineTotalEl.textContent = lineTotal.toFixed(2);
    total += lineTotal;
  });
  const totalEl = document.getElementById('bill-total-amount');
  if (totalEl) totalEl.value = total.toFixed(2);
  const totalDisplay = document.getElementById('bill-total-display');
  if (totalDisplay) totalDisplay.textContent = total.toFixed(2);
  updateDueAmount();
}

function updateDueAmount() {
  const total = parseFloat(document.getElementById('bill-total-amount')?.value) || 0;
  const paid = parseFloat(document.getElementById('bill-paid-amount')?.value) || 0;
  const dueEl = document.getElementById('bill-due-display');
  if (dueEl) dueEl.textContent = Math.max(total - paid, 0).toFixed(2);
}

// Add a new blank service row to the billing form
function addBillRow() {
  const wrap = document.getElementById('bill-items-wrap');
  if (!wrap) return;
  const row = document.createElement('div');
  row.className = 'bill-item-row';
  row.style.cssText = 'display:flex; gap:10px; margin-bottom:10px; align-items:center;';
  row.innerHTML = `
    <input type="text" name="item_name[]" class="form-control" placeholder="Item / Service name" style="flex:2;" required>
    <input type="number" name="item_qty[]" class="form-control item-qty" placeholder="Qty" value="1" min="1" style="flex:1;" oninput="calcBillTotal()">
    <input type="number" name="item_price[]" class="form-control item-price" placeholder="Unit Price" step="0.01" style="flex:1;" oninput="calcBillTotal()">
    <span style="flex:1;">Tk. <span class="item-line-total">0.00</span></span>
    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove(); calcBillTotal();">✕</button>
  `;
  wrap.appendChild(row);
}
