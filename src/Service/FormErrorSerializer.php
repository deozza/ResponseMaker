<?php
namespace Deozza\ResponseMakerBundle\Service;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class FormErrorSerializer
{
    private $translator;

    public function convertFormToArray(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->convertFormToArray($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }
}