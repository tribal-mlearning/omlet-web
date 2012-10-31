<?php

namespace Frontend\PortalBundle\Controller;

use Core\Library\EntityBundle\Services\EntityLibrary;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations\Prefix;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Core\Library\EntityBundle\Form\Type\PackageType;
use Core\Library\EntityBundle\Entity\Package;
use Symfony\Component\HttpFoundation\Response;
use Core\Library\EntityBundle\Entity\File;
use Core\Library\EntityBundle\Entity\PermissionContext;
use Core\Security\AccessControlBundle\Annotations\Permissions;

class PackageController extends Controller
{
    /**
     * Package index
     *
     * @Route("/")
     * @Template()
     * @Method("GET")
     * @Permissions("view", context="package")
     */
    public function indexAction() {
        $packageService = $this->get('service_package.packages');
        $categoriesService = $this->get('service_package.categories');
        return array(
            'packages' => $packageService->findPackagesForCurrentUser(),
            'hasCategories' => $categoriesService->countCategories(),
        );
    }

    /**
     * Show details of a package
     *
     * @Route("/show/{id}")
     * @Template()
     * @Method("GET")
     * @Permissions("view", context="package")
     */
    public function showAction($id)
    {
        $packageService = $this->get('service_package.packages');
        $categoriesService = $this->get('service_package.categories');
        return array(
            'entity' => $packageService->find($id),
            'hasCategories' => $categoriesService->countCategories(),
        );
    }

     /**
      * Deletes a package
      *
     * @Route("/delete/{id}")
     * @Template()
     * @Method("GET")
     * @Permissions("delete", context="package")
     */
    public function deleteAction($id)
    {
        $packageService = $this->get('service_package.packages');
        $packageService->delete($id);

        $translatorService = $this->get('translator');

        $this->get('session')->setFlash('success', $translatorService->trans('message.delete.success', array(), 'package'));
        return $this->redirect($this->generateUrl('frontend_portal_package_index'));
    }

    /**
     * New package
     *
     * @Route("/new")
     * @Template()
     * @Method("GET")
     * @Permissions("create", context="package")
     */
    public function newAction()
    {
        $categoriesService = $this->get('service_package.categories');
        $translatorService = $this->get('translator');
        
        $entity = new Package();
        $form = $this->createForm(new PackageType($translatorService), $entity);
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'hasCategories' => $categoriesService->countCategories(),
        );
    }

    /**
     * Create a package
     *
     * @Route("/create")
     * @Template("FrontendPortalBundle:Package:new.html.twig")
     * @Method("POST")
     * @Permissions("create", context="package")
     */
    public function createAction()
    {
        $categoriesService = $this->get('service_package.categories');
        $packageService = $this->get('service_package.packages');
        $translatorService = $this->get('translator');

        $entity = new Package();
        $request = $this->getRequest();
        $form = $this->createForm(new PackageType($translatorService), $entity, array('validation_groups' => array('Default', 'create')));
        $form->bindRequest($request);

        if ($form->isValid()) {
            $packageService->save($entity);

            $this->get('session')->setFlash('success', $translatorService->trans('message.create.success', array(), 'package'));

            return $this->redirect($this->generateUrl('frontend_portal_package_show', array('id' => $entity->getId())));
        }

        $this->get('session')->setFlash('error', $translatorService->trans('message.create.fail', array(), 'package'));

        return array(
            'entity' => $entity, 
            'form' => $form->createView(),
            'hasCategories' => $categoriesService->countCategories()
        );
    }

    /**
     * Edit a package
     *
     * @Route("/edit/{id}")
     * @Template()
     * @Method("GET")
     * @Permissions("edit", context="package")
     */
    public function editAction($id)
    {
        $packageService = $this->get('service_package.packages');
        $categoriesService = $this->get('service_package.categories');
        $translatorService = $this->get('translator');
        
        $entity = $packageService->find($id);
        $form = $this->createForm(new PackageType($translatorService), $entity);
        return array(
            'entity' => $entity,
            'edit_form' => $form->createView(),
            'hasCategories' => $categoriesService->countCategories(),
        );
    }

    /**
     * Update a package
     *
     * @Route("/update/{id}")
     * @Template("FrontendPortalBundle:Package:edit.html.twig")
     * @Method("POST")
     * @Permissions("edit", context="package")
     */
    public function updateAction($id)
    {
        $packageService = $this->get('service_package.packages');
        $categoriesService = $this->get('service_package.categories');
        $translatorService = $this->get('translator');

        $entity = $packageService->find($id);
        $request = $this->getRequest();
        $form = $this->createForm(new PackageType($translatorService), $entity);
        
        // Treating Symfony bug that throws an exception on binding when the date format is wrong. This should be fired on form validation.
        // http://stackoverflow.com/questions/8083363/symfony2-validating-a-date-using-the-form-validator-returns-error
        try {
            $form->bindRequest($request);
        } catch(\InvalidArgumentException $e) {
            $this->get('session')->setFlash('error', $translatorService->trans('message.update.fail', array(), 'package'));
            $form['publishStart']->addError(new \Symfony\Component\Form\FormError($translatorService->trans('message.publish_start.invalid_format', array(), 'package')));
            return array('entity' => $entity, 'edit_form' => $form->createView(), 'hasCategories' => $categoriesService->countCategories());
        }

        if ($form->isValid()) {
            $packageService->save($entity);

            $this->get('session')->setFlash('success', $translatorService->trans('message.update.success', array(), 'package'));

            return $this->redirect($this->generateUrl('frontend_portal_package_index'));
        }

        $this->get('session')->setFlash('error', $translatorService->trans('message.update.fail', array(), 'package'));

        return array(
            'entity' => $entity,
            'edit_form' => $form->createView(),
            'hasCategories' => $categoriesService->countCategories(),
        );
    }

    /**
     * Download a package
     *
     * @Route("/download/{id}/file/{file_id}")
     * @Method("GET")
     * @Permissions("download", context="package")
     */
    public function downloadAction($id, $file_id)
    {
        $package = $this->get('service_package.packages')->find($id);

        $file = $package->getFile($file_id)->getFile();

        $response = new Response(file_get_contents($file->getAbsolutePath()));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $file->getName() . '"');

        return $response;
    }
}
