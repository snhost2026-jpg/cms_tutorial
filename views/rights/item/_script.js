function updateItems(r) {
    _opts.items.available = r.available || {};
    _opts.items.assigned = r.assigned || {};
    search('available');
    search('assigned');
}

function updateUsers(r) {
    _opts.users = r;
    listUsers();
}

function listUsers() {
    var $container = $('#list-users');
    $container.empty();

    if (!_opts.users || !_opts.users.users || !_opts.users.users.length) {
        $container.html('<span class="text-muted small">No users currently assigned to this item.</span>');
        return;
    }

    $.each(_opts.users.users, function (idx, username) {
        $('<span class="badge bg-light text-dark border px-2 py-1">')
            .html('<i class="fa-solid fa-user me-1 text-primary"></i>' + username)
            .appendTo($container);
    });

    if (_opts.users.prev !== undefined || _opts.users.next !== undefined) {
        var $pagination = $('<div class="d-inline-flex gap-1 ms-2">');
        if (_opts.users.prev !== undefined) {
            $('<a href="#" class="btn btn-sm btn-outline-secondary py-0 px-2" data-target="prev">&laquo; Prev</a>').appendTo($pagination);
        }
        if (_opts.users.next !== undefined) {
            $('<a href="#" class="btn btn-sm btn-outline-secondary py-0 px-2" data-target="next">Next &raquo;</a>').appendTo($pagination);
        }
        $pagination.appendTo($container);
    }
}

$('#list-users').on('click', 'a[data-target]', function (e) {
    e.preventDefault();
    var target = $(this).data('target');
    var page = _opts.users[target];
    if (page !== undefined) {
        $.get(_opts.getUserUrl, {page: page}, function (r) {
            updateUsers(r);
        });
    }
    return false;
});

$('.btn-assign').click(function () {
    var $this = $(this);
    var target = $this.data('target');
    var actionUrl = $this.data('action');
    var items = $('select.list[data-target="' + target + '"]').val();

    if (items && items.length) {
        $this.prop('disabled', true);
        $this.find('.spinner-icon').show();
        $this.find('.btn-text').css('opacity', 0.5);

        $.post(actionUrl, {items: items}, function (r) {
            updateItems(r);
        }).fail(function (xhr) {
            alert('Failed to update: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.responseText));
        }).always(function () {
            $this.prop('disabled', false);
            $this.find('.spinner-icon').hide();
            $this.find('.btn-text').css('opacity', 1);
        });
    } else {
        alert('Please select at least one item from the ' + (target === 'available' ? 'Available' : 'Granted') + ' list first.');
    }
    return false;
});

$('.search[data-target]').on('input keyup', function () {
    search($(this).data('target'));
});

$('.filter-type').on('change', function () {
    search($(this).data('target'));
});

function search(target) {
    var $list = $('select.list[data-target="' + target + '"]');
    $list.empty();
    var q = ($('.search[data-target="' + target + '"]').val() || '').toLowerCase();
    var selectedType = $('.filter-type[data-target="' + target + '"]:checked').val() || '';

    var groups = {
        role: [$('<optgroup label="── System Roles ──">'), false, 0],
        permission: [$('<optgroup label="── Permissions ──">'), false, 0],
        route: [$('<optgroup label="── Specific Routes (/) ──">'), false, 0]
    };

    var totalCount = 0;
    var visibleCount = 0;

    $.each(_opts.items[target] || {}, function (name, group) {
        totalCount++;
        if (!groups[group]) {
            group = (name && name[0] === '/') ? 'route' : 'permission';
        }

        if (selectedType && group !== selectedType) {
            return;
        }

        if (name.toLowerCase().indexOf(q) >= 0) {
            visibleCount++;
            var $option = $('<option>').text(name).val(name);
            if (group === 'route') {
                $option.css('color', '#0d6efd');
            } else if (group === 'role') {
                $option.css('font-weight', 'bold');
            }
            $option.appendTo(groups[group][0]);
            groups[group][1] = true;
            groups[group][2]++;
        }
    });

    $.each(groups, function (k, val) {
        if (val[1]) {
            val[0].attr('label', val[0].attr('label') + ' (' + val[2] + ')');
            $list.append(val[0]);
        }
    });

    $('#' + target + '-count').text(visibleCount + (visibleCount !== totalCount ? ' of ' + totalCount : '') + ' items');
}

// Initial populate
listUsers();
search('available');
search('assigned');
