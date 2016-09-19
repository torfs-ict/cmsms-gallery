<?php

class GalleryPage extends Content {
    /** @var Gallery */
    protected $mod;
    protected $mDirectory = null;

    public function __construct()
    {
        $this->mod = cms_utils::get_module('Gallery');
        parent::__construct();
    }

    public function ModuleName() {
        return 'Gallery';
    }

    function FillParams($params, $editing = false)
    {
        if (isset($params['directory'])) $this->SetPropertyValue('directory', $params['directory']);
        if (isset($params['gallery_template'])) $this->SetPropertyValue('gallery_template', $params['gallery_template']);
        parent::FillParams($params,$editing);
    }



    function FriendlyName()
    {
        return $this->mod->Lang('type_page');
    }

    public function HasPreview()
    {
        return true;
    }

    public function HasTemplate()
    {
        return true;
    }

    public function Cachable()
    {
        return false;
    }


    public function IsDefaultPossible()
    {
        return true;
    }

    public function IsViewable()
    {
        return true;
    }

    function SetProperties()
    {
        parent::SetProperties();
        $this->AddProperty('directory', 3, self::TAB_MAIN, true);
        $this->AddProperty('gallery_template', 0, self::TAB_OPTIONS, true);
    }

    function Show($param = 'content_en')
    {
        if (empty($this->mod)) {
            $this->mod = cms_utils::get_module('Gallery');
        }
        if ($param == 'content_en' && CmsApp::get_instance()->is_frontend_request()) {
            $template = CmsLayoutTemplate::load((int)$this->GetPropertyValue('gallery_template'))->get_name();
            $ret = parent::Show($param);
            $smarty = CmsApp::get_instance()->GetSmarty();
            $obj = $smarty->createTemplate($this->mod->GetTemplateResource($template), null, null, $smarty);
            $obj->assignGlobal('gallery', $this);
            $ret .= $obj->fetch();
            return $ret;
        }
        return parent::Show($param); // TODO: Change the autogenerated stub
    }


    protected function display_single_element($one, $adding)
    {
        static $_templates;

        if( $_templates == null ) {
            $_type = CmsLayoutTemplateType::load('Gallery::page');
            $_tpl = CmsLayoutTemplate::template_query(array('t:'.$_type->get_id(), 'as_list'=>1));
            if( is_array($_tpl) && count($_tpl) > 0 ) {
                $_templates = array();
                foreach( $_tpl as $tpl_id => $tpl_name ) {
                    $_templates[] = array('value'=>$tpl_id,'label'=>$tpl_name);
                }
            }
        }

        switch ($one) {

            case 'gallery_template':
                try {
                    $template_id = $this->GetPropertyValue('gallery_template');
                    if ($template_id < 1) {
                        try {
                            $dflt_tpl = CmsLayoutTemplate::load_dflt_by_type('Gallery::page');
                            $template_id = $dflt_tpl->get_id();
                        } catch (Exception $e) {
                            audit('', 'CMSContentManager', 'No default page template found');
                        }
                    }
                    $out = CmsFormUtils::create_dropdown('gallery_template', $_templates, $template_id, array('id' => 'gallery_template'));
                    return array('<label for="gallery_template">*' . $this->mod->Lang('label_gallery_template') . ':</label>', $out);
                } catch (CmsException $e) {
                    // nothing here yet.
                }
                break;

            case 'directory':
                $dirs = glob(CmsApp::get_instance()->GetConfig()->offsetGet('image_uploads_path') . '/*', GLOB_ONLYDIR);
                array_walk($dirs, function(&$value, &$key) {
                    $value = basename($value);
                });
                $dirs = array_combine($dirs, $dirs);
                return array(
                    sprintf('<label for="gallery_directory">%s</label>', htmlentities($this->mod->Lang('label_directory'))),
                    CmsFormUtils::create_dropdown('directory', $dirs, $this->GetPropertyValue('directory'), array('id' => 'gallery_directory'))
                );
                break;

            default:
                return parent::display_single_element($one, $adding);
        }
    }

    public function GetImages() {
        $update = false;
        $directory = $this->GetPropertyValue('directory');
        $glob = glob(sprintf('%s/%s/*.*', $this->mod->config->offsetGet('image_uploads_path'), $directory));
        array_walk($glob, function(&$image) use (&$update, $directory) {
            $test = @getimagesize($image);
            if ($test === false) {
                $image = null;
                $update = true;
            } else {
                $image = sprintf('%s/%s', $directory, basename($image));
            }
        });
        if ($update === true) {
            return array_values(array_filter($glob));
        } else {
            return $glob;
        }
    }

}