<?php
// ================================================
// SPAW PHP WYSIWYG editor control
// ================================================
// English language file
// ================================================
// Developed: Alan Mendelevich, alan@solmetra.lt
// Copyright: Solmetra (c)2003 All rights reserved.
// ------------------------------------------------
//                                www.solmetra.com
// ================================================
// v.1.0, 2003-03-20
// ================================================

// charset to be used in dialogs
$spaw_lang_charset = 'iso-8859-1';

// language text data array
// first dimension - block, second - exact phrase
// alternative text for toolbar buttons and title for dropdowns - 'title'

$spaw_lang_data = [
    'cut' => [
        'title' => 'Cut',
    ],
    'copy' => [
        'title' => 'Copy',
    ],
    'paste' => [
        'title' => 'Paste',
    ],
    'undo' => [
        'title' => 'Undo',
    ],
    'redo' => [
        'title' => 'Redo',
    ],
    'image_insert' => [
        'title' => 'Insert image',
        'select' => 'Select',
        'delete' => 'Delete', // new 1.0.5
        'cancel' => 'Cancel',
        'library' => 'Library',
        'preview' => 'Preview',
        'images' => 'Images',
        'upload' => 'Upload image',
        'upload_button' => 'Upload',
        'error' => 'Error',
        'error_no_image' => 'Please select an image',
        'error_uploading' => 'An error occured while handling file upload. Please try again later',
        'error_wrong_type' => 'Wrong image file type',
        'error_no_dir' => 'Library doesn\'t physically exist',
        'error_cant_delete' => 'Delete failed', // new 1.0.5
    ],
    'image_prop' => [
        'title' => 'Image properties',
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
        'source' => 'Source',
        'alt' => 'Alternative text',
        'align' => 'Align',
        'left' => 'left',
        'right' => 'right',
        'top' => 'top',
        'middle' => 'middle',
        'bottom' => 'bottom',
        'absmiddle' => 'absmiddle',
        'texttop' => 'texttop',
        'baseline' => 'baseline',
        'width' => 'Width',
        'height' => 'Height',
        'border' => 'Border',
        'hspace' => 'Hor. space',
        'vspace' => 'Vert. space',
        'error' => 'Error',
        'error_width_nan' => 'Width is not a number',
        'error_height_nan' => 'Height is not a number',
        'error_border_nan' => 'Border is not a number',
        'error_hspace_nan' => 'Horizontal space is not a number',
        'error_vspace_nan' => 'Vertical space is not a number',
    ],
    'hr' => [
        'title' => 'Horizontal rule',
    ],
    'table_create' => [
        'title' => 'Create table',
    ],
    'table_prop' => [
        'title' => 'Table properties',
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
        'rows' => 'Rows',
        'columns' => 'Columns',
        'css_class' => 'CSS class', // <=== new 1.0.6
        'width' => 'Width',
        'height' => 'Height',
        'border' => 'Border',
        'pixels' => 'pixels',
        'cellpadding' => 'Cell padding',
        'cellspacing' => 'Cell spacing',
        'bg_color' => 'Background color',
        'background' => 'Background image', // <=== new 1.0.6
        'error' => 'Error',
        'error_rows_nan' => 'Rows is not a number',
        'error_columns_nan' => 'Columns is not a number',
        'error_width_nan' => 'Width is not a number',
        'error_height_nan' => 'Height is not a number',
        'error_border_nan' => 'Border is not a number',
        'error_cellpadding_nan' => 'Cell padding is not a number',
        'error_cellspacing_nan' => 'Cell spacing is not a number',
    ],
    'table_cell_prop' => [
        'title' => 'Cell properties',
        'horizontal_align' => 'Horizontal align',
        'vertical_align' => 'Vertical align',
        'width' => 'Width',
        'height' => 'Height',
        'css_class' => 'CSS class',
        'no_wrap' => 'No wrap',
        'bg_color' => 'Background color',
        'background' => 'Background image', // <=== new 1.0.6
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
        'top' => 'Top',
        'middle' => 'Middle',
        'bottom' => 'Bottom',
        'baseline' => 'Baseline',
        'error' => 'Error',
        'error_width_nan' => 'Width is not a number',
        'error_height_nan' => 'Height is not a number',
    ],
    'table_row_insert' => [
        'title' => 'Insert row',
    ],
    'table_column_insert' => [
        'title' => 'Insert column',
    ],
    'table_row_delete' => [
        'title' => 'Delete row',
    ],
    'table_column_delete' => [
        'title' => 'Delete column',
    ],
    'table_cell_merge_right' => [
        'title' => 'Merge right',
    ],
    'table_cell_merge_down' => [
        'title' => 'Merge down',
    ],
    'table_cell_split_horizontal' => [
        'title' => 'Split cell horizontally',
    ],
    'table_cell_split_vertical' => [
        'title' => 'Split cell vertically',
    ],
    'style' => [
        'title' => 'Style',
    ],
    'font' => [
        'title' => 'Font',
    ],
    'fontsize' => [
        'title' => 'Size',
    ],
    'paragraph' => [
        'title' => 'Paragraph',
    ],
    'bold' => [
        'title' => 'Bold',
    ],
    'italic' => [
        'title' => 'Italic',
    ],
    'underline' => [
        'title' => 'Underline',
    ],
    'ordered_list' => [
        'title' => 'Ordered list',
    ],
    'bulleted_list' => [
        'title' => 'Bulleted list',
    ],
    'indent' => [
        'title' => 'Indent',
    ],
    'unindent' => [
        'title' => 'Unindent',
    ],
    'left' => [
        'title' => 'Left',
    ],
    'center' => [
        'title' => 'Center',
    ],
    'right' => [
        'title' => 'Right',
    ],
    'fore_color' => [
        'title' => 'Fore color',
    ],
    'bg_color' => [
        'title' => 'Background color',
    ],
    'design_tab' => [
        'title' => 'Switch to WYSIWYG (design) mode',
    ],
    'html_tab' => [
        'title' => 'Switch to HTML (code) mode',
    ],
    'colorpicker' => [
        'title' => 'Color picker',
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
    ],
    'cleanup' => [
        'title' => 'HTML cleanup (remove styles)',
        'confirm' => 'Performing this action will remove all styles, fonts and useless tags from the current content. Some or all your formatting may be lost.',
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
    ],
    'toggle_borders' => [
        'title' => 'Toggle borders',
    ],
    'hyperlink' => [
        'title' => 'Hyperlink',
        'url' => 'URL',
        'name' => 'Name',
        'target' => 'Target',
        'title_attr' => 'Title',
        'a_type' => 'Type', // <=== new 1.0.6
        'type_link' => 'Link', // <=== new 1.0.6
        'type_anchor' => 'Anchor', // <=== new 1.0.6
        'type_link2anchor' => 'Link to anchor', // <=== new 1.0.6
        'anchors' => 'Anchors', // <=== new 1.0.6
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
    ],
    'hyperlink_targets' => [ // <=== new 1.0.5
                                       '_self' => 'same frame (_self)',
                                       '_blank' => 'new empty window (_blank)',
                                       '_top' => 'top frame (_top)',
                                       '_parent' => 'parent frame (_parent)',
    ],
    'table_row_prop' => [
        'title' => 'Row properties',
        'horizontal_align' => 'Horizontal align',
        'vertical_align' => 'Vertical align',
        'css_class' => 'CSS class',
        'no_wrap' => 'No wrap',
        'bg_color' => 'Background color',
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
        'top' => 'Top',
        'middle' => 'Middle',
        'bottom' => 'Bottom',
        'baseline' => 'Baseline',
    ],
    'symbols' => [
        'title' => 'Special characters',
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
    ],
    'templates' => [
        'title' => 'Templates',
    ],
    'page_prop' => [
        'title' => 'Page properties',
        'title_tag' => 'Title',
        'charset' => 'Charset',
        'background' => 'Background image',
        'bgcolor' => 'Background color',
        'text' => 'Text color',
        'link' => 'Link color',
        'vlink' => 'Visited link color',
        'alink' => 'Active link color',
        'leftmargin' => 'Left margin',
        'topmargin' => 'Top margin',
        'css_class' => 'CSS class',
        'ok' => '   OK   ',
        'cancel' => 'Cancel',
    ],
    'preview' => [
        'title' => 'Preview',
    ],
    'image_popup' => [
        'title' => 'Image popup',
    ],
    'zoom' => [
        'title' => 'Zoom',
    ],
    'subscript' => [ // <=== new 1.0.7
                                       'title' => 'Subscript',
    ],
    'superscript' => [ // <=== new 1.0.7
                                       'title' => 'Superscript',
    ],
];


