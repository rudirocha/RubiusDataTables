<?php

namespace Rubius\DataTablesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('RubiusDataTablesBundle:Default:index.html.twig', array('name' => $name));
    }
}
