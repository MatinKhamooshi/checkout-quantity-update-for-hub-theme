(function($) {
    function addSpinnerButtons() {
        $('.quantity').each(function() {
            var $container = $(this);
            var $input = $container.find('input[type="number"]');
            // اگر دکمه‌های spinner قبلاً اضافه نشده‌اند، اضافه می‌کنیم.
            if ($input.length && !$container.find('.ui-spinner-button').length) {
                var $upButton = $('<a tabindex="-1" aria-hidden="true" class="ui-spinner-button ui-spinner-up ui-corner-tr"></a>');
                var $downButton = $('<a tabindex="-1" aria-hidden="true" class="ui-spinner-button ui-spinner-down ui-corner-br"></a>');
                $input.after($upButton, $downButton);
            }
            // به‌روزرسانی وضعیت دکمه کاهش بر اساس مقدار input
            var currentVal = parseInt($input.val(), 10) || 0;
            var $downBtn = $container.find('.ui-spinner-down');
            if (currentVal <= 1) {
                $downBtn.data('isTrash', true);
                $downBtn.addClass('remove-active');
            } else {
                $downBtn.data('isTrash', false);
                $downBtn.removeClass('remove-active');
            }
        });
    }
    
    // اجرای تابع در بارگذاری اولیه و پس از هر به‌روزرسانی AJAX
    $(document).ready(addSpinnerButtons);
    $(document).ajaxComplete(addSpinnerButtons);
    $(document.body).on('updated_checkout', function() {
        setTimeout(addSpinnerButtons, 500);
    });
    
    // رویداد کلیک برای دکمه افزایش
    $(document).on('click', '.ui-spinner-up', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('input[type="number"]');
        var currentVal = parseInt($input.val(), 10) || 0;
        var maxVal = parseInt($input.attr('max'), 10);
        var $downBtn = $(this).siblings('.ui-spinner-down');
        if (!isNaN(maxVal) && currentVal >= maxVal) return;
        $input.val(currentVal + 1).trigger('change');
        // اگر مقدار جدید بیشتر از 1 شد، دکمه کاهش حالت remove-active را از دست بدهد.
        if (currentVal + 1 > 1 && $downBtn.data('isTrash')) {
            $downBtn.data('isTrash', false);
            $downBtn.removeClass('remove-active');
        }
    });
    
    // رویداد کلیک برای دکمه کاهش
    $(document).on('click', '.ui-spinner-down', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('input[type="number"]');
        var currentVal = parseInt($input.val(), 10) || 0;
        var $downBtn = $(this);
        if (currentVal <= 1) {
            if ($downBtn.data('isTrash')) {
                // اگر مقدار 1 است و دکمه در حالت remove-active است، مقدار به 0 تنظیم شود.
                $input.val(0).trigger('change');
            } else {
                // در حالت اولیه، اگر مقدار 1 است، فقط کلاس remove-active اضافه شود.
                $downBtn.data('isTrash', true);
                $downBtn.addClass('remove-active');
            }
        } else {
            $input.val(currentVal - 1).trigger('change');
            if (currentVal - 1 <= 1) {
                $downBtn.data('isTrash', true);
                $downBtn.addClass('remove-active');
            }
        }
    });
})(jQuery);