$(document).ready(function() {
    $('.js-copy-entry').on('click', function () {
        const id = $(this).data('entry-id');
        const $textarea = $('#copy-entry-' + id);

        if (!$textarea.length) {
            alert('Brak danych do skopiowania');
            return;
        }

        $textarea.removeClass('d-none');
        $textarea[0].select();
        $textarea[0].setSelectionRange(0, 99999);

        try {
            document.execCommand('copy');

            const $btn = $(this);
            const originalText = $btn.text();
            $btn.text('Skopiowano');

            setTimeout(function () {
                $btn.text(originalText);
            }, 1200);

        } catch (e) {
            alert('Nie udało się skopiować');
        }

        $textarea.addClass('d-none');
        window.getSelection().removeAllRanges();
    });
});