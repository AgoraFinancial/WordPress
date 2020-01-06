function loadWatchlistPage( user_id, user_token, has_tickers ){

    // Check for params
    if(!user_id) return false;
    if(!user_token) return false;

    // Set vars
    var ticker_input = $('input#addTicker'),
        watchlist_instructions = $('#watchlist-instructions'),
        watchlist_table = $('#watchlist-table');

    // Preload table with tickers only
    if (has_tickers) {
        watchlist_instructions.hide();
        loadWatchlistTable();
    }

    $('#btnAddTicker').click(function(e) {
        e.preventDefault();
        var tickerSymbol = ticker_input.val();
        ticker_input.removeClass('error-doesnt-exist');

        // Load table if currently empty
        if (watchlist_table.html().length == 0) {
            loadWatchlistTable();    
        }

        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'addTicker',
                uid: user_id,
                token: user_token,
                val: tickerSymbol
            },
            function(r) {
                r = JSON.parse(r);
                if (r) {
                    if (r[0].Name){                        
                        watchlist_table.show();
                        watchlist_instructions.hide();
                        ticker_input.attr('placeholder','Enter Ticker Symbol');
                        loadWatchlistRow(r[0]);
                    }
                } else {
                    ticker_input.addClass('error-doesnt-exist');
                    var errormessage = '<div class="callout ticker-doesnt-exist alert small">This Ticker Does Not Exist. Please Try Again.<br><strong>NOTE:</strong> The watchlist does not track OTC stocks.</div>';
                    $(errormessage).appendTo($(".watchlist-form")).fadeIn('fast', function() {
                        childElm = $(this);
                        setTimeout(function() {
                            childElm.fadeOut('slow');
                            $("input#addTicker").removeClass('error-doesnt-exist');
                        }, 4000);
                    });
                }
            }
        );
    });

    // Expand ticker content
    $(document).on('click tap','a.ticker_chart_more.expands', function(e) {
        e.preventDefault();
        $(e.target).removeClass('expands');
        watchlist_instructions.hide();
        var tickerSymbol = $(e.target).data('symbol');
        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'getTickerContent',
                uid: user_id,
                token: tickerSymbol
            },
            function(r) {
                r = buildWatchlistIframe(tickerSymbol) + r;
                $(e.target).attr('style', '').addClass('collapse').html('&minus; View Less');
                $(e.target).closest('tr').after('<tr class="' + tickerSymbol + ' watchlist-expand-wrap"><td colspan="5">' + r + '</td></tr>');
            }
        );
    });

    // Collapse ticker content
    $(document).on('click tap', 'a.ticker_chart_more.collapse', function(e) {
        e.preventDefault();
        var tickerSymbol = $(e.target).data('symbol');
        $(e.target).removeClass('collapse').addClass('expands').html('&plus; View More');
        $(e.target).closest('tr').next().remove();
    });

    // Remove ticker row
    $(document).on('click tap', 'a.ticker_chart_remove', function(e) {
        e.preventDefault();
        var tickerSymbol = $(e.target).data('symbol');
        watchlist_instructions.hide();
        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'removeTicker',
                uid: user_id,
                token: user_token,
                val: tickerSymbol
            },
            function(r) {
                loadWatchlistTable();
            }
        );
    });

    // Build iframe html
    function buildWatchlistIframe( symbol ) {
        if (!symbol) return;
        var html = '<iframe id="tradingview_b489d" src="https://s.tradingview.com/widgetembed/?symbol=' + symbol + '&interval=D&hidetoptoolbar=1&saveimage=0&toolbarbg=f1f3f6&studies=&hideideas=1&theme=White&style=1&timezone=Etc%2FUTC&studies_overrides=%7B%7D&overrides=%7B%7D&enabled_features=%5B%5D&disabled_features=%5B%5D&locale=en&" width="100%" height="450" frameborder="0" allowtransparency="true" scrolling="no" allowfullscreen=""></iframe>';
        return html;
    }

    // Build watchlist table thead
    function buildWatchlistThead() {
        var html = '';
        html += '<thead>';
            html += '<tr>';
                html += '<th data-label="Symbol" sort-column="0">Symbol</th>';
                html += '<th data-label="Last Price" sort-column="1">Last&nbsp;Price</th>';
                html += '<th data-label="Change" sort-column="2">Change</th>';
                html += '<th data-label="% Change" sort-column="3">%&nbsp;Change</th>';
                html += '<th data-label="Actions" sort-column="4">Actions</th>';
            html += '</tr>';
        html += '</thead>';
        html += '<tbody></tbody>';
        return html;
    }

    // Build watchlist table row
    function buildWatchlistRow( symbol, price, change, percent, color_class ) {
        var html = '';
        html += '<tr>';
            html += '<td data-label="Symbol">' + symbol + '</td>';
            html += '<td data-label="Last Price">$' + addPriceZeros(price) + '</td>';
            html += '<td data-label="Change" class="' + color_class + '">' + change + '</td>';
            html += '<td data-label="% Change" class="' + color_class + '">' + percent + '</td>';
            html += '<td data-label="Actions">';
                html += '<a class="button secondary small ticker_chart_more expands" data-symbol="' + symbol + '">&plus; View More</a>';
                html += '<a class="button alert small ticker_chart_remove" data-symbol="' + symbol + '">&times; Remove</a>';
            html += '</td>';
        html += '</tr>';
        return html;
    }

    // Create watchlist row
    function loadWatchlistRow( obj ) {
        var Symbol = obj.Symbol,
            Price = obj.LastTradePriceOnly,
            Change = obj.Change,
            ChangeinPercent = obj.ChangeinPercent;
        if (Price === null) Price = 'n/a';    
        if (Change === null) Change = 'n/a';    
        if (ChangeinPercent === null) ChangeinPercent = 'n/a';
        if (Change === 0) {
            clsColor = 'ticker-no-change';
        } else if (Change < 0) {
            clsColor = 'ticker-down';
        } else {
            clsColor = 'ticker-up';
        }
        $('#watchlist-table tbody').append(buildWatchlistRow( Symbol, Price, Change, ChangeinPercent, clsColor ));
    }

    // Load watchlist 
    function loadWatchlistTable() {
        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'getTickers',
                uid: user_id,
                token: user_token
            },
            function(r) {
                var hasTickers = 0;
                watchlist_table.hide();
                watchlist_table.html(buildWatchlistThead());
                sortTableInit(); // Re-init sorting
                if (r) {
                    r = JSON.parse(r);
                    $.each(r, function(idx,obj) {
                        loadWatchlistRow(obj);
                        hasTickers = 1;
                    });
                }
                if (hasTickers) {
                    watchlist_table.show();
                    watchlist_instructions.hide();
                } else {
                    watchlist_instructions.show();
                }
            }
        );
    }

}

// Add to watchlist button for ticker pages
jQuery(document).on('click tap','#add-to-watchlist', function(e) {
    e.preventDefault();
    var user_id = jQuery(this).data('id');
    var user_token = jQuery(this).data('token');
    var symbol = jQuery(this).data('symbol');
    console.log(user_id);
    console.log(user_token);
    console.log(symbol);
    var button = jQuery(this);
    jQuery.post(themeAjaxUrl,
        {
            action: 'user_data_ajax',
            method: 'addTicker',
            uid: user_id,
            token: user_token,
            val: symbol,
        },
        function(r) {
            console.log('Added');
            if (r) {
                button.remove();
            }
        }
    );
});