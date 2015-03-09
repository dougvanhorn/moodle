$(function () {
    var img_caption = '',
        img_class = '';
    var img_url = '';

    $('img.zoom').each(function () {
        if ($(this).attr('title')) {
            img_caption = $(this).attr('title');
        } else {
            img_caption = '';
        }
        img_url = $(this).attr('src');
        img_class = $(this).hasClass('right') ? 'right' : 'left';
        $(this).attr('title', 'CLICK TO ENLARGE');
        $(this).removeClass(img_class);
        $(this).wrap('<dl class="figure ' + img_class + '"><dt></dt><dd>' + img_caption + '</dd>');

    });
    
    
    $('img.overview').each(function () {
        if ($(this).attr('title')) {
            img_caption = $(this).attr('title');
        } else {
            img_caption = '';
        }
        img_url = $(this).attr('src');
        img_class = '';//$(this).hasClass('right') ? 'right' : 'left';
        //$(this).attr('title', 'CLICK TO ENLARGE');
        $(this).removeClass(img_class);
        $(this).wrap('<dl class="coursemain ' + img_class + '"><dt></dt><dd>' + img_caption + '</dd>');

    });
    
    var current_state = "big";
    $("dl.figure").click(function () {
        zoomer(this);
    });

    function zoomer(thisObj) {
        if ($(thisObj).hasClass('zoomed')) {
            $(thisObj).closest('dl').animate({
                width: "200",
                /*marginLeft: "0px"*/
            }, 200);

         $(thisObj).removeClass('zoomed');
        } else  {
            $(thisObj).closest('dl').animate({
                width: "620",
                /*marginLeft: "-3px"*/
            }, 200);
             $(thisObj).addClass('zoomed');
        }

    }

    $('.Lobjective').each(function () {
        $(this).prepend($('<p class="learning_objective_title">').text("Learning Objective"));
    });

});


/* Function buildUrl
 * Used by Badge.
 *
 * var url = "http://mysite.com/";
 * var parameters = new Array();
 * parameters["firstname"] = "John Jr";
 * parameters["lastname"] = "Doe";
 * alert(buildUrl(url, parameters));
 *
 * >>>>>>>>   displays: http://mysite.com/?firstname=John%20Jr&lastname=Doe
 */    
function buildUrl(url, parameters) {
    var qs = "";
    for (var key in parameters) {
        var value = parameters[key];
        qs += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
    }
    if (qs.length > 0)  {
        qs = qs.substring(0, qs.length-1); //chop off last "&"
        url = url + "?" + qs;
    }
  return url;
}

window.onload = prepareLinks;

function prepareLinks() {
    var links = document.getElementsByTagName("a");
    for (var i = 0; i < links.length; i++) {
        if (links[i].getAttribute("class") == "popup") {
            links[i].onclick = function () {
                popUp(this.getAttribute("href"));
                return false;
            }
        }
    }
}

//function popUp(winURL) {
//    window.open(winURL, "popup", "width=740,height=680,scrollbars=1");
//}

// this adds a class to the main title of the first article in a course
// deprecated in favor of php solution, here for earliest SPCs
if (typeof topart !== 'undefined') {
    var d = document.getElementById('region-main-wrap');
    d.className = d.className + 'topArticle';
}

$(document).ready(function() {
    //==================================================
    $('a.popup:not(.bactotop),a.jumplink:not(.bactotop) ').click(function() {
        var NWin = window.open($(this).prop('href'), '', 'height=800,width=800,scrollbars=yes');
        if (window.focus) {
            NWin.focus();
        }
        return false;
    });
    //SPC MENU TOOLTIP ==================================================    
    var targets = $( "[class*='progress_']" ),
        target  = false,
        tooltip = false,
        title   = false;
        vpos    = 14;
 
    targets.bind( 'mouseenter', function() {
        target  = $( this );
        tip     = target.attr( 'title' );
        tooltip = $( '<div id="tooltip"></div>' );
        color =  $( this ).attr('class')

        if( !tip || tip == '' )
            return false;
 
        target.removeAttr( 'title' );
        tooltip.css( 'opacity', 0 )
               .html( tip )
               .appendTo( 'body' );
 
        var init_tooltip = function()
        {
            /*if( $( window ).width() < tooltip.outerWidth() * 1.5 )
            {    
                tooltip.css( 'max-width', $( window ).width() / 2 );}
            else
            {
                tooltip.css( 'max-width', 140 );
                tooltip.addClass( color );
            }*/
                tooltip.css( 'width', 127 );
                tooltip.addClass( color );
            var pos_left = target.offset().left + 86;//( target.outerWidth() / 2 ) - ( tooltip.outerWidth() / 2 ),
                pos_top  = target.offset().top - tooltip.outerHeight() - vpos;
 
            /*
            if( pos_left < 0 )
            {
                pos_left = target.offset().left + target.outerWidth() / 2 - vpos;
                tooltip.addClass( 'left' );
            }
                else
                {
                    tooltip.removeClass( 'left' );
                }
            if( pos_left + tooltip.outerWidth() > $( window ).width() )
            {
                pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + vpos;
                tooltip.addClass( 'right' );
            }
                else
                {
                    tooltip.removeClass( 'right' );
                }
            */
            if( pos_top < 0 )
            {
                var pos_top  = target.offset().top + target.outerHeight();
                tooltip.addClass( 'top' );
            }
                else
                {
                    tooltip.removeClass( 'top' );
                }
 
            tooltip.css( { left: pos_left, top: pos_top } )
                   .animate( { top: '+=10', opacity: 1 }, 50 );
        };
 
        init_tooltip();
        $( window ).resize( init_tooltip );
 
        var remove_tooltip = function()
        {
            tooltip.animate( { top: '-=10', opacity: 0 }, 50, function()
            {
                $( this ).remove();
            });
 
            target.attr( 'title', tip );
        };
 
        target.bind( 'mouseleave', remove_tooltip );
        tooltip.bind( 'click', remove_tooltip );
    });
});
