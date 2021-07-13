<?php

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRoute
{
    /**
     * @Route("/path", name="action")
     */
    public function action():int
    {
        return 123;    //Used to prevent rule RemoveEmptyClassMethodRector
    }
}

