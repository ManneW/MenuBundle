<?php

namespace Symfony\Cmf\Bundle\MenuBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Symfony\Cmf\Bundle\MenuBundle\Document\MenuItem;

class MenuItemAdmin extends Admin
{
    protected $contentRoot;
    protected $menuRoot;

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', 'text')
            ->add('name', 'text')
            ->add('label', 'text')
            ->add('uri', 'text')
            ->add('route', 'text')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('parent', 'doctrine_phpcr_type_tree_model', array('root_node' => $this->menuRoot, 'choice_list' => array(), 'select_root_node' => true))
                ->add('name', 'text', ($this->hasSubject() && null !== $this->getSubject()->getId()) ? array('attr' => array('readonly' => 'readonly')) : array())
                ->add('label', 'text')
                ->add('uri', 'text', array('required' => false))
                ->add('route', 'text', array('required' => false))
                ->add('content', 'doctrine_phpcr_type_tree_model', array('root_node' => $this->contentRoot, 'choice_list' => array(), 'required' => false))
            ->end();
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', 'text')
            ->add('name', 'text')
            ->add('label', 'text')
            ->add('uri', 'text')
            ->add('content', 'text')
        ;
    }

    public function getNewInstance()
    {
        /** @var $new MenuItem */
        $new = parent::getNewInstance();
        if ($this->hasRequest()) {
            $parentId = $this->getRequest()->query->get('parent');
            if (null !== $parentId) {
                $new->setParent($this->getModelManager()->find(null, $parentId));
            }
        }
        return $new;
    }

    public function getExportFormats()
    {
        return array();
    }

    public function setContentRoot($contentRoot)
    {
        $this->contentRoot = $contentRoot;
    }

    public function setMenuRoot($menuRoot)
    {
        $this->menuRoot = $menuRoot;
    }

    public function setContentTreeBlock($contentTreeBlock)
    {
        $this->contentTreeBlock = $contentTreeBlock;
    }

    public function prePersist($object)
    {
        if ($object->getParent() instanceof MenuItem) {
            // check the name and append item in case it's missing
            $name = $object->getName();
            if ('item' !==  substr($name, -strlen($name))) {
                $name .= '-item';
                $object->setName($name);
            }
        }
    }

    /**
     * Return the content tree to show at the left, current node (or parent for new ones) selected
     *
     * @param string $position
     * @return array
     */
    public function getBlocks($position)
    {
        if ('left' == $position) {
            $selected = ($this->hasSubject() && $this->getSubject()->getId()) ? $this->getSubject()->getId() : ($this->hasRequest() ? $this->getRequest()->query->get('parent') : null);
            return array(array('type' => 'sonata_admin_doctrine_phpcr.tree_block', 'settings' => array('selected' => $selected)));
        }
    }

}
