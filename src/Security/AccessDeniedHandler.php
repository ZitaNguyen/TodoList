<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;


class AccessDeniedHandler implements AccessDeniedHandlerInterface
{

    private $twig;


    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }


    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $content = $this->twig->render('error/error403.html.twig');

        return new Response($content, 403);
    }
}