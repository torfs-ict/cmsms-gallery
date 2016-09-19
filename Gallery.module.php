<?php

class Gallery extends CMSModule {
    public function __construct() {
        $this->RegisterContentType();
        parent::__construct();
    }

    public function GetVersion()
    {
        return '1.0.0-alpha.1';
    }

    public function GetAuthor()
    {
        return 'Kristof Torfs';
    }

    public function GetAuthorEmail()
    {
        return 'kristof@torfs.org';
    }

    public function HasCapability($capability, $params = array())
    {
        switch($capability) {
            case CmsCoreCapabilities::CONTENT_TYPES: return true;
        }
        return false;
    }


    public function Install()
    {
        try {
            $uid = null;
            if( cmsms()->test_state(CmsApp::STATE_INSTALL) ) {
                $uid = 1; // hardcode to first user
            } else {
                $uid = get_userid();
            }

            // Setup page template
            $templateType = new CmsLayoutTemplateType();
            $templateType->set_originator($this->GetName());
            $templateType->set_name('page');
            $templateType->set_dflt_flag(TRUE);
            $templateType->set_lang_callback('Gallery::page_type_lang_callback');
            $templateType->set_content_callback('Gallery::reset_page_type_defaults');
            $templateType->reset_content_to_factory();
            $templateType->save();

            // Setup Simplex Theme HTML5 sample gallery template
            $fn = cms_join_path(__DIR__, 'templates', 'page.tpl');
            if (file_exists($fn)) {
                $template = @file_get_contents($fn);
                $tpl = new CmsLayoutTemplate();
                $tpl->set_name('Simplex Gallery');
                $tpl->set_owner($uid);
                $tpl->set_content($template);
                $tpl->set_type($templateType);
                $tpl->add_design('Simplex');
                $tpl->save();
            }

        } catch (CmsException $e) {
            // log it
            debug_to_log(__FILE__ . ':' . __LINE__ . ' ' . $e->GetMessage());
            audit('', $this->GetName(), 'Installation error: ' . $e->GetMessage());
        }
        return false;
    }

    public function IsPluginModule()
    {
        return true;
    }


    public function Uninstall()
    {
        try {
            // Remove templates & template types
            $types = CmsLayoutTemplateType::load_all_by_originator($this->GetName());
            if (is_array($types) && count($types)) {
                foreach($types as $type) {
                    $templates = $type->get_template_list();
                    if (is_array($templates) && count($templates)) {
                        foreach($templates as $template) {
                            $template->delete();
                        }
                    }
                    $type->delete();
                }
            }
        } catch (Exception $e) {
            // log it
            debug_to_log(__FILE__ . ':' . __LINE__ . ' ' . $e->GetMessage());
            audit('', $this->GetName(), 'Uninstall error: ' . $e->GetMessage());
        }
        return false;
    }

    public static function page_type_lang_callback($str)
    {
        $mod = cms_utils::get_module('Gallery');
        if (is_object($mod)) return $mod->Lang('type_' . $str);
    }

    public static function reset_page_type_defaults(CmsLayoutTemplateType $type)
    {
        if ($type->get_originator() != 'Gallery') throw new CmsLogicException('Cannot reset contents for this template type');

        $fn = null;
        switch( $type->get_name() ) {
            case 'page':
                $fn = 'page.tpl';
                break;
        }

        $fn = cms_join_path(__DIR__, 'templates', $fn);
        if (file_exists($fn)) return @file_get_contents($fn);
    }

    protected function RegisterContentType() {
        $pc = new CmsContentTypePlaceholder();
        $pc->class = 'GalleryPage';
        $pc->filename = realpath(cms_join_path(__DIR__, 'lib', 'Gallery', 'Page.php'));
        $pc->friendlyname = $this->Lang('type_page');
        $pc->loaded = false;
        $pc->type = 'gallerypage';
        ContentOperations::get_instance()->register_content_type($pc);
    }

    protected function InitializeAdmin()
    {
        $this->RegisterContentType();
    }

    protected function InitializeFrontend()
    {
        $this->RegisterContentType();
    }

}