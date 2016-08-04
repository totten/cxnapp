<?php

namespace Civi\MemberStatusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CiviMemberStatusBundle:Default:index.html.twig', array('name' => $name));
    }
}
