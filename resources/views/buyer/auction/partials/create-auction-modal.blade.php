<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content ">

      <div class="modal-header bg-graident text-white">
        <h4 class="modal-title">{{ !empty($editId) ? 'Edit Auction' : 'Create Auction' }}</h4>
        <button type="button"
                class="btn-close {{ $rfqType === 'Scheduled' ? '' : 'empty-schedule-date' }}"
                data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="auction-modal-body-wrapper">
          <p><b>Note: </b>You can select a maximum of 20 vendors.</p>

          {{-- Inline error alert container (filled by JS when needed) --}}
          <div id="auctionErrorBlock" class="alert alert-danger d-none" role="alert"></div>

          <form id="auctionForm" method="POST" action="{{ $action }}" autocomplete="off">
            @csrf

            {{-- Vendors --}}
            <div class="form-group mb-3">
              <label for="selectvendor">Vendor <span class="text-danger">*</span></label>
              <select class="form-select selectvendor" name="vendor_ids[]" id="selectvendor" multiple required>
                @foreach($vendors as $v)
                  <option value="{{ $v['vendor_user_id'] }}"
                    {{ in_array((int)$v['vendor_user_id'], $selectedVendorIds ?? []) ? 'selected' : '' }}>
                    {{ $v['legal_name'] }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Date --}}
            <div class="form-group mb-3">
              <label for="auctionDate">Date <span class="text-danger">*</span></label>
              <input type="text" class="form-control auction-date-picker" name="auction_date" id="auctionDate" placeholder="dd/mm/yyyy" required value="{{ $prefill['auction_date'] ?? '' }}">

              <input type="hidden" name="rfq_no" id="rfqNo" value="{{ $rfqId }}">
              <input type="hidden" name="current_status" id="currentStatus" value="{{ $currentStatus }}">
              <input type="hidden" name="edit_id" id="edit_id" value="{{ $editId }}">
            </div>

            {{-- Time (30-min slots) --}}
            <div class="form-group mb-1">
              <label for="auctionTime">Time <span class="text-danger">*</span></label>
              <select class="form-control auction-time" name="auction_time" id="auctionTime"
                      data-current="{{ $prefill['auction_time'] ?? '' }}" required>
                <option value="">Select Time</option>
                {{-- options injected by JS --}}
              </select>

              <span id="timeError" class="small" style="color: red; display: none;">
                Please select a time greater than or equal to the current time.
              </span>
            </div>

            {{-- Products Table --}}
            <div class="table-responsive mt-3">
              <table class="table table-bordered">
                <thead>
                <tr>
                  <th>Product</th>
                  <th>Specifications</th>
                  <th>Size</th>
                  <th>Qty</th>
                  <th style="width:130px;">
                    Start Price (<span class="minBidCurrency-column">₹</span>)
                    <span class="text-danger">*</span>
                  </th>
                  <th style="width:120px;">Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($variants as $key => $val)
                  @php
                    $variantId    = $val['variant_grp_id'] ?? $key;
                    $rfqvariantId = $val['id'] ?? $key;
                    $prod         = $val['product_name'] ?? ($val['prod_name'] ?? '');
                    $spec         = $val['specification'] ?? '';
                    $size         = $val['size'] ?? '';
                    $qty          = (float)($val['quantity'] ?? 0);

                    $startPrefFromDB = isset($prefillVariantPrices) ? ($prefillVariantPrices[$rfqvariantId] ?? null) : null;
                    $startPref = $startPrefFromDB !== null
                        ? (float)$startPrefFromDB
                        : (isset($val['start_price']) ? (float)$val['start_price'] : null);

                    $shortProd = mb_strlen($prod) > 16 ? mb_substr($prod, 0, 16) : $prod;
                    $shortSpec = mb_strlen($spec) > 16 ? mb_substr($spec, 0, 16) : $spec;
                    $shortSize = mb_strlen($size) > 10 ? mb_substr($size, 0, 10) : $size;
                  @endphp

                  <tr>
                    {{-- Product --}}
                    <td style="text-align:left;">
                      {!! $shortProd !!}
                      @if(mb_strlen($prod) > 16)
                        <i title="{{ $prod }}" class="bi bi-info-circle-fill"></i>
                      @endif
                      <input type="hidden" name="variants[{{ $variantId }}][product_name]" value="{{ $prod }}">
                      <input type="hidden" name="variants[{{ $variantId }}][rfq_variant_id]" value="{{ $rfqvariantId }}">
                    </td>

                    {{-- Specification --}}
                    <td style="text-align:left;">
                      {!! $shortSpec !!}
                      @if(mb_strlen($spec) > 16)
                        <i title="{{ $spec }}" class="bi bi-info-circle-fill"></i>
                      @endif
                      <input type="hidden" name="variants[{{ $variantId }}][specification]" value="{{ $spec }}">
                    </td>

                    {{-- Size --}}
                    <td>
                      {!! $shortSize !!}
                      @if(mb_strlen($shortSize) > 10)
                        <i title="{{ $size }}" class="bi bi-info-circle-fill"></i>
                      @endif
                      <input type="hidden" name="variants[{{ $variantId }}][size]" value="{{ $size }}">
                    </td>

                    {{-- Qty --}}
                    <td class="variant-qty" data-qty="{{ $qty }}">
                      {{ rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.') }}
                      <input type="hidden" name="variants[{{ $variantId }}][quantity]" value="{{ $qty }}">
                    </td>

                    {{-- Start Price --}}
                    <td>
                      <input type="number"
                             class="form-control start-price"
                             name="variants[{{ $variantId }}][start_price]"
                             id="start_price_{{ $variantId }}"
                             placeholder="{{ $startPref !== null ? number_format($startPref,2,'.','') : 'Enter start price' }}"
                             value="{{ $startPref !== null ? number_format($startPref,2,'.','') : '' }}"
                             required
                             step="0.01"
                             min="0"
                             data-qty="{{ $qty }}">
                      <input type="hidden" name="variants[{{ $variantId }}][variant_grp_id]" value="{{ $variantId }}">
                    </td>

                    {{-- Row Total --}}
                    <td class="row-total" id="row_total_{{ $variantId }}">0.00</td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center text-muted">No products found</td></tr>
                @endforelse

                <tr>
                  <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                  <td><strong id="grandTotal">0.00</strong></td>
                </tr>
                </tbody>
              </table>
            </div>

            {{-- Currency + Min Bid Decrement --}}
            <div class="row g-3">
              <div class="col-md-3">
                <label for="minBidCurrency">Currency <span class="text-danger">*</span></label>
                <select class="form-select" id="minBidCurrency" name="min_bid_currency" required>
                  @foreach($currencies as $c)
                    @php
                      $name   = $c->currency_name;
                      $symbol = $c->currency_symbol;
                      $map    = ['₹'=>'INR', '$'=>'USD', 'रु'=>'NPR'];
                      $value  = $map[$symbol] ?? $symbol;
                      $dataSymbol = ($symbol === 'रु') ? 'NPR' : $symbol;
                      $isSel = ( ($prefill['min_bid_currency'] ?? '') === $value );
                    @endphp
                    <option value="{{ $value }}" data-symbol="{{ $dataSymbol }}" {{ $isSel ? 'selected' : '' }}>
                      {{ $name }} ({{ $symbol }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-9">
                <label for="minBidDecrement">Min Bid Decrement (%) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="minBidDecrement" name="min_bid_decrement"
                       placeholder="Enter minimum bid decrement" step="0.01" min="0.50" required
                       value="{{ isset($prefill['min_bid_decrement']) ? number_format($prefill['min_bid_decrement'], 2, '.', '') : '' }}"
                       onblur="if (parseFloat(this.value) < 0.50) this.value = ''; ">
              </div>
            </div>

            {{-- Auction Type (Normal vs Lot-wise) — robust prefill for edit --}}
            @php
              $pvCount = (is_countable($variants ?? []) ? count($variants) : 0);
              $resolvedAuctionType = (int) old('auction_type',
                                          $prefill['auction_type']
                                          ?? (($auction->auction_type ?? null) ?? 1));

              $isForcedLot = $pvCount >= 20;                 // force lot-wise at 20+
              $isChecked   = $isForcedLot ? true : ($resolvedAuctionType === 2);
            @endphp

            <div class="form-group col-md-12 mt-3">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="auction_type"
                  name="auction_type_value"
                  value="2"
                  {{ $isChecked ? 'checked' : '' }}
                  {{ $isForcedLot ? 'disabled' : '' }}
                >
                <label class="form-check-label" for="auction_type">
                  Schedule the Entire Auction Lot-wise
                </label>
              </div>

              @if($isForcedLot)
                {{-- Ensure value is posted even when checkbox is disabled --}}
                <input type="hidden" name="auction_type" value="2" id="auction_type_hidden">
                <small class="text-muted d-block mt-1">
                  You have {{ $pvCount }} variants. Lot-wise scheduling is enforced for 20 or more variants.
                </small>
              @else
                {{-- Hidden fallback; JS will swap 1/2 to mirror checkbox state on submit --}}
                <input type="hidden" name="auction_type" value="{{ $isChecked ? 2 : 1 }}" id="auction_type_hidden">
                <small class="text-muted d-block mt-1">
                  Toggle this to schedule and conduct the auction lot-wise for all selected products/variants.
                </small>
              @endif
            </div>

            <div class="row mt-3 align-items-center">
              <div class="col-12 text-end">
                <button type="submit" class="text-center btn-rfq-auction btn btn-primary btn-sm btn-rfq btn-rfq-primary">
                  Save
                </button>
              </div>
            </div>

          </form>
        </div> {{-- /auction-modal-body-wrapper --}}
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  $('.selectvendor').SumoSelect({
    selectAll: false,
    nativeOnDevice: [],
    maxHeight: 100,
    csvDispCount: 7,
    placeholder: 'Select Vendor'
  });
  var now = new Date();
  var dd = String(now.getDate()).padStart(2, '0');
  var mm = String(now.getMonth() + 1).padStart(2, '0');
  var yyyy = now.getFullYear();
  var today = dd + '-' + mm + '-' + yyyy;
  $('.auction-date-picker').datetimepicker({
    lang: 'en',
    timepicker: false,
    format: 'd/m/Y',
    formatDate: 'd-m-Y',
    minDate: today,
    scrollMonth: false,
    scrollInput: false,
    onShow: function () {
      this.setOptions({ minDate: today });
    },
  });
});
(function($){
  // ===== Number formatting (Indian) =====
  const nf = new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmt = v => nf.format(v ?? 0);
  const toNum = v => {
    if (typeof v === 'number') return v;
    if (!v) return 0;
    return parseFloat(String(v).replace(/,/g,'').trim()) || 0;
  };

  // ===== Build 30-min time slots =====
  function buildTimeOptions(){
    const sel = document.getElementById('auctionTime');
    if (!sel || sel.dataset.populated) return;
    sel.dataset.populated = '1';

    const current = (sel.getAttribute('data-current') || '').trim(); // "HH:mm:ss"
    const currentHHMM = current ? current.split(':').slice(0,2).join(':') : '';

    // Clear existing
    sel.innerHTML = '';

    // Placeholder first
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = 'Select Time';
    sel.appendChild(ph);

    for (let h = 0; h < 24; h++){
      for (const m of [0, 30]){
        const hh = String(h).padStart(2,'0');
        const mm = String(m).padStart(2,'0');
        const value = `${hh}:${mm}:00`;
        const hour12 = ((h + 11) % 12) + 1;
        const ampm = h < 12 ? 'AM' : 'PM';

        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = `${String(hour12).padStart(2,'0')}:${mm} ${ampm}`;
        if (current && (current === value || currentHHMM === `${hh}:${mm}`)) {
          opt.selected = true;
        }
        sel.appendChild(opt);
      }
    }

    // Initialize Sumo after options are set
    $('.auction-time').SumoSelect({
      selectAll: false,
      nativeOnDevice: [],
      maxHeight: 100,
      csvDispCount: 7,
      placeholder: 'Select Time'
    });
  }

  // ===== Time validator (>= now if today) =====
  function validateTime(){
    const dateStr = ($('#auctionDate').val() || '').trim(); // dd/mm/YYYY
    const timeStr = ($('#auctionTime').val() || '').trim(); // HH:mm:ss
    const $err = $('#timeError');
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

  // ===== Totals Calculation (jQuery) =====
  function getRowQty($inp){
    const $qtyTd = $inp.closest('tr').find('.variant-qty');
    let qty = $qtyTd.data('qty');
    if (qty === undefined || qty === null || qty === '') {
      qty = $qtyTd.text();
    }
    return toNum(qty);
  }

  function calcRowTotal($inp){
    const qty = getRowQty($inp);
    const raw = $inp.val();
    const fallback = $inp.attr('placeholder');
    const price = raw === '' ? toNum(fallback) : toNum(raw);
    const total = qty * price;

    $inp.closest('tr').find('.row-total').text(fmt(total));
    return total;
  }

  function updateGrandTotal($scope){
    let sum = 0;
    ($scope || $(document)).find('.row-total').each(function(){
      sum += toNum($(this).text());
    });
    $('#grandTotal').text(fmt(sum));
  }

  function initTotals($root){
    $root.find('.start-price').each(function(){ calcRowTotal($(this)); });
    updateGrandTotal($root);
  }

  // ===== Delegated Events =====
  $(document).on('input keyup change', '.start-price', function(){
    const $inp = $(this);
    calcRowTotal($inp);
    updateGrandTotal($inp.closest('table'));
  });

  $(document).on('blur', '.start-price', function(){
    const $inp = $(this);
    const v = $inp.val();
    const val = v === '' ? toNum($inp.attr('placeholder')) : toNum(v);
    $inp.val(val ? val.toFixed(2) : '');
    calcRowTotal($inp);
    updateGrandTotal($inp.closest('table'));
  });

  // Vendor cap 20
  $(document).on('change', '#selectvendor', function(){
    const sel = this;
    const count = Array.from(sel.options).filter(o => o.selected).length;
    if (count > 20){
      sel.options[sel.selectedIndex].selected = false;
      alert('You can select a maximum of 20 vendors.');
    }
  });

  // Currency symbol in header
  $(document).on('change', '#minBidCurrency', function(){
    const sym = $(this).find('option:selected').data('symbol') || this.value;
    $('.minBidCurrency-column').text(sym);
  });

  // Date/time validation
  $(document).on('change', '#auctionDate, #auctionTime', validateTime);

  // ===== Init when modal opens =====
  $(document).on('shown.bs.modal', '#{{ $modalId }}', function(){
    buildTimeOptions();
    validateTime();

    const $root = $('#{{ $modalId }}');
    initTotals($root);

    const $cur = $('#minBidCurrency');
    if ($cur.length) {
      const sym = $cur.find('option:selected').data('symbol') || $cur.val();
      $('.minBidCurrency-column').text(sym);
    }
  });

  // If modal already visible (edge case during hot swaps)
  if ($('#{{ $modalId }}').hasClass('show')) {
    initTotals($('#{{ $modalId }}'));
  }

})(jQuery);
</script>

<script>
(function ($) {
  // --- Toastr global options (allow HTML for bold tags from backend) ---
  if (window.toastr) {
    toastr.options = Object.assign({}, toastr.options, {
      escapeHtml: false,
      closeButton: true,
      progressBar: true,
      timeOut: 8000
    });
  }

  // Helper: ensure time has seconds (:00)
  function normalizeTime(val) {
    if (!val) return val;
    const parts = val.split(':');
    if (parts.length === 2) return parts[0] + ':' + parts[1] + ':00';
    if (parts.length === 3) return parts[0] + ':' + parts[1] + ':00';
    return val;
  }

  // Parse JSON safely (from responseText fallbacks)
  function safeParseJSON(txt) {
    try { return JSON.parse(txt); } catch (_e) { return null; }
  }

  // Build an HTML UL from an array of messages
  function buildListHTML(arr) {
    return `<ul style="margin:0; padding-left:18px;">${arr.map(s => `<li>${s}</li>`).join('')}</ul>`;
  }

  // Centralized error renderer
  function showErrors(errs, { toastDelay = 200 } = {}){
    const block = document.getElementById('auctionErrorBlock');

    // Normalize to array
    let list = [];
    if (Array.isArray(errs)) {
      list = errs;
    } else if (errs && typeof errs === 'object') {
      if (Array.isArray(errs.messages)) {
        list = errs.messages;
      } else {
        Object.values(errs).forEach(v => {
          if (Array.isArray(v)) list.push(...v);
          else if (typeof v === 'string') list.push(v);
        });
      }
    } else if (typeof errs === 'string') {
      list = [errs];
    }

    if (!list.length) list = ['An error occurred.'];

    // 1) separate toasts
    if (window.toastr) {
      list.forEach((msg, i) => setTimeout(() => toastr.error(msg), i * toastDelay));
    }

    // 2) inline UL for persistence
    if (block) {
      const html = `<ul style="margin:0; padding-left:18px;">${list.map(s => `<li>${s}</li>`).join('')}</ul>`;
      block.classList.remove('d-none');
      block.innerHTML = html;
    }
  }

  // Validate time >= now if same day (uses your existing #timeError)
  function validateAuctionDateTime() {
    $('#auctionDate, #auctionTime').trigger('change');
    return $('#timeError').is(':visible') === false;
  }

  // --- Sync lot-wise toggle to hidden field so value always posts (only when not disabled) ---
  function syncAuctionTypeToggle() {
    var $toggle = $('#auction_type');
    var $hidden = $('#auction_type_hidden');
    if (!$toggle.length || !$hidden.length) return;

    if ($toggle.is(':disabled')) {
      // Forced lot-wise; hidden already set to 2
      $hidden.val('2');
      return;
    }
    $hidden.val($toggle.is(':checked') ? '2' : '1');
  }
  $(document).on('change', '#auction_type', syncAuctionTypeToggle);
  $(document).on('shown.bs.modal', '#{{ $modalId }}', function(){ syncAuctionTypeToggle(); });
  if ($('#{{ $modalId }}').hasClass('show')) { syncAuctionTypeToggle(); }

  // Client-side form validation
  function validateForm() {
    const vendors = $('#selectvendor').val();
    const auctionDate = $('#auctionDate').val();
    const auctionTime = $('#auctionTime').val();
    const minBidDecrement = parseFloat($('#minBidDecrement').val());
    const rfqNo = $('#rfqNo').val();

    $('#auctionErrorBlock').addClass('d-none').empty();

    if (!vendors || !vendors.length || !auctionDate || !auctionTime || !rfqNo || isNaN(minBidDecrement)) {
      showErrors(['All fields are required.']);
      return false;
    }
    if (vendors.length > 20) {
      showErrors(['You can select a maximum of 20 vendors.']);
      return false;
    }
    if (minBidDecrement <= 0) {
      showErrors(['Minimum bid decrement must be a positive number.']);
      return false;
    } else if (minBidDecrement < 0.50) {
      showErrors(['Minimum bid decrement must be at least 0.50%.']);
      return false;
    } else if (minBidDecrement > 99) {
      showErrors(['Minimum bid decrement must not be greater than 99%.']);
      return false;
    }

    const rows = $('input[name^="variants"][name$="[variant_grp_id]"]');
    if (!rows.length) {
      showErrors(['Please add at least one product variant.']);
      return false;
    }

    let ok = true;
    $('input[name^="variants"][name$="[start_price]"]').each(function () {
      const val = parseFloat($(this).val() || $(this).attr('placeholder') || '0');
      if (isNaN(val) || val <= 0) { ok = false; return false; }
    });
    if (!ok) {
      showErrors(['Start price for all products must be a positive number.']);
      return false;
    }

    if (!validateAuctionDateTime()) {
      showErrors(['Please select a time greater than or equal to the current time.']);
      return false;
    }

    return true;
  }

  // Submit via AJAX (Laravel 12)
  $(document).on('click', '.btn-rfq-auction', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $form = $('#auctionForm');

    const t = $('#auctionTime').val();
    if (t) $('#auctionTime').val(normalizeTime(t));

    // Ensure auction_type is synced just before submit
    syncAuctionTypeToggle();

    if (!validateForm()) return;

    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('Saving...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
      success: function (res) {
        // res may be object or JSON string
        const result = (typeof res === 'string') ? (safeParseJSON(res) || {}) : (res || {});

        // Success contract
        if (result.status === 'success' || result.success === true) {
          if (window.toastr) toastr.success(result.message || 'Auction saved successfully.');
          if (result.redirect) { window.location.href = result.redirect; }
          else { location.reload(); }
          return;
        }

        // Explicit error contract
        if (result.status === 'error') {
          if (result.messages || result.errors) {
            showErrors(result.messages || result.errors);
          } else if (result.message) {
            showErrors(result.message);
          } else {
            showErrors(['An error occurred.']);
          }
          return;
        }

        // Unknown but includes messages/errors/message
        if (result.messages || result.errors) {
          showErrors(result.messages || result.errors);
        } else if (result.message) {
          showErrors(result.message);
        } else {
          if (window.toastr) toastr.error('Unexpected response format.');
        }
      },
      error: function (xhr) {
        // Prefer JSON object
        if (xhr.responseJSON) {
          const rj = xhr.responseJSON;
          if (rj.messages || rj.errors) return showErrors(rj.messages || rj.errors);
          if (rj.message) return showErrors(rj.message);
        }

        // Try responseText as JSON
        const parsed = safeParseJSON(xhr.responseText || '');
        if (parsed) {
          if (parsed.messages || parsed.errors) return showErrors(parsed.messages || parsed.errors);
          if (parsed.message) return showErrors(parsed.message);
        }

        // Fallback
        showErrors(['An error occurred while processing your request.']);
      },
      complete: function () {
        $btn.prop('disabled', false).html(originalHtml);
      }
    });
  });

})(jQuery);
</script>
