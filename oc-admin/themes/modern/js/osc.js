/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/* ===================================================
 * osc tooltip
 * ===================================================
 * Usage:
 * Display a custom tooltip on mouse over.
 * $(selector).tooltip(message, {options});
 *
 * options = {
 *     layout: ['gray-tooltip', 'black-tooltip','info-tooltip','warning-tooltip','success-tooltip','error-tooltip'],
 *     position: {
 *         x: ['left',right,'middle'],
 *         y: ['top','bottom','middle']
 *     }
 * }
 **/
/*jshint browser: true*/
osc.tooltip = function (message, options) {
    defaults = {
        position: {
            y: 'middle',
            x: 'right'
        },
        layout: 'black-tooltip'
    }
    var opts = $.extend({}, defaults, options);

    // check if exists tooltip
    var $tooltip = $('#osc-tooltip');
    if ($tooltip.length === 0) {
        $tooltip = $('<div id="osc-tooltip"></div>');
        $('body').append($tooltip);
    }

    //Add the message
    var hovered;
    $(this).hover(function () {
        hovered = true;
        var offset = $(this).offset();
        var tooltipContainer = $('<div class="tooltip-message"></div>');
        tooltipContainer.append(message);
        $tooltip.html(tooltipContainer).attr('class', opts.layout + ' ' + opts.position.x + '-' + opts.position.y).append('<div class="tooltip-arrow"></div>').show();
        switch (opts.position.y) {
            case 'top':
                positionTop = offset.top - ($tooltip.outerHeight());
                break
            case 'middle':
                positionTop = offset.top - ($tooltip.outerHeight() / 2) + ($(this).outerHeight() / 2);
                break
            case 'bottom':
                positionTop = offset.top + $(this).outerHeight();
                break
        }
        switch (opts.position.x) {
            case 'left':
                positionLeft = offset.left - $tooltip.outerWidth();
                break
            case 'middle':
                positionLeft = offset.left - ($tooltip.outerWidth() / 2) + ($(this).outerWidth() / 2);
                break
            case 'right':
                positionLeft = offset.left + $(this).width();
                break
        }
        $tooltip.css({
            left: positionLeft,
            top: positionTop
        });

    }, function () {
        hovered = false;
        setTimeout(function () {
            if (!hovered) {
                $tooltip.hide();
            }
        }, 100);
    });
};

//extend
$.fn.osc_tooltip = osc.tooltip;


var OSC_ESC_MAP = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
};

function oscEscapeHTML(str) {
    if (str != undefined) {
        return str.toString().replace(/[&<>'"]/g, function (c) {
            return OSC_ESC_MAP[c];
        });
    }
    return "";
}
function setJsMessage(alertClass, alertMessage) {
    var jsMessage = document.getElementById("jsMessage");
    var pTag = jsMessage.querySelector("p");
    pTag.setAttribute("class", alertClass);
    pTag.textContent = alertMessage;
    jsMessage.classList.remove('hide');
    jsMessage.removeAttribute('style');
}
// show div.actions on hover of the row in datatables
// in pure JavaScript, we wouldn't need to use jQuery
window.onload = function() {
    var dataTablesRows = document.querySelectorAll('#datatablesForm tr');
    var dataTablesRows_length = dataTablesRows.length;
    for (var i = 0; i < dataTablesRows_length; i++) {
        var actions_div = dataTablesRows[i].querySelector('.actions');
        if (actions_div) {
            dataTablesRows[i].onmouseover = function () {
                this.classList.add('show-actions');
            };
            dataTablesRows[i].onmouseout = function () {
                this.classList.remove('show-actions');
            };
            var more_trigger = dataTablesRows[i].querySelector('.show-more-trigger');
            if (more_trigger) {
                more_trigger.addEventListener('click', function (event) {
                    var actions_ul = this.nextElementSibling;
                    actions_ul.classList.add('d-block');
                    event.target.classList.add('hide');
                    event.stopPropagation();
                    event.preventDefault();
                    document.addEventListener('click', function (event) {
                        // check parent element is not actions_ul.parentElement or doesn't have hide class
                        if (event.target.nextElementSibling !== actions_ul) {
                            actions_ul.classList.remove('d-block');
                            actions_ul.parentNode.querySelector('.show-more-trigger').classList.remove('hide');
                            //remove this event listener
                            document.removeEventListener('click', arguments.callee);
                        }
                    });
                });
            }
        }
    }
}