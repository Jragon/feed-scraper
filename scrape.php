<div id="content">
<form action="" method="GET" align="center">
    <input type="text" name="search" size="64" id="search" placeholder="Search RSS feeds" />
    <input type="hidden" name="pg" value="1">
    <input type="submit" value="Search" />
</form>

<?php
echo "If no results are found try to do the Next page or a different Search<br />Be patient as it discovers the feeds<br />";
if ( !isset( $_GET ) || empty( $_GET ) ) {
    echo "Insert your search terms <br />";
} else {
    $random_array = array(
         "digital photo",
        "photography",
        "camera photo",
        "camera photographic",
        "fine art photography" 
    );
    shuffle( $random_array );
    $random_search_term = $random_array[ 0 ];
    $search_term        = trim( $_GET[ 'search' ] );
    if ( !isset( $_GET[ 'search' ] ) || $search_term == "" || !preg_match( "/^[a-zA-Z0-9 -.+]+$/", $search_term ) ) {
        $search_term = $random_search_term;
        echo "Your search term did not contain acceptable characters, a random search was used.<br />";
    }
    //$search_term = mysql_real_escape_string(trim($_GET['search']));
    $search_term = str_replace( array(
         " ",
        ",",
        "++" 
    ), "+", $search_term );
    echo "Searching feeds for '$search_term' <br />"; 
    //$pg = false;
    if ( !isset( $_GET[ 'pg' ] ) or !is_numeric( $_GET[ 'pg' ] )) {
        $pg = 1;
    } else {
        $pg = $_GET[ 'pg' ];
    }
    $result_page = $pg * 10 + 1;
    if ( $pg == 1 ) {
        $result_page = 1;
    }
    $next_page = $pg + 1;
    $prev_page = $pg - 1;
    if ( $prev_page <= 1 ) {
        $prev_page = 1;
    }
    echo " <a href='?search=$search_term&pg=$prev_page'>Previous</a> <b>[$pg]</b>  <a href='?search=$search_term&pg=$next_page'>Next</a>";
    echo "<br /><br />";
    function getparsedHost( $new_parse_url )
    {
        $parsedUrl = parse_url( trim( $new_parse_url ) );
        return trim( $parsedUrl[ 'host' ] ? $parsedUrl[ 'host' ] : array_shift( explode( '/', $parsedUrl[ 'path' ], 2 ) ) );
    }
    function locateFeedUrl( $url )
    {
        if ( !empty( $url ) || $url != "" ) {
            $url = str_ireplace( "https://", "http://", trim( $url ) );
            if ( substr( $url, 0, 4 ) != "http" ) {
                $url = "http://$url";
            }
            //$url = "http://".getparsedHost($url);//uncomment this line if want the main sites feeds for any link
            $html = @file_get_contents( $url );
            if ( !$html ) {
                //echo "$url not found";
            } else {
                if ( preg_match_all( '#<link[^>]+type=\s*(?:"|)(application/rss\+xml|application/atom\+xml|text/xml)[^>]*>#is', $html, $matches ) ) {
                    if ( !$matches ) {
                        echo "No feeds found for: $url";
                    } else {
                        foreach ( $matches[ 0 ] as $match ) {
                            if ( preg_match( '#href=\s*(?:"|)([^"\s>]+)#i', $match, $rssUrl ) ) {
                                if ( substr( $rssUrl[ 1 ], 0, 1 ) == "/" || substr( $rssUrl[ 1 ], 0, 2 ) == "./" || substr( $rssUrl[ 1 ], 0, 3 ) == "../" || substr( $rssUrl[ 1 ], 0, 4 ) == ".../" ) {
                                    $rssUrl[ 1 ] = str_replace( array(
                                         "./",
                                        "../",
                                        ".../" 
                                    ), "/", $rssUrl[ 1 ] );
                                    $rssUrl[ 1 ] = "http://" . getparsedHost( $url ) . $rssUrl[ 1 ];
                                }
                                $feed_link = trim( $rssUrl[ 1 ] );
                                //$feed_link_array[] = trim($rssUrl[1]);//created array to be used outside loop
                                echo "<a href ='$feed_link'>$feed_link</a><br />"; //echo all found feeds
                                //return "<a href ='$feed_link'>$feed_link</a><br />";//return one result, main feed    
                            }
                        }
                    }
                }
            }
        } else {
            echo "Wrong values";
        }
    }
    function getPage( $url, $referer, $agent, $header, $timeout )
    {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HEADER, $header );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt( $ch, CURLOPT_HTTPPROXYTUNNEL, 1 );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_REFERER, $referer );
        curl_setopt( $ch, CURLOPT_USERAGENT, $agent );
        $result[ 'EXE' ] = curl_exec( $ch );
        $result[ 'INF' ] = curl_getinfo( $ch );
        $result[ 'ERR' ] = curl_error( $ch );
        curl_close( $ch );
        return $result;
    }
    $result = getPage(
    //'[proxy IP]:[port]', // get a proxy from somewhere
        
    // "http://www.bing.com/search?q=$search_term&count 10&first=$result_page",
        "http://www.bing.com/search?q=$search_term&go=&qs=n&sk=&sc=8-7&first=$result_page&FORM=PQRE", 'http://www.bing.com/', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0a2) Gecko/20110613 Firefox/6.0a2', 1, 5 );
    if ( empty( $result[ 'ERR' ] ) ) {
        preg_match_all( '(<div class="sb_tlst">.*<h3>.*<a href="(.*)".*>(.*)</a>.*</h3>.*</div>)siU', $result[ 'EXE' ], $matched );
        for ( $i = 0; $i < count( $matched[ 2 ] ); $i++ ) {
            //$matched[2][$i] = strip_tags($matched[2][$i]);
            $link            = trim( $matched[ 1 ][ $i ] );
            $title           = trim( $matched[ 2 ][ $i ] );
            $website_data[ ] = "$link|$title";
        }
    } else {
        echo "Awww, it didn't work ";
    }
    //displaying data
    if ( !empty( $website_data ) ) {
        $website_data = array_unique( $website_data );
        foreach ( $website_data as $website_array ) {
            $data      = explode( "|", $website_array );
            $web_url   = $data[ 0 ];
            $web_title = $data[ 1 ];
            $web_feeds = locateFeedUrl( $web_url );
            echo $web_feeds;
            //echo "<a href='$web_url'>$web_title</a>";//search results
        }
    } else {
        echo "No results were found";
    }
    //if no submit
}
?>

</div>