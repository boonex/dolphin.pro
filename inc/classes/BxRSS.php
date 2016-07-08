<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxRSS
{
    var $sXmlText;

    //mandatory
    var $title;
    var $link;
    var $description;

    //optional
    var $language;
    var $copyright;
    var $managingEditor;
    var $webMaster;
    var $pubDate;
    var $lastBuildDate;
    var $category;
    var $generator;
    var $docs;
    var $cloud;
    var $ttl;
    var $image;
    var $rating;
    var $textInput;
    var $skipHours;
    var $skipDays;

    //array with items
    var $items;

    function __construct( $url )
    {
        $this -> items = array();

        if( $url and $this -> sXmlText = bx_file_get_contents( $url ) )
            $this -> _doFillFromText();
        else
            return null;
    }

    function _doFillFromText()
    {
        $vals = $ind = array();

        $xml_parser = xml_parser_create( 'UTF-8' );

        xml_parser_set_option ( $xml_parser, XML_OPTION_CASE_FOLDING, 0 );
        xml_parser_set_option ( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
        xml_parser_set_option ( $xml_parser, XML_OPTION_TARGET_ENCODING, 'UTF-8' );

        xml_parse_into_struct( $xml_parser, $this -> sXmlText, $vals, $ind );

        xml_parser_free( $xml_parser );

        $this -> sXmlText = '';

        //mandatory
        $this -> title        = $vals[ $ind['title'][0] ]['value'];
        $this -> link         = $vals[ $ind['link'][0] ]['value'];
        $this -> description  = $vals[ $ind['description'][0] ]['value'];

        //optional
        if( $ind['language']       and $vals[ $ind['language'][0] ]['level'] == 3 )
            $this -> language        = $vals[ $ind['language'][0] ]['value'];

        if( $ind['copyright']      and $vals[ $ind['copyright'][0] ]['level'] == 3 )
            $this -> copyright       = $vals[ $ind['copyright'][0] ]['value'];

        if( $ind['managingEditor'] and $vals[ $ind['managingEditor'][0] ]['level'] == 3 )
            $this -> managingEditor  = $vals[ $ind['managingEditor'][0] ]['value'];

        if( $ind['webMaster']      and $vals[ $ind['webMaster'][0] ]['level'] == 3 )
            $this -> webMaster       = $vals[ $ind['webMaster'][0] ]['value'];

        if( $ind['lastBuildDate']  and $vals[ $ind['lastBuildDate'][0] ]['level'] == 3 )
            $this -> lastBuildDate   = $vals[ $ind['lastBuildDate'][0] ]['value'];

        if( $ind['generator']      and $vals[ $ind['generator'][0] ]['level'] == 3 )
            $this -> generator       = $vals[ $ind['generator'][0] ]['value'];

        if( $ind['docs']           and $vals[ $ind['docs'][0] ]['level'] == 3 )
            $this -> docs            = $vals[ $ind['docs'][0] ]['value'];

        if( $ind['cloud']          and $vals[ $ind['cloud'][0] ]['level'] == 3 )
            $this -> cloud           = $vals[ $ind['cloud'][0] ]['value'];

        if( $ind['ttl']            and $vals[ $ind['ttl'][0] ]['level'] == 3 )
            $this -> ttl             = $vals[ $ind['ttl'][0] ]['value'];

        if( $ind['image']          and $vals[ $ind['image'][0] ]['level'] == 3 )
            $this -> image           = $vals[ $ind['image'][0] ]['value'];

        if( $ind['rating']         and $vals[ $ind['rating'][0] ]['level'] == 3 )
            $this -> rating          = $vals[ $ind['rating'][0] ]['value'];

        if( $ind['textInput']      and $vals[ $ind['textInput'][0] ]['level'] == 3 )
            $this -> textInput       = $vals[ $ind['textInput'][0] ]['value'];

        if( $ind['skipHours']      and $vals[ $ind['skipHours'][0] ]['level'] == 3 )
            $this -> skipHours       = $vals[ $ind['skipHours'][0] ]['value'];

        if( $ind['skipDays']       and $vals[ $ind['skipDays'][0] ]['level'] == 3 )
            $this -> skipDays        = $vals[ $ind['skipDays'][0] ]['value'];

        if( $ind['pubDate']        and $vals[ $ind['pubDate'][0] ]['level'] == 3 )
            $this -> pubDate         = $vals[ $ind['pubDate'][0] ]['value'];

        if( $ind['category']       and $vals[ $ind['category'][0] ]['level'] == 3 )
            $this -> category        = $vals[ $ind['category'][0] ]['value'];

        //get dolphin version
        if( $ind['dolphin']        and $vals[ $ind['dolphin'][0] ]['level'] == 3 )
            $this -> dolVersion      = $vals[ $ind['dolphin'][0] ]['value'];

        //items
        if ($ind && $ind['item'])
        foreach( $ind['item'] as $itemInd ) {
            if( $vals[ $itemInd ]['type'] == 'close' )
                continue;

            $aItem = array();
            $aItem['category'] = array();

            while( $vals[ ++$itemInd ]['level'] == 4 ) {
                if( $vals[ $itemInd ]['tag'] == 'category' )
                    $aItem['category'][] = $vals[ $itemInd ];
                else
                    $aItem[ $vals[ $itemInd ]['tag'] ] = $vals[ $itemInd ];
            }
            $this -> items[] = new BxRSSItem( $aItem );
        }
    }
}

class BxRSSItem
{
    var $title;
    var $link;
    var $description;
    var $author;
    var $category;
    var $comments;
    var $pubDate;

    var $guid;
    var $guid_isPermaLink;

    var $source;
    var $source_url;

    var $enclosure;
    var $enclosure_url;
    var $enclosure_length;
    var $enclosure_type;

    function __construct( $aItem )
    {
        $this -> title       = $aItem['title']['value'];
        $this -> link        = $aItem['link']['value'];
        $this -> description = $aItem['description']['value'];
        $this -> author      = $aItem['author']['value'];
        $this -> comments    = $aItem['comments']['value'];
        $this -> pubDate     = $aItem['pubDate']['value'];

        $this -> source      = $aItem['source']['value'];
        $this -> source_url  = $aItem['source']['attributes']['url'];

        $this -> enclosure         = $aItem['enclosure']['value'];
        $this -> enclosure_url     = $aItem['enclosure']['attributes']['url'];
        $this -> enclosure_length  = $aItem['enclosure']['attributes']['length'];
        $this -> enclosure_type    = $aItem['enclosure']['attributes']['type'];

        $this -> guid        = $aItem['guid']['value'];

        if( $aItem['guid']['attributes']['isPermaLink'] == 'false' )
            $this -> guid_isPermaLink = false;
        else
            $this -> guid_isPermaLink = true; //default value is true

        $this -> category = array(); // (title => url)
        foreach( $aItem['category'] as $category ) {
            $this -> category[ $category['value'] ] = $category['attributes']['url'] ;
        }
    }
}
