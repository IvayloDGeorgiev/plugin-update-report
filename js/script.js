jQuery(document).ready(function($) {
    //Adding array of data for the dates into the Form input
    $('[data-generate-pdf-form]').on('submit', function(ev){
        const form = $(ev.currentTarget);
        const fromValue = $('[name=from_value]').val();
        const toValue = $('[name=to_value]').val();
        $('[name=dateFrom]').attr('value', fromValue);
        $('[name=dateTo]').attr('value', toValue);
    })
});