(function ($) {
  // ===== Helpers =====
  const nf = new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmt = v => nf.format(v ?? 0);
  const toNum = v => {
    if (typeof v === 'number') return v;
    if (v === null || v === undefined) return 0;
    return parseFloat(String(v).replace(/,/g, '').trim()) || 0;
  };

  // ===== Build 30-min time slots =====
  function buildTimeOptions(modal) {
    const sel = modal.find('#auctionTime')[0];
    if (!sel || sel.dataset.populated) return;
    sel.dataset.populated = '1';
    for (let h = 0; h < 24; h++) {
      for (const m of [0, 30]) {
        const hh = String(h).padStart(2, '0');
        const mm = String(m).padStart(2, '0');
        const opt = document.createElement('option');
        opt.value = `${hh}:${mm}:00`;
        const hour12 = ((h + 11) % 12) + 1;
        const ampm = h < 12 ? 'AM' : 'PM';
        opt.textContent = `${String(hour12).padStart(2, '0')}:${mm} ${ampm}`;
        sel.appendChild(opt);
      }
    }
  }

  // ===== Time validator (>= now if today) =====
  function validateTime(modal) {
    const dateStr = (modal.find('#auctionDate').val() || '').trim(); // dd/mm/YYYY
    const timeStr = (modal.find('#auctionTime').val() || '').trim(); // HH:mm:ss
    const $err = modal.find('#timeError');
    if (!dateStr || !timeStr) { $err.hide(); return; }
    const [d, m, y] = dateStr.split('/').map(Number);
    const [H, i, s] = timeStr.split(':').map(Number);
    const selected = new Date(y, (m - 1), d, H, i, s || 0);
    const now = new Date();
    const sameDay = selected.getFullYear() === now.getFullYear()
      && selected.getMonth() === now.getMonth()
      && selected.getDate() === now.getDate();
    $err.toggle(sameDay && selected < now);
  }

  // ===== Totals Calculation =====
  function getRowQty($inp) {
    const $qtyTd = $inp.closest('tr').find('.variant-qty');
    let qty = $qtyTd.data('qty');
    if (qty === undefined || qty === null || qty === '') {
      qty = $qtyTd.text();
    }
    return toNum(qty);
  }

  function calcRowTotal($inp) {
    const qty = getRowQty($inp);
    const raw = $inp.val();
    const fallback = $inp.attr('placeholder'); // allows prefills via placeholder
    const price = raw === '' ? toNum(fallback) : toNum(raw);
    const total = qty * price;

    $inp.closest('tr').find('.row-total').text(fmt(total));
    return total;
  }

  function updateGrandTotal(scope) {
    let sum = 0;
    scope.find('.row-total').each(function () {
      sum += toNum($(this).text());
    });
    scope.find('#grandTotal').text(fmt(sum));
  }

  function initTotals(modal) {
    modal.find('.start-price').each(function () { calcRowTotal($(this)); });
    updateGrandTotal(modal);
  }

  function initCurrencySymbol(modal) {
    const $cur = modal.find('#minBidCurrency');
    if ($cur.length) {
      const sym = $cur.find('option:selected').data('symbol') || $cur.val();
      modal.find('.minBidCurrency-column').text(sym);
    }
  }

  // ===== Event bindings (delegated to document for dynamic modal content) =====
  // Price changed -> live update
  $(document).on('input keyup change', '.auction-create-modal .start-price', function () {
    const $inp = $(this);
    const modal = $inp.closest('.auction-create-modal');
    calcRowTotal($inp);
    updateGrandTotal(modal);
  });

  // Blur -> normalize to 2 decimals
  $(document).on('blur', '.auction-create-modal .start-price', function () {
    const $inp = $(this);
    const v = $inp.val();
    const val = v === '' ? toNum($inp.attr('placeholder')) : toNum(v);
    $inp.val(val ? val.toFixed(2) : '');
    const modal = $inp.closest('.auction-create-modal');
    calcRowTotal($inp);
    updateGrandTotal(modal);
  });

  // Vendor cap 20
  $(document).on('change', '.auction-create-modal #selectvendor', function () {
    const sel = this;
    const count = Array.from(sel.options).filter(o => o.selected).length;
    if (count > 20) {
      sel.options[sel.selectedIndex].selected = false;
      alert('You can select a maximum of 20 vendors.');
    }
  });

  // Currency symbol in header
  $(document).on('change', '.auction-create-modal #minBidCurrency', function () {
    const modal = $(this).closest('.auction-create-modal');
    const sym = $(this).find('option:selected').data('symbol') || this.value;
    modal.find('.minBidCurrency-column').text(sym);
  });

  // Date/time validation
  $(document).on('change', '.auction-create-modal #auctionDate, .auction-create-modal #auctionTime', function () {
    validateTime($(this).closest('.auction-create-modal'));
  });

  // ===== Initialize when modal opens =====
  $(document).on('shown.bs.modal', '.auction-create-modal', function () {
    const modal = $(this);
    buildTimeOptions(modal);
    validateTime(modal);
    initTotals(modal);
    initCurrencySymbol(modal);
  });

  // If already visible on load (edge case during hot swap)
  $(function () {
    $('.auction-create-modal.show').each(function () {
      const modal = $(this);
      buildTimeOptions(modal);
      validateTime(modal);
      initTotals(modal);
      initCurrencySymbol(modal);
    });
  });

})(jQuery);
