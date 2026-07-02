<?php
/**
 * htmlfilter.inc
 * ---------------
 * This set of functions allows you to filter html in order to remove
 * any malicious tags from it. Useful in cases when you need to filter
 * user input for any cross-site-scripting attempts.
 *
 * Copyright (C) 2002-2004 by Duke University
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA
 * 02110-1301  USA
 *
 * @Author  Konstantin Riabitsev <icon@linux.duke.edu>
 * @Author  Jim Jagielski <jim@jaguNET.com / jimjag@gmail.com>
 * @Version 1.1 ($Date$)
 */

/**
 * This function returns the final tag out of the tag name, an array
 * of attributes, and the type of the tag. This function is called by
 * tln_sanitize internally.
 *
 * @param string $tagname the name of the tag.
 * @param array $attary the array of attributes and their values
 * @param integer $tagtype The type of the tag (see in comments).
 * @return string A string with the final tag representation.
 */
function tln_tagprint($tagname, $attary, $tagtype)
{
    if ($tagtype == 2) {
        $fulltag = '</' . $tagname . '>';
    } else {
        $fulltag = '<' . $tagname;
        if (is_array($attary) && sizeof($attary)) {
            $atts = array();
            foreach($attary as $attname => $attvalue) {
                array_push($atts, "$attname=$attvalue");
            }
            $fulltag .= ' ' . join(' ', $atts);
        }
        if ($tagtype == 3) {
            $fulltag .= ' /';
        }
        $fulltag .= '>';
    }
    return $fulltag;
}

/**
 * A small helper function to use with array_walk. Modifies a by-ref
 * value and makes it lowercase.
 *
 * @param string $val a value passed by-ref.
 * @return      void since it modifies a by-ref value.
 */
function tln_casenormalize(&$val)
{
    $val = strtolower($val);
}

/**
 * This function skips any whitespace from the current position within
 * a string and to the next non-whitespace value.
 *
 * @param string $body the string
 * @param integer $offset the offset within the string where we should start
 * looking for the next non-whitespace character.
 * @return integer          the location within the $body where the next
 * non-whitespace char is located.
 */
function tln_skipspace($body, $offset)
{
    preg_match('/^(\s*)/s', substr($body, $offset), $matches);
    if (sizeof($matches[1])) {
        $count = strlen($matches[1]);
        $offset += $count;
    }
    return $offset;
}

/**
 * This function looks for the next character within a string. It's
 * really just a glorified "strpos", except it catches the failures
 * nicely.
 *
 * @param string $body   The string to look for needle in.
 * @param integer $offset Start looking from this position.
 * @param string $needle The character/string to look for.
 * @return integer           location of the next occurrence of the needle, or
 * strlen($body) if needle wasn't found.
 */
function tln_findnxstr($body, $offset, $needle)
{
    $pos = strpos($body, $needle, $offset);
    if ($pos === false) {
        $pos = strlen($body);
    }
    return $pos;
}

/**
 * This function takes a PCRE-style regexp and tries to match it
 * within the string.
 *
 * @param string $body   The string to look for needle in.
 * @param integer $offset Start looking from here.
 * @param string $reg       A PCRE-style regex to match.
 * @return array|boolean  Returns a false if no matches found, or an array
 * with the following members:
 * - integer with the location of the match within $body
 * - string with whatever content between offset and the match
 * - string with whatever it is we matched
 */
function tln_findnxreg($body, $offset, $reg)
{
    $matches = array();
    $retarr = array();
    $preg_rule = '%^(.*?)(' . $reg . ')%s';
    preg_match($preg_rule, substr($body, $offset), $matches);
    if (!isset($matches[0]) || !$matches[0]) {
        $retarr = false;
    } else {
        $retarr[0] = $offset + strlen($matches[1]);
        $retarr[1] = $matches[1];
        $retarr[2] = $matches[2];
    }
    return $retarr;
}

/**
 * This function looks for the next tag.
 *
 * @param string $body   String where to look for the next tag.
 * @param integer $offset Start looking from here.
 * @return array|boolean false if no more tags exist in the body, or
 * an array with the following members:
 * - string with the name of the tag
 * - array with attributes and their values
 * - integer with tag type (1, 2, or 3)
 * - integer where the tag starts (starting "<")
 * - integer where the tag ends (ending ">")
 * first three members will be false, if the tag is invalid.
 */
function tln_getnxtag($body, $offset)
{
    if ($offset > strlen($body)) {
        return false;
    }
    $lt = tln_findnxstr($body, $offset, '<');
    if ($lt == strlen($body)) {
        return false;
    }
    $pos = tln_skipspace($body, $lt + 1);
    if ($pos >= strlen($body)) {
        return array(false, false, false, $lt, strlen($body));
    }
    switch (substr($body, $pos, 1)) {
    case '/':
        $tagtype = 2;
        $pos++;
        break;
    case '!':
        if (substr($body, $pos + 1, 2) == '--') {
            $gt = strpos($body, '-->', $pos);
            if ($gt === false) {
                $gt = strlen($body);
            } else {
                $gt += 2;
            }
            return array(false, false, false, $lt, $gt);
        } else {
            $gt = tln_findnxstr($body, $pos, '>');
            return array(false, false, false, $lt, $gt);
        }
        break;
    default:
        $tagtype = 1;
        break;
    }

    $regary = tln_findnxreg($body, $pos, '[^\w\-_]');
    if ($regary == false) {
        return array(false, false, false, $lt, strlen($body));
    }
    list($pos, $tagname, $match) = $regary;
    $tagname = strtolower($tagname);
    switch ($match) {
    case '/':
        if (substr($body, $pos, 2) == '/>') {
            $pos++;
            $tagtype = 3;
        } else {
            $gt = tln_findnxstr($body, $pos, '>');
            $retary = array(false, false, false, $lt, $gt);
            return $retary;
        }
    case '>':
        return array($tagname, false, $tagtype, $lt, $pos);
        break;
    default:
        if (!preg_match('/\s/', $match)) {
            $gt = tln_findnxstr($body, $lt, '>');
            return array(false, false, false, $lt, $gt);
        }
        break;
    }

    $attary = array();

    while ($pos <= strlen($body)) {
        $pos = tln_skipspace($body, $pos);
        if ($pos == strlen($body)) {
            return array(false, false, false, $lt, $pos);
        }
        $matches = array();
        if (preg_match('%^(\s*)(>|/>)%s', substr($body, $pos), $matches)) {
            $pos += strlen($matches[1]);
            if ($matches[2] == '/>') {
                $tagtype = 3;
                $pos++;
            }
            return array($tagname, $attary, $tagtype, $lt, $pos);
        }

        $regary = tln_findnxreg($body, $pos, '[^\w\-_]');
        if ($regary == false) {
            return array(false, false, false, $lt, strlen($body));
        }
        list($pos, $attname, $match) = $regary;
        $attname = strtolower($attname);
        switch ($match) {
        case '/':
            if (substr($body, $pos, 2) == '/>') {
                $pos++;
                $tagtype = 3;
            } else {
                $gt = tln_findnxstr($body, $pos, '>');
                $retary = array(false, false, false, $lt, $gt);
                return $retary;
            }
        case '>':
            $attary[$attname] = '"yes"'; // 🌟 FIXED
            return array($tagname, $attary, $tagtype, $lt, $pos);
            break;
        default:
            $pos = tln_skipspace($body, $pos);
            $char = substr($body, $pos, 1);
            if ($char == '=') {
                $pos++;
                $pos = tln_skipspace($body, $pos);
                $quot = substr($body, $pos, 1);
                if ($quot == '\'') {
                    $regary = tln_findnxreg($body, $pos + 1, '\'');
                    if ($regary == false) {
                        return array(false, false, false, $lt, strlen($body));
                    }
                    list($pos, $attval, $match) = $regary;
                    $pos++;
                    $attary[$attname] = '\'' . $attval . '\''; // 🌟 FIXED
                } elseif ($quot == '"') {
                    $regary = tln_findnxreg($body, $pos + 1, '\"');
                    if ($regary == false) {
                        return array(false, false, false, $lt, strlen($body));
                    }
                    list($pos, $attval, $match) = $regary;
                    $pos++;
                    $attary[$attname] = '"' . $attval . '"'; // 🌟 FIXED
                } else {
                    $regary = tln_findnxreg($body, $pos, '[\s>]');
                    if ($regary == false) {
                        return array(false, false, false, $lt, strlen($body));
                    }
                    list($pos, $attval, $match) = $regary;
                    $attval = preg_replace('/\"/s', '&quot;', $attval);
                    $attary[$attname] = '"' . $attval . '"'; // 🌟 FIXED
                }
            } elseif (preg_match('|[\w/>]|', $char)) {
                $attary[$attname] = '"yes"'; // 🌟 FIXED
            } else {
                $gt = tln_findnxstr($body, $pos, '>');
                return array(false, false, false, $lt, $gt);
            }
            break;
        }
    }
    return array(false, false, false, $lt, strlen($body));
}

/**
 * Translates entities into literal values so they can be checked.
 */
function tln_deent(&$attvalue, $regex, $hex = false)
{
    $matches = array();
    preg_match_all($regex, $attvalue, $matches);
    if (is_array($matches) && sizeof($matches[0]) > 0) {
        $repl = array();
        for ($i = 0; $i < sizeof($matches[0]); $i++) {
            $numval = $matches[1][$i];
            if ($hex) {
                $numval = hexdec($numval);
            }
            $repl[$matches[0][$i]] = chr($numval); // 🌟 FIXED
        }
        $attvalue = strtr($attvalue, $repl);
        return true;
    } else {
        return false;
    }
}

/**
 * This function checks attribute values for entity-encoded values.
 */
function tln_defang(&$attvalue)
{
    if (strpos($attvalue, '&') === false && strpos($attvalue, '\\') === false) {
        return;
    }
    do {
        $m = false;
        $m = $m || tln_deent($attvalue, '/\&#0*(\d+);*/s');
        $m = $m || tln_deent($attvalue, '/\&#x0*((\d|[a-f])+);*/si', true);
        $m = $m || tln_deent($attvalue, '/\\\\(\d+)/s', true);
    } while ($m == true);
    $attvalue = stripslashes($attvalue);
}

/**
 * Kill any tabs, newlines, or carriage returns.
 */
function tln_unspace(&$attvalue)
{
    if (strcspn($attvalue, "\t\r\n\0 ") != strlen($attvalue)) {
        $attvalue = str_replace(
            array("\t", "\r", "\n", "\0", " "),
            array('', '', '', '', ''),
            $attvalue
        );
    }
}

/**
 * This function runs various checks against the attributes.
 */
function tln_fixatts(
    $tagname,
    $attary,
    $rm_attnames,
    $bad_attvals,
    $add_attr_to_tag,
    $trans_image_path,
    $block_external_images
) {
    foreach($attary as $attname => $attvalue) {
        foreach ($rm_attnames as $matchtag => $matchattrs) {
            if (preg_match($matchtag, $tagname)) {
                foreach ($matchattrs as $matchattr) {
                    if (preg_match($matchattr, $attname)) {
                        unset($attary[$attname]); // 🌟 FIXED
                        continue;
                    }
                }
            }
        }
        $oldattvalue = $attvalue;
        tln_defang($attvalue);
        if ($attname == 'style' && $attvalue !== $oldattvalue) {
            $attvalue = "idiocy";
            $attary[$attname] = $attvalue; // 🌟 FIXED
        }
        tln_unspace($attvalue);
        foreach ($bad_attvals as $matchtag => $matchattrs) {
            if (preg_match($matchtag, $tagname)) {
                foreach ($matchattrs as $matchattr => $valary) {
                    if (preg_match($matchattr, $attname)) {
                        list($valmatch, $valrepl) = $valary;
                        $newvalue = preg_replace($valmatch, $valrepl, $attvalue);
                        if ($newvalue != $attvalue) {
                            $attary[$attname] = $newvalue; // 🌟 FIXED
                            $attvalue = $newvalue;
                        }
                    }
                }
            }
        }
        if ($attname == 'style') {
            if (preg_match('/[\0-\37\200-\377]+/', $attvalue)) {
                $attary[$attname] = '"disallowed character"'; // 🌟 FIXED
            }
            $aMatch = array();
            preg_match_all("/url\s*\((.+)\)/si", $attvalue, $aMatch);
            if (count($aMatch) && isset($aMatch[1])) {
                foreach($aMatch[1] as $sMatch) {
                    $urlvalue = $sMatch;
                    tln_fixurl($attname, $urlvalue, $trans_image_path, $block_external_images);
                    $attary[$attname] = str_replace($sMatch, $urlvalue, $attvalue);
                }
            }
        }
     }
    foreach ($add_attr_to_tag as $matchtag => $addattary) {
        if (preg_match($matchtag, $tagname)) {
            $attary = array_merge($attary, $addattary);
        }
    }
    return $attary;
}

function tln_fixurl($attname, &$attvalue, $trans_image_path, $block_external_images)
{
    $sQuote = '"';
    $attvalue = trim($attvalue);
    if ($attvalue && ($attvalue[0] == '"' || $attvalue[0] == "'")) {
        $sQuote = $attvalue[0];
        $attvalue = trim(substr($attvalue, 1, -1));
    }

    if ($attvalue == '') {
        $attvalue = $sQuote . $trans_image_path . $sQuote;
    } else {
        if (preg_match('/[\0-\37\200-\377]+/', $attvalue)) {
            switch ($attname) {
                case 'href':
                    $attvalue = $sQuote . 'http://invalid-stuff-detected.example.com' . $sQuote;
                    break;
                default:
                    $attvalue = $sQuote . $trans_image_path . $sQuote;
                    break;
            }
        } else {
            $aUrl = parse_url($attvalue);
            if (isset($aUrl['scheme'])) {
                switch(strtolower($aUrl['scheme'])) {
                    case 'mailto':
                    case 'http':
                    case 'https':
                    case 'ftp':
                        if ($attname != 'href') {
                            if ($block_external_images == true) {
                                $attvalue = $sQuote . $trans_image_path . $sQuote;
                            } else {
                                if (!isset($aUrl['path'])) {
                                    $attvalue = $sQuote . $trans_image_path . $sQuote;
                                }
                            }
                        } else {
                            $attvalue = $sQuote . $attvalue . $sQuote;
                        }
                        break;
                    case 'outbind':
                        $attvalue = $sQuote . $attvalue . $sQuote;
                        break;
                    case 'cid':
                        $attvalue = $sQuote . $attvalue . $sQuote;
                        break;
                    default:
                        $attvalue = $sQuote . $trans_image_path . $sQuote;
                        break;
                }
            } else {
                if (!isset($aUrl['path']) || $aUrl['path'] != $trans_image_path) {
                    $attvalue = $sQuote . $trans_image_path . $sQuote;
                }
            }
        }
    }
}

function tln_fixstyle($body, $pos, $trans_image_path, $block_external_images)
{
    $content = '';
    $sToken = '';
    $bSucces = false;
    $bEndTag = false;
    for ($i=$pos,$iCount=strlen($body);$i<$iCount;++$i) {
        $char = $body[$i]; // 🌟 FIXED
        switch ($char) {
            case '<':
                $sToken = $char;
                break;
            case '/':
                 if ($sToken == '<') {
                    $sToken .= $char;
                    $bEndTag = true;
                 } else {
                    $content .= $char;
                 }
                 break;
            case '>':
                 if ($bEndTag) {
                    $sToken .= $char;
                    if (preg_match('/\<\/\s*style\s*\>/i',$sToken,$aMatch)) {
                        $newpos = $i + 1;
                        $bSucces = true;
                        break 2;
                    } else {
                        $content .= $sToken;
                     }
                    $bEndTag = false;
                 } else {
                    $content .= $char;
                 }
                 break;
            case '!':
                if ($sToken == '<') {
                    if (isset($body[$i+2]) && substr($body,$i,3) == '!--') { // 🌟 FIXED
                        $i = strpos($body,'-->',$i+3);
                        if ($i === false) {
                            $i = strlen($body);
                        }
                        $sToken = '';
                    }
                } else {
                    $content .= $char;
                }
                break;
            default:
                if ($bEndTag) {
                    $sToken .= $char;
                } else {
                    $content .= $char;
                }
                break;
        }
    }
    if ($bSucces == FALSE){
        return array(FALSE, strlen($body));
    }

    $content = preg_replace("|body(\s*\{.*?\})|si", ".bodyclass\\1", $content);
    if (preg_match('/[\16-\37\200-\377]+/',$content)) {
        $content = '';
        return array($content, $newpos);
    }

    $content = preg_replace("/^\s*(@import.*)$/mi","\n\n",$content);
    $content = preg_replace("/(\\\\)?u(\\\\)?r(\\\\)?l(\\\\)?/i", 'url', $content);
    $aMatch = array();
    preg_match_all("/url\s*\((.+)\)/si",$content,$aMatch);
    if (count($aMatch) && isset($aMatch[1])) {
        $aValue = $aReplace = array();
        foreach($aMatch[1] as $sMatch) {
            $urlvalue = $sMatch;
            tln_fixurl('style',$urlvalue, $trans_image_path, $block_external_images);
            $aValue[] = $sMatch;
            $aReplace[] = $urlvalue;
        }
        $content = str_replace($aValue,$aReplace,$content);
    }

    $contentTemp = $content;
    tln_defang($contentTemp);
    tln_unspace($contentTemp);

    $match   = array('/\/\*.*\*\//',
                    '/expression/i',
                    '/behaviou*r/i',
                    '/binding/i',
                    '/include-source/i',
                    '/javascript/i',
                    '/script/i',
                    '/position/i');
    $replace = array('','idiocy', 'idiocy', 'idiocy', 'idiocy', 'idiocy', 'idiocy', '');
    $contentNew = preg_replace($match, $replace, $contentTemp);
    if ($contentNew !== $contentTemp) {
        $content = $contentNew;
    }
    return array($content, $newpos);
}

function tln_body2div($attary, $trans_image_path)
{
    $divattary = array('class' => "'bodyclass'");
    $text = '#000000';
    $has_bgc_stl = $has_txt_stl = false;
    $styledef = '';
    if (is_array($attary) && sizeof($attary) > 0){
        foreach ($attary as $attname=>$attvalue){
            $quotchar = substr($attvalue, 0, 1);
            $attvalue = str_replace($quotchar, "", $attvalue);
            switch ($attname){
                case 'background':
                    $styledef .= "background-image: url('$trans_image_path'); ";
                    break;
                case 'bgcolor':
                    $has_bgc_stl = true;
                    $styledef .= "background-color: $attvalue; ";
                    break;
                case 'text':
                    $has_txt_stl = true;
                    $styledef .= "color: $attvalue; ";
                    break;
            }
        }
        if ($has_bgc_stl && !$has_txt_stl) {
            $styledef .= "color: $text; ";
        }
        if (strlen($styledef) > 0){
            $divattary["style"] = "\"$styledef\""; // 🌟 FIXED
        }
    }
    return $divattary;
}

function tln_sanitize(
    $body,
    $tag_list,
    $rm_tags_with_content,
    $self_closing_tags,
    $force_tag_closing,
    $rm_attnames,
    $bad_attvals,
    $add_attr_to_tag,
    $trans_image_path,
    $block_external_images
) {
    $rm_tags = array_shift($tag_list);
    @array_walk($tag_list, 'tln_casenormalize');
    @array_walk($rm_tags_with_content, 'tln_casenormalize');
    @array_walk($self_closing_tags, 'tln_casenormalize');
    $curpos = 0;
    $open_tags = array();
    $trusted = "\n";
    $skip_content = false;
    $body = preg_replace('/&(\{.*?\};)/si', '&amp;\\1', $body);
    while (($curtag = tln_getnxtag($body, $curpos)) != false) {
        list($tagname, $attary, $tagtype, $lt, $gt) = $curtag;
        $free_content = substr($body, $curpos, $lt-$curpos);
        if ($tagname == "style" && $tagtype == 1){
            list($free_content, $curpos) =
                tln_fixstyle($body, $gt+1, $trans_image_path, $block_external_images);
            if ($free_content != FALSE){
                if ( !empty($attary) ) {
                    $attary = tln_fixatts($tagname,
                                         $attary,
                                         $rm_attnames,
                                         $bad_attvals,
                                         $add_attr_to_tag,
                                         $trans_image_path,
                                         $block_external_images
                                 );
                }
                $trusted .= tln_tagprint($tagname, $attary, $tagtype);
                $trusted .= $free_content;
                $trusted .= tln_tagprint($tagname, null, 2);
            }
            continue;
        }
        if ($skip_content == false){
            $trusted .= $free_content;
        }
        if ($tagname != false) {
            if ($tagtype == 2) {
                if ($skip_content == $tagname) {
                    $tagname = false;
                    $skip_content = false;
                } else {
                    if ($skip_content == false) {
                        if ($tagname == "body") {
                            $tagname = "div";
                        }
                        if (isset($open_tags[$tagname]) && $open_tags[$tagname] > 0) { // 🌟 FIXED
                            $open_tags[$tagname]--; // 🌟 FIXED
                        } else {
                            $tagname = false;
                        }
                    }
                }
            } else {
                if ($skip_content == false) {
                    if ($tagtype == 1 && in_array($tagname, $self_closing_tags)) {
                        $tagtype = 3;
                    }
                    if ($tagtype == 1 && in_array($tagname, $rm_tags_with_content)) {
                        $skip_content = $tagname;
                    } else {
                        if (($rm_tags == false && in_array($tagname, $tag_list)) ||
                            ($rm_tags == true && !in_array($tagname, $tag_list))
                        ) {
                            $tagname = false;
                        } else {
                            if ($tagname == "body"){
                                $tagname = "div";
                                $attary = tln_body2div($attary, $trans_image_path);
                            }
                            if ($tagtype == 1) {
                                if (isset($open_tags[$tagname])) { // 🌟 FIXED
                                    $open_tags[$tagname]++; // 🌟 FIXED
                                } else {
                                    $open_tags[$tagname] = 1; // 🌟 FIXED
                                }
                            }
                            if (is_array($attary) && sizeof($attary) > 0) {
                                $attary = tln_fixatts(
                                    $tagname,
                                    $attary,
                                    $rm_attnames,
                                    $bad_attvals,
                                    $add_attr_to_tag,
                                    $trans_image_path,
                                    $block_external_images
                                );
                            }
                        }
                    }
                }
            }
            if ($tagname != false && $skip_content == false) {
                 $trusted .= tln_tagprint($tagname, $attary, $tagtype);
            }
        }
        $curpos = $gt + 1;
    }
    $trusted .= substr($body, $curpos, strlen($body) - $curpos);
    if ($force_tag_closing == true) {
        foreach ($open_tags as $tagname => $opentimes) {
            while ($opentimes > 0) {
                $trusted .= '</' . $tagname . '>';
                $opentimes--;
            }
        }
        $trusted .= "\n";
    }
    $trusted .= "\n";
    return $trusted;
}

function HTMLFilter($body, $trans_image_path, $block_external_images = false)
{
    $tag_list = array(
        false,
        "object",
        "meta",
        "html",
        "head",
        "base",
        "link",
        "frame",
        "iframe",
        "plaintext",
        "marquee"
    );

    $rm_tags_with_content = array(
        "script",
        "applet",
        "embed",
        "title",
        "frameset",
        "xmp",
        "xml"
    );
    $self_closing_tags =  array(
        "img",
        "br",
        "hr",
        "input",
        "outbind"
    );
    $force_tag_closing = true;

    $rm_attnames = array(
        "/.*/" =>
            array(
                "/^on.*/i",
                "/^dynsrc/i",
                "/^data.*/i",
                "/^lowsrc.*/i"
            )
    );
    $bad_attvals = array(
        "/.*/" =>
        array(
            "/^src|background/i" =>
            array(
                array(
                    '/^([\'"])\s*\S+script\s*:.*([\'"])/si',
                    '/^([\'"])\s*mocha\s*:*.*([\'"])/si',
                    '/^([\'"])\s*about\s*:.*([\'"])/si'
                ),
                array(
                    "\\1$trans_image_path\\2",
                    "\\1$trans_image_path\\2",
                    "\\1$trans_image_path\\2"
                )
            ),
            "/^href|action/i" =>
            array(
                array(
                    '/^([\'"])\s*\S+script\s*:.*([\'"])/si',
                    '/^([\'"])\s*mocha\s*:*.*([\'"])/si',
                    '/^([\'"])\s*about\s*:.*([\'"])/si'
                ),
                array(
                    "\\1#\\1",
                    "\\1#\\1",
                    "\\1#\\1"
                )
            ),
            "/^style/i" =>
            array(
                array(
                    "/\/\*.*\*\//",
                    "/expression/i",
                    "/binding/i",
                    "/behaviou*r/i",
                    "/include-source/i",
                    '/position\s*:/i',
                    '/(\\\\)?u(\\\\)?r(\\\\)?l(\\\\)?/i',
                    '/url\s*\(\s*([\'"])\s*\S+script\s*:.*([\'"])\s*\)/si',
                    '/url\s*\(\s*([\'"])\s*mocha\s*:.*([\'"])\s*\)/si',
                    '/url\s*\(\s*([\'"])\s*about\s*:.*([\'"])\s*\)/si',
                    '/(.*)\s*:\s*url\s*\(\s*([\'"]*)\s*\S+script\s*:.*([\'"]*)\s*\)/si'
                ),
                array(
                    "",
                    "idiocy",
                    "idiocy",
                    "idiocy",
                    "idiocy",
                    "idiocy",
                    "url",
                    "url(\\1#\\1)",
                    "url(\\1#\\1)",
                    "url(\\1#\\1)",
                    "\\1:url(\\2#\\3)"
                )
            )
        )
    );
    if ($block_external_images) {
        array_push(
            $bad_attvals['/.*/']['/^src|background/i'][0], // 🌟 FIXED
            '/^([\'\"])\s*https*:.*([\'\"])/si'
        );
        array_push(
            $bad_attvals['/.*/']['/^src|background/i'][1], // 🌟 FIXED
            "\\1$trans_image_path\\1"
        );
        array_push(
            $bad_attvals['/.*/']['/^style/i'][0], // 🌟 FIXED
            '/url\(([\'\"])\s*https*:.*([\'\"])\)/si'
        );
        array_push(
            $bad_attvals['/.*/']['/^style/i'][1], // 🌟 FIXED
            "url(\\1$trans_image_path\\1)"
        );
    }

    $add_attr_to_tag = array(
        "/^a$/i" =>
            array('target' => '"_blank"')
    );
    $trusted = tln_sanitize(
        $body,
        $tag_list,
        $rm_tags_with_content,
        $self_closing_tags,
        $force_tag_closing,
        $rm_attnames,
        $bad_attvals,
        $add_attr_to_tag,
        $trans_image_path,
        $block_external_images
    );
    return $trusted;
}