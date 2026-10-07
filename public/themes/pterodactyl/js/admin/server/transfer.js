$(document).ready(function () {
    const $modal = $('#transferServerModal');

    $('#pNodeId').select2({
        placeholder: 'Select a Node',
        dropdownParent: $modal,
    }).change();

    if (typeof Pterodactyl !== 'undefined' && Array.isArray(Pterodactyl.nodeData)) {
        $('#pNodeId option').each(function () {
            const val = $(this).val();
            const found = Pterodactyl.nodeData.find((n) => n.id == val);
            if (found && found.server_limit !== null && found.servers_count >= found.server_limit) {
                $(this).prop('disabled', true);
                $(this).text($(this).text() + ' (limit reached)');
            }
        });
    }

    $('#pAllocation').select2({
        placeholder: 'Select a Default Allocation',
        dropdownParent: $modal,
    });

    $('#pAllocationAdditional').select2({
        placeholder: 'Select Additional Allocations',
        dropdownParent: $modal,
    });
});

$('#pNodeId').on('change', function () {
    let currentNode = $(this).val();

    $.each(Pterodactyl.nodeData, function (i, v) {
        if (v.id == currentNode) {
            $('#pAllocation').html('').select2({
                data: v.allocations,
                placeholder: 'Select a Default Allocation',
            });

            updateAdditionalAllocations();
        }
    });
});

$('#pAllocation').on('change', function () {
    updateAdditionalAllocations();
});

function updateAdditionalAllocations() {
    let currentAllocation = $('#pAllocation').val();
    let currentNode = $('#pNodeId').val();

    $.each(Pterodactyl.nodeData, function (i, v) {
        if (v.id == currentNode) {
            let allocations = [];

            for (let i = 0; i < v.allocations.length; i++) {
                const allocation = v.allocations[i];

                if (allocation.id != currentAllocation) {
                    allocations.push(allocation);
                }
            }

            $('#pAllocationAdditional').html('').select2({
                data: allocations,
                placeholder: 'Select Additional Allocations',
            });
        }
    });
}
