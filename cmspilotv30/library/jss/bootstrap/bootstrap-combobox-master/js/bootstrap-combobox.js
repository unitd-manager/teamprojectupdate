(function($) {
    $.widget("ui.combobox", {
        _create: function() {
            var input, self = this,
                select = this.element.hide(),
                name   = select.attr("name");
                selected = select.children(":selected"),
                value = selected.val() ? selected.text() : "",
                wrapper = $("<span>").addClass("ui-combobox").insertAfter(select);

            input = $("<input name="+name+">").appendTo(wrapper).val(value).addClass("ui-state-default").autocomplete({
                delay: 0,
                minLength: 0,
                source: function(request, response) {
                    var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                    response(select.children("option").map(function() {
                        var text = $(this).text();
                        if (this.value && (!request.term || matcher.test(text))) return {
                            label: text.replace(
                            new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + $.ui.autocomplete.escapeRegex(request.term) + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "$1"),
                            value: text,
                            option: this
                        };
                    }));
                },
                select: function(event, ui) {
                    ui.item.option.selected = true;
                    self._trigger("selected", event, {
                        item: ui.item.option
                    });
                    
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i"),
                            valid = false;
                        select.children("option").each(function() {
                            if ($(this).text().match(matcher)) {
                                this.selected = valid = true;
                                return false;
                            }
                        });
                        if (!valid) {
                            var parent = $(this).closest(".type-select.ym-fbox-select");
                            // remove invalid value, as it didn't match anything
                            $(this).val("");
                            var labelText = $("label", parent).html();
                            select.val("");
                            input.data("autocomplete").term = "";
                            //alert("Please select "+labelText);
                            //$(this).focus();
                            return false;
                        }
                    }
                }
            }).addClass("ui-widget ui-widget-content ui-corner-left");

            input.data("autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("item.autocomplete", item).append("<a>" + item.label + "</a>").appendTo(ul);
            };

            $("<button>").attr("tabIndex", -1).attr("title", "Show All Items").appendTo(wrapper).button({
                icons: {
                    primary: "ui-icon-triangle-1-s"
                },
                text: false
            }).removeClass("ui-corner-all").addClass("ui-corner-right ui-button-icon ui-combobox-button").click(function() {
                // close if already visible
                if (input.autocomplete("widget").is(":visible")) {
                    input.autocomplete("close");
                    return;
                }

                // work around a bug (likely same cause as #5265)
                $(this).blur();

                // pass empty string as value to search for, displaying all results
                input.autocomplete("search", "");
                input.focus();
            });
        },

        destroy: function() {
            this.wrapper.remove();
            this.element.show();
            $.Widget.prototype.destroy.call(this);
        }
    });
})(jQuery);

$(function() {
    $("#combobox").combobox({
        selected: function(event, ui) {
            $('#log').text('selected ' + $("#combobox").val());
        }
    });
    
    //$("#combobox").closest(".ui-widget").find("input, button").prop("disabled", true);
});