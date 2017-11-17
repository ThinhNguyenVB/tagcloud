<?php

/**
 * @Project NUKEVIET 4.x
 * @Author Thinhweb Blog <thinhwebhp@gmail.com>
 * @Copyright (C) 2017 Thinhweb Blog. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sat, 11 Dec 2017 06:46:54 GMT
 */

if (! defined('NV_MAINFILE')) {
    die('Stop!!!');
}

if (! nv_function_exists('nv_news_tag')) {
    function nv_config_news_tag($module, $data_block, $lang_block)
    {
        global $nv_Cache, $site_mods, $db_config;

        $html_input = '';
        $html = '';

        $html .= '<div class="form-group">';
        $html .= '  <label class="control-label col-sm-6">' . $lang_block['module'] . ':</label>';
        $html .= '  <div class="col-sm-18"><select name="config_module_name" id="config_module_name" class="w200 form-control">';
        $sql = 'SELECT title, module_data, custom_title FROM ' . $db_config['prefix'] . '_' . NV_LANG_DATA . '_modules WHERE module_file = "news"';
        $list = $nv_Cache->db( $sql, 'title', $module );
        foreach( $list as $l )
        {
            $sel = ( $data_block['module_name'] == $l['title'] ) ? ' selected' : '';
            $html .= '<option value="' . $l['title'] . '" ' . $sel . '>' . $l['custom_title'] . '</option>';
        }

        $html .= '  </select>';
        $html .= '  </div>';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-sm-6">' . $lang_block['title_length'] . ':</label>';
        $html .= '<div class="col-sm-18"><input type="text" class="form-control w200" name="config_title_length" size="5" value="' . $data_block['title_length'] . '"/></div>';
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-sm-6">' . $lang_block['numrow'] . ':</label>';
        $html .= '<div class="col-sm-18"><input type="text" class="form-control w200" name="config_numrow" size="5" value="' . $data_block['numrow'] . '"/></div>';
        $html .= '</div>';
        return $html;
    }

    function nv_config_news_tag_submit($module, $lang_block)
    {
        global $nv_Request;
        $return = array();
        $return['error'] = array();
        $return['config'] = array();
        $return['config']['module_name'] = $nv_Request->get_title('config_module_name', 'post', 'news');
        $return['config']['numrow'] = $nv_Request->get_int('config_numrow', 'post', 0);
        return $return;
    }

    function nv_news_tag($block_config)
    {
        global $module_array_cat, $site_mods, $module_config, $global_config, $nv_Cache, $db, $lang_block;
        $module = $block_config['module_name'];
        $show_no_image = $module_config[$module]['show_no_image'];
        $blockwidth = $module_config[$module]['blockwidth'];

        if (file_exists(NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/blocks/global.block_tag.tpl')) {
            $block_theme = $global_config['module_theme'];
        } else {
            $block_theme = 'default';
        }
        $xtpl = new XTemplate('global.block_tag.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/blocks');
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('TEMPLATE', $block_theme);
        $xtpl->assign('BLANG', $lang_block);

        $tags_number = $block_config['numrow'];

        /// tin binh luan nhieu
        $db->sqlreset()
            ->select('tid, numnews, alias, description, keywords')
            ->from(NV_PREFIXLANG . '_' . $module. '_tags')
            ->order('tid DESC')
            ->limit($tags_number);

        $list_news_tags = $nv_Cache->db($db->sql(), '', $module);

        if (! empty($list_news_tags)) {
            $t = sizeof($list_news_tags) - 1;
            $minFontSize = 12;
            $maxFontSize = 30;
            $minimumCount = min($list_news_tags);
            $maximumCount = max($list_news_tags);
            $spread       = $maximumCount['numnews'] - $minimumCount['numnews'];
            $spread == 0 && $spread = 1;

            foreach ($list_news_tags as  $i => $l_news_tags) {
                $size = $minFontSize + ( $l_news_tags['numnews'] - $minimumCount['numnews']) * ( $maxFontSize - $minFontSize ) / $spread;

                if ($size < 20){
                    $class = 'smallest';
                }
                elseif ($size >= 20 and $size < 40){
                    $class = 'small';
                }
                elseif ($size >= 40 and $size < 60){
                    $class = 'medium';
                }
                elseif ($size >= 60 and $size < 80){
                    $class = 'large';
                }
                else{
                    $class = 'largest';
                }

                $l_news_tags['size'] = $class;
                $l_news_tags['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module. '&amp;' . NV_OP_VARIABLE . '=tag/' . urlencode($l_news_tags['alias']);
                $xtpl->assign('ROW_NEWS_TAGS', $l_news_tags);
                $xtpl->assign('SLASH', ($t == $i) ? '' : ', ');
                $xtpl->parse('main.loop_news_tags');
            }
        }

        $xtpl->parse('main');
        return $xtpl->text('main');

    }
}

if (defined('NV_SYSTEM')) {
    global $site_mods, $module_name, $global_array_cat, $module_array_cat, $nv_Cache, $db;
    $module = $block_config['module_name'];
    if (isset($site_mods[$module])) {
        if ($module == $module_name) {
            $module_array_cat = $global_array_cat;
            unset($module_array_cat[0]);
        } else {
            $module_array_cat = array();
            $sql = 'SELECT catid, parentid, title, alias, viewcat, subcatid, numlinks, description, inhome, keywords, groups_view FROM ' . NV_PREFIXLANG . '_' . $site_mods[$module]['module_data'] . '_cat ORDER BY sort ASC';
            $list = $nv_Cache->db($sql, 'catid', $module);
            if(!empty($list))
            {
                foreach ($list as $l) {
                    $module_array_cat[$l['catid']] = $l;
                    $module_array_cat[$l['catid']]['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $l['alias'];
                }
            }
        }
        $content = nv_news_tag($block_config);
    }
}