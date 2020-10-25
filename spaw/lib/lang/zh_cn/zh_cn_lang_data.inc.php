<?php
// ================================================
// SPAW PHP WYSIWYG editor control
// ================================================
// Chinese gb2312 language file
// ================================================
// Developed: Alan Mendelevich, alan@solmetra.lt
// Simplified Chinese translation: php_pp@hotmail.com; http://xoops.org.cn
// Copyright: Solmetra (c)2003 All rights reserved.
// ------------------------------------------------
//                                www.solmetra.com
// ================================================
// v.1.0, 2003-03-20
// ================================================

// charset to be used in dialogs
$spaw_lang_charset = 'gb2312';

// language text data array
// first dimension - block, second - exact phrase
// alternative text for toolbar buttons and title for dropdowns - 'title'

$spaw_lang_data = [
    'cut' => [
        'title' => '剪切',
    ],
    'copy' => [
        'title' => '复制',
    ],
    'paste' => [
        'title' => '粘贴',
    ],
    'undo' => [
        'title' => '复原',
    ],
    'redo' => [
        'title' => '重复',
    ],
    'image_insert' => [
        'title' => '插入图片',
        'select' => '选取',
        'delete' => '删除', // new 1.0.5
        'cancel' => '取消',
        'library' => '资料夹',
        'preview' => '预览',
        'images' => '图片',
        'upload' => '上传图片',
        'upload_button' => '上传',
        'error' => '错误',
        'error_no_image' => '请选定图片',
        'error_uploading' => '文件上传发生错误. 请稍候重传',
        'error_wrong_type' => '文件类型不符',
        'error_no_dir' => '找不到文件目录',
        'error_cant_delete' => '无法删除', // new 1.0.5
    ],
    'image_prop' => [
        'title' => '图片属性',
        'ok' => '   确定   ',
        'cancel' => '取消',
        'source' => '来源',
        'alt' => '文字提示',
        'align' => '对齐',
        'left' => '左',
        'right' => '右',
        'top' => '上',
        'middle' => '中',
        'bottom' => '下',
        'absmiddle' => '绝对中央',
        'texttop' => '文字顶端',
        'baseline' => '基线',
        'width' => '宽度',
        'height' => '高度',
        'border' => '边框宽度',
        'hspace' => '水平间距',
        'vspace' => '垂直间距',
        'error' => '错误',
        'error_width_nan' => '宽度不是数字',
        'error_height_nan' => '高度不是数字',
        'error_border_nan' => '边框宽度不是数字',
        'error_hspace_nan' => '水平间距不是数字',
        'error_vspace_nan' => '垂直间距不是数字',
    ],
    'hr' => [
        'title' => '水平线',
    ],
    'table_create' => [
        'title' => '新增表格',
    ],
    'table_prop' => [
        'title' => '表格属性',
        'ok' => '   确定   ',
        'cancel' => '取消',
        'rows' => '列数',
        'columns' => '行数',
        'css_class' => 'CSS类', // <=== new 1.0.6
        'width' => '宽度',
        'height' => '高度',
        'border' => '边框宽度',
        'pixels' => 'px',
        'cellpadding' => '文框间距',
        'cellspacing' => '框线间距',
        'bg_color' => '背景颜色',
        'background' => '背景图片', // <=== new 1.0.6
        'error' => '错误',
        'error_rows_nan' => '列数不是数字',
        'error_columns_nan' => '行数不是数字',
        'error_width_nan' => '宽度不是数字',
        'error_height_nan' => '高度不是数字',
        'error_border_nan' => '边框宽度不是数字',
        'error_cellpadding_nan' => '文框间距不是数字',
        'error_cellspacing_nan' => '框线间距不是数字',
    ],
    'table_cell_prop' => [
        'title' => '储存格属性',
        'horizontal_align' => '水平对齐',
        'vertical_align' => '垂直对齐',
        'width' => '宽度',
        'height' => '高度',
        'css_class' => 'CSS类',
        'no_wrap' => '文字不换行',
        'bg_color' => '背景颜色',
        'background' => '背景图片', // <=== new 1.0.6
        'ok' => '   确定   ',
        'cancel' => '取消',
        'left' => '左',
        'center' => '中',
        'right' => '右',
        'top' => '顶',
        'middle' => '中央',
        'bottom' => '底',
        'baseline' => '基准线',
        'error' => '错误',
        'error_width_nan' => '宽度不是数字',
        'error_height_nan' => '高度不是数字',
    ],
    'table_row_insert' => [
        'title' => '插入行',
    ],
    'table_column_insert' => [
        'title' => '插入列',
    ],
    'table_row_delete' => [
        'title' => '删除行',
    ],
    'table_column_delete' => [
        'title' => '删除列',
    ],
    'table_cell_merge_right' => [
        'title' => '向右合并',
    ],
    'table_cell_merge_down' => [
        'title' => '向下合并',
    ],
    'table_cell_split_horizontal' => [
        'title' => '水平分割',
    ],
    'table_cell_split_vertical' => [
        'title' => '垂直分割',
    ],
    'style' => [
        'title' => '类型',
    ],
    'font' => [
        'title' => '字体',
    ],
    'fontsize' => [
        'title' => '字号',
    ],
    'paragraph' => [
        'title' => '段落',
    ],
    'bold' => [
        'title' => '粗体',
    ],
    'italic' => [
        'title' => '斜体',
    ],
    'underline' => [
        'title' => '下划线',
    ],
    'ordered_list' => [
        'title' => '序号列',
    ],
    'bulleted_list' => [
        'title' => '点号表列',
    ],
    'indent' => [
        'title' => '增加缩进',
    ],
    'unindent' => [
        'title' => '减少缩进',
    ],
    'left' => [
        'title' => '靠左切齐',
    ],
    'center' => [
        'title' => '中间对齐',
    ],
    'right' => [
        'title' => '靠右切齐',
    ],
    'fore_color' => [
        'title' => '字体颜色',
    ],
    'bg_color' => [
        'title' => '背景颜色',
    ],
    'design_tab' => [
        'title' => '切换 WYSIWYG (效果)模式',
    ],
    'html_tab' => [
        'title' => '切换 HTML (源码)模式',
    ],
    'colorpicker' => [
        'title' => '调色盘',
        'ok' => '   确定   ',
        'cancel' => '取消',
    ],
    'cleanup' => [
        'title' => '清除HTML (移除网页格式)',
        'confirm' => '这个动作将会清除所有的网页格式，请注意.',
        'ok' => '   确定   ',
        'cancel' => '取消',
    ],
    'toggle_borders' => [
        'title' => '切换边线',
    ],
    'hyperlink' => [
        'title' => '超链接',
        'url' => '网址',
        'name' => '名称',
        'target' => '目标框架',
        'title_attr' => '主题',
        'a_type' => '类型', // <=== new 1.0.6
        'type_link' => '链接', // <=== new 1.0.6
        'type_anchor' => 'Anchor', // <=== new 1.0.6
        'type_link2anchor' => '链接anchor', // <=== new 1.0.6
        'anchors' => 'Anchors', // <=== new 1.0.6
        'ok' => '   确定   ',
        'cancel' => '取消',
    ],
    'hyperlink_targets' => [ // <=== new 1.0.5
                                       '_self' => '同一框架 (_self)',
                                       '_blank' => '新开窗口 (_blank)',
                                       '_top' => '最顶部框架 (_top)',
                                       '_parent' => '父框架 (_parent)',
    ],
    'table_row_prop' => [
        'title' => '行属性',
        'horizontal_align' => '水平对齐',
        'vertical_align' => '垂直对齐',
        'css_class' => 'CSS类',
        'no_wrap' => '不换行',
        'bg_color' => '背景颜色',
        'ok' => '   确定   ',
        'cancel' => '取消',
        'left' => '左',
        'center' => '中',
        'right' => '右',
        'top' => '顶',
        'middle' => '中央',
        'bottom' => '底部',
        'baseline' => '基线',
    ],
    'symbols' => [
        'title' => '特殊符号',
        'ok' => '   确定   ',
        'cancel' => '取消',
    ],
    'templates' => [
        'title' => '模板',
    ],
    'page_prop' => [
        'title' => '网页属性',
        'title_tag' => '主题',
        'charset' => '文字编码',
        'background' => '背景图片',
        'bgcolor' => '背景颜色',
        'text' => '文字颜色',
        'link' => '链接颜色',
        'vlink' => '访问过的链接颜色',
        'alink' => '正在执行的链接颜色',
        'leftmargin' => '左边界',
        'topmargin' => '上边界',
        'css_class' => 'CSS类',
        'ok' => '   确定   ',
        'cancel' => '取消',
    ],
    'preview' => [
        'title' => '预览',
    ],
    'image_popup' => [
        'title' => '图片弹出',
    ],
    'zoom' => [
        'title' => '收缩',
    ],
    'subscript' => [ // <=== new 1.0.7
                                       'title' => '下角标',
    ],
    'superscript' => [ // <=== new 1.0.7
                                       'title' => '上角标',
    ],
];
