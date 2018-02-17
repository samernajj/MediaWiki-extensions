$( ".deletebtn" ).click(function() {
    if (confirm("Are you sure?")) {
        parent_class = $(this).parent();
        action_input = parent_class.find('#action');
        action_input.val('delete');
        parent_class.submit();
    }
    return false;
});