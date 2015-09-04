/*
Color Input for jQuery
Copyright (c) 2008 Anthony Johnston
http://www.antix.co.uk
http://code.google.com/p/jquery-colorinput/
        
version 0.9.6
*/

/// <reference path="http://code.jquery.com/jquery-latest.min.js" />

(function($) {

    $.fn.colorInput = function(options) {
        /// <summary>Convert a input elements into a colour picker<summary>
        /// <param name="options">options (optional)</param>
        options = jQuery.extend($.colorInput.defaults, options);

        this.each(function() {
            $(this).data("colorControl", new $.colorInput(this, options));
        });

        return this;
    }
    $.fn.colorSelected = function(handler) {
        /// <summary>Custom event for colour selection<summary>
        /// <param name="handler">handler</param>

        if (handler) {
            this.bind("colorSelected", handler);

            return this;
        } else {
            this.trigger("colorSelected");
        }
    }
    $.fn.colorAccept = function(handler) {
        /// <summary>Custom event for accepting a color selection<summary>
        /// <param name="handler">handler</param>

        if (handler) {
            this.bind("colorAccept", handler);

            return this;
        } else {
            this.trigger("colorAccept");
        }
    }
    $.fn.colorCancel = function(handler) {
        /// <summary>Custom event for cancelling a colour selection<summary>
        /// <param name="handler">handler</param>

        if (handler) {
            this.bind("colorCancel", handler);

            return this;
        } else {
            this.trigger("colorCancel");
        }
    }

    $.colorInput = function(input, options) {
        /// <summary>Create a color picker for the input passed</summary>
        /// <param name="$input">an input element (required)</param>
        /// <param name="options">options (optional)</param>

        var $input = $(input);
        // check not already colorInput'ed, if so return
        if ($input.data("colorControl") != null) { return this; }

        var control = this,
            saturationBrightnessSize = options.cells * options.cellSize,
            totalWidth = options.cells * options.cellSize + options.hueWidth;

        // add a button to show current color and show popup
        var buttonString = "<button ".concat("class='colorButton' style='width:", options.hueWidth, "px;height:", options.hueWidth, "px;margin:0;padding:0;border:none 0;line-height:", options.hueWidth - 2, "px'>&nbsp;</button>");
        
        var $button = $(buttonString),
            $buttonContainer, $buttonAccept, $buttonCancel;
        $input.after($button);
        $button.click(function(e) {
            if ($.colorInput.current == control) { accept() }
            else {
                if ($.colorInput.current) { $.colorInput.current.cancel() }
                else { show(); }
            }

            e.stopPropagation();
            return false;
        });

        // check for dropdown
        var $dropdown = $("#".concat(options.dropdownId));
        if ($dropdown.length == 1) {
            if (options.acceptCancelButtons) {
                // grab button references
                $buttonContainer = $dropdown.find(".colorButtons");
                $buttonAccept = $buttonContainer.find(":eq(0)");
                $buttonCancel = $buttonContainer.find(":eq(1)");
            }
        } else {
            // create and add to document
            $dropdown =
                $("<div ".concat("id='", options.dropdownId, "' class='colorDropdown' style='display:none;position:absolute;overflow:visible;height:0;margin:0;padding:0;cursor:pointer'></div>"))
                    .append(
                        $("<div ".concat("style='float:left;width:", saturationBrightnessSize, "px;height:", saturationBrightnessSize, "px;margin:0;padding:0'></div>"))
                            .append(getHueMap(options))
                            .append(getSaturationBrightnessMap(options)));

            if (options.acceptCancelButtons) {
                // setup accept and cancel buttons
                $buttonAccept = $(buttonString)
                    .text(options.textAccept)
                    .css({ width: Math.round(saturationBrightnessSize / 2) });
                $buttonCancel = $(buttonString)
                    .text(options.textCancel)
                    .css({ width: Math.round(saturationBrightnessSize / 2) });
                
                $buttonContainer = $("<div ".concat("class='colorButtons' style='position:absolute;z-index:0;left:0;width=", saturationBrightnessSize, "px;height:", options.hueWidth, "px'></div>"))
                    .append($buttonAccept)
                    .append($buttonCancel);
                
                $dropdown.prepend($buttonContainer);
            }

            $(document.body).append($dropdown);
        }

        var 
            $sb = $dropdown.find(".saturationBrightnessMap"),
            $h = $dropdown.find(".hueMap"),
            value = new $.colorInput.color(), valueHex, originalValueHex,
            shown = false;

        if (options.hideInput) { $input.hide(); }

        // private functions
        var drawTimeoutId;
        var draw = function(delay) {
            /// <summary>Draw current color variations of saturation and brightness</summary>

            if (drawTimeoutId) {
                // clear the timeout
                clearTimeout(drawTimeoutId);
                drawTimeoutId = null;
            }
            if (delay) {
                // delay the draw
                drawTimeoutId = setTimeout(draw, delay);
                return;
            }

            // draw hues, to offset of current color
            var h = value.hue * saturationBrightnessSize - Math.round(value.hue * saturationBrightnessSize);
            var color = new $.colorInput.color(0, 1, 1);
            showHueSelected(null);
            $h.find("div").each(function() {
                color.hue = h / saturationBrightnessSize;
                this.hue = color.hue;
                this.color = color.toHex();
                this.style.backgroundColor = this.color;

                if (this.hue == value.hue) { showHueSelected(this); }

                h++;
            });

            // draw saturation and brightness variants to offsets
            var s = 0,
                b = 0;
            color = new $.colorInput.color(value.hue, 0, 0);
            showSaturationBrightnessSelected(null);
            $sb.find("div").each(function() {
                color.saturation = 1 - s / (options.cells - 1);
                color.brightness = 1 - b / (options.cells - 1);
                this.saturation = color.saturation;
                this.brightness = color.brightness;
                this.color = color.toHex();
                this.style.backgroundColor = this.color;

                if (this.color == valueHex) { showSaturationBrightnessSelected(this); }

                s++;
                if (s == options.cells) {
                    s = 0;
                    b++;
                }
            });
        }
        var selectColor = function() {
            /// <summary>selects the color from the input</summary>

            var val = $input.find('input[type=text]').val();
            if (val != valueHex) {
                value.fromHex(val);
                setValueHex(val, shown);
            }
        }
        var setValueHex = function(val, doDraw) {
            /// <summary>set the valueHex and button colour</summary>

            valueHex = val;
            $input.find('input[type=text]').val(val);

            control.isChanged = originalValueHex && val != originalValueHex;

            $button.css({
                backgroundColor: val.length > 0 ? val : '#000000',
                color: value.brightnessContrast().toHex()
            });
            if (options.acceptCancelButtons) {
                $buttonAccept.css({
                    backgroundColor: val,
                    color: value.brightnessContrast().toHex()
                });
            }

            if (doDraw) { draw(options.hoverSelect ? 50 : 0) }
        }
        var accept = function() {
            /// <summary>hide the dropdown, keep the current value</summary>

            if (originalValueHex != valueHex) { $input.change(); }
            originalValueHex = null;
            hide();
        }
        var cancel = function() {
            /// <summary>hide the dropdown, revert to original value</summary>

            if (control.isChanged) {
                $input.find('input[type=text]').val(originalValueHex);
                selectColor();
                $input.colorSelected();
            }
            originalValueHex = null;
            hide();
        }
        var hide = function() {
            /// <summary>hide the dropdown</summary>

            // unbind events
            $.each(options.cancelOnClick, function() {
                $(this).unbind("click.color");
            });
            $h.find("div")
                .unbind("mousedown")
                .unbind("mousemove")
                .unbind("mouseup")
                .unbind("click");

            $sb.find("div")
                .unbind("mousedown")
                .unbind("mousemove")
                .unbind("mouseup")
                .unbind("click");

            $sb.unbind("dblclick");

            $dropdown.unbind("click");
            if (options.acceptCancelButtons) {
                $buttonContainer.animate(
                    { marginTop: 0 },
                    { duration: 125 }
                );
                $buttonAccept.unbind("click");
                $buttonCancel.unbind("click");
            }
            $h.animate(
                { marginLeft: saturationBrightnessSize - options.hueWidth },
                {
                    duration: 125,
                    complete: function() {
                        $h.css({ display: "none" });
                        $dropdown.animate(
                            {
                                height: 0,
                                width: 0
                            },
                            {
                                duration: 250,
                                complete: function() {
                                    $dropdown.css({ display: "none" });
                                }
                            }
                        );
                    }
                }
            );

            $.colorInput.current = null;
            control.isChanged = false;
            shown = false;
        }
        var show = function() {
            /// <summary>show the dropdown</summary>

            originalValueHex = valueHex;
            if (options.acceptCancelButtons) {
                $buttonAccept.add($buttonCancel).css({
                    backgroundColor: valueHex,
                    color: value.brightnessContrast().toHex()
                });
            }
            draw();

            // bind events
            $.each(options.cancelOnClick, function() {
                $(this).bind("click.color", cancel);
            });
            if (options.hoverSelect) {
                $h.find("div")
                    .mousemove(function(e) {
                        setHue(this);
                        $input.colorSelected();
                    });
                $sb.find("div")
                    .mousemove(function(e) {
                        setSaturationBrightness(this);
                        $input.colorSelected();
                    });
                $sb.dblclick(function(e) {
                    accept();

                    e.stopPropagation();
                    return false;
                });

                $sb.click(function(e) {
                    accept();

                    e.stopPropagation();
                    return false;
                });
            } else {
                $h.find("div")
                    .mousedown(function(e) { return mousedown(e, $h); })
                    .mousemove(function(e) {
                        if (mousemove(e, $h)) {
                            setHue(this);
                            $input.colorSelected();
                        }
                    })
                    .mouseup(function(e) { mouseup(e, $h); })
                    .click(function(e) {
                        setHue(this);
                        $input.colorSelected();
                    });
                $sb.find("div")
                    .mousedown(function(e) { return mousedown(e, $sb); })
                    .mousemove(function(e) {
                        if (mousemove(e, $sb)) {
                            setSaturationBrightness(this);
                            $input.colorSelected();
                        }
                    })
                    .mouseup(function(e) { mouseup(e, $sb); })
                    .click(function(e) {
                        setSaturationBrightness(this);
                        $input.colorSelected();
                    });
                $sb.dblclick(function(e) {
                    accept();

                    e.stopPropagation();
                    return false;
                });
            }

            $dropdown
                .click(function(e) {
                    e.stopPropagation();
                    return false;
                });

            // show animation
            var offset = $button.offset();
            $dropdown
                .css({
                    display: "",
                    top: offset.top + $button.height() + "px",
                    left: offset.left + (options.showLeft ? -saturationBrightnessSize : 0) + "px"
                })
                .animate(
                    {
                        height: saturationBrightnessSize,
                        width: saturationBrightnessSize
                    },
                    {
                        duration: 500,
                        complete: function() {
                            $h
                                .css({ display: "" })
                                .animate(
                                    { marginLeft: saturationBrightnessSize },
                                    { duration: 250 }
                                );
                            if (options.acceptCancelButtons) {
                                $buttonContainer.animate(
                                    { marginTop: -options.hueWidth },
                                    { duration: 250 }
                                );
                                $buttonAccept.click(function(e) {
                                    accept();
                                    e.stopPropagation();
                                    return false;
                                });
                                $buttonCancel.click(function(e) {
                                    cancel();
                                    e.stopPropagation();
                                    return false;
                                });
                            }
                        }
                    }
                );

            $.colorInput.current = control;
            shown = true;
        }

        if ($.browser.msie) { // don't allow text selection
            $dropdown.attr('unselectable', 'on');
            $dropdown.find("div").attr('unselectable', 'on');
        }

        // handle dragging on/off
        var mousedown = function(e, $item) {
            if ($item.data("drag")) { mouseup(e, $item); }
            $item.data("drag", true);
            return false;
        }
        var mouseup = function(e, $item) {
            $item.data("drag", false);
        }
        var mousemove = function(e, $item) {
            if (!$item.data("drag")) { return false; }
            if ($.browser.msie && !e.button) {
                mouseup(e, $item);
                return false;
            }
            return true;
        }

        var showHueSelected = function(element) {
            /// <summary>show the element passed as selected</summary>
            /// <param name="element">div element</param>

            var selected = $h.data("selected");
            if (selected) {
                selected.style.zIndex = 0;
                selected.style.width = options.hueWidth + "px";
                selected.style.margin = "0";
                selected.style.border = "none 0";
            }
            $h.data("selected", element);

            if (element) {
                element.style.zIndex = 1;
                element.style.width = (options.hueWidth - 2) + "px";
                element.style.margin = "-1px 0";
                element.style.border = "solid 1px #000";
            }
        }
        var setHue = function(element) {
            /// <summary>set the hue to the one stored on the element</summary>
            /// <param name="element">div element</param>

            value.hue = element.hue;
            setValueHex(value.toHex(), shown);
        }

        var showSaturationBrightnessSelected = function(element) {
            /// <summary>show the element passed as selected</summary>
            /// <param name="element">div element</param>

            var selected = $sb.data("selected");
            if (selected) {
                selected.style.width = options.cellSize + "px";
                selected.style.height = options.cellSize + "px";
                selected.style.border = "none 0";
            }
            $sb.data("selected", element);

            if (element) {
                element.style.width = (options.cellSize - 2) + "px";
                element.style.height = (options.cellSize - 2) + "px";
                element.style.border = "solid 1px ".concat(value.brightnessContrast().toHex());
            }
        }
        var setSaturationBrightness = function(element) {
            /// <summary>set the saturation and brightness to the one stored on the element</summary>
            /// <param name="element">div element</param>

            value.saturation = element.saturation;
            value.brightness = element.brightness;
            setValueHex(value.toHex(), false);

            showSaturationBrightnessSelected(element);
        }

        // bind input events
        $input.bind("change", selectColor);
        $input.bind("colorAccept", accept);
        $input.bind("colorCancel", cancel);
        if (options.change) { $input.change(options.change) }
        if (options.colorSelected) { $input.colorSelected(options.colorSelected) }

        // exposed functions/properties
        this.accept = accept;
        this.cancel = cancel;
        this.isChanged = false;

        // show color
        selectColor();

        return this;
    }

    // private functions
    function getSaturationBrightnessMap(options) {
        /// <summary>saturation/brightness map</summary>
        var sbMap = ["<div class='saturationBrightnessMap' style='position:absolute;z-index:1;overflow:hidden;width:", options.cells * options.cellSize, "px;height:", options.cells * options.cellSize, "px'>"];
        for (var b = 0; b < options.cells; b++) {
            for (var s = 0; s < options.cells; s++) {
                sbMap.push("<div ".concat("style='float:left;overflow:hidden;width:", options.cellSize, "px;height:", options.cellSize, "px;border:none 0'></div>"));
            }
        }
        sbMap.push("</div>");
        return sbMap.join("");
    }
    function getHueMap(options) {
        /// <summary>hue map</summary>
        var hMap = ["<div class='hueMap' style='position:absolute;z-index:0;width:", options.hueWidth, "px;height:", options.cells * options.cellSize, "px'>"];
        for (var h = 0; h < options.cells * options.cellSize; h++) {
            hMap.push("<div style='float:left;position:relative;overflow:hidden;width:", options.hueWidth, "px;height:1px;border:none 0'>&nbsp;</div>");
        }
        hMap.push("</div>");
        return hMap.join("");
    }

    // defaults
    $.colorInput.defaults = {
        acceptCancelButtons: true,
        cancelOnClick: [document],
        cells: 15,
        cellSize: 10,
        change: null,
        colorSelected: null,
        dropdownId: "ColorDropdown",
        hideInput: true,
        hoverSelect: false,
        hueWidth: 20,
        noHash: false,
        showLeft: false,
        textAccept: "ok",
        textCancel: "cancel"
    };

    // Stores reference to currently shown colorInput control
    $.colorInput.current = null;

    $.colorInput.color = function(hue, saturation, brightness) {
        /// <summary>HSB colour object</summary>
        /// <param name="hue">hue, 0-1 (optional, default 0)</param>
        /// <param name="saturation">saturation, 0-1 (optional, default 0)</param>
        /// <param name="brightness">brightness, 0-1 (optional, default 0)</param>

        if (arguments.length == 1 && hue.constructor == String) {
            this.fromHex(hue);
        } else {
            this.hue = parseFloat(hue) || 0;
            this.saturation = parseFloat(saturation) || 0;
            this.brightness = parseFloat(brightness) || 0;
        }

        this.isValid = function() {
            /// <summary>Check values are a valid colour</summary>
            if (isNaN(this.hue)) { return false }
            while (this.hue > 1) { this.hue -= 1; }
            while (this.hue < 0) { this.hue += 1; }

            if (isNaN(this.saturation)) { return false }
            if (this.saturation > 1) { this.saturation = 1; }
            else if (this.saturation < 0) { this.saturation = 0; }

            if (isNaN(this.brightness)) { return false }
            if (this.brightness > 1) { this.brightness = 1; }
            else if (this.brightness < 0) { this.brightness = 0; }

            return true;
        }
        this.isValid();

        this.brightnessContrast = function() {
            /// <summary>gets a black or white color to contrast the current one</summary>
            return new $.colorInput.color(0, 0, this.brightness >= .75 ? 0 : 1);
        }

        this.valueToHex = function(value) {
            /// <summary>Convert a value to hex</summary>
            /// <param name="value">value 0-1 (required)</param>
            var s = Math.round(value * 255).toString(16);
            return s.length == 1 ? "0" + s : s;
        }
        this.hexToValue = function(h) {
            /// <summary>Convert hex to a value</summary>
            /// <param name="value">value 00-FF (required)</param>
            return parseInt(h, 16) / 255;
        }

        this.fromHex = function(hex) {
            /// <summary>Set this colour using a hex value</summary>
            /// <param name="hex">hex value (required)</param>
            if (hex.slice(0, 1) == "#") { hex = hex.slice(1); }
            if (hex.length == 3) { hex = hex.split(''); hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]; }
            if (hex.length != 6) { // invalidate the color
                this.brightness = NaN;
                return;
            }

            var red = this.hexToValue(hex.substr(0, 2));
            var green = this.hexToValue(hex.substr(2, 2));
            var blue = this.hexToValue(hex.substr(4, 2));

            this.brightness = Math.max(Math.max(red, green), blue);
            var min = Math.min(Math.min(red, green), blue);
            if (min == this.brightness) {
                this.hue = 0;
                this.saturation = 0;
            } else {
                var delta = this.brightness - min;
                this.saturation = delta / this.brightness;
                if (red == this.brightness) { this.hue = (green - blue) / delta; }
                else if (green == this.brightness) { this.hue = 2 + ((blue - red) / delta); }
                else { this.hue = 4 + ((red - green) / delta); }

                this.hue /= 6;
                if (this.hue < 0) { this.hue += 1; }
                if (this.hue > 1) { this.hue -= 1; }
            }

            return this;
        }
        this.toHex = function(noHash) {
            /// <summary>Covert this colour into a hex value</summary>
            /// <param name="noHash">add a hash (optional, default false)</param>
            if (!this.isValid()) { return "Transparent"; }

            var red, green, blue;
            var i = Math.floor(this.hue * 6);
            var f = (this.hue * 6) - i;
            var p = this.brightness * (1 - this.saturation);
            var q = this.brightness * (1 - (this.saturation * f));
            var t = this.brightness * (1 - (this.saturation * (1 - f)));
            switch (i) {
                case 1: red = q; green = this.brightness; blue = p; break;
                case 2: red = p; green = this.brightness; blue = t; break;
                case 3: red = p; green = q; blue = this.brightness; break;
                case 4: red = t; green = p; blue = this.brightness; break;
                case 5: red = this.brightness; green = p; blue = q; break;
                case 6: case 0:
                    red = this.brightness; green = t; blue = p; break;
            }

            return noHash ? "" : "#" + this.valueToHex(red) + this.valueToHex(green) + this.valueToHex(blue);
        }
    }

})(jQuery);