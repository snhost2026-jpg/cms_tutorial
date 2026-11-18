function updateRoutes(r) {
    _opts.routes.available = r.available || [];
    _opts.routes.assigned = r.assigned || [];
    search('available');
    search('assigned');
}

$('#btn-new').click(function () {
    var $this = $(this);
    var route = ($('#inp-route').val() || '').trim();
    if (route !== '') {
        $this.prop('disabled', true);
        $this.find('.spinner-icon').show();
        $this.find('.btn-text').css('opacity', 0.5);

        $.post($this.data('action'), {route: route}, function (r) {
            $('#inp-route').val('').focus();
            updateRoutes(r);
        }).fail(function (xhr) {
            alert('Failed to add route: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.responseText));
        }).always(function () {
            $this.prop('disabled', false);
            $this.find('.spinner-icon').hide();
            $this.find('.btn-text').css('opacity', 1);
        });
    } else {
        alert('Please enter a route string (e.g. /sales/lead/export).');
    }
    return false;
});

$('.btn-assign').click(function () {
    var $this = $(this);
    var target = $this.data('target');
    var actionUrl = $this.data('action');
    var routes = $('select.list[data-target="' + target + '"]').val();

    if (routes && routes.length) {
        $this.prop('disabled', true);
        $this.find('.spinner-icon').show();
        $this.find('.btn-text').css('opacity', 0.5);

        $.post(actionUrl, {routes: routes}, function (r) {
            updateRoutes(r);
        }).fail(function (xhr) {
            alert('Failed to update routes: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.responseText));
        }).always(function () {
            $this.prop('disabled', false);
            $this.find('.spinner-icon').hide();
            $this.find('.btn-text').css('opacity', 1);
        });
    } else {
        alert('Please select at least one route from the list first.');
    }
    return false;
});

$('#btn-refresh').click(function () {
    var $this = $(this);
    var $icon = $this.find('.refresh-icon');
    $this.prop('disabled', true);
    $icon.addClass('fa-spin');

    $.post($this.data('action'), function (r) {
        updateRoutes(r);
    }).fail(function (xhr) {
        alert('Failed to refresh route scan.');
    }).always(function () {
        $this.prop('disabled', false);
        $icon.removeClass('fa-spin');
    });
    return false;
});

$('.search[data-target]').on('input keyup', function () {
    search($(this).data('target'));
});

function search(target) {
    var $list = $('select.list[data-target="' + target + '"]');
    $list.empty();
    var q = ($('.search[data-target="' + target + '"]').val() || '').toLowerCase();
    var totalCount = 0;
    var visibleCount = 0;

    $.each(_opts.routes[target] || [], function () {
        var r = this;
        totalCount++;
        if (r.toLowerCase().indexOf(q) >= 0) {
            visibleCount++;
            $('<option>').text(r).val(r).appendTo($list);
        }
    });

    $('#' + target + '-count').text(visibleCount + (visibleCount !== totalCount ? ' of ' + totalCount : '') + ' items');
}

// Initial populate
search('available');
search('assigned');
