
$(document).ready(function () {
    const specialCharRegex = /[!@#$%^&*()?":{}|<>[\]\\\/'`;+=~`_—✓©®™•¡¿§¤€£¥₩₹†‡‰¶∆∑∞µΩ≈≠≤≥÷±√°¢¡¬‽№☯☮☢☣♻⚡⚠✔️🔒🎉😊💡🌍🚀📦🧩🛠️🐍🔥💾📁🖥️⌨️🔧🔍]/g;

    // Handle typing (input event is better than keypress)
    $(document).on("input", "input:not([readonly]):not([disabled]):not([type='file'])", function () {//pingki
        const input = $(this);
        let val = input.val();
        const max = parseInt(input.attr('maxlength'), 10);

        // If not allowed, strip special characters
        if (!input.hasClass("specialCharacterAllowed")) {
            val = val.replace(specialCharRegex, '');
        }

        // Apply maxlength trimming
        if (!isNaN(max) && val.length > max) {
            val = val.substring(0, max);
        }

        input.val(val);
    });

    // Paste event (prevent default paste and sanitize)
    $(document).on('paste', 'input:not([readonly]):not([disabled])', function (e) {
        e.preventDefault();

        const input = $(this);
        const el = this;
        let pastedText = (e.originalEvent || e).clipboardData.getData('text');

        // If not allowed, remove special characters
        if (!input.hasClass("specialCharacterAllowed")) {
            pastedText = pastedText.replace(specialCharRegex, "");
        }

        const max = parseInt(input.attr('maxlength'), 10);
        const start = el.selectionStart;
        const end = el.selectionEnd;
        const currentVal = input.val();

        let newVal = currentVal.slice(0, start) + pastedText + currentVal.slice(end);

        if (!isNaN(max) && newVal.length > max) {
            newVal = newVal.substring(0, max);
        }

        input.val(newVal);
        const caretPos = Math.min(start + pastedText.length, newVal.length);
        el.setSelectionRange(caretPos, caretPos);
    });

    //===numeric only value
    $(document).on('keypress', '.smt_numeric_only', function (e) {
        let charCode = e.which || e.keyCode;

        // Prevent "e", spaces, and non-numeric characters except "."
        if (charCode === 69 || charCode === 101 || charCode === 32) { // "E", "e", and space
            return false;
        }

        let character = String.fromCharCode(charCode);
        let inputValue = $(this).val();

        // Allow only one decimal point
        if (character === '.' && inputValue.includes('.')) {
            return false;
        }

        // Allow numbers and one decimal point
        return /[0-9.]$/.test(character);
    }).on('paste', function (e) {
        let pastedData = e.originalEvent.clipboardData.getData('text');
        let numericValue = pastedData.replace(/[^0-9.]/g, '');

        // Prevent multiple decimals
        let decimalCount = (numericValue.match(/\./g) || []).length;
        if (decimalCount > 1) {
            numericValue = numericValue.replace(/\.(?=.*\.)/g, ''); // Remove extra decimal points
        }

        $(this).val(numericValue);
        e.preventDefault();
    }).on('blur', '.smt_numeric_only', function (e) {
        let value = e.target.value;

        // Remove extra decimal points or invalid characters
        value = value.replace(/[^0-9.]/g, '');

        // Ensure only two decimal places
        if (value.includes('.')) {
            let [integerPart, decimalPart] = value.split('.');
            decimalPart = decimalPart.slice(0, 2); // Keep only two decimals
            value = integerPart + (decimalPart ? '.' + decimalPart : '');
        }

        e.target.value = value;
    });
    //===numeric only value
});
// resources/js/app.js

// CSRF token error handling
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    const errorText = jqxhr.responseText || "";

    if (errorText.includes("CSRF token mismatch")) {
        toastr.error("Session expired. Reloading...");
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
});
