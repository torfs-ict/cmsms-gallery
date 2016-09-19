<?php

/** @var Gallery $this */
/** @var array $params */
if (!isset($gCms)) exit;

$design_id = (int) get_parameter_value($params,'design_id',-1);
$type = CmsLayoutTemplateType::load('Gallery::page');
$type_id = $type->get_id();
$design = CmsLayoutCollection::load($design_id);
$template_list = $design->get_templates();

$out = array();
$templates = CmsLayoutTemplate::load_bulk($template_list);
if( is_array($templates) && count($templates) ) {
    foreach( $templates as $one ) {
        if( $one->get_type_id() != $type_id ) continue;
        if( !$one->get_listable() ) continue;
        $out[$one->get_id()] = $one->get_name();
    }
}

if( !is_array($out) || count($out) == 0 ) $out = null;
echo json_encode($out);
exit;
